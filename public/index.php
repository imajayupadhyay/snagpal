<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$page = $site['page'];
$page['canonical'] = '/';

render('layouts/main', [
    'site' => $site,
    'page' => $page,
]);
