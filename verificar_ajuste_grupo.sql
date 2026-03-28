-- Verificar itens com margem personalizada aplicada
SELECT 
    id,
    grupo,
    categoria,
    codigo,
    descricao,
    custo_material,
    custo_mao_obra,
    custo_equipamento,
    (custo_material + custo_mao_obra + custo_equipamento) as custo_total,
    margem_personalizada,
    usa_margem_personalizada,
    valor_cobranca,
    ROUND(((valor_cobranca / NULLIF(custo_material + custo_mao_obra + custo_equipamento, 0)) - 1) * 100, 2) as margem_calculada
FROM orcamento_itens
WHERE usa_margem_personalizada = 1
ORDER BY grupo, categoria, codigo;
