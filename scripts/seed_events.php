<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $force = in_array('--force', array_slice($argv, 1), true);
    $default = homepage_default_content();
    $items = array_values(array_filter($default['events']['items'] ?? [], 'is_array'));

    if ($items === []) {
        echo "No static event items found to seed.\n";
        exit(0);
    }

    $adminId = (int) (db()->query('SELECT id FROM admin_users ORDER BY id ASC LIMIT 1')->fetchColumn() ?: 0);
    $existingCount = (int) db()->query('SELECT COUNT(*) FROM events')->fetchColumn();

    if ($existingCount > 0 && ! $force) {
        echo "Events table already has {$existingCount} row(s). Re-run with --force to seed anyway.\n";
        exit(0);
    }

    $seeded = 0;

    foreach ($items as $index => $item) {
        $video = homepage_text($item['video'] ?? '');
        $isVideoLink = preg_match('#^https?://#i', $video) === 1;
        $isUpcoming = strtolower((string) ($item['status'] ?? '')) === 'upcoming';

        $event = [
            'id' => null,
            'title' => homepage_text($item['title'] ?? ''),
            'meta_label' => homepage_text($item['meta'] ?? ''),
            'description' => homepage_textarea($item['description'] ?? ''),
            'event_date' => $isUpcoming
                ? (new DateTimeImmutable('today'))->modify('+' . (($index + 1) * 14) . ' days')->format('Y-m-d')
                : (new DateTimeImmutable('today'))->modify('-' . (($index + 1) * 30) . ' days')->format('Y-m-d'),
            'event_time_label' => '',
            'location' => '',
            'video_source_type' => $isVideoLink ? 'link' : 'upload',
            'video_url' => $isVideoLink ? $video : '',
            'video_path' => $isVideoLink ? '' : $video,
            'poster_image' => homepage_text($item['poster'] ?? ''),
            'registration_label' => '',
            'registration_url' => '',
            'status' => 'published',
            'sort_order' => $index + 1,
            'published_at' => (new DateTimeImmutable('today'))->modify('-' . $index . ' days')->format('Y-m-d\T09:00'),
        ];

        $errors = event_admin_validate($event);

        if ($errors !== []) {
            echo 'Skipped "' . ($event['title'] ?: 'event') . '": ' . implode(' ', $errors) . "\n";
            continue;
        }

        event_admin_save($event, $adminId);
        $seeded++;
    }

    echo "Seeded {$seeded} event(s).\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");

    if ($exception->getPrevious()) {
        fwrite(STDERR, $exception->getPrevious()->getMessage() . "\n");
    }

    exit(1);
}
