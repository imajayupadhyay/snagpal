<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/upload.php';

start_admin_session();

$admin = require_admin();
$errors = [];

try {
    $pageContent = awards_page_content();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf_token($_POST['_token'] ?? null)) {
            $errors[] = 'Your session expired. Please try again.';
        } else {
            $pageContent = awards_page_content_from_post($_POST);
            $pageContent['hero_card_image_src'] = upload_awards_image(
                'hero_card_image_upload',
                $pageContent['hero_card_image_src'],
                $errors
            );

            foreach ($pageContent['recognitions'] as $index => $item) {
                $pageContent['recognitions'][$index]['image'] = upload_awards_image(
                    'recognition_image_' . $index,
                    (string) ($item['image'] ?? ''),
                    $errors
                );
            }

            if ($errors === []) {
                awards_page_save_content($pageContent, (int) $admin['id']);
                flash('success', 'Awards page updated.');
                redirect(admin_awards_url());
            }
        }
    }
} catch (Throwable) {
    $pageContent = awards_page_default_content();
    $errors[] = 'Unable to load the awards page content. Please try again.';
}

render('admin/awards', [
    'admin' => $admin,
    'pageContent' => $pageContent,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Awards Page',
]);
