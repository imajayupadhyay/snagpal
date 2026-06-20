<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();

render('admin/dashboard', [
    'admin' => $admin,
    'site' => $site,
    'pageTitle' => 'Admin Dashboard',
]);
