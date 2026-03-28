<?php

declare(strict_types=1);

?><div class="card">
    <form method="post" action="/?route=orcamentos/storeSinapi" enctype="multipart/form-data">
        <div class="form">
            <div class="field full">
                <div style="font-weight:800; font-size:16px; margin-bottom:8px;">🧮 Novo Orçamento SINAPI</div>
                <div class="muted" style="font-size:12px;">Crie um orçamento usando a Calculadora SINAPI para elementos construtivos</div>
            </div>

            <div class="field">
                <label>Nº Proposta</label>
                <input name="numero_proposta" value="<?php echo htmlspecialchars((string)($orcamento['numero_proposta'] ?? '')); ?>">
                <?php if (!empty($errors['numero_proposta'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['numero_proposta']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Data</label>
                <input type="date" name="data" value="<?php echo htmlspecialchars((string)($orcamento['data'] ?? '')); ?>">
            </div>

            <div class="field">
                <label>Cliente</label>
                <input name="cliente_nome" value="<?php echo htmlspecialchars((string)($orcamento['cliente_nome'] ?? '')); ?>">
                <?php if (!empty($errors['cliente_nome'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['cliente_nome']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Arquiteto</label>
                <input name="arquiteto_nome" value="<?php echo htmlspecialchars((string)($orcamento['arquiteto_nome'] ?? '')); ?>">
            </div>

            <div class="field">
                <label>Obra</label>
                <input name="obra_nome" value="<?php echo htmlspecialchars((string)($orcamento['obra_nome'] ?? '')); ?>">
            </div>
            <div class="field">
                <label>Endereço da obra</label>
                <input name="endereco_obra" value="<?php echo htmlspecialchars((string)($orcamento['endereco_obra'] ?? '')); ?>">
            </div>

            <div class="field">
                <label>Local</label>
                <input name="local_obra" value="<?php echo htmlspecialchars((string)($orcamento['local_obra'] ?? '')); ?>">
            </div>
            <div class="field">
                <label>Área (m²)</label>
                <input name="area_m2" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['area_m2'] ?? '')); ?>">
            </div>

            <div class="field">
                <label>Contrato</label>
                <input name="contrato" value="<?php echo htmlspecialchars((string)($orcamento['contrato'] ?? '')); ?>">
            </div>
            <div class="field">
                <label>Prazo (dias)</label>
                <input name="prazo_dias" inputmode="numeric" value="<?php echo htmlspecialchars((string)($orcamento['prazo_dias'] ?? '')); ?>">
            </div>

            <div class="field">
                <label>Referência</label>
                <input name="referencia" value="<?php echo htmlspecialchars((string)($orcamento['referencia'] ?? '')); ?>">
            </div>
            <div class="field">
                <label>Rev.</label>
                <input name="rev" value="<?php echo htmlspecialchars((string)($orcamento['rev'] ?? '')); ?>">
            </div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;"></div>

            <div class="field">
                <label>Empresa</label>
                <input name="empresa_nome" value="<?php echo htmlspecialchars((string)($orcamento['empresa_nome'] ?? '')); ?>">
            </div>
            <div class="field">
                <label>Telefone</label>
                <input name="empresa_telefone" value="<?php echo htmlspecialchars((string)($orcamento['empresa_telefone'] ?? '')); ?>">
            </div>

            <div class="field full">
                <label>Endereço</label>
                <input name="empresa_endereco" value="<?php echo htmlspecialchars((string)($orcamento['empresa_endereco'] ?? '')); ?>">
            </div>

            <div class="field">
                <label>E-mail</label>
                <input name="empresa_email" value="<?php echo htmlspecialchars((string)($orcamento['empresa_email'] ?? '')); ?>">
            </div>

            <div class="field full">
                <label>Logo da Empresa (PNG)</label>
                <input type="file" name="logo" accept="image/png">
            </div>

            <div class="field full">
                <label>Capas Personalizadas (até 4 imagens A4)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                        <div>
                            <label style="font-size:12px;color:#999;">Capa <?php echo $i; ?></label>
                            <input type="file" name="capa_<?php echo $i; ?>" accept="image/*">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;">
                <div style="font-weight:700; font-size:13px; margin-bottom:8px; color:#C9973A;">⚙️ Configurações Administrativas</div>
            </div>

            <div class="field">
                <label>% Custos Administrativos</label>
                <input name="percentual_custos_adm" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['percentual_custos_adm'] ?? '5')); ?>" placeholder="5.00">
                <div class="muted" style="font-size:11px; margin-top:4px;">Percentual sobre valor total da obra</div>
            </div>

            <div class="field">
                <label>% Impostos</label>
                <input name="percentual_impostos" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['percentual_impostos'] ?? '8')); ?>" placeholder="8.00">
                <div class="muted" style="font-size:11px; margin-top:4px;">Percentual sobre valor total da obra</div>
            </div>

            <div class="field">
                <label>% Margem Mão de Obra (Padrão)</label>
                <input name="margem_mao_obra" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_mao_obra'] ?? '20')); ?>" placeholder="20">
                <div class="muted" style="font-size:11px; margin-top:4px;">Digite apenas o número: 20 para 20%</div>
            </div>

            <div class="field">
                <label>% Margem Materiais (Padrão)</label>
                <input name="margem_materiais" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_materiais'] ?? '25')); ?>" placeholder="25">
                <div class="muted" style="font-size:11px; margin-top:4px;">Digite apenas o número: 25 para 25%</div>
            </div>

            <div class="field">
                <label>% Margem Equipamentos (Padrão)</label>
                <input name="margem_equipamentos" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_equipamentos'] ?? '20')); ?>" placeholder="20">
                <div class="muted" style="font-size:11px; margin-top:4px;">Digite apenas o número: 20 para 20%</div>
            </div>

            <div class="field">
                <label>% Ajuste Pro Rata de Materiais</label>
                <input name="ajuste_prorata_materiais" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['ajuste_prorata_materiais'] ?? '0')); ?>" placeholder="0">
                <div class="muted" style="font-size:11px; margin-top:4px;">Reajuste sobre custo de materiais SINAPI</div>
            </div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;"></div>

            <div class="field full">
                <label style="font-size:16px; font-weight:600; color:#4FC3F7; margin-bottom:12px; display:block;">
                    📐 Áreas Personalizadas
                </label>
                <div class="muted" style="font-size:12px; margin-bottom:12px;">
                    Configure as áreas que aparecerão no PDF. Estas áreas serão usadas para calcular o preço por m².
                </div>
                <div id="areas-container">
                    <div class="area-row" style="display:grid; grid-template-columns:2fr 1fr 1fr 40px; gap:8px; margin-bottom:8px; align-items:end;">
                        <div>
                            <label style="font-size:11px; color:#999;">Nome da Área</label>
                            <input type="text" name="areas[0][nome]" value="AREA INTERNA" placeholder="Ex: AREA INTERNA" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">m²</label>
                            <input type="number" step="0.01" name="areas[0][m2]" placeholder="0.00" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">Fator</label>
                            <input type="number" step="0.01" name="areas[0][fator]" value="1" placeholder="1" style="width:100%;">
                        </div>
                        <button type="button" class="btn-remove-area" style="background:#f44336; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; font-size:16px;" onclick="removerArea(this)">🗑️</button>
                    </div>
                    <div class="area-row" style="display:grid; grid-template-columns:2fr 1fr 1fr 40px; gap:8px; margin-bottom:8px; align-items:end;">
                        <div>
                            <label style="font-size:11px; color:#999;">Nome da Área</label>
                            <input type="text" name="areas[1][nome]" value="VARANDA COBERTA" placeholder="Ex: VARANDA COBERTA" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">m²</label>
                            <input type="number" step="0.01" name="areas[1][m2]" placeholder="0.00" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">Fator</label>
                            <input type="number" step="0.01" name="areas[1][fator]" value="1" placeholder="1" style="width:100%;">
                        </div>
                        <button type="button" class="btn-remove-area" style="background:#f44336; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; font-size:16px;" onclick="removerArea(this)">🗑️</button>
                    </div>
                    <div class="area-row" style="display:grid; grid-template-columns:2fr 1fr 1fr 40px; gap:8px; margin-bottom:8px; align-items:end;">
                        <div>
                            <label style="font-size:11px; color:#999;">Nome da Área</label>
                            <input type="text" name="areas[2][nome]" value="ABRIGO AUTOS" placeholder="Ex: ABRIGO AUTOS" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">m²</label>
                            <input type="number" step="0.01" name="areas[2][m2]" placeholder="0.00" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">Fator</label>
                            <input type="number" step="0.01" name="areas[2][fator]" value="1" placeholder="1" style="width:100%;">
                        </div>
                        <button type="button" class="btn-remove-area" style="background:#f44336; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; font-size:16px;" onclick="removerArea(this)">🗑️</button>
                    </div>
                </div>
                <button type="button" class="btn" style="margin-top:8px;" onclick="adicionarArea()">➕ Adicionar Área</button>
            </div>

            <div class="field full" style="display:flex; justify-content:flex-end; gap:8px; flex-direction:row; align-items:center;">
                <a class="btn" href="/?route=orcamentos/index">Cancelar</a>
                <button class="btn primary" type="submit">Criar orçamento SINAPI</button>
            </div>
        </div>
    </form>
</div>

<script>
let areaIndex = 3;

function adicionarArea() {
    const container = document.getElementById('areas-container');
    const div = document.createElement('div');
    div.className = 'area-row';
    div.style.cssText = 'display:grid; grid-template-columns:2fr 1fr 1fr 40px; gap:8px; margin-bottom:8px; align-items:end;';
    
    div.innerHTML = `
        <div>
            <label style="font-size:11px; color:#999;">Nome da Área</label>
            <input type="text" name="areas[${areaIndex}][nome]" placeholder="Ex: AREA INTERNA" style="width:100%;">
        </div>
        <div>
            <label style="font-size:11px; color:#999;">m²</label>
            <input type="number" step="0.01" name="areas[${areaIndex}][m2]" placeholder="0.00" style="width:100%;">
        </div>
        <div>
            <label style="font-size:11px; color:#999;">Fator</label>
            <input type="number" step="0.01" name="areas[${areaIndex}][fator]" value="1" placeholder="1" style="width:100%;">
        </div>
        <button type="button" class="btn-remove-area" style="background:#f44336; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; font-size:16px;" onclick="removerArea(this)">🗑️</button>
    `;
    
    container.appendChild(div);
    areaIndex++;
}

function removerArea(btn) {
    const row = btn.closest('.area-row');
    if (row) {
        row.remove();
    }
}
</script>
