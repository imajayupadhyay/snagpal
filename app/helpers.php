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

function site_default_navigation_links(): array
{
    return [
        'about' => ['label' => 'About Shweta', 'href' => url_path('about-shweta/'), 'class' => 'nav-about'],
        'awards' => ['label' => 'Awards & Recognitions', 'href' => url_path('awards-and-recognitions/'), 'class' => 'nav-awards'],
        'cohorts' => ['label' => 'Cohorts', 'href' => url_path('cohorts/'), 'class' => 'nav-cohorts'],
        'events' => ['label' => 'Events', 'href' => url_path('events/'), 'class' => 'nav-events'],
    ];
}

function site_default_header_cta(): array
{
    return ['label' => 'Schedule a Meet', 'href' => '#schedule', 'class' => 'cta'];
}

function site_is_hidden_navigation_item(array $item): bool
{
    $label = strtolower(trim((string) ($item['label'] ?? '')));
    $href = strtolower(trim((string) ($item['href'] ?? '')));

    return in_array($label, ['expertise', 'focus areas', 'research'], true)
        || in_array($href, ['#expertise', '#focus', '#research'], true);
}

function site_navigation_key(array $item): ?string
{
    $label = strtolower(trim(str_replace('&', 'and', (string) ($item['label'] ?? ''))));
    $href = strtolower(trim((string) ($item['href'] ?? '')));

    if ($label === 'about shweta' || str_contains($href, 'about-shweta')) {
        return 'about';
    }

    if ($label === 'awards and recognitions' || str_contains($href, 'awards-and-recognitions')) {
        return 'awards';
    }

    if ($label === 'cohorts' || preg_match('~(^|/)cohorts(/|$)~', $href) === 1) {
        return 'cohorts';
    }

    if ($label === 'events' || preg_match('~(^|/)events(/|$)~', $href) === 1) {
        return 'events';
    }

    return null;
}

function site_navigation_item_classes(array $item): string
{
    $class = trim((string) ($item['class'] ?? ''));
    $key = site_navigation_key($item);
    $classByKey = [
        'about' => 'nav-about',
        'awards' => 'nav-awards',
        'cohorts' => 'nav-cohorts',
        'events' => 'nav-events',
    ];

    if ($key !== null && isset($classByKey[$key]) && ! in_array($classByKey[$key], preg_split('/\s+/', $class) ?: [], true)) {
        $class = trim($class . ' ' . $classByKey[$key]);
    }

    return $class;
}

function site_navigation_is_cta(array $item): bool
{
    $classes = preg_split('/\s+/', strtolower(trim((string) ($item['class'] ?? '')))) ?: [];

    return in_array('cta', $classes, true);
}

function site_navigation_is_schedule_cta(array $item): bool
{
    $label = (string) ($item['label'] ?? '');
    $href = (string) ($item['href'] ?? '');

    return site_navigation_is_cta($item)
        && ($href === '#schedule' || stripos($label, 'schedule') !== false);
}

function site_navigation_is_current(array $item, string $currentPage): bool
{
    $key = site_navigation_key($item);

    return ($currentPage === 'about' && $key === 'about')
        || ($currentPage === 'awards' && $key === 'awards')
        || ($currentPage === 'cohorts' && $key === 'cohorts')
        || ($currentPage === 'events' && $key === 'events');
}

function site_navigation_display_href(string $href, string $currentPage): string
{
    return $currentPage !== 'home' && str_starts_with($href, '#') ? url_path($href) : $href;
}

function site_navigation_regular_items(array $navigation): array
{
    return array_values(array_filter($navigation, static fn (array $item): bool => ! site_navigation_is_cta($item)));
}

function site_navigation_cta_item(array $navigation): array
{
    foreach ($navigation as $item) {
        if (is_array($item) && site_navigation_is_cta($item)) {
            return $item;
        }
    }

    return site_default_header_cta();
}

function site_navigation(array $navigation, bool $ensureDefaults = true, bool $ensureCta = true): array
{
    $items = [];
    $cta = null;

    foreach ($navigation as $item) {
        if (! is_array($item)) {
            continue;
        }

        $label = trim((string) ($item['label'] ?? ''));
        $href = trim((string) ($item['href'] ?? ''));

        if ($label === '' || $href === '') {
            continue;
        }

        $clean = [
            'label' => $label,
            'href' => $href,
            'class' => site_navigation_item_classes($item),
        ];

        if (! empty($item['external'])) {
            $clean['external'] = true;
        }

        if (site_is_hidden_navigation_item($clean)) {
            continue;
        }

        if (site_navigation_is_cta($clean)) {
            $cta ??= $clean;
            continue;
        }

        $items[] = $clean;
    }

    if ($ensureDefaults) {
        $items = site_navigation_with_defaults($items);
    }

    if ($ensureCta) {
        $cta ??= site_default_header_cta();
    }

    if ($cta !== null) {
        $cta['class'] = site_navigation_cta_classes((string) ($cta['class'] ?? 'cta'));
        $items[] = $cta;
    }

    return $items;
}

function site_navigation_with_defaults(array $items): array
{
    $defaults = site_default_navigation_links();

    foreach ($defaults as $key => $default) {
        $present = false;

        foreach ($items as $item) {
            if (site_navigation_key($item) === $key) {
                $present = true;
                break;
            }
        }

        if ($present) {
            continue;
        }

        $insertAt = site_navigation_default_insert_index($items, $key);
        array_splice($items, $insertAt, 0, [$default]);
    }

    return $items;
}

function site_navigation_default_insert_index(array $items, string $key): int
{
    if ($key === 'about') {
        return 0;
    }

    $afterKeys = [
        'awards' => ['about'],
        'cohorts' => ['awards', 'about'],
        'events' => ['cohorts', 'awards', 'about'],
    ];
    $preferred = $afterKeys[$key] ?? [];
    $insertAt = 0;

    foreach ($items as $index => $item) {
        $itemKey = site_navigation_key($item);

        if ($itemKey !== null && in_array($itemKey, $preferred, true)) {
            $insertAt = $index + 1;

            if ($itemKey === $preferred[0]) {
                break;
            }
        }
    }

    return $insertAt;
}

function site_navigation_cta_classes(string $class): string
{
    $classes = preg_split('/\s+/', strtolower(trim($class))) ?: [];

    if (! in_array('cta', $classes, true)) {
        $class = trim($class . ' cta');
    }

    return $class !== '' ? $class : 'cta';
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
