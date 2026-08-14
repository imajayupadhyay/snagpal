<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];

try {
    $registrations = event_registration_admin_all();
    $overview = event_registration_admin_counts();
} catch (Throwable) {
    $registrations = [];
    $overview = ['total' => 0, 'today' => 0, 'month' => 0, 'latest' => ''];
    $errors[] = 'The event_registrations table is not ready. Run php scripts/migrate.php from the portfolio folder.';
}

render('admin/event_registrations', [
    'admin' => $admin,
    'registrations' => $registrations,
    'overview' => $overview,
    'errors' => $errors,
    'pageTitle' => 'Event Registrations',
]);
