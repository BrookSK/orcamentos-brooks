<?php

declare(strict_types=1);

use App\Models\OrcamentoItem;

$itens = $itens ?? [];

$grouped = [];
foreach ($itens as $it) {
    $grupo = (string)($it['grupo'] ?? '');
    $categoria = (string)($it['categoria'] ?? '');
    $grouped[$grupo][$categoria][] = $it;
}

$totalGeral = 0.0;

?><div class="card" style="padding:16px; margin-bottom:12px;" data-orcamento-id="<?php echo (int)$orcamento['id']; ?>">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
        <div>
            <div style="font-weight:800; font-size:14px;">Orçamento: <?php echo htmlspecialchars((string)$orcamento['numero_proposta']); ?></div>
            <div class="muted" style="margin-top:4px; font-size:12px;">
                Cliente: <?php echo htmlspecialchars((string)$orcamento['cliente_nome']); ?>
                <?php if (!empty($orcamento['obra_nome'])) : ?>
                    · Obra: <?php echo htmlspecialchars((string)$orcamento['obra_nome']); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="actions">
            <a class="btn" href="/?route=orcamentos/index">Voltar</a>
            <a class="btn" href="/?route=orcamentos/edit&id=<?php echo (int)$orcamento['id']; ?>">Editar cabeçalho</a>
            <button class="btn" style="background: #2196F3; color: white;" onclick="recalcularMargens(<?php echo (int)$orcamento['id']; ?>)">🔄 Recalcular Margens</button>
            <a class="btn" style="background: #e94560; color: white;" href="/?route=orcamentos/adequacao&id=<?php echo (int)$orcamento['id']; ?>">💰 Ajustar Valor do Contrato</a>
            <!-- <a class="btn" href="/?route=orcamentos/print&id=<?php echo (int)$orcamento['id']; ?>" target="_blank">Imprimir</a> -->
            <a class="btn primary" href="/?route=orcamentos/pdf&id=<?php echo (int)$orcamento['id']; ?>" target="_blank">Exportar PDF</a>
            <a class="btn" style="background: #C9973A; color: white;" href="/?route=orcamentos/pdfAdmin&id=<?php echo (int)$orcamento['id']; ?>" target="_blank">📊 PDF Administrativo</a>
        </div>
    </div>
</div>

<div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="font-weight:800; margin-bottom:10px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleCalculadora()">
        <span>🧮 Calculadora SINAPI - Adicionar elementos construtivos</span>
        <span id="toggle-calc-icon" style="font-size: 20px;">▼</span>
    </div>
    <div id="calculadora-sinapi" style="display: none;">
        <div id="sinapi-step1">
            <div style="font-size:11px; font-weight:700; letter-spacing:2px; color:#C9973A; margin-bottom:8px;">PASSO 1</div>
            <div style="font-size:16px; font-weight:800; margin-bottom:16px;">O que você vai construir?</div>
            <div id="sinapi-element-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:10px; margin-bottom:20px;"></div>
        </div>

        <div id="sinapi-step2" style="display:none;">
            <div style="font-size:11px; font-weight:700; letter-spacing:2px; color:#C9973A; margin-bottom:8px;">PASSO 2</div>
            <div style="font-size:16px; font-weight:800; margin-bottom:16px;">Informe as medidas</div>
            <div style="background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.1); border-radius:10px; padding:20px; margin-bottom:16px;">
                <div style="display:flex; gap:12px; margin-bottom:16px; align-items:flex-start;">
                    <div id="sinapi-el-icon" style="font-size:32px;"></div>
                    <div style="flex:1;">
                        <div id="sinapi-el-name" style="font-size:16px; font-weight:800;"></div>
                        <div id="sinapi-el-desc" style="font-size:11px; color:#999; margin-top:4px;"></div>
                    </div>
                    <button onclick="voltarStep1SINAPI()" style="font-size:11px; padding:6px 12px; border:1px solid rgba(255,255,255,.1); border-radius:6px; background:rgba(255,255,255,.04); cursor:pointer; color:#999;">← Voltar</button>
                </div>
                <div id="sinapi-dims-fields" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px; margin-bottom:16px;"></div>
                <div id="sinapi-extras-container"></div>
                <button class="btn primary" style="width:100%; padding:12px;" onclick="calcularSINAPI()">📋 Gerar Estimativa de Materiais</button>
            </div>
        </div>

        <div id="sinapi-resultado" style="display:none;">
            <div style="background:linear-gradient(135deg,#1a1916 0%,#2d2a24 100%); border-radius:10px; padding:20px; margin-bottom:16px; color:#fff;">
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                    <div>
                        <div style="font-size:9px; letter-spacing:2px; color:#888; margin-bottom:6px;">ELEMENTO</div>
                        <div id="sinapi-res-el" style="font-size:14px; font-weight:800; color:#C9973A;"></div>
                        <div id="sinapi-res-dims" style="font-size:10px; color:#888; margin-top:4px;"></div>
                    </div>
                    <div>
                        <div style="font-size:9px; letter-spacing:2px; color:#888; margin-bottom:6px;">QUANTIDADE</div>
                        <div id="sinapi-res-qty" style="font-size:20px; font-weight:800; color:#C9973A;"></div>
                        <div id="sinapi-res-un" style="font-size:10px; color:#888; margin-top:4px;"></div>
                    </div>
                    <div>
                        <div style="font-size:9px; letter-spacing:2px; color:#888; margin-bottom:6px;">TOTAL ESTIMADO</div>
                        <div id="sinapi-res-total" style="font-size:20px; font-weight:800; color:#C9973A;"></div>
                        <div style="font-size:10px; color:#888; margin-top:4px;">incluindo M+MO+EQ</div>
                    </div>
                </div>
            </div>

            <div style="background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.1); border-radius:10px; padding:16px; margin-bottom:16px;">
                <div style="font-size:13px; font-weight:800; margin-bottom:12px;">Lista de materiais calculados</div>
                
                <div style="display:flex; gap:8px; margin-bottom:12px;">
                    <button onclick="selecionarTodosSINAPI(true); return false;" style="flex:1; padding:8px; border:1px solid rgba(255,255,255,.1); border-radius:6px; background:rgba(255,255,255,.04); cursor:pointer; color:#999; font-size:11px;">
                        ☑ Selecionar todos
                    </button>
                    <button onclick="selecionarTodosSINAPI(false); return false;" style="flex:1; padding:8px; border:1px solid rgba(255,255,255,.1); border-radius:6px; background:rgba(255,255,255,.04); cursor:pointer; color:#999; font-size:11px;">
                        ☐ Desmarcar todos
                    </button>
                </div>

                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,.1);">
                            <th style="padding:8px; text-align:center; font-size:10px; color:#999; width:40px;">✓</th>
                            <th style="padding:8px; text-align:left; font-size:10px; color:#999;">Tipo</th>
                            <th style="padding:8px; text-align:left; font-size:10px; color:#999;">Material / Insumo</th>
                            <th style="padding:8px; text-align:right; font-size:10px; color:#999;">Quantidade</th>
                            <th style="padding:8px; text-align:center; font-size:10px; color:#999; width:70px;">Unid.</th>
                            <th style="padding:8px; text-align:right; font-size:10px; color:#999;">Valor Unit.</th>
                            <th style="padding:8px; text-align:right; font-size:10px; color:#999;">Subtotal</th>
                            <th style="padding:8px; text-align:center; font-size:10px; color:#999; width:60px;">Fonte</th>
                        </tr>
                    </thead>
                    <tbody id="sinapi-mat-tbody"></tbody>
                </table>
            </div>

            <button class="btn primary" style="width:100%; padding:14px; font-size:14px;" onclick="abrirModalCategoria()">
                ✓ Adicionar itens selecionados ao orçamento
            </button>
            <button onclick="voltarStep1SINAPI()" style="width:100%; margin-top:8px; padding:10px; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:rgba(255,255,255,.04); cursor:pointer; color:#999; font-size:12px;">
                ← Calcular outro elemento
            </button>
        </div>

        <div style="margin-top:12px; padding:12px; background:rgba(201,151,58,.1); border-radius:8px; border:1px solid rgba(201,151,58,.3);">
            <div style="font-size:12px; color:#C9973A; font-weight:600; margin-bottom:6px;">💡 Como usar:</div>
            <div style="font-size:11px; color:#999; line-height:1.6;">
                1. Escolha um elemento construtivo (muro, laje, piso, telhado, fundação, etc.)<br>
                2. Informe as dimensões e opções desejadas<br>
                3. Clique em "Gerar Estimativa de Materiais"<br>
                4. Revise os materiais, mão de obra e equipamentos calculados<br>
                5. Clique em "Adicionar itens selecionados ao orçamento" para importar
            </div>
        </div>
    </div>
</div>

<div class="card" style="padding:16px; margin-bottom:12px;">
    <div style="font-weight:800; margin-bottom:10px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleAdicionarItem()">
        <span>Adicionar item manualmente</span>
        <span id="toggle-icon" style="font-size: 20px;">▼</span>
    </div>
    <div id="form-adicionar-item" style="display: none;">
    <form method="post" action="/?route=orcamentos/itemStore">
        <input type="hidden" name="orcamento_id" value="<?php echo (int)$orcamento['id']; ?>">
        <div class="form" style="padding:0;">
            <div class="field">
                <label>Grupo</label>
                <?php $grupos = $grupos ?? []; ?>
                <?php $currentGrupo = (string)($item['grupo'] ?? ''); ?>
                <select name="grupo">
                    <option value="">Selecione</option>
                    <?php if ($currentGrupo !== '' && !in_array($currentGrupo, $grupos, true)) : ?>
                        <option value="<?php echo htmlspecialchars($currentGrupo); ?>" selected><?php echo htmlspecialchars($currentGrupo); ?></option>
                    <?php endif; ?>
                    <?php foreach ($grupos as $g) : ?>
                        <option value="<?php echo htmlspecialchars((string)$g); ?>" <?php echo ((string)$g === $currentGrupo) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$g); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['grupo'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['grupo']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Categoria</label>
                <?php $categorias = $categorias ?? []; ?>
                <?php $currentCategoria = (string)($item['categoria'] ?? ''); ?>
                <select name="categoria">
                    <option value="">Selecione</option>
                    <?php if ($currentCategoria !== '' && !in_array($currentCategoria, $categorias, true)) : ?>
                        <option value="<?php echo htmlspecialchars($currentCategoria); ?>" selected><?php echo htmlspecialchars($currentCategoria); ?></option>
                    <?php endif; ?>
                    <?php foreach ($categorias as $c) : ?>
                        <option value="<?php echo htmlspecialchars((string)$c); ?>" <?php echo ((string)$c === $currentCategoria) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$c); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['categoria'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['categoria']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Tipo de Custo <span style="color:#f44336;">*</span></label>
                <?php $currentClassificacao = (string)($item['classificacao_custo'] ?? ''); ?>
                <select name="classificacao_custo" required>
                    <option value="">Selecione o tipo de custo</option>
                    <option value="material" <?php echo $currentClassificacao === 'material' ? 'selected' : ''; ?>>Material (usa margem de materiais)</option>
                    <option value="mao_obra" <?php echo $currentClassificacao === 'mao_obra' ? 'selected' : ''; ?>>Mão de Obra (usa margem de mão de obra)</option>
                    <option value="equipamento" <?php echo $currentClassificacao === 'equipamento' ? 'selected' : ''; ?>>Equipamento (usa margem de equipamentos)</option>
                </select>
                <?php if (!empty($errors['classificacao_custo'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['classificacao_custo']); ?></div><?php endif; ?>
                <div class="muted" style="font-size:12px;margin-top:4px;">Define qual margem do cabeçalho será aplicada (se não usar margem personalizada)</div>
            </div>

            <div class="field">
                <label>Código</label>
                <input name="codigo" value="<?php echo htmlspecialchars((string)($item['codigo'] ?? '')); ?>">
                <?php if (!empty($errors['codigo'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['codigo']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Ordem (opcional)</label>
                <input name="ordem" inputmode="numeric" value="<?php echo htmlspecialchars((string)($item['ordem'] ?? '0')); ?>">
            </div>

            <div class="field full">
                <label>Descrição</label>
                <textarea name="descricao" rows="6" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); color:var(--text); outline:none; resize:vertical;"><?php echo htmlspecialchars((string)($item['descricao'] ?? '')); ?></textarea>
                <?php if (!empty($errors['descricao'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['descricao']); ?></div><?php endif; ?>
                <div class="muted" style="font-size:12px; margin-top:6px;">Dica: para múltiplas linhas, você pode colar com quebras de linha e o PDF preserva.</div>
            </div>

            <div class="field">
                <label>Quantidade</label>
                <input name="quantidade" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['quantidade'] ?? '')); ?>">
                <?php if (!empty($errors['quantidade'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['quantidade']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label>Unidade</label>
                <?php $unidades = $unidades ?? []; ?>
                <?php $currentUnidade = (string)($item['unidade'] ?? ''); ?>
                <select name="unidade">
                    <option value="">Selecione</option>
                    <?php if ($currentUnidade !== '' && !in_array($currentUnidade, $unidades, true)) : ?>
                        <option value="<?php echo htmlspecialchars($currentUnidade); ?>" selected><?php echo htmlspecialchars($currentUnidade); ?></option>
                    <?php endif; ?>
                    <?php foreach ($unidades as $u) : ?>
                        <option value="<?php echo htmlspecialchars((string)$u); ?>" <?php echo ((string)$u === $currentUnidade) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$u); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['unidade'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['unidade']); ?></div><?php endif; ?>
            </div>

            <div class="field">
                <label>Valor unitário (custo)</label>
                <input name="valor_unitario" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['valor_unitario'] ?? '')); ?>">
                <?php if (!empty($errors['valor_unitario'])) : ?><div class="error"><?php echo htmlspecialchars((string)$errors['valor_unitario']); ?></div><?php endif; ?>
                <div class="muted" style="font-size:12px;margin-top:4px;">Custo base do item (sem margem)</div>
            </div>

            <div class="field">
                <label>
                    <input type="checkbox" name="usa_margem_personalizada" value="1" <?php echo !empty($item['usa_margem_personalizada']) ? 'checked' : ''; ?> style="width:auto;margin-right:6px;">
                    Usar margem personalizada
                </label>
            </div>

            <div class="field">
                <label>% Margem Personalizada</label>
                <input name="margem_personalizada" inputmode="decimal" value="<?php echo htmlspecialchars((string)($item['margem_personalizada'] ?? '0')); ?>">
                <div class="muted" style="font-size:12px;margin-top:4px;">Deixe 0 para usar margem global do orçamento (ex: 25 para 25%)</div>
            </div>

            <div class="field" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                <button class="btn primary" type="submit">Adicionar</button>
            </div>
        </div>
    </form>
    </div>
</div>

<script>
function toggleAdicionarItem() {
    const form = document.getElementById('form-adicionar-item');
    const icon = document.getElementById('toggle-icon');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        icon.textContent = '▲';
    } else {
        form.style.display = 'none';
        icon.textContent = '▼';
    }
}
</script>

<div class="card">
    <div style="margin-bottom: 12px; display: flex; gap: 8px; align-items: center;">
        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
            <input type="checkbox" id="toggle-admin-columns" style="width: auto;" checked>
            <span style="font-weight: 600;">Mostrar colunas administrativas (custos, margens, % BDI)</span>
        </label>
    </div>
    
    <div style="overflow-x: auto;">
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table id="orcamento-table" style="table-layout: auto; width: 100%;">
        <thead>
        <tr>
            <th style="width:30px;"></th>
            <th style="width:60px">Código</th>
            <th style="width:auto; min-width:150px;">Descrição</th>
            <th class="center" style="width:45px">Un</th>
            <th class="center" style="width:60px">Qtd</th>
            <th class="num admin-col" style="width:80px;">Custo Mat.</th>
            <th class="num admin-col" style="width:80px;">Custo M.O.</th>
            <th class="num admin-col" style="width:80px;">Custo Equip.</th>
            <th class="center admin-col" style="width:55px;">% BDI</th>
            <th class="num admin-col" style="width:80px;">Margem Un.</th>
            <th class="num" style="width:85px">Vlr Unit.</th>
            <th class="num admin-col" style="width:85px;">Lucro Total</th>
            <th class="num" style="width:85px">Vlr Total</th>
            <th style="width:120px"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($grouped)) : ?>
            <tr><td colspan="14" class="muted">Nenhum item cadastrado neste orçamento.</td></tr>
        <?php endif; ?>

        <?php 
        // Buscar margens globais do orçamento
        $margemMaoObraGlobal = (float)($orcamento['margem_mao_obra'] ?? 50);
        $margemMateriaisGlobal = (float)($orcamento['margem_materiais'] ?? 20);
        $margemEquipamentosGlobal = (float)($orcamento['margem_equipamentos'] ?? 20);
        
        // DEBUG: Mostrar margens carregadas
        echo "<!-- DEBUG MARGENS: MO={$margemMaoObraGlobal}% | MAT={$margemMateriaisGlobal}% | EQ={$margemEquipamentosGlobal}% -->\n";
        ?>

        <?php foreach ($grouped as $grupo => $cats) : ?>
            <tr class="group-row" draggable="true" data-grupo="<?php echo htmlspecialchars($grupo); ?>" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%); cursor:move;">
                <td style="cursor:move; text-align:center; width:30px; color:#4FC3F7; font-size:18px; padding:10px;">⋮⋮</td>
                <td colspan="11" style="cursor:move; font-weight:800; padding:12px; font-size:15px; color:#fff; letter-spacing:1px;">
                    <?php echo htmlspecialchars($grupo !== '' ? $grupo : 'SEM GRUPO'); ?>
                </td>
                <td colspan="2" style="padding:8px; text-align:right;">
                    <button class="btn" style="padding:6px 12px; font-size:12px; background:#4FC3F7; color:#000;" onclick="editarDescontoGrupo('<?php echo htmlspecialchars($grupo); ?>', <?php echo (int)$orcamento['id']; ?>); event.stopPropagation();">
                        ⚙️ Ajuste de Valores
                    </button>
                </td>
            </tr>

            <?php foreach ($cats as $categoria => $rows) : ?>
                <tr class="subtotal-row category-header" draggable="true" data-categoria="<?php echo htmlspecialchars($categoria); ?>" data-grupo="<?php echo htmlspecialchars($grupo); ?>" style="background: rgba(201, 151, 58, 0.15);">
                    <td style="cursor:move; text-align:center; width:30px; color:#C9973A; font-size:16px; padding:8px;">⋮⋮</td>
                    <td colspan="13" style="cursor:move; font-weight:700; padding:10px;">
                        <?php echo htmlspecialchars($categoria !== '' ? $categoria : 'SEM CATEGORIA'); ?>
                    </td>
                </tr>
                
                <!-- Repetir cabeçalho da tabela para cada categoria -->
                <tr class="category-table-header">
                    <th style="width:30px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);"></th>
                    <th style="width:60px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Código</th>
                    <th style="width:auto; min-width:150px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Descrição</th>
                    <th class="center" style="width:45px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Un</th>
                    <th class="center" style="width:60px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Qtd</th>
                    <th class="num admin-col" style="width:80px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Custo Mat.</th>
                    <th class="num admin-col" style="width:80px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Custo M.O.</th>
                    <th class="num admin-col" style="width:80px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Custo Equip.</th>
                    <th class="center admin-col" style="width:55px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">% BDI</th>
                    <th class="num admin-col" style="width:80px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Margem Un.</th>
                    <th class="num" style="width:85px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Vlr Unit.</th>
                    <th class="num admin-col" style="width:85px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Lucro Total</th>
                    <th class="num" style="width:85px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);">Vlr Total</th>
                    <th style="width:120px; text-align:left; font-size:12px; color:var(--muted); font-weight:600; padding:12px; border-bottom:1px solid var(--line); background:rgba(255,255,255,.03);"></th>
                </tr>

                <?php $subtotalCategoria = 0.0; ?>
                <?php foreach ($rows as $row) : ?>
                    <?php
                        $quantidade = (float)($row['quantidade'] ?? 0);
                        $custoMaterialTotal = (float)($row['custo_material'] ?? 0);
                        $custoMaoObraTotal = (float)($row['custo_mao_obra'] ?? 0);
                        $margemPersonalizada = (float)($row['margem_personalizada'] ?? 0);
                        $usaMargemPersonalizada = (int)($row['usa_margem_personalizada'] ?? 0);
                        $valorUnitario = (float)($row['valor_unitario'] ?? 0);
                        $valorCobranca = (float)($row['valor_cobranca'] ?? 0);
                        
                        // Fallback para valor_unitario se valor_cobranca não existir
                        if ($valorCobranca == 0) {
                            $valorCobranca = $valorUnitario;
                        }
                        
                        // Calcular custos unitários
                        // Os custos são sempre salvos como UNITÁRIOS no banco
                        $custoMaterialUnit = $custoMaterialTotal;
                        $custoMaoObraUnit = $custoMaoObraTotal;
                        
                        // Aplicar ajuste pro rata de materiais
                        $ajusteProRata = (float)($orcamento['ajuste_prorata_materiais'] ?? 0);
                        if ($ajusteProRata > 0 && $custoMaterialUnit > 0) {
                            $custoMaterialUnit = $custoMaterialUnit * (1 + ($ajusteProRata / 100));
                        }
                        
                        $custoUnitTotal = $custoMaterialUnit + $custoMaoObraUnit;
                        
                        // Se não há custo detalhado, usar valor_unitario como base de custo
                        $custoBase = $custoUnitTotal > 0 ? $custoUnitTotal : $valorUnitario;
                        
                        $margemUnit = $valorCobranca - $custoBase;
                        $valorTotal = round($quantidade * $valorCobranca, 2);
                        
                        // Calcular % BDI aplicado baseado na classificacao_custo
                        $classificacaoCusto = (string)($row['classificacao_custo'] ?? '');
                        $percentualBdi = 0;
                        
                        if ($usaMargemPersonalizada && $margemPersonalizada > 0) {
                            $percentualBdi = $margemPersonalizada;
                        } elseif (!$usaMargemPersonalizada && $classificacaoCusto !== '') {
                            // Usar margem baseada na classificacao_custo
                            if ($classificacaoCusto === 'mao_obra') {
                                $percentualBdi = $margemMaoObraGlobal;
                            } elseif ($classificacaoCusto === 'equipamento') {
                                $percentualBdi = $margemEquipamentosGlobal;
                            } elseif ($classificacaoCusto === 'material') {
                                $percentualBdi = $margemMateriaisGlobal;
                            }
                            // DEBUG
                            echo "<!-- Item {$row['id']}: classificacao_custo={$classificacaoCusto} | usa_pers={$usaMargemPersonalizada} | BDI={$percentualBdi}% -->\n";
                        } elseif ($custoBase > 0.01 && $valorCobranca > $custoBase) {
                            $percentualBdi = (($valorCobranca - $custoBase) / $custoBase) * 100;
                            if ($percentualBdi > 999) {
                                $percentualBdi = 0;
                            }
                        }
                        
                        $subtotalCategoria += $valorTotal;
                        $totalGeral += $valorTotal;
                    ?>
                    <?php
                        // Calcular custo de equipamento unitário
                        // Os custos são sempre salvos como UNITÁRIOS no banco
                        $custoEquipamentoTotal = (float)($row['custo_equipamento'] ?? 0);
                        $custoEquipamentoUnit = $custoEquipamentoTotal;
                        
                        // Calcular lucro total (margem unitária × quantidade)
                        $lucroTotal = $margemUnit * $quantidade;
                    ?>
                    <tr id="item-<?php echo (int)$row['id']; ?>" class="item-row" draggable="true" data-item-id="<?php echo (int)$row['id']; ?>" data-ordem="<?php echo (int)($row['ordem'] ?? 0); ?>">
                        <td class="drag-handle" style="cursor:move; text-align:center; width:30px; color:#666;">⋮⋮</td>
                        <td class="muted"><?php echo htmlspecialchars((string)$row['codigo']); ?></td>
                        <td style="white-space:pre-line;"><?php echo htmlspecialchars((string)$row['descricao']); ?></td>
                        <td class="center"><?php echo htmlspecialchars((string)$row['unidade']); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatNumber($quantidade); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($custoMaterialUnit); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($custoMaoObraUnit); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($custoEquipamentoUnit); ?></td>
                        <td class="center admin-col"><?php echo number_format($percentualBdi, 1, ',', '.'); ?>%</td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($margemUnit); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney($valorCobranca); ?></td>
                        <td class="num admin-col"><?php echo OrcamentoItem::formatMoney($lucroTotal); ?></td>
                        <td class="num"><?php echo OrcamentoItem::formatMoney($valorTotal); ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="/?route=orcamentos/itemEdit&orcamento_id=<?php echo (int)$orcamento['id']; ?>&id=<?php echo (int)$row['id']; ?>&return_anchor=item-<?php echo (int)$row['id']; ?>">Editar</a>
                                <form class="inline" method="post" action="/?route=orcamentos/itemDelete" onsubmit="return confirm('Excluir este item?');">
                                    <input type="hidden" name="orcamento_id" value="<?php echo (int)$orcamento['id']; ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button class="btn danger" type="submit">Excluir</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Botão para adicionar novo item na categoria -->
                <tr class="add-item-row">
                    <td colspan="14" style="padding:12px; text-align:center;">
                        <button class="btn" style="background:#4CAF50; color:white; width:100%;" onclick="toggleAddItemForm('<?php echo htmlspecialchars($grupo, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($categoria, ENT_QUOTES); ?>'); return false;">
                            ➕ Adicionar Item
                        </button>
                    </td>
                </tr>

                <!-- Formulário inline para adicionar item (inicialmente oculto) -->
                <?php $formHash = preg_replace('/[^a-zA-Z0-9]/', '', $grupo . $categoria); ?>
                <tr class="add-item-form-row" id="add-form-<?php echo $formHash; ?>" style="display:none;">
                    <td colspan="14" style="padding:20px; background:rgba(76,175,80,0.05); border:2px solid #4CAF50;">
                        <div style="max-width:900px; margin:0 auto;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                                <h3 style="margin:0; color:#4CAF50;">Novo Item - <?php echo htmlspecialchars($categoria); ?></h3>
                                <button onclick="toggleAddItemForm('<?php echo htmlspecialchars($grupo, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($categoria, ENT_QUOTES); ?>'); return false;" style="background:transparent; border:none; font-size:24px; cursor:pointer; color:#999;">×</button>
                            </div>
                            
                            <form id="form-add-item-<?php echo $formHash; ?>" onsubmit="salvarNovoItem(event, '<?php echo htmlspecialchars($grupo, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($categoria, ENT_QUOTES); ?>', '<?php echo $formHash; ?>')">
                                <input type="hidden" name="orcamento_id" value="<?php echo (int)$orcamento['id']; ?>">
                                <input type="hidden" name="grupo" value="<?php echo htmlspecialchars($grupo); ?>">
                                <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria); ?>">
                                
                                <div style="margin-bottom:16px; position:relative;">
                                    <label style="display:block; margin-bottom:4px; font-size:12px; color:#999;">Descrição (digite para buscar no SINAPI)</label>
                                    <input type="text" 
                                           name="descricao" 
                                           id="descricao-<?php echo $formHash; ?>"
                                           required 
                                           autocomplete="off"
                                           oninput="buscarSINAPI(this.value, '<?php echo $formHash; ?>')"
                                           style="width:100%; padding:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:4px; color:inherit;">
                                    <div id="sinapi-suggestions-<?php echo $formHash; ?>" class="sinapi-suggestions" style="display:none; position:absolute; top:100%; left:0; right:0; background:#2a2a2a; border:1px solid #4CAF50; border-radius:4px; max-height:200px; overflow-y:auto; z-index:1000; margin-top:4px;"></div>
                                </div>
                                
                                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
                                    <div>
                                        <label style="display:block; margin-bottom:4px; font-size:12px; color:#999;">Unidade</label>
                                        <select name="unidade" required style="width:100%; padding:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:4px; color:inherit;">
                                            <option value="">Selecione</option>
                                            <?php foreach ($unidades as $un) : ?>
                                                <option value="<?php echo htmlspecialchars((string)$un); ?>"><?php echo htmlspecialchars((string)$un); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label style="display:block; margin-bottom:4px; font-size:12px; color:#999;">Quantidade</label>
                                        <input type="number" name="quantidade" step="0.01" required value="1" style="width:100%; padding:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:4px; color:inherit;">
                                    </div>
                                    
                                    <div>
                                        <label style="display:block; margin-bottom:4px; font-size:12px; color:#999;">Valor Unitário</label>
                                        <input type="number" name="valor_unitario" step="0.01" required value="0" style="width:100%; padding:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:4px; color:inherit;">
                                    </div>
                                </div>
                                
                                <div style="margin-bottom:16px;">
                                    <label style="display:block; margin-bottom:4px; font-size:12px; color:#999;">Tipo de Custo</label>
                                    <select name="classificacao_custo" style="width:100%; padding:8px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:4px; color:inherit;">
                                        <option value="material">Material</option>
                                        <option value="mao_obra">Mão de Obra</option>
                                        <option value="equipamento">Equipamento</option>
                                    </select>
                                    <div style="font-size:11px; color:#999; margin-top:4px;">O código será gerado automaticamente ao salvar</div>
                                </div>
                                
                                <div style="display:flex; gap:12px;">
                                    <button type="submit" class="btn primary" style="flex:1; background:#4CAF50; color:white;">Salvar Item</button>
                                    <button type="button" onclick="toggleAddItemForm('<?php echo htmlspecialchars($grupo, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($categoria, ENT_QUOTES); ?>'); return false;" class="btn" style="background:#666; color:white;">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>

                <tr class="total-row">
                    <td colspan="12" class="num">Total <?php echo htmlspecialchars((string)$categoria); ?></td>
                    <td class="num"><?php echo OrcamentoItem::formatMoney($subtotalCategoria); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if (!empty($grouped)) : ?>
            <tr class="total-row">
                <td colspan="12" class="num">Total Geral</td>
                <td class="num"><?php echo OrcamentoItem::formatMoney($totalGeral); ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if (!empty($grouped)) : ?>
<div class="card" style="padding:16px; margin-top:12px;">
    <div style="font-weight:800; margin-bottom:12px; font-size:14px;">📊 Custos Administrativos e Impostos</div>
    
    <table style="width:100%; max-width:600px;">
        <tbody>
            <tr>
                <td style="padding:8px 0; font-weight:600;">Subtotal da Obra:</td>
                <td style="padding:8px 0; text-align:right; font-weight:600;"><?php echo OrcamentoItem::formatMoney($totalGeral); ?></td>
            </tr>
            <?php 
            $percentualCustosAdm = (float)($orcamento['percentual_custos_adm'] ?? 0);
            $percentualImpostos = (float)($orcamento['percentual_impostos'] ?? 0);
            $valorCustosAdm = $totalGeral * ($percentualCustosAdm / 100);
            $valorImpostos = $totalGeral * ($percentualImpostos / 100);
            $totalComTaxas = $totalGeral + $valorCustosAdm + $valorImpostos;
            ?>
            <?php if ($percentualCustosAdm > 0) : ?>
            <tr>
                <td style="padding:8px 0; color:var(--muted);">Custos Administrativos (<?php echo number_format($percentualCustosAdm, 2, ',', '.'); ?>%):</td>
                <td style="padding:8px 0; text-align:right; color:var(--muted);">+ <?php echo OrcamentoItem::formatMoney($valorCustosAdm); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($percentualImpostos > 0) : ?>
            <tr>
                <td style="padding:8px 0; color:var(--muted);">Impostos (<?php echo number_format($percentualImpostos, 2, ',', '.'); ?>%):</td>
                <td style="padding:8px 0; text-align:right; color:var(--muted);">+ <?php echo OrcamentoItem::formatMoney($valorImpostos); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($percentualCustosAdm > 0 || $percentualImpostos > 0) : ?>
            <tr style="border-top:2px solid rgba(255,255,255,0.1);">
                <td style="padding:12px 0 8px; font-weight:800; font-size:16px;">TOTAL FINAL:</td>
                <td style="padding:12px 0 8px; text-align:right; font-weight:800; font-size:16px; color:#4CAF50;"><?php echo OrcamentoItem::formatMoney($totalComTaxas); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($percentualCustosAdm == 0 && $percentualImpostos == 0) : ?>
    <div class="muted" style="font-size:12px; margin-top:8px;">
        💡 Configure custos administrativos e impostos no cabeçalho do orçamento para visualizar o total final.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
document.getElementById('toggle-admin-columns').addEventListener('change', function() {
    const adminCols = document.querySelectorAll('.admin-col');
    
    if (this.checked) {
        adminCols.forEach(col => col.style.display = '');
    } else {
        adminCols.forEach(col => col.style.display = 'none');
    }
});
</script>

<script src="/public/orcamento-drag-drop.js"></script>

<script src="/public/orcamento-drag-drop.js"></script>

<script>
function editarDescontoGrupo(grupo, orcamentoId) {
    const modal = document.createElement('div');
    modal.id = 'modal-desconto-grupo';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:10000;display:flex;align-items:center;justify-content:center;';
    
    modal.innerHTML = `
        <div style="background:#1a1916;border-radius:12px;padding:24px;max-width:500px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.5);">
            <div style="font-size:18px;font-weight:800;margin-bottom:16px;color:#4FC3F7;">
                ⚙️ Ajuste de Valores: ${grupo}
            </div>
            <div style="font-size:13px;color:#999;margin-bottom:20px;">
                Aplicar ajuste em TODOS os itens deste grupo (sobrepõe margens globais)
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:12px;color:#4FC3F7;font-weight:600;margin-bottom:12px;">
                    Tipo de Ajuste
                </label>
                <div style="display:flex;gap:16px;margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="radio" name="tipo-ajuste" value="reduzir" checked style="width:auto;">
                        <span style="color:#fff;">🔻 Reduzir (Desconto)</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="radio" name="tipo-ajuste" value="aumentar" style="width:auto;">
                        <span style="color:#fff;">🔺 Aumentar (Acréscimo)</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:12px;color:#4FC3F7;font-weight:600;margin-bottom:8px;">
                    Percentual (0 a 100)
                </label>
                <input type="number" id="desconto-grupo-input" min="0" max="100" step="0.1" value="0" 
                       style="width:100%;padding:12px;border-radius:6px;border:1px solid rgba(79,195,247,0.3);background:rgba(255,255,255,0.04);color:#fff;font-size:16px;">
                <div style="font-size:10px;color:#999;margin-top:6px;">
                    Exemplo: 10 para 10% de redução/aumento
                </div>
            </div>
            
            <div style="display:flex;gap:12px;">
                <button onclick="fecharModalDescontoGrupo()" 
                        style="flex:1;padding:12px;border:1px solid rgba(255,255,255,.1);border-radius:8px;background:rgba(255,255,255,.04);cursor:pointer;color:#999;font-size:14px;">
                    Cancelar
                </button>
                <button onclick="aplicarDescontoGrupo('${grupo}', ${orcamentoId})" 
                        class="btn primary" style="flex:1;padding:12px;font-size:14px;">
                    Aplicar Ajuste
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.getElementById('desconto-grupo-input').focus();
}

function fecharModalDescontoGrupo() {
    const modal = document.getElementById('modal-desconto-grupo');
    if (modal) modal.remove();
}

function aplicarDescontoGrupo(grupo, orcamentoId) {
    const percentual = parseFloat(document.getElementById('desconto-grupo-input').value || 0);
    const tipoAjuste = document.querySelector('input[name="tipo-ajuste"]:checked').value;
    
    if (percentual === 0) {
        alert('Digite um percentual diferente de zero');
        return;
    }
    
    if (percentual < 0 || percentual > 100) {
        alert('O percentual deve estar entre 0 e 100');
        return;
    }
    
    // Converter para negativo se for redução
    const desconto = tipoAjuste === 'reduzir' ? -percentual : percentual;
    
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#2196F3;color:white;padding:12px 20px;border-radius:8px;z-index:10001;';
    loadingMsg.innerHTML = '⏳ Aplicando ajuste...';
    document.body.appendChild(loadingMsg);
    
    fetch('/?route=orcamentos/aplicarDescontoGrupo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            orcamento_id: orcamentoId,
            grupo: grupo,
            desconto: desconto
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadingMsg.innerHTML = '✓ Ajuste aplicado em ' + data.count + ' itens!';
            loadingMsg.style.background = '#4CAF50';
            fecharModalDescontoGrupo();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            loadingMsg.innerHTML = '✗ Erro: ' + (data.error || 'Erro desconhecido');
            loadingMsg.style.background = '#f44336';
            setTimeout(() => loadingMsg.remove(), 3000);
        }
    })
    .catch(err => {
        loadingMsg.innerHTML = '✗ Erro de conexão';
        loadingMsg.style.background = '#f44336';
        setTimeout(() => loadingMsg.remove(), 3000);
    });
}
});
</script>

<script src="/public/sinapi-calc-data.js"></script>
<script src="/public/sinapi-calculator.js"></script>
<script>
function toggleCalculadora() {
    const calc = document.getElementById('calculadora-sinapi');
    const icon = document.getElementById('toggle-calc-icon');
    
    if (calc.style.display === 'none') {
        calc.style.display = 'block';
        icon.textContent = '▲';
        if (typeof renderGridSINAPI === 'function') {
            renderGridSINAPI();
        }
    } else {
        calc.style.display = 'none';
        icon.textContent = '▼';
    }
}

function selecionarTodosSINAPI(checked) {
    document.querySelectorAll('.sinapi-item-check').forEach(cb => cb.checked = checked);
}

function recalcularMargens(orcamentoId) {
    if (!confirm('Recalcular os valores de cobrança de todos os itens baseado nas margens do cabeçalho?\n\nIsto irá atualizar o valor de cobrança de todos os itens que NÃO usam margem personalizada.')) {
        return;
    }
    
    const loadingMsg = document.createElement('div');
    loadingMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#2196F3;color:white;padding:12px 20px;border-radius:8px;z-index:10001;';
    loadingMsg.innerHTML = '⏳ Recalculando margens...';
    document.body.appendChild(loadingMsg);
    
    fetch('/?route=orcamentos/recalcularMargens', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ orcamento_id: orcamentoId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadingMsg.innerHTML = '✓ ' + data.count + ' itens recalculados!';
            loadingMsg.style.background = '#4CAF50';
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            loadingMsg.innerHTML = '✗ Erro: ' + (data.error || 'Erro desconhecido');
            loadingMsg.style.background = '#f44336';
            setTimeout(() => loadingMsg.remove(), 3000);
        }
    })
    .catch(err => {
        loadingMsg.innerHTML = '✗ Erro de conexão';
        loadingMsg.style.background = '#f44336';
        setTimeout(() => loadingMsg.remove(), 3000);
    });
}
</script>

<script>
// Scroll suave para o item após edição
(function() {
    // Verificar se há item para scroll (via sessão PHP)
    <?php 
    $scrollToItem = null;
    if (isset($_SESSION['scroll_to_item'])) {
        $scrollToItem = (int)$_SESSION['scroll_to_item'];
        unset($_SESSION['scroll_to_item']); // Limpar após usar
    }
    ?>
    
    const scrollToItemId = <?php echo $scrollToItem ? $scrollToItem : 'null'; ?>;
    
    function scrollToItem() {
        let itemId = null;
        
        // Prioridade 1: Item da sessão PHP
        if (scrollToItemId) {
            itemId = 'item-' + scrollToItemId;
            console.log('🎯 Scroll via sessão para:', itemId);
        }
        // Prioridade 2: Hash na URL
        else if (window.location.hash) {
            itemId = window.location.hash.substring(1);
            console.log('🎯 Scroll via hash para:', itemId);
        }
        
        if (itemId) {
            const element = document.getElementById(itemId);
            if (element) {
                console.log('✓ Elemento encontrado:', element);
                // Scroll imediato primeiro
                element.scrollIntoView({ block: 'center' });
                // Depois scroll suave
                setTimeout(() => {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Destacar o item
                    element.style.transition = 'background-color 0.5s';
                    element.style.backgroundColor = 'rgba(76, 175, 80, 0.3)';
                    setTimeout(() => {
                        element.style.backgroundColor = '';
                    }, 2000);
                }, 100);
            } else {
                console.log('✗ Elemento não encontrado:', itemId);
            }
        }
    }
    
    // Tentar quando DOM carregar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scrollToItem);
    } else {
        scrollToItem();
    }
    
    // Tentar novamente após tudo carregar (fallback)
    window.addEventListener('load', scrollToItem);
})();
</script>


<script>
// ========== ADICIONAR ITEM INLINE ==========

let sinapiTimeout = null;

function toggleAddItemForm(grupo, categoria) {
    // Gerar hash simples removendo caracteres especiais
    const hash = (grupo + categoria).replace(/[^a-zA-Z0-9]/g, '');
    const formRow = document.getElementById('add-form-' + hash);
    
    if (formRow.style.display === 'none') {
        // Fechar todos os outros formulários abertos
        document.querySelectorAll('.add-item-form-row').forEach(row => {
            row.style.display = 'none';
        });
        
        // Abrir este formulário
        formRow.style.display = '';
        
        // Focar no campo de descrição
        setTimeout(() => {
            const descInput = document.getElementById('descricao-' + hash);
            if (descInput) {
                descInput.focus();
                // Scroll suave até o formulário
                formRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    } else {
        // Fechar formulário
        formRow.style.display = 'none';
        
        // Limpar formulário
        const form = document.getElementById('form-add-item-' + hash);
        if (form) {
            form.reset();
        }
        
        // Limpar sugestões
        const suggestions = document.getElementById('sinapi-suggestions-' + hash);
        if (suggestions) {
            suggestions.style.display = 'none';
            suggestions.innerHTML = '';
        }
    }
}

function buscarSINAPI(query, hash) {
    // Limpar timeout anterior
    if (sinapiTimeout) {
        clearTimeout(sinapiTimeout);
    }
    
    // Se query muito curta, esconder sugestões
    if (query.length < 3) {
        const suggestions = document.getElementById('sinapi-suggestions-' + hash);
        if (suggestions) {
            suggestions.style.display = 'none';
        }
        return;
    }
    
    console.log('Buscando SINAPI:', query);
    
    // Aguardar 300ms antes de buscar (debounce)
    sinapiTimeout = setTimeout(() => {
        const url = '/?route=orcamentos/buscarSinapi&q=' + encodeURIComponent(query);
        console.log('URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Dados recebidos:', data);
                mostrarSugestoesSINAPI(data, hash);
            })
            .catch(error => {
                console.error('Erro ao buscar SINAPI:', error);
            });
    }, 300);
}

function mostrarSugestoesSINAPI(itens, hash) {
    const suggestions = document.getElementById('sinapi-suggestions-' + hash);
    if (!suggestions) return;
    
    if (itens.length === 0) {
        suggestions.style.display = 'none';
        return;
    }
    
    suggestions.innerHTML = '';
    
    itens.forEach(item => {
        const div = document.createElement('div');
        div.style.cssText = 'padding:12px; border-bottom:1px solid rgba(255,255,255,0.1); cursor:pointer; transition:background 0.2s;';
        div.onmouseover = function() { this.style.background = 'rgba(76,175,80,0.1)'; };
        div.onmouseout = function() { this.style.background = 'transparent'; };
        div.onclick = function() { selecionarItemSINAPI(item, hash); };
        
        div.innerHTML = `
            <div style="font-weight:600; margin-bottom:4px;">${item.codigo} - ${item.descricao}</div>
            <div style="font-size:11px; color:#999;">
                ${item.unidade} | R$ ${parseFloat(item.preco_unitario || 0).toFixed(2)}
            </div>
        `;
        
        suggestions.appendChild(div);
    });
    
    suggestions.style.display = 'block';
}

function selecionarItemSINAPI(item, hash) {
    const form = document.getElementById('form-add-item-' + hash);
    if (!form) return;
    
    // Preencher campos do formulário
    form.querySelector('[name="descricao"]').value = item.descricao || '';
    form.querySelector('[name="unidade"]').value = item.unidade || '';
    form.querySelector('[name="valor_unitario"]').value = parseFloat(item.preco_unitario || 0).toFixed(2);
    
    // Esconder sugestões
    const suggestions = document.getElementById('sinapi-suggestions-' + hash);
    if (suggestions) {
        suggestions.style.display = 'none';
    }
    
    // Focar no campo quantidade
    const qtdInput = form.querySelector('[name="quantidade"]');
    if (qtdInput) {
        qtdInput.focus();
        qtdInput.select();
    }
}

function salvarNovoItem(event, grupo, categoria, hash) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Mostrar loading
    const submitBtn = form.querySelector('[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Salvando...';
    submitBtn.disabled = true;
    
    fetch('/?route=orcamentos/itemStoreAjax', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarregar a página mantendo a posição
            const scrollPos = window.scrollY;
            window.location.href = window.location.href.split('#')[0] + '#add-form-' + hash;
            window.location.reload();
        } else {
            alert('Erro ao salvar item: ' + (data.message || 'Erro desconhecido'));
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar item. Verifique o console para mais detalhes.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
    
    return false;
}

// Fechar sugestões ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('.sinapi-suggestions') && !e.target.matches('[id^="descricao-"]')) {
        document.querySelectorAll('.sinapi-suggestions').forEach(el => {
            el.style.display = 'none';
        });
    }
});
</script>
