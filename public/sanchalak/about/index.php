<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/upload.php';

start_admin_session();

$admin = require_admin();
$errors = [];

try {
    $pageContent = about_page_content();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf_token($_POST['_token'] ?? null)) {
            $errors[] = 'Your session expired. Please try again.';
        } else {
            $pageContent = about_page_content_from_post($_POST);
            $pageContent['portrait_src'] = upload_about_image('portrait_image_upload', $pageContent['portrait_src'], $errors);

            foreach ($pageContent['research_media'] as $index => $media) {
                $pageContent['research_media'][$index]['src'] = upload_about_image(
                    'research_media_image_' . $index,
                    (string) ($media['src'] ?? ''),
                    $errors
                );
            }

            if ($errors === []) {
                about_page_save_content($pageContent, (int) $admin['id']);
                flash('success', 'About page updated.');
                redirect(admin_about_url());
            }
        }
    }
} catch (Throwable) {
    $pageContent = about_page_default_content();
    $errors[] = 'Unable to load the about page content. Please try again.';
}

render('admin/about', [
    'admin' => $admin,
    'pageContent' => $pageContent,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'About Page',
]);
