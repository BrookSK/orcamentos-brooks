<?php
// Limpar todos os caches possíveis

echo "Limpando caches...\n\n";

// 1. OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache limpo\n";
} else {
    echo "⚠ OPcache não disponível\n";
}

// 2. APCu
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✓ APCu limpo\n";
} else {
    echo "⚠ APCu não disponível\n";
}

// 3. Realpath cache
clearstatcache(true);
echo "✓ Realpath cache limpo\n";

echo "\n✓✓✓ Caches limpos com sucesso!\n";
echo "\nAgora teste novamente: ?route=sinapi/diagnostico\n";
