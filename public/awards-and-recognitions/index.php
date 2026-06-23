<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$site['awards_page'] = awards_page_content();
$page = $site['page'];
$page['title'] = 'Awards and Recognitions - Shweta Nagpal';
$page['description'] = 'Awards, recognitions, institutional mandates, and public-sector engagement milestones for Shweta Nagpal.';
$page['canonical'] = '/awards-and-recognitions/';
$page['og_type'] = 'website';
$page['og_title'] = '';
$page['og_description'] = '';
$page['og_image'] = '';

render('layouts/awards', [
    'site' => $site,
    'page' => $page,
]);
