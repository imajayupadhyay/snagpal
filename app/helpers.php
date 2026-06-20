<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url_path(string $path = ''): string
{
    $config = $GLOBALS['config'] ?? [];
    $basePath = trim((string) ($config['base_path'] ?? ''), '/');
    $path = ltrim($path, '/');

    $segments = array_filter([$basePath, $path], static fn (string $segment): bool => $segment !== '');

    return '/' . implode('/', $segments);
}

function asset(string $path): string
{
    $config = $GLOBALS['config'] ?? [];
    $version = (string) ($config['asset_version'] ?? '');
    $path = ltrim($path, '/');

    if (preg_match('#^https?://#', $path) === 1) {
        return $path;
    }

    $url = str_starts_with($path, 'uploads/')
        ? url_path($path)
        : url_path('assets/' . $path);

    return $version !== '' ? $url . '?v=' . rawurlencode($version) : $url;
}

function render(string $view, array $data = []): void
{
    $path = APP_PATH . '/views/' . $view . '.php';

    if (! is_file($path)) {
        throw new RuntimeException(sprintf('View not found: %s', $view));
    }

    extract($data, EXTR_SKIP);
    require $path;
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}

function flash(string $key, ?string $value = null): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return is_string($message) ? $message : null;
}

function external_link_attrs(array $item): string
{
    if (empty($item['external'])) {
        return '';
    }

    return ' target="_blank" rel="noopener"';
}

function cohort_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'cohort';
}

function cohort_items(array $cohorts): array
{
    $items = array_values(array_filter($cohorts['items'] ?? [], 'is_array'));
    $seen = [];

    foreach ($items as $index => $item) {
        $baseSlug = cohort_slug((string) ($item['slug'] ?? $item['title'] ?? ('cohort-' . ($index + 1))));
        $slug = $baseSlug;
        $suffix = 2;

        while (isset($seen[$slug])) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $seen[$slug] = true;
        $items[$index]['slug'] = $slug;
        $items[$index]['index'] = $index + 1;
        $items[$index]['detail_url'] = url_path('cohorts/detail/?slug=' . rawurlencode($slug));
    }

    return $items;
}

function cohort_find_by_slug(array $cohorts, string $slug): ?array
{
    foreach (cohort_items($cohorts) as $item) {
        if (($item['slug'] ?? '') === $slug) {
            return $item;
        }
    }

    return null;
}

/**
 * Build the responsive embed markup for a cohort video.
 *
 * Accepts a YouTube/Vimeo link (rendered as a privacy-friendly, lazy iframe),
 * a direct video URL, or an uploaded file path (rendered as a <video> element).
 * Returns an empty string when no source is provided.
 */
function cohort_video_html(string $video, string $title = '', string $poster = ''): string
{
    $video = trim($video);

    if ($video === '') {
        return '';
    }

    $label = e($title !== '' ? $title : 'Cohort video');

    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $video, $m) === 1) {
        $src = 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0';

        return '<iframe class="cohort-frame" src="' . e($src) . '" title="' . $label . '"'
            . ' loading="lazy" referrerpolicy="strict-origin-when-cross-origin"'
            . ' allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"'
            . ' allowfullscreen></iframe>';
    }

    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $video, $m) === 1) {
        $src = 'https://player.vimeo.com/video/' . $m[1];

        return '<iframe class="cohort-frame" src="' . e($src) . '" title="' . $label . '"'
            . ' loading="lazy" referrerpolicy="strict-origin-when-cross-origin"'
            . ' allow="fullscreen; picture-in-picture" allowfullscreen></iframe>';
    }

    $src = preg_match('#^https?://#', $video) === 1 ? $video : asset($video);
    $posterAttr = $poster !== '' ? ' poster="' . e(asset($poster)) . '"' : '';

    return '<video class="cohort-video" controls preload="none" playsinline' . $posterAttr . '>'
        . '<source src="' . e($src) . '">'
        . 'Your browser does not support embedded video.'
        . '</video>';
}
