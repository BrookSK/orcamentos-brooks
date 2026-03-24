<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    private static function filePath(): string
    {
        $config = require __DIR__ . '/../../config/config.php';
        $path = $config['log']['path'] ?? null;
        if (is_string($path) && $path !== '') {
            return $path;
        }

        return __DIR__ . '/../../storage/logs/app.log';
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARN', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function exception(\Throwable $e, array $context = []): void
    {
        $context = array_merge($context, [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        self::write('EXCEPTION', 'Unhandled exception', $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        try {
            $file = self::filePath();
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $record = [
                'ts' => date('c'),
                'level' => $level,
                'message' => $message,
                'context' => $context,
            ];

            $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $ignored) {
        }
    }
}
