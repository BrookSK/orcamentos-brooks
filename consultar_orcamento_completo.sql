-- Consulta completa de todos os dados do orçamento ID 40
-- P 0078-26 | DAVI DAMAZIO | GÊNESIS II

-- ============================================
-- 1. DADOS DO ORÇAMENTO
-- ============================================
SELECT 
    'ORÇAMENTO' as tipo,
    o.*
FROM orcamentos o
WHERE o.id = 40;

-- ============================================
-- 2. ITENS DO ORÇAMENTO (com cálculos)
-- ============================================
SELECT 
    'ITENS' as tipo,
    oi.id,
    oi.codigo,
    oi.grupo,
    oi.categoria,
    oi.descricao,
    oi.unidade,
    oi.quantidade,
    oi.ordem,
    oi.valor_unitario as custo_unitario,
    oi.valor_cobranca as valor_cobranca_unitario,
    oi.custo_material,
    oi.custo_mao_obra,
    oi.custo_equipamento,
    oi.usa_margem_personalizada,
    oi.margem_personalizada,
    oi.percentual_realizado,
    oi.etapa,
    -- Cálculos
    (oi.quantidade * oi.valor_unitario) as custo_total,
    (oi.quantidade * oi.valor_cobranca) as valor_total,
    ((oi.valor_cobranca - oi.valor_unitario) * oi.quantidade) as lucro_total,
    CASE 
        WHEN oi.valor_unitario > 0 THEN 
            ROUND(((oi.valor_cobranca - oi.valor_unitario) / oi.valor_unitario * 100), 2)
        ELSE 0 
    END as margem_percentual
FROM orcamento_itens oi
WHERE oi.orcamento_id = 40
ORDER BY oi.ordem, oi.id;

-- ============================================
-- 3. RESUMO POR GRUPO
-- ============================================
SELECT 
    'RESUMO_GRUPO' as tipo,
    oi.grupo,
    COUNT(*) as total_itens,
    SUM(oi.quantidade * oi.valor_unitario) as custo_total_grupo,
    SUM(oi.quantidade * oi.valor_cobranca) as valor_total_grupo,
    SUM((oi.valor_cobranca - oi.valor_unitario) * oi.quantidade) as lucro_total_grupo
FROM orcamento_itens oi
WHERE oi.orcamento_id = 40
GROUP BY oi.grupo
ORDER BY oi.grupo;

-- ============================================
-- 4. RESUMO POR CATEGORIA
-- ============================================
SELECT 
    'RESUMO_CATEGORIA' as tipo,
    oi.grupo,
    oi.categoria,
    COUNT(*) as total_itens,
    SUM(oi.quantidade * oi.valor_unitario) as custo_total_categoria,
    SUM(oi.quantidade * oi.valor_cobranca) as valor_total_categoria,
    SUM((oi.valor_cobranca - oi.valor_unitario) * oi.quantidade) as lucro_total_categoria
FROM orcamento_itens oi
WHERE oi.orcamento_id = 40
GROUP BY oi.grupo, oi.categoria
ORDER BY oi.grupo, oi.categoria;

-- ============================================
-- 5. MARGENS DO ORÇAMENTO
-- ============================================
SELECT 
    'MARGENS' as tipo,
    om.*
FROM orcamento_margens om
WHERE om.orcamento_id = 40;

-- ============================================
-- 6. DESCONTOS DO ORÇAMENTO
-- ============================================
SELECT 
    'DESCONTOS' as tipo,
    od.*
FROM orcamento_descontos od
WHERE od.orcamento_id = 40;

-- ============================================
-- 7. CORES DO ORÇAMENTO
-- ============================================
SELECT 
    'CORES' as tipo,
    oc.*
FROM orcamento_cores oc
WHERE oc.orcamento_id = 40;

-- ============================================
-- 8. ADEQUAÇÕES DO ORÇAMENTO
-- ============================================
SELECT 
    'ADEQUACOES' as tipo,
    oa.*
FROM orcamento_adequacoes oa
WHERE oa.orcamento_id = 40;

-- ============================================
-- 9. TOTAIS GERAIS
-- ============================================
SELECT 
    'TOTAIS_GERAIS' as tipo,
    COUNT(*) as total_itens,
    SUM(oi.quantidade) as quantidade_total,
    SUM(oi.quantidade * oi.valor_unitario) as custo_total_orcamento,
    SUM(oi.quantidade * oi.valor_cobranca) as valor_total_orcamento,
    SUM((oi.valor_cobranca - oi.valor_unitario) * oi.quantidade) as lucro_total_orcamento,
    CASE 
        WHEN SUM(oi.quantidade * oi.valor_unitario) > 0 THEN
            ROUND((SUM((oi.valor_cobranca - oi.valor_unitario) * oi.quantidade) / 
                   SUM(oi.quantidade * oi.valor_unitario) * 100), 2)
        ELSE 0
    END as margem_media_percentual
FROM orcamento_itens oi
WHERE oi.orcamento_id = 40;

-- ============================================
-- 10. ITENS COM MARGEM PERSONALIZADA
-- ============================================
SELECT 
    'ITENS_MARGEM_PERSONALIZADA' as tipo,
    oi.id,
    oi.codigo,
    oi.descricao,
    oi.margem_personalizada,
    oi.valor_unitario,
    oi.valor_cobranca
FROM orcamento_itens oi
WHERE oi.orcamento_id = 40
  AND oi.usa_margem_personalizada = 1
ORDER BY oi.ordem;

-- ============================================
-- 11. VERIFICAR CONSISTÊNCIA DE DADOS
-- ============================================
SELECT 
    'VERIFICACAO_CONSISTENCIA' as tipo,
    oi.id,
    oi.codigo,
    oi.descricao,
    oi.valor_unitario,
    oi.valor_cobranca,
    CASE 
        WHEN oi.valor_cobranca = 0 THEN 'VALOR_COBRANCA_ZERO'
        WHEN oi.valor_unitario = 0 THEN 'VALOR_UNITARIO_ZERO'
        WHEN oi.valor_cobranca < oi.valor_unitario THEN 'COBRANCA_MENOR_QUE_CUSTO'
        ELSE 'OK'
    END as status
FROM orcamento_itens oi
WHERE oi.orcamento_id = 40
  AND (oi.valor_cobranca = 0 OR oi.valor_unitario = 0 OR oi.valor_cobranca < oi.valor_unitario)
ORDER BY oi.ordem;
