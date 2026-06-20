<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! verify_csrf_token($_POST['_token'] ?? null)) {
    redirect(admin_dashboard_url());
}

logout_admin();
redirect(admin_login_url());
