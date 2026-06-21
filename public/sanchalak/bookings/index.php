<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/app/bootstrap.php';
require APP_PATH . '/auth.php';

start_admin_session();

$admin = require_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf_token($_POST['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        try {
            if ($action === 'confirm_booking') {
                $bookingId = (int) ($_POST['booking_id'] ?? 0);
                $errors = schedule_confirm_booking($bookingId);

                if ($errors === []) {
                    admin_notification_mark_source_read('meeting_booking', $bookingId, (int) $admin['id']);
                    flash('success', 'Booking confirmed.');
                    redirect(admin_bookings_url());
                }
            } elseif ($action === 'cancel_booking') {
                $bookingId = (int) ($_POST['booking_id'] ?? 0);
                $errors = schedule_cancel_booking($bookingId);

                if ($errors === []) {
                    admin_notification_mark_source_read('meeting_booking', $bookingId, (int) $admin['id']);
                    flash('success', 'Booking cancelled.');
                    redirect(admin_bookings_url());
                }
            } else {
                $errors[] = 'Unknown booking action.';
            }
        } catch (Throwable) {
            $errors[] = 'Unable to update the booking. Check the database connection and try again.';
        }
    }
}

try {
    $overview = schedule_admin_booking_overview();
    $bookings = schedule_admin_bookings();
} catch (Throwable) {
    $overview = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'upcoming' => 0, 'past' => 0];
    $bookings = [];
    $errors[] = 'Booking tables are not ready. Run php portfolio/scripts/migrate.php.';
}

render('admin/bookings', [
    'admin' => $admin,
    'overview' => $overview,
    'bookings' => $bookings,
    'errors' => $errors,
    'success' => flash('success'),
    'pageTitle' => 'Bookings',
]);
