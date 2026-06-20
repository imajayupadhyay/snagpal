<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$cohorts = cohort_public_archive(is_array($site['cohorts'] ?? null) ? $site['cohorts'] : []);
$site['cohorts'] = $cohorts;
$site['cohorts_page'] = cohorts_page_content();
$page = $site['page'];
$page['title'] = 'Cohorts - Shweta Nagpal';
$page['description'] = 'Cohort recordings, notes, and capability-building sessions on AI governance, public-sector technology, procurement, and critical infrastructure.';

render('layouts/cohorts', [
    'site' => $site,
    'page' => $page,
    'contentView' => 'pages/cohorts_index',
    'cohortItems' => cohort_items($cohorts),
]);
