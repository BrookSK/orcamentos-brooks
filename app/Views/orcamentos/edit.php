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
                <label>% Margem Mão de Obra (Padrão)</label>
                <input name="margem_mao_obra" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_mao_obra'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">
                    Margem de lucro padrão para itens de mão de obra<br>
                    <strong>Digite apenas o número:</strong> 20 para 20%, 25 para 25%, 30 para 30%<br>
                    <strong style="color:#c00;">NÃO digite:</strong> 200, 0.20, ou 20%
                </div>
            </div>
            <div class="field">
                <label>% Margem Materiais (Padrão)</label>
                <input name="margem_materiais" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_materiais'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">
                    Margem de lucro padrão para materiais<br>
                    <strong>Digite apenas o número:</strong> 20 para 20%, 25 para 25%, 30 para 30%<br>
                    <strong style="color:#c00;">NÃO digite:</strong> 200, 0.20, ou 20%
                </div>
            </div>

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

            <div class="field full" style="display:flex; justify-content:flex-end; gap:8px; flex-direction:row; align-items:center;">
                <a class="btn" href="/?route=orcamentos/show&id=<?php echo (int)($orcamento['id'] ?? 0); ?>">Cancelar</a>
                <button class="btn primary" type="submit">Atualizar</button>
            </div>
        </div>
    </form>
</div>

