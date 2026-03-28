<?php
// Script para limpar cache do OPcache

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpo com sucesso!\n";
} else {
    echo "⚠️ OPcache não está habilitado.\n";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cache limpo com sucesso!\n";
}

echo "\n✅ Cache limpo. Tente acessar o PDF novamente.\n";
