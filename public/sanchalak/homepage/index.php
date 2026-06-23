<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/upload.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$content = homepage_content();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $content = homepage_content_from_post($_POST, $content);
        $content['page']['og_image'] = upload_homepage_image('og_image_upload', (string) ($content['page']['og_image'] ?? ''), $errors);
        $content['hero']['image']['src'] = upload_homepage_image('hero_image_upload', $content['hero']['image']['src'], $errors);
        $content['profile']['image']['src'] = upload_homepage_image('profile_image_upload', $content['profile']['image']['src'], $errors);

        foreach ($content['research']['media'] as $index => $media) {
            $content['research']['media'][$index]['src'] = upload_homepage_image(
                'research_media_image_' . $index,
                (string) ($media['src'] ?? ''),
                $errors
            );
        }

        foreach ($content['cohorts']['items'] as $index => $cohort) {
            $content['cohorts']['items'][$index]['video'] = upload_homepage_video(
                'cohort_video_' . $index,
                (string) ($cohort['video'] ?? ''),
                $errors
            );
            $content['cohorts']['items'][$index]['poster'] = upload_homepage_image(
                'cohort_poster_' . $index,
                (string) ($cohort['poster'] ?? ''),
                $errors
            );
        }

        if ($errors === []) {
            try {
                homepage_save_content($content, (int) $admin['id']);
                flash('success', 'Homepage content updated.');
                redirect(admin_homepage_url());
            } catch (Throwable) {
                $errors[] = 'Unable to save homepage content. Check the database connection.';
            }
        }
    }
}

render('admin/homepage', [
    'admin' => $admin,
    'content' => $content,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Homepage Content',
]);
