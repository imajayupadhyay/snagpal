<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

render('layouts/main', [
    'site' => $site,
    'page' => $site['page'],
]);
