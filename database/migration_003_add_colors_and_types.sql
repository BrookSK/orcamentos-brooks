-- Migration 003: Adicionar cores, tipos e agrupamentos
-- Data: 2026-03-25

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Adicionar campos à tabela orcamento_itens
ALTER TABLE `orcamento_itens`
ADD COLUMN `tipo_custo` ENUM('previsto', 'efetivo') NULL DEFAULT 'previsto' COMMENT 'Tipo de custo: previsto (material) ou efetivo (mão de obra)',
ADD COLUMN `grupo_finalidade` VARCHAR(255) NULL COMMENT 'Agrupamento por finalidade (ex: FUNDAÇÃO PROFUNDA)',
ADD COLUMN `cor_etapa` VARCHAR(20) NULL COMMENT 'Cor da etapa em hexadecimal';

-- Criar tabela de configuração de cores por etapa
CREATE TABLE IF NOT EXISTS `orcamento_cores_etapas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `etapa` VARCHAR(100) NOT NULL UNIQUE,
  `cor` VARCHAR(20) NOT NULL COMMENT 'Cor em hexadecimal (ex: #4A90E2)',
  `cor_nome` VARCHAR(50) NULL COMMENT 'Nome da cor (ex: Azul)',
  `icone` VARCHAR(50) NULL COMMENT 'Ícone da etapa (ex: 🏗️)',
  `ordem` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  INDEX `idx_cores_etapa` (`etapa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Popular cores padrão para as etapas
INSERT INTO `orcamento_cores_etapas` (`etapa`, `cor`, `cor_nome`, `icone`, `ordem`, `created_at`, `updated_at`) VALUES
('ETAPA CINZA (BRUTA)', '#607D8B', 'Cinza', '🏗️', 1, NOW(), NOW()),
('ETAPA ACABAMENTOS', '#4CAF50', 'Verde', '🎨', 2, NOW(), NOW()),
('ETAPA DE GERENCIAMENTO', '#2196F3', 'Azul', '👷', 3, NOW(), NOW()),
('TAXA DE ADMINISTRAÇÃO + IMPOSTOS', '#FF9800', 'Laranja', '💰', 4, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- Criar índices para otimização
ALTER TABLE `orcamento_itens`
ADD INDEX `idx_itens_tipo_custo` (`orcamento_id`, `tipo_custo`),
ADD INDEX `idx_itens_grupo_finalidade` (`orcamento_id`, `grupo_finalidade`);

SET FOREIGN_KEY_CHECKS = 1;
