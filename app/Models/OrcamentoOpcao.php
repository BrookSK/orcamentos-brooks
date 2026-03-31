<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class OrcamentoOpcao
{
    public static function allByTipo(string $tipo): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM orcamento_opcoes WHERE tipo = :tipo ORDER BY nome');
        $stmt->execute([':tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    public static function namesByTipo(string $tipo): array
    {
        $rows = self::allByTipo($tipo);
        $out = [];
        foreach ($rows as $r) {
            $out[] = (string)($r['nome'] ?? '');
        }
        return array_values(array_filter($out, static fn($v) => $v !== ''));
    }

    public static function create(string $tipo, string $nome): void
    {
        $pdo = Database::pdo();
        $now = self::nowForDb($pdo);

        $stmt = $pdo->prepare('INSERT INTO orcamento_opcoes (tipo, nome, created_at) VALUES (:tipo, :nome, :created_at)');
        $stmt->execute([
            ':tipo' => $tipo,
            ':nome' => $nome,
            ':created_at' => $now,
        ]);
    }

    public static function createIfNotExists(string $tipo, string $nome): void
    {
        $tipo = trim($tipo);
        $nome = trim($nome);
        if ($tipo === '' || $nome === '') {
            return;
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM orcamento_opcoes WHERE tipo = :tipo AND nome = :nome LIMIT 1');
        $stmt->execute([':tipo' => $tipo, ':nome' => $nome]);
        $exists = $stmt->fetchColumn();
        if ($exists) {
            return;
        }

        self::create($tipo, $nome);
    }

    public static function seedFromTemplateJson(string $jsonPath): void
    {
        if (!is_file($jsonPath)) {
            return;
        }

        $raw = file_get_contents($jsonPath);
        if (!is_string($raw) || $raw === '') {
            return;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return;
        }

        $addUnidadeFromItem = static function (array $item): void {
            $unidade = trim((string)($item['unidade'] ?? ''));
            if ($unidade !== '') {
                self::createIfNotExists('unidade', $unidade);
            }
        };

        $walkSections = static function (string $grupoNome, array $sections) use (&$walkSections, $addUnidadeFromItem): void {
            $grupoNome = trim($grupoNome);
            if ($grupoNome !== '') {
                self::createIfNotExists('grupo', $grupoNome);
            }

            foreach ($sections as $section) {
                if (!is_array($section)) {
                    continue;
                }

                $categoria = trim((string)($section['descricao'] ?? ''));
                if ($categoria !== '') {
                    self::createIfNotExists('categoria', $categoria);
                    self::createIfNotExists('grupo', $categoria);
                }

                if (!empty($section['itens']) && is_array($section['itens'])) {
                    foreach ($section['itens'] as $it) {
                        if (!is_array($it)) {
                            continue;
                        }
                        $addUnidadeFromItem($it);
                    }
                }

                if (!empty($section['subgrupos']) && is_array($section['subgrupos'])) {
                    foreach ($section['subgrupos'] as $sub) {
                        if (!is_array($sub)) {
                            continue;
                        }

                        $subCategoria = trim((string)($sub['descricao'] ?? ''));
                        if ($subCategoria !== '') {
                            self::createIfNotExists('categoria', $subCategoria);
                        }

                        if (!empty($sub['itens']) && is_array($sub['itens'])) {
                            foreach ($sub['itens'] as $it) {
                                if (!is_array($it)) {
                                    continue;
                                }
                                $addUnidadeFromItem($it);
                            }
                        }
                    }
                }
            }
        };

        if (!empty($decoded['servicos_preliminares']) && is_array($decoded['servicos_preliminares'])) {
            $walkSections('SERVIÇOS PRELIMINARES', $decoded['servicos_preliminares']);
        }

        if (!empty($decoded['obra']) && is_array($decoded['obra'])) {
            $walkSections('OBRA', $decoded['obra']);
        }
    }

    public static function delete(int $id, string $tipo): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM orcamento_opcoes WHERE id = :id AND tipo = :tipo');
        $stmt->execute([':id' => $id, ':tipo' => $tipo]);
    }

    public static function update(int $id, string $tipo, string $nome): void
    {
        $pdo = Database::pdo();
        
        // Verificar se já existe outro registro com o mesmo nome e tipo
        $stmt = $pdo->prepare('SELECT id FROM orcamento_opcoes WHERE tipo = :tipo AND nome = :nome AND id != :id LIMIT 1');
        $stmt->execute([
            ':tipo' => $tipo,
            ':nome' => $nome,
            ':id' => $id,
        ]);
        
        if ($stmt->fetchColumn()) {
            throw new \Exception('Já existe um registro com este nome.');
        }
        
        $stmt = $pdo->prepare('UPDATE orcamento_opcoes SET nome = :nome WHERE id = :id AND tipo = :tipo');
        $stmt->execute([
            ':id' => $id,
            ':tipo' => $tipo,
            ':nome' => $nome,
        ]);
    }

    public static function validate(string $nome): array
    {
        $errors = [];
        if (trim($nome) === '') {
            $errors['nome'] = 'Informe um nome.';
        }
        return $errors;
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
