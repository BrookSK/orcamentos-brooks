<?php
/**
 * Script para limpar cache do OPcache e verificar status
 */

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Limpar Cache OPcache</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;}';
echo '.box{background:#2d2d2d;border:2px solid #4CAF50;border-radius:8px;padding:20px;margin:20px 0;}';
echo '.title{color:#4CAF50;font-size:20px;font-weight:bold;margin-bottom:10px;}';
echo '.info{color:#ffaa44;font-size:14px;margin:10px 0;padding:10px;background:#1a1a1a;border-radius:4px;}';
echo '.success{color:#4CAF50;} .error{color:#ff4444;} .warning{color:#ffaa44;}';
echo '.btn{display:inline-block;padding:10px 20px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;margin:10px 5px;}';
echo '</style></head><body>';

echo '<div class="box">';
echo '<div class="title">🧹 Limpeza de Cache PHP OPcache</div>';

// Verificar se OPcache está disponível
if (!function_exists('opcache_get_status')) {
    echo '<div class="warning">⚠️ OPcache não está disponível neste servidor.</div>';
    echo '<div class="info">O servidor pode estar usando outro sistema de cache ou nenhum cache.</div>';
    echo '</div></body></html>';
    exit;
}

$status = opcache_get_status();

if (!$status) {
    echo '<div class="warning">⚠️ OPcache está instalado mas não está ativo.</div>';
    echo '</div></body></html>';
    exit;
}

echo '<div class="info"><strong>Status do OPcache:</strong></div>';
echo '<div class="info">Habilitado: ' . ($status['opcache_enabled'] ? '<span class="success">Sim</span>' : '<span class="error">Não</span>') . '</div>';
echo '<div class="info">Cache cheio: ' . ($status['cache_full'] ? '<span class="warning">Sim</span>' : '<span class="success">Não</span>') . '</div>';
echo '<div class="info">Memória usada: ' . number_format($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB</div>';
echo '<div class="info">Memória livre: ' . number_format($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . ' MB</div>';
echo '<div class="info">Arquivos em cache: ' . number_format($status['opcache_statistics']['num_cached_scripts']) . '</div>';
echo '<div class="info">Hits: ' . number_format($status['opcache_statistics']['hits']) . '</div>';
echo '<div class="info">Misses: ' . number_format($status['opcache_statistics']['misses']) . '</div>';

// Tentar limpar o cache
if (isset($_GET['limpar']) && $_GET['limpar'] === '1') {
    echo '<div class="info" style="margin-top:20px;"><strong>Limpando cache...</strong></div>';
    
    if (function_exists('opcache_reset')) {
        if (opcache_reset()) {
            echo '<div class="success">✅ Cache OPcache limpo com sucesso!</div>';
            echo '<div class="info">Todos os arquivos PHP em cache foram removidos.</div>';
            echo '<div class="info">O servidor irá recompilar os arquivos na próxima requisição.</div>';
            
            // Invalidar arquivo específico
            $controllerPath = __DIR__ . '/app/Controllers/OrcamentoController.php';
            if (function_exists('opcache_invalidate') && file_exists($controllerPath)) {
                opcache_invalidate($controllerPath, true);
                echo '<div class="success">✅ Arquivo OrcamentoController.php invalidado especificamente.</div>';
            }
            
            echo '<div class="info" style="margin-top:15px;">';
            echo '<a href="/?route=orcamentos/showSinapi&id=29" class="btn">Testar Orçamento SINAPI</a>';
            echo '<a href="/verificar_atualizacao.php" class="btn">Verificar Atualização</a>';
            echo '</div>';
        } else {
            echo '<div class="error">❌ Falha ao limpar o cache OPcache.</div>';
            echo '<div class="warning">Pode ser necessário permissões especiais ou reiniciar o PHP-FPM.</div>';
        }
    } else {
        echo '<div class="error">❌ Função opcache_reset() não está disponível.</div>';
    }
} else {
    echo '<div class="info" style="margin-top:20px;">';
    echo '<a href="?limpar=1" class="btn">🧹 Limpar Cache Agora</a>';
    echo '<a href="/verificar_atualizacao.php" class="btn">🔍 Verificar Atualização</a>';
    echo '</div>';
    
    echo '<div class="warning" style="margin-top:15px;">⚠️ Limpar o cache pode causar um pequeno aumento temporário no tempo de resposta enquanto os arquivos são recompilados.</div>';
}

// Listar arquivos em cache (se disponível)
if (isset($status['scripts']) && is_array($status['scripts'])) {
    $controllerInCache = false;
    foreach ($status['scripts'] as $file => $info) {
        if (strpos($file, 'OrcamentoController.php') !== false) {
            $controllerInCache = true;
            echo '<div class="info" style="margin-top:20px;"><strong>OrcamentoController.php em cache:</strong></div>';
            echo '<div class="info">Arquivo: ' . htmlspecialchars($file) . '</div>';
            echo '<div class="info">Última modificação: ' . date('Y-m-d H:i:s', $info['timestamp']) . '</div>';
            echo '<div class="info">Hits: ' . number_format($info['hits']) . '</div>';
            break;
        }
    }
    
    if (!$controllerInCache) {
        echo '<div class="warning" style="margin-top:20px;">⚠️ OrcamentoController.php não foi encontrado no cache.</div>';
    }
}

echo '</div></body></html>';
