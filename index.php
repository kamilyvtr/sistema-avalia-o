<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sistema de Avaliação - Home</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#E76F51", // Keeping the coral from the logo as primary
                        "background-light": "#F9FAFB",
                        "background-dark": "#0F172A",
                        coral: {
                            50: "#FFF5F2",
                            100: "#FFEBE5",
                            500: "#E76F51",
                        },
                        mint: {
                            50: "#F0FDF4",
                            100: "#DCFCE7",
                            500: "#10B981",
                        }
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                        sans: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "1rem",
                        '2xl': "1.5rem",
                    },
                },
            },
        };
    </script>
<style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .glass-card {
            backdrop-filter: blur(8px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col transition-colors duration-300">
<div class="fixed top-6 right-6">
</div>
<main class="flex-grow flex items-center justify-center px-4 py-12">
<div class="max-w-4xl w-full text-center">
<div class="mb-16 animate-fade-in-down">
<div class="">
<span class="material-icons-round text-primary text-5xl">analytics</span>

<!-- conteudo dos textos, HTML e dos ícones -->
</div>
<h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
                    Sistema de Avaliação
                </h1>
<p class="text-lg md:text-xl text-slate-500 dark:text-slate-400 font-medium">
                    Escolha uma opção para continuar
                </p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
<a class="glass-card group flex flex-col p-8 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl text-left" href="tela1.php">

<div class="flex items-center justify-between mb-8">
<div class="w-16 h-16 bg-coral-50 dark:bg-coral-500/10 rounded-2xl flex items-center justify-center transition-colors group-hover:bg-coral-100 dark:group-hover:bg-coral-500/20">
<span class="material-icons-round text-primary text-3xl">rate_review</span>
</div>
<span class="material-icons-round text-slate-300 group-hover:text-primary transition-colors transform group-hover:translate-x-1">arrow_forward_ios</span>
</div>
<div>
<h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                            Avaliar Experiência
                        </h3>
<p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                            Deixe sua avaliação sobre o atendimento e nos ajude a melhorar constantemente.
                        </p>
</div>
</a>
<a class="glass-card group flex flex-col p-8 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl text-left" href="dashboard.php">

<div class="flex items-center justify-between mb-8">
<div class="w-16 h-16 bg-mint-50 dark:bg-mint-500/10 rounded-2xl flex items-center justify-center transition-colors group-hover:bg-mint-100 dark:group-hover:bg-mint-500/20">
<span class="material-icons-round text-mint-500 text-3xl">insights</span>
</div>
<span class="material-icons-round text-slate-300 group-hover:text-mint-500 transition-colors transform group-hover:translate-x-1">arrow_forward_ios</span>
</div>
<div>
<h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                            Área Administrativa
                        </h3>
<p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                            Acesse o painel de controle para visualizar estatísticas, relatórios e feedbacks detalhados.
                        </p>
</div>
</a>
</div>
</div>
</main>
<footer class="py-12 text-center">
<div class="max-w-4xl mx-auto px-4">
<div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 dark:via-slate-700 to-transparent mb-8"></div>
<p class="text-sm text-slate-400 dark:text-slate-500 flex items-center justify-center gap-2">
<span class="material-icons-round text-base">verified_user</span>
           Projeto desenvolvido no SENAI, com atenção à qualidade e usabilidade.
            </p>
</div>
</footer>
<style>
        @keyframes fade-in-down {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-down {
            animation: fade-in-down 0.8s ease-out;
        }
    </style>

</body></html>