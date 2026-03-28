<?php

declare(strict_types=1);

namespace App\Api;

/**
 * API para buscar preços SINAPI do banco de dados
 * Endpoint: /api/sinapi/preco
 */
class SinapiPrecosApi
{
    /**
     * Buscar preço de um insumo SINAPI
     * 
     * @param string $codigo Código SINAPI do insumo
     * @param string $uf Estado (opcional, padrão SP)
     * @return array ['success' => bool, 'preco' => float, 'descricao' => string, 'unidade' => string]
     */
    public static function buscarPreco(string $codigo, string $uf = 'SP'): array
    {
        try {
            $pdo = \App\Core\Database::pdo();
            
            // Query para buscar preço na tabela de insumos SINAPI
            $stmt = $pdo->prepare(
                "SELECT codigo, descricao, unidade, tipo, preco_unit, uf, referencia, regime 
                 FROM sinapi_insumos 
                 WHERE codigo = :codigo 
                 AND uf = :uf
                 ORDER BY referencia DESC
                 LIMIT 1"
            );
            
            $stmt->execute([
                ':codigo' => $codigo,
                ':uf' => $uf
            ]);
            
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'codigo' => $resultado['codigo'],
                    'descricao' => $resultado['descricao'],
                    'unidade' => $resultado['unidade'],
                    'tipo' => $resultado['tipo'],
                    'preco' => (float)$resultado['preco_unit'],
                    'uf' => $resultado['uf'],
                    'referencia' => $resultado['referencia'],
                    'regime' => $resultado['regime'],
                    'fonte' => 'banco_dados'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Código não encontrado no banco de dados',
                'codigo' => $codigo
            ];
            
        } catch (\Exception $e) {
            \App\Core\Logger::error('Erro ao buscar preço SINAPI: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erro ao consultar banco de dados',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Buscar múltiplos preços de uma vez
     * 
     * @param array $codigos Array de códigos SINAPI
     * @param string $uf Estado
     * @return array Array associativo [codigo => dados]
     */
    public static function buscarMultiplosPrecos(array $codigos, string $uf = 'SP'): array
    {
        if (empty($codigos)) {
            return [];
        }
        
        try {
            $pdo = \App\Core\Database::pdo();
            
            // Preparar placeholders para IN clause
            $placeholders = implode(',', array_fill(0, count($codigos), '?'));
            
            $sql = "SELECT codigo, descricao, unidade, tipo, preco_unit, uf, referencia, regime 
                    FROM sinapi_insumos 
                    WHERE codigo IN ($placeholders)
                    AND uf = ?
                    ORDER BY codigo, referencia DESC";
            
            $stmt = $pdo->prepare($sql);
            
            // Bind dos parâmetros
            $params = array_merge($codigos, [$uf]);
            $stmt->execute($params);
            
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Organizar por código (pegar apenas o primeiro resultado de cada)
            $precos = [];
            foreach ($resultados as $row) {
                $codigo = $row['codigo'];
                if (!isset($precos[$codigo])) {
                    $precos[$codigo] = [
                        'success' => true,
                        'codigo' => $row['codigo'],
                        'descricao' => $row['descricao'],
                        'unidade' => $row['unidade'],
                        'tipo' => $row['tipo'],
                        'preco' => (float)$row['preco_unit'],
                        'uf' => $row['uf'],
                        'referencia' => $row['referencia'],
                        'regime' => $row['regime'],
                        'fonte' => 'banco_dados'
                    ];
                }
            }
            
            return $precos;
            
        } catch (\Exception $e) {
            \App\Core\Logger::error('Erro ao buscar múltiplos preços SINAPI: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Listar todos os códigos disponíveis (para autocomplete)
     * 
     * @param string $termo Termo de busca
     * @param string $uf Estado
     * @param int $limite Limite de resultados
     * @return array Lista de insumos
     */
    public static function listarInsumos(string $termo = '', string $uf = 'SP', int $limite = 50): array
    {
        try {
            $pdo = \App\Core\Database::pdo();
            
            // Remover filtro de UF para ser mais flexível
            $sql = "SELECT codigo, descricao, unidade, tipo, preco_unit, uf, referencia, regime 
                    FROM sinapi_insumos 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($termo)) {
                $sql .= " AND (codigo LIKE :termo OR descricao LIKE :termo)";
                $params[':termo'] = '%' . $termo . '%';
            }
            
            // Priorizar registros do estado solicitado
            $sql .= " ORDER BY (uf = :uf) DESC, codigo LIMIT :limite";
            $params[':uf'] = $uf;
            
            $stmt = $pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            \App\Core\Logger::error('Erro ao listar insumos SINAPI: ' . $e->getMessage());
            return [];
        }
    }
}
