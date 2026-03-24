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

        $valorUnitario = (float)$data['valor_unitario'];
        $quantidade = (float)$data['quantidade'];
        $valorTotal = self::calculateTotal($quantidade, $valorUnitario);

        $stmt = $pdo->prepare(
            'INSERT INTO orcamento_itens (orcamento_id, grupo, categoria, codigo, descricao, quantidade, unidade, valor_unitario, valor_total, ordem) '
            . 'VALUES (:orcamento_id, :grupo, :categoria, :codigo, :descricao, :quantidade, :unidade, :valor_unitario, :valor_total, :ordem)'
        );

        $stmt->execute([
            ':orcamento_id' => $orcamentoId,
            ':grupo' => (string)$data['grupo'],
            ':categoria' => (string)$data['categoria'],
            ':codigo' => (string)$data['codigo'],
            ':descricao' => (string)$data['descricao'],
            ':quantidade' => (float)$quantidade,
            ':unidade' => (string)$data['unidade'],
            ':valor_unitario' => (float)$valorUnitario,
            ':valor_total' => (float)$valorTotal,
            ':ordem' => (int)($data['ordem'] ?? 0),
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

        $valorUnitario = (float)$data['valor_unitario'];
        $quantidade = (float)$data['quantidade'];
        $valorTotal = self::calculateTotal($quantidade, $valorUnitario);

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
            . ' ordem = :ordem'
            . ' WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':grupo' => (string)$data['grupo'],
            ':categoria' => (string)$data['categoria'],
            ':codigo' => (string)$data['codigo'],
            ':descricao' => (string)$data['descricao'],
            ':quantidade' => (float)$quantidade,
            ':unidade' => (string)$data['unidade'],
            ':valor_unitario' => (float)$valorUnitario,
            ':valor_total' => (float)$valorTotal,
            ':ordem' => (int)($data['ordem'] ?? 0),
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
        $out['ordem'] = (int)($data['ordem'] ?? 0);

        $out['valor_unitario'] = self::parsePtBrNumber((string)($data['valor_unitario'] ?? '0'));
        $out['quantidade'] = self::parsePtBrNumber((string)($data['quantidade'] ?? '0'));

        return $out;
    }

    private static function calculateTotal(float $quantidade, float $valorUnitario): float
    {
        return round($quantidade * $valorUnitario, 2);
    }

    public static function formatMoney(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    public static function formatNumber(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}
