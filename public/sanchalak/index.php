<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

if (current_admin() !== null) {
    redirect(admin_dashboard_url());
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } elseif ($password === '') {
        $errors[] = 'Enter your password.';
    } else {
        try {
            if (attempt_admin_login($email, $password)) {
                redirect(admin_dashboard_url());
            }

            $errors[] = 'Invalid email or password.';
        } catch (Throwable) {
            $errors[] = 'Unable to connect to the database. Check the local database settings.';
        }
    }
}

render('admin/login', [
    'email' => $email,
    'errors' => $errors,
    'pageTitle' => 'Admin Login',
]);
