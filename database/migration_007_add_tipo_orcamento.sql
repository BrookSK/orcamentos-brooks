-- Adicionar coluna tipo_orcamento para diferenciar orçamentos manuais e SINAPI
ALTER TABLE `orcamentos` 
ADD COLUMN `tipo_orcamento` ENUM('manual', 'sinapi') NOT NULL DEFAULT 'manual' AFTER `tipo`;
