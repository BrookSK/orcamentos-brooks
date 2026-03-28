-- Script para recalcular valor_cobranca de todos os itens do orçamento 26
-- aplicando as margens globais configuradas no cabeçalho

-- PASSO 1: Verificar margens globais configuradas no orçamento
SELECT 
    id,
    numero_proposta,
    margem_mao_obra,
    margem_materiais
FROM orcamentos
WHERE id = 26;

-- PASSO 2: Verificar itens antes da correção
SELECT 
    id,
    codigo,
    LEFT(descricao, 40) as descricao,
    categoria,
    valor_unitario,
    valor_cobranca,
    margem_personalizada,
    usa_margem_personalizada,
    CASE 
        WHEN usa_margem_personalizada = 1 THEN 'PERSONALIZADA'
        WHEN categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%' THEN 'GLOBAL MÃO OBRA'
        ELSE 'GLOBAL MATERIAIS'
    END as tipo_margem,
    CASE 
        WHEN usa_margem_personalizada = 1 THEN margem_personalizada
        WHEN categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%' THEN 
            (SELECT margem_mao_obra FROM orcamentos WHERE id = 26)
        ELSE 
            (SELECT margem_materiais FROM orcamentos WHERE id = 26)
    END as margem_aplicar,
    CASE 
        WHEN usa_margem_personalizada = 1 THEN 
            ROUND(valor_unitario * (1 + margem_personalizada / 100), 2)
        WHEN categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%' THEN 
            ROUND(valor_unitario * (1 + (SELECT margem_mao_obra FROM orcamentos WHERE id = 26) / 100), 2)
        ELSE 
            ROUND(valor_unitario * (1 + (SELECT margem_materiais FROM orcamentos WHERE id = 26) / 100), 2)
    END as valor_cobranca_correto
FROM orcamento_itens
WHERE orcamento_id = 26
ORDER BY CAST(SUBSTRING_INDEX(codigo, '.', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, '.', -1) AS UNSIGNED);

-- PASSO 3: Atualizar valor_cobranca de itens que usam margem personalizada
UPDATE orcamento_itens
SET valor_cobranca = ROUND(valor_unitario * (1 + margem_personalizada / 100), 2)
WHERE orcamento_id = 26
  AND usa_margem_personalizada = 1
  AND margem_personalizada > 0;

-- PASSO 4: Atualizar valor_cobranca de itens de MÃO DE OBRA que usam margem global
UPDATE orcamento_itens
SET valor_cobranca = ROUND(valor_unitario * (1 + (SELECT margem_mao_obra FROM orcamentos WHERE id = 26) / 100), 2)
WHERE orcamento_id = 26
  AND usa_margem_personalizada = 0
  AND (categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%');

-- PASSO 5: Atualizar valor_cobranca de itens de MATERIAIS que usam margem global
UPDATE orcamento_itens
SET valor_cobranca = ROUND(valor_unitario * (1 + (SELECT margem_materiais FROM orcamentos WHERE id = 26) / 100), 2)
WHERE orcamento_id = 26
  AND usa_margem_personalizada = 0
  AND categoria NOT LIKE '%MÃO DE OBRA%'
  AND categoria NOT LIKE '%MAO DE OBRA%';

-- PASSO 6: Verificar itens após a correção
SELECT 
    id,
    codigo,
    LEFT(descricao, 40) as descricao,
    categoria,
    valor_unitario,
    valor_cobranca,
    margem_personalizada,
    usa_margem_personalizada,
    CASE 
        WHEN usa_margem_personalizada = 1 THEN 'PERSONALIZADA'
        WHEN categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%' THEN 'GLOBAL MÃO OBRA'
        ELSE 'GLOBAL MATERIAIS'
    END as tipo_margem,
    CASE 
        WHEN usa_margem_personalizada = 1 THEN margem_personalizada
        WHEN categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%' THEN 
            (SELECT margem_mao_obra FROM orcamentos WHERE id = 26)
        ELSE 
            (SELECT margem_materiais FROM orcamentos WHERE id = 26)
    END as margem_aplicada,
    ROUND(((valor_cobranca - valor_unitario) / valor_unitario) * 100, 2) as margem_real_percentual
FROM orcamento_itens
WHERE orcamento_id = 26
ORDER BY CAST(SUBSTRING_INDEX(codigo, '.', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(codigo, '.', -1) AS UNSIGNED);

-- PASSO 7: Estatísticas finais
SELECT 
    COUNT(*) as total_itens,
    SUM(CASE WHEN usa_margem_personalizada = 1 THEN 1 ELSE 0 END) as itens_margem_personalizada,
    SUM(CASE WHEN usa_margem_personalizada = 0 AND (categoria LIKE '%MÃO DE OBRA%' OR categoria LIKE '%MAO DE OBRA%') THEN 1 ELSE 0 END) as itens_margem_global_mao_obra,
    SUM(CASE WHEN usa_margem_personalizada = 0 AND categoria NOT LIKE '%MÃO DE OBRA%' AND categoria NOT LIKE '%MAO DE OBRA%' THEN 1 ELSE 0 END) as itens_margem_global_materiais
FROM orcamento_itens
WHERE orcamento_id = 26;
