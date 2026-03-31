<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

// ============================================
// ROTA DA API SINAPI
// ============================================
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($requestUri, '/api/sinapi-precos') !== false || isset($_GET['api']) && $_GET['api'] === 'sinapi-precos') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido']);
        exit;
    }
    
    try {
        $uf = $_GET['uf'] ?? 'SP';
        
        // MODO 1: Listar insumos
        if (isset($_GET['listar'])) {
            $termo = $_GET['termo'] ?? '';
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
            $insumos = \App\Api\SinapiPrecosApi::listarInsumos($termo, $uf, $limite);
            echo json_encode(['success' => true, 'total' => count($insumos), 'insumos' => $insumos, 'uf' => $uf], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // MODO 2: Buscar múltiplos códigos
        if (isset($_GET['codigos'])) {
            $codigos = array_filter(array_map('trim', explode(',', $_GET['codigos'])));
            if (empty($codigos)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum código fornecido']);
                exit;
            }
            $precos = \App\Api\SinapiPrecosApi::buscarMultiplosPrecos($codigos, $uf);
            echo json_encode(['success' => true, 'total' => count($precos), 'precos' => $precos, 'uf' => $uf], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // MODO 3: Buscar código único
        if (isset($_GET['codigo'])) {
            $codigo = trim($_GET['codigo']);
            if (empty($codigo)) {
                echo json_encode(['success' => false, 'message' => 'Código não fornecido']);
                exit;
            }
            $resultado = \App\Api\SinapiPrecosApi::buscarPreco($codigo, $uf);
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos'], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno', 'error' => $e->getMessage()]);
        exit;
    }
}

// ============================================
// ROTA DA API SINAPI - ATUALIZAR PREÇO
// ============================================
if (isset($_GET['api']) && $_GET['api'] === 'sinapi-atualizar-preco') {
    // Suprimir qualquer output anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    // Desabilitar display de erros para esta rota
    ini_set('display_errors', '0');
    
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        ob_end_clean();
        http_response_code(200);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'JSON inválido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!isset($input['codigo']) || !isset($input['preco'])) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Código e preço são obrigatórios'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $codigo = trim($input['codigo']);
        $preco = (float)$input['preco'];
        $uf = $input['uf'] ?? 'SP';
        $unidade = isset($input['unidade']) ? trim($input['unidade']) : null;
        
        if (empty($codigo) || $preco < 0) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Atualizar no banco de dados
        $db = \App\Core\Database::pdo();
        
        // Verificar se o registro existe
        $checkStmt = $db->prepare("SELECT codigo, preco_unit, unidade FROM sinapi_insumos WHERE codigo = :codigo AND uf = :uf");
        $checkStmt->execute([':codigo' => $codigo, ':uf' => $uf]);
        $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$existing) {
            ob_end_clean();
            echo json_encode([
                'success' => false, 
                'error' => 'Código SINAPI não encontrado',
                'codigo' => $codigo
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Atualizar preço e opcionalmente unidade
        if ($unidade !== null) {
            $stmt = $db->prepare("
                UPDATE sinapi_insumos 
                SET preco_unit = :preco, unidade = :unidade 
                WHERE codigo = :codigo AND uf = :uf
            ");
            $stmt->execute([
                ':preco' => $preco,
                ':unidade' => $unidade,
                ':codigo' => $codigo,
                ':uf' => $uf
            ]);
        } else {
            $stmt = $db->prepare("
                UPDATE sinapi_insumos 
                SET preco_unit = :preco 
                WHERE codigo = :codigo AND uf = :uf
            ");
            $stmt->execute([
                ':preco' => $preco,
                ':codigo' => $codigo,
                ':uf' => $uf
            ]);
        }
        
        $rowsAffected = $stmt->rowCount();
        
        ob_end_clean();
        
        if ($rowsAffected > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Atualizado com sucesso',
                'codigo' => $codigo,
                'preco' => $preco,
                'unidade' => $unidade ?? $existing['unidade'],
                'preco_anterior' => $existing['preco_unit']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Nenhuma alteração'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
        
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Erro interno',
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Erro crítico',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================
// ROTA DA API SINAPI - ATUALIZAR DESCRIÇÃO
// ============================================
if (isset($_GET['api']) && $_GET['api'] === 'sinapi-atualizar-descricao') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();
    
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $codigo = trim($input['codigo'] ?? '');
        $descricao = trim($input['descricao'] ?? '');
        $uf = $input['uf'] ?? 'SP';
        
        if (empty($codigo) || empty($descricao)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Dados inválidos'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $db = \App\Core\Database::pdo();
        
        $stmt = $db->prepare("UPDATE sinapi_insumos SET descricao = :descricao WHERE codigo = :codigo AND uf = :uf");
        $stmt->execute([':descricao' => $descricao, ':codigo' => $codigo, ':uf' => $uf]);
        
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Descrição atualizada'], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

use App\Controllers\ItemController;
use App\Controllers\OrcamentoController;
use App\Controllers\SinapiController;

$uriPath = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$uriPath = $uriPath !== '' ? rtrim($uriPath, '/') : '';

if (!isset($_GET['route']) && $uriPath !== '') {
    if (preg_match('#^/orcamentos/(\d+)$#', $uriPath, $m) === 1) {
        $_GET['route'] = 'orcamentos/show';
        $_GET['id'] = $m[1];
    } elseif (preg_match('#^/orcamentos/(\d+)/adequacao$#', $uriPath, $m) === 1) {
        $_GET['route'] = 'orcamentos/adequacao';
        $_GET['id'] = $m[1];
    } elseif (preg_match('#^/orcamentos/(\d+)/pdf$#', $uriPath, $m) === 1) {
        $_GET['route'] = 'orcamentos/pdf';
        $_GET['id'] = $m[1];
    }
}

$route = (string)($_GET['route'] ?? 'orcamentos/index');
$route = trim($route, '/');

[$controllerName, $action] = array_pad(explode('/', $route, 2), 2, 'index');

$controllerName = strtolower((string)$controllerName);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($controllerName === 'orcamentos') {
    $controller = new OrcamentoController();
    $allowed = [
        'index' => ['GET'],
        'create' => ['GET'],
        'store' => ['POST'],
        'createSinapi' => ['GET'],
        'storeSinapi' => ['POST'],
        'showSinapi' => ['GET'],
        'addFromSinapi' => ['POST'],
        'edit' => ['GET'],
        'update' => ['POST'],
        'delete' => ['POST'],
        'show' => ['GET'],
        'itemStore' => ['POST'],
        'itemStoreAjax' => ['POST'],
        'buscarSinapi' => ['GET'],
        'itemEdit' => ['GET'],
        'itemUpdate' => ['POST'],
        'itemDelete' => ['POST'],
        'renomearCategoria' => ['POST'],
        'reorderItems' => ['POST'],
        'aplicarDescontoGrupo' => ['POST'],
        'recalcularMargens' => ['POST'],
        'grupos' => ['GET'],
        'gruposStore' => ['POST'],
        'gruposUpdate' => ['POST'],
        'gruposDelete' => ['POST'],
        'categorias' => ['GET'],
        'categoriasStore' => ['POST'],
        'categoriasUpdate' => ['POST'],
        'categoriasDelete' => ['POST'],
        'unidades' => ['GET'],
        'unidadesStore' => ['POST'],
        'unidadesUpdate' => ['POST'],
        'unidadesDelete' => ['POST'],
        'print' => ['GET'],
        'pdf' => ['GET'],
        'pdfAdmin' => ['GET'],
        'adequacao' => ['GET'],
        'adequacaoPreview' => ['POST'],
        'adequacaoAplicar' => ['POST'],
    ];

    if (!isset($allowed[$action]) || !in_array($method, $allowed[$action], true)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }

    $controller->{$action}();
    exit;
}

if ($controllerName === 'sinapi') {
    $controller = new SinapiController();
    $allowed = [
        'instalar' => ['GET'],
        'diagnostico' => ['GET'],
        'gerenciar' => ['GET'],
        'atualizar' => ['POST'],
        'excluir' => ['POST'],
    ];

    if (!isset($allowed[$action]) || !in_array($method, $allowed[$action], true)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }

    $controller->{$action}();
    exit;
}

if ($controllerName === 'items') {
    $controller = new ItemController();
    $allowed = [
        'index' => ['GET'],
        'create' => ['GET'],
        'store' => ['POST'],
        'edit' => ['GET'],
        'update' => ['POST'],
        'delete' => ['POST'],
    ];

    if (!isset($allowed[$action]) || !in_array($method, $allowed[$action], true)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }

    $controller->{$action}();
    exit;
}

http_response_code(404);
echo 'Not found';
