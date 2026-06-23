<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/upload.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$settings = seo_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $settings = seo_settings_from_post($_POST);
        $settings['default_og_image'] = upload_seo_image('default_og_image_upload', $settings['default_og_image'], $errors);
        $settings['person']['image'] = upload_seo_image('person_image_upload', $settings['person']['image'], $errors);

        if ($settings['site_name'] === '') {
            $errors[] = 'Add a site name.';
        }

        if ($errors === []) {
            try {
                seo_settings_save($settings, (int) $admin['id']);
                flash('success', 'SEO settings updated.');
                redirect(admin_seo_url());
            } catch (Throwable) {
                $errors[] = 'Unable to save SEO settings. Check the database connection.';
            }
        }
    }
}

render('admin/seo', [
    'admin' => $admin,
    'settings' => $settings,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'SEO & Site Settings',
]);
