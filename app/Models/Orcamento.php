<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Orcamento
{
    private static function parsePtBrNumber(string $value): float
    {
        $value = trim($value);
        if ($value === '') {
            return 0.0;
        }

        $value = str_replace(' ', '', $value);

        $hasComma = strpos($value, ',') !== false;
        $hasDot = strpos($value, '.') !== false;

        if ($hasComma) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return (float)$value;
        }

        if ($hasDot) {
            $lastDot = strrpos($value, '.');
            $dec = substr($value, $lastDot + 1);
            if (preg_match('/^\d{1,2}$/', $dec) === 1) {
                return (float)$value;
            }

            $value = str_replace('.', '', $value);
            return (float)$value;
        }

        return (float)$value;
    }

    public static function all(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT * FROM orcamentos ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::pdo();

        $now = self::nowForDb($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO orcamentos ('
            . ' numero_proposta, cliente_nome, arquiteto_nome, obra_nome, endereco_obra, local_obra, data, referencia, area_m2, contrato, tipo, prazo_dias, rev,'
            . ' empresa_nome, empresa_endereco, empresa_telefone, empresa_email, logo_path, created_at, updated_at'
            . ') VALUES ('
            . ' :numero_proposta, :cliente_nome, :arquiteto_nome, :obra_nome, :endereco_obra, :local_obra, :data, :referencia, :area_m2, :contrato, :tipo, :prazo_dias, :rev,'
            . ' :empresa_nome, :empresa_endereco, :empresa_telefone, :empresa_email, :logo_path, :created_at, :updated_at'
            . ')'
        );

        $stmt->execute([
            ':numero_proposta' => (string)$data['numero_proposta'],
            ':cliente_nome' => (string)$data['cliente_nome'],
            ':arquiteto_nome' => $data['arquiteto_nome'] !== '' ? (string)$data['arquiteto_nome'] : null,
            ':obra_nome' => $data['obra_nome'] !== '' ? (string)$data['obra_nome'] : null,
            ':endereco_obra' => $data['endereco_obra'] !== '' ? (string)$data['endereco_obra'] : null,
            ':local_obra' => $data['local_obra'] !== '' ? (string)$data['local_obra'] : null,
            ':data' => $data['data'] !== '' ? (string)$data['data'] : null,
            ':referencia' => $data['referencia'] !== '' ? (string)$data['referencia'] : null,
            ':area_m2' => $data['area_m2'] !== '' ? (float)$data['area_m2'] : null,
            ':contrato' => $data['contrato'] !== '' ? (string)$data['contrato'] : null,
            ':tipo' => $data['tipo'] !== '' ? (string)$data['tipo'] : null,
            ':prazo_dias' => $data['prazo_dias'] !== '' ? (int)$data['prazo_dias'] : null,
            ':rev' => $data['rev'] !== '' ? (string)$data['rev'] : null,
            ':empresa_nome' => $data['empresa_nome'] !== '' ? (string)$data['empresa_nome'] : null,
            ':empresa_endereco' => $data['empresa_endereco'] !== '' ? (string)$data['empresa_endereco'] : null,
            ':empresa_telefone' => $data['empresa_telefone'] !== '' ? (string)$data['empresa_telefone'] : null,
            ':empresa_email' => $data['empresa_email'] !== '' ? (string)$data['empresa_email'] : null,
            ':logo_path' => $data['logo_path'] !== '' ? (string)$data['logo_path'] : null,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::pdo();
        $now = self::nowForDb($pdo);

        $stmt = $pdo->prepare(
            'UPDATE orcamentos SET'
            . ' numero_proposta = :numero_proposta,'
            . ' cliente_nome = :cliente_nome,'
            . ' arquiteto_nome = :arquiteto_nome,'
            . ' obra_nome = :obra_nome,'
            . ' endereco_obra = :endereco_obra,'
            . ' local_obra = :local_obra,'
            . ' data = :data,'
            . ' referencia = :referencia,'
            . ' area_m2 = :area_m2,'
            . ' contrato = :contrato,'
            . ' tipo = :tipo,'
            . ' prazo_dias = :prazo_dias,'
            . ' rev = :rev,'
            . ' empresa_nome = :empresa_nome,'
            . ' empresa_endereco = :empresa_endereco,'
            . ' empresa_telefone = :empresa_telefone,'
            . ' empresa_email = :empresa_email,'
            . ' logo_path = :logo_path,'
            . ' updated_at = :updated_at'
            . ' WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
            ':numero_proposta' => (string)$data['numero_proposta'],
            ':cliente_nome' => (string)$data['cliente_nome'],
            ':arquiteto_nome' => $data['arquiteto_nome'] !== '' ? (string)$data['arquiteto_nome'] : null,
            ':obra_nome' => $data['obra_nome'] !== '' ? (string)$data['obra_nome'] : null,
            ':endereco_obra' => $data['endereco_obra'] !== '' ? (string)$data['endereco_obra'] : null,
            ':local_obra' => $data['local_obra'] !== '' ? (string)$data['local_obra'] : null,
            ':data' => $data['data'] !== '' ? (string)$data['data'] : null,
            ':referencia' => $data['referencia'] !== '' ? (string)$data['referencia'] : null,
            ':area_m2' => $data['area_m2'] !== '' ? (float)$data['area_m2'] : null,
            ':contrato' => $data['contrato'] !== '' ? (string)$data['contrato'] : null,
            ':tipo' => $data['tipo'] !== '' ? (string)$data['tipo'] : null,
            ':prazo_dias' => $data['prazo_dias'] !== '' ? (int)$data['prazo_dias'] : null,
            ':rev' => $data['rev'] !== '' ? (string)$data['rev'] : null,
            ':empresa_nome' => $data['empresa_nome'] !== '' ? (string)$data['empresa_nome'] : null,
            ':empresa_endereco' => $data['empresa_endereco'] !== '' ? (string)$data['empresa_endereco'] : null,
            ':empresa_telefone' => $data['empresa_telefone'] !== '' ? (string)$data['empresa_telefone'] : null,
            ':empresa_email' => $data['empresa_email'] !== '' ? (string)$data['empresa_email'] : null,
            ':logo_path' => $data['logo_path'] !== '' ? (string)$data['logo_path'] : null,
            ':updated_at' => $now,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM orcamentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function validate(array $data): array
    {
        $errors = [];

        if (trim((string)($data['numero_proposta'] ?? '')) === '') {
            $errors['numero_proposta'] = 'Informe o número da proposta.';
        }
        if (trim((string)($data['cliente_nome'] ?? '')) === '') {
            $errors['cliente_nome'] = 'Informe o cliente.';
        }

        return $errors;
    }

    public static function normalize(array $data): array
    {
        $fields = [
            'numero_proposta',
            'cliente_nome',
            'arquiteto_nome',
            'obra_nome',
            'endereco_obra',
            'local_obra',
            'data',
            'referencia',
            'area_m2',
            'contrato',
            'tipo',
            'prazo_dias',
            'rev',
            'empresa_nome',
            'empresa_endereco',
            'empresa_telefone',
            'empresa_email',
            'logo_path',
        ];

        $out = [];
        foreach ($fields as $f) {
            $out[$f] = trim((string)($data[$f] ?? ''));
        }

        $out['area_m2'] = $out['area_m2'] !== '' ? (string)self::parsePtBrNumber((string)$out['area_m2']) : '';
        $out['prazo_dias'] = $out['prazo_dias'] !== '' ? (string)(int)$out['prazo_dias'] : '';

        return $out;
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
