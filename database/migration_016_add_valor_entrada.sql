-- Migration 016: Adicionar campo valor_entrada
-- Adiciona campo para armazenar o valor de entrada pago pelo cliente

ALTER TABLE `orcamentos`
ADD COLUMN `valor_entrada` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor de entrada pago pelo cliente' AFTER `percentual_impostos`;
