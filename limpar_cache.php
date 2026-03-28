<?php
/**
 * Script para limpar cache do OPcache
 * Acesse via navegador: https://orcamento.onsolutionsbrasil.com.br/limpar_cache.php
 */

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Limpar Cache</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;}';
echo '.box{background:#2d2d2d;border:2px solid #4CAF50;border-radius:8px;padding:20px;margin:20px 0;}';
echo '.success{color:#4CAF50;} .error{color:#ff4444;} .info{color:#ffaa44;}';
echo '</style></head><body>';

echo '<div class="box">';
echo '<h1>🧹 Limpeza de Cache</h1>';

$success = false;

// Tentar limpar OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo '<p class="success">✅ OPcache limpo com sucesso!</p>';
        $success = true;
    } else {
        echo '<p class="error">❌ Falha ao limpar OPcache</p>';
    }
} else {
    echo '<p class="info">⚠️ OPcache não disponível</p>';
}

// Invalidar arquivos específicos
$files = [
    __DIR__ . '/app/Controllers/OrcamentoController.php',
    __DIR__ . '/app/Views/orcamentos/show.php',
    __DIR__ . '/app/Views/orcamentos/show_sinapi.php'
];

if (function_exists('opcache_invalidate')) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            opcache_invalidate($file, true);
            echo '<p class="success">✅ Invalidado: ' . basename($file) . '</p>';
            $success = true;
        }
    }
}

if ($success) {
    echo '<p class="info">Cache limpo! Tente acessar o orçamento agora:</p>';
    echo '<p><a href="/?route=orcamentos/show&id=29" style="color:#4CAF50;">Abrir Orçamento ID 29</a></p>';
} else {
    echo '<p class="error">Não foi possível limpar o cache automaticamente.</p>';
    echo '<p class="info">Você precisa reiniciar o PHP-FPM manualmente.</p>';
}

echo '</div></body></html>';
