<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        try {
            if ($action === 'create_admin') {
                $errors = admin_create_user($_POST);

                if ($errors === []) {
                    flash('success', 'New admin created.');
                    redirect(admin_users_url());
                }
            } elseif ($action === 'update_password') {
                $errors = admin_update_password((int) ($_POST['admin_id'] ?? 0), $_POST);

                if ($errors === []) {
                    flash('success', 'Password updated.');
                    redirect(admin_users_url());
                }
            } elseif ($action === 'delete_admin') {
                $errors = admin_delete_user((int) ($_POST['admin_id'] ?? 0), (int) $admin['id']);

                if ($errors === []) {
                    flash('success', 'Admin removed.');
                    redirect(admin_users_url());
                }
            } else {
                $errors[] = 'Unknown user action.';
            }
        } catch (Throwable) {
            $errors[] = 'Unable to update admin users. Check the database connection and try again.';
        }
    }
}

try {
    $admins = admin_users_all();
} catch (Throwable) {
    $admins = [];
    $errors[] = 'The admin_users table is not ready. Run php portfolio/scripts/migrate.php.';
}

render('admin/users', [
    'admin' => $admin,
    'admins' => $admins,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Users',
]);
