-- Migration 013: Adicionar campos de áreas personalizadas
-- Data: 2026-03-28
-- Descrição: Adiciona campos JSON para armazenar áreas personalizadas do orçamento

ALTER TABLE orcamentos 
ADD COLUMN areas_personalizadas TEXT NULL COMMENT 'JSON com áreas personalizadas: [{"nome":"AREA INTERNA","m2":344.10,"fator":1}]';

-- Exemplo de estrutura JSON:
-- [
--   {"nome": "AREA INTERNA", "m2": 344.10, "fator": 1},
--   {"nome": "VARANDA COBERTA", "m2": 103.94, "fator": 1},
--   {"nome": "ABRIGO AUTOS", "m2": 47.52, "fator": 1},
--   {"nome": "AREA DESCOBERTA", "m2": 139.79, "fator": 1},
--   {"nome": "PISCINA", "m2": 87.62, "fator": 1}
-- ]
