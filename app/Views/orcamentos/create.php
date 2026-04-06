<?php

declare(strict_types=1);

?><div class="card">
    <form method="post" action="/?route=orcamentos/store" enctype="multipart/form-data">
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
                <label>% Margem Mão de Obra (Padrão)</label>
                <input name="margem_mao_obra" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_mao_obra'] ?? '50')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Margem de lucro padrão para mão de obra (ex: 50 para 50%)</div>
            </div>
            <div class="field">
                <label>% Margem Materiais (Padrão)</label>
                <input name="margem_materiais" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_materiais'] ?? '20')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Margem de lucro padrão para materiais (ex: 20 para 20%)</div>
            </div>
            <div class="field">
                <label>% Margem Equipamentos (Padrão)</label>
                <input name="margem_equipamentos" inputmode="decimal" value="<?php echo htmlspecialchars((string)($orcamento['margem_equipamentos'] ?? '20')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Margem de lucro padrão para equipamentos (ex: 20 para 20%)</div>
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

            <div class="field full">
                <label>Logo da Empresa (PNG)</label>
                <input type="file" name="logo" accept="image/png">
                <div class="muted" style="font-size:12px;margin-top:4px;">Faça upload de uma logo em PNG para substituir o texto "BROOKS CONSTRUTORA" no PDF.</div>
            </div>

            <div class="field full">
                <label>Capas Personalizadas (até 4 imagens A4)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
                    <div>
                        <label style="font-size:12px;color:#999;">Capa 1</label>
                        <input type="file" name="capa_1" accept="image/*">
                    </div>
                    <div>
                        <label style="font-size:12px;color:#999;">Capa 2</label>
                        <input type="file" name="capa_2" accept="image/*">
                    </div>
                    <div>
                        <label style="font-size:12px;color:#999;">Capa 3</label>
                        <input type="file" name="capa_3" accept="image/*">
                    </div>
                    <div>
                        <label style="font-size:12px;color:#999;">Capa 4</label>
                        <input type="file" name="capa_4" accept="image/*">
                    </div>
                </div>
                <div class="muted" style="font-size:12px;margin-top:8px;">Faça upload de até 4 imagens tamanho A4 para usar como capas do PDF exportado.</div>
            </div>

            <div class="field full">
                <label>Capa Personalizada (Imagem A4)</label>
                <input type="file" name="capa" accept="image/*">
                <div class="muted" style="font-size:12px;margin-top:4px;">Faça upload de uma imagem tamanho A4 para usar como capa do PDF exportado.</div>
            </div>

            <div class="field full">
                <label>
                    <input type="checkbox" name="use_template_items" value="1" <?php echo !empty($use_template_items) ? 'checked' : ''; ?>>
                    Criar com itens do template
                </label>
                <div class="muted" style="font-size:12px;">Você pode editar/remover os itens depois.</div>
            </div>

            <div class="field full">
                <label>
                    <input type="checkbox" name="save_as_template" value="1">
                    Tornar esse o novo template padrão
                </label>
                <div class="muted" style="font-size:12px;">Ao salvar, os itens deste orçamento substituirão o template atual usado para criar novos orçamentos.</div>
            </div>

            <div class="field full" style="display:flex; justify-content:flex-end; gap:8px; flex-direction:row; align-items:center;">
                <a class="btn" href="/?route=orcamentos/index">Cancelar</a>
                <button class="btn primary" type="submit">Salvar</button>
            </div>
        </div>
    </form>
</div>
