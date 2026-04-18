<?php
session_start();
require_once 'config.php';

// Verificar se veio da tela 1 (tem emoção na sessão)
if (!isset($_SESSION['avaliacao']['emocao_banco'])) {
    header('Location: tela1.php');
    exit();
}

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pular'])) {
        // Usuário pulou - salvar null nas estrelas
        $_SESSION['avaliacao']['estrelas'] = null;
    } else {
        // Salvar estrelas se fornecidas
        $_SESSION['avaliacao']['estrelas'] = [
            'atendimento' => isset($_POST['atendimento']) ? (int)$_POST['atendimento'] : null,
            'ambiente' => isset($_POST['ambiente']) ? (int)$_POST['ambiente'] : null,
            'qualidade' => isset($_POST['qualidade']) ? (int)$_POST['qualidade'] : null
        ];
    }
    
    // REDIRECIONAR CORRETAMENTE
    header('Location: obrigado.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Avaliação Detalhada - Sistema de Pesquisa</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#fb923c",
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
        --primary-orange: #fb923c;
        --secondary-orange: #f97316;
        --preview-orange: rgba(251, 146, 60, 0.8);
    }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #fcfcfc;
        background-image: 
            radial-gradient(at 0% 0%, hsla(25,100%,96%,0.5) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(0,0%,98%,1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(25,100%,96%,0.5) 0px, transparent 50%),
            radial-gradient(at 0% 100%, hsla(220,30%,98%,1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    .dark body {
        background-color: #0f172a;
        background-image: 
            radial-gradient(at 0% 0%, hsla(25,30%,10%,0.5) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(220,30%,10%,0.5) 0px, transparent 50%);
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.035);
    }
    .dark .glass-panel {
        background: rgba(30, 41, 59, 0.8);
        border-color: rgba(255,255,255,0.05);
    }
    .custom-gradient-orange {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
    }
    .star-rating button {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 48;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .star-active .material-symbols-outlined {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;
    }
    .star-selected {
        color: var(--primary-orange) !important;
    }
    
    .star-preview .material-symbols-outlined {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;
        color: var(--preview-orange) !important;
        transform: scale(1.08);
        filter: drop-shadow(0 2px 4px rgba(251, 146, 60, 0.3));
    }
    
    .star-unselected .material-symbols-outlined {
        color: #cbd5e1 !important;
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 48;
    }
    
    .dark .star-unselected .material-symbols-outlined {
        color: #64748b !important;
    }
    
    /* Efeitos de animação */
    @keyframes softPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .star-hover {
        animation: softPulse 1.5s ease-in-out infinite;
    }
    
    /* Feedback visual para seleção */
    .selection-confirmed {
        position: relative;
    }
    
    .selection-confirmed::after {
        content: '';
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        border: 2px solid var(--primary-orange);
        border-radius: 8px;
        opacity: 0;
        animation: fadeInBorder 0.3s ease-out forwards;
    }
    
    @keyframes fadeInBorder {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    /* Estrelas selecionadas com mais destaque */
    .star-selected .material-symbols-outlined {
        filter: drop-shadow(0 4px 6px rgba(251, 146, 60, 0.4));
        animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
        from {
            filter: drop-shadow(0 4px 6px rgba(251, 146, 60, 0.4));
        }
        to {
            filter: drop-shadow(0 4px 12px rgba(251, 146, 60, 0.6));
        }
    }
</style>
</head>
<body class="text-indigo-950 dark:text-slate-100 min-h-screen flex flex-col items-center justify-center p-4">
<div class="w-full max-w-lg mb-8 px-4">
    <div class="flex justify-center gap-3 mb-4">
        <div class="h-1.5 w-16 rounded-full bg-[var(--primary-orange)]"></div>
        <div class="h-1.5 w-16 rounded-full bg-[var(--primary-orange)]"></div>
        <div class="h-1.5 w-16 rounded-full bg-slate-200 dark:bg-slate-700"></div>
    </div>
</div>

<main class="w-full max-w-lg glass-panel rounded-[2rem] p-6 sm:p-8 md:p-14 border border-white dark:border-slate-700">
    <!-- FORMULÁRIO PHP -->
    <form method="POST" action="">
        <header class="text-center mb-8 sm:mb-10 md:mb-12">
            <h1 class="text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight text-indigo-950 dark:text-white mb-3">
                Avalie alguns pontos
            </h1>
            <p class="text-sm sm:text-base text-slate-400 dark:text-slate-400">
                Dê uma nota para cada categoria abaixo.
            </p>
        </header>
        
        <div class="space-y-10 sm:space-y-12 mb-10 sm:mb-14">
            <!-- Atendimento -->
            <div class="flex flex-col items-center">
                <h3 class="text-indigo-950 dark:text-slate-200 font-semibold mb-4 sm:mb-5 text-base sm:text-lg">Atendimento</h3>
                <div class="star-rating flex gap-1 sm:gap-2" id="atendimento-stars" data-category="atendimento">
                    <button type="button" class="star-btn star-unselected" data-index="0" data-value="1">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="1" data-value="2">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="2" data-value="3">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="3" data-value="4">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="4" data-value="5">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                </div>
                <input type="hidden" name="atendimento" id="input-atendimento" value="">
            </div>
            
            <!-- Ambiente -->
            <div class="flex flex-col items-center">
                <h3 class="text-indigo-950 dark:text-slate-200 font-semibold mb-4 sm:mb-5 text-base sm:text-lg">Ambiente</h3>
                <div class="star-rating flex gap-1 sm:gap-2" id="ambiente-stars" data-category="ambiente">
                    <button type="button" class="star-btn star-unselected" data-index="0" data-value="1">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="1" data-value="2">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="2" data-value="3">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="3" data-value="4">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="4" data-value="5">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                </div>
                <input type="hidden" name="ambiente" id="input-ambiente" value="">
            </div>
            
            <!-- Qualidade do serviço -->
            <div class="flex flex-col items-center">
                <h3 class="text-indigo-950 dark:text-slate-200 font-semibold mb-4 sm:mb-5 text-base sm:text-lg">Qualidade do serviço</h3>
                <div class="star-rating flex gap-1 sm:gap-2" id="qualidade-stars" data-category="qualidade">
                    <button type="button" class="star-btn star-unselected" data-index="0" data-value="1">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="1" data-value="2">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="2" data-value="3">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="3" data-value="4">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                    <button type="button" class="star-btn star-unselected" data-index="4" data-value="5">
                        <span class="material-symbols-outlined text-3xl sm:text-4xl">star</span>
                    </button>
                </div>
                <input type="hidden" name="qualidade" id="input-qualidade" value="">
            </div>
        </div>
        
        <div class="flex flex-col items-center space-y-4 sm:space-y-6">
            <button type="submit" class="w-full py-4 px-6 custom-gradient-orange hover:opacity-90 text-white font-bold rounded-xl shadow-lg shadow-orange-500/10 transition-all duration-300 active:scale-[0.98] text-base">
                Próximo
            </button>
            <button type="submit" name="pular" value="1" class="text-slate-400 dark:text-slate-500 hover:text-orange-500 dark:hover:text-orange-400 font-medium transition-colors text-sm underline underline-offset-4 decoration-slate-200 dark:decoration-slate-700">
                Pular esta etapa
            </button>
        </div>
    </form>
    <!-- FIM DO FORMULÁRIO -->
</main>

<footer class="mt-8 sm:mt-12 text-center">
    <button type="button" onclick="window.location.href='tela1.php'" class="py-2 px-6 text-slate-400 dark:text-slate-500 hover:text-indigo-950 dark:hover:text-white font-medium transition-colors text-sm mb-4">
        Voltar para etapa anterior
    </button>
    <p class="text-slate-400/60 dark:text-slate-600 text-xs sm:text-[13px]">
        Etapa 2 de 3 • Seu progresso é salvo automaticamente
    </p>
</footer>



<script>
// Sistema de seleção e preview de estrelas
class StarRatingSystem {
    constructor() {
        this.currentSelections = {
            atendimento: 0,
            ambiente: 0,
            qualidade: 0
        };
        this.init();
    }
    
    // Inicializa todos os grupos de estrelas
    init() {
        const categories = ['atendimento', 'ambiente', 'qualidade'];
        
        categories.forEach(category => {
            const container = document.getElementById(`${category}-stars`);
            if (!container) return;
            
            const stars = container.querySelectorAll('.star-btn');
            
            // Garante que todas as estrelas começam vazias
            stars.forEach(star => {
                star.classList.remove('star-active', 'star-selected', 'star-preview', 'selection-confirmed');
                star.classList.add('star-unselected');
                const icon = star.querySelector('.material-symbols-outlined');
                if (icon) {
                    icon.style.fontVariationSettings = "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 48";
                }
            });
            
            // Adiciona eventos para cada estrela
            stars.forEach((star, index) => {
                // Eventos de mouse (desktop)
                star.addEventListener('mouseenter', () => this.showPreview(category, index));
                star.addEventListener('click', () => this.selectStar(category, index));
                
                // Eventos de toque (mobile)
                star.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    this.showPreview(category, index);
                });
                
                star.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    const touch = e.touches[0];
                    const starUnderTouch = document.elementFromPoint(touch.clientX, touch.clientY);
                    if (starUnderTouch && starUnderTouch.classList.contains('star-btn')) {
                        const touchIndex = parseInt(starUnderTouch.dataset.index);
                        if (!isNaN(touchIndex)) {
                            this.showPreview(category, touchIndex);
                        }
                    }
                });
                
                star.addEventListener('touchend', (e) => {
                    e.preventDefault();
                    this.selectStar(category, index);
                });
            });
            
            // Remove preview quando o mouse sai do container
            container.addEventListener('mouseleave', () => this.clearPreview(category));
        });
    }
    
    // Mostra preview das estrelas
    showPreview(category, hoverIndex) {
        const container = document.getElementById(`${category}-stars`);
        if (!container) return;
        
        const stars = container.querySelectorAll('.star-btn');
        
        // Remove classes de preview anteriores
        stars.forEach(star => {
            star.classList.remove('star-preview');
        });
        
        // Adiciona preview até o índice do hover
        for (let i = 0; i <= hoverIndex; i++) {
            if (stars[i]) {
                stars[i].classList.add('star-preview');
                stars[i].classList.remove('star-unselected');
            }
        }
    }
    
    // Remove o preview
    clearPreview(category) {
        const container = document.getElementById(`${category}-stars`);
        if (!container) return;
        
        const stars = container.querySelectorAll('.star-btn');
        
        // Remove todas as classes de preview
        stars.forEach(star => {
            star.classList.remove('star-preview');
            
            // Se não estiver selecionada, volta a ser "unselected"
            if (!star.classList.contains('star-selected')) {
                star.classList.add('star-unselected');
            }
        });
        
        // Restaura a seleção atual se existir
        if (this.currentSelections[category] > 0) {
            this.restoreSelection(category);
        }
    }
    
    // Seleciona uma estrela permanentemente
    selectStar(category, selectedIndex) {
        // Índice + 1 porque as estrelas vão de 1-5 no banco
        const valorEstrela = selectedIndex + 1;
        this.currentSelections[category] = valorEstrela;
        const container = document.getElementById(`${category}-stars`);
        
        if (!container) return;
        
        const stars = container.querySelectorAll('.star-btn');
        
        // Remove todas as classes
        stars.forEach(star => {
            star.classList.remove('star-active', 'star-selected', 'star-preview', 'star-unselected', 'selection-confirmed');
        });
        
        // Adiciona classes de seleção até o índice escolhido
        for (let i = 0; i <= selectedIndex; i++) {
            if (stars[i]) {
                stars[i].classList.add('star-active', 'star-selected', 'selection-confirmed');
                
                setTimeout(() => {
                    stars[i].classList.remove('selection-confirmed');
                }, 300);
            }
        }

        // Atualiza o campo oculto correspondente (valor de 1-5)
        document.getElementById(`input-${category}`).value = valorEstrela;
    }
    
    // Restaura a seleção anterior
    restoreSelection(category) {
        const selectedIndex = this.currentSelections[category] - 1; // -1 porque no display é 0-4
        if (selectedIndex >= 0) {
            const container = document.getElementById(`${category}-stars`);
            if (!container) return;
            
            const stars = container.querySelectorAll('.star-btn');
            
            // Remove todas as classes
            stars.forEach(star => {
                star.classList.remove('star-active', 'star-selected', 'star-preview', 'star-unselected');
            });
            
            // Restaura a seleção
            for (let i = 0; i <= selectedIndex; i++) {
                if (stars[i]) {
                    stars[i].classList.add('star-active', 'star-selected');
                }
            }
        }
    }
}

// Inicializa o sistema de avaliação por estrelas
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa o sistema
    const starSystem = new StarRatingSystem();
    
    // Melhora a acessibilidade com teclado
    document.querySelectorAll('.star-btn').forEach(star => {
        star.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const container = star.closest('.star-rating');
                if (container) {
                    const category = container.dataset.category;
                    const index = parseInt(star.dataset.index);
                    starSystem.selectStar(category, index);
                }
            }
        });
    });
    
    // Garantia extra: força a visibilidade das estrelas não selecionadas
    setTimeout(() => {
        document.querySelectorAll('.star-btn').forEach(star => {
            if (!star.classList.contains('star-selected')) {
                star.classList.add('star-unselected');
            }
        });
    }, 100);
});
</script>
</body>
</html>