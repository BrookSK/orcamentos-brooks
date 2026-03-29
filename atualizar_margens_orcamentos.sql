-- Script para atualizar margens de orçamentos manuais existentes
-- que foram criados sem valores de margem configurados

-- Atualizar orçamentos que têm margem_mao_obra = 0 ou NULL
UPDATE orcamentos
SET margem_mao_obra = 50.00
WHERE (margem_mao_obra IS NULL OR margem_mao_obra = 0)
  AND tipo_orcamento = 'manual';

-- Atualizar orçamentos que têm margem_materiais = 0 ou NULL
UPDATE orcamentos
SET margem_materiais = 20.00
WHERE (margem_materiais IS NULL OR margem_materiais = 0)
  AND tipo_orcamento = 'manual';

-- Atualizar orçamentos que têm margem_equipamentos = 0 ou NULL
UPDATE orcamentos
SET margem_equipamentos = 20.00
WHERE (margem_equipamentos IS NULL OR margem_equipamentos = 0)
  AND tipo_orcamento = 'manual';

-- Verificar resultado
SELECT 
    id,
    numero_proposta,
    tipo_orcamento,
    margem_mao_obra,
    margem_materiais,
    margem_equipamentos
FROM orcamentos
WHERE tipo_orcamento = 'manual'
ORDER BY id DESC
LIMIT 10;
