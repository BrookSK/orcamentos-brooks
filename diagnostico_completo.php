<?php
/**
 * Diagnóstico completo do problema
 * Acesse: https://orcamento.onsolutionsbrasil.com.br/diagnostico_completo.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO COMPLETO ===\n\n";

// 1. Verificar qual arquivo está sendo usado
echo "1. ARQUIVO SENDO EXECUTADO:\n";
$arquivoReal = __DIR__ . '/app/Helpers/OrcamentoPDF.php';
$arquivoRealPath = realpath($arquivoReal);
echo "   Caminho esperado: $arquivoReal\n";
echo "   Caminho real: $arquivoRealPath\n";
echo "   Existe: " . (file_exists($arquivoRealPath) ? 'SIM' : 'NÃO') . "\n";

if (file_exists($arquivoRealPath)) {
    $linhas = file($arquivoRealPath);
    $totalLinhas = count($linhas);
    echo "   Total de linhas: $totalLinhas\n";
    echo "   Última modificação: " . date('Y-m-d H:i:s', filemtime($arquivoRealPath)) . "\n";
    echo "   Tamanho: " . number_format(filesize($arquivoRealPath)) . " bytes\n";
    
    // Verificar linha 1386
    echo "\n2. CONTEÚDO DA LINHA 1386:\n";
    if (isset($linhas[1385])) {
        echo "   " . trim($linhas[1385]) . "\n";
        
        // Verificar se tem foreach com $itensGrupo
        if (strpos($linhas[1385], 'foreach') !== false && strpos($linhas[1385], '$itensGrupo') !== false) {
            echo "   ✗ PROBLEMA: Linha 1386 TEM foreach com \$itensGrupo!\n";
            echo "   Isso significa que o arquivo está DESATUALIZADO.\n";
        } else {
            echo "   ✓ Linha 1386 NÃO tem foreach com \$itensGrupo\n";
        }
    }
    
    // Procurar onde está o foreach problemático
    echo "\n3. PROCURANDO 'foreach (\$itensGrupo':\n";
    $encontrados = [];
    foreach ($linhas as $num => $linha) {
        if (strpos($linha, 'foreach ($itensGrupo') !== false) {
            $encontrados[] = ($num + 1);
        }
    }
    
    if (empty($encontrados)) {
        echo "   ✗ NÃO ENCONTRADO - arquivo pode estar corrompido\n";
    } else {
        echo "   Encontrado nas linhas: " . implode(', ', $encontrados) . "\n";
    }
    
    // Verificar se tem a proteção
    echo "\n4. VERIFICANDO PROTEÇÃO:\n";
    $temProtecao = false;
    foreach ($linhas as $num => $linha) {
        if (strpos($linha, 'if (!is_array($itensGrupo))') !== false) {
            $temProtecao = true;
            echo "   ✓ Proteção encontrada na linha " . ($num + 1) . "\n";
            break;
        }
    }
    
    if (!$temProtecao) {
        echo "   ✗ Proteção NÃO encontrada - arquivo está desatualizado!\n";
    }
}

// 5. Verificar cache PHP
echo "\n5. CACHE PHP:\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status !== false) {
        echo "   OPcache: ATIVO\n";
        echo "   Arquivos em cache: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        
        // Tentar invalidar o arquivo específico
        if (function_exists('opcache_invalidate')) {
            $invalidado = opcache_invalidate($arquivoRealPath, true);
            echo "   Tentativa de invalidar cache: " . ($invalidado ? 'SUCESSO' : 'FALHOU') . "\n";
        }
        
        // Tentar resetar todo o cache
        if (function_exists('opcache_reset')) {
            $resetado = opcache_reset();
            echo "   Tentativa de resetar cache: " . ($resetado ? 'SUCESSO' : 'FALHOU') . "\n";
        }
    } else {
        echo "   OPcache: DESATIVADO\n";
    }
} else {
    echo "   OPcache: NÃO INSTALADO\n";
}

// 6. Verificar se há múltiplas cópias
echo "\n6. PROCURANDO MÚLTIPLAS CÓPIAS:\n";
$possiveisCaminhos = [
    __DIR__ . '/app/Helpers/OrcamentoPDF.php',
    __DIR__ . '/../app/Helpers/OrcamentoPDF.php',
    '/desenvolvimento/wwwroot/orcamento.onsolutionsbrasil.com.br/app/Helpers/OrcamentoPDF.php',
];

foreach ($possiveisCaminhos as $caminho) {
    if (file_exists($caminho)) {
        $real = realpath($caminho);
        $mod = date('Y-m-d H:i:s', filemtime($real));
        $linhas = count(file($real));
        echo "   ✓ $real\n";
        echo "     Modificado: $mod | Linhas: $linhas\n";
    }
}

// 7. Testar se a classe pode ser carregada
echo "\n7. TESTANDO CARREGAMENTO DA CLASSE:\n";
try {
    if (class_exists('App\Helpers\OrcamentoPDF')) {
        echo "   ✓ Classe OrcamentoPDF pode ser carregada\n";
        
        // Verificar qual arquivo foi carregado
        $reflection = new ReflectionClass('App\Helpers\OrcamentoPDF');
        $arquivoCarregado = $reflection->getFileName();
        echo "   Arquivo carregado: $arquivoCarregado\n";
        
        if ($arquivoCarregado !== $arquivoRealPath) {
            echo "   ✗ PROBLEMA: Arquivo carregado é DIFERENTE do esperado!\n";
        }
    } else {
        echo "   ⚠ Classe não pode ser carregada diretamente\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNÓSTICO ===\n";
echo "Se a linha 1386 TEM 'foreach (\$itensGrupo)', o arquivo no servidor está desatualizado.\n";
echo "Você precisa fazer upload do arquivo correto novamente.\n";
echo "\nSe a linha 1386 NÃO TEM 'foreach (\$itensGrupo)', o problema é cache do PHP.\n";
echo "Reinicie o PHP-FPM: sudo systemctl restart php-fpm\n";

echo "\n=== FIM ===\n";
