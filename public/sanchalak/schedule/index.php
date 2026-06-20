<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        try {
            if ($action === 'create_month_slots') {
                $old = $_POST;
                $result = schedule_create_monthly_slots($_POST, (int) $admin['id']);
                $errors = $result['errors'];

                if ($errors === []) {
                    flash('success', sprintf(
                        'Monthly availability created. %d slot%s added, %d skipped.',
                        $result['created'],
                        (int) $result['created'] === 1 ? '' : 's',
                        $result['skipped']
                    ));
                    redirect(admin_schedule_url());
                }
            } elseif ($action === 'create_slot') {
                $old = $_POST;
                $errors = schedule_create_admin_slot($_POST, (int) $admin['id']);

                if ($errors === []) {
                    flash('success', 'Meeting slot created.');
                    redirect(admin_schedule_url());
                }
            } elseif ($action === 'slot_status') {
                $errors = schedule_update_slot_status((int) ($_POST['slot_id'] ?? 0), (string) ($_POST['status'] ?? ''));

                if ($errors === []) {
                    flash('success', 'Slot status updated.');
                    redirect(admin_schedule_url());
                }
            } elseif ($action === 'delete_slot') {
                $errors = schedule_delete_slot((int) ($_POST['slot_id'] ?? 0));

                if ($errors === []) {
                    flash('success', 'Slot deleted.');
                    redirect(admin_schedule_url());
                }
            } elseif ($action === 'lock_day') {
                $old = $_POST;
                $errors = schedule_create_day_lock($_POST, (int) $admin['id']);

                if ($errors === []) {
                    flash('success', 'Day locked. Public visitors will not see slots on that date.');
                    redirect(admin_schedule_url());
                }
            } elseif ($action === 'unlock_day') {
                $errors = schedule_unlock_day((int) ($_POST['lock_id'] ?? 0));

                if ($errors === []) {
                    flash('success', 'Day unlocked. Open slots on that date can be booked again.');
                    redirect(admin_schedule_url());
                }
            } else {
                $errors[] = 'Unknown schedule action.';
            }
        } catch (Throwable) {
            $errors[] = 'Unable to update the schedule. Check the database connection and migrations.';
        }
    }
}

try {
    $overview = schedule_admin_overview();
    $slots = schedule_admin_slots();
    $dayLocks = schedule_admin_day_locks();
} catch (Throwable) {
    $overview = ['open_slots' => 0, 'confirmed_bookings' => 0, 'upcoming_bookings' => 0, 'locked_days' => 0];
    $slots = [];
    $dayLocks = [];
    $errors[] = 'Schedule tables are not ready. Run php portfolio/scripts/migrate.php.';
}

render('admin/schedule', [
    'admin' => $admin,
    'overview' => $overview,
    'slots' => $slots,
    'dayLocks' => $dayLocks,
    'errors' => $errors,
    'old' => $old,
    'success' => flash('success'),
    'pageTitle' => 'Schedule',
]);
