<?php

declare(strict_types=1);

function event_admin_default(): array
{
    return [
        'id' => null,
        'title' => '',
        'meta_label' => '',
        'description' => '',
        'event_date' => '',
        'event_time_label' => '',
        'location' => '',
        'video_source_type' => 'link',
        'video_url' => '',
        'video_path' => '',
        'poster_image' => '',
        'registration_label' => '',
        'registration_url' => '',
        'status' => 'draft',
        'sort_order' => 0,
        'published_at' => '',
    ];
}

function event_admin_status_options(): array
{
    return [
        'draft' => 'Draft',
        'published' => 'Published',
    ];
}

function event_admin_source_options(): array
{
    return [
        'link' => 'Video link',
        'upload' => 'Uploaded video',
    ];
}

function event_admin_all(): array
{
    return db()->query(
        'SELECT id, title, meta_label, event_date, location, status, sort_order, published_at, updated_at
         FROM events
         ORDER BY sort_order ASC, COALESCE(event_date, "9999-12-31") ASC, id DESC'
    )->fetchAll();
}

function event_admin_counts(): array
{
    $counts = [
        'total' => 0,
        'published' => 0,
        'draft' => 0,
        'upcoming' => 0,
        'past' => 0,
    ];

    $rows = db()->query('SELECT status, event_date FROM events')->fetchAll();
    $today = (new DateTimeImmutable('today'))->format('Y-m-d');

    foreach ($rows as $row) {
        $status = (string) ($row['status'] ?? '');
        $counts['total']++;

        if (isset($counts[$status])) {
            $counts[$status]++;
        }

        $eventDate = (string) ($row['event_date'] ?? '');

        if ($eventDate !== '' && $eventDate < $today) {
            $counts['past']++;
        } else {
            $counts['upcoming']++;
        }
    }

    return $counts;
}

function event_admin_find(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $event = $statement->fetch();

    return is_array($event) ? event_admin_normalize($event) : null;
}

function event_admin_normalize(array $event): array
{
    return array_merge(event_admin_default(), [
        'id' => isset($event['id']) ? (int) $event['id'] : null,
        'title' => (string) ($event['title'] ?? ''),
        'meta_label' => (string) ($event['meta_label'] ?? ''),
        'description' => (string) ($event['description'] ?? ''),
        'event_date' => event_admin_date_value($event['event_date'] ?? ''),
        'event_time_label' => (string) ($event['event_time_label'] ?? ''),
        'location' => (string) ($event['location'] ?? ''),
        'video_source_type' => in_array((string) ($event['video_source_type'] ?? ''), ['link', 'upload'], true)
            ? (string) $event['video_source_type']
            : 'link',
        'video_url' => (string) ($event['video_url'] ?? ''),
        'video_path' => (string) ($event['video_path'] ?? ''),
        'poster_image' => (string) ($event['poster_image'] ?? ''),
        'registration_label' => (string) ($event['registration_label'] ?? ''),
        'registration_url' => (string) ($event['registration_url'] ?? ''),
        'status' => in_array((string) ($event['status'] ?? ''), array_keys(event_admin_status_options()), true)
            ? (string) $event['status']
            : 'draft',
        'sort_order' => (int) ($event['sort_order'] ?? 0),
        'published_at' => event_admin_datetime_value($event['published_at'] ?? ''),
    ]);
}

function event_admin_from_post(array $post, ?array $current = null): array
{
    $current = $current !== null ? event_admin_normalize($current) : event_admin_default();
    $sourceType = (string) ($post['video_source_type'] ?? 'link');
    $status = (string) ($post['status'] ?? 'draft');

    return [
        'id' => $current['id'],
        'title' => homepage_text($post['title'] ?? ''),
        'meta_label' => homepage_text($post['meta_label'] ?? ''),
        'description' => homepage_textarea($post['description'] ?? ''),
        'event_date' => event_admin_date_value($post['event_date'] ?? ''),
        'event_time_label' => homepage_text($post['event_time_label'] ?? ''),
        'location' => homepage_text($post['location'] ?? ''),
        'video_source_type' => in_array($sourceType, ['link', 'upload'], true) ? $sourceType : 'link',
        'video_url' => homepage_text($post['video_url'] ?? ''),
        'video_path' => homepage_text($post['video_path'] ?? ($current['video_path'] ?? '')),
        'poster_image' => homepage_text($post['poster_image'] ?? ($current['poster_image'] ?? '')),
        'registration_label' => homepage_text($post['registration_label'] ?? ''),
        'registration_url' => homepage_text($post['registration_url'] ?? ''),
        'status' => in_array($status, array_keys(event_admin_status_options()), true) ? $status : 'draft',
        'sort_order' => (int) ($post['sort_order'] ?? 0),
        'published_at' => event_admin_datetime_value($post['published_at'] ?? ''),
    ];
}

function event_admin_validate(array $event): array
{
    $errors = [];

    if ($event['title'] === '') {
        $errors[] = 'Add an event title.';
    }

    if ($event['event_date'] !== '' && ! event_admin_date_is_valid((string) $event['event_date'])) {
        $errors[] = 'Use a valid event date.';
    }

    if ($event['status'] === 'published' && $event['description'] === '') {
        $errors[] = 'Add a description before publishing.';
    }

    if ($event['video_source_type'] === 'link') {
        if ($event['video_url'] === '' && $event['status'] === 'published') {
            $errors[] = 'Add a video link before publishing.';
        } elseif ($event['video_url'] !== '' && ! event_admin_url_is_allowed((string) $event['video_url'])) {
            $errors[] = 'Use a valid http(s) video link.';
        }
    }

    if ($event['video_source_type'] === 'upload' && $event['video_path'] === '' && $event['status'] === 'published') {
        $errors[] = 'Upload a video before publishing, or switch the source to video link.';
    }

    if ($event['registration_url'] !== '' && ! event_admin_url_is_allowed((string) $event['registration_url'])) {
        $errors[] = 'Use a valid http(s) registration link.';
    }

    if ($event['published_at'] !== '' && ! event_admin_datetime_is_valid((string) $event['published_at'])) {
        $errors[] = 'Use a valid published date and time.';
    }

    return $errors;
}

function event_admin_save(array $event, int $adminId): int
{
    if ($event['published_at'] === '' && $event['status'] === 'published') {
        $event['published_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    $pdo = db();

    if (! empty($event['id'])) {
        $statement = $pdo->prepare(
            'UPDATE events
             SET title = :title,
                 meta_label = :meta_label,
                 description = :description,
                 event_date = :event_date,
                 event_time_label = :event_time_label,
                 location = :location,
                 video_source_type = :video_source_type,
                 video_url = :video_url,
                 video_path = :video_path,
                 poster_image = :poster_image,
                 registration_label = :registration_label,
                 registration_url = :registration_url,
                 status = :status,
                 sort_order = :sort_order,
                 published_at = :published_at,
                 updated_by = :updated_by
             WHERE id = :id'
        );
        $statement->execute(event_admin_statement_params($event, $adminId) + ['id' => (int) $event['id']]);

        return (int) $event['id'];
    }

    $statement = $pdo->prepare(
        'INSERT INTO events (
            title, meta_label, description, event_date, event_time_label, location,
            video_source_type, video_url, video_path, poster_image,
            registration_label, registration_url, status,
            sort_order, published_at, created_by, updated_by
        ) VALUES (
            :title, :meta_label, :description, :event_date, :event_time_label, :location,
            :video_source_type, :video_url, :video_path, :poster_image,
            :registration_label, :registration_url, :status,
            :sort_order, :published_at, :created_by, :updated_by
        )'
    );
    $statement->execute(event_admin_statement_params($event, $adminId) + ['created_by' => $adminId]);

    return (int) $pdo->lastInsertId();
}

function event_admin_delete(int $id): array
{
    if ($id <= 0) {
        return ['Invalid event selected.'];
    }

    $statement = db()->prepare('DELETE FROM events WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0 ? [] : ['That event no longer exists.'];
}

function event_admin_statement_params(array $event, int $adminId): array
{
    return [
        'title' => (string) $event['title'],
        'meta_label' => event_admin_nullable($event['meta_label'] ?? ''),
        'description' => event_admin_nullable($event['description'] ?? ''),
        'event_date' => event_admin_nullable($event['event_date'] ?? ''),
        'event_time_label' => event_admin_nullable($event['event_time_label'] ?? ''),
        'location' => event_admin_nullable($event['location'] ?? ''),
        'video_source_type' => (string) $event['video_source_type'],
        'video_url' => event_admin_nullable($event['video_url'] ?? ''),
        'video_path' => event_admin_nullable($event['video_path'] ?? ''),
        'poster_image' => event_admin_nullable($event['poster_image'] ?? ''),
        'registration_label' => event_admin_nullable($event['registration_label'] ?? ''),
        'registration_url' => event_admin_nullable($event['registration_url'] ?? ''),
        'status' => (string) $event['status'],
        'sort_order' => (int) $event['sort_order'],
        'published_at' => event_admin_nullable(event_admin_mysql_datetime((string) ($event['published_at'] ?? ''))),
        'updated_by' => $adminId,
    ];
}

function event_admin_nullable(mixed $value): ?string
{
    $value = is_string($value) ? trim($value) : (string) $value;

    return $value === '' ? null : $value;
}

function event_admin_url_is_allowed(string $url): bool
{
    return strlen($url) <= 500 && preg_match('#^https?://#i', $url) === 1;
}

function event_admin_date_value(mixed $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d');
    } catch (Throwable) {
        return $value;
    }
}

function event_admin_date_is_valid(string $value): bool
{
    try {
        new DateTimeImmutable($value);
        return true;
    } catch (Throwable) {
        return false;
    }
}

function event_admin_datetime_value(mixed $value): string
{
    $value = trim(str_replace('T', ' ', (string) $value));

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d\TH:i');
    } catch (Throwable) {
        return $value;
    }
}

function event_admin_mysql_datetime(string $value): string
{
    $value = trim(str_replace('T', ' ', $value));

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return $value;
    }
}

function event_admin_datetime_is_valid(string $value): bool
{
    try {
        new DateTimeImmutable(str_replace('T', ' ', $value));
        return true;
    } catch (Throwable) {
        return false;
    }
}

function event_public_archive(array $fallbackEvents): array
{
    $archive = [
        'upcoming' => [],
        'past' => [],
    ];

    try {
        $rows = db()->query(
            'SELECT *
             FROM events
             WHERE status = "published"
             ORDER BY sort_order ASC, COALESCE(published_at, created_at) DESC, id DESC'
        )->fetchAll();

        $items = array_map('event_public_from_row', $rows);
    } catch (Throwable) {
        $items = array_map(
            'event_public_from_legacy_item',
            array_values(array_filter($fallbackEvents['items'] ?? [], 'is_array'))
        );
    }

    $upcoming = array_values(array_filter($items, static fn (array $item): bool => $item['bucket'] === 'upcoming'));
    $past = array_values(array_filter($items, static fn (array $item): bool => $item['bucket'] === 'past'));

    usort($upcoming, static function (array $a, array $b): int {
        $orderCompare = $a['sort_order'] <=> $b['sort_order'];

        if ($orderCompare !== 0) {
            return $orderCompare;
        }

        return (string) $a['event_date'] <=> (string) $b['event_date'];
    });

    usort($past, static function (array $a, array $b): int {
        $orderCompare = $a['sort_order'] <=> $b['sort_order'];

        if ($orderCompare !== 0) {
            return $orderCompare;
        }

        return (string) $b['event_date'] <=> (string) $a['event_date'];
    });

    $archive['upcoming'] = $upcoming;
    $archive['past'] = $past;

    return $archive;
}

function event_public_from_row(array $row): array
{
    $event = event_admin_normalize($row);
    $video = $event['video_source_type'] === 'upload' ? $event['video_path'] : $event['video_url'];
    $dateLabel = event_public_date_label($event['event_date'], $event['event_time_label']);

    return [
        'id' => $event['id'],
        'title' => $event['title'],
        'meta' => $event['meta_label'] !== '' ? $event['meta_label'] : $dateLabel,
        'description' => $event['description'],
        'video' => $video,
        'poster' => $event['poster_image'],
        'date_label' => $dateLabel,
        'location' => $event['location'],
        'registration_label' => $event['registration_label'],
        'registration_url' => $event['registration_url'],
        'event_date' => $event['event_date'],
        'sort_order' => $event['sort_order'],
        'bucket' => event_public_bucket($event['event_date']),
    ];
}

function event_public_from_legacy_item(array $item): array
{
    $status = strtolower((string) ($item['status'] ?? ''));

    return [
        'id' => null,
        'title' => (string) ($item['title'] ?? ''),
        'meta' => (string) ($item['meta'] ?? ''),
        'description' => (string) ($item['description'] ?? ''),
        'video' => (string) ($item['video'] ?? ''),
        'poster' => (string) ($item['poster'] ?? ''),
        'date_label' => '',
        'location' => '',
        'registration_label' => '',
        'registration_url' => '',
        'event_date' => '',
        'sort_order' => 0,
        'bucket' => $status === 'upcoming' ? 'upcoming' : 'past',
    ];
}

function event_public_bucket(string $date): string
{
    if ($date === '') {
        return 'upcoming';
    }

    try {
        $eventDate = new DateTimeImmutable($date);
    } catch (Throwable) {
        return 'upcoming';
    }

    $today = new DateTimeImmutable('today');

    return $eventDate >= $today ? 'upcoming' : 'past';
}

function event_public_date_label(string $date, string $timeLabel): string
{
    $dateLabel = '';

    if ($date !== '') {
        try {
            $dateLabel = (new DateTimeImmutable($date))->format('j M Y');
        } catch (Throwable) {
            $dateLabel = '';
        }
    }

    $timeLabel = trim($timeLabel);

    if ($dateLabel !== '' && $timeLabel !== '') {
        return $dateLabel . ' · ' . $timeLabel;
    }

    return $dateLabel !== '' ? $dateLabel : $timeLabel;
}

function events_page_default_content(): array
{
    return [
        'kicker' => 'Upcoming · Past Events',
        'heading_line1' => 'Events',
        'heading_line2' => 'Calendar',
        'intro' => 'Talks, workshops, roundtables, and public engagements on AI governance, critical infrastructure technology, and responsible public-sector adoption.',
        'panel_eyebrow' => 'Event Library',
        'panel_title' => 'Video-led event cards',
        'panel_description' => 'Each container can use a YouTube/Vimeo link or an uploaded video file, with event text placed below the media.',
        'note' => 'Static placeholder event videos can be replaced with uploaded files or official YouTube/Vimeo links from the admin workflow later.',
    ];
}

function events_page_content(): array
{
    $default = events_page_default_content();

    try {
        $statement = db()->prepare('SELECT content_json FROM site_contents WHERE content_key = :key LIMIT 1');
        $statement->execute(['key' => 'events_page']);
        $row = $statement->fetch();

        if (! $row) {
            return $default;
        }

        $stored = json_decode((string) $row['content_json'], true, 512, JSON_THROW_ON_ERROR);

        return is_array($stored) ? array_merge($default, $stored) : $default;
    } catch (Throwable) {
        return $default;
    }
}

function events_page_content_from_post(array $post): array
{
    $default = events_page_default_content();

    return [
        'kicker' => homepage_text($post['kicker'] ?? $default['kicker']),
        'heading_line1' => homepage_text($post['heading_line1'] ?? $default['heading_line1']),
        'heading_line2' => homepage_text($post['heading_line2'] ?? $default['heading_line2']),
        'intro' => homepage_textarea($post['intro'] ?? $default['intro']),
        'panel_eyebrow' => homepage_text($post['panel_eyebrow'] ?? $default['panel_eyebrow']),
        'panel_title' => homepage_text($post['panel_title'] ?? $default['panel_title']),
        'panel_description' => homepage_textarea($post['panel_description'] ?? $default['panel_description']),
        'note' => homepage_textarea($post['note'] ?? $default['note']),
    ];
}

function events_page_save_content(array $content, ?int $adminId = null): void
{
    $json = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $statement = db()->prepare(
        'INSERT INTO site_contents (content_key, content_json, updated_by)
         VALUES (:content_key, :content_json, :updated_by)
         ON DUPLICATE KEY UPDATE
             content_json = VALUES(content_json),
             updated_by = VALUES(updated_by),
             updated_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([
        'content_key' => 'events_page',
        'content_json' => $json,
        'updated_by' => $adminId,
    ]);
}
