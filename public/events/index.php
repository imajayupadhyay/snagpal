<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$site['events'] = event_public_archive(is_array($site['events'] ?? null) ? $site['events'] : []);
$site['events_page'] = events_page_content();
$page = $site['page'];
$page['title'] = 'Upcoming & Past Events - Shweta Nagpal';
$page['description'] = 'Upcoming and past events featuring talks, workshops, and public-sector technology engagements by Shweta Nagpal.';

render('layouts/events', [
    'site' => $site,
    'page' => $page,
]);
