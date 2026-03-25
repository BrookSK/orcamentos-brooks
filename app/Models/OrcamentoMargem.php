<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrcamentoMargem
{
    public static function getByOrcamento(int $orcamentoId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamento_margens WHERE orcamento_id = :id ORDER BY tipo, referencia');
        $stmt->execute([':id' => $orcamentoId]);
        return $stmt->fetchAll();
    }

    public static function getMargem(int $orcamentoId, string $tipo, string $referencia): float
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT margem FROM orcamento_margens WHERE orcamento_id = :id AND tipo = :tipo AND referencia = :ref');
        $stmt->execute([':id' => $orcamentoId, ':tipo' => $tipo, ':ref' => $referencia]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (float)$result : 0.0;
    }

    public static function setMargem(int $orcamentoId, string $tipo, string $referencia, float $margem): void
    {
        $pdo = Database::pdo();
        $now = self::nowForDb($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO orcamento_margens (orcamento_id, tipo, referencia, margem, created_at, updated_at) '
            . 'VALUES (:orcamento_id, :tipo, :referencia, :margem, :created_at, :updated_at) '
            . 'ON DUPLICATE KEY UPDATE margem = :margem2, updated_at = :updated_at2'
        );

        $stmt->execute([
            ':orcamento_id' => $orcamentoId,
            ':tipo' => $tipo,
            ':referencia' => $referencia,
            ':margem' => $margem,
            ':margem2' => $margem,
            ':created_at' => $now,
            ':updated_at' => $now,
            ':updated_at2' => $now,
        ]);
    }

    public static function deleteMargem(int $orcamentoId, string $tipo, string $referencia): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM orcamento_margens WHERE orcamento_id = :id AND tipo = :tipo AND referencia = :ref');
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
