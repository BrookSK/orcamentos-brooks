<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\ItemController;
use App\Controllers\OrcamentoController;

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
        'edit' => ['GET'],
        'update' => ['POST'],
        'delete' => ['POST'],
        'show' => ['GET'],
        'itemStore' => ['POST'],
        'itemEdit' => ['GET'],
        'itemUpdate' => ['POST'],
        'itemDelete' => ['POST'],
        'grupos' => ['GET'],
        'gruposStore' => ['POST'],
        'gruposDelete' => ['POST'],
        'categorias' => ['GET'],
        'categoriasStore' => ['POST'],
        'categoriasDelete' => ['POST'],
        'unidades' => ['GET'],
        'unidadesStore' => ['POST'],
        'unidadesDelete' => ['POST'],
        'print' => ['GET'],
        'pdf' => ['GET'],
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
