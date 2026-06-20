<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$content = homepage_content();
$navigation = site_navigation(
    $content['navigation'] ?? [],
    empty($content['header_navigation_managed']),
    true
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $navigation = header_navigation_from_post($_POST, $errors);

        if ($errors === []) {
            $content['navigation'] = $navigation;
            $content['header_navigation_managed'] = true;

            try {
                homepage_save_content($content, (int) $admin['id']);
                flash('success', 'Header navigation updated.');
                redirect(admin_header_url());
            } catch (Throwable) {
                $errors[] = 'Unable to save header settings. Check the database connection.';
            }
        }
    }
}

render('admin/header', [
    'admin' => $admin,
    'navigation' => $navigation,
    'menuItems' => site_navigation_regular_items($navigation),
    'cta' => site_navigation_cta_item($navigation),
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Header Management',
]);
