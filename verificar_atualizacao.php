<?php
/**
 * Script para verificar se as atualizações foram aplicadas
 */

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Verificação de Atualização</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;}';
echo '.box{background:#2d2d2d;border:2px solid #4CAF50;border-radius:8px;padding:20px;margin:20px 0;}';
echo '.title{color:#4CAF50;font-size:20px;font-weight:bold;margin-bottom:10px;}';
echo '.info{color:#ffaa44;font-size:14px;margin:10px 0;padding:10px;background:#1a1a1a;border-radius:4px;}';
echo '.code{color:#888;font-size:11px;margin-top:15px;padding:10px;background:#0a0a0a;border-radius:4px;overflow-x:auto;}';
echo '.success{color:#4CAF50;} .error{color:#ff4444;} .warning{color:#ffaa44;}';
echo '</style></head><body>';

echo '<div class="box">';
echo '<div class="title">🔍 Verificação de Atualização do Sistema</div>';

// Verificar arquivo do controller
$controllerPath = __DIR__ . '/app/Controllers/OrcamentoController.php';
echo '<div class="info"><strong>Arquivo:</strong> ' . htmlspecialchars($controllerPath) . '</div>';

if (!file_exists($controllerPath)) {
    echo '<div class="error">❌ Arquivo não encontrado!</div>';
    echo '</div></body></html>';
    exit;
}

$fileContent = file_get_contents($controllerPath);
$fileSize = filesize($controllerPath);
$lastModified = date('Y-m-d H:i:s', filemtime($controllerPath));

echo '<div class="info"><strong>Tamanho:</strong> ' . number_format($fileSize) . ' bytes</div>';
echo '<div class="info"><strong>Última modificação:</strong> ' . htmlspecialchars($lastModified) . '</div>';

// Verificar se a correção foi aplicada
$hasOldCode = strpos($fileContent, "\$maxCodigos = ['MATERIAIS' => ['major' => 0, 'minor' => 0], 'MÃO DE OBRA'") !== false;
$hasNewCode = strpos($fileContent, "\$maxCodigos = [];\n            \n            foreach (\$itensExistentes as \$item) {\n                \$grupo = (string)(\$item['grupo'] ?? '');\n                \$codigo = (string)(\$item['codigo'] ?? '');\n                \n                // Inicializar grupo se não existir") !== false;

echo '<div class="info"><strong>Status da Correção:</strong></div>';

if ($hasOldCode) {
    echo '<div class="error">❌ CÓDIGO ANTIGO DETECTADO - Servidor ainda está usando versão antiga!</div>';
    echo '<div class="warning">⚠️ O servidor precisa recarregar o arquivo PHP atualizado.</div>';
} elseif ($hasNewCode) {
    echo '<div class="success">✅ CÓDIGO NOVO DETECTADO - Correção aplicada com sucesso!</div>';
} else {
    echo '<div class="warning">⚠️ Não foi possível detectar qual versão está em uso.</div>';
}

// Verificar linhas específicas
echo '<div class="info"><strong>Verificação de Linhas Específicas:</strong></div>';

$lines = explode("\n", $fileContent);
$totalLines = count($lines);

echo '<div class="info">Total de linhas: ' . $totalLines . '</div>';

// Procurar pela função addFromSinapi
$addFromSinapiLine = 0;
foreach ($lines as $num => $line) {
    if (strpos($line, 'public function addFromSinapi') !== false) {
        $addFromSinapiLine = $num + 1;
        break;
    }
}

if ($addFromSinapiLine > 0) {
    echo '<div class="info">Função addFromSinapi encontrada na linha: ' . $addFromSinapiLine . '</div>';
    
    // Mostrar trecho do código
    $startLine = max(0, $addFromSinapiLine + 60);
    $endLine = min($totalLines - 1, $addFromSinapiLine + 80);
    
    echo '<div class="code"><strong>Trecho do código (linhas ' . ($startLine + 1) . '-' . ($endLine + 1) . '):</strong><pre>';
    for ($i = $startLine; $i <= $endLine; $i++) {
        $lineNum = $i + 1;
        $lineContent = htmlspecialchars($lines[$i]);
        
        // Destacar linhas importantes
        if (strpos($lines[$i], '$maxCodigos') !== false) {
            echo '<span style="color:#4CAF50;">' . str_pad($lineNum, 4, ' ', STR_PAD_LEFT) . ': ' . $lineContent . '</span>' . "\n";
        } else {
            echo str_pad($lineNum, 4, ' ', STR_PAD_LEFT) . ': ' . $lineContent . "\n";
        }
    }
    echo '</pre></div>';
}

// Informações sobre cache do PHP
echo '<div class="info"><strong>Informações do PHP:</strong></div>';
echo '<div class="info">Versão PHP: ' . PHP_VERSION . '</div>';
echo '<div class="info">OPcache habilitado: ' . (function_exists('opcache_get_status') && opcache_get_status() ? 'Sim' : 'Não') . '</div>';

if (function_exists('opcache_get_status')) {
    $opcacheStatus = opcache_get_status();
    if ($opcacheStatus && isset($opcacheStatus['opcache_enabled'])) {
        echo '<div class="warning">⚠️ OPcache está ativo. Pode ser necessário limpar o cache.</div>';
        
        if (function_exists('opcache_reset')) {
            echo '<div class="info">Tentando limpar OPcache...</div>';
            if (opcache_reset()) {
                echo '<div class="success">✅ OPcache limpo com sucesso!</div>';
            } else {
                echo '<div class="error">❌ Falha ao limpar OPcache (permissões?)</div>';
            }
        }
    }
}

echo '<div class="info" style="margin-top:20px;"><strong>Próximos Passos:</strong></div>';
echo '<div class="info">';
echo '1. Se o código antigo foi detectado, o arquivo precisa ser enviado novamente ao servidor<br>';
echo '2. Se o código novo foi detectado mas ainda há erros, limpe o cache do PHP/OPcache<br>';
echo '3. Reinicie o PHP-FPM se necessário: <code>sudo systemctl restart php-fpm</code><br>';
echo '4. Tente acessar novamente: <a href="/?route=orcamentos/showSinapi&id=29" style="color:#4CAF50;">/?route=orcamentos/showSinapi&id=29</a>';
echo '</div>';

echo '</div></body></html>';
