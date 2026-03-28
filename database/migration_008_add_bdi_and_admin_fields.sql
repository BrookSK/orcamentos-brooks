-- Adicionar campos de BDI e configurações administrativas

-- Adicionar BDI por item
ALTER TABLE `orcamento_itens` 
ADD COLUMN `percentual_bdi` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `valor_cobranca`;

-- Adicionar configurações administrativas no orçamento
ALTER TABLE `orcamentos` 
ADD COLUMN `percentual_custos_adm` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `tipo_orcamento`,
ADD COLUMN `percentual_impostos` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `percentual_custos_adm`;

-- Comentários
-- percentual_bdi: BDI (Benefícios e Despesas Indiretas) por item - define margem de lucro
-- percentual_custos_adm: Custos administrativos gerais (% sobre valor total da obra)
-- percentual_impostos: Impostos (% sobre valor total da obra)
