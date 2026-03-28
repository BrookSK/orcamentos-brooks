-- Migration 010: Adicionar campo custo_equipamento para custos de locação
-- Data: 2026-03-28
-- Descrição: Equipamentos não são nem material nem mão de obra, precisam de campo próprio

-- Adicionar coluna custo_equipamento
ALTER TABLE orcamento_itens 
ADD COLUMN custo_equipamento DECIMAL(15,2) DEFAULT 0.00 AFTER custo_mao_obra;

-- Comentário explicativo
ALTER TABLE orcamento_itens 
MODIFY COLUMN custo_equipamento DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Custo unitário de locação de equipamento';

-- Migrar custos de equipamentos existentes
-- Itens com categoria EQUIPAMENTO que têm custo_material devem mover para custo_equipamento
UPDATE orcamento_itens
SET custo_equipamento = custo_material,
    custo_material = 0
WHERE (categoria LIKE '%EQUIPAMENTO%' OR grupo LIKE '%EQUIPAMENTO%')
  AND custo_material > 0;

-- Verificar resultado
SELECT 
    COUNT(*) as total_equipamentos,
    SUM(CASE WHEN custo_equipamento > 0 THEN 1 ELSE 0 END) as com_custo_equipamento,
    SUM(CASE WHEN custo_material > 0 THEN 1 ELSE 0 END) as com_custo_material_incorreto
FROM orcamento_itens
WHERE categoria LIKE '%EQUIPAMENTO%' OR grupo LIKE '%EQUIPAMENTO%';
