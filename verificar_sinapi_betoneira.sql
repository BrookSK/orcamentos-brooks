-- Verificar se há dados na tabela sinapi_insumos
SELECT COUNT(*) as total FROM sinapi_insumos;

-- Buscar por "beton"
SELECT codigo, descricao, unidade, preco_unit 
FROM sinapi_insumos 
WHERE descricao LIKE '%beton%' 
OR codigo LIKE '%beton%'
LIMIT 10;

-- Buscar por "betoneira"
SELECT codigo, descricao, unidade, preco_unit 
FROM sinapi_insumos 
WHERE descricao LIKE '%betoneira%' 
OR codigo LIKE '%betoneira%'
LIMIT 10;

-- Ver alguns registros da tabela
SELECT codigo, descricao, unidade, preco_unit 
FROM sinapi_insumos 
LIMIT 10;
