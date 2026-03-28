<?php

/**
 * Endpoint API: Buscar preços SINAPI do banco de dados
 * 
 * GET /sinapi-precos-api.php?codigo=1379&uf=SP
 * GET /sinapi-precos-api.php?codigos=1379,1106,370&uf=SP
 * GET /sinapi-precos-api.php?listar=1&termo=cimento&uf=SP
 */

require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Apenas GET permitido
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use GET.'
    ]);
    exit;
}

try {
    $uf = $_GET['uf'] ?? 'SP';
    
    // Validar UF
    $ufsValidas = [
        'AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN',
        'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'
    ];
    
    if (!in_array($uf, $ufsValidas)) {
        $uf = 'SP';
    }
    
    // MODO 1: Listar insumos (autocomplete)
    if (isset($_GET['listar'])) {
        $termo = $_GET['termo'] ?? '';
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
        
        $insumos = \App\Api\SinapiPrecosApi::listarInsumos($termo, $uf, $limite);
        
        echo json_encode([
            'success' => true,
            'total' => count($insumos),
            'insumos' => $insumos,
            'uf' => $uf
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // MODO 2: Buscar múltiplos códigos
    if (isset($_GET['codigos'])) {
        $codigosStr = $_GET['codigos'];
        $codigos = array_map('trim', explode(',', $codigosStr));
        $codigos = array_filter($codigos); // Remove vazios
        
        if (empty($codigos)) {
            echo json_encode([
                'success' => false,
                'message' => 'Nenhum código fornecido'
            ]);
            exit;
        }
        
        $precos = \App\Api\SinapiPrecosApi::buscarMultiplosPrecos($codigos, $uf);
        
        echo json_encode([
            'success' => true,
            'total' => count($precos),
            'precos' => $precos,
            'uf' => $uf
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // MODO 3: Buscar código único
    if (isset($_GET['codigo'])) {
        $codigo = trim($_GET['codigo']);
        
        if (empty($codigo)) {
            echo json_encode([
                'success' => false,
                'message' => 'Código não fornecido'
            ]);
            exit;
        }
        
        $resultado = \App\Api\SinapiPrecosApi::buscarPreco($codigo, $uf);
        
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // Nenhum parâmetro válido
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros inválidos',
        'uso' => [
            'buscar_um' => '/sinapi-precos-api.php?codigo=1379&uf=SP',
            'buscar_varios' => '/sinapi-precos-api.php?codigos=1379,1106,370&uf=SP',
            'listar' => '/sinapi-precos-api.php?listar=1&termo=cimento&uf=SP'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
