-- Adicionar colunas capa_path para armazenar até 4 capas personalizadas
ALTER TABLE `orcamentos` 
ADD COLUMN `capa_path_1` VARCHAR(255) NULL AFTER `logo_path`,
ADD COLUMN `capa_path_2` VARCHAR(255) NULL AFTER `capa_path_1`,
ADD COLUMN `capa_path_3` VARCHAR(255) NULL AFTER `capa_path_2`,
ADD COLUMN `capa_path_4` VARCHAR(255) NULL AFTER `capa_path_3`;
