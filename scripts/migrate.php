<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $files = glob(ROOT_PATH . '/database/migrations/*.sql') ?: [];
    sort($files);

    foreach ($files as $file) {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException('Unable to read migration file: ' . basename($file));
        }

        db()->exec($sql);
        echo 'Migrated: ' . basename($file) . "\n";
    }
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");

    if ($exception->getPrevious()) {
        fwrite(STDERR, $exception->getPrevious()->getMessage() . "\n");
    }

    exit(1);
}
