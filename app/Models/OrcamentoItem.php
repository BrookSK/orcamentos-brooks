<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrcamentoItem
{
    private const MAX_DECIMAL_15_2 = 9999999999999.99;

    private static function parsePtBrNumber(string $value): float
    {
        $value = trim($value);
        if ($value === '') {
            return 0.0;
        }

        $value = str_replace(' ', '', $value);

        $hasComma = strpos($value, ',') !== false;
        $hasDot = strpos($value, '.') !== false;

        // Caso 1: formato PT-BR clássico: 1.234,56
        if ($hasComma) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return (float)$value;
        }

        // Caso 2: só ponto. Pode ser milhar (1.200) ou decimal (221.19)
        if ($hasDot) {
            $lastDot = strrpos($value, '.');
            $dec = substr($value, $lastDot + 1);

            // Se a parte após o último ponto tem 1-2 dígitos, tratamos como decimal.
            // Ex.: 221.19 => 221.19
            if (preg_match('/^\d{1,2}$/', $dec) === 1) {
                return (float)$value;
            }

            // Se tem 3 dígitos após o último ponto, normalmente é milhar.
            // Ex.: 1.200 => 1200
            $value = str_replace('.', '', $value);
            return (float)$value;
        }

        // Caso 3: só dígitos
        return (float)$value;
    }

    private static function calculateTotal(float $quantidade, float $valorUnitario): float
    {
        return round($quantidade * $valorUnitario, 2);
    }

    public static function allByOrcamento(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = :id ORDER BY ordem, grupo, categoria, id');
        $stmt->execute([':id' => $orcamentoId]);
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['valor_total'] = self::calculateTotal((float)$item['quantidade'], (float)$item['valor_unitario']);
        }

        return $items;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamento_itens WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['valor_total'] = self::calculateTotal((float)$row['quantidade'], (float)$row['valor_unitario']);
        return $row;
    }

    public static function create(int $orcamentoId, array $data): int
    {
        $pdo = Database::pdo();

        $valorUnitario = (float)($data['valor_unitario'] ?? 0);
        $quantidade = (float)($data['quantidade'] ?? 0);
        $valorTotal = self::calculateTotal($quantidade, $valorUnitario);
        
        $custoMaterial = (float)($data['custo_material'] ?? 0);
        $custoMaoObra = (float)($data['custo_mao_obra'] ?? 0);
        $margemLucro = (float)($data['margem_lucro'] ?? 0);
        $descontoItem = (float)($data['desconto_item'] ?? 0);
        $percentualRealizado = (float)($data['percentual_realizado'] ?? 0);
        $percentualBdi = (float)($data['percentual_bdi'] ?? 0);
        
        // Se valor_cobranca foi passado explicitamente (SINAPI), usar ele
        // Caso contrário, calcular baseado em custos + margem + desconto
        if (isset($data['valor_cobranca']) && $data['valor_cobranca'] !== '') {
            $valorCobranca = (float)$data['valor_cobranca'];
        } else {
            $valorCobranca = self::calculateValorCobranca($custoMaterial, $custoMaoObra, $margemLucro, $descontoItem);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO orcamento_itens (orcamento_id, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, ordem, etapa, custo_material, custo_mao_obra, valor_cobranca, margem_lucro, desconto_item, percentual_realizado, percentual_bdi) '
            . 'VALUES (:orcamento_id, :grupo, :categoria, :codigo, :descricao, :quantidade, :unidade, :valor_unitario, :valor_total, :ordem, :etapa, :custo_material, :custo_mao_obra, :valor_cobranca, :margem_lucro, :desconto_item, :percentual_realizado, :percentual_bdi)'
        );

        $stmt->execute([
            ':orcamento_id' => $orcamentoId,
            ':grupo' => (string)($data['grupo'] ?? ''),
            ':categoria' => (string)($data['categoria'] ?? ''),
            ':codigo' => (string)($data['codigo'] ?? ''),
            ':descricao' => (string)($data['descricao'] ?? ''),
            ':quantidade' => $quantidade,
            ':unidade' => (string)($data['unidade'] ?? ''),
            ':valor_unitario' => $valorUnitario,
            ':valor_total' => $valorTotal,
            ':ordem' => (int)($data['ordem'] ?? 0),
            ':etapa' => (string)($data['etapa'] ?? ''),
            ':custo_material' => $custoMaterial,
            ':custo_mao_obra' => $custoMaoObra,
            ':valor_cobranca' => $valorCobranca,
            ':margem_lucro' => $margemLucro,
            ':desconto_item' => $descontoItem,
            ':percentual_realizado' => $percentualRealizado,
            ':percentual_bdi' => $percentualBdi,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM orcamento_itens WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::pdo();

        $valorUnitario = (float)($data['valor_unitario'] ?? 0);
        $quantidade = (float)($data['quantidade'] ?? 0);
        $valorTotal = self::calculateTotal($quantidade, $valorUnitario);
        
        $custoMaterial = (float)($data['custo_material'] ?? 0);
        $custoMaoObra = (float)($data['custo_mao_obra'] ?? 0);
        $margemLucro = (float)($data['margem_lucro'] ?? 0);
        $descontoItem = (float)($data['desconto_item'] ?? 0);
        $percentualRealizado = (float)($data['percentual_realizado'] ?? 0);
        $percentualBdi = (float)($data['percentual_bdi'] ?? 0);
        if ($percentualRealizado < 0) {
            $percentualRealizado = 0;
        }
        if ($percentualRealizado > 100) {
            $percentualRealizado = 100;
        }
        
        // Se valor_cobranca foi passado explicitamente (SINAPI), usar ele
        // Caso contrário, calcular baseado em custos + margem + desconto
        if (isset($data['valor_cobranca']) && $data['valor_cobranca'] !== '') {
            $valorCobranca = (float)$data['valor_cobranca'];
        } else {
            $valorCobranca = self::calculateValorCobranca($custoMaterial, $custoMaoObra, $margemLucro, $descontoItem);
        }

        $stmt = $pdo->prepare(
            'UPDATE orcamento_itens SET'
            . ' grupo = :grupo,'
            . ' categoria = :categoria,'
            . ' codigo = :codigo,'
            . ' descricao = :descricao,'
            . ' quantidade = :quantidade,'
            . ' unidade = :unidade,'
            . ' valor_unitario = :valor_unitario,'
            . ' valor_total = :valor_total,'
            . ' ordem = :ordem,'
            . ' etapa = :etapa,'
            . ' custo_material = :custo_material,'
            . ' custo_mao_obra = :custo_mao_obra,'
            . ' valor_cobranca = :valor_cobranca,'
            . ' margem_lucro = :margem_lucro,'
            . ' desconto_item = :desconto_item,'
            . ' percentual_realizado = :percentual_realizado,'
            . ' percentual_bdi = :percentual_bdi'
            . ' WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':grupo' => (string)($data['grupo'] ?? ''),
            ':categoria' => (string)($data['categoria'] ?? ''),
            ':codigo' => (string)($data['codigo'] ?? ''),
            ':descricao' => (string)($data['descricao'] ?? ''),
            ':quantidade' => $quantidade,
            ':unidade' => (string)($data['unidade'] ?? ''),
            ':valor_unitario' => $valorUnitario,
            ':valor_total' => $valorTotal,
            ':ordem' => (int)($data['ordem'] ?? 0),
            ':etapa' => (string)($data['etapa'] ?? ''),
            ':custo_material' => $custoMaterial,
            ':custo_mao_obra' => $custoMaoObra,
            ':valor_cobranca' => $valorCobranca,
            ':margem_lucro' => $margemLucro,
            ':desconto_item' => $descontoItem,
            ':percentual_realizado' => $percentualRealizado,
            ':percentual_bdi' => $percentualBdi,
        ]);
    }

    public static function validate(array $data): array
    {
        $errors = [];

        if (trim((string)($data['grupo'] ?? '')) === '') {
            $errors['grupo'] = 'Informe o grupo.';
        }
        if (trim((string)($data['categoria'] ?? '')) === '') {
            $errors['categoria'] = 'Informe a categoria.';
        }
        if (trim((string)($data['codigo'] ?? '')) === '') {
            $errors['codigo'] = 'Informe o código.';
        }
        if (trim((string)($data['descricao'] ?? '')) === '') {
            $errors['descricao'] = 'Informe a descrição.';
        }

        $valorUnitarioRaw = (string)($data['valor_unitario'] ?? '');
        $quantidadeRaw = (string)($data['quantidade'] ?? '');

        $valorUnitarioParsed = self::parsePtBrNumber($valorUnitarioRaw);
        $quantidadeParsed = self::parsePtBrNumber($quantidadeRaw);

        $valorTotal = self::calculateTotal($quantidadeParsed, $valorUnitarioParsed);
        if ($valorUnitarioParsed > self::MAX_DECIMAL_15_2) {
            $errors['valor_unitario'] = 'Valor unitário muito alto.';
        }
        if ($quantidadeParsed > self::MAX_DECIMAL_15_2) {
            $errors['quantidade'] = 'Quantidade muito alta.';
        }
        if ($valorTotal > self::MAX_DECIMAL_15_2) {
            $errors['valor_unitario'] = 'O total (quantidade x valor unitário) ficou muito alto.';
        }

        if (trim($valorUnitarioRaw) === '' || $valorUnitarioParsed == 0.0 && !preg_match('/^\s*0([\.,]0+)?\s*$/', $valorUnitarioRaw)) {
            $errors['valor_unitario'] = 'Informe um valor unitário válido.';
        }
        if (trim($quantidadeRaw) === '' || $quantidadeParsed == 0.0 && !preg_match('/^\s*0([\.,]0+)?\s*$/', $quantidadeRaw)) {
            $errors['quantidade'] = 'Informe uma quantidade válida.';
        }

        if (trim((string)($data['unidade'] ?? '')) === '') {
            $errors['unidade'] = 'Informe a unidade.';
        }

        return $errors;
    }

    public static function normalize(array $data): array
    {
        $out = [];
        $out['grupo'] = trim((string)($data['grupo'] ?? ''));
        $out['categoria'] = trim((string)($data['categoria'] ?? ''));
        $out['codigo'] = trim((string)($data['codigo'] ?? ''));
        $out['descricao'] = trim((string)($data['descricao'] ?? ''));
        $out['unidade'] = trim((string)($data['unidade'] ?? ''));
        $out['etapa'] = trim((string)($data['etapa'] ?? ''));
        $out['ordem'] = (int)($data['ordem'] ?? 0);

        $out['valor_unitario'] = self::parsePtBrNumber((string)($data['valor_unitario'] ?? '0'));
        $out['quantidade'] = self::parsePtBrNumber((string)($data['quantidade'] ?? '0'));
        $out['custo_material'] = self::parsePtBrNumber((string)($data['custo_material'] ?? '0'));
        $out['custo_mao_obra'] = self::parsePtBrNumber((string)($data['custo_mao_obra'] ?? '0'));
        $out['margem_lucro'] = self::parsePtBrNumber((string)($data['margem_lucro'] ?? '0'));
        $out['desconto_item'] = self::parsePtBrNumber((string)($data['desconto_item'] ?? '0'));

        $percentualRealizado = self::parsePtBrNumber((string)($data['percentual_realizado'] ?? '0'));
        if ($percentualRealizado < 0) {
            $percentualRealizado = 0;
        }
        if ($percentualRealizado > 100) {
            $percentualRealizado = 100;
        }
        $out['percentual_realizado'] = $percentualRealizado;

        return $out;
    }

    public static function formatMoney(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    public static function formatNumber(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    public static function calculateValorCobranca(float $custoMaterial, float $custoMaoObra, float $margemLucro, float $descontoItem): float
    {
        $custoTotal = $custoMaterial + $custoMaoObra;
        $valorComMargem = $custoTotal * (1 + ($margemLucro / 100));
        $valorFinal = $valorComMargem * (1 - ($descontoItem / 100));
        return round($valorFinal, 2);
    }

    public static function getTotaisPorEtapa(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT etapa, '
            . 'SUM(custo_material) as total_material, '
            . 'SUM(custo_mao_obra) as total_mao_obra, '
            . 'SUM(valor_cobranca) as total_cobranca '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id '
            . 'GROUP BY etapa '
            . 'ORDER BY ordem'
        );
        $stmt->execute([':id' => $orcamentoId]);
        return $stmt->fetchAll();
    }

    public static function getTotaisPorCategoria(int $orcamentoId, string $etapa = ''): array
    {
        $pdo = Database::pdo();
        if ($etapa !== '') {
            $stmt = $pdo->prepare(
                'SELECT categoria, '
                . 'SUM(custo_material) as total_material, '
                . 'SUM(custo_mao_obra) as total_mao_obra, '
                . 'SUM(valor_cobranca) as total_cobranca '
                . 'FROM orcamento_itens '
                . 'WHERE orcamento_id = :id AND etapa = :etapa '
                . 'GROUP BY categoria '
                . 'ORDER BY ordem'
            );
            $stmt->execute([':id' => $orcamentoId, ':etapa' => $etapa]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT categoria, '
                . 'SUM(custo_material) as total_material, '
                . 'SUM(custo_mao_obra) as total_mao_obra, '
                . 'SUM(valor_cobranca) as total_cobranca '
                . 'FROM orcamento_itens '
                . 'WHERE orcamento_id = :id '
                . 'GROUP BY categoria '
                . 'ORDER BY ordem'
            );
            $stmt->execute([':id' => $orcamentoId]);
        }
        return $stmt->fetchAll();
    }

    public static function getTotaisGerais(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT '
            . 'SUM(custo_material) as total_material, '
            . 'SUM(custo_mao_obra) as total_mao_obra, '
            . 'SUM(valor_cobranca) as total_cobranca, '
            . 'SUM(custo_material + custo_mao_obra) as custo_total '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $result = $stmt->fetch();
        if (!$result) {
            return [
                'total_material' => 0,
                'total_mao_obra' => 0,
                'total_cobranca' => 0,
                'custo_total' => 0,
            ];
        }

        $totalMaterial = (float)($result['total_material'] ?? 0);
        $totalMaoObra = (float)($result['total_mao_obra'] ?? 0);
        $totalCobranca = (float)($result['total_cobranca'] ?? 0);
        $custoTotal = (float)($result['custo_total'] ?? 0);

        if ($totalCobranca <= 0 && $custoTotal > 0) {
            $totalCobranca = $custoTotal;
        }

        return [
            'total_material' => $totalMaterial,
            'total_mao_obra' => $totalMaoObra,
            'total_cobranca' => $totalCobranca,
            'custo_total' => $custoTotal,
        ];
    }

    /**
     * Agrupa itens por finalidade (grupo_finalidade), separando material e mão de obra
     * Retorna array com estrutura: [grupo_finalidade => ['material' => [...], 'mao_obra' => [...]]]
     */
    public static function getItensAgrupadosPorFinalidade(int $orcamentoId, string $etapa = ''): array
    {
        $pdo = Database::pdo();
        
        if ($etapa !== '') {
            $stmt = $pdo->prepare(
                'SELECT * FROM orcamento_itens '
                . 'WHERE orcamento_id = :id AND etapa = :etapa '
                . 'ORDER BY grupo_finalidade, categoria, ordem, id'
            );
            $stmt->execute([':id' => $orcamentoId, ':etapa' => $etapa]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT * FROM orcamento_itens '
                . 'WHERE orcamento_id = :id '
                . 'ORDER BY grupo_finalidade, categoria, ordem, id'
            );
            $stmt->execute([':id' => $orcamentoId]);
        }
        
        $items = $stmt->fetchAll();
        $agrupados = [];
        
        foreach ($items as $item) {
            $finalidade = $item['grupo_finalidade'] ?: $item['grupo'];
            
            if (!isset($agrupados[$finalidade])) {
                $agrupados[$finalidade] = [
                    'material' => [],
                    'mao_obra' => [],
                    'total_material' => 0.0,
                    'total_mao_obra' => 0.0,
                    'total_geral' => 0.0,
                ];
            }
            
            // Determinar se é material ou mão de obra baseado na categoria
            $isMaterial = stripos($item['categoria'], 'MATERIAL') !== false || 
                         stripos($item['categoria'], 'CUSTO PREVISTO') !== false;
            
            if ($isMaterial) {
                $agrupados[$finalidade]['material'][] = $item;
                $agrupados[$finalidade]['total_material'] += (float)$item['custo_material'];
            } else {
                $agrupados[$finalidade]['mao_obra'][] = $item;
                $agrupados[$finalidade]['total_mao_obra'] += (float)$item['custo_mao_obra'];
            }
            
            $agrupados[$finalidade]['total_geral'] += (float)$item['valor_cobranca'];
        }
        
        return $agrupados;
    }

    /**
     * Retorna resumo de valores por etapa com separação de material e mão de obra
     */
    public static function getResumoEtapas(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT '
            . 'etapa, '
            . 'SUM(custo_material) as total_material, '
            . 'SUM(custo_mao_obra) as total_mao_obra, '
            . 'SUM(valor_cobranca) as total_cobranca, '
            . 'SUM(custo_material + custo_mao_obra) as custo_total, '
            . 'COUNT(*) as total_itens '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id '
            . 'GROUP BY etapa '
            . 'ORDER BY MIN(ordem)'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $totalCobranca = (float)($row['total_cobranca'] ?? 0);
            $custoTotal = (float)($row['custo_total'] ?? 0);
            if ($totalCobranca <= 0 && $custoTotal > 0) {
                $row['total_cobranca'] = $custoTotal;
            }
        }
        unset($row);
        return $rows;
    }

    public static function getResumoPdfEtapas(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT '
            . 'grupo, '
            . 'etapa, '
            . 'SUM(valor_cobranca) as total_cobranca, '
            . 'SUM(custo_material + custo_mao_obra) as custo_total, '
            . 'CASE '
            . '  WHEN SUM(valor_cobranca) > 0 THEN (SUM(valor_cobranca * percentual_realizado) / SUM(valor_cobranca)) '
            . '  ELSE AVG(percentual_realizado) '
            . 'END as percentual_realizado '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id '
            . 'GROUP BY etapa, grupo '
            . 'ORDER BY MIN(ordem), etapa, grupo'
        );
        $stmt->execute([':id' => $orcamentoId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $totalCobranca = (float)($row['total_cobranca'] ?? 0);
            $custoTotal = (float)($row['custo_total'] ?? 0);
            if ($totalCobranca <= 0 && $custoTotal > 0) {
                $row['total_cobranca'] = $custoTotal;
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Retorna resumo de valores por grupo/finalidade dentro de uma etapa
     */
    public static function getResumoGruposPorEtapa(int $orcamentoId, string $etapa): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT '
            . 'COALESCE(grupo_finalidade, grupo) as grupo_nome, '
            . 'SUM(custo_material) as total_material, '
            . 'SUM(custo_mao_obra) as total_mao_obra, '
            . 'SUM(valor_cobranca) as total_cobranca, '
            . 'COUNT(*) as total_itens '
            . 'FROM orcamento_itens '
            . 'WHERE orcamento_id = :id AND etapa = :etapa '
            . 'GROUP BY grupo_nome '
            . 'ORDER BY MIN(ordem)'
        );
        $stmt->execute([':id' => $orcamentoId, ':etapa' => $etapa]);
        return $stmt->fetchAll();
    }

    /**
     * Atualiza o tipo de custo e grupo de finalidade de um item
     */
    public static function updateTipoEGrupo(int $id, string $tipoCusto, string $grupoFinalidade): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE orcamento_itens SET '
            . 'tipo_custo = :tipo_custo, '
            . 'grupo_finalidade = :grupo_finalidade '
            . 'WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':tipo_custo' => $tipoCusto,
            ':grupo_finalidade' => $grupoFinalidade,
        ]);
    }
}
