<?php

declare(strict_types=1);

?><div class="card">
    <?php if (!empty($errors['geral'])) : ?>
        <div style="background: #CC1F2D; color: white; padding: 12px 16px; margin-bottom: 16px; border-radius: 8px;">
            <strong>Erro:</strong> <?php echo htmlspecialchars((string)$errors['geral']); ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/?route=orcamentos/update" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int)($orcamento['id'] ?? 0); ?>">
        <input type="hidden" name="tipo_orcamento" value="<?php echo htmlspecialchars((string)($orcamento['tipo_orcamento'] ?? 'manual')); ?>">
        <div class="form">
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

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;"></div>

            <div class="field">
                <label>% Custos Administrativos</label>
                <input name="percentual_custos_adm" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['percentual_custos_adm'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Percentual sobre o valor da obra (ex: 5.5 para 5,5%)</div>
            </div>
            <div class="field">
                <label>% Impostos</label>
                <input name="percentual_impostos" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['percentual_impostos'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Percentual sobre o valor da obra (ex: 8.65 para 8,65%)</div>
            </div>
            
            <div class="field">
                <label>Valor de Entrada (R$)</label>
                <input name="valor_entrada" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['valor_entrada'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Valor já pago pelo cliente como entrada (ex: 50000.00)</div>
            </div>

            <div class="field">
                <label>% Margem Mão de Obra (Padrão)</label>
                <input name="margem_mao_obra" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_mao_obra'] ?? '50')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">
                    Margem de lucro padrão para itens de mão de obra<br>
                    <strong>Digite apenas o número:</strong> 20 para 20%, 25 para 25%, 30 para 30%<br>
                    <strong style="color:#c00;">NÃO digite:</strong> 200, 0.20, ou 20%
                </div>
            </div>
            <div class="field">
                <label>% Margem Materiais (Padrão)</label>
                <input name="margem_materiais" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_materiais'] ?? '20')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">
                    Margem de lucro padrão para materiais<br>
                    <strong>Digite apenas o número:</strong> 20 para 20%, 25 para 25%, 30 para 30%<br>
                    <strong style="color:#c00;">NÃO digite:</strong> 200, 0.20, ou 20%
                </div>
            </div>

            <div class="field">
                <label>% Margem Equipamentos (Padrão)</label>
                <input name="margem_equipamentos" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_equipamentos'] ?? '20')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">
                    Margem de lucro padrão para equipamentos/locação<br>
                    <strong>Digite apenas o número:</strong> 20 para 20%, 25 para 25%, 30 para 30%<br>
                    <strong style="color:#c00;">NÃO digite:</strong> 200, 0.20, ou 20%
                </div>
            </div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;"></div>

            <div class="field">
                <label>% Ajuste Pro Rata de Materiais</label>
                <input name="ajuste_prorata_materiais" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['ajuste_prorata_materiais'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">
                    Percentual de reajuste aplicado sobre o custo de materiais do SINAPI<br>
                    <strong>Exemplo:</strong> Se o SINAPI traz R$ 100,00 e você digitar 1, o custo será R$ 101,00<br>
                    <strong>Digite apenas o número:</strong> 1 para 1%, 2.5 para 2,5%, 5 para 5%<br>
                    <strong style="color:#c00;">NÃO digite:</strong> 100, 0.01, ou 1%
                </div>
            </div>
            <div class="field"></div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;"></div>

            <div class="field full">
                <label style="font-size:16px; font-weight:600; color:#4FC3F7; margin-bottom:12px; display:block;">
                    📐 Áreas Personalizadas
                </label>
                <div class="muted" style="font-size:12px; margin-bottom:12px;">
                    Configure as áreas que aparecerão no PDF. Estas áreas serão usadas para calcular o preço por m².
                </div>
                <div id="areas-container">
                    <?php 
                    $areasPersonalizadas = [];
                    if (!empty($orcamento['areas_personalizadas'])) {
                        $areasPersonalizadas = json_decode($orcamento['areas_personalizadas'], true);
                        if (!is_array($areasPersonalizadas)) {
                            $areasPersonalizadas = [];
                        }
                    }
                    
                    // Se não tiver áreas, adicionar áreas padrão
                    if (empty($areasPersonalizadas)) {
                        $areasPersonalizadas = [
                            ['nome' => 'AREA INTERNA', 'm2' => '', 'fator' => '1'],
                            ['nome' => 'VARANDA COBERTA', 'm2' => '', 'fator' => '1'],
                            ['nome' => 'ABRIGO AUTOS', 'm2' => '', 'fator' => '1'],
                            ['nome' => 'AREA DESCOBERTA', 'm2' => '', 'fator' => '1'],
                            ['nome' => 'PISCINA', 'm2' => '', 'fator' => '1'],
                        ];
                    }
                    
                    foreach ($areasPersonalizadas as $index => $area) :
                    ?>
                    <div class="area-row" style="display:grid; grid-template-columns:2fr 1fr 1fr 150px 40px; gap:8px; margin-bottom:8px; align-items:end;">
                        <div>
                            <label style="font-size:11px; color:#999;">Nome da Área</label>
                            <input type="text" name="areas[<?php echo $index; ?>][nome]" value="<?php echo htmlspecialchars($area['nome'] ?? ''); ?>" placeholder="Ex: AREA INTERNA" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">m²</label>
                            <input type="number" step="0.01" name="areas[<?php echo $index; ?>][m2]" value="<?php echo htmlspecialchars((string)($area['m2'] ?? '')); ?>" placeholder="0.00" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">Fator</label>
                            <input type="number" step="0.01" name="areas[<?php echo $index; ?>][fator]" value="<?php echo htmlspecialchars((string)($area['fator'] ?? '1')); ?>" placeholder="1" style="width:100%;">
                        </div>
                        <div>
                            <label style="font-size:11px; color:#999;">Tipo de Área</label>
                            <?php $tipoArea = (string)($area['tipo_area'] ?? 'terreno'); ?>
                            <select name="areas[<?php echo $index; ?>][tipo_area]" style="width:100%; padding:8px;">
                                <option value="terreno" <?php echo $tipoArea === 'terreno' ? 'selected' : ''; ?>>Terreno</option>
                                <option value="terrea" <?php echo $tipoArea === 'terrea' ? 'selected' : ''; ?>>Construída Térrea</option>
                                <option value="superior" <?php echo $tipoArea === 'superior' ? 'selected' : ''; ?>>Construída Superior</option>
                                <option value="nao_somar" <?php echo $tipoArea === 'nao_somar' ? 'selected' : ''; ?>>❌ Não somar à área construída</option>
                            </select>
                        </div>
                        <button type="button" class="btn-remove-area" style="background:#f44336; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; font-size:16px;" onclick="removerArea(this)">🗑️</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn" style="margin-top:8px;" onclick="adicionarArea()">➕ Adicionar Área</button>
            </div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:12px; margin-top:4px;"></div>
            <div class="field full">
                <label>Logo da Empresa (PNG)</label>
                <input type="file" name="logo" accept="image/png">
                <?php if (!empty($orcamento['logo_path'])) : ?>
                    <div style="margin-top:8px;">
                        <img src="<?php echo htmlspecialchars((string)$orcamento['logo_path']); ?>" style="max-width:200px;max-height:80px;" alt="Logo atual">
                        <div class="muted" style="font-size:12px;margin-top:4px;">Logo atual. Faça upload de uma nova para substituir.</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="field full">
                <label>Capas Personalizadas (até 4 imagens A4)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                        <div>
                            <label style="font-size:12px;color:#999;">Capa <?php echo $i; ?></label>
                            <input type="file" name="capa_<?php echo $i; ?>" accept="image/*">
                            <?php if (!empty($orcamento['capa_path_' . $i])) : ?>
                                <div style="margin-top:4px;">
                                    <img src="<?php echo htmlspecialchars((string)$orcamento['capa_path_' . $i]); ?>" style="max-width:100%;max-height:140px;" alt="Capa <?php echo $i; ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="muted" style="font-size:12px;margin-top:8px;">Faça upload de até 4 imagens tamanho A4 para usar como capas do PDF exportado.</div>
            </div>

            <div class="field full" style="border-top:1px solid rgba(255,255,255,.08); padding-top:16px; margin-top:12px;">
                <label style="display:flex; align-items:center; gap:12px; cursor:pointer; background:rgba(201,151,58,0.1); padding:16px; border-radius:8px; border:2px solid rgba(201,151,58,0.3);">
                    <input type="checkbox" name="tornar_template" value="1" style="width:auto; margin:0;">
                    <div>
                        <div style="font-size:15px; font-weight:700; color:#C9973A;">
                            ⭐ Tornar esse o novo template padrão
                        </div>
                        <div class="muted" style="font-size:12px; margin-top:4px;">
                            Ao marcar esta opção, todos os itens deste orçamento serão salvos como template. 
                            Novos orçamentos criados com template usarão estes itens como base.
                        </div>
                    </div>
                </label>
            </div>

            <div class="field full" style="display:flex; justify-content:flex-end; gap:8px; flex-direction:row; align-items:center;">
                <a class="btn" href="/?route=orcamentos/show&id=<?php echo (int)($orcamento['id'] ?? 0); ?>">Cancelar</a>
                <button class="btn primary" type="submit">Atualizar</button>
            </div>
        </div>
    </form>
</div>

<script>
let areaIndex = <?php echo count($areasPersonalizadas); ?>;

function adicionarArea() {
    const container = document.getElementById('areas-container');
    const div = document.createElement('div');
    div.className = 'area-row';
    div.style.cssText = 'display:grid; grid-template-columns:2fr 1fr 1fr 150px 40px; gap:8px; margin-bottom:8px; align-items:end;';
    
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
        <div>
            <label style="font-size:11px; color:#999;">Tipo de Área</label>
            <select name="areas[${areaIndex}][tipo_area]" style="width:100%; padding:8px;">
                <option value="terreno">Terreno</option>
                <option value="terrea">Construída Térrea</option>
                <option value="superior">Construída Superior</option>
                <option value="nao_somar">❌ Não somar à área construída</option>
            </select>
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
