-- Script para verificar se os custos do SINAPI estão sendo salvos corretamente
-- Execute este script para diagnosticar o problema de custos zerados no PDF

-- 1. Verificar itens com custos zerados (possível problema)
SELECT 
    id,
    codigo,
    LEFT(descricao, 50) as descricao_resumida,
    categoria,
    quantidade,
    custo_material,
    custo_mao_obra,
    valor_unitario,
    valor_cobranca,
    margem_personalizada,
    usa_margem_personalizada
FROM orcamento_itens
WHERE orcamento_id = 26
  AND (custo_material = 0 OR custo_material IS NULL)
  AND (custo_mao_obra = 0 OR custo_mao_obra IS NULL)
ORDER BY CAST(SUBSTRING_INDEX(codigo, '.', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, '.', -1) AS UNSIGNED);

-- 2. Verificar itens com custos preenchidos (funcionando corretamente)
SELECT 
    id,
    codigo,
    LEFT(descricao, 50) as descricao_resumida,
    categoria,
    quantidade,
    custo_material,
    custo_mao_obra,
    valor_unitario,
    valor_cobranca,
    margem_personalizada,
    usa_margem_personalizada
FROM orcamento_itens
WHERE orcamento_id = 26
  AND (custo_material > 0 OR custo_mao_obra > 0)
ORDER BY CAST(SUBSTRING_INDEX(codigo, '.', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, '.', -1) AS UNSIGNED);

-- 3. Estatísticas gerais
SELECT 
    COUNT(*) as total_itens,
    SUM(CASE WHEN custo_material > 0 OR custo_mao_obra > 0 THEN 1 ELSE 0 END) as itens_com_custo,
    SUM(CASE WHEN (custo_material = 0 OR custo_material IS NULL) AND (custo_mao_obra = 0 OR custo_mao_obra IS NULL) THEN 1 ELSE 0 END) as itens_sem_custo
FROM orcamento_itens
WHERE orcamento_id = 26;
