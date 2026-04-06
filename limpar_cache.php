<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpar Cache</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 40px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 {
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .result {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .success {
            border-left: 4px solid #4CAF50;
        }
        .info {
            border-left: 4px solid #2196F3;
        }
        .warning {
            border-left: 4px solid #ff9800;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-secondary {
            background: #2196F3;
        }
        .btn-secondary:hover {
            background: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Limpeza de Cache</h1>
        
        <?php
        $results = [];
        
        // 1. Limpar OPcache
        if (function_exists('opcache_reset')) {
            if (opcache_reset()) {
                $results[] = ['type' => 'success', 'message' => '✓ OPcache limpo com sucesso'];
            } else {
                $results[] = ['type' => 'warning', 'message' => '⚠ Não foi possível limpar o OPcache'];
            }
        } else {
            $results[] = ['type' => 'info', 'message' => 'ℹ OPcache não está habilitado'];
        }
        
        // 2. Limpar cache de realpath
        if (function_exists('clearstatcache')) {
            clearstatcache(true);
            $results[] = ['type' => 'success', 'message' => '✓ Cache de realpath limpo'];
        }
        
        // 3. Informações sobre cache do navegador
        $results[] = ['type' => 'info', 'message' => 'ℹ Para limpar o cache do navegador, pressione Ctrl+Shift+R (ou Cmd+Shift+R no Mac)'];
        
        // 4. Verificar se há sessão ativa
        if (session_status() === PHP_SESSION_ACTIVE) {
            $results[] = ['type' => 'info', 'message' => 'ℹ Sessão PHP ativa detectada'];
        }
        
        // Exibir resultados
        foreach ($results as $result) {
            echo '<div class="result ' . $result['type'] . '">' . htmlspecialchars($result['message']) . '</div>';
        }
        ?>
        
        <div style="margin-top: 30px;">
            <h2>📋 Instruções</h2>
            <div class="result info">
                <p><strong>Se o orçamento ainda não atualizar após editar um grupo:</strong></p>
                <ol>
                    <li>Clique no botão "Limpar Cache" abaixo</li>
                    <li>Pressione <strong>Ctrl+Shift+R</strong> (ou <strong>Cmd+Shift+R</strong> no Mac) na página do orçamento</li>
                    <li>Se ainda não funcionar, feche e abra o navegador novamente</li>
                </ol>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="limpar_cache.php" class="btn">🔄 Limpar Cache Novamente</a>
            <a href="/?route=orcamentos/index" class="btn btn-secondary">← Voltar para Orçamentos</a>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px; font-size: 12px; color: #999;">
            <strong>Informações do Sistema:</strong><br>
            PHP Version: <?php echo PHP_VERSION; ?><br>
            OPcache: <?php echo function_exists('opcache_get_status') && opcache_get_status() ? 'Habilitado' : 'Desabilitado'; ?><br>
            Memory Limit: <?php echo ini_get('memory_limit'); ?><br>
            Timestamp: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>
