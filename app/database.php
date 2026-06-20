<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $database = $GLOBALS['config']['database'] ?? [];
    $charset = (string) ($database['charset'] ?? 'utf8mb4');
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $database['host'] ?? '127.0.0.1',
        $database['port'] ?? '3306',
        $database['database'] ?? '',
        $charset
    );

    try {
        $pdo = new PDO($dsn, (string) ($database['username'] ?? ''), (string) ($database['password'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        throw new RuntimeException('Database connection failed. Check portfolio/.env and confirm MySQL is running.', 0, $exception);
    }

    return $pdo;
}
