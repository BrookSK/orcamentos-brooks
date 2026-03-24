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

            <div class="field full">
                <label>
                    <input type="checkbox" name="use_template_items" value="1" <?php echo !empty($use_template_items) ? 'checked' : ''; ?>>
                    Criar com itens do template
                </label>
                <div class="muted" style="font-size:12px;">Você pode editar/remover os itens depois.</div>
            </div>

            <div class="field full" style="display:flex; justify-content:flex-end; gap:8px; flex-direction:row; align-items:center;">
                <a class="btn" href="/?route=orcamentos/index">Cancelar</a>
                <button class="btn primary" type="submit">Salvar</button>
            </div>
        </div>
    </form>
</div>
