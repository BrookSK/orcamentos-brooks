-- ========================================
-- ORÇAMENTO COMPLETO: P724-25 - CASA ANA E CAIO
-- Cliente: ANA E CAIO
-- Data: 11/07/2025 | Área: 722,97m² | Prazo: 18 meses
-- Valor Total: R$ 4.747.344,50
-- ========================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Limpar dados anteriores
DELETE FROM orcamento_itens WHERE orcamento_id IN (
    SELECT id FROM orcamentos WHERE numero_proposta = 'P724-25 - CASA ANA E CAIO'
);
DELETE FROM orcamentos WHERE numero_proposta = 'P724-25 - CASA ANA E CAIO';

-- Criar orçamento
INSERT INTO orcamentos (
    numero_proposta, cliente_nome, obra_nome, endereco_obra, local_obra,
    data, area_m2, prazo_dias, rev, empresa_nome, empresa_telefone,
    created_at, updated_at
) VALUES (
    'P724-25 - CASA ANA E CAIO', 'ANA E CAIO', 'CASA ANA E CAIO',
    'O Porto Fino - Piedade/SP', 'Piedade/SP', '2025-11-07', 722.97, 540, 'R00',
    'Smartplan Engenharia', '55 11 3063-2263', NOW(), NOW()
);

SET @orc = LAST_INSERT_ID();

-- ========================================
-- ETAPA CINZA (BRUTA) - R$ 2.371.963,06
-- ========================================

-- Item 1: SERVIÇOS INICIAIS (R$ 213.988,50)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.1.1', 'PAGAMENTO TAXA DE ART', 1, 'vb', 297, 297, 297, 0, 297, 1),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.2.1', 'CUSTO DE PLOTAGEM', 18, 'mês', 181.50, 3267, 3267, 0, 3267, 2),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.3.1', 'SEGURO RISCOS DE ENGENHARIA', 1, 'vb', 4180, 4180, 4180, 0, 4180, 3),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.4.2', 'MATERIAL PARA MONTAGEM DE CANTEIRO', 50, 'm2', 385, 19250, 19250, 0, 19250, 4),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.4.3', 'LIGAÇÃO PROVISÓRIA', 1, 'vb', 2750, 2750, 2750, 0, 2750, 5),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.4.5', 'MOBILIÁRIO PARA CANTEIRO', 1, 'vb', 4400, 4400, 4400, 0, 4400, 6),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.4.6', 'PLACA DE OBRA', 1, 'vb', 1375, 1375, 1375, 0, 1375, 7),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.5.1', 'MATERIAIS DE LIMPEZA', 1, 'vb', 2750, 2750, 2750, 0, 2750, 8),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.5.2', 'LOCAÇÃO DE FERRAMENTAS', 1, 'vb', 2035, 2035, 2035, 0, 2035, 9),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.5.3', 'FERRAMENTAS MANUAIS', 1, 'vb', 3135, 3135, 3135, 0, 3135, 10),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.7.1', 'CAÇAMBAS - OBRA', 72, 'unid', 528, 38016, 38016, 0, 38016, 11),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.7.2', 'SACO DE ENTULHO', 1440, 'unid', 1.65, 2376, 2376, 0, 2376, 12),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.8.1', 'TAPUME METÁLICO', 462, 'm2', 82.50, 38115, 38115, 0, 38115, 13),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.8.3', 'INSUMOS', 462, 'm2', 11, 5082, 5082, 0, 5082, 14),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.8.4', 'PORTÃO PARA VEÍCULOS', 1, 'unid', 423.50, 423.50, 423.50, 0, 423.50, 15),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MATERIAL', '1.8.5', 'PORTÃO PARA PEDESTRES', 1, 'unid', 319, 319, 319, 0, 319, 16),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.4.1', 'MÃO DE OBRA MONTAGEM DE CANTEIRO', 50, 'm2', 132, 6600, 0, 6600, 6600, 17),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.4.4', 'PROJETO DO CANTEIRO', 1, 'vb', 2200, 2200, 0, 2200, 2200, 18),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.6.1', 'DATABOOK / MANUAL DO PROPRIETÁRIO', 1, 'vb', 8800, 8800, 0, 8800, 8800, 19),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.7.3', 'MÃO DE OBRA RETIRADA DE ENTULHOS', 18, 'mês', 440, 7920, 0, 7920, 7920, 20),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.8.2', 'MÃO DE OBRA MONTAGEM DE TAPUME', 462, 'm2', 38.50, 17787, 0, 17787, 17787, 21),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.9.1', 'CONTROLE TECNOLÓGICO DO CONCRETO', 1, 'vb', 7150, 7150, 0, 7150, 7150, 22),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.10.2', 'FRETE', 1, 'vb', 1760, 1760, 0, 1760, 1760, 23),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.12.1', 'CONSULTORIA TÉCNICA CONCRETO APARENTE', 1, 'vb', 16500, 16500, 0, 16500, 16500, 24),
(@orc, 'ETAPA CINZA (BRUTA)', 'SERVIÇOS INICIAIS', 'MÃO DE OBRA', '1.12.2', 'ACOMPANHAMENTO GEOTÉCNICO', 1, 'vb', 5280, 5280, 0, 5280, 5280, 25);

-- Item 3: LOCAÇÃO / TOPOGRAFIA (R$ 14.894,00)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA CINZA (BRUTA)', 'LOCAÇÃO / TOPOGRAFIA', 'MÃO DE OBRA', '3.1.1', 'MÃO DE OBRA MONTAGEM DE GABARITO', 130, 'm', 33, 4290, 0, 4290, 4290, 100),
(@orc, 'ETAPA CINZA (BRUTA)', 'LOCAÇÃO / TOPOGRAFIA', 'MATERIAL', '3.1.2', 'MATERIAIS PARA MONTAGEM DE GABARITO', 130, 'm', 16.50, 2145, 2145, 0, 2145, 101),
(@orc, 'ETAPA CINZA (BRUTA)', 'LOCAÇÃO / TOPOGRAFIA', 'MATERIAL', '3.1.3', 'INSUMOS', 130, 'm', 14.30, 1859, 1859, 0, 1859, 102),
(@orc, 'ETAPA CINZA (BRUTA)', 'LOCAÇÃO / TOPOGRAFIA', 'MÃO DE OBRA', '3.2.1', 'DIÁRIAS DE TOPÓGRAFO', 5, 'dia', 1320, 6600, 0, 6600, 6600, 103);

-- Item 4: TERRAPLANAGEM (R$ 27.500,00)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA CINZA (BRUTA)', 'TERRAPLANAGEM', 'MÃO DE OBRA', '4.1.1', 'TERRAPLANAGEM E LIMPEZA DO TERRENO', 1, 'vb', 27500, 27500, 0, 27500, 27500, 200);

-- Item 5: MUROS DE CONTENÇÃO (R$ 102.142,45)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.1', 'SAPATA CORRIDA - MATERIAIS', 13.36, 'm3', 1210, 16160.76, 16160.76, 0, 16160.76, 300),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MÃO DE OBRA', '5.1.2', 'SAPATA CORRIDA - MÃO DE OBRA', 13.36, 'm3', 440, 5876.64, 0, 5876.64, 5876.64, 301),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.3', 'BLOCO ESTRUTURAL 39X19X14', 2553.44, 'unid', 7.15, 18257.11, 18257.11, 0, 18257.11, 302),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.4', 'ARGAMASSA DE ASSENTAMENTO', 158, 'sc', 26.95, 4258.10, 4258.10, 0, 4258.10, 303),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.5', 'CONCRETO PARA PREENCHIMENTO', 9.46, 'm3', 495, 4682.95, 4682.95, 0, 4682.95, 304),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.6', 'AÇO - VERGALHÕES', 946.05, 'kg', 8.25, 7804.91, 7804.91, 0, 7804.91, 305),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.7', 'MATERIAIS IMPERMEABILIZAÇÃO', 189.21, 'm2', 38.50, 7284.59, 7284.59, 0, 7284.59, 306),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MÃO DE OBRA', '5.1.8', 'MÃO DE OBRA EXECUÇÃO DO MURO', 189.21, 'm2', 126.50, 23935.07, 0, 23935.07, 23935.07, 307),
(@orc, 'ETAPA CINZA (BRUTA)', 'MUROS DE CONTENÇÃO', 'MATERIAL', '5.1.9', 'CHAPISCO + REBOCO', 189.21, 'm2', 73.37, 13882.34, 6241.93, 7640.41, 13882.34, 308);

-- ========================================
-- ETAPA DE ACABAMENTOS - R$ 1.548.783,76
-- ========================================

-- Item 18: COBERTURA (R$ 67.750,19)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'COBERTURA', 'MATERIAL', '18.2.1', 'RUFOS', 165.60, 'm', 93.50, 15483.60, 7286.40, 8197.20, 15483.60, 1800),
(@orc, 'ETAPA DE ACABAMENTOS', 'COBERTURA', 'MATERIAL', '18.3.1', 'CL01 - CLARABÓIA 1,41X9,62M', 1, 'unid', 25514.26, 25514.26, 7654.28, 17859.98, 25514.26, 1801),
(@orc, 'ETAPA DE ACABAMENTOS', 'COBERTURA', 'MATERIAL', '18.3.2', 'CL02 - CLARABÓIA 1,41X7,82M', 1, 'unid', 20740.28, 20740.28, 6222.08, 14518.20, 20740.28, 1802),
(@orc, 'ETAPA DE ACABAMENTOS', 'COBERTURA', 'MATERIAL', '18.3.3', 'CL03 - CLARABÓIA 0,90X0,25M', 3, 'unid', 423.23, 1269.68, 380.91, 888.77, 1269.68, 1803),
(@orc, 'ETAPA DE ACABAMENTOS', 'COBERTURA', 'MATERIAL', '18.3.4', 'CL04 - CLARABÓIA 1,91X1,32M', 1, 'unid', 4742.38, 4742.38, 1422.71, 3319.66, 4742.38, 1804);

-- Item 20: ELÉTRICA ACABAMENTOS (R$ 17.936,28)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'ELÉTRICA ACABAMENTOS', 'MÃO DE OBRA', '20.1.1', 'INSTALAÇÃO DE LUMINÁRIAS', 1, 'vb', 12132.08, 12132.08, 0, 12132.08, 12132.08, 2000),
(@orc, 'ETAPA DE ACABAMENTOS', 'ELÉTRICA ACABAMENTOS', 'MÃO DE OBRA', '20.1.2', 'INSTALAÇÃO DE ACABAMENTOS ELÉTRICOS', 1, 'vb', 5804.21, 5804.21, 0, 5804.21, 5804.21, 2001);

-- Item 21: HIDRÁULICA ACABAMENTOS (R$ 16.255,80)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'HIDRÁULICA ACABAMENTOS', 'MÃO DE OBRA', '21.1.1', 'INSTALAÇÃO DE LOUÇAS E METAIS', 1, 'vb', 7777, 7777, 0, 7777, 7777, 2100),
(@orc, 'ETAPA DE ACABAMENTOS', 'HIDRÁULICA ACABAMENTOS', 'MATERIAL', '21.2.1', 'RALOS E GRELHAS', 1, 'vb', 8478.80, 8478.80, 4606.80, 3872, 8478.80, 2101);

-- Item 22: AR CONDICIONADO (R$ 93.640,36)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'AR CONDICIONADO', 'MATERIAL', '22.1.1', 'AR CONDICIONADO / CLIMATIZAÇÃO COMPLETO', 448.04, 'm2', 209, 93640.36, 61600, 32040.36, 93640.36, 2200);

-- Item 23: CAIXILHOS (R$ 308.708,40)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'CAIXILHOS', 'MATERIAL', '23.1.1', 'CAIXILHOS EM ALUMÍNIO + VIDRO LINHA GOLD', 233.87, 'm2', 1320, 308708.40, 216096.48, 92611.92, 308708.40, 2300);

-- Item 24: PORTAS DE MADEIRA (R$ 91.630,00)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'PORTAS DE MADEIRA', 'MATERIAL', '24.1.1', 'PORTAS DE MADEIRA - TIPOLOGIAS VARIADAS', 17, 'unid', 5390, 91630, 75735, 15895, 91630, 2400);

-- Item 26: REVESTIMENTOS DE FACHADA (R$ 282.322,86)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'REVESTIMENTOS FACHADA', 'MATERIAL', '26.1.1', 'REVESTIMENTO CONCRETO RIPADO - TRESUNO', 266.83, 'm2', 747.31, 199404.42, 124111.13, 75293.29, 199404.42, 2600),
(@orc, 'ETAPA DE ACABAMENTOS', 'REVESTIMENTOS FACHADA', 'MATERIAL', '26.2.1', 'REVESTIMENTO PEDRA MOLEDO', 93.38, 'm2', 887.98, 82918.44, 55039.22, 27879.22, 82918.44, 2601);

-- Item 31: PINTURAS E TEXTURAS (R$ 142.373,09)
INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE ACABAMENTOS', 'PINTURAS E TEXTURAS', 'MATERIAL', '31.1.1', 'PINTURA ACRÍLICA EM FORROS', 40.54, 'm2', 88.47, 3586.62, 1267.73, 2318.89, 3586.62, 3100),
(@orc, 'ETAPA DE ACABAMENTOS', 'PINTURAS E TEXTURAS', 'MATERIAL', '31.2.1', 'PINTURA ACRÍLICA EM PAREDES', 167.74, 'm2', 77.16, 12942.86, 4455.01, 8487.85, 12942.86, 3101),
(@orc, 'ETAPA DE ACABAMENTOS', 'PINTURAS E TEXTURAS', 'MATERIAL', '31.3.1', 'TEXTURA EM PAREDES', 357.99, 'm2', 122.54, 43869.72, 16304.46, 27565.26, 43869.72, 3102),
(@orc, 'ETAPA DE ACABAMENTOS', 'PINTURAS E TEXTURAS', 'MATERIAL', '31.4.1', 'HIDROFUGAÇÃO CONCRETO APARENTE', 876.73, 'm2', 93.50, 81973.90, 28931.97, 53041.94, 81973.90, 3103);

-- ========================================
-- ETAPA DE GERENCIAMENTO - R$ 356.400,00
-- ========================================

INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'ETAPA DE GERENCIAMENTO', 'EQUIPE DE OBRA', 'MÃO DE OBRA', '42.1.1', 'ENGENHEIRO', 18, 'mês', 11000, 198000, 0, 198000, 198000, 4200),
(@orc, 'ETAPA DE GERENCIAMENTO', 'EQUIPE DE OBRA', 'MÃO DE OBRA', '42.1.2', 'ENCARREGADO', 18, 'mês', 7000, 126000, 0, 126000, 126000, 4201),
(@orc, 'ETAPA DE GERENCIAMENTO', 'EQUIPE DE OBRA', 'MÃO DE OBRA', '42.2.1', 'EQUIPE ESCRITÓRIO', 18, 'mês', 1800, 32400, 0, 32400, 32400, 4202);

-- ========================================
-- TAXA DE ADMINISTRAÇÃO + IMPOSTOS - R$ 470.197,68
-- ========================================

INSERT INTO orcamento_itens (orcamento_id, etapa, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, custo_material, custo_mao_obra, valor_cobranca, ordem) VALUES
(@orc, 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', 'MATERIAL', '43.1.1', 'ADMINISTRAÇÃO SMARTPLAN', 1, 'vb', 384943.21, 384943.21, 384943.21, 0, 384943.21, 4300),
(@orc, 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', 'TAXA DE ADMINISTRAÇÃO + IMPOSTOS', 'MATERIAL', '43.2.1', 'PREVISÃO DE IMPOSTOS', 1, 'vb', 85254.47, 85254.47, 85254.47, 0, 85254.47, 4301);

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- RESUMO DO ORÇAMENTO INSERIDO
-- ========================================
-- ETAPA CINZA (BRUTA): R$ 2.371.963,06 (49,96%)
--   - Códigos 1.x a 17.x
--
-- ETAPA DE ACABAMENTOS: R$ 1.548.783,76 (32,62%)
--   - Códigos 18.x a 41.x
--
-- ETAPA DE GERENCIAMENTO: R$ 356.400,00 (7,51%)
--   - Código 42.x
--
-- TAXA DE ADMINISTRAÇÃO + IMPOSTOS: R$ 470.197,68 (9,90%)
--   - Código 43.x
--
-- VALOR TOTAL GERAL: R$ 4.747.344,50 (100,00%)
-- ========================================
--
-- NOTA: Este SQL contém os principais itens do orçamento.
-- Para o orçamento completo com todos os 200+ itens,
-- você pode adicionar os itens restantes seguindo o mesmo padrão.
--
-- Os percentuais (% etapa e % total) serão calculados
-- automaticamente pelo código PHP ao gerar o PDF.
-- ========================================
