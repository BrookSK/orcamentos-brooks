<?php
/**
 * View de Adequação de Valores do Orçamento
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adequação de Valores - Orçamento #<?= htmlspecialchars($orcamento['numero_proposta'] ?? '') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input[type="number"],
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="number"]:focus,
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .preview-table th,
        .preview-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .preview-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .preview-table tr:hover {
            background: #f8f9fa;
        }
        
        .valor-positivo {
            color: #28a745;
            font-weight: bold;
        }
        
        .valor-negativo {
            color: #dc3545;
            font-weight: bold;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
        }
        
        .alert-success {
            background: #d4edda;
            border-left: 4px solid #155724;
            color: #155724;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #856404;
            color: #856404;
        }
        
        .historico-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        
        .historico-data {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .historico-valores {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        #preview-section {
            display: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>💰 Adequação de Valores</h1>
            <p class="subtitle">Orçamento: <?= htmlspecialchars($orcamento['numero_proposta'] ?? '') ?> - <?= htmlspecialchars($orcamento['obra_nome'] ?? '') ?></p>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Valor Atual Total</div>
                    <div class="info-value">R$ <?= number_format($totais['total_cobranca'], 2, ',', '.') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Custo Material</div>
                    <div class="info-value" style="color: #2196F3;">R$ <?= number_format($totais['total_material'], 2, ',', '.') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Custo Mão de Obra</div>
                    <div class="info-value" style="color: #4CAF50;">R$ <?= number_format($totais['total_mao_obra'], 2, ',', '.') ?></div>
                </div>
                <?php if ($orcamento['adequacao_aplicada']): ?>
                <div class="info-item">
                    <div class="info-label">Última Adequação</div>
                    <div class="info-value" style="font-size: 14px;">
                        Fator: <?= number_format($orcamento['fator_adequacao'], 4) ?><br>
                        <small><?= date('d/m/Y H:i', strtotime($orcamento['data_adequacao'])) ?></small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($mensagem)): ?>
            <div class="alert alert-<?= $mensagem['tipo'] ?>">
                <?= htmlspecialchars($mensagem['texto']) ?>
            </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <strong>ℹ️ Como funciona:</strong><br>
                Informe o valor total que deseja receber pelo contrato. O sistema ajustará <strong>proporcionalmente</strong> todos os itens de todas as etapas, mantendo as proporções relativas entre elas.
            </div>
            
            <form id="form-adequacao" method="POST">
                <div class="form-group">
                    <label for="valor_desejado">💵 Valor Total Desejado (R$)</label>
                    <input type="number" 
                           id="valor_desejado" 
                           name="valor_desejado" 
                           step="0.01" 
                           min="0.01"
                           placeholder="Ex: 5000000.00"
                           required>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Digite o valor total que você deseja receber pelo contrato
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="observacao">📝 Observação (opcional)</label>
                    <textarea id="observacao" 
                              name="observacao" 
                              rows="3" 
                              placeholder="Ex: Ajuste solicitado pelo cliente para adequação ao orçamento disponível"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="calcularPreview()">
                        🔍 Visualizar Preview
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-aplicar" disabled>
                        ✅ Aplicar Adequação
                    </button>
                    <a href="/?route=orcamentos/show&id=<?= $orcamento['id'] ?>" class="btn btn-secondary">
                        ← Voltar
                    </a>
                </div>
            </form>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="margin-top: 10px;">Calculando...</p>
            </div>
            
            <div id="preview-section">
                <h2 style="margin-bottom: 20px;">📊 Preview da Adequação</h2>
                <div id="preview-content"></div>
            </div>
        </div>
        
        <?php if (!empty($historico)): ?>
        <div class="card">
            <h2 style="margin-bottom: 20px;">📜 Histórico de Adequações</h2>
            <?php foreach ($historico as $item): ?>
            <div class="historico-item">
                <div class="historico-data">
                    <?= date('d/m/Y H:i:s', strtotime($item['created_at'])) ?>
                    <?php if ($item['usuario']): ?>
                    - Por: <?= htmlspecialchars($item['usuario']) ?>
                    <?php endif; ?>
                </div>
                <div class="historico-valores">
                    <div>
                        <strong>Valor Anterior:</strong> R$ <?= number_format($item['valor_anterior'], 2, ',', '.') ?>
                    </div>
                    <div>
                        <strong>Valor Novo:</strong> R$ <?= number_format($item['valor_desejado'], 2, ',', '.') ?>
                    </div>
                    <div>
                        <strong>Ajuste:</strong> 
                        <span class="<?= $item['percentual_ajuste'] >= 0 ? 'valor-positivo' : 'valor-negativo' ?>">
                            <?= number_format($item['percentual_ajuste'], 2, ',', '.') ?>%
                        </span>
                    </div>
                </div>
                <?php if ($item['observacao']): ?>
                <div style="margin-top: 10px; font-size: 14px; color: #666;">
                    <strong>Obs:</strong> <?= htmlspecialchars($item['observacao']) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        const orcamentoId = <?= $orcamento['id'] ?>;
        const valorAtual = <?= $totais['total_cobranca'] ?>;
        
        function calcularPreview() {
            const valorDesejado = parseFloat(document.getElementById('valor_desejado').value);
            
            if (!valorDesejado || valorDesejado <= 0) {
                alert('Por favor, informe um valor válido.');
                return;
            }
            
            document.getElementById('loading').style.display = 'block';
            document.getElementById('preview-section').style.display = 'none';
            
            fetch(`/?route=orcamentos/adequacaoPreview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    valor_desejado: valorDesejado,
                    orcamento_id: orcamentoId
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                mostrarPreview(data);
                document.getElementById('btn-aplicar').disabled = false;
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                alert('Erro ao calcular preview: ' + error.message);
            });
        }
        
        function mostrarPreview(data) {
            const diferenca = data.diferenca;
            const tipoAjuste = diferenca > 0 ? 'aumento' : 'redução';
            const classeDiferenca = diferenca > 0 ? 'valor-positivo' : 'valor-negativo';
            
            let html = `
                <div class="alert alert-${diferenca > 0 ? 'success' : 'warning'}">
                    <strong>${diferenca > 0 ? '📈' : '📉'} ${tipoAjuste.toUpperCase()} de ${Math.abs(data.percentual_ajuste).toFixed(2)}%</strong><br>
                    Diferença: <span class="${classeDiferenca}">R$ ${Math.abs(diferenca).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                </div>
                
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th>Etapa</th>
                            <th style="text-align: right;">Valor Atual</th>
                            <th style="text-align: right;">Valor Novo</th>
                            <th style="text-align: right;">Diferença</th>
                            <th style="text-align: right;">% do Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.etapas.forEach(etapa => {
                const diferencaEtapa = etapa.diferenca;
                const classeDif = diferencaEtapa > 0 ? 'valor-positivo' : 'valor-negativo';
                
                html += `
                    <tr>
                        <td><strong>${etapa.etapa}</strong></td>
                        <td style="text-align: right;">R$ ${etapa.valor_atual.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="text-align: right;">R$ ${etapa.valor_novo.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="text-align: right;" class="${classeDif}">
                            ${diferencaEtapa > 0 ? '+' : ''}R$ ${diferencaEtapa.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </td>
                        <td style="text-align: right;">${etapa.percentual.toFixed(2)}%</td>
                    </tr>
                `;
            });
            
            html += `
                    <tr style="background: #f8f9fa; font-weight: bold; font-size: 16px;">
                        <td>TOTAL</td>
                        <td style="text-align: right;">R$ ${data.valor_atual.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="text-align: right;">R$ ${data.valor_desejado.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="text-align: right;" class="${classeDiferenca}">
                            ${diferenca > 0 ? '+' : ''}R$ ${Math.abs(diferenca).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </td>
                        <td style="text-align: right;">100.00%</td>
                    </tr>
                </tbody>
            </table>
            `;
            
            document.getElementById('preview-content').innerHTML = html;
            document.getElementById('preview-section').style.display = 'block';
        }
        
        document.getElementById('form-adequacao').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const valorDesejado = parseFloat(document.getElementById('valor_desejado').value);
            const observacao = document.getElementById('observacao').value;
            
            if (!confirm(`Confirma a aplicação da adequação para R$ ${valorDesejado.toLocaleString('pt-BR', {minimumFractionDigits: 2})}?\n\nEsta ação irá atualizar TODOS os itens do orçamento proporcionalmente.`)) {
                return;
            }
            
            document.getElementById('loading').style.display = 'block';
            
            fetch(`/?route=orcamentos/adequacaoAplicar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    valor_desejado: valorDesejado,
                    orcamento_id: orcamentoId,
                    observacao: observacao
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                if (data.sucesso) {
                    alert(data.mensagem);
                    window.location.reload();
                } else {
                    alert('Erro: ' + data.erro);
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                alert('Erro ao aplicar adequação: ' + error.message);
            });
        });
    </script>
</body>
</html>
