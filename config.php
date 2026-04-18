<?php
// ============================================
// CONFIGURAÇÃO DO SISTEMA CSAT - PRODUÇÃO (INFINITYFREE)
// ============================================

// Ativar exibição de erros (DESATIVAR EM PRODUÇÃO)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do banco de dados - INFINITYFREE
define('DB_HOST', 'sql310.infinityfree.com'); // Da sua imagem
define('DB_USER', 'if0_41077573');            // Da sua imagem  
define('DB_PASS', 'root123456s');             // Da sua imagem
define('DB_NAME', 'if0_41077573_app');        // Você precisa criar este banco primeiro!

// URL base do sistema
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$folder = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . "://" . $host . $folder);

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// ============================================
// FUNÇÕES DE CONEXÃO COM BANCO
// ============================================

function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Erro de conexão: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log("Erro no banco: " . $e->getMessage());
            // Em produção, não exiba erros detalhados
            echo "Erro na conexão com o banco de dados. Tente novamente mais tarde.";
            return null;
        }
    }
    
    return $conn;
}

function dbSelect($sql, $params = []) {
    $conn = getConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $stmt->close();
    return $data;
}

function dbExecute($sql, $params = []) {
    $conn = getConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            else $types .= 's';
        }
        $stmt->bind_param($types, ...$params);
    }
    
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}



function mapearEmocaoParaBanco($emocao_tela) {
    $map = [
        'Muito Ruim' => 'MUITO_RUIM',
        'Ruim' => 'RUIM',
        'Regular' => 'REGULAR',
        'Bom' => 'BOM',
        'Excelente' => 'EXCELENTE'
    ];
    
    return isset($map[$emocao_tela]) ? $map[$emocao_tela] : 'REGULAR';
}

function mapearEmocaoParaCategoria($emocao_banco) {
    $map = [
        'MUITO_RUIM' => 'negativa',
        'RUIM' => 'negativa',
        'REGULAR' => 'neutra',
        'BOM' => 'positiva',
        'EXCELENTE' => 'positiva'
    ];
    
    return isset($map[$emocao_banco]) ? $map[$emocao_banco] : 'neutra';
}

// ============================================
// FUNÇÕES DE CONTROLE DE HISTÓRICO
// ============================================

// Função para manipular histórico
function iniciarNovaAvaliacao() {
    // Limpar sessão de avaliação
    if (isset($_SESSION['avaliacao'])) {
        unset($_SESSION['avaliacao']);
    }
    
    // Setar flag de nova avaliação
    $_SESSION['nova_avaliacao'] = true;
}

// Função para verificar se é uma nova avaliação
function isNovaAvaliacao() {
    return isset($_SESSION['nova_avaliacao']) && $_SESSION['nova_avaliacao'] === true;
}

// Função para limpar flag de nova avaliação
function limparFlagNovaAvaliacao() {
    if (isset($_SESSION['nova_avaliacao'])) {
        unset($_SESSION['nova_avaliacao']);
    }
}

// ============================================
// FUNÇÕES AUXILIARES DE VALIDAÇÃO
// ============================================

function validarNotaEstrelas($nota) {
    if ($nota === null || $nota === '') {
        return null;
    }
    
    $nota = (int)$nota;
    return ($nota >= 1 && $nota <= 5) ? $nota : null;
}

function getEmocaoTelaFromBanco($emocao_banco) {
    $map = [
        'MUITO_RUIM' => 'Muito Ruim',
        'RUIM' => 'Ruim',
        'REGULAR' => 'Regular',
        'BOM' => 'Bom',
        'EXCELENTE' => 'Excelente'
    ];
    
    return isset($map[$emocao_banco]) ? $map[$emocao_banco] : 'Regular';
}

// ============================================
// FUNÇÕES DE SEGURANÇA
// ============================================

function verificarSessaoAtiva() {
    return isset($_SESSION) && !empty($_SESSION);
}

function logout() {
    session_unset();
    session_destroy();
}

// ============================================
// FUNÇÕES DE FORMATAÇÃO
// ============================================

function formatarData($data, $formato = 'd/m/Y H:i') {
    $date = new DateTime($data);
    return $date->format($formato);
}

function calcularPorcentagem($valor, $total) {
    if ($total == 0) return 0;
    return round(($valor / $total) * 100, 2);
}

// ============================================
// FIM DO ARQUIVO CONFIG.PHP
// ============================================
?> 