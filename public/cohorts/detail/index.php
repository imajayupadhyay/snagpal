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

render('layouts/cohorts', [
    'site' => $site,
    'page' => $page,
    'contentView' => 'pages/cohort_detail',
    'cohort' => $cohort,
    'cohortItems' => $items,
]);
