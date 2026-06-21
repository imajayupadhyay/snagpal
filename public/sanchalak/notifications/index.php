<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$redirectTo = admin_notification_safe_redirect($_POST['redirect_to'] ?? ($_SERVER['HTTP_REFERER'] ?? admin_dashboard_url()));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($redirectTo);
}

if (! verify_csrf_token($_POST['_token'] ?? null)) {
    flash('error', 'Your session expired. Please try again.');
    redirect($redirectTo);
}

$action = (string) ($_POST['action'] ?? '');

if ($action === 'mark_read' || $action === 'mark_read_open') {
    admin_notification_mark_read((int) ($_POST['notification_id'] ?? 0), (int) $admin['id']);
} elseif ($action === 'mark_all_read') {
    admin_notifications_mark_all_read((int) $admin['id']);
}

redirect($redirectTo);
