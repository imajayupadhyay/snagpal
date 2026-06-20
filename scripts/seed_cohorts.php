<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $force = in_array('--force', array_slice($argv, 1), true);
    $content = homepage_content();
    $cohorts = is_array($content['cohorts'] ?? null) ? $content['cohorts'] : [];
    $items = cohort_items($cohorts);

    if ($items === []) {
        echo "No static cohort items found to seed.\n";
        exit(0);
    }

    $adminId = (int) (db()->query('SELECT id FROM admin_users ORDER BY id ASC LIMIT 1')->fetchColumn() ?: 0);
    $seeded = 0;

    foreach ($items as $index => $item) {
        $slug = cohort_slug((string) ($item['slug'] ?? $item['title'] ?? ('cohort-' . ($index + 1))));
        $existing = cohort_admin_find_by_slug($slug);

        if ($existing !== null && ! $force) {
            echo 'Skipped existing cohort: ' . $slug . "\n";
            continue;
        }

        $video = homepage_text($item['video'] ?? '');
        $isVideoLink = preg_match('#^https?://#i', $video) === 1;
        $takeawaysJson = json_encode(cohort_seed_takeaways(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $meta = homepage_text($item['meta'] ?? ('Cohort ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)));
        $description = homepage_textarea($item['description'] ?? '');

        $cohort = [
            'id' => $existing['id'] ?? null,
            'title' => homepage_text($item['title'] ?? ''),
            'slug' => $slug,
            'meta_label' => $meta,
            'excerpt' => $description,
            'description' => $description,
            'content' => cohort_seed_article_content($item),
            'video_source_type' => $isVideoLink ? 'link' : 'upload',
            'video_url' => $isVideoLink ? $video : '',
            'video_path' => $isVideoLink ? '' : $video,
            'poster_image' => homepage_text($item['poster'] ?? ''),
            'resource_label' => 'Open cohort notes',
            'resource_url' => '',
            'takeaways_json' => is_string($takeawaysJson) ? $takeawaysJson : '[]',
            'status' => 'published',
            'is_featured' => $index === 0 ? 1 : 0,
            'sort_order' => $index + 1,
            'published_at' => (new DateTimeImmutable('today'))->modify('-' . $index . ' days')->format('Y-m-d\T09:00'),
        ];

        $errors = cohort_admin_validate($cohort, ! empty($cohort['id']) ? (int) $cohort['id'] : null);

        if ($errors !== []) {
            echo 'Skipped "' . ($cohort['title'] ?: $slug) . '": ' . implode(' ', $errors) . "\n";
            continue;
        }

        cohort_admin_save($cohort, $adminId);
        $seeded++;
    }

    echo "Seeded {$seeded} cohort(s).\n";

    if (! $force) {
        echo "Existing slugs are skipped. Re-run with --force only when you want to overwrite seeded cohort fields.\n";
    }
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");

    if ($exception->getPrevious()) {
        fwrite(STDERR, $exception->getPrevious()->getMessage() . "\n");
    }

    exit(1);
}
