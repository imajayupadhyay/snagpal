<?php

declare(strict_types=1);

function homepage_default_content(): array
{
    static $content = null;

    if ($content === null) {
        $content = require APP_PATH . '/data/site.php';
    }

    return $content;
}

function homepage_content(): array
{
    $default = homepage_default_content();

    try {
        $statement = db()->prepare('SELECT content_json FROM site_contents WHERE content_key = :key LIMIT 1');
        $statement->execute(['key' => 'homepage']);
        $row = $statement->fetch();

        if (! $row) {
            return $default;
        }

        $stored = json_decode((string) $row['content_json'], true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($stored)) {
            return $default;
        }

        return homepage_merge_content($default, $stored);
    } catch (Throwable) {
        return $default;
    }
}

function homepage_content_exists(): bool
{
    try {
        $statement = db()->prepare('SELECT 1 FROM site_contents WHERE content_key = :key LIMIT 1');
        $statement->execute(['key' => 'homepage']);

        return (bool) $statement->fetchColumn();
    } catch (Throwable) {
        return false;
    }
}

function homepage_save_content(array $content, ?int $adminId = null): void
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
        'content_key' => 'homepage',
        'content_json' => $json,
        'updated_by' => $adminId,
    ]);
}

function homepage_seed_from_static(bool $force = false): bool
{
    if (! $force && homepage_content_exists()) {
        return false;
    }

    homepage_save_content(homepage_default_content());

    return true;
}

function homepage_merge_content(array $default, array $stored): array
{
    foreach ($stored as $key => $value) {
        if (is_array($value) && isset($default[$key]) && is_array($default[$key]) && ! array_is_list($value)) {
            $default[$key] = homepage_merge_content($default[$key], $value);
            continue;
        }

        $default[$key] = $value;
    }

    return $default;
}

function homepage_content_from_post(array $post, array $current): array
{
    return [
        'page' => [
            'lang' => homepage_text($post['page']['lang'] ?? 'en'),
            'title' => homepage_text($post['page']['title'] ?? ''),
            'description' => homepage_textarea($post['page']['description'] ?? ''),
            'theme_color' => homepage_text($post['page']['theme_color'] ?? '#0C5E55'),
        ],
        'identity' => [
            'first_name' => homepage_text($post['identity']['first_name'] ?? ''),
            'last_name' => homepage_text($post['identity']['last_name'] ?? ''),
            'full_name' => homepage_text($post['identity']['full_name'] ?? ''),
            'tagline' => homepage_text($post['identity']['tagline'] ?? ''),
            'footer_tagline' => homepage_text($post['identity']['footer_tagline'] ?? ''),
            'location' => homepage_text($post['identity']['location'] ?? ''),
            'email' => homepage_text($post['identity']['email'] ?? ''),
            'linkedin' => [
                'label' => homepage_text($post['identity']['linkedin']['label'] ?? ''),
                'url' => homepage_text($post['identity']['linkedin']['url'] ?? ''),
            ],
        ],
        'navigation' => homepage_rows_from_columns($post['navigation'] ?? [], ['label', 'href', 'class'], ['label', 'href']),
        'hero' => [
            'kicker' => homepage_text($post['hero']['kicker'] ?? ''),
            'kicker_suffix' => homepage_text($post['hero']['kicker_suffix'] ?? ''),
            'role' => homepage_textarea($post['hero']['role'] ?? ''),
            'lede' => homepage_textarea($post['hero']['lede'] ?? ''),
            'image' => [
                'src' => homepage_text($post['hero']['image']['src'] ?? ($current['hero']['image']['src'] ?? '')),
                'alt' => homepage_text($post['hero']['image']['alt'] ?? ''),
            ],
            'stats' => homepage_rows_from_columns($post['hero_stats'] ?? [], ['value', 'label'], ['value', 'label']),
        ],
        'topics' => homepage_lines($post['topics_text'] ?? ''),
        'recommendations' => homepage_rows_from_columns($post['recommendations'] ?? [], ['q', 'w'], ['q', 'w']),
        'recommendations_note' => homepage_text($post['recommendations_note'] ?? ''),
        'profile' => [
            'heading' => homepage_text($post['profile']['heading'] ?? ''),
            'image' => [
                'src' => homepage_text($post['profile']['image']['src'] ?? ($current['profile']['image']['src'] ?? '')),
                'alt' => homepage_text($post['profile']['image']['alt'] ?? ''),
            ],
            'lead_html' => homepage_rich_text($post['profile']['lead_html'] ?? ''),
            'paragraphs_html' => homepage_paragraphs($post['profile_paragraphs_text'] ?? ''),
        ],
        'expertise' => [
            'heading' => homepage_text($post['expertise']['heading'] ?? ''),
            'items' => homepage_rows_from_columns($post['expertise_items'] ?? [], ['number', 'title', 'description'], ['title', 'description']),
        ],
        'quotes' => homepage_lines($post['quotes_text'] ?? ''),
        'quote_author' => homepage_text($post['quote_author'] ?? ''),
        'focus' => [
            'heading' => homepage_text($post['focus']['heading'] ?? ''),
            'items' => homepage_rows_from_columns($post['focus_items'] ?? [], ['title', 'description'], ['title', 'description']),
        ],
        'research' => [
            'heading' => homepage_text($post['research']['heading'] ?? ''),
            'media' => homepage_rows_from_columns($post['research_media'] ?? [], ['src', 'alt', 'caption'], ['src']),
            'items' => homepage_rows_from_columns($post['research_items'] ?? [], ['year', 'title', 'description', 'kind'], ['title', 'description']),
            'note' => homepage_text($post['research']['note'] ?? ''),
        ],
        'cohorts' => [
            'heading' => homepage_text($post['cohorts']['heading'] ?? ''),
            'intro' => homepage_textarea($post['cohorts']['intro'] ?? ''),
            'items' => homepage_rows_from_columns($post['cohorts_items'] ?? [], ['title', 'meta', 'description', 'video', 'poster'], ['title']),
            'note' => homepage_text($post['cohorts']['note'] ?? ''),
        ],
        'schedule' => [
            'eyebrow' => homepage_text($post['schedule']['eyebrow'] ?? ''),
            'heading' => homepage_text($post['schedule']['heading'] ?? ''),
            'description' => homepage_textarea($post['schedule']['description'] ?? ''),
            'email_subject' => homepage_text($post['schedule']['email_subject'] ?? ''),
        ],
    ];
}

function homepage_rows_from_columns(array $columns, array $fields, array $requiredFields): array
{
    $max = 0;

    foreach ($fields as $field) {
        $values = $columns[$field] ?? [];

        if (is_array($values)) {
            $max = max($max, count($values));
        }
    }

    $rows = [];

    for ($i = 0; $i < $max; $i++) {
        $row = [];
        $hasRequiredValue = false;

        foreach ($fields as $field) {
            $value = homepage_textarea($columns[$field][$i] ?? '');
            $row[$field] = $value;

            if (in_array($field, $requiredFields, true) && $value !== '') {
                $hasRequiredValue = true;
            }
        }

        if ($hasRequiredValue) {
            $rows[] = $row;
        }
    }

    return $rows;
}

function homepage_text(mixed $value): string
{
    return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
}

function homepage_textarea(mixed $value): string
{
    return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
}

function homepage_rich_text(mixed $value): string
{
    return trim(strip_tags((string) $value, '<b><strong><em><i><br>'));
}

function homepage_lines(mixed $value): array
{
    $lines = preg_split('/\R+/', homepage_textarea($value)) ?: [];

    return array_values(array_filter(array_map('homepage_text', $lines), static fn (string $line): bool => $line !== ''));
}

function homepage_paragraphs(mixed $value): array
{
    $parts = preg_split('/\R\s*\R/', homepage_textarea($value)) ?: [];

    return array_values(array_filter(array_map('homepage_rich_text', $parts), static fn (string $paragraph): bool => $paragraph !== ''));
}
