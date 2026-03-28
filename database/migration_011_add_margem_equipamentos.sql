-- Migration 011: Adicionar margem global para equipamentos
-- Data: 2026-03-28
-- Descrição: Adiciona campo margem_equipamentos no cabeçalho do orçamento

-- Adicionar coluna margem_equipamentos na tabela orcamentos
ALTER TABLE orcamentos 
ADD COLUMN margem_equipamentos DECIMAL(5,2) DEFAULT 20.00 AFTER margem_materiais;

-- Comentário explicativo
ALTER TABLE orcamentos 
MODIFY COLUMN margem_equipamentos DECIMAL(5,2) DEFAULT 20.00 COMMENT 'Margem de lucro global para equipamentos (%)';

-- Atualizar orçamentos existentes com valor padrão de 20%
UPDATE orcamentos
SET margem_equipamentos = 20.00
WHERE margem_equipamentos IS NULL OR margem_equipamentos = 0;

-- Verificar resultado
SELECT 
    id,
    numero_proposta,
    margem_mao_obra,
    margem_materiais,
    margem_equipamentos
FROM orcamentos
LIMIT 10;
