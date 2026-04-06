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
            . ' numero_proposta, cliente_nome, arquiteto_nome, obra_nome, endereco_obra, local_obra, data, referencia, area_m2, contrato, tipo, tipo_orcamento, prazo_dias, rev,'
            . ' empresa_nome, empresa_endereco, empresa_telefone, empresa_email, logo_path, capa_path_1, capa_path_2, capa_path_3, capa_path_4, percentual_custos_adm, percentual_impostos, margem_mao_obra, margem_materiais, margem_equipamentos, ajuste_prorata_materiais, areas_personalizadas, created_at, updated_at'
            . ') VALUES ('
            . ' :numero_proposta, :cliente_nome, :arquiteto_nome, :obra_nome, :endereco_obra, :local_obra, :data, :referencia, :area_m2, :contrato, :tipo, :tipo_orcamento, :prazo_dias, :rev,'
            . ' :empresa_nome, :empresa_endereco, :empresa_telefone, :empresa_email, :logo_path, :capa_path_1, :capa_path_2, :capa_path_3, :capa_path_4, :percentual_custos_adm, :percentual_impostos, :margem_mao_obra, :margem_materiais, :margem_equipamentos, :ajuste_prorata_materiais, :areas_personalizadas, :created_at, :updated_at'
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
            ':tipo_orcamento' => $data['tipo_orcamento'] ?? 'manual',
            ':prazo_dias' => $data['prazo_dias'] !== '' ? (int)$data['prazo_dias'] : null,
            ':rev' => $data['rev'] !== '' ? (string)$data['rev'] : null,
            ':empresa_nome' => $data['empresa_nome'] !== '' ? (string)$data['empresa_nome'] : null,
            ':empresa_endereco' => $data['empresa_endereco'] !== '' ? (string)$data['empresa_endereco'] : null,
            ':empresa_telefone' => $data['empresa_telefone'] !== '' ? (string)$data['empresa_telefone'] : null,
            ':empresa_email' => $data['empresa_email'] !== '' ? (string)$data['empresa_email'] : null,
            ':logo_path' => $data['logo_path'] !== '' ? (string)$data['logo_path'] : null,
            ':capa_path_1' => $data['capa_path_1'] !== '' ? (string)$data['capa_path_1'] : null,
            ':capa_path_2' => $data['capa_path_2'] !== '' ? (string)$data['capa_path_2'] : null,
            ':capa_path_3' => $data['capa_path_3'] !== '' ? (string)$data['capa_path_3'] : null,
            ':capa_path_4' => $data['capa_path_4'] !== '' ? (string)$data['capa_path_4'] : null,
            ':percentual_custos_adm' => (float)($data['percentual_custos_adm'] ?? 0),
            ':percentual_impostos' => (float)($data['percentual_impostos'] ?? 0),
            ':margem_mao_obra' => (float)($data['margem_mao_obra'] ?? 0),
            ':margem_materiais' => (float)($data['margem_materiais'] ?? 0),
            ':margem_equipamentos' => (float)($data['margem_equipamentos'] ?? 20),
            ':ajuste_prorata_materiais' => (float)($data['ajuste_prorata_materiais'] ?? 0),
            ':areas_personalizadas' => !empty($data['areas_personalizadas']) ? (string)$data['areas_personalizadas'] : null,
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
            . ' tipo_orcamento = :tipo_orcamento,'
            . ' prazo_dias = :prazo_dias,'
            . ' rev = :rev,'
            . ' empresa_nome = :empresa_nome,'
            . ' empresa_endereco = :empresa_endereco,'
            . ' empresa_telefone = :empresa_telefone,'
            . ' empresa_email = :empresa_email,'
            . ' logo_path = :logo_path,'
            . ' capa_path_1 = :capa_path_1,'
            . ' capa_path_2 = :capa_path_2,'
            . ' capa_path_3 = :capa_path_3,'
            . ' capa_path_4 = :capa_path_4,'
            . ' percentual_custos_adm = :percentual_custos_adm,'
            . ' percentual_impostos = :percentual_impostos,'
            . ' valor_entrada = :valor_entrada,'
            . ' margem_mao_obra = :margem_mao_obra,'
            . ' margem_materiais = :margem_materiais,'
            . ' margem_equipamentos = :margem_equipamentos,'
            . ' ajuste_prorata_materiais = :ajuste_prorata_materiais,'
            . ' areas_personalizadas = :areas_personalizadas,'
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
            ':tipo_orcamento' => $data['tipo_orcamento'] ?? 'manual',
            ':prazo_dias' => $data['prazo_dias'] !== '' ? (int)$data['prazo_dias'] : null,
            ':rev' => $data['rev'] !== '' ? (string)$data['rev'] : null,
            ':empresa_nome' => $data['empresa_nome'] !== '' ? (string)$data['empresa_nome'] : null,
            ':empresa_endereco' => $data['empresa_endereco'] !== '' ? (string)$data['empresa_endereco'] : null,
            ':empresa_telefone' => $data['empresa_telefone'] !== '' ? (string)$data['empresa_telefone'] : null,
            ':empresa_email' => $data['empresa_email'] !== '' ? (string)$data['empresa_email'] : null,
            ':logo_path' => $data['logo_path'] !== '' ? (string)$data['logo_path'] : null,
            ':capa_path_1' => $data['capa_path_1'] !== '' ? (string)$data['capa_path_1'] : null,
            ':capa_path_2' => $data['capa_path_2'] !== '' ? (string)$data['capa_path_2'] : null,
            ':capa_path_3' => $data['capa_path_3'] !== '' ? (string)$data['capa_path_3'] : null,
            ':capa_path_4' => $data['capa_path_4'] !== '' ? (string)$data['capa_path_4'] : null,
            ':percentual_custos_adm' => (float)($data['percentual_custos_adm'] ?? 0),
            ':percentual_impostos' => (float)($data['percentual_impostos'] ?? 0),
            ':valor_entrada' => (float)($data['valor_entrada'] ?? 0),
            ':margem_mao_obra' => (float)($data['margem_mao_obra'] ?? 0),
            ':margem_materiais' => (float)($data['margem_materiais'] ?? 0),
            ':margem_equipamentos' => (float)($data['margem_equipamentos'] ?? 20),
            ':ajuste_prorata_materiais' => (float)($data['ajuste_prorata_materiais'] ?? 0),
            ':areas_personalizadas' => !empty($data['areas_personalizadas']) ? (string)$data['areas_personalizadas'] : null,
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
            'tipo_orcamento',
            'prazo_dias',
            'rev',
            'empresa_nome',
            'empresa_endereco',
            'empresa_telefone',
            'empresa_email',
            'logo_path',
            'capa_path_1',
            'capa_path_2',
            'capa_path_3',
            'capa_path_4',
            'percentual_custos_adm',
            'percentual_impostos',
            'valor_entrada',
            'margem_mao_obra',
            'margem_materiais',
            'margem_equipamentos',
            'ajuste_prorata_materiais',
        ];

        $out = [];
        foreach ($fields as $f) {
            $out[$f] = trim((string)($data[$f] ?? ''));
        }

        $out['area_m2'] = $out['area_m2'] !== '' ? (string)self::parsePtBrNumber((string)$out['area_m2']) : '';
        $out['prazo_dias'] = $out['prazo_dias'] !== '' ? (string)(int)$out['prazo_dias'] : '';
        $out['percentual_custos_adm'] = $out['percentual_custos_adm'] !== '' ? self::parsePtBrNumber((string)$out['percentual_custos_adm']) : 0.0;
        $out['percentual_impostos'] = $out['percentual_impostos'] !== '' ? self::parsePtBrNumber((string)$out['percentual_impostos']) : 0.0;
        $out['valor_entrada'] = $out['valor_entrada'] !== '' ? self::parsePtBrNumber((string)$out['valor_entrada']) : 0.0;
        $out['margem_mao_obra'] = $out['margem_mao_obra'] !== '' ? self::parsePtBrNumber((string)$out['margem_mao_obra']) : 50.0;
        $out['margem_materiais'] = $out['margem_materiais'] !== '' ? self::parsePtBrNumber((string)$out['margem_materiais']) : 20.0;
        $out['margem_equipamentos'] = $out['margem_equipamentos'] !== '' ? self::parsePtBrNumber((string)$out['margem_equipamentos']) : 20.0;
        $out['ajuste_prorata_materiais'] = $out['ajuste_prorata_materiais'] !== '' ? self::parsePtBrNumber((string)$out['ajuste_prorata_materiais']) : 0.0;
        
        // Processar áreas personalizadas
        if (isset($data['areas']) && is_array($data['areas'])) {
            $areas = [];
            foreach ($data['areas'] as $area) {
                if (!empty($area['nome'])) {
                    $areas[] = [
                        'nome' => trim((string)$area['nome']),
                        'm2' => !empty($area['m2']) ? (float)$area['m2'] : 0,
                        'fator' => !empty($area['fator']) ? (float)$area['fator'] : 1,
                        'tipo_area' => !empty($area['tipo_area']) ? (string)$area['tipo_area'] : 'terreno',
                    ];
                }
            }
            $out['areas_personalizadas'] = !empty($areas) ? json_encode($areas, JSON_UNESCAPED_UNICODE) : '';
        }
        
        // Garantir que tipo_orcamento seja sempre 'manual' ou 'sinapi'
        if (!in_array($out['tipo_orcamento'], ['manual', 'sinapi'], true)) {
            $out['tipo_orcamento'] = 'manual';
        }

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
