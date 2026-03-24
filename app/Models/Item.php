<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Item
{
    public static function all(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT * FROM items ORDER BY categoria COLLATE NOCASE ASC, id ASC');
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['valor_total'] = self::calculateTotal((float)$item['quantidade'], (float)$item['valor_unitario']);
        }

        return $items;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $row['valor_total'] = self::calculateTotal((float)$row['quantidade'], (float)$row['valor_unitario']);
        return $row;
    }

    public static function create(array $data): int
    {
        $pdo = Database::pdo();

        $valorUnitario = (float)$data['valor_unitario'];
        $quantidade = (float)$data['quantidade'];
        $valorTotal = self::calculateTotal($quantidade, $valorUnitario);

        $stmt = $pdo->prepare(
            'INSERT INTO items (nome, categoria, unidade, valor_unitario, quantidade, valor_total) '
            . 'VALUES (:nome, :categoria, :unidade, :valor_unitario, :quantidade, :valor_total)'
        );

        $stmt->execute([
            ':nome' => (string)$data['nome'],
            ':categoria' => (string)$data['categoria'],
            ':unidade' => (string)$data['unidade'],
            ':valor_unitario' => $valorUnitario,
            ':quantidade' => $quantidade,
            ':valor_total' => $valorTotal,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::pdo();

        $valorUnitario = (float)$data['valor_unitario'];
        $quantidade = (float)$data['quantidade'];
        $valorTotal = self::calculateTotal($quantidade, $valorUnitario);

        $stmt = $pdo->prepare(
            'UPDATE items SET\n'
            . ' nome = :nome,\n'
            . ' categoria = :categoria,\n'
            . ' unidade = :unidade,\n'
            . ' valor_unitario = :valor_unitario,\n'
            . ' quantidade = :quantidade,\n'
            . ' valor_total = :valor_total\n'
            . ' WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':nome' => (string)$data['nome'],
            ':categoria' => (string)$data['categoria'],
            ':unidade' => (string)$data['unidade'],
            ':valor_unitario' => $valorUnitario,
            ':quantidade' => $quantidade,
            ':valor_total' => $valorTotal,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM items WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function validate(array $data): array
    {
        $errors = [];

        $nome = trim((string)($data['nome'] ?? ''));
        $categoria = trim((string)($data['categoria'] ?? ''));
        $unidade = trim((string)($data['unidade'] ?? ''));

        if ($nome === '') {
            $errors['nome'] = 'Informe o nome.';
        }
        if ($categoria === '') {
            $errors['categoria'] = 'Informe a categoria.';
        }
        if ($unidade === '') {
            $errors['unidade'] = 'Informe a unidade.';
        }

        $valorUnitarioRaw = str_replace(',', '.', (string)($data['valor_unitario'] ?? ''));
        $quantidadeRaw = str_replace(',', '.', (string)($data['quantidade'] ?? ''));

        if ($valorUnitarioRaw === '' || !is_numeric($valorUnitarioRaw)) {
            $errors['valor_unitario'] = 'Informe um valor unitário válido.';
        }
        if ($quantidadeRaw === '' || !is_numeric($quantidadeRaw)) {
            $errors['quantidade'] = 'Informe uma quantidade válida.';
        }

        return $errors;
    }

    public static function normalize(array $data): array
    {
        $data['nome'] = trim((string)($data['nome'] ?? ''));
        $data['categoria'] = trim((string)($data['categoria'] ?? ''));
        $data['unidade'] = trim((string)($data['unidade'] ?? ''));
        $data['valor_unitario'] = (float)str_replace(',', '.', (string)($data['valor_unitario'] ?? '0'));
        $data['quantidade'] = (float)str_replace(',', '.', (string)($data['quantidade'] ?? '0'));

        return $data;
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
