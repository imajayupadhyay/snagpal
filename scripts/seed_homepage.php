<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$force = in_array('--force', $argv, true);

try {
    $seeded = homepage_seed_from_static($force);
    echo $seeded
        ? "Seeded homepage content from app/data/site.php.\n"
        : "Homepage content already exists. Use --force to overwrite it.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");

    if ($exception->getPrevious()) {
        fwrite(STDERR, $exception->getPrevious()->getMessage() . "\n");
    }

    exit(1);
}
