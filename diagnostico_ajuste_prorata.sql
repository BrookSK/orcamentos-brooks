-- ============================================
-- DIAGNÓSTICO COMPLETO: AJUSTE PRO RATA DE MATERIAIS
-- ============================================

-- 1. Verificar se a coluna existe
SELECT 'Verificando coluna ajuste_prorata_materiais...' AS etapa;
SHOW COLUMNS FROM orcamentos LIKE 'ajuste_prorata_materiais';

-- 2. Ver orçamentos com ajuste configurado
SELECT 'Orçamentos com ajuste pro rata configurado:' AS etapa;
SELECT id, numero_proposta, ajuste_prorata_materiais, margem_materiais, margem_mao_obra, margem_equipamentos
FROM orcamentos 
WHERE ajuste_prorata_materiais > 0
ORDER BY id DESC;

-- 3. Ver todos os orçamentos (últimos 10)
SELECT 'Últimos 10 orçamentos (todos):' AS etapa;
SELECT id, numero_proposta, ajuste_prorata_materiais, margem_materiais, margem_mao_obra, margem_equipamentos
FROM orcamentos 
ORDER BY id DESC 
LIMIT 10;

-- 4. Atualizar orçamento 29 com 1% de ajuste (TESTE)
SELECT 'Aplicando 1% de ajuste no orçamento 29...' AS etapa;
UPDATE orcamentos 
SET ajuste_prorata_materiais = 1.00 
WHERE id = 29;

-- 5. Confirmar atualização
SELECT 'Confirmando atualização do orçamento 29:' AS etapa;
SELECT id, numero_proposta, ajuste_prorata_materiais, margem_materiais, margem_mao_obra, margem_equipamentos
FROM orcamentos 
WHERE id = 29;

-- 6. Ver itens do orçamento 29 com custo de material
SELECT 'Itens do orçamento 29 com custo de material:' AS etapa;
SELECT 
    id,
    codigo,
    LEFT(descricao, 40) AS descricao_resumida,
    quantidade,
    custo_material,
    custo_mao_obra,
    custo_equipamento,
    valor_unitario,
    valor_cobranca,
    -- Simular cálculo do custo unitário
    CASE 
        WHEN custo_material > 0 AND quantidade > 0 THEN
            CASE 
                WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material
                ELSE custo_material / quantidade
            END
        ELSE 0
    END AS custo_material_unitario_calculado,
    -- Simular aplicação do ajuste (1%)
    CASE 
        WHEN custo_material > 0 AND quantidade > 0 THEN
            CASE 
                WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material * 1.01
                ELSE (custo_material / quantidade) * 1.01
            END
        ELSE 0
    END AS custo_material_com_ajuste_1pct
FROM orcamento_itens 
WHERE orcamento_id = 29 
  AND custo_material > 0
ORDER BY ordem 
LIMIT 10;

-- 7. Comparação: antes e depois do ajuste
SELECT 'Comparação de valores (primeiros 5 itens):' AS etapa;
SELECT 
    codigo,
    LEFT(descricao, 30) AS descricao,
    quantidade,
    ROUND(custo_material, 2) AS custo_mat_banco,
    ROUND(
        CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material
            ELSE custo_material / quantidade
        END, 
    2) AS custo_unit_sem_ajuste,
    ROUND(
        CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material * 1.01
            ELSE (custo_material / quantidade) * 1.01
        END, 
    2) AS custo_unit_com_ajuste_1pct,
    ROUND(
        (CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material * 1.01
            ELSE (custo_material / quantidade) * 1.01
        END) - 
        (CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material
            ELSE custo_material / quantidade
        END),
    2) AS diferenca
FROM orcamento_itens 
WHERE orcamento_id = 29 
  AND custo_material > 0
ORDER BY ordem 
LIMIT 5;

-- 8. Resumo final
SELECT 'RESUMO FINAL:' AS etapa;
SELECT 
    'Orçamento 29 configurado com 1% de ajuste pro rata' AS status,
    COUNT(*) AS total_itens_com_material,
    ROUND(SUM(custo_material), 2) AS soma_custos_material_banco,
    ROUND(SUM(
        CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material * quantidade
            ELSE custo_material
        END
    ), 2) AS soma_custos_material_total,
    ROUND(SUM(
        CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material * quantidade * 1.01
            ELSE custo_material * 1.01
        END
    ), 2) AS soma_com_ajuste_1pct,
    ROUND(SUM(
        CASE 
            WHEN ABS(custo_material - valor_unitario) < 0.01 THEN custo_material * quantidade * 0.01
            ELSE custo_material * 0.01
        END
    ), 2) AS economia_total_ajuste
FROM orcamento_itens 
WHERE orcamento_id = 29 
  AND custo_material > 0;
