<?php
require_once 'config.php';

// ============================================
// CONSULTAS AO BANCO DE DADOS - SEM VIEW
// ============================================

$conn = getConnection();

// 1. DADOS GERAIS - SUBSTITUINDO A VIEW
$query_view = "SELECT 
    COUNT(*) as total_avaliacoes,
    SUM(CASE 
        WHEN nota_experiencia IN ('MUITO_RUIM', 'RUIM') THEN 1 
        ELSE 0 
    END) as negativas,
    SUM(CASE 
        WHEN nota_experiencia = 'REGULAR' THEN 1 
        ELSE 0 
    END) as neutras,
    SUM(CASE 
        WHEN nota_experiencia IN ('BOM', 'EXCELENTE') THEN 1 
        ELSE 0 
    END) as positivas,
    ROUND(
        SUM(CASE 
            WHEN nota_experiencia IN ('BOM', 'EXCELENTE') THEN 1 
            ELSE 0 
        END) * 100.0 / 
        NULLIF(COUNT(*), 0), 
        2
    ) as csat,
    ROUND(AVG(NULLIF(nota_atendimento, 0)), 1) as media_atendimento,
    ROUND(AVG(NULLIF(nota_ambiente, 0)), 1) as media_ambiente,
    ROUND(AVG(NULLIF(nota_qualidade_servico, 0)), 1) as media_qualidade
FROM avaliacoes";

$result_view = $conn->query($query_view);
$dados = $result_view ? $result_view->fetch_assoc() : null;

// 2. VOLUME MENSAL (últimos 12 meses)
$query_mensal = "SELECT 
    DATE_FORMAT(data_avaliacao, '%Y-%m') as mes_ano,
    DATE_FORMAT(data_avaliacao, '%b') as mes_nome,
    COUNT(*) as total
FROM avaliacoes
WHERE data_avaliacao >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY mes_ano, mes_nome
ORDER BY mes_ano
LIMIT 12";

$result_mensal = $conn->query($query_mensal);
$volume_data = [];
$meses_labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

if ($result_mensal) {
    while ($row = $result_mensal->fetch_assoc()) {
        $volume_data[] = $row;
    }
}

// 3. COMENTÁRIOS RECENTES
$query_comentarios = "SELECT 
    a.nota_experiencia,
    a.comentario,
    DATE_FORMAT(a.data_avaliacao, '%d/%m/%Y') as data_formatada,
    a.nota_atendimento,
    a.nota_ambiente,
    a.nota_qualidade_servico,
    CASE 
        WHEN a.nota_experiencia IN ('MUITO_RUIM', 'RUIM') THEN 'negativa'
        WHEN a.nota_experiencia = 'REGULAR' THEN 'neutra'
        WHEN a.nota_experiencia IN ('BOM', 'EXCELENTE') THEN 'positiva'
        ELSE 'neutra'
    END as categoria,
    CASE 
        WHEN a.nota_experiencia = 'MUITO_RUIM' THEN 'Muito Ruim'
        WHEN a.nota_experiencia = 'RUIM' THEN 'Ruim'
        WHEN a.nota_experiencia = 'REGULAR' THEN 'Regular'
        WHEN a.nota_experiencia = 'BOM' THEN 'Bom'
        WHEN a.nota_experiencia = 'EXCELENTE' THEN 'Excelente'
        ELSE 'Regular'
    END as avaliacao_texto
FROM avaliacoes a
WHERE a.comentario IS NOT NULL AND TRIM(a.comentario) != ''
ORDER BY a.data_avaliacao DESC
LIMIT 10";

$result_comentarios = $conn->query($query_comentarios);
$comentarios = [];
if ($result_comentarios) {
    while ($row = $result_comentarios->fetch_assoc()) {
        $comentarios[] = $row;
    }
}

if ($conn) $conn->close();

// 4. CÁLCULO DE PORCENTAGENS PARA CSAT
$total = $dados['total_avaliacoes'] ?? 0;
$negativas = $dados['negativas'] ?? 0;
$neutras = $dados['neutras'] ?? 0;
$positivas = $dados['positivas'] ?? 0;

$perc_positivas = $total > 0 ? round(($positivas / $total) * 100) : 0;
$perc_neutras = $total > 0 ? round(($neutras / $total) * 100) : 0;
$perc_negativas = $total > 0 ? round(($negativas / $total) * 100) : 0;

// Preparar dados para o gráfico de volume mensal
$meses_completos = [];
foreach ($meses_labels as $mes) {
    $meses_completos[$mes] = 0;
}

foreach ($volume_data as $dado) {
    $mes_abrev = substr($dado['mes_nome'], 0, 3);
    if (isset($meses_completos[$mes_abrev])) {
        $meses_completos[$mes_abrev] = $dado['total'];
    }
}

// Gerar string para conic-gradient dinâmico
$gradient_css = "#22C55E 0% {$perc_positivas}%, #F97316 {$perc_positivas}% " . ($perc_positivas + $perc_neutras) . "%, #EF4444 " . ($perc_positivas + $perc_neutras) . "% 100%";

// Médias das estrelas
$media_atendimento = $dados['media_atendimento'] ?? 0;
$media_ambiente = $dados['media_ambiente'] ?? 0;
$media_qualidade = $dados['media_qualidade'] ?? 0;

// Função para gerar estrelas visuais
function gerarEstrelas($media) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($media)) {
            $html .= '<span class="material-symbols-outlined fill-1 text-yellow-500">star</span>';
        } elseif ($i - 0.5 <= $media) {
            $html .= '<span class="material-symbols-outlined fill-1 text-yellow-500 opacity-50">star</span>';
        } else {
            $html .= '<span class="material-symbols-outlined opacity-30">star</span>';
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html class="light" lang="pt-br">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Painel de Análise - Sistema de Avaliação</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<style type="text/tailwindcss">
    :root {
        --primary: #F97316;
        --background: #F5F6FA;
        --card-light: #FFFFFF;
        --text-main: #1E293B;
        --text-muted: #64748B;
    }
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: var(--background);
        overflow-x: hidden;
    }
    
    /* Comments Sidebar */
    .comments-sidebar {
        position: fixed;
        top: 0;
        right: -100%;
        width: 100%;
        max-width: 380px;
        height: 100vh;
        background: white;
        box-shadow: -5px 0 25px rgba(0,0,0,0.1);
        transition: right 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
    }
    
    .dark .comments-sidebar {
        background: #1E293B;
        border-left: 1px solid #334155;
    }
    
    .comments-sidebar-open {
        right: 0;
    }
    
    /* Backdrop */
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        display: none;
    }
    
    /* Stats Cards */
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .dark .stats-card {
        background: #1E293B;
        border-color: #334155;
    }
    
    /* Donut Chart */
    .donut-chart-container {
        position: relative;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: conic-gradient(
            #22C55E 0% 72%, 
            #F97316 72% 88%, 
            #EF4444 88% 100%
        );
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .donut-chart-inner {
        width: 110px;
        height: 110px;
        background: white;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .dark .donut-chart-inner {
        background: #1E293B;
    }
    
    /* Comment Cards */
    .comment-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }
    
    .dark .comment-card {
        background: #1E293B;
        border-color: #334155;
    }
    
    .comment-rating {
        display: flex;
        gap: 2px;
        margin-bottom: 8px;
    }
    
    .fill-1 { 
        font-variation-settings: 'FILL' 1; 
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#F97316",
                },
                borderRadius: {
                    'xl': "16px",
                    '2xl': "24px",
                },
            },
        },
    };
</script>
</head>
<body class="dark:bg-[#0F172A] text-slate-800 dark:text-slate-100 min-h-screen transition-colors duration-200">
    <!-- Backdrop -->
    <div id="backdrop" class="sidebar-backdrop" onclick="toggleCommentsSidebar()"></div>
    
    <!-- Comments Sidebar -->
    <aside id="commentsSidebar" class="comments-sidebar">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Comentários</h2>
                <button onclick="toggleCommentsSidebar()" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <!-- Comments List -->
            <div id="commentsList" class="space-y-4">
                <?php if (empty($comentarios)): ?>
                    <div class="text-center py-12">
                        <span class="material-symbols-outlined text-slate-300 text-5xl mb-4">chat</span>
                        <p class="text-slate-500 dark:text-slate-400">Nenhum comentário disponível</p>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-2">Os comentários aparecerão aqui automaticamente</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comment-card">
                            <?php 
                            // Calcular média das estrelas se existirem
                            $tem_estrelas = $comentario['nota_atendimento'] || $comentario['nota_ambiente'] || $comentario['nota_qualidade_servico'];
                            if ($tem_estrelas):
                                $notas = [];
                                if ($comentario['nota_atendimento']) $notas[] = $comentario['nota_atendimento'];
                                if ($comentario['nota_ambiente']) $notas[] = $comentario['nota_ambiente'];
                                if ($comentario['nota_qualidade_servico']) $notas[] = $comentario['nota_qualidade_servico'];
                                
                                $nota_media = !empty($notas) ? round(array_sum($notas) / count($notas)) : 0;
                            ?>
                            <div class="comment-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="material-symbols-outlined <?php echo $i <= $nota_media ? 'fill-1 text-yellow-500' : 'text-slate-300'; ?>">star</span>
                                <?php endfor; ?>
                            </div>
                            <?php endif; ?>
                            <p class="text-slate-700 dark:text-slate-300 text-sm mb-2"><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex justify-between">
                                <span>Avaliação: <?php echo $comentario['avaliacao_texto']; ?></span>
                                <span><?php echo $comentario['data_formatada']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="p-4 md:p-6 lg:p-8 min-h-screen">
        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 lg:mb-10">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Painel de Análise</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm md:text-base">Sistema de avaliação de satisfação</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <span class="material-symbols-outlined text-primary text-sm">calendar_month</span>
                    <span class="bg-transparent border-none p-0 text-sm font-semibold text-slate-700 dark:text-slate-200"><?php echo date('d/m/Y'); ?></span>
                </div>
                <!-- Botão de Sair (Porta) -->
                <button onclick="window.location.href='index.php'" 
                        class="p-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl shadow-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors flex items-center gap-2"
                        title="Sair do painel">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="hidden md:inline text-sm font-medium">Sair</span>
                </button>
                <button onclick="toggleCommentsSidebar()" class="p-2.5 bg-primary text-white rounded-xl shadow-lg shadow-orange-500/20 hover:bg-orange-600 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined">chat</span>
                    <span class="hidden md:inline text-sm font-medium">Comentários</span>
                </button>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-8">
            <div class="stats-card">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-900/10 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-500">sentiment_dissatisfied</span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Negativas</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1"><?php echo $negativas; ?></div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Avaliações Negativas</p>
            </div>
            <div class="stats-card">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/10 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-orange-500">sentiment_neutral</span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Neutras</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1"><?php echo $neutras; ?></div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Avaliações Neutras</p>
            </div>
            <div class="stats-card">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 bg-green-50 dark:bg-green-900/10 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-500">sentiment_satisfied</span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Positivas</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1"><?php echo number_format($positivas, 0, ',', '.'); ?></div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Avaliações Positivas</p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 mb-8">
            <div class="lg:col-span-4 bg-white dark:bg-[#1E293B] p-6 lg:p-8 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col items-center">
                <h3 class="w-full text-left font-bold text-slate-500 dark:text-slate-400 mb-6 lg:mb-8 uppercase text-xs tracking-[0.15em]">Satisfação Global</h3>
                <div class="donut-chart-container" style="background: conic-gradient(<?php echo $gradient_css; ?>);">
                    <div class="donut-chart-inner">
                        <span class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $dados['csat'] ?? 0; ?>%</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">CSAT</span>
                    </div>
                </div>
                <div class="mt-6 w-full flex flex-col gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-xs font-bold text-slate-400">Positivas: <span class="text-green-600"><?php echo $perc_positivas; ?>%</span></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                        <span class="text-xs font-bold text-slate-400">Neutras: <span class="text-orange-500"><?php echo $perc_neutras; ?>%</span></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-xs font-bold text-slate-400">Negativas: <span class="text-red-500"><?php echo $perc_negativas; ?>%</span></span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 bg-white dark:bg-[#1E293B] p-6 lg:p-8 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col">
                <div class="flex justify-between items-center mb-6 lg:mb-10">
                    <h3 class="font-bold text-slate-500 dark:text-slate-400 uppercase text-xs tracking-[0.15em]">Volume Mensal</h3>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                            <span class="text-[10px] font-bold text-slate-400">AVALIAÇÕES</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 min-h-[240px] lg:min-h-[280px] relative">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 1000 300">
                        <defs>
                            <linearGradient id="areaGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#F97316" stop-opacity="0.15"></stop>
                                <stop offset="100%" stop-color="#F97316" stop-opacity="0"></stop>
                            </linearGradient>
                        </defs>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="1000" y1="0" y2="0"></line>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="1000" y1="75" y2="75"></line>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="1000" y1="150" y2="150"></line>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="1000" y1="225" y2="225"></line>
                        <line stroke="#f1f5f9" stroke-width="1" x1="0" x2="1000" y1="300" y2="300"></line>
                        <path d="M0,250 Q100,220 200,240 T400,120 T600,180 T800,80 T1000,100 V300 H0 Z" fill="url(#areaGradient)"></path>
                        <path d="M0,250 Q100,220 200,240 T400,120 T600,180 T800,80 T1000,100" fill="none" stroke="#F97316" stroke-linecap="round" stroke-width="3"></path>
                    </svg>
                    <div class="mt-4 flex justify-between text-[10px] font-bold text-slate-400 px-1 uppercase">
                        <span>Jan</span><span>Fev</span><span>Mar</span><span>Abr</span><span>Mai</span><span>Jun</span><span>Jul</span><span>Ago</span><span>Set</span><span>Out</span><span>Nov</span><span>Dez</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ratings Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
            <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Atendimento</p>
                    <span class="text-xs font-mono text-slate-400"><?php echo number_format($media_atendimento, 1); ?>/5.0</span>
                </div>
                <div class="flex gap-1.5 text-yellow-500">
                    <?php echo gerarEstrelas($media_atendimento); ?>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Ambiente</p>
                    <span class="text-xs font-mono text-slate-400"><?php echo number_format($media_ambiente, 1); ?>/5.0</span>
                </div>
                <div class="flex gap-1.5 text-yellow-500">
                    <?php echo gerarEstrelas($media_ambiente); ?>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Qualidade</p>
                    <span class="text-xs font-mono text-slate-400"><?php echo number_format($media_qualidade, 1); ?>/5.0</span>
                </div>
                <div class="flex gap-1.5 text-yellow-500">
                    <?php echo gerarEstrelas($media_qualidade); ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Floating Button - Apenas botão de comentários -->
    <div class="fixed bottom-6 right-6 flex flex-col gap-3">
        <button class="p-3 bg-primary text-white rounded-full shadow-lg shadow-orange-500/30 hover:scale-110 transition-transform" onclick="toggleCommentsSidebar()">
            <span class="material-symbols-outlined">chat</span>
        </button>
    </div>

    <script>
        // Toggle Comments Sidebar
        function toggleCommentsSidebar() {
            const sidebar = document.getElementById('commentsSidebar');
            const backdrop = document.getElementById('backdrop');
            
            sidebar.classList.toggle('comments-sidebar-open');
            
            if (sidebar.classList.contains('comments-sidebar-open')) {
                backdrop.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } else {
                backdrop.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // Close sidebar with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('commentsSidebar');
                if (sidebar.classList.contains('comments-sidebar-open')) {
                    toggleCommentsSidebar();
                }
            }
        });
        
        // Close sidebar when clicking on backdrop
        document.getElementById('backdrop').addEventListener('click', toggleCommentsSidebar);
    </script>
</body>
</html>