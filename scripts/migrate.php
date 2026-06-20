<?php

declare(strict_types=1);

/**
 * Incremental migration runner.
 *
 * Tracks applied migrations in a `schema_migrations` ledger so each file in
 * database/migrations/ runs exactly once, in filename order. Safe to run on
 * every deploy — already-applied migrations are skipped.
 *
 * Usage:
 *   php scripts/migrate.php              Apply any pending migrations.
 *   php scripts/migrate.php --baseline   Mark ALL current migrations as applied
 *                                        WITHOUT running them. Use once on a DB
 *                                        that was provisioned before the ledger
 *                                        existed (its schema is already current).
 */

require dirname(__DIR__) . '/app/bootstrap.php';

/**
 * Split a migration file into individual SQL statements.
 * Migrations are DDL only — no semicolons inside string literals.
 */
function migration_statements(string $sql): array
{
    $lines = preg_split('/\R/', $sql) ?: [];
    $kept = [];

    foreach ($lines as $line) {
        if (preg_match('/^\s*--/', $line)) {
            continue; // strip full-line comments
        }
        $kept[] = $line;
    }

    $clean = implode("\n", $kept);
    $parts = array_map('trim', explode(';', $clean));

    return array_values(array_filter($parts, static fn (string $s): bool => $s !== ''));
}

try {
    $pdo = db();

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY schema_migrations_migration_unique (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $applied = array_flip($pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));

    $files = glob(ROOT_PATH . '/database/migrations/*.sql') ?: [];
    sort($files);

    $record = $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (:migration)');
    $baseline = in_array('--baseline', array_slice($argv, 1), true);

    if ($baseline) {
        $marked = 0;

        foreach ($files as $file) {
            $name = basename($file);

            if (! isset($applied[$name])) {
                $record->execute(['migration' => $name]);
                echo "Baselined (marked applied, not run): {$name}\n";
                $marked++;
            }
        }

        echo $marked === 0
            ? "Nothing to baseline — ledger already covers every migration.\n"
            : "Baseline complete. {$marked} migration(s) marked as applied.\n";
        exit(0);
    }

    $ran = 0;

    foreach ($files as $file) {
        $name = basename($file);

        if (isset($applied[$name])) {
            continue;
        }

        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException('Unable to read migration file: ' . $name);
        }

        foreach (migration_statements($sql) as $statement) {
            try {
                $pdo->exec($statement);
            } catch (Throwable $statementError) {
                fwrite(STDERR, "Migration failed in {$name}:\n  " . $statementError->getMessage() . "\n");
                fwrite(STDERR, "While running statement:\n  " . preg_replace('/\s+/', ' ', $statement) . "\n");
                exit(1);
            }
        }

        $record->execute(['migration' => $name]);
        echo "Migrated: {$name}\n";
        $ran++;
    }

    echo $ran === 0
        ? "Nothing to migrate — the database is up to date.\n"
        : "Done. Applied {$ran} new migration(s).\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");

    if ($exception->getPrevious()) {
        fwrite(STDERR, $exception->getPrevious()->getMessage() . "\n");
    }

    exit(1);
}
