<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrcamentoCor
{
    public static function getCorPorEtapa(string $etapa): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamento_cores_etapas WHERE etapa = :etapa');
        $stmt->execute([':etapa' => $etapa]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result;
        }
        
        // Retornar cor padrão se não encontrar
        return [
            'etapa' => $etapa,
            'cor' => '#9E9E9E',
            'cor_nome' => 'Cinza',
            'icone' => '📋',
            'ordem' => 999,
        ];
    }

    public static function getAllCores(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT * FROM orcamento_cores_etapas ORDER BY ordem');
        return $stmt->fetchAll();
    }

    public static function setCorEtapa(string $etapa, string $cor, string $corNome = '', string $icone = '', int $ordem = 0): void
    {
        $pdo = Database::pdo();
        $now = self::nowForDb($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO orcamento_cores_etapas (etapa, cor, cor_nome, icone, ordem, created_at, updated_at) '
            . 'VALUES (:etapa, :cor, :cor_nome, :icone, :ordem, :created_at, :updated_at) '
            . 'ON DUPLICATE KEY UPDATE cor = :cor2, cor_nome = :cor_nome2, icone = :icone2, ordem = :ordem2, updated_at = :updated_at2'
        );

        $stmt->execute([
            ':etapa' => $etapa,
            ':cor' => $cor,
            ':cor_nome' => $corNome,
            ':icone' => $icone,
            ':ordem' => $ordem,
            ':cor2' => $cor,
            ':cor_nome2' => $corNome,
            ':icone2' => $icone,
            ':ordem2' => $ordem,
            ':created_at' => $now,
            ':updated_at' => $now,
            ':updated_at2' => $now,
        ]);
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
