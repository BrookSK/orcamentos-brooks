<?php
/**
 * Debug do método store
 * Substitua temporariamente a rota store para usar este arquivo
 */

// Capturar TODOS os erros
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/debug_errors.log');

// Registrar shutdown para capturar erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "ERRO FATAL CAPTURADO:\n\n";
        echo "Tipo: " . $error['type'] . "\n";
        echo "Mensagem: " . $error['message'] . "\n";
        echo "Arquivo: " . $error['file'] . "\n";
        echo "Linha: " . $error['line'] . "\n";
        
        file_put_contents(__DIR__ . '/debug_errors.log', 
            date('Y-m-d H:i:s') . " FATAL: " . print_r($error, true) . "\n\n",
            FILE_APPEND
        );
    }
});

file_put_contents(__DIR__ . '/debug_errors.log', 
    date('Y-m-d H:i:s') . " === INÍCIO DEBUG STORE ===\n",
    FILE_APPEND
);

try {
    require __DIR__ . '/app/bootstrap.php';
    
    file_put_contents(__DIR__ . '/debug_errors.log', 
        date('Y-m-d H:i:s') . " Bootstrap carregado\n",
        FILE_APPEND
    );
    
    // Simular o que o controller faz
    $controller = new \App\Controllers\OrcamentoController();
    
    file_put_contents(__DIR__ . '/debug_errors.log', 
        date('Y-m-d H:i:s') . " Controller criado\n",
        FILE_APPEND
    );
    
    // Chamar o método store
    $controller->store();
    
    file_put_contents(__DIR__ . '/debug_errors.log', 
        date('Y-m-d H:i:s') . " Store executado com sucesso\n",
        FILE_APPEND
    );
    
} catch (\Throwable $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERRO CAPTURADO:\n\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    file_put_contents(__DIR__ . '/debug_errors.log', 
        date('Y-m-d H:i:s') . " ERRO: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n",
        FILE_APPEND
    );
}

file_put_contents(__DIR__ . '/debug_errors.log', 
    date('Y-m-d H:i:s') . " === FIM DEBUG STORE ===\n\n",
    FILE_APPEND
);
