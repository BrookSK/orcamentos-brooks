<?php
/**
 * Script para verificar se o código está atualizado no servidor
 * Acesse: https://orcamento.onsolutionsbrasil.com.br/verificar_versao_codigo.php
 * DELETE este arquivo após usar!
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFICAÇÃO DE VERSÃO DO CÓDIGO ===\n\n";

$arquivo = __DIR__ . '/app/Helpers/OrcamentoPDF.php';

if (!file_exists($arquivo)) {
    echo "✗ ERRO: Arquivo não encontrado!\n";
    exit;
}

$linhas = file($arquivo);
$totalLinhas = count($linhas);

echo "Total de linhas: $totalLinhas\n\n";

// Verificar linha 1386 (onde o erro está acontecendo)
echo "=== LINHA 1386 (onde o erro ocorre) ===\n";
if (isset($linhas[1385])) {
    echo trim($linhas[1385]) . "\n";
    // Verificar contexto (5 linhas antes e depois)
    echo "\nContexto (linhas 1381-1391):\n";
    for ($i = 1380; $i <= 1390; $i++) {
        if (isset($linhas[$i])) {
            echo sprintf("%4d: %s\n", $i + 1, rtrim($linhas[$i]));
        }
    }
} else {
    echo "Linha 1386 não existe (arquivo tem menos linhas)\n";
}

echo "\n=== LINHA 1833 (onde deveria estar o código correto) ===\n";
if (isset($linhas[1832])) {
    echo trim($linhas[1832]) . "\n";
    // Verificar contexto
    echo "\nContexto (linhas 1828-1838):\n";
    for ($i = 1827; $i <= 1837; $i++) {
        if (isset($linhas[$i])) {
            echo sprintf("%4d: %s\n", $i + 1, rtrim($linhas[$i]));
        }
    }
} else {
    echo "Linha 1833 não existe\n";
}

// Procurar por "foreach ($itensGrupo"
echo "\n=== PROCURANDO 'foreach (\$itensGrupo' ===\n";
$encontrados = 0;
foreach ($linhas as $num => $linha) {
    if (strpos($linha, 'foreach ($itensGrupo') !== false) {
        $encontrados++;
        echo sprintf("Linha %d: %s\n", $num + 1, trim($linha));
    }
}
echo "Total encontrado: $encontrados ocorrência(s)\n";

// Verificar data de modificação
$dataModificacao = filemtime($arquivo);
echo "\n=== DATA DE MODIFICAÇÃO ===\n";
echo "Última modificação: " . date('Y-m-d H:i:s', $dataModificacao) . "\n";
echo "Timestamp: $dataModificacao\n";

// Verificar se há OPcache
echo "\n=== CACHE PHP ===\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status !== false) {
        echo "OPcache: ATIVO\n";
        echo "Para limpar: acesse limpar_cache_php.php\n";
    } else {
        echo "OPcache: DESATIVADO\n";
    }
} else {
    echo "OPcache: NÃO INSTALADO\n";
}

echo "\n=== DIAGNÓSTICO ===\n";
if ($totalLinhas < 1833) {
    echo "✗ PROBLEMA: Arquivo está desatualizado!\n";
    echo "  O arquivo tem apenas $totalLinhas linhas, mas deveria ter pelo menos 2082 linhas.\n";
    echo "  SOLUÇÃO: Faça upload do arquivo atualizado para o servidor.\n";
} else {
    echo "✓ Arquivo parece estar atualizado (tem linhas suficientes)\n";
    if (function_exists('opcache_get_status') && opcache_get_status() !== false) {
        echo "  Mas o OPcache pode estar servindo versão antiga.\n";
        echo "  SOLUÇÃO: Execute limpar_cache_php.php\n";
    }
}

echo "\n=== FIM ===\n";
echo "\nIMPORTANTE: DELETE este arquivo após usar!\n";
