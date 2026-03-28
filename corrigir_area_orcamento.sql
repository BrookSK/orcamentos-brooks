-- Corrigir campo area_m2 no orçamento 28

-- 1. Verificar valor atual
SELECT 
    id,
    numero_proposta,
    area_m2,
    cliente_nome,
    obra_nome
FROM orcamentos
WHERE id = 28;

-- 2. Atualizar com a área total (soma das áreas hardcoded)
-- Área Interna: 344,10 + Varanda: 103,94 + Abrigo: 47,52 + Descoberta: 139,79 + Piscina: 87,62 = 722,97
UPDATE orcamentos
SET area_m2 = 722.97
WHERE id = 28;

-- 3. Verificar após atualização
SELECT 
    id,
    numero_proposta,
    area_m2,
    cliente_nome,
    obra_nome
FROM orcamentos
WHERE id = 28;
