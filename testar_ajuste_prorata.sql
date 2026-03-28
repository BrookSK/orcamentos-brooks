-- Testar ajuste pro rata de materiais

-- 1. Atualizar orçamento 29 com 1% de ajuste
UPDATE orcamentos 
SET ajuste_prorata_materiais = 1.00 
WHERE id = 29;

-- 2. Verificar se foi salvo
SELECT id, numero_proposta, ajuste_prorata_materiais, margem_materiais 
FROM orcamentos 
WHERE id = 29;

-- 3. Ver alguns itens deste orçamento para conferir os custos
SELECT id, codigo, descricao, quantidade, custo_material, custo_mao_obra, custo_equipamento, valor_unitario, valor_cobranca
FROM orcamento_itens 
WHERE orcamento_id = 29 
ORDER BY ordem 
LIMIT 10;
