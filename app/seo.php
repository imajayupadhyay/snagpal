<?php

declare(strict_types=1);

/**
 * Global SEO / Site Settings.
 *
 * Stored as a single `seo_settings` row in the existing `site_contents`
 * key/value table (same pattern as homepage/about/awards content). Powers
 * the shared <head> partial: canonical/OG/Twitter tags, verification meta,
 * analytics injection, icons, and the JSON-LD Person + WebSite schema.
 */

function seo_settings_defaults(): array
{
    return [
        // Part B2 — Global SEO / Site Settings
        'site_name' => 'Shweta Nagpal',
        'default_og_image' => '',
        'default_robots' => 'index, follow, max-image-preview:large, max-snippet:-1',
        'canonical' => [
            'host' => 'shwetanagpal.com',
            'https' => true,
            'www' => false,
            'trailing_slash' => true,
        ],
        'verification' => [
            'google' => '',
            'bing' => '',
        ],
        'analytics_id' => '',
        'twitter_handle' => '',
        'locale' => 'en_US',
        // Leave blank to auto-generate from the canonical domain.
        'robots_txt' => '',
        // Part B3 — Structured Data / Person
        'person' => [
            'enabled' => true,
            'name' => 'Shweta Nagpal',
            'job_title' => 'Nodal Officer, AI Governance',
            'org_name' => 'Bhakra Beas Management Board',
            'org_url' => 'https://bbmb.gov.in/',
            'org_type' => 'GovernmentOrganization',
            'image' => 'images/profile-commemoration.jpg',
            'description' => 'Nodal Officer for AI Governance at the Bhakra Beas Management Board, Ministry of Power, Government of India.',
            'email' => '',
            'email_public' => false,
            'telephone' => '',
            'address_locality' => 'Chandigarh',
            'address_region' => 'Chandigarh',
            'address_country' => 'IN',
        ],
        // sameAs[] — official + social profiles
        'social_links' => [
            'https://www.linkedin.com/in/shweta-nagpal-15856239',
        ],
        // award[] — feed from the Awards section
        'awards' => [],
    ];
}

function seo_settings(): array
{
    $default = seo_settings_defaults();

    try {
        $statement = db()->prepare('SELECT content_json FROM site_contents WHERE content_key = :key LIMIT 1');
        $statement->execute(['key' => 'seo_settings']);
        $row = $statement->fetch();

        if (! $row) {
            return $default;
        }

        $stored = json_decode((string) $row['content_json'], true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($stored)) {
            return $default;
        }

        return seo_merge($default, $stored);
    } catch (Throwable) {
        return $default;
    }
}

function seo_settings_save(array $settings, ?int $adminId = null): void
{
    $json = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $statement = db()->prepare(
        'INSERT INTO site_contents (content_key, content_json, updated_by)
         VALUES (:content_key, :content_json, :updated_by)
         ON DUPLICATE KEY UPDATE
             content_json = VALUES(content_json),
             updated_by = VALUES(updated_by),
             updated_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([
        'content_key' => 'seo_settings',
        'content_json' => $json,
        'updated_by' => $adminId,
    ]);
}

/**
 * Recursively overlay stored values onto the defaults. Mirrors
 * homepage_merge_content() but kept local so this module is self-contained.
 */
function seo_merge(array $default, array $stored): array
{
    foreach ($stored as $key => $value) {
        if (is_array($value) && isset($default[$key]) && is_array($default[$key]) && ! array_is_list($value)) {
            $default[$key] = seo_merge($default[$key], $value);
            continue;
        }

        $default[$key] = $value;
    }

    return $default;
}

function seo_settings_from_post(array $post): array
{
    $defaults = seo_settings_defaults();
    $canonical = is_array($post['canonical'] ?? null) ? $post['canonical'] : [];
    $verification = is_array($post['verification'] ?? null) ? $post['verification'] : [];
    $person = is_array($post['person'] ?? null) ? $post['person'] : [];

    $host = seo_clean_host((string) ($canonical['host'] ?? $defaults['canonical']['host']));

    return [
        'site_name' => seo_text($post['site_name'] ?? $defaults['site_name']),
        'default_og_image' => seo_text($post['default_og_image'] ?? ''),
        'default_robots' => seo_text($post['default_robots'] ?? $defaults['default_robots']),
        'canonical' => [
            'host' => $host !== '' ? $host : $defaults['canonical']['host'],
            'https' => ! empty($canonical['https']),
            'www' => ! empty($canonical['www']),
            'trailing_slash' => ! empty($canonical['trailing_slash']),
        ],
        'verification' => [
            'google' => seo_text($verification['google'] ?? ''),
            'bing' => seo_text($verification['bing'] ?? ''),
        ],
        'analytics_id' => seo_text($post['analytics_id'] ?? ''),
        'twitter_handle' => seo_clean_handle($post['twitter_handle'] ?? ''),
        'locale' => seo_text($post['locale'] ?? $defaults['locale']),
        'robots_txt' => seo_textarea($post['robots_txt'] ?? ''),
        'person' => [
            'enabled' => ! empty($person['enabled']),
            'name' => seo_text($person['name'] ?? ''),
            'job_title' => seo_text($person['job_title'] ?? ''),
            'org_name' => seo_text($person['org_name'] ?? ''),
            'org_url' => seo_text($person['org_url'] ?? ''),
            'org_type' => seo_person_org_type((string) ($person['org_type'] ?? '')),
            'image' => seo_text($person['image'] ?? ''),
            'description' => seo_textarea($person['description'] ?? ''),
            'email' => seo_text($person['email'] ?? ''),
            'email_public' => ! empty($person['email_public']),
            'telephone' => seo_text($person['telephone'] ?? ''),
            'address_locality' => seo_text($person['address_locality'] ?? ''),
            'address_region' => seo_text($person['address_region'] ?? ''),
            'address_country' => seo_text($person['address_country'] ?? ''),
        ],
        'social_links' => seo_clean_list($post['social_links'] ?? []),
        'awards' => seo_clean_list($post['awards'] ?? []),
    ];
}

function seo_person_org_type(string $value): string
{
    $allowed = ['GovernmentOrganization', 'Organization', 'EducationalOrganization'];

    return in_array($value, $allowed, true) ? $value : 'GovernmentOrganization';
}

function seo_text(mixed $value): string
{
    return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
}

function seo_textarea(mixed $value): string
{
    return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
}

function seo_clean_host(string $host): string
{
    $host = trim($host);
    $host = preg_replace('#^https?://#i', '', $host) ?? $host;
    $host = preg_replace('#^www\.#i', '', $host) ?? $host;

    return rtrim(trim($host), '/');
}

function seo_clean_handle(mixed $value): string
{
    $handle = seo_text($value);

    if ($handle === '') {
        return '';
    }

    return '@' . ltrim($handle, '@');
}

/**
 * Accepts either a repeatable array of rows or a newline-separated textarea
 * and returns a clean, de-duplicated, non-empty list of single-line values.
 */
function seo_clean_list(mixed $value): array
{
    $items = [];

    if (is_array($value)) {
        $items = $value;
    } elseif (is_string($value)) {
        $items = preg_split('/\R+/', $value) ?: [];
    }

    $clean = [];

    foreach ($items as $item) {
        $line = seo_text($item);

        if ($line !== '' && ! in_array($line, $clean, true)) {
            $clean[] = $line;
        }
    }

    return $clean;
}

/** Scheme + host, e.g. https://shwetanagpal.com (no trailing slash). */
function seo_canonical_base(array $seo): string
{
    $canonical = is_array($seo['canonical'] ?? null) ? $seo['canonical'] : [];
    $host = seo_clean_host((string) ($canonical['host'] ?? ''));

    if ($host === '') {
        // Fall back to the configured app URL host (keeps dev usable).
        $appUrl = (string) ($GLOBALS['config']['url'] ?? 'http://localhost');

        return rtrim($appUrl, '/');
    }

    $scheme = ! empty($canonical['https']) ? 'https' : 'http';
    $prefix = ! empty($canonical['www']) ? 'www.' : '';

    return $scheme . '://' . $prefix . $host;
}

/** Build an absolute canonical URL for a site path. */
function seo_canonical_url(array $seo, string $path): string
{
    $canonical = is_array($seo['canonical'] ?? null) ? $seo['canonical'] : [];
    $base = seo_canonical_base($seo);

    $query = '';
    if (str_contains($path, '?')) {
        [$path, $query] = explode('?', $path, 2);
        $query = $query !== '' ? '?' . $query : '';
    }

    $path = '/' . ltrim($path, '/');

    // Normalise trailing slash (skip files and the bare root).
    $isFile = (bool) preg_match('#\.[a-z0-9]{2,5}$#i', $path);

    if (! empty($canonical['trailing_slash'])) {
        if ($path !== '/' && ! $isFile && ! str_ends_with($path, '/')) {
            $path .= '/';
        }
    } elseif ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }

    return $base . $path . $query;
}

/** Turn an asset path or relative URL into an absolute one. */
function seo_absolute_url(array $seo, string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $value) === 1) {
        return $value;
    }

    return seo_canonical_base($seo) . asset($value);
}

/** The current request path (no query string), normalised with a leading slash. */
function seo_current_path(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);

    return is_string($path) && $path !== '' ? $path : '/';
}

/**
 * Build the schema.org Person array from the saved settings.
 * Only includes optional keys when their source field is non-empty.
 */
function seo_person_schema(array $seo): array
{
    $person = is_array($seo['person'] ?? null) ? $seo['person'] : [];

    if (empty($person['enabled']) || ($person['name'] ?? '') === '') {
        return [];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => (string) $person['name'],
        'url' => seo_canonical_url($seo, '/'),
    ];

    if (($person['job_title'] ?? '') !== '') {
        $schema['jobTitle'] = (string) $person['job_title'];
    }

    if (($person['org_name'] ?? '') !== '') {
        $org = [
            '@type' => seo_person_org_type((string) ($person['org_type'] ?? 'Organization')),
            'name' => (string) $person['org_name'],
        ];

        if (($person['org_url'] ?? '') !== '') {
            $org['url'] = (string) $person['org_url'];
        }

        $schema['worksFor'] = $org;
    }

    $image = seo_absolute_url($seo, (string) ($person['image'] ?? ''));
    if ($image !== '') {
        $schema['image'] = $image;
    }

    $address = array_filter([
        'addressLocality' => (string) ($person['address_locality'] ?? ''),
        'addressRegion' => (string) ($person['address_region'] ?? ''),
        'addressCountry' => (string) ($person['address_country'] ?? ''),
    ], static fn (string $v): bool => $v !== '');

    if ($address !== []) {
        $schema['address'] = array_merge(['@type' => 'PostalAddress'], $address);
    }

    if (($person['description'] ?? '') !== '') {
        $schema['description'] = (string) $person['description'];
    }

    if (! empty($person['email_public']) && ($person['email'] ?? '') !== '') {
        $schema['email'] = (string) $person['email'];
    }

    if (($person['telephone'] ?? '') !== '') {
        $schema['telephone'] = (string) $person['telephone'];
    }

    $sameAs = seo_clean_list($seo['social_links'] ?? []);
    if ($sameAs !== []) {
        $schema['sameAs'] = $sameAs;
    }

    $awards = seo_clean_list($seo['awards'] ?? []);
    if ($awards !== []) {
        $schema['award'] = $awards;
    }

    return $schema;
}

/** The robots.txt body — admin override if set, else generated from defaults. */
function seo_robots_txt(array $seo): string
{
    $override = trim((string) ($seo['robots_txt'] ?? ''));

    if ($override !== '') {
        return $override;
    }

    $sitemap = seo_canonical_url($seo, '/sitemap.xml');

    return "User-agent: *\n"
        . "Allow: /\n"
        . "Disallow: /sanchalak/\n\n"
        . 'Sitemap: ' . $sitemap . "\n";
}

function seo_website_schema(array $seo): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => (string) ($seo['site_name'] ?? 'Shweta Nagpal'),
        'url' => seo_canonical_url($seo, '/'),
    ];
}

/**
 * Public, indexable URLs for the sitemap. Returns a list of
 * ['loc' => absolute-url, 'priority' => '0.8', 'changefreq' => 'monthly'].
 */
function seo_sitemap_urls(array $seo): array
{
    $urls = [
        ['path' => '/', 'priority' => '1.0', 'changefreq' => 'monthly'],
        ['path' => '/about-shweta/', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['path' => '/awards-and-recognitions/', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['path' => '/cohorts/', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['path' => '/events/', 'priority' => '0.7', 'changefreq' => 'weekly'],
    ];

    // Add each published cohort detail page when the data is available.
    try {
        if (function_exists('cohort_public_archive') && function_exists('cohort_items')) {
            $site = homepage_content();
            $cohorts = cohort_public_archive(is_array($site['cohorts'] ?? null) ? $site['cohorts'] : []);

            foreach (cohort_items($cohorts) as $cohort) {
                $slug = (string) ($cohort['slug'] ?? '');

                if ($slug !== '') {
                    $urls[] = [
                        'path' => '/cohorts/detail/?slug=' . rawurlencode($slug),
                        'priority' => '0.5',
                        'changefreq' => 'monthly',
                    ];
                }
            }
        }
    } catch (Throwable) {
        // Ignore — the static pages above are always enough for a valid sitemap.
    }

    $out = [];
    foreach ($urls as $url) {
        $out[] = [
            'loc' => seo_canonical_url($seo, $url['path']),
            'priority' => $url['priority'],
            'changefreq' => $url['changefreq'],
        ];
    }

    return $out;
}
