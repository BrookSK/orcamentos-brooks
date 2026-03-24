<?php

declare(strict_types=1);

return [
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'sql_orcamento_onsolutionsbrasil_com_br',
        'user' => getenv('DB_USER') ?: 'sql_orcamento_onsolutionsbrasil_com_br',
        'pass' => getenv('DB_PASS') ?: '70cd8876f1968',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'sqlite_path' => __DIR__ . '/../storage/database.sqlite',
    ],
    'log' => [
        'path' => getenv('APP_LOG_PATH') ?: (__DIR__ . '/../storage/logs/app.log'),
    ],
];
