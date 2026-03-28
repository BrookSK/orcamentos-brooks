<?php
$pageTitle = 'Gerenciar Itens SINAPI';
ob_start();
?>

<style>
.sinapi-gerenciar {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.sinapi-filtros {
    background: rgba(255,255,255,0.05);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.sinapi-filtros input,
.sinapi-filtros select {
    padding: 8px 12px;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px;
    background: rgba(255,255,255,0.08);
    color: var(--text);
    font-size: 14px;
}

.sinapi-filtros input[type="text"] {
    flex: 1;
    min-width: 300px;
}

.sinapi-acoes {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.btn-sinapi {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-primary {
    background: #C9973A;
    color: #1a1a1a;
}

.btn-primary:hover {
    background: #d4a347;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.sinapi-tabela {
    background: rgba(255,255,255,0.03);
    border-radius: 8px;
    overflow: hidden;
}

.sinapi-tabela table {
    width: 100%;
    border-collapse: collapse;
}

.sinapi-tabela th {
    background: rgba(201,151,58,0.2);
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #C9973A;
}

.sinapi-tabela td {
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.sinapi-tabela tr:hover {
    background: rgba(255,255,255,0.05);
}

.sinapi-tabela input[type="text"],
.sinapi-tabela input[type="number"] {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 4px;
    background: rgba(255,255,255,0.05);
    color: var(--text);
    font-size: 13px;
}

.sinapi-tabela input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.sinapi-paginacao {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
    padding: 20px;
}

.sinapi-paginacao a,
.sinapi-paginacao span {
    padding: 8px 12px;
    border-radius: 4px;
    background: rgba(255,255,255,0.05);
    color: var(--text);
    text-decoration: none;
    transition: all 0.2s;
}

.sinapi-paginacao a:hover {
    background: rgba(201,151,58,0.2);
}

.sinapi-paginacao .atual {
    background: #C9973A;
    color: #1a1a1a;
    font-weight: 600;
}

.sinapi-stats {
    text-align: center;
    color: #999;
    font-size: 14px;
    margin-bottom: 15px;
}
</style>

<div class="sinapi-gerenciar">
    <h1>Gerenciar Itens SINAPI</h1>
    
    <div class="sinapi-stats">
        Total de <?php echo number_format($total, 0, ',', '.'); ?> itens cadastrados
    </div>
    
    <form method="GET" action="?route=sinapi/gerenciar" class="sinapi-filtros">
        <input type="hidden" name="route" value="sinapi/gerenciar">
        <input type="text" name="termo" placeholder="Buscar por código ou descrição..." value="<?php echo htmlspecialchars($termo); ?>">
        <select name="tipo">
            <option value="">Todos os tipos</option>
            <option value="INSUMO" <?php echo $tipo === 'INSUMO' ? 'selected' : ''; ?>>Insumo</option>
            <option value="COMPOSICAO" <?php echo $tipo === 'COMPOSICAO' ? 'selected' : ''; ?>>Composição</option>
        </select>
        <button type="submit" class="btn-sinapi btn-primary">Filtrar</button>
        <?php if ($termo || $tipo): ?>
            <a href="?route=sinapi/gerenciar" class="btn-sinapi" style="background:#666;color:white;">Limpar</a>
        <?php endif; ?>
    </form>
    
    <div class="sinapi-acoes">
        <button onclick="selecionarTodos()" class="btn-sinapi btn-primary">Selecionar Todos</button>
        <button onclick="deselecionarTodos()" class="btn-sinapi" style="background:#666;color:white;">Desmarcar Todos</button>
        <button onclick="excluirSelecionados()" class="btn-sinapi btn-danger">Excluir Selecionados</button>
    </div>
    
    <div class="sinapi-tabela">
        <table>
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="check-all" onchange="toggleTodos(this.checked)">
                    </th>
                    <th style="width:100px;">Código</th>
                    <th>Descrição</th>
                    <th style="width:80px;">Unidade</th>
                    <th style="width:100px;">Tipo</th>
                    <th style="width:120px;">Preço Unit.</th>
                    <th style="width:80px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr data-codigo="<?php echo htmlspecialchars($item['codigo']); ?>">
                    <td>
                        <input type="checkbox" class="item-check" value="<?php echo htmlspecialchars($item['codigo']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($item['codigo']); ?></td>
                    <td>
                        <input type="text" 
                               class="item-descricao" 
                               value="<?php echo htmlspecialchars($item['descricao']); ?>"
                               data-original="<?php echo htmlspecialchars($item['descricao']); ?>">
                    </td>
                    <td>
                        <input type="text" 
                               class="item-unidade" 
                               value="<?php echo htmlspecialchars($item['unidade']); ?>"
                               maxlength="10"
                               data-original="<?php echo htmlspecialchars($item['unidade']); ?>">
                    </td>
                    <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                    <td>
                        <input type="number" 
                               class="item-preco" 
                               value="<?php echo number_format($item['preco_unit'], 2, '.', ''); ?>"
                               step="0.01"
                               min="0"
                               data-original="<?php echo $item['preco_unit']; ?>">
                    </td>
                    <td>
                        <button onclick="salvarItem('<?php echo htmlspecialchars($item['codigo']); ?>')" 
                                class="btn-sinapi btn-primary" 
                                style="padding:4px 8px;font-size:12px;">
                            Salvar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPaginas > 1): ?>
    <div class="sinapi-paginacao">
        <?php if ($pagina > 1): ?>
            <a href="?route=sinapi/gerenciar&pagina=<?php echo $pagina - 1; ?>&termo=<?php echo urlencode($termo); ?>&tipo=<?php echo urlencode($tipo); ?>">« Anterior</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
            <?php if ($i === $pagina): ?>
                <span class="atual"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?route=sinapi/gerenciar&pagina=<?php echo $i; ?>&termo=<?php echo urlencode($termo); ?>&tipo=<?php echo urlencode($tipo); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($pagina < $totalPaginas): ?>
            <a href="?route=sinapi/gerenciar&pagina=<?php echo $pagina + 1; ?>&termo=<?php echo urlencode($termo); ?>&tipo=<?php echo urlencode($tipo); ?>">Próxima »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleTodos(checked) {
    document.querySelectorAll('.item-check').forEach(cb => cb.checked = checked);
}

function selecionarTodos() {
    document.getElementById('check-all').checked = true;
    toggleTodos(true);
}

function deselecionarTodos() {
    document.getElementById('check-all').checked = false;
    toggleTodos(false);
}

async function salvarItem(codigo) {
    const row = document.querySelector(`tr[data-codigo="${codigo}"]`);
    const descricao = row.querySelector('.item-descricao').value;
    const unidade = row.querySelector('.item-unidade').value;
    const preco = parseFloat(row.querySelector('.item-preco').value);
    
    try {
        const response = await fetch('/?route=sinapi/atualizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo, descricao, unidade, preco })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✓ Item atualizado com sucesso!');
            row.querySelector('.item-descricao').dataset.original = descricao;
            row.querySelector('.item-unidade').dataset.original = unidade;
            row.querySelector('.item-preco').dataset.original = preco;
        } else {
            alert('Erro: ' + data.error);
        }
    } catch (error) {
        alert('Erro ao salvar: ' + error.message);
    }
}

async function excluirSelecionados() {
    const checks = document.querySelectorAll('.item-check:checked');
    
    if (checks.length === 0) {
        alert('Selecione pelo menos um item para excluir.');
        return;
    }
    
    if (!confirm(`Tem certeza que deseja excluir ${checks.length} item(ns)?`)) {
        return;
    }
    
    const codigos = Array.from(checks).map(cb => cb.value);
    
    try {
        const response = await fetch('/?route=sinapi/excluir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigos })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`✓ ${data.count} item(ns) excluído(s) com sucesso!`);
            window.location.reload();
        } else {
            alert('Erro: ' + data.error);
        }
    } catch (error) {
        alert('Erro ao excluir: ' + error.message);
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
