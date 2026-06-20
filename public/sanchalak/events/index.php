<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';
require APP_PATH . '/upload.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$editing = null;
$form = event_admin_default();
$pageContent = events_page_content();

try {
    $editId = (int) ($_GET['edit'] ?? 0);
    $editing = $editId > 0 ? event_admin_find($editId) : null;
    $form = $editing ?? event_admin_default();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf_token($_POST['_token'] ?? null)) {
            $errors[] = 'Your session expired. Please try again.';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'save_page') {
                $pageContent = events_page_content_from_post($_POST);
                events_page_save_content($pageContent, (int) $admin['id']);
                flash('success', 'Page header updated.');
                redirect(admin_events_url());
            } elseif ($action === 'save_event') {
                $id = (int) ($_POST['event_id'] ?? 0);
                $current = $id > 0 ? event_admin_find($id) : null;

                if ($id > 0 && $current === null) {
                    $errors[] = 'That event no longer exists.';
                    $form = event_admin_from_post($_POST, null);
                } else {
                    $form = event_admin_from_post($_POST, $current);
                    $form['poster_image'] = upload_event_poster('poster_upload', (string) ($form['poster_image'] ?? ''), $errors);
                    $form['video_path'] = upload_event_video('video_upload', (string) ($form['video_path'] ?? ''), $errors);

                    if ($errors === []) {
                        $errors = event_admin_validate($form);
                    }

                    if ($errors === []) {
                        $savedId = event_admin_save($form, (int) $admin['id']);
                        flash('success', $id > 0 ? 'Event updated.' : 'Event created.');
                        redirect(admin_events_url(['edit' => $savedId]));
                    }
                }
            } elseif ($action === 'delete_event') {
                $errors = event_admin_delete((int) ($_POST['event_id'] ?? 0));

                if ($errors === []) {
                    flash('success', 'Event deleted.');
                    redirect(admin_events_url());
                }
            } else {
                $errors[] = 'Unknown event action.';
            }
        }
    }

    $events = event_admin_all();
    $overview = event_admin_counts();
} catch (Throwable) {
    $events = [];
    $overview = event_admin_counts_fallback();
    $errors[] = 'The events table is not ready. Run php scripts/migrate.php from the portfolio folder.';
}

render('admin/events', [
    'admin' => $admin,
    'events' => $events,
    'overview' => $overview,
    'form' => $form,
    'editing' => $editing,
    'pageContent' => $pageContent,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Events',
]);

function event_admin_counts_fallback(): array
{
    return [
        'total' => 0,
        'published' => 0,
        'draft' => 0,
        'upcoming' => 0,
        'past' => 0,
    ];
}
