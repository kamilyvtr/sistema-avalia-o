<?php
session_start();

// Se veio do index com nova avaliação
$isNovaAvaliacao = isset($_GET['nova']) ? true : false;

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emocao'])) {
    // Salvar na sessão
    $_SESSION['avaliacao'] = [
        'emocao' => $_POST['emocao'],
        'emocao_banco' => $_POST['emocao']
    ];
    
    // Redirecionar para tela2.php
    header('Location: tela2.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sistema de Avaliação - Pesquisa</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0.1&amp;display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#FF7E67",
                    "background-light": "#FAFAFA",
                    "background-dark": "#0F172A",
                    "card-light": "#FFFFFF",
                    "card-dark": "#1E293B",
                    "teal-accent": "#2DD4BF"
                },
                fontFamily: {
                    display: ["Plus Jakarta Sans", "sans-serif"],
                },
                borderRadius: {
                    DEFAULT: "1.5rem",
                },
            },
        },
    };
</script>
<style type="text/tailwindcss">
    .mesh-gradient {
        background-color: #ffffff;
        background-image: 
            radial-gradient(at 0% 0%, rgba(226, 232, 240, 0.4) 0, transparent 40%), 
            radial-gradient(at 100% 0%, rgba(226, 232, 240, 0.4) 0, transparent 40%),
            radial-gradient(at 50% 100%, rgba(226, 232, 240, 0.3) 0, transparent 50%);
    }
    .material-symbols-rounded {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 48;
    }
    .rating-icon {
        @apply w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-2xl flex items-center justify-center transition-all duration-300 cursor-pointer hover:scale-110 active:scale-95;
    }   
    .rating-icon .material-symbols-rounded {
        font-size: 2.5rem;
    }
    @media (min-width: 640px) {
        .rating-icon .material-symbols-rounded {
            font-size: 3rem;
        }
    }
    @media (min-width: 768px) {
        .rating-icon .material-symbols-rounded {
            font-size: 3.5rem;
        }
    }
    
    .rating-selected {
        transform: scale(1.1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .dark .rating-selected {
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    }
    
    .btn-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-disabled:hover {
        transform: none !important;
        background-color: #FF7E67 !important;
    }
    
    .emotion-text {
        transition: all 0.3s ease;
    }
    
    .icon-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .rating-grid {
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .rating-grid {
            gap: 1rem;
        }
    }
    @media (min-width: 768px) {
        .rating-grid {
            gap: 1.5rem;
        }
    }
    
    .emotion-label {
        font-size: 0.875rem;
        font-weight: 700;
    }
    @media (min-width: 640px) {
        .emotion-label {
            font-size: 0.875rem;
        }
    }
    @media (min-width: 768px) {
        .emotion-label {
            font-size: 0.9375rem;
        }
    }
    
    .selected-feedback {
        display: none;
        margin-top: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #FF7E67;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4 md:p-8 mesh-gradient transition-colors duration-300">

</div>
<main class="w-full max-w-4xl bg-white dark:bg-slate-900 rounded-[3rem] shadow-[0_30px_60px_rgba(0,0,0,0.04)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden border border-slate-50 dark:border-slate-800 transition-colors duration-300">
<div class="px-4 sm:px-6 md:px-8 lg:px-24 py-8 sm:py-12 md:py-16 lg:py-24 text-center">
<header class="mb-8 sm:mb-10 md:mb-12 lg:mb-14 flex flex-col items-center">
<div class="w-16 h-16 sm:w-18 sm:h-18 md:w-20 md:h-20 bg-orange-50 dark:bg-orange-950/30 rounded-full flex items-center justify-center mb-6 sm:mb-8 border border-orange-100/50 dark:border-orange-900/30">
<span class="material-symbols-rounded text-primary text-4xl sm:text-5xl">sentiment_satisfied</span>
</div>
<h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-3 sm:mb-4">
                    Como foi sua experiência?
                </h1>

</header>

<!-- FORMULÁRIO PHP -->
<form method="POST" action="" id="form-avaliacao">
<div class="flex flex-wrap justify-center rating-grid mb-12 sm:mb-14 md:mb-16">
<!-- Muito Ruim -->
<div class="icon-container group flex flex-col items-center gap-2 sm:gap-3">
<button type="button" class="rating-icon bg-red-50 dark:bg-red-950/20 text-red-400 group-hover:bg-red-100 group-hover:text-red-500" 
        data-emotion="Muito Ruim"
        onclick="selectRating(this, 'Muito Ruim')">
<span class="material-symbols-rounded">sentiment_extremely_dissatisfied</span>
</button>
<span class="emotion-label text-slate-400 uppercase tracking-widest transition-opacity duration-300">Muito Ruim</span>
<div class="selected-feedback" id="feedback-muito-ruim">✓ Selecionado</div>
</div>

<!-- Ruim -->
<div class="icon-container group flex flex-col items-center gap-2 sm:gap-3">
<button type="button" class="rating-icon bg-orange-50 dark:bg-orange-950/20 text-orange-400 group-hover:bg-orange-100 group-hover:text-orange-500" 
        data-emotion="Ruim"
        onclick="selectRating(this, 'Ruim')">
<span class="material-symbols-rounded">sentiment_dissatisfied</span>
</button>
<span class="emotion-label text-slate-400 uppercase tracking-widest transition-opacity duration-300">Ruim</span>
<div class="selected-feedback" id="feedback-ruim">✓ Selecionado</div>
</div>

<!-- Regular -->
<div class="icon-container group flex flex-col items-center gap-2 sm:gap-3">
<button type="button" class="rating-icon bg-yellow-50 dark:bg-yellow-950/20 text-yellow-400 group-hover:bg-yellow-100 group-hover:text-yellow-500" 
        data-emotion="Regular"
        onclick="selectRating(this, 'Regular')">
<span class="material-symbols-rounded">sentiment_neutral</span>
</button>
<span class="emotion-label text-slate-400 uppercase tracking-widest transition-opacity duration-300">Regular</span>
<div class="selected-feedback" id="feedback-regular">✓ Selecionado</div>
</div>

<!-- Bom -->
<div class="icon-container group flex flex-col items-center gap-2 sm:gap-3">
<button type="button" class="rating-icon bg-emerald-50 dark:bg-emerald-950/20 text-emerald-400 group-hover:bg-emerald-100 group-hover:text-emerald-500" 
        data-emotion="Bom"
        onclick="selectRating(this, 'Bom')">
<span class="material-symbols-rounded">sentiment_satisfied</span>
</button>
<span class="emotion-label text-slate-400 uppercase tracking-widest transition-opacity duration-300">Bom</span>
<div class="selected-feedback" id="feedback-bom">✓ Selecionado</div>
</div>

<!-- Excelente -->
<div class="icon-container group flex flex-col items-center gap-2 sm:gap-3">
<button type="button" class="rating-icon bg-teal-50 dark:bg-teal-950/20 text-teal-400 group-hover:bg-teal-100 group-hover:text-teal-500" 
        data-emotion="Excelente"
        onclick="selectRating(this, 'Excelente')">
<span class="material-symbols-rounded">sentiment_very_satisfied</span>
</button>
<span class="emotion-label text-slate-400 uppercase tracking-widest transition-opacity duration-300">Excelente</span>
<div class="selected-feedback" id="feedback-excelente">✓ Selecionado</div>
</div>
</div>

<!-- Campo oculto para enviar a emoção -->
<input type="hidden" name="emocao" id="input-emocao">

<!-- Área de exibição da seleção atual -->
<div class="mb-8 sm:mb-10 md:mb-12 text-center">
    <div id="selected-emotion-display" class="hidden">
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mb-2">Sua avaliação:</p>
        <p id="selected-emotion-text" class="text-lg sm:text-xl font-bold text-primary"></p>
    </div>
</div>

<div class="flex justify-center mb-12 sm:mb-14 md:mb-16">
<button type="submit" id="next-button" class="w-full md:w-auto min-w-[200px] sm:min-w-[240px] px-8 sm:px-12 py-4 sm:py-5 bg-primary text-white font-bold text-base sm:text-lg rounded-full shadow-lg shadow-orange-500/20 hover:bg-orange-600 hover:shadow-orange-500/40 transform transition-all duration-300 hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 sm:gap-3 btn-disabled">
                    Próximo
                    <span class="material-symbols-rounded">arrow_forward</span>
</button>
</div>
</form>

<div class="text-center mt-8">
    <a href="index.php" class="inline-flex items-center gap-2 text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-primary transition-colors">
        <span class="material-symbols-rounded">arrow_back</span>
        Voltar ao Início
    </a>
</div>

</div>
</main>

<script>
let selectedEmotion = null;

function selectRating(button, emotionName) {
    document.querySelectorAll('.rating-icon').forEach(btn => {
        btn.classList.remove('rating-selected');
    });
    
    button.classList.add('rating-selected');
    selectedEmotion = emotionName;
    document.getElementById('input-emocao').value = emotionName;
    
    document.querySelectorAll('.selected-feedback').forEach(feedback => {
        feedback.style.display = 'none';
    });
    
    const emotionId = emotionName.toLowerCase().replace(' ', '-');
    const feedbackElement = document.getElementById(`feedback-${emotionId}`);
    if (feedbackElement) {
        feedbackElement.style.display = 'block';
    }
    
    const displayElement = document.getElementById('selected-emotion-display');
    const emotionTextElement = document.getElementById('selected-emotion-text');
    
    displayElement.classList.remove('hidden');
    emotionTextElement.textContent = emotionName;
    
    const nextButton = document.getElementById('next-button');
    nextButton.classList.remove('btn-disabled');
    nextButton.disabled = false;
}

document.addEventListener('DOMContentLoaded', function() {
    const nextButton = document.getElementById('next-button');
    nextButton.classList.add('btn-disabled');
    nextButton.disabled = true;
    
    const ratingIcons = document.querySelectorAll('.rating-icon');
    ratingIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            if (!this.classList.contains('rating-selected')) {
                this.style.transform = 'scale(1.1)';
            }
        });
        
        icon.addEventListener('mouseleave', function() {
            if (!this.classList.contains('rating-selected')) {
                this.style.transform = 'scale(1)';
            }
        });
    });
});
</script>
</body>
</html>