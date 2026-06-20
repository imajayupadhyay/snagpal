<?php

declare(strict_types=1);

function public_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $sessionPath = ROOT_PATH . '/storage/sessions';

    if (! is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }

    session_name('shweta_public_session');
    session_save_path($sessionPath);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function public_csrf_token(): string
{
    public_session_start();

    if (empty($_SESSION['_public_csrf_token'])) {
        $_SESSION['_public_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_public_csrf_token'];
}

function public_csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(public_csrf_token()) . '">';
}

function verify_public_csrf_token(?string $token): bool
{
    public_session_start();

    return is_string($token)
        && isset($_SESSION['_public_csrf_token'])
        && hash_equals((string) $_SESSION['_public_csrf_token'], $token);
}

function public_flash(string $key, mixed $value = null): mixed
{
    public_session_start();

    if (func_num_args() === 2) {
        $_SESSION['_public_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_public_flash'][$key] ?? null;
    unset($_SESSION['_public_flash'][$key]);

    return $message;
}

function schedule_available_slots(int $days = 90): array
{
    try {
        $days = max(1, $days);
        $statement = db()->prepare(
            'SELECT s.id, s.slot_date, s.start_time, s.end_time
             FROM meeting_slots s
             WHERE s.status = "open"
               AND s.slot_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . $days . ' DAY)
               AND TIMESTAMP(s.slot_date, s.start_time) > NOW()
               AND NOT EXISTS (
                   SELECT 1 FROM meeting_bookings b
                   WHERE b.meeting_slot_id = s.id AND b.status IN ("confirmed", "pending")
               )
               AND NOT EXISTS (
                   SELECT 1 FROM meeting_day_locks l
                   WHERE l.lock_date = s.slot_date
               )
             ORDER BY s.slot_date ASC, s.start_time ASC'
        );
        $statement->execute();

        return array_map(static function (array $slot): array {
            return [
                'id' => (int) $slot['id'],
                'date' => (string) $slot['slot_date'],
                'start_time' => schedule_time_value((string) $slot['start_time']),
                'end_time' => schedule_time_value((string) $slot['end_time']),
                'date_label' => schedule_format_date((string) $slot['slot_date']),
                'time_label' => schedule_format_slot_label((string) $slot['start_time'], (string) $slot['end_time']),
            ];
        }, $statement->fetchAll());
    } catch (Throwable) {
        return [];
    }
}

function schedule_submit_booking(array $post): array
{
    $errors = [];

    if (! verify_public_csrf_token($post['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reopen the booking form and try again.';
    }

    if (trim((string) ($post['website'] ?? '')) !== '') {
        $errors[] = 'Unable to submit this booking request.';
    }

    $slotId = filter_var($post['slot_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $selectedDate = schedule_clean_text($post['meeting_date'] ?? '');
    $name = schedule_clean_text($post['visitor_name'] ?? '');
    $email = strtolower(schedule_clean_text($post['visitor_email'] ?? ''));
    $phone = schedule_clean_text($post['visitor_phone'] ?? '');
    $message = schedule_clean_multiline($post['message'] ?? '');

    if (! is_int($slotId)) {
        $errors[] = 'Please select an available meeting slot.';
    }

    if (! schedule_is_date($selectedDate)) {
        $errors[] = 'Please choose a valid meeting date.';
    }

    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        $errors[] = 'Please enter your name.';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone !== '' && mb_strlen($phone) > 40) {
        $errors[] = 'Please enter a shorter phone number.';
    }

    if (mb_strlen($message) > 1000) {
        $errors[] = 'Please keep the message under 1000 characters.';
    }

    $old = [
        'meeting_date' => $selectedDate,
        'slot_id' => is_int($slotId) ? (string) $slotId : '',
        'visitor_name' => $name,
        'visitor_email' => $email,
        'visitor_phone' => $phone,
        'message' => $message,
    ];

    if ($errors !== []) {
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    try {
        $pdo = db();
        $pdo->beginTransaction();

        $statement = $pdo->prepare('SELECT id, slot_date, start_time, end_time, status FROM meeting_slots WHERE id = :id FOR UPDATE');
        $statement->execute(['id' => $slotId]);
        $slot = $statement->fetch();

        if (! $slot || (string) $slot['status'] !== 'open') {
            $errors[] = 'That meeting slot is no longer available.';
        } elseif ((string) $slot['slot_date'] !== $selectedDate) {
            $errors[] = 'Please choose the slot again for the selected date.';
        } elseif (! schedule_slot_is_future((string) $slot['slot_date'], (string) $slot['start_time'])) {
            $errors[] = 'That meeting slot has already passed.';
        } else {
            $statement = $pdo->prepare('SELECT id FROM meeting_day_locks WHERE lock_date = :lock_date LIMIT 1 FOR UPDATE');
            $statement->execute(['lock_date' => $slot['slot_date']]);

            if ($statement->fetch()) {
                $errors[] = 'That day is no longer available for booking. Please choose another date.';
            }

            $statement = $pdo->prepare('SELECT id FROM meeting_bookings WHERE meeting_slot_id = :slot_id AND status IN ("confirmed", "pending") LIMIT 1 FOR UPDATE');
            $statement->execute(['slot_id' => $slotId]);

            if ($statement->fetch()) {
                $errors[] = 'That meeting slot was just requested by someone else. Please choose another slot.';
            }
        }

        if ($errors !== []) {
            $pdo->rollBack();
            return ['ok' => false, 'errors' => $errors, 'old' => $old];
        }

        $statement = $pdo->prepare(
            'INSERT INTO meeting_bookings
                (meeting_slot_id, visitor_name, visitor_email, visitor_phone, message, status)
             VALUES
                (:meeting_slot_id, :visitor_name, :visitor_email, :visitor_phone, :message, "pending")'
        );
        $statement->execute([
            'meeting_slot_id' => $slotId,
            'visitor_name' => $name,
            'visitor_email' => $email,
            'visitor_phone' => $phone !== '' ? $phone : null,
            'message' => $message !== '' ? $message : null,
        ]);

        $pdo->commit();

        try {
            notify_booking_requested([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'date_label' => schedule_format_date((string) $slot['slot_date']),
                'time_label' => schedule_format_slot_label((string) $slot['start_time'], (string) $slot['end_time']),
            ]);
        } catch (Throwable $mailException) {
            error_log('[booking] request emails failed: ' . $mailException->getMessage());
        }

        return [
            'ok' => true,
            'message' => 'Thanks, ' . $name . '. Your meeting request for '
                . schedule_format_date((string) $slot['slot_date'])
                . ' at ' . schedule_format_slot_label((string) $slot['start_time'], (string) $slot['end_time'])
                . ' has been received and is pending confirmation. We will email you at ' . $email
                . ' once it is confirmed.',
            'old' => [],
        ];
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'errors' => ['Unable to book that slot right now. Please choose another slot or try again shortly.'],
            'old' => $old,
        ];
    }
}

function schedule_admin_overview(): array
{
    try {
        $open = (int) db()->query(
            'SELECT COUNT(*)
             FROM meeting_slots s
             WHERE s.status = "open"
               AND TIMESTAMP(s.slot_date, s.start_time) > NOW()
               AND NOT EXISTS (
                   SELECT 1 FROM meeting_bookings b
                   WHERE b.meeting_slot_id = s.id AND b.status IN ("confirmed", "pending")
               )
               AND NOT EXISTS (
                   SELECT 1 FROM meeting_day_locks l
                   WHERE l.lock_date = s.slot_date
               )'
        )->fetchColumn();

        $booked = (int) db()->query('SELECT COUNT(*) FROM meeting_bookings WHERE status = "confirmed"')->fetchColumn();
        $upcoming = (int) db()->query(
            'SELECT COUNT(*)
             FROM meeting_bookings b
             INNER JOIN meeting_slots s ON s.id = b.meeting_slot_id
             WHERE b.status = "confirmed" AND TIMESTAMP(s.slot_date, s.start_time) > NOW()'
        )->fetchColumn();
        $locked = (int) db()->query('SELECT COUNT(*) FROM meeting_day_locks WHERE lock_date >= CURDATE()')->fetchColumn();

        return ['open_slots' => $open, 'confirmed_bookings' => $booked, 'upcoming_bookings' => $upcoming, 'locked_days' => $locked];
    } catch (Throwable) {
        return ['open_slots' => 0, 'confirmed_bookings' => 0, 'upcoming_bookings' => 0, 'locked_days' => 0];
    }
}

function schedule_admin_slots(): array
{
    $statement = db()->query(
        'SELECT
            s.id, s.slot_date, s.start_time, s.end_time, s.status, s.notes, s.created_at,
            b.id AS booking_id, b.visitor_name, b.visitor_email, b.visitor_phone, b.status AS booking_status,
            l.id AS day_lock_id, l.reason AS day_lock_reason
         FROM meeting_slots s
         LEFT JOIN meeting_bookings b ON b.meeting_slot_id = s.id AND b.status IN ("confirmed", "pending")
         LEFT JOIN meeting_day_locks l ON l.lock_date = s.slot_date
         WHERE s.slot_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
         ORDER BY s.slot_date ASC, s.start_time ASC
         LIMIT 250'
    );

    return $statement->fetchAll();
}

function schedule_admin_bookings(): array
{
    $statement = db()->query(
        'SELECT
            b.id, b.visitor_name, b.visitor_email, b.visitor_phone, b.message, b.status,
            b.created_at, b.cancelled_at,
            s.slot_date, s.start_time, s.end_time
         FROM meeting_bookings b
         INNER JOIN meeting_slots s ON s.id = b.meeting_slot_id
         ORDER BY s.slot_date DESC, s.start_time DESC, b.created_at DESC
         LIMIT 250'
    );

    return $statement->fetchAll();
}

function schedule_admin_booking_overview(): array
{
    try {
        $pending = (int) db()->query('SELECT COUNT(*) FROM meeting_bookings WHERE status = "pending"')->fetchColumn();
        $confirmed = (int) db()->query('SELECT COUNT(*) FROM meeting_bookings WHERE status = "confirmed"')->fetchColumn();
        $cancelled = (int) db()->query('SELECT COUNT(*) FROM meeting_bookings WHERE status = "cancelled"')->fetchColumn();
        $upcoming = (int) db()->query(
            'SELECT COUNT(*)
             FROM meeting_bookings b
             INNER JOIN meeting_slots s ON s.id = b.meeting_slot_id
             WHERE b.status = "confirmed" AND TIMESTAMP(s.slot_date, s.start_time) > NOW()'
        )->fetchColumn();
        $past = (int) db()->query(
            'SELECT COUNT(*)
             FROM meeting_bookings b
             INNER JOIN meeting_slots s ON s.id = b.meeting_slot_id
             WHERE b.status = "confirmed" AND TIMESTAMP(s.slot_date, s.start_time) <= NOW()'
        )->fetchColumn();

        return ['pending' => $pending, 'confirmed' => $confirmed, 'cancelled' => $cancelled, 'upcoming' => $upcoming, 'past' => $past];
    } catch (Throwable) {
        return ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'upcoming' => 0, 'past' => 0];
    }
}

function schedule_admin_day_locks(): array
{
    $statement = db()->query(
        'SELECT
            l.id, l.lock_date, l.reason, l.created_at,
            COUNT(b.id) AS confirmed_booking_count
         FROM meeting_day_locks l
         LEFT JOIN meeting_slots s ON s.slot_date = l.lock_date
         LEFT JOIN meeting_bookings b ON b.meeting_slot_id = s.id AND b.status = "confirmed"
         WHERE l.lock_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
         GROUP BY l.id, l.lock_date, l.reason, l.created_at
         ORDER BY l.lock_date ASC
         LIMIT 120'
    );

    return $statement->fetchAll();
}

function schedule_create_monthly_slots(array $post, int $adminId): array
{
    $month = schedule_clean_text($post['bulk_month'] ?? '');
    $weekdays = schedule_weekdays_from_post($post['weekdays'] ?? []);
    $start = schedule_clean_text($post['bulk_start_time'] ?? '');
    $end = schedule_clean_text($post['bulk_end_time'] ?? '');
    $duration = schedule_duration_minutes_from_post($post, 'slot_duration', 'custom_duration');
    $notes = schedule_clean_text($post['bulk_notes'] ?? '');
    $errors = [];

    if (! schedule_is_month($month)) {
        $errors[] = 'Please choose a valid month.';
    }

    if ($weekdays === []) {
        $errors[] = 'Please select at least one day of the week.';
    }

    if (! schedule_is_time($start) || ! schedule_is_time($end)) {
        $errors[] = 'Please enter valid start and end times.';
    } elseif ($start >= $end) {
        $errors[] = 'The end time must be after the start time.';
    }

    if (! is_int($duration)) {
        $errors[] = 'Please choose a valid slot duration between 5 minutes and 8 hours.';
    }

    if (mb_strlen($notes) > 255) {
        $errors[] = 'Please keep notes under 255 characters.';
    }

    if ($errors !== []) {
        return ['errors' => $errors, 'created' => 0, 'skipped' => 0];
    }

    $firstDay = DateTimeImmutable::createFromFormat('!Y-m-d', $month . '-01');

    if (! $firstDay instanceof DateTimeImmutable) {
        return ['errors' => ['Please choose a valid month.'], 'created' => 0, 'skipped' => 0];
    }

    $nextMonth = $firstDay->modify('first day of next month');
    $today = new DateTimeImmutable('today');
    $now = new DateTimeImmutable('now');
    $slotInterval = new DateInterval('PT' . $duration . 'M');
    $created = 0;
    $skipped = 0;
    $attempted = 0;
    $note = $notes !== '' ? $notes : 'Monthly availability: ' . $firstDay->format('F Y');

    try {
        $pdo = db();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT IGNORE INTO meeting_slots (slot_date, start_time, end_time, status, notes, created_by)
             VALUES (:slot_date, :start_time, :end_time, "open", :notes, :created_by)'
        );

        for ($day = $firstDay; $day < $nextMonth; $day = $day->modify('+1 day')) {
            if (! in_array((int) $day->format('N'), $weekdays, true)) {
                continue;
            }

            if ($day < $today) {
                $skipped++;
                continue;
            }

            $date = $day->format('Y-m-d');
            $cursor = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $start);
            $windowEnd = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $end);

            if (! $cursor instanceof DateTimeImmutable || ! $windowEnd instanceof DateTimeImmutable) {
                $skipped++;
                continue;
            }

            while ($cursor < $windowEnd) {
                $slotEnd = $cursor->add($slotInterval);

                if ($slotEnd > $windowEnd) {
                    break;
                }

                $attempted++;

                if ($attempted > 800) {
                    $pdo->rollBack();
                    return ['errors' => ['This setup would create too many slots. Reduce the days, range, or duration.'], 'created' => 0, 'skipped' => 0];
                }

                if ($cursor <= $now) {
                    $skipped++;
                    $cursor = $slotEnd;
                    continue;
                }

                $statement->execute([
                    'slot_date' => $date,
                    'start_time' => $cursor->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'notes' => $note,
                    'created_by' => $adminId,
                ]);

                if ($statement->rowCount() > 0) {
                    $created++;
                } else {
                    $skipped++;
                }

                $cursor = $slotEnd;
            }
        }

        $pdo->commit();
    } catch (Throwable) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['errors' => ['Unable to create monthly slots. Check the database and try again.'], 'created' => 0, 'skipped' => 0];
    }

    if ($created === 0 && $skipped === 0) {
        return ['errors' => ['No slots matched the selected month, days, and time range.'], 'created' => 0, 'skipped' => 0];
    }

    return ['errors' => [], 'created' => $created, 'skipped' => $skipped];
}

function schedule_create_admin_slot(array $post, int $adminId): array
{
    $date = schedule_clean_text($post['slot_date'] ?? '');
    $start = schedule_clean_text($post['start_time'] ?? '');
    $duration = schedule_duration_minutes_from_post($post, 'single_slot_duration', 'single_custom_duration');
    $end = schedule_clean_text($post['end_time'] ?? '');
    $status = schedule_clean_text($post['status'] ?? 'open');
    $notes = schedule_clean_text($post['notes'] ?? '');
    $errors = [];

    if (! schedule_is_date($date)) {
        $errors[] = 'Please enter a valid slot date.';
    }

    if (! schedule_is_time($start)) {
        $errors[] = 'Please enter a valid start time.';
    }

    if (! is_int($duration)) {
        if (! schedule_is_time($end)) {
            $errors[] = 'Please choose a valid slot duration between 5 minutes and 8 hours.';
        }
    } elseif (schedule_is_date($date) && schedule_is_time($start)) {
        $end = schedule_add_minutes_to_time($date, $start, $duration);

        if (! schedule_is_time($end)) {
            $errors[] = 'Unable to calculate the slot end time from that duration.';
        }
    }

    if (schedule_is_time($start) && schedule_is_time($end)) {
        if ($start >= $end) {
            $errors[] = 'The slot end time must be after the start time.';
        } elseif (schedule_is_date($date) && ! schedule_slot_is_future($date, $start)) {
            $errors[] = 'The slot start time must be in the future.';
        }
    }

    if (! in_array($status, ['open', 'closed'], true)) {
        $errors[] = 'Please choose a valid slot status.';
    }

    if (mb_strlen($notes) > 255) {
        $errors[] = 'Please keep slot notes under 255 characters.';
    }

    if ($errors !== []) {
        return $errors;
    }

    try {
        $statement = db()->prepare(
            'INSERT INTO meeting_slots (slot_date, start_time, end_time, status, notes, created_by)
             VALUES (:slot_date, :start_time, :end_time, :status, :notes, :created_by)'
        );
        $statement->execute([
            'slot_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
            'notes' => $notes !== '' ? $notes : null,
            'created_by' => $adminId,
        ]);
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            return ['That slot already exists.'];
        }

        return ['Unable to create the slot. Check the database and try again.'];
    }

    return [];
}

function schedule_update_slot_status(int $slotId, string $status): array
{
    if ($slotId <= 0 || ! in_array($status, ['open', 'closed'], true)) {
        return ['Invalid slot update request.'];
    }

    $statement = db()->prepare('UPDATE meeting_slots SET status = :status WHERE id = :id');
    $statement->execute(['status' => $status, 'id' => $slotId]);

    return [];
}

function schedule_delete_slot(int $slotId): array
{
    if ($slotId <= 0) {
        return ['Invalid slot delete request.'];
    }

    $statement = db()->prepare('SELECT COUNT(*) FROM meeting_bookings WHERE meeting_slot_id = :id');
    $statement->execute(['id' => $slotId]);

    if ((int) $statement->fetchColumn() > 0) {
        return ['Slots with booking history cannot be deleted. Cancel the booking or close the slot instead.'];
    }

    $statement = db()->prepare('DELETE FROM meeting_slots WHERE id = :id');
    $statement->execute(['id' => $slotId]);

    return [];
}

function schedule_create_day_lock(array $post, int $adminId): array
{
    $date = schedule_clean_text($post['lock_date'] ?? '');
    $reason = schedule_clean_text($post['lock_reason'] ?? '');
    $errors = [];

    if (! schedule_is_date($date)) {
        $errors[] = 'Please enter a valid lock date.';
    } else {
        $lockDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($lockDate instanceof DateTimeImmutable && $lockDate < new DateTimeImmutable('today')) {
            $errors[] = 'Past dates cannot be locked.';
        }
    }

    if (mb_strlen($reason) > 255) {
        $errors[] = 'Please keep the lock reason under 255 characters.';
    }

    if ($errors !== []) {
        return $errors;
    }

    try {
        $statement = db()->prepare(
            'INSERT INTO meeting_day_locks (lock_date, reason, created_by)
             VALUES (:lock_date, :reason, :created_by)
             ON DUPLICATE KEY UPDATE
                 reason = VALUES(reason),
                 created_by = VALUES(created_by),
                 updated_at = CURRENT_TIMESTAMP'
        );
        $statement->execute([
            'lock_date' => $date,
            'reason' => $reason !== '' ? $reason : null,
            'created_by' => $adminId,
        ]);
    } catch (Throwable) {
        return ['Unable to lock that day. Check the database and try again.'];
    }

    return [];
}

function schedule_unlock_day(int $lockId): array
{
    if ($lockId <= 0) {
        return ['Invalid day unlock request.'];
    }

    $statement = db()->prepare('DELETE FROM meeting_day_locks WHERE id = :id');
    $statement->execute(['id' => $lockId]);

    return [];
}

function schedule_confirm_booking(int $bookingId): array
{
    if ($bookingId <= 0) {
        return ['Invalid booking confirmation request.'];
    }

    $details = db()->prepare(
        'SELECT b.visitor_name, b.visitor_email, s.slot_date, s.start_time, s.end_time
         FROM meeting_bookings b
         INNER JOIN meeting_slots s ON s.id = b.meeting_slot_id
         WHERE b.id = :id AND b.status = "pending"
         LIMIT 1'
    );
    $details->execute(['id' => $bookingId]);
    $booking = $details->fetch();

    $statement = db()->prepare(
        'UPDATE meeting_bookings
         SET status = "confirmed"
         WHERE id = :id AND status = "pending"'
    );
    $statement->execute(['id' => $bookingId]);

    if ($statement->rowCount() === 0) {
        return ['That booking is no longer pending, or no longer exists.'];
    }

    if ($booking) {
        try {
            notify_booking_confirmed([
                'name' => (string) $booking['visitor_name'],
                'email' => (string) $booking['visitor_email'],
                'date_label' => schedule_format_date((string) $booking['slot_date']),
                'time_label' => schedule_format_slot_label((string) $booking['start_time'], (string) $booking['end_time']),
            ]);
        } catch (Throwable $mailException) {
            error_log('[booking] confirmation email failed: ' . $mailException->getMessage());
        }
    }

    return [];
}

function schedule_cancel_booking(int $bookingId): array
{
    if ($bookingId <= 0) {
        return ['Invalid booking cancellation request.'];
    }

    $statement = db()->prepare(
        'UPDATE meeting_bookings
         SET status = "cancelled", cancelled_at = CURRENT_TIMESTAMP
         WHERE id = :id AND status IN ("confirmed", "pending")'
    );
    $statement->execute(['id' => $bookingId]);

    if ($statement->rowCount() === 0) {
        return ['That booking is already cancelled or no longer exists.'];
    }

    return [];
}

function schedule_clean_text(mixed $value): string
{
    return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
}

function schedule_clean_multiline(mixed $value): string
{
    return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
}

function schedule_weekday_options(): array
{
    return [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];
}

function schedule_duration_options(): array
{
    return [
        15 => '15 minutes',
        20 => '20 minutes',
        30 => '30 minutes',
        45 => '45 minutes',
        60 => '1 hour',
        90 => '1 hour 30 minutes',
        120 => '2 hours',
    ];
}

function schedule_duration_minutes_from_post(array $post, string $durationKey, string $customKey): ?int
{
    $value = schedule_clean_text($post[$durationKey] ?? '');

    if ($value === 'custom') {
        $value = schedule_clean_text($post[$customKey] ?? '');
    }

    $duration = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 5, 'max_range' => 480]]);

    return is_int($duration) ? $duration : null;
}

function schedule_add_minutes_to_time(string $date, string $time, int $minutes): string
{
    $start = DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $time);

    if (! $start instanceof DateTimeImmutable) {
        return '';
    }

    return $start->add(new DateInterval('PT' . $minutes . 'M'))->format('H:i');
}

function schedule_weekdays_from_post(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    $weekdays = [];

    foreach ($value as $day) {
        $day = filter_var($day, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 7]]);

        if (is_int($day)) {
            $weekdays[] = $day;
        }
    }

    $weekdays = array_values(array_unique($weekdays));
    sort($weekdays);

    return $weekdays;
}

function schedule_is_month(string $value): bool
{
    $month = DateTimeImmutable::createFromFormat('!Y-m', $value);

    return $month instanceof DateTimeImmutable && $month->format('Y-m') === $value;
}

function schedule_is_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
}

function schedule_is_time(string $value): bool
{
    $time = DateTimeImmutable::createFromFormat('!H:i', $value);

    return $time instanceof DateTimeImmutable && $time->format('H:i') === $value;
}

function schedule_slot_is_future(string $date, string $startTime): bool
{
    $startsAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . schedule_time_value($startTime) . ':00')
        ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . $startTime);

    if (! $startsAt instanceof DateTimeImmutable) {
        return false;
    }

    return $startsAt > new DateTimeImmutable('now');
}

function schedule_time_value(string $value): string
{
    return substr($value, 0, 5);
}

function schedule_format_date(string $date): string
{
    $value = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    return $value instanceof DateTimeImmutable ? $value->format('D, M j, Y') : $date;
}

function schedule_format_time(string $time): string
{
    $value = DateTimeImmutable::createFromFormat('!H:i:s', $time)
        ?: DateTimeImmutable::createFromFormat('!H:i', $time);

    return $value instanceof DateTimeImmutable ? $value->format('g:i A') : $time;
}

function schedule_format_time_range(string $start, string $end): string
{
    return schedule_format_time($start) . ' - ' . schedule_format_time($end);
}

function schedule_format_slot_label(string $start, string $end): string
{
    return schedule_format_time_range($start, $end) . ' (' . schedule_format_duration($start, $end) . ')';
}

function schedule_format_duration(string $start, string $end): string
{
    $startValue = DateTimeImmutable::createFromFormat('!H:i:s', $start)
        ?: DateTimeImmutable::createFromFormat('!H:i', $start);
    $endValue = DateTimeImmutable::createFromFormat('!H:i:s', $end)
        ?: DateTimeImmutable::createFromFormat('!H:i', $end);

    if (! $startValue instanceof DateTimeImmutable || ! $endValue instanceof DateTimeImmutable || $endValue <= $startValue) {
        return 'custom duration';
    }

    $minutes = (int) (($endValue->getTimestamp() - $startValue->getTimestamp()) / 60);
    $hours = intdiv($minutes, 60);
    $remainingMinutes = $minutes % 60;
    $parts = [];

    if ($hours > 0) {
        $parts[] = $hours . ' hour' . ($hours === 1 ? '' : 's');
    }

    if ($remainingMinutes > 0) {
        $parts[] = $remainingMinutes . ' minute' . ($remainingMinutes === 1 ? '' : 's');
    }

    return implode(' ', $parts);
}
