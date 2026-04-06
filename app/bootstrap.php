<?php

declare(strict_types=1);

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    \App\Core\Logger::error('PHP error', [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line,
    ]);

    return false;
});

set_exception_handler(static function (\Throwable $e): void {
    \App\Core\Logger::exception($e, [
        'uri' => (string)($_SERVER['REQUEST_URI'] ?? ''),
        'method' => (string)($_SERVER['REQUEST_METHOD'] ?? ''),
    ]);

    http_response_code(500);
    
    // Detectar se é uma requisição AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    $debug = ((string)($_GET['debug'] ?? '') === '1') || ((string)getenv('APP_DEBUG') === '1');
    
    // SEMPRE mostrar erro detalhado (remova esta linha em produção)
    $debug = true;
    
    if ($isAjax) {
        // Para requisições AJAX, sempre retornar JSON
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $debug ? $e->getMessage() : 'Erro interno do servidor',
            'debug' => $debug ? [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ] : null
        ]);
        return;
    }
    
    if ($debug) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Exception: " . get_class($e) . "\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        echo $e->getTraceAsString();
        return;
    }
    echo 'Erro interno. Verifique o log do sistema.';
});

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

// Boot DB early so schema is ensured.
\App\Core\Database::pdo();

// Seed default options (grupos/categorias/unidades) from template JSON, if present.
\App\Models\OrcamentoOpcao::seedFromTemplateJson(__DIR__ . '/../estimativa_custos.json');
