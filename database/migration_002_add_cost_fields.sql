-- Migration 002: Adicionar campos de custo, margem e descontos
-- Data: 2026-03-25

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adicionar campos à tabela orcamentos
ALTER TABLE `orcamentos` 
ADD COLUMN `desconto_geral` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Desconto geral em percentual (0-100)',
ADD COLUMN `margem_lucro_padrao` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Margem de lucro padrão em percentual (0-100)',
ADD COLUMN `prazo_obra` VARCHAR(100) NULL COMMENT 'Prazo da obra (ex: 18 meses)',
ADD COLUMN `fonte` VARCHAR(255) NULL COMMENT 'Fonte/Referência do orçamento',
ADD COLUMN `revisao` VARCHAR(50) NULL COMMENT 'Revisão do orçamento (ex: R00)';

-- Adicionar campos à tabela orcamento_itens
ALTER TABLE `orcamento_itens`
ADD COLUMN `etapa` VARCHAR(100) NULL COMMENT 'Etapa do orçamento (ETAPA CINZA, ETAPA ACABAMENTOS, etc)',
ADD COLUMN `custo_material` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Custo previsto de material',
ADD COLUMN `custo_mao_obra` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Custo efetivo de mão de obra',
ADD COLUMN `valor_cobranca` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Valor de cobrança ao cliente',
ADD COLUMN `margem_lucro` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Margem de lucro em percentual (0-100)',
ADD COLUMN `desconto_item` DECIMAL(5,2) NULL DEFAULT 0.00 COMMENT 'Desconto específico do item em percentual (0-100)';

-- Criar índice para etapa
ALTER TABLE `orcamento_itens`
ADD INDEX `idx_itens_etapa` (`orcamento_id`, `etapa`);

-- Criar tabela de descontos por categoria/etapa
CREATE TABLE IF NOT EXISTS `orcamento_descontos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `orcamento_id` INT NOT NULL,
  `tipo` ENUM('categoria', 'etapa', 'grupo') NOT NULL COMMENT 'Tipo de desconto',
  `referencia` VARCHAR(255) NOT NULL COMMENT 'Nome da categoria/etapa/grupo',
  `desconto` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Desconto em percentual (0-100)',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  CONSTRAINT `fk_descontos_orcamento` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_desconto_tipo_ref` (`orcamento_id`, `tipo`, `referencia`),
  INDEX `idx_descontos_orcamento` (`orcamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de configurações de margem por categoria
CREATE TABLE IF NOT EXISTS `orcamento_margens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `orcamento_id` INT NOT NULL,
  `tipo` ENUM('categoria', 'etapa', 'grupo') NOT NULL COMMENT 'Tipo de margem',
  `referencia` VARCHAR(255) NOT NULL COMMENT 'Nome da categoria/etapa/grupo',
  `margem` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Margem em percentual (0-100)',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  CONSTRAINT `fk_margens_orcamento` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_margem_tipo_ref` (`orcamento_id`, `tipo`, `referencia`),
  INDEX `idx_margens_orcamento` (`orcamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
