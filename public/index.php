<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\ItemController;
use App\Controllers\OrcamentoController;

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
