<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$site['cohorts'] = cohort_public_recent(is_array($site['cohorts'] ?? null) ? $site['cohorts'] : []);
$site['cohorts']['items'] = cohort_items($site['cohorts']);

$page = $site['page'];
$page['canonical'] = '/';

render('layouts/main', [
    'site' => $site,
    'page' => $page,
]);
