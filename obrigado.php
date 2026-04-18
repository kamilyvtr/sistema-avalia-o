<?php
session_start();
require_once 'config.php';

// Verificar se veio da tela 2 (tem emoção na sessão)
if (!isset($_SESSION['avaliacao']['emocao_banco'])) {
    header('Location: tela1.php');
    exit();
}
$mensagem_erro = '';
$avaliacao_salva = false;

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario = isset($_POST['comentario']) ? sanitize($_POST['comentario']) : null;
    
    // Conectar ao banco e salvar tudo
    $conn = getConnection();
    
    if ($conn) {
        // CORRIGIDO: nota_experiencia é STRING, não inteiro
        $nota_experiencia = $_SESSION['avaliacao']['emocao_banco']; // Já está no formato correto: 'MUITO_RUIM', etc.
        
        // Estrelas (se existirem) - CONVERTER para INT e validar
        $nota_atendimento = isset($_SESSION['avaliacao']['estrelas']['atendimento']) ? 
            validarNotaEstrelas($_SESSION['avaliacao']['estrelas']['atendimento']) : null;
        
        $nota_ambiente = isset($_SESSION['avaliacao']['estrelas']['ambiente']) ? 
            validarNotaEstrelas($_SESSION['avaliacao']['estrelas']['ambiente']) : null;
        
        $nota_qualidade_servico = isset($_SESSION['avaliacao']['estrelas']['qualidade']) ? 
            validarNotaEstrelas($_SESSION['avaliacao']['estrelas']['qualidade']) : null;
        
        try {
            // Inserir no banco
            $sql = "INSERT INTO avaliacoes 
                    (nota_experiencia, nota_atendimento, nota_ambiente, nota_qualidade_servico, comentario, data_avaliacao) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                // CORRIGIDO: "siiis" porque nota_experiencia é string, as outras são inteiros ou NULL
                // Precisamos lidar com NULLs
                if ($nota_atendimento === null) $nota_atendimento = 0;
                if ($nota_ambiente === null) $nota_ambiente = 0;
                if ($nota_qualidade_servico === null) $nota_qualidade_servico = 0;
                
                $stmt->bind_param(
                    "siiis",
                    $nota_experiencia,
                    $nota_atendimento,
                    $nota_ambiente,
                    $nota_qualidade_servico,
                    $comentario
                );
                
                if ($stmt->execute()) {
                    // Limpar sessão
                    unset($_SESSION['avaliacao']);
                    
                    // Marcar que é uma nova avaliação
                    iniciarNovaAvaliacao();
                    
                    // Redirecionar para SI MESMO com parâmetro de sucesso
                    redirect('obrigado.php?salvo=1');
                    exit;
                } else {
                    $mensagem_erro = "Erro ao salvar avaliação: " . $conn->error;
                }
                
                $stmt->close();
            } else {
                $mensagem_erro = "Erro ao preparar query: " . $conn->error;
            }
        } catch (Exception $e) {
            $mensagem_erro = "Erro no sistema: " . $e->getMessage();
        }
        
        $conn->close();
    } else {
        $mensagem_erro = "Erro ao conectar ao banco de dados.";
    }
}

// Verificar se a avaliação já foi salva (vem do parâmetro na URL)
if (isset($_GET['salvo']) && $_GET['salvo'] == 1) {
    $avaliacao_salva = true;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Obrigado - Sistema de Pesquisa</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#F87171",
                    orange: {
                        400: "#fb923c",
                        500: "#f97316",
                    },
                    indigo: {
                        950: "#1e1b4b",
                    },
                },
                fontFamily: {
                    display: ["Plus Jakarta Sans", "sans-serif"],
                    sans: ["Plus Jakarta Sans", "sans-serif"],
                },
                borderRadius: {
                    DEFAULT: "1rem",
                },
            },
        },
    };
</script>
<style type="text/tailwindcss">
    :root {
        --deep-indigo: #1e1b4b;
        --soft-coral: #F87171;
        --primary-orange: #fb923c;
        --bg-white: #ffffff;
        --bg-off-white: #fcfcfc;
    }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: linear-gradient(180deg, var(--bg-white) 0%, var(--bg-off-white) 100%);
        background-attachment: fixed;
    }
    .dark body {
        background: radial-gradient(at top left, #1e1b4b, transparent),
                    radial-gradient(at bottom right, #0f172a, transparent);
        background-color: #020617;
    }
    .glass-panel {
        background: #ffffff;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.04);
    }
    .dark .glass-panel {
        background: rgba(30, 27, 75, 0.4);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .custom-gradient-orange {
        background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
    }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;
    }
    .icon-outline {
        font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
    }
</style>
</head>
<body class="text-indigo-950 dark:text-slate-100 min-h-screen flex flex-col items-center justify-center p-4">
<div class="w-full max-w-lg mb-8 px-4">
<div class="flex justify-center gap-2 mb-4">
<div class="h-1.5 w-full rounded-full bg-orange-500"></div>
<div class="h-1.5 w-full rounded-full bg-orange-500"></div>
<div class="h-1.5 w-full rounded-full bg-orange-500"></div>
<div class="h-1.5 w-full rounded-full bg-orange-500"></div>
</div>
</div>
<main class="w-full max-w-lg glass-panel rounded-[2.5rem] p-8 md:p-14 border border-slate-100 dark:border-indigo-500/10">

<?php if ($avaliacao_salva): ?>
    <!-- TELA DE AGRADECIMENTO (APÓS SALVAR) -->
    <header class="text-center mb-10">
        <div class="w-24 h-24 bg-orange-50 dark:bg-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-8">
            <span class="material-symbols-outlined text-orange-500 text-6xl">celebration</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-indigo-950 dark:text-white mb-4">
            Avaliação Salva!
        </h1>
        <p class="text-slate-500 dark:text-indigo-200/60 text-lg leading-relaxed">
            Sua avaliação foi registrada com sucesso. Muito obrigado!
        </p>
    </header>
    
    <div class="space-y-4">
        <button onclick="iniciarNovaAvaliacao()" class="w-full py-5 px-6 custom-gradient-orange hover:brightness-105 text-white font-bold rounded-2xl shadow-xl shadow-orange-500/20 transition-all duration-300 active:scale-[0.98] text-lg flex items-center justify-center gap-3">
            Nova Avaliação
            <span class="material-symbols-outlined icon-outline text-2xl">rate_review</span>
        </button>
        
        <button onclick="window.location.href='tela1.php'" class="w-full py-5 px-6 bg-slate-100 dark:bg-indigo-900/30 hover:bg-slate-200 dark:hover:bg-indigo-800/30 text-slate-700 dark:text-slate-300 font-bold rounded-2xl border border-slate-200 dark:border-indigo-700/30 transition-all duration-300 active:scale-[0.98] text-lg flex items-center justify-center gap-3">
            <span class="material-symbols-outlined icon-outline text-2xl">arrow_back</span>
            Voltar ao Início
        </button>
    </div>

<?php else: ?>
    <!-- FORMULÁRIO DE COMENTÁRIO FINAL -->
    <form method="POST" action="">
        <header class="text-center mb-10">
            <div class="w-24 h-24 bg-orange-50 dark:bg-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-8">
                <span class="material-symbols-outlined text-orange-500 text-6xl">check_circle</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-indigo-950 dark:text-white mb-4">
                Obrigado!
            </h1>
            <p class="text-slate-500 dark:text-indigo-200/60 text-lg leading-relaxed">
                Sua opinião é fundamental para melhorarmos nossos serviços cada vez mais.
            </p>
        </header>
        
        <!-- Exibir mensagem de erro se houver -->
        <?php if ($mensagem_erro): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                <strong class="text-red-700 dark:text-red-300">Erro:</strong>
                <span class="text-red-600 dark:text-red-400"><?php echo htmlspecialchars($mensagem_erro); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="space-y-6 mb-10">
            <div class="flex flex-col">
                <label class="text-indigo-950 dark:text-indigo-100 font-semibold mb-3 text-sm" for="comments">
                    Algum comentário adicional? (Opcional)
                </label>
                <textarea class="w-full rounded-2xl border-slate-100 dark:border-indigo-900/50 bg-slate-50/50 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-100 focus:ring-orange-400 focus:border-orange-400 transition-all placeholder:text-slate-400 dark:placeholder:text-indigo-300/30 p-4 resize-none" id="comments" name="comentario" placeholder="Escreva aqui suas sugestões ou elogios..." rows="4"></textarea>
            </div>
        </div>
        
        <div class="space-y-4">
            <button type="submit" class="w-full py-5 px-6 custom-gradient-orange hover:brightness-105 text-white font-bold rounded-2xl shadow-xl shadow-orange-500/20 transition-all duration-300 active:scale-[0.98] text-lg flex items-center justify-center gap-3">
                Finalizar
                <span class="material-symbols-outlined icon-outline text-2xl">check</span>
            </button>
        </div>
    </form>
<?php endif; ?>

</main>
<footer class="mt-12 text-center">
<p class="text-slate-400 dark:text-indigo-200/20 text-sm font-medium tracking-wide">
    PESQUISA CONCLUÍDA • 100% FINALIZADO
</p>
</footer>


</body>
</html>

<script>
// Função para iniciar nova avaliação
function iniciarNovaAvaliacao() {
    // Primeiro, vamos para o estado do index (sem navegar)
    history.replaceState({ page: "index", novaAvaliacao: true }, "", "index.php");
    
    // Agora navegamos para tela1.php
    window.location.href = "tela1.php";
}

// Configurar histórico quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Se a avaliação já foi salva, manipulamos o histórico
    <?php if ($avaliacao_salva): ?>
        // Já foi salva - substituir todo o histórico
        history.replaceState({ page: "index" }, "", "index.php");
        history.pushState({ page: "obrigado", saved: true }, "", "obrigado.php?salvo=1");
    <?php else: ?>
        // Se ainda não salvou, configuramos o histórico
        history.replaceState({ page: "index" }, "", "index.php");
        history.pushState({ page: "obrigado" }, "", "obrigado.php");
    <?php endif; ?>
    
    // Listener para quando o usuário clicar em "Voltar"
    window.addEventListener('popstate', function(event) {
        // Se tentar voltar do obrigado, vamos para o index
        if (event.state && event.state.page !== "index") {
            history.replaceState({ page: "index" }, "", "index.php");
            window.location.href = "index.php";
        }
    });
});
</script>