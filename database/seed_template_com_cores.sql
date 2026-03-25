-- ========================================
-- SEED COMPLETO COM CORES E AGRUPAMENTOS
-- Template Orçamento Casa Ana e Caio
-- Inclui: tipo_custo, grupo_finalidade, cores
-- Data: 2026-03-25
-- ========================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ========================================
-- CRIAR ORÇAMENTO PRINCIPAL
-- ========================================

INSERT INTO `orcamentos` (
    `numero_proposta`, `cliente_nome`, `arquiteto_nome`, `obra_nome`, 
    `endereco_obra`, `local_obra`, `data`, `referencia`, `area_m2`, 
    `contrato`, `tipo`, `prazo_dias`, `rev`, `prazo_obra`, `fonte`, `revisao`,
    `empresa_nome`, `empresa_endereco`, `empresa_telefone`, `empresa_email`, 
    `desconto_geral`, `margem_lucro_padrao`, `created_at`, `updated_at`
) VALUES (
    'P724-25 - CASA ANA E CAIO',
    'ANA E CAIO',
    '',
    'CASA ANA E CAIO',
    'O Porto Fino - Piedade/SP',
    'Piedade/SP',
    '2025-11-07',
    '',
    722.27,
    'Administração',
    'Residencial',
    NULL,
    'R00',
    '18 meses',
    'P724-25 - CASA ANA E CAIO - R00',
    'R00',
    'Brooks Construtora',
    'Endereço da empresa',
    '(00) 0000-0000',
    'contato@brooks.com.br',
    0.00,
    0.00,
    NOW(),
    NOW()
);

SET @orcamento_id = LAST_INSERT_ID();

-- ========================================
-- CONFIGURAR CORES DAS ETAPAS
-- ========================================

INSERT INTO `orcamento_cores_etapas` (`etapa`, `cor`, `cor_nome`, `icone`, `ordem`, `created_at`, `updated_at`) VALUES
('ETAPA CINZA (BRUTA)', '#607D8B', 'Cinza', '🏗️', 1, NOW(), NOW()),
('ETAPA ACABAMENTOS', '#4CAF50', 'Verde', '🎨', 2, NOW(), NOW()),
('ETAPA DE GERENCIAMENTO', '#2196F3', 'Azul', '👷', 3, NOW(), NOW()),
('TAXA DE ADMINISTRAÇÃO + IMPOSTOS', '#FF9800', 'Laranja', '💰', 4, NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- ========================================
-- ETAPA CINZA (BRUTA) - SERVIÇOS INICIAIS
-- ========================================

-- MATERIAL / CUSTO PREVISTO
INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.1.1', 'PAGAMENTO TAXA DE ART', 1.000, 'vb', 297.00, 297.00, 297.00, 0.00, 297.00, 0.00, 0.00, 1, 'previsto', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.2.1', 'CUSTO DE PLOTAGEM', 16.000, 'm²s', 161.50, 3267.00, 3267.00, 0.00, 3267.00, 0.00, 0.00, 2, 'previsto', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.3.1', 'SEGURO RISCOS DE ENGENHARIA, COM RESPONSABILIDADE', 1.000, 'vb', 4180.00, 4180.00, 4180.00, 0.00, 4180.00, 0.00, 0.00, 3, 'previsto', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.4.2', 'MATERIAL PARA MONTAGEM DE CANTEIRO/ALMOXARIFADO', 50.000, 'm2', 365.00, 19250.00, 19250.00, 0.00, 19250.00, 0.00, 0.00, 4, 'previsto', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.4.3', 'LOCAÇÃO PROVISÓRIA', 1.000, 'vb', 2750.00, 2750.00, 2750.00, 0.00, 2750.00, 0.00, 0.00, 5, 'previsto', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.4.5', 'MOBILIÁRIO PARA CANTEIRO', 1.000, 'vb', 4400.00, 4400.00, 4400.00, 0.00, 4400.00, 0.00, 0.00, 6, 'previsto', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL / CUSTO PREVISTO', '1.8.1', 'TARUME METÁLICO - MATERIAL', 462.000, 'm2', 82.50, 38115.00, 38115.00, 0.00, 38115.00, 0.00, 0.00, 13, 'previsto', 'SERVIÇOS INICIAIS');

-- MÃO DE OBRA / CUSTO EFETIVO
INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA / CUSTO EFETIVO', '1.4.1', 'MÃO DE OBRA MONTAGEM DE CANTEIRO', 50.000, 'm2', 132.00, 6600.00, 0.00, 6600.00, 6600.00, 0.00, 0.00, 17, 'efetivo', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA / CUSTO EFETIVO', '1.4.3', 'LOCAÇÃO PROVISÓRIA', 1.000, 'vb', 2293.50, 2293.50, 0.00, 2293.50, 2293.50, 0.00, 0.00, 18, 'efetivo', 'SERVIÇOS INICIAIS'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA / CUSTO EFETIVO', '1.8.2', 'MÃO DE OBRA PARA MONTAGEM DE TAPUME', 462.000, 'm2', 38.50, 17787.00, 0.00, 17787.00, 17787.00, 0.00, 0.00, 23, 'efetivo', 'SERVIÇOS INICIAIS');

-- ========================================
-- ETAPA CINZA - FUNDAÇÃO PROFUNDA
-- ========================================

-- MATERIAL / CUSTO PREVISTO
INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'FUNDAÇÃO', 'MATERIAL / CUSTO PREVISTO', '6.1.1', 'ESTACAS DE CONCRETO', 40.000, 'und', 303.60, 12144.00, 12144.00, 0.00, 12144.00, 0.00, 0.00, 200, 'previsto', 'FUNDAÇÃO PROFUNDA'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'FUNDAÇÃO', 'MATERIAL / CUSTO PREVISTO', '6.1.2', 'ARGAMASSA DE ASSENTAMENTO', 20.000, 'sc', 344.96, 6899.20, 6899.20, 0.00, 6899.20, 0.00, 0.00, 201, 'previsto', 'FUNDAÇÃO PROFUNDA'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'FUNDAÇÃO', 'MATERIAL / CUSTO PREVISTO', '6.1.3', 'CONCRETO PARA PREENCHIMENTO', 15.000, 'm3', 88.00, 1320.00, 1320.00, 0.00, 1320.00, 0.00, 0.00, 202, 'previsto', 'FUNDAÇÃO PROFUNDA');

-- MÃO DE OBRA / CUSTO EFETIVO
INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'FUNDAÇÃO', 'MÃO DE OBRA / CUSTO EFETIVO', '6.2.1', 'EXECUÇÃO DE ESTACAS', 40.000, 'und', 220.00, 8800.00, 0.00, 8800.00, 8800.00, 0.00, 0.00, 210, 'efetivo', 'FUNDAÇÃO PROFUNDA'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'FUNDAÇÃO', 'MÃO DE OBRA / CUSTO EFETIVO', '6.2.2', 'MONTAGEM DE FORMAS', 20.000, 'm²', 154.00, 3080.00, 0.00, 3080.00, 3080.00, 0.00, 0.00, 211, 'efetivo', 'FUNDAÇÃO PROFUNDA'),
(@orcamento_id, 'ETAPA CINZA (BRUTA)', 'FUNDAÇÃO', 'MÃO DE OBRA / CUSTO EFETIVO', '6.2.3', 'CONCRETAGEM', 15.000, 'm3', 183.33, 2750.00, 0.00, 2750.00, 2750.00, 0.00, 0.00, 212, 'efetivo', 'FUNDAÇÃO PROFUNDA');

-- ========================================
-- ETAPA ACABAMENTOS - COBERTURA
-- ========================================

-- MATERIAL / CUSTO PREVISTO
INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA ACABAMENTOS', 'COBERTURA', 'MATERIAL / CUSTO PREVISTO', '18.1', 'ESTRUTURA DE MADEIRA', 1.000, 'vb', 8197.20, 8197.20, 8197.20, 0.00, 8197.20, 0.00, 0.00, 500, 'previsto', 'COBERTURA'),
(@orcamento_id, 'ETAPA ACABAMENTOS', 'COBERTURA', 'MATERIAL / CUSTO PREVISTO', '18.2', 'TELHAS E ACESSÓRIOS', 1.000, 'vb', 17859.98, 17859.98, 17859.98, 0.00, 17859.98, 0.00, 0.00, 501, 'previsto', 'COBERTURA'),
(@orcamento_id, 'ETAPA ACABAMENTOS', 'COBERTURA', 'MATERIAL / CUSTO PREVISTO', '18.3', 'CALHAS E RUFOS', 1.000, 'vb', 14518.20, 14518.20, 14518.20, 0.00, 14518.20, 0.00, 0.00, 502, 'previsto', 'COBERTURA');

-- MÃO DE OBRA / CUSTO EFETIVO
INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA ACABAMENTOS', 'COBERTURA', 'MÃO DE OBRA / CUSTO EFETIVO', '18.1', 'MONTAGEM ESTRUTURA DE MADEIRA', 1.000, 'vb', 8197.20, 8197.20, 0.00, 8197.20, 8197.20, 0.00, 0.00, 505, 'efetivo', 'COBERTURA'),
(@orcamento_id, 'ETAPA ACABAMENTOS', 'COBERTURA', 'MÃO DE OBRA / CUSTO EFETIVO', '18.2', 'INSTALAÇÃO DE TELHAS', 1.000, 'vb', 17859.98, 17859.98, 0.00, 17859.98, 17859.98, 0.00, 0.00, 506, 'efetivo', 'COBERTURA'),
(@orcamento_id, 'ETAPA ACABAMENTOS', 'COBERTURA', 'MÃO DE OBRA / CUSTO EFETIVO', '18.3', 'INSTALAÇÃO DE CALHAS E RUFOS', 1.000, 'vb', 14518.20, 14518.20, 0.00, 14518.20, 14518.20, 0.00, 0.00, 507, 'efetivo', 'COBERTURA');

-- ========================================
-- ETAPA DE GERENCIAMENTO
-- ========================================

INSERT INTO `orcamento_itens` (
    `orcamento_id`, `etapa`, `grupo`, `categoria`, `codigo`, `descricao`, 
    `quantidade`, `unidade`, `valor_unitario`, `valor_total`, 
    `custo_material`, `custo_mao_obra`, `valor_cobranca`, 
    `margem_lucro`, `desconto_item`, `ordem`,
    `tipo_custo`, `grupo_finalidade`
) VALUES
(@orcamento_id, 'ETAPA DE GERENCIAMENTO', 'EQUIPE DE OBRA', 'MÃO DE OBRA / CUSTO EFETIVO', '42.1', 'ENGENHEIRO RESIDENTE', 18.000, 'mês', 11000.00, 198000.00, 0.00, 198000.00, 198000.00, 0.00, 0.00, 1000, 'efetivo', 'EQUIPE DE OBRA'),
(@orcamento_id, 'ETAPA DE GERENCIAMENTO', 'EQUIPE DE OBRA', 'MÃO DE OBRA / CUSTO EFETIVO', '42.2', 'MESTRE DE OBRAS', 18.000, 'mês', 7000.00, 126000.00, 0.00, 126000.00, 126000.00, 0.00, 0.00, 1001, 'efetivo', 'EQUIPE DE OBRA'),
(@orcamento_id, 'ETAPA DE GERENCIAMENTO', 'EQUIPE DE OBRA', 'MÃO DE OBRA / CUSTO EFETIVO', '42.3', 'AUXILIAR ADMINISTRATIVO', 18.000, 'mês', 1800.00, 32400.00, 0.00, 32400.00, 32400.00, 0.00, 0.00, 1002, 'efetivo', 'EQUIPE DE OBRA');

-- Adicionar opções de etapas
INSERT INTO `orcamento_opcoes` (`tipo`, `nome`, `created_at`) VALUES
('etapa', 'ETAPA CINZA (BRUTA)', NOW()),
('etapa', 'ETAPA ACABAMENTOS', NOW()),
('etapa', 'ETAPA DE GERENCIAMENTO', NOW()),
('etapa', 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', NOW())
ON DUPLICATE KEY UPDATE created_at = created_at;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- EXIBIR RESULTADO COM RESUMO
-- ========================================

SELECT 
    @orcamento_id as 'ID do Orçamento',
    'Template criado com cores e agrupamentos!' as 'Status';

-- Resumo por Etapa
SELECT 
    etapa as 'Etapa',
    COUNT(*) as 'Itens',
    SUM(custo_material) as 'Total Material',
    SUM(custo_mao_obra) as 'Total Mão de Obra',
    SUM(valor_cobranca) as 'Total Cobrança'
FROM orcamento_itens 
WHERE orcamento_id = @orcamento_id
GROUP BY etapa
ORDER BY MIN(ordem);

-- Resumo por Tipo de Custo
SELECT 
    tipo_custo as 'Tipo',
    COUNT(*) as 'Itens',
    SUM(custo_material) as 'Material',
    SUM(custo_mao_obra) as 'Mão de Obra',
    SUM(valor_cobranca) as 'Total'
FROM orcamento_itens 
WHERE orcamento_id = @orcamento_id
GROUP BY tipo_custo;

-- Cores Configuradas
SELECT * FROM orcamento_cores_etapas ORDER BY ordem;
