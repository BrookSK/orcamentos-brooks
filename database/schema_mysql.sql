-- Schema MySQL para o sistema de Orçamentos
-- Ajuste o database antes de executar (USE ...), se necessário.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Opcional:
-- USE `sql_orcamento_onsolutionsbrasil_com_br`;

DROP TABLE IF EXISTS `orcamento_itens`;
DROP TABLE IF EXISTS `orcamento_opcoes`;
DROP TABLE IF EXISTS `orcamentos`;

CREATE TABLE `orcamentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero_proposta` VARCHAR(50) NOT NULL,
  `cliente_nome` VARCHAR(255) NOT NULL,
  `arquiteto_nome` VARCHAR(255) NULL,
  `obra_nome` VARCHAR(255) NULL,
  `endereco_obra` VARCHAR(255) NULL,
  `local_obra` VARCHAR(255) NULL,
  `data` DATE NULL,
  `referencia` VARCHAR(100) NULL,
  `area_m2` DECIMAL(10,2) NULL,
  `contrato` VARCHAR(100) NULL,
  `tipo` VARCHAR(100) NULL,
  `prazo_dias` INT NULL,
  `rev` VARCHAR(20) NULL,
  `empresa_nome` VARCHAR(255) NULL,
  `empresa_endereco` VARCHAR(255) NULL,
  `empresa_telefone` VARCHAR(100) NULL,
  `empresa_email` VARCHAR(255) NULL,
  `logo_path` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  INDEX `idx_orcamentos_numero` (`numero_proposta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orcamento_itens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `orcamento_id` INT NOT NULL,
  `grupo` VARCHAR(255) NOT NULL,
  `categoria` VARCHAR(255) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `descricao` TEXT NOT NULL,
  `quantidade` DECIMAL(10,2) NOT NULL,
  `unidade` VARCHAR(50) NOT NULL,
  `valor_unitario` DECIMAL(10,2) NOT NULL,
  `valor_total` DECIMAL(10,2) NOT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  CONSTRAINT `fk_itens_orcamento` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos`(`id`) ON DELETE CASCADE,
  INDEX `idx_itens_orcamento` (`orcamento_id`),
  INDEX `idx_itens_grouping` (`orcamento_id`, `grupo`, `categoria`, `ordem`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orcamento_opcoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo` VARCHAR(30) NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uq_orcamento_opcoes_tipo_nome` (`tipo`, `nome`),
  INDEX `idx_orcamento_opcoes_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
