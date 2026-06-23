<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$site['about_page'] = about_page_content();
$page = $site['page'];
$page['title'] = 'About Shweta Nagpal - AI Governance & Public-Sector Technology';
$page['description'] = 'About Shweta Nagpal, Nodal Officer for AI Governance at BBMB, working across public-sector technology, critical infrastructure, responsible AI, and e-governance.';
$page['canonical'] = '/about-shweta/';
$page['og_type'] = 'profile';
$page['og_title'] = '';
$page['og_description'] = '';
$page['og_image'] = '';

render('layouts/about', [
    'site' => $site,
    'page' => $page,
]);
