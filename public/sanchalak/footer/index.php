<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$content = homepage_content();
$footer = is_array($content['footer'] ?? null) ? $content['footer'] : homepage_default_content()['footer'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $footer = [
            'copyright_name' => homepage_text($_POST['footer']['copyright_name'] ?? ''),
            'tagline' => homepage_text($_POST['footer']['tagline'] ?? ''),
            'back_to_top_label' => homepage_text($_POST['footer']['back_to_top_label'] ?? ''),
        ];

        if ($footer['copyright_name'] === '') {
            $errors[] = 'Add the copyright name.';
        }

        if ($footer['back_to_top_label'] === '') {
            $errors[] = 'Add the back-to-top link label.';
        }

        if ($errors === []) {
            $content['footer'] = $footer;

            try {
                homepage_save_content($content, (int) $admin['id']);
                flash('success', 'Footer updated.');
                redirect(admin_footer_url());
            } catch (Throwable) {
                $errors[] = 'Unable to save footer settings. Check the database connection.';
            }
        }
    }
}

render('admin/footer', [
    'admin' => $admin,
    'footer' => $footer,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Footer Management',
]);
