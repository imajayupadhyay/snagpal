<?php

declare(strict_types=1);

function cohort_admin_default(): array
{
    return [
        'id' => null,
        'title' => '',
        'slug' => '',
        'category_id' => null,
        'meta_label' => '',
        'excerpt' => '',
        'description' => '',
        'content' => '',
        'video_source_type' => 'link',
        'video_url' => '',
        'video_path' => '',
        'poster_image' => '',
        'resource_label' => '',
        'resource_url' => '',
        'takeaways_json' => '[]',
        'status' => 'draft',
        'is_featured' => 0,
        'sort_order' => 0,
        'published_at' => '',
        'created_at' => '',
        'updated_at' => '',
    ];
}

function cohort_admin_status_options(): array
{
    return [
        'draft' => 'Draft',
        'published' => 'Published',
    ];
}

function cohort_admin_source_options(): array
{
    return [
        'link' => 'Video link',
        'upload' => 'Uploaded video',
    ];
}

function cohort_admin_all(): array
{
    return db()->query(
        'SELECT cohorts.id, cohorts.title, cohorts.slug, cohorts.meta_label, cohorts.excerpt, cohorts.status,
                cohorts.is_featured, cohorts.sort_order, cohorts.published_at, cohorts.updated_at,
                cohorts.category_id, cohort_categories.name AS category_name
         FROM cohorts
         LEFT JOIN cohort_categories ON cohort_categories.id = cohorts.category_id
         ORDER BY cohorts.is_featured DESC, cohorts.sort_order ASC, COALESCE(cohorts.published_at, cohorts.created_at) DESC, cohorts.id DESC'
    )->fetchAll();
}

function cohort_admin_counts(): array
{
    $counts = [
        'total' => 0,
        'published' => 0,
        'draft' => 0,
        'featured' => 0,
    ];

    $rows = db()->query(
        'SELECT status, COUNT(*) AS total, SUM(is_featured = 1) AS featured_count
         FROM cohorts
         GROUP BY status'
    )->fetchAll();

    foreach ($rows as $row) {
        $status = (string) ($row['status'] ?? '');
        $total = (int) ($row['total'] ?? 0);
        $counts['total'] += $total;

        if (isset($counts[$status])) {
            $counts[$status] = $total;
        }

        $counts['featured'] += (int) ($row['featured_count'] ?? 0);
    }

    return $counts;
}

function cohort_admin_find(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM cohorts WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $cohort = $statement->fetch();

    return is_array($cohort) ? cohort_admin_normalize($cohort) : null;
}

function cohort_admin_find_by_slug(string $slug): ?array
{
    $slug = cohort_slug($slug);

    if ($slug === '') {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM cohorts WHERE slug = :slug LIMIT 1');
    $statement->execute(['slug' => $slug]);
    $cohort = $statement->fetch();

    return is_array($cohort) ? cohort_admin_normalize($cohort) : null;
}

function cohort_admin_normalize(array $cohort): array
{
    return array_merge(cohort_admin_default(), [
        'id' => isset($cohort['id']) ? (int) $cohort['id'] : null,
        'title' => (string) ($cohort['title'] ?? ''),
        'slug' => (string) ($cohort['slug'] ?? ''),
        'category_id' => ! empty($cohort['category_id']) ? (int) $cohort['category_id'] : null,
        'meta_label' => (string) ($cohort['meta_label'] ?? ''),
        'excerpt' => (string) ($cohort['excerpt'] ?? ''),
        'description' => (string) ($cohort['description'] ?? ''),
        'content' => (string) ($cohort['content'] ?? ''),
        'video_source_type' => in_array((string) ($cohort['video_source_type'] ?? ''), ['link', 'upload'], true)
            ? (string) $cohort['video_source_type']
            : 'link',
        'video_url' => (string) ($cohort['video_url'] ?? ''),
        'video_path' => (string) ($cohort['video_path'] ?? ''),
        'poster_image' => (string) ($cohort['poster_image'] ?? ''),
        'resource_label' => (string) ($cohort['resource_label'] ?? ''),
        'resource_url' => (string) ($cohort['resource_url'] ?? ''),
        'takeaways_json' => (string) ($cohort['takeaways_json'] ?? '[]'),
        'status' => in_array((string) ($cohort['status'] ?? ''), array_keys(cohort_admin_status_options()), true)
            ? (string) $cohort['status']
            : 'draft',
        'is_featured' => ! empty($cohort['is_featured']) ? 1 : 0,
        'sort_order' => (int) ($cohort['sort_order'] ?? 0),
        'published_at' => cohort_admin_datetime_value($cohort['published_at'] ?? ''),
        'created_at' => cohort_admin_datetime_value($cohort['created_at'] ?? ''),
        'updated_at' => cohort_admin_datetime_value($cohort['updated_at'] ?? ''),
    ]);
}

function cohort_admin_from_post(array $post, ?array $current = null): array
{
    $current = $current !== null ? cohort_admin_normalize($current) : cohort_admin_default();
    $title = homepage_text($post['title'] ?? '');
    $slug = homepage_text($post['slug'] ?? '');
    $sourceType = (string) ($post['video_source_type'] ?? 'link');
    $status = (string) ($post['status'] ?? 'draft');
    $takeawaysJson = json_encode(cohort_admin_lines($post['takeaways_text'] ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($slug === '' && $title !== '') {
        $slug = cohort_slug($title);
    }

    $categoryId = (int) ($post['category_id'] ?? 0);

    return [
        'id' => $current['id'],
        'title' => $title,
        'slug' => cohort_slug($slug),
        'category_id' => $categoryId > 0 ? $categoryId : null,
        'meta_label' => homepage_text($post['meta_label'] ?? ''),
        'excerpt' => homepage_textarea($post['excerpt'] ?? ''),
        'description' => homepage_textarea($post['description'] ?? ''),
        'content' => cohort_rich_content($post['content'] ?? ''),
        'video_source_type' => in_array($sourceType, ['link', 'upload'], true) ? $sourceType : 'link',
        'video_url' => homepage_text($post['video_url'] ?? ''),
        'video_path' => homepage_text($post['video_path'] ?? ($current['video_path'] ?? '')),
        'poster_image' => homepage_text($post['poster_image'] ?? ($current['poster_image'] ?? '')),
        'resource_label' => homepage_text($post['resource_label'] ?? ''),
        'resource_url' => homepage_text($post['resource_url'] ?? ''),
        'takeaways_json' => is_string($takeawaysJson) ? $takeawaysJson : '[]',
        'status' => in_array($status, array_keys(cohort_admin_status_options()), true) ? $status : 'draft',
        'is_featured' => ! empty($post['is_featured']) ? 1 : 0,
        'sort_order' => (int) ($post['sort_order'] ?? 0),
        'published_at' => cohort_admin_datetime_value($post['published_at'] ?? ''),
    ];
}

function cohort_admin_validate(array $cohort, ?int $ignoreId = null): array
{
    $errors = [];

    if ($cohort['title'] === '') {
        $errors[] = 'Add a cohort title.';
    }

    if ($cohort['slug'] === '') {
        $errors[] = 'Add a URL slug.';
    } elseif (cohort_admin_slug_exists((string) $cohort['slug'], $ignoreId)) {
        $errors[] = 'That URL slug is already used by another cohort.';
    }

    if ($cohort['status'] === 'published') {
        if ($cohort['excerpt'] === '') {
            $errors[] = 'Add an excerpt before publishing.';
        }

        if ($cohort['description'] === '') {
            $errors[] = 'Add a card description before publishing.';
        }

        if (cohort_rich_content_text((string) $cohort['content']) === '') {
            $errors[] = 'Add the article content before publishing.';
        }
    }

    if ($cohort['video_source_type'] === 'link') {
        if ($cohort['video_url'] === '' && $cohort['status'] === 'published') {
            $errors[] = 'Add a video link before publishing.';
        } elseif ($cohort['video_url'] !== '' && ! cohort_admin_url_is_allowed((string) $cohort['video_url'])) {
            $errors[] = 'Use a valid http(s) video link.';
        }
    }

    if ($cohort['video_source_type'] === 'upload' && $cohort['video_path'] === '' && $cohort['status'] === 'published') {
        $errors[] = 'Upload a video before publishing, or switch the source to video link.';
    }

    if ($cohort['resource_url'] !== '' && ! cohort_admin_url_is_allowed((string) $cohort['resource_url'])) {
        $errors[] = 'Use a valid http(s) related link.';
    }

    if ($cohort['published_at'] !== '' && ! cohort_admin_datetime_is_valid((string) $cohort['published_at'])) {
        $errors[] = 'Use a valid published date and time.';
    }

    return $errors;
}

function cohort_admin_slug_exists(string $slug, ?int $ignoreId = null): bool
{
    $sql = 'SELECT id FROM cohorts WHERE slug = :slug';
    $params = ['slug' => $slug];

    if ($ignoreId !== null && $ignoreId > 0) {
        $sql .= ' AND id <> :id';
        $params['id'] = $ignoreId;
    }

    $sql .= ' LIMIT 1';
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return (bool) $statement->fetchColumn();
}

function cohort_admin_save(array $cohort, int $adminId): int
{
    if ($cohort['published_at'] === '' && $cohort['status'] === 'published') {
        $cohort['published_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        if ((int) $cohort['is_featured'] === 1) {
            $pdo->exec('UPDATE cohorts SET is_featured = 0');
        }

        if (! empty($cohort['id'])) {
            $statement = $pdo->prepare(
                'UPDATE cohorts
                 SET title = :title,
                     slug = :slug,
                     category_id = :category_id,
                     meta_label = :meta_label,
                     excerpt = :excerpt,
                     description = :description,
                     content = :content,
                     video_source_type = :video_source_type,
                     video_url = :video_url,
                     video_path = :video_path,
                     poster_image = :poster_image,
                     resource_label = :resource_label,
                     resource_url = :resource_url,
                     takeaways_json = :takeaways_json,
                     status = :status,
                     is_featured = :is_featured,
                     sort_order = :sort_order,
                     published_at = :published_at,
                     updated_by = :updated_by
                 WHERE id = :id'
            );
            $statement->execute(cohort_admin_statement_params($cohort, $adminId) + ['id' => (int) $cohort['id']]);
            $savedId = (int) $cohort['id'];
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO cohorts (
                    title, slug, category_id, meta_label, excerpt, description, content,
                    video_source_type, video_url, video_path, poster_image,
                    resource_label, resource_url, takeaways_json, status, is_featured,
                    sort_order, published_at, created_by, updated_by
                ) VALUES (
                    :title, :slug, :category_id, :meta_label, :excerpt, :description, :content,
                    :video_source_type, :video_url, :video_path, :poster_image,
                    :resource_label, :resource_url, :takeaways_json, :status, :is_featured,
                    :sort_order, :published_at, :created_by, :updated_by
                )'
            );
            $statement->execute(cohort_admin_statement_params($cohort, $adminId) + ['created_by' => $adminId]);
            $savedId = (int) $pdo->lastInsertId();
        }

        $pdo->commit();

        return $savedId;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function cohort_admin_delete(int $id): array
{
    if ($id <= 0) {
        return ['Invalid cohort selected.'];
    }

    $statement = db()->prepare('DELETE FROM cohorts WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0 ? [] : ['That cohort no longer exists.'];
}

function cohort_admin_statement_params(array $cohort, int $adminId): array
{
    return [
        'title' => (string) $cohort['title'],
        'slug' => (string) $cohort['slug'],
        'category_id' => ! empty($cohort['category_id']) ? (int) $cohort['category_id'] : null,
        'meta_label' => cohort_admin_nullable($cohort['meta_label'] ?? ''),
        'excerpt' => cohort_admin_nullable($cohort['excerpt'] ?? ''),
        'description' => cohort_admin_nullable($cohort['description'] ?? ''),
        'content' => cohort_admin_nullable($cohort['content'] ?? ''),
        'video_source_type' => (string) $cohort['video_source_type'],
        'video_url' => cohort_admin_nullable($cohort['video_url'] ?? ''),
        'video_path' => cohort_admin_nullable($cohort['video_path'] ?? ''),
        'poster_image' => cohort_admin_nullable($cohort['poster_image'] ?? ''),
        'resource_label' => cohort_admin_nullable($cohort['resource_label'] ?? ''),
        'resource_url' => cohort_admin_nullable($cohort['resource_url'] ?? ''),
        'takeaways_json' => (string) ($cohort['takeaways_json'] ?? '[]'),
        'status' => (string) $cohort['status'],
        'is_featured' => (int) $cohort['is_featured'],
        'sort_order' => (int) $cohort['sort_order'],
        'published_at' => cohort_admin_nullable(cohort_admin_mysql_datetime((string) ($cohort['published_at'] ?? ''))),
        'updated_by' => $adminId,
    ];
}

function cohort_admin_nullable(mixed $value): ?string
{
    $value = is_string($value) ? trim($value) : (string) $value;

    return $value === '' ? null : $value;
}

function cohort_admin_lines(mixed $value): array
{
    $lines = preg_split('/\R+/', homepage_textarea($value)) ?: [];

    return array_values(array_filter(array_map('homepage_text', $lines), static fn (string $line): bool => $line !== ''));
}

function cohort_admin_takeaways_text(array $cohort): string
{
    $decoded = json_decode((string) ($cohort['takeaways_json'] ?? '[]'), true);

    if (! is_array($decoded)) {
        return '';
    }

    return implode("\n", array_values(array_filter(array_map('homepage_text', $decoded))));
}

function cohort_admin_url_is_allowed(string $url): bool
{
    return strlen($url) <= 500 && preg_match('#^https?://#i', $url) === 1;
}

function cohort_admin_datetime_value(mixed $value): string
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

function cohort_admin_mysql_datetime(string $value): string
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

function cohort_admin_datetime_is_valid(string $value): bool
{
    try {
        new DateTimeImmutable(str_replace('T', ' ', $value));
        return true;
    } catch (Throwable) {
        return false;
    }
}

function cohort_public_date_label(mixed $value): string
{
    $value = trim(str_replace('T', ' ', (string) $value));

    if ($value === '') {
        return '';
    }

    try {
        return (new DateTimeImmutable($value))->format('F j, Y');
    } catch (Throwable) {
        return '';
    }
}

function cohort_public_archive(array $fallbackCohorts): array
{
    $archive = [
        'heading' => (string) ($fallbackCohorts['heading'] ?? 'Cohorts'),
        'intro' => (string) ($fallbackCohorts['intro'] ?? ''),
        'items' => [],
        'note' => (string) ($fallbackCohorts['note'] ?? ''),
    ];

    try {
        $rows = db()->query(
            'SELECT *
             FROM cohorts
             WHERE status = "published"
             ORDER BY is_featured DESC, sort_order ASC, COALESCE(published_at, created_at) DESC, id DESC'
        )->fetchAll();

        $archive['items'] = array_map('cohort_public_from_row', $rows);

        return $archive;
    } catch (Throwable) {
        return $fallbackCohorts;
    }
}

function cohort_public_from_row(array $row): array
{
    $cohort = cohort_admin_normalize($row);
    $description = $cohort['description'] !== '' ? $cohort['description'] : $cohort['excerpt'];
    $video = $cohort['video_source_type'] === 'upload' ? $cohort['video_path'] : $cohort['video_url'];
    $category = $cohort['category_id'] !== null ? (cohort_categories_lookup()[$cohort['category_id']] ?? null) : null;

    return [
        'id' => $cohort['id'],
        'title' => $cohort['title'],
        'slug' => $cohort['slug'],
        'category_slug' => $category['slug'] ?? '',
        'category_name' => $category['name'] ?? '',
        'meta' => $cohort['meta_label'] !== '' ? $cohort['meta_label'] : 'Cohort',
        'excerpt' => $cohort['excerpt'],
        'description' => $description,
        'content' => $cohort['content'],
        'video' => $video,
        'poster' => $cohort['poster_image'],
        'resource_label' => $cohort['resource_label'],
        'resource_url' => $cohort['resource_url'],
        'takeaways' => cohort_public_takeaways($cohort),
        'published_at' => $cohort['published_at'],
        'created_at' => $cohort['created_at'],
        'updated_at' => $cohort['updated_at'],
        'is_featured' => (int) $cohort['is_featured'],
    ];
}

function cohort_public_find_by_slug(array $items, string $slug): ?array
{
    $slug = cohort_slug($slug);

    foreach ($items as $item) {
        if (($item['slug'] ?? '') === $slug) {
            return $item;
        }
    }

    return null;
}

function cohort_public_takeaways(array $cohort): array
{
    if (isset($cohort['takeaways']) && is_array($cohort['takeaways'])) {
        return array_values(array_filter(array_map('homepage_text', $cohort['takeaways'])));
    }

    $decoded = json_decode((string) ($cohort['takeaways_json'] ?? '[]'), true);

    if (! is_array($decoded)) {
        return [];
    }

    return array_values(array_filter(array_map('homepage_text', $decoded), static fn (string $line): bool => $line !== ''));
}

function cohort_public_content_paragraphs(mixed $content): array
{
    $parts = preg_split('/\R\s*\R/', homepage_textarea($content)) ?: [];

    return array_values(array_filter(array_map('homepage_textarea', $parts), static fn (string $paragraph): bool => $paragraph !== ''));
}

function cohort_public_content_html(mixed $content, mixed $fallback = ''): string
{
    $html = cohort_rich_content($content);

    if (cohort_rich_content_text($html) === '') {
        $html = cohort_rich_content($fallback);
    }

    return $html;
}

function cohort_rich_content_for_editor(mixed $content): string
{
    $html = cohort_rich_content($content);

    return $html !== '' ? $html : '<p><br></p>';
}

function cohort_rich_content(mixed $content): string
{
    $content = trim((string) $content);

    if ($content === '') {
        return '';
    }

    if ($content === strip_tags($content)) {
        return cohort_plain_text_to_html($content);
    }

    $content = preg_replace('/<(script|style|iframe|object|embed)\b[^>]*>.*?<\/\1>/is', '', $content) ?? '';
    $allowedTags = '<p><h2><h3><h4><strong><b><em><i><ul><ol><li><blockquote><a><br>';
    $clean = strip_tags($content, $allowedTags);
    $clean = preg_replace_callback('/<([a-z][a-z0-9]*)(\s[^>]*)?>/i', static function (array $matches): string {
        $tag = strtolower($matches[1]);

        if ($tag === 'b') {
            return '<strong>';
        }

        if ($tag === 'i') {
            return '<em>';
        }

        if ($tag === 'br') {
            return '<br>';
        }

        if ($tag !== 'a') {
            return '<' . $tag . '>';
        }

        $attributes = (string) ($matches[2] ?? '');
        $href = '';

        if (preg_match('/\shref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attributes, $hrefMatch) === 1) {
            $href = html_entity_decode((string) ($hrefMatch[1] ?? $hrefMatch[2] ?? $hrefMatch[3] ?? ''), ENT_QUOTES, 'UTF-8');
        }

        if ($href === '' || ! cohort_content_href_is_allowed($href)) {
            return '<a>';
        }

        $externalAttrs = preg_match('#^https?://#i', $href) === 1 ? ' target="_blank" rel="noopener"' : '';

        return '<a href="' . e($href) . '"' . $externalAttrs . '>';
    }, $clean) ?? '';
    $clean = str_replace(['</b>', '</i>'], ['</strong>', '</em>'], $clean);
    $clean = preg_replace('/<p>\s*<\/p>/i', '', $clean) ?? $clean;

    return trim($clean);
}

function cohort_plain_text_to_html(string $content): string
{
    $parts = preg_split('/\R\s*\R/', homepage_textarea($content)) ?: [];
    $paragraphs = [];

    foreach ($parts as $part) {
        $part = trim($part);

        if ($part !== '') {
            $paragraphs[] = '<p>' . nl2br(e($part)) . '</p>';
        }
    }

    return implode("\n", $paragraphs);
}

function cohort_rich_content_text(string $content): string
{
    return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8')) ?? '');
}

function cohort_content_href_is_allowed(string $href): bool
{
    $href = trim($href);

    return strlen($href) <= 500
        && (preg_match('#^https?://#i', $href) === 1
            || preg_match('#^mailto:#i', $href) === 1
            || preg_match('#^tel:#i', $href) === 1
            || str_starts_with($href, '/')
            || str_starts_with($href, '#'));
}

function cohort_seed_article_content(array $item): string
{
    $title = homepage_text($item['title'] ?? 'Cohort');
    $description = homepage_textarea($item['description'] ?? '');

    return trim(implode("\n\n", array_filter([
        $description,
        'This cohort is framed for public institutions that need to move from technology discussion to implementation discipline. The session connects policy language with the practical work of ownership, procurement, data stewardship, security, and accountability.',
        'The emphasis is not only on what technology can do, but on the conditions under which public systems should adopt it. That means treating risk, documentation, review, and public trust as part of the operating model from the beginning.',
        'Use this entry to publish the recording, notes, links, and takeaways for "' . $title . '".',
    ], static fn (string $part): bool => $part !== '')));
}

function cohort_seed_takeaways(): array
{
    return [
        'Policy guidance needs an operational model before it can become repeatable institutional practice.',
        'Public-sector AI adoption depends on risk ownership, auditability, and clear decision rights.',
        'Capability-building matters as much as procurement when technology affects public trust.',
    ];
}

function cohorts_page_default_content(): array
{
    return [
        'kicker' => 'Cohorts · Video Notes',
        'heading_line1' => 'Cohort',
        'heading_line2' => 'Library',
        'browse_cta_label' => 'Browse Cohorts',
        'stat2_value' => 'Video',
        'stat2_label' => 'Recording-led learning format',
        'stat3_value' => 'AI',
        'stat3_label' => 'Governance and public technology',
        'stat4_value' => 'Admin',
        'stat4_label' => 'Database-backed publishing workflow',
    ];
}

function cohorts_page_content(): array
{
    $default = cohorts_page_default_content();

    try {
        $statement = db()->prepare('SELECT content_json FROM site_contents WHERE content_key = :key LIMIT 1');
        $statement->execute(['key' => 'cohorts_page']);
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

function cohorts_page_content_from_post(array $post): array
{
    $default = cohorts_page_default_content();

    return [
        'kicker' => homepage_text($post['kicker'] ?? $default['kicker']),
        'heading_line1' => homepage_text($post['heading_line1'] ?? $default['heading_line1']),
        'heading_line2' => homepage_text($post['heading_line2'] ?? $default['heading_line2']),
        'browse_cta_label' => homepage_text($post['browse_cta_label'] ?? $default['browse_cta_label']),
        'stat2_value' => homepage_text($post['stat2_value'] ?? $default['stat2_value']),
        'stat2_label' => homepage_text($post['stat2_label'] ?? $default['stat2_label']),
        'stat3_value' => homepage_text($post['stat3_value'] ?? $default['stat3_value']),
        'stat3_label' => homepage_text($post['stat3_label'] ?? $default['stat3_label']),
        'stat4_value' => homepage_text($post['stat4_value'] ?? $default['stat4_value']),
        'stat4_label' => homepage_text($post['stat4_label'] ?? $default['stat4_label']),
    ];
}

function cohorts_page_save_content(array $content, ?int $adminId = null): void
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
        'content_key' => 'cohorts_page',
        'content_json' => $json,
        'updated_by' => $adminId,
    ]);
}
