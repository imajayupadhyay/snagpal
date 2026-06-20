<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf_token($_POST['_token'] ?? null)) {
            $errors[] = 'Your session expired. Please try again.';
        } else {
            $action = (string) ($_POST['action'] ?? '');
            $id = (int) ($_POST['submission_id'] ?? 0);

            if ($action === 'publish') {
                $errors = recommendation_admin_publish($id, (string) ($_POST['target'] ?? ''), (int) $admin['id']);

                if ($errors === []) {
                    flash('success', 'Recommendation published.');
                    redirect(admin_recommendations_url());
                }
            } elseif ($action === 'reject') {
                $errors = recommendation_admin_reject($id, (int) $admin['id']);

                if ($errors === []) {
                    flash('success', 'Submission rejected.');
                    redirect(admin_recommendations_url());
                }
            } elseif ($action === 'delete') {
                $errors = recommendation_admin_delete($id);

                if ($errors === []) {
                    flash('success', 'Submission deleted.');
                    redirect(admin_recommendations_url());
                }
            } else {
                $errors[] = 'Unknown action.';
            }
        }
    }

    $submissions = recommendation_admin_all();
    $overview = recommendation_admin_counts();
} catch (Throwable) {
    $submissions = [];
    $overview = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    $errors[] = 'The recommendation_submissions table is not ready. Run php scripts/migrate.php from the portfolio folder.';
}

render('admin/recommendations', [
    'admin' => $admin,
    'submissions' => $submissions,
    'overview' => $overview,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Recommendations',
]);
