-- Migration 012: Adicionar campo de ajuste pro rata de materiais
-- Este campo permite aplicar um percentual de reajuste sobre os custos de materiais

ALTER TABLE orcamentos 
ADD COLUMN ajuste_prorata_materiais DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentual de reajuste pro rata para materiais (ex: 1.5 para 1.5%)';
