<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';

$cohorts = cohort_public_archive(is_array($site['cohorts'] ?? null) ? $site['cohorts'] : []);
$site['cohorts'] = $cohorts;
$items = cohort_items($cohorts);
$requestedSlug = trim((string) ($_GET['slug'] ?? ''));
$cohort = $requestedSlug !== '' ? cohort_public_find_by_slug($items, $requestedSlug) : ($items[0] ?? null);

if ($cohort === null) {
    http_response_code(404);
}

$page = $site['page'];
$page['title'] = ($cohort['title'] ?? 'Cohort') . ' - Shweta Nagpal Cohorts';
$page['description'] = $cohort['description'] ?? 'Cohort detail from Shweta Nagpal on AI governance and public-sector technology.';
$page['og_type'] = 'article';
$page['og_title'] = '';
$page['og_description'] = '';
$page['og_image'] = (string) ($cohort['poster'] ?? '');

if ($cohort === null) {
    $page['robots'] = 'noindex, follow';
    $page['canonical'] = '/cohorts/';
} else {
    $page['canonical'] = '/cohorts/detail/?slug=' . rawurlencode((string) ($cohort['slug'] ?? $requestedSlug));
    $seo = seo_settings();
    $person = is_array($seo['person'] ?? null) ? $seo['person'] : [];
    $page['author_name'] = seo_text($person['name'] ?? '');
    if ($page['author_name'] === '') {
        $page['author_name'] = seo_text($seo['site_name'] ?? 'Shweta Nagpal');
    }
    $page['author_url'] = seo_canonical_url($seo, '/about-shweta/');
    $articleDates = seo_article_dates(
        $cohort['published_at'] ?? ($cohort['created_at'] ?? ''),
        $cohort['updated_at'] ?? ($cohort['published_at'] ?? ($cohort['created_at'] ?? ''))
    );
    $page['published_at'] = $articleDates['published'];
    $page['updated_at'] = $articleDates['modified'];
    $page['article_section'] = (string) ($cohort['category_name'] ?? '');
    $page['schemas'] = [
        seo_cohort_blog_posting_schema($seo, $cohort, $page['canonical']),
        seo_breadcrumb_schema($seo, [
            ['name' => 'Home', 'path' => '/'],
            ['name' => 'Cohorts', 'path' => '/cohorts/'],
            ['name' => (string) ($cohort['title'] ?? 'Cohort'), 'path' => $page['canonical']],
        ]),
    ];
}

render('layouts/cohorts', [
    'site' => $site,
    'page' => $page,
    'contentView' => 'pages/cohort_detail',
    'cohort' => $cohort,
    'cohortItems' => $items,
]);
