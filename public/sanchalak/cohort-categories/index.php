<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$editing = null;
$form = cohort_category_admin_default();

try {
    $editId = (int) ($_GET['edit'] ?? 0);
    $editing = $editId > 0 ? cohort_category_admin_find($editId) : null;
    $form = $editing ?? cohort_category_admin_default();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf_token($_POST['_token'] ?? null)) {
            $errors[] = 'Your session expired. Please try again.';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'save_category') {
                $id = (int) ($_POST['category_id'] ?? 0);
                $current = $id > 0 ? cohort_category_admin_find($id) : null;

                if ($id > 0 && $current === null) {
                    $errors[] = 'That category no longer exists.';
                    $form = cohort_category_admin_from_post($_POST, null);
                } else {
                    $form = cohort_category_admin_from_post($_POST, $current);
                    $errors = cohort_category_admin_validate($form, $id > 0 ? $id : null);

                    if ($errors === []) {
                        cohort_category_admin_save($form);
                        flash('success', $id > 0 ? 'Category updated.' : 'Category created.');
                        redirect(admin_cohort_categories_url());
                    }
                }
            } elseif ($action === 'delete_category') {
                $errors = cohort_category_admin_delete((int) ($_POST['category_id'] ?? 0));

                if ($errors === []) {
                    flash('success', 'Category deleted. Cohorts in this category are now uncategorized.');
                    redirect(admin_cohort_categories_url());
                }
            } else {
                $errors[] = 'Unknown action.';
            }
        }
    }

    $categories = cohort_category_admin_all();
    $overview = cohort_category_admin_counts();
} catch (Throwable) {
    $categories = [];
    $overview = ['total' => 0];
    $errors[] = 'The cohort_categories table is not ready. Run php scripts/migrate.php from the portfolio folder.';
}

render('admin/cohort_categories', [
    'admin' => $admin,
    'categories' => $categories,
    'overview' => $overview,
    'form' => $form,
    'editing' => $editing,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Cohort Categories',
]);
