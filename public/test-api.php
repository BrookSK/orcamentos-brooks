<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'API funcionando!',
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s')
]);
