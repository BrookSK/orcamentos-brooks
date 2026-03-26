<?php
/**
 * View de Adequação de Valores do Orçamento
 */
?>
<div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
        <div>
            <div style="font-weight:800; font-size:14px;">Adequação de Valores</div>
            <div class="muted" style="margin-top:4px; font-size:12px;">
                Orçamento: <?php echo htmlspecialchars((string)($orcamento['numero_proposta'] ?? '')); ?>
                <?php if (!empty($orcamento['cliente_nome'])) : ?>
                    · Cliente: <?php echo htmlspecialchars((string)$orcamento['cliente_nome']); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="actions">
            <a class="btn" href="/?route=orcamentos/show&id=<?php echo (int)($orcamento['id'] ?? 0); ?>">Voltar</a>
        </div>
    </div>
</div>

<div class="card" style="padding:16px; margin-bottom:12px; background:#fff; color:#111;">
    <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px;">
        <div style="border:1px solid rgba(0,0,0,.12); border-radius:12px; padding:12px;">
            <div style="font-size:12px; color:#444; font-weight:700;">VALOR ATUAL TOTAL</div>
            <div style="font-size:18px; font-weight:900; margin-top:6px;">R$ <?php echo number_format((float)($totais['total_cobranca'] ?? 0), 2, ',', '.'); ?></div>
        </div>
        <div style="border:1px solid rgba(0,0,0,.12); border-radius:12px; padding:12px;">
            <div style="font-size:12px; color:#444; font-weight:700;">CUSTO MATERIAL</div>
            <div style="font-size:18px; font-weight:900; margin-top:6px; color:#1d4ed8;">R$ <?php echo number_format((float)($totais['total_material'] ?? 0), 2, ',', '.'); ?></div>
        </div>
        <div style="border:1px solid rgba(0,0,0,.12); border-radius:12px; padding:12px;">
            <div style="font-size:12px; color:#444; font-weight:700;">CUSTO MÃO DE OBRA</div>
            <div style="font-size:18px; font-weight:900; margin-top:6px; color:#16a34a;">R$ <?php echo number_format((float)($totais['total_mao_obra'] ?? 0), 2, ',', '.'); ?></div>
        </div>
    </div>

    <div style="margin-top:14px; border:1px solid rgba(0,0,0,.12); background:#eef8ff; padding:12px; border-radius:12px;">
        <div style="font-weight:900; margin-bottom:4px;">Como funciona:</div>
        <div style="color:#111;">Informe o valor total que você deseja receber pelo contrato. O sistema ajustará <strong>proporcionalmente</strong> todos os itens de todas as etapas, mantendo as proporções relativas entre elas.</div>
    </div>

    <form id="form-adequacao">
        <div class="form-group">
            <label for="valor_desejado">💰 Valor Total Desejado (R$)</label>
            <input type="text" 
                   id="valor_desejado" 
                   name="valor_desejado" 
                   inputmode="decimal"
                   placeholder="Ex: 8.000.000,00 ou 8 M"
                   required>
            <small style="color: #666; margin-top: 5px; display: block;">
                Digite o valor total que você deseja receber pelo contrato
            </small>
        </div>

        <div class="form-group">
            <label for="observacao">� Observação (opcional)</label>
            <textarea id="observacao" 
                      name="observacao" 
                      rows="3" 
                      placeholder="Ex: Ajuste solicitado pelo cliente para adequação ao orçamento disponível"></textarea>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <button type="button" class="btn" onclick="calcularPreview()">Visualizar Preview</button>
            <button type="submit" class="btn primary" id="btn-aplicar" disabled>Aplicar Adequação</button>
        </div>
    </form>

    <div class="loading" id="loading">
        <div class="spinner"></div>
        <p style="margin-top: 10px;">Calculando...</p>
    </div>

    <div id="preview-section" style="display:none; margin-top:16px;">
        <div style="font-weight:900; margin-bottom:10px;">Preview da Adequação</div>
        <div id="preview-content"></div>
    </div>
</div>

<?php if (!empty($historico)) : ?>
<div class="card" style="padding:16px; margin-bottom:12px; background:#fff; color:#111;">
    <div style="font-weight:900; margin-bottom:10px;">Histórico de Adequações</div>
    <div style="overflow:auto; border:1px solid rgba(0,0,0,.12); border-radius:12px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f3f4f6;">
                <tr>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.10);">Data</th>
                    <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">Anterior</th>
                    <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">Novo</th>
                    <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">Ajuste</th>
                    <th style="padding:10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.10);">Obs.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($historico ?? []) as $item) : ?>
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                            <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime((string)($item['created_at'] ?? '')))); ?>
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right;">
                            R$ <?php echo number_format((float)($item['valor_anterior'] ?? 0), 2, ',', '.'); ?>
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right;">
                            R$ <?php echo number_format((float)($item['valor_desejado'] ?? 0), 2, ',', '.'); ?>
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right; font-weight:900; color:<?php echo ((float)($item['percentual_ajuste'] ?? 0)) >= 0 ? '#15803d' : '#b91c1c'; ?>;">
                            <?php echo number_format((float)($item['percentual_ajuste'] ?? 0), 2, ',', '.'); ?>%
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);">
                            <?php echo htmlspecialchars((string)($item['observacao'] ?? '')); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
        const orcamentoId = <?= (int)($orcamento['id'] ?? 0) ?>;
        const valorAtual = <?= (float)($totais['total_cobranca'] ?? 0) ?>;

        function parseValorDesejado(raw) {
            if (raw == null) {
                return 0;
            }

            let s = String(raw).trim();
            if (s === '') {
                return 0;
            }

            s = s.replace(/\s+/g, '');
            s = s.replace(/^R\$?/i, '');

            let multiplier = 1;
            const last = s.slice(-1).toLowerCase();
            if (last === 'm') {
                multiplier = 1000000;
                s = s.slice(0, -1);
            } else if (last === 'k') {
                multiplier = 1000;
                s = s.slice(0, -1);
            }

            s = s.replace(/\./g, '');
            s = s.replace(',', '.');

            const num = parseFloat(s);
            if (!Number.isFinite(num)) {
                return 0;
            }

            return num * multiplier;
        }
        
        function calcularPreview() {
            const valorDesejado = parseValorDesejado(document.getElementById('valor_desejado').value);
            
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
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const msg = (data && (data.error || data.erro)) ? (data.error || data.erro) : 'Erro ao calcular preview.';
                    throw new Error(msg);
                }
                return data;
            })
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
            if (!data || !Array.isArray(data.etapas)) {
                const msg = (data && (data.error || data.erro)) ? (data.error || data.erro) : 'Resposta inválida do servidor.';
                alert('Erro ao calcular preview: ' + msg);
                return;
            }

            const diferenca = data.diferenca;
            const tipoAjuste = diferenca > 0 ? 'aumento' : 'redução';
            const classeDiferenca = diferenca > 0 ? 'valor-positivo' : 'valor-negativo';
            
            let html = `
                <div style="border:1px solid rgba(0,0,0,.12); background:${diferenca > 0 ? '#e8fff0' : '#fff9db'}; padding:12px; border-radius:12px; margin-bottom:12px;">
                    <div style="font-weight:900;">${tipoAjuste.toUpperCase()} de ${Math.abs(data.percentual_ajuste).toFixed(2)}%</div>
                    <div style="margin-top:6px;">Diferença: <strong style="color:${diferenca > 0 ? '#15803d' : '#b45309'};">R$ ${Math.abs(diferenca).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></div>
                </div>
                <div style="overflow:auto; border:1px solid rgba(0,0,0,.12); border-radius:12px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f3f4f6;">
                        <tr>
                            <th style="padding:10px; text-align:left; border-bottom:1px solid rgba(0,0,0,.10);">Etapa</th>
                            <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">Valor Atual</th>
                            <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">Valor Novo</th>
                            <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">Diferença</th>
                            <th style="padding:10px; text-align:right; border-bottom:1px solid rgba(0,0,0,.10);">% do Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.etapas.forEach(etapa => {
                const diferencaEtapa = etapa.diferenca;
                const classeDif = diferencaEtapa > 0 ? 'valor-positivo' : 'valor-negativo';
                
                html += `
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06);"><strong>${etapa.etapa}</strong></td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right;">R$ ${etapa.valor_atual.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right;">R$ ${etapa.valor_novo.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right; color:${diferencaEtapa > 0 ? '#15803d' : '#b91c1c'}; font-weight:900;">
                            ${diferencaEtapa > 0 ? '+' : ''}R$ ${diferencaEtapa.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.06); text-align:right;">${etapa.percentual.toFixed(2)}%</td>
                    </tr>
                `;
            });
            
            html += `
                    <tr style="background:#f3f4f6; font-weight:900;">
                        <td style="padding:10px;">TOTAL</td>
                        <td style="padding:10px; text-align:right;">R$ ${data.valor_atual.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="padding:10px; text-align:right;">R$ ${data.valor_desejado.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="padding:10px; text-align:right; color:${diferenca > 0 ? '#15803d' : '#b91c1c'};">${diferenca > 0 ? '+' : ''}R$ ${Math.abs(diferenca).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td style="padding:10px; text-align:right;">100.00%</td>
                    </tr>
                </tbody>
            </table>
            </div>
            `;
            
            document.getElementById('preview-content').innerHTML = html;
            document.getElementById('preview-section').style.display = 'block';
        }
        
        document.getElementById('form-adequacao').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const valorDesejado = parseValorDesejado(document.getElementById('valor_desejado').value);
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
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const msg = (data && (data.error || data.erro)) ? (data.error || data.erro) : 'Erro ao aplicar adequação.';
                    throw new Error(msg);
                }
                return data;
            })
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                if (data.sucesso) {
                    alert(data.mensagem || 'Adequação aplicada com sucesso.');
                    window.location.reload();
                } else {
                    alert('Erro: ' + (data.erro || 'Falha ao aplicar adequação.'));
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                alert('Erro ao aplicar adequação: ' + error.message);
            });
        });
    </script>

<style>
    #form-adequacao input,
    #form-adequacao textarea {
        background: #fff;
        color: #111;
        border: 1px solid rgba(0,0,0,.20);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        width: 100%;
        outline: none;
    }
    #form-adequacao input:focus,
    #form-adequacao textarea:focus {
        border-color: rgba(91,140,255,.75);
        box-shadow: 0 0 0 3px rgba(91,140,255,.18);
    }
    #form-adequacao label {
        color: #111;
        font-size: 12px;
        font-weight: 800;
        display: block;
        margin-bottom: 6px;
    }
</style>
