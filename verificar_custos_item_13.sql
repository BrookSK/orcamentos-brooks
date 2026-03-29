-- Verificar custos do item 1.3 (PROJETO ESTRUTURAL)
SELECT 
    id,
    codigo,
    descricao,
    quantidade,
    valor_unitario,
    valor_cobranca,
    custo_material,
    custo_mao_obra,
    custo_equipamento,
    classificacao_custo,
    usa_margem_personalizada,
    margem_personalizada
FROM orcamento_itens 
WHERE orcamento_id = 40 
  AND codigo = '1.3';

-- Verificar todos os itens com custos
SELECT 
    codigo,
    LEFT(descricao, 30) as descricao,
    valor_unitario,
    custo_material,
    custo_mao_obra,
    custo_equipamento,
    classificacao_custo,
    (custo_material + custo_mao_obra + custo_equipamento) as custo_total
FROM orcamento_itens 
WHERE orcamento_id = 40
ORDER BY CAST(SUBSTRING_INDEX(codigo, '.', 1) AS UNSIGNED), 
         CAST(SUBSTRING_INDEX(codigo, '.', -1) AS UNSIGNED);
