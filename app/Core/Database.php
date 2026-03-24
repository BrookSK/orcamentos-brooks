<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $db = $config['db'] ?? [];

        $driver = (string)($db['driver'] ?? 'mysql');

        if ($driver === 'mysql') {
            $host = (string)($db['host'] ?? '127.0.0.1');
            $port = (string)($db['port'] ?? '3306');
            $name = (string)($db['name'] ?? 'orcamentos');
            $user = (string)($db['user'] ?? 'root');
            $pass = (string)($db['pass'] ?? '');
            $charset = (string)($db['charset'] ?? 'utf8mb4');

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            self::ensureSchemaMySql(self::$pdo);
        } elseif ($driver === 'sqlite') {
            $sqlitePath = $db['sqlite_path'] ?? null;
            if (!is_string($sqlitePath) || $sqlitePath === '') {
                throw new \RuntimeException('Missing sqlite_path configuration.');
            }

            $dir = dirname($sqlitePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            self::$pdo = new PDO('sqlite:' . $sqlitePath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            self::ensureSchemaSqlite(self::$pdo);
        } else {
            throw new \RuntimeException('Unsupported DB driver.');
        }

        return self::$pdo;
    }

    private static function ensureSchemaSqlite(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orcamentos (\n"
            . "  id INTEGER PRIMARY KEY AUTOINCREMENT,\n"
            . "  numero_proposta TEXT NOT NULL,\n"
            . "  cliente_nome TEXT NOT NULL,\n"
            . "  arquiteto_nome TEXT,\n"
            . "  obra_nome TEXT,\n"
            . "  endereco_obra TEXT,\n"
            . "  local_obra TEXT,\n"
            . "  data TEXT,\n"
            . "  referencia TEXT,\n"
            . "  area_m2 REAL,\n"
            . "  contrato TEXT,\n"
            . "  tipo TEXT,\n"
            . "  prazo_dias INTEGER,\n"
            . "  rev TEXT,\n"
            . "  empresa_nome TEXT,\n"
            . "  empresa_endereco TEXT,\n"
            . "  empresa_telefone TEXT,\n"
            . "  empresa_email TEXT,\n"
            . "  logo_path TEXT,\n"
            . "  created_at TEXT NOT NULL,\n"
            . "  updated_at TEXT NOT NULL\n"
            . ");"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orcamento_itens (\n"
            . "  id INTEGER PRIMARY KEY AUTOINCREMENT,\n"
            . "  orcamento_id INTEGER NOT NULL,\n"
            . "  grupo TEXT NOT NULL,\n"
            . "  categoria TEXT NOT NULL,\n"
            . "  codigo TEXT NOT NULL,\n"
            . "  descricao TEXT NOT NULL,\n"
            . "  quantidade REAL NOT NULL,\n"
            . "  unidade TEXT NOT NULL,\n"
            . "  valor_unitario REAL NOT NULL,\n"
            . "  valor_total REAL NOT NULL,\n"
            . "  ordem INTEGER NOT NULL DEFAULT 0\n"
            . ");"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orcamento_opcoes (\n"
            . "  id INTEGER PRIMARY KEY AUTOINCREMENT,\n"
            . "  tipo TEXT NOT NULL,\n"
            . "  nome TEXT NOT NULL,\n"
            . "  created_at TEXT NOT NULL\n"
            . ");"
        );

        $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_orcamento_opcoes_tipo_nome ON orcamento_opcoes(tipo, nome)');
    }

    private static function ensureSchemaMySql(PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orcamentos (\n"
            . "  id INT AUTO_INCREMENT PRIMARY KEY,\n"
            . "  numero_proposta VARCHAR(50) NOT NULL,\n"
            . "  cliente_nome VARCHAR(255) NOT NULL,\n"
            . "  arquiteto_nome VARCHAR(255) NULL,\n"
            . "  obra_nome VARCHAR(255) NULL,\n"
            . "  endereco_obra VARCHAR(255) NULL,\n"
            . "  local_obra VARCHAR(255) NULL,\n"
            . "  data DATE NULL,\n"
            . "  referencia VARCHAR(100) NULL,\n"
            . "  area_m2 DECIMAL(15,2) NULL,\n"
            . "  contrato VARCHAR(100) NULL,\n"
            . "  tipo VARCHAR(100) NULL,\n"
            . "  prazo_dias INT NULL,\n"
            . "  rev VARCHAR(20) NULL,\n"
            . "  empresa_nome VARCHAR(255) NULL,\n"
            . "  empresa_endereco VARCHAR(255) NULL,\n"
            . "  empresa_telefone VARCHAR(100) NULL,\n"
            . "  empresa_email VARCHAR(255) NULL,\n"
            . "  logo_path VARCHAR(255) NULL,\n"
            . "  created_at DATETIME NOT NULL,\n"
            . "  updated_at DATETIME NOT NULL,\n"
            . "  INDEX idx_orcamentos_numero (numero_proposta)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orcamento_itens (\n"
            . "  id INT AUTO_INCREMENT PRIMARY KEY,\n"
            . "  orcamento_id INT NOT NULL,\n"
            . "  grupo VARCHAR(255) NOT NULL,\n"
            . "  categoria VARCHAR(255) NOT NULL,\n"
            . "  codigo VARCHAR(50) NOT NULL,\n"
            . "  descricao TEXT NOT NULL,\n"
            . "  quantidade DECIMAL(15,2) NOT NULL,\n"
            . "  unidade VARCHAR(50) NOT NULL,\n"
            . "  valor_unitario DECIMAL(15,2) NOT NULL,\n"
            . "  valor_total DECIMAL(15,2) NOT NULL,\n"
            . "  ordem INT NOT NULL DEFAULT 0,\n"
            . "  CONSTRAINT fk_itens_orcamento FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE,\n"
            . "  INDEX idx_itens_orcamento (orcamento_id),\n"
            . "  INDEX idx_itens_grouping (orcamento_id, grupo, categoria, ordem, id)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orcamento_opcoes (\n"
            . "  id INT AUTO_INCREMENT PRIMARY KEY,\n"
            . "  tipo VARCHAR(30) NOT NULL,\n"
            . "  nome VARCHAR(255) NOT NULL,\n"
            . "  created_at DATETIME NOT NULL,\n"
            . "  UNIQUE KEY uq_orcamento_opcoes_tipo_nome (tipo, nome),\n"
            . "  INDEX idx_orcamento_opcoes_tipo (tipo)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
    }
}
