<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrcamentoDesconto
{
    public static function getByOrcamento(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamento_descontos WHERE orcamento_id = :id ORDER BY tipo, referencia');
        $stmt->execute([':id' => $orcamentoId]);
        return $stmt->fetchAll();
    }

    public static function getDesconto(int $orcamentoId, string $tipo, string $referencia): float
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT desconto FROM orcamento_descontos WHERE orcamento_id = :id AND tipo = :tipo AND referencia = :ref');
        $stmt->execute([':id' => $orcamentoId, ':tipo' => $tipo, ':ref' => $referencia]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (float)$result : 0.0;
    }

    public static function setDesconto(int $orcamentoId, string $tipo, string $referencia, float $desconto): void
    {
        $pdo = Database::pdo();
        $now = self::nowForDb($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO orcamento_descontos (orcamento_id, tipo, referencia, desconto, created_at, updated_at) '
            . 'VALUES (:orcamento_id, :tipo, :referencia, :desconto, :created_at, :updated_at) '
            . 'ON DUPLICATE KEY UPDATE desconto = :desconto2, updated_at = :updated_at2'
        );

        $stmt->execute([
            ':orcamento_id' => $orcamentoId,
            ':tipo' => $tipo,
            ':referencia' => $referencia,
            ':desconto' => $desconto,
            ':desconto2' => $desconto,
            ':created_at' => $now,
            ':updated_at' => $now,
            ':updated_at2' => $now,
        ]);
    }

    public static function deleteDesconto(int $orcamentoId, string $tipo, string $referencia): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM orcamento_descontos WHERE orcamento_id = :id AND tipo = :tipo AND referencia = :ref');
        $stmt->execute([':id' => $orcamentoId, ':tipo' => $tipo, ':ref' => $referencia]);
    }

    private static function nowForDb(\PDO $pdo): string
    {
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            return date('Y-m-d H:i:s');
        }
        return date('c');
    }
}
