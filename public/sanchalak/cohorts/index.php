<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/upload.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$editing = null;
$form = cohort_admin_default();
$pageContent = cohorts_page_content();

try {
    $editId = (int) ($_GET['edit'] ?? 0);
    $editing = $editId > 0 ? cohort_admin_find($editId) : null;
    $form = $editing ?? cohort_admin_default();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf_token($_POST['_token'] ?? null)) {
            $errors[] = 'Your session expired. Please try again.';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'save_page') {
                $pageContent = cohorts_page_content_from_post($_POST);
                cohorts_page_save_content($pageContent, (int) $admin['id']);
                flash('success', 'Page header updated.');
                redirect(admin_cohorts_url());
            } elseif ($action === 'save_cohort') {
                $id = (int) ($_POST['cohort_id'] ?? 0);
                $current = $id > 0 ? cohort_admin_find($id) : null;

                if ($id > 0 && $current === null) {
                    $errors[] = 'That cohort no longer exists.';
                    $form = cohort_admin_from_post($_POST, null);
                } else {
                    $form = cohort_admin_from_post($_POST, $current);
                    $form['poster_image'] = upload_cohort_poster('poster_upload', (string) ($form['poster_image'] ?? ''), $errors);
                    $form['video_path'] = upload_cohort_video('video_upload', (string) ($form['video_path'] ?? ''), $errors);

                    if ($errors === []) {
                        $errors = cohort_admin_validate($form, $id > 0 ? $id : null);
                    }

                    if ($errors === []) {
                        $savedId = cohort_admin_save($form, (int) $admin['id']);
                        flash('success', $id > 0 ? 'Cohort updated.' : 'Cohort created.');
                        redirect(admin_cohorts_url(['edit' => $savedId]));
                    }
                }
            } elseif ($action === 'delete_cohort') {
                $errors = cohort_admin_delete((int) ($_POST['cohort_id'] ?? 0));

                if ($errors === []) {
                    flash('success', 'Cohort deleted.');
                    redirect(admin_cohorts_url());
                }
            } else {
                $errors[] = 'Unknown cohort action.';
            }
        }
    }

    $cohorts = cohort_admin_all();
    $overview = cohort_admin_counts();
} catch (Throwable) {
    $cohorts = [];
    $overview = cohort_admin_counts_fallback();
    $errors[] = 'The cohorts table is not ready. Run php scripts/migrate.php from the portfolio folder.';
}

render('admin/cohorts', [
    'admin' => $admin,
    'cohorts' => $cohorts,
    'overview' => $overview,
    'form' => $form,
    'editing' => $editing,
    'pageContent' => $pageContent,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Cohorts',
]);

function cohort_admin_counts_fallback(): array
{
    return [
        'total' => 0,
        'published' => 0,
        'draft' => 0,
        'featured' => 0,
    ];
}
