-- Diagnóstico de custos zerados no orçamento 28

-- 1. Verificar quantos itens têm custos zerados
SELECT 
    COUNT(*) as total_itens,
    SUM(CASE WHEN custo_material = 0 AND custo_mao_obra = 0 THEN 1 ELSE 0 END) as itens_sem_custo,
    SUM(CASE WHEN custo_material > 0 OR custo_mao_obra > 0 THEN 1 ELSE 0 END) as itens_com_custo
FROM orcamento_itens
WHERE orcamento_id = 28;

-- 2. Ver alguns exemplos de itens sem custo
SELECT 
    id,
    codigo,
    LEFT(descricao, 50) as descricao,
    categoria,
    quantidade,
    custo_material,
    custo_mao_obra,
    valor_unitario,
    valor_cobranca,
    margem_personalizada,
    usa_margem_personalizada
FROM orcamento_itens
WHERE orcamento_id = 28
  AND custo_material = 0
  AND custo_mao_obra = 0
LIMIT 10;

-- 3. Ver margens globais do orçamento
SELECT 
    id,
    numero_proposta,
    margem_mao_obra,
    margem_materiais
FROM orcamentos
WHERE id = 28;

-- 4. SOLUÇÃO: Preencher custos baseado em valor_unitario e categoria
-- Para itens de MATERIAIS: custo_material = valor_unitario
UPDATE orcamento_itens
SET custo_material = valor_unitario,
    custo_mao_obra = 0
WHERE orcamento_id = 28
  AND custo_material = 0
  AND custo_mao_obra = 0
  AND (categoria LIKE '%MATERIAIS%' OR categoria LIKE '%MATERIAL%')
  AND categoria NOT LIKE '%MÃO DE OBRA%'
  AND categoria NOT LIKE '%MAO DE OBRA%';

-- 5. Para itens de MÃO DE OBRA: custo_mao_obra = valor_unitario
UPDATE orcamento_itens
SET custo_material = 0,
    custo_mao_obra = valor_unitario
WHERE orcamento_id = 28
  AND custo_material = 0
  AND custo_mao_obra = 0
  AND (categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%');

-- 6. Para itens de EQUIPAMENTOS: custo_material = valor_unitario (considerar como custo de material)
UPDATE orcamento_itens
SET custo_material = valor_unitario,
    custo_mao_obra = 0
WHERE orcamento_id = 28
  AND custo_material = 0
  AND custo_mao_obra = 0
  AND categoria LIKE '%EQUIPAMENTO%';

-- 7. Para itens restantes sem categoria clara: custo_material = valor_unitario
UPDATE orcamento_itens
SET custo_material = valor_unitario,
    custo_mao_obra = 0
WHERE orcamento_id = 28
  AND custo_material = 0
  AND custo_mao_obra = 0;

-- 8. Verificar resultado após correção
SELECT 
    COUNT(*) as total_itens,
    SUM(CASE WHEN custo_material = 0 AND custo_mao_obra = 0 THEN 1 ELSE 0 END) as itens_sem_custo,
    SUM(CASE WHEN custo_material > 0 OR custo_mao_obra > 0 THEN 1 ELSE 0 END) as itens_com_custo
FROM orcamento_itens
WHERE orcamento_id = 28;

-- 9. Ver alguns exemplos após correção
SELECT 
    id,
    codigo,
    LEFT(descricao, 50) as descricao,
    categoria,
    quantidade,
    custo_material,
    custo_mao_obra,
    valor_unitario,
    valor_cobranca,
    ROUND((valor_cobranca - valor_unitario), 2) as margem_unitaria,
    ROUND((valor_cobranca - valor_unitario) * quantidade, 2) as lucro_total
FROM orcamento_itens
WHERE orcamento_id = 28
ORDER BY CAST(SUBSTRING_INDEX(codigo, '.', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, '.', -1) AS UNSIGNED)
LIMIT 20;
