-- ========================================
-- Script para verificar a coluna classificacao_custo
-- ========================================

-- 1. Verificar se a coluna existe
SHOW COLUMNS FROM orcamento_itens LIKE 'classificacao_custo';

-- 2. Ver a estrutura completa da tabela
DESCRIBE orcamento_itens;

-- 3. Verificar itens do orçamento 40 e suas classificações
SELECT 
    id,
    grupo,
    categoria,
    codigo,
    descricao,
    classificacao_custo,
    valor_unitario,
    usa_margem_personalizada,
    margem_personalizada
FROM orcamento_itens 
WHERE orcamento_id = 40
ORDER BY ordem, id;

-- 4. Contar quantos itens ainda não têm classificação
SELECT 
    COUNT(*) as total_itens,
    SUM(CASE WHEN classificacao_custo IS NULL THEN 1 ELSE 0 END) as sem_classificacao,
    SUM(CASE WHEN classificacao_custo = 'material' THEN 1 ELSE 0 END) as materiais,
    SUM(CASE WHEN classificacao_custo = 'mao_obra' THEN 1 ELSE 0 END) as mao_obra,
    SUM(CASE WHEN classificacao_custo = 'equipamento' THEN 1 ELSE 0 END) as equipamentos
FROM orcamento_itens 
WHERE orcamento_id = 40;
