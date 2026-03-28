-- Verificar valores salvos no banco para os itens de Areia
SELECT 
    id,
    codigo,
    LEFT(descricao, 40) as descricao,
    quantidade,
    custo_material,
    custo_mao_obra,
    valor_unitario,
    valor_cobranca,
    margem_personalizada,
    usa_margem_personalizada,
    ROUND((valor_cobranca - valor_unitario), 2) as margem_calculada,
    ROUND(((valor_cobranca - valor_unitario) / valor_unitario) * 100, 2) as percentual_margem
FROM orcamento_itens 
WHERE orcamento_id = 28 
  AND descricao LIKE '%Areia%'
ORDER BY id;
