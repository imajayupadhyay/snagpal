<?php

declare(strict_types=1);

function event_registration_clean_text(mixed $value): string
{
    return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
}

function event_registration_safe_referer_path(): string
{
    $fallback = url_path('events/');
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $path = trim((string) (parse_url($referer, PHP_URL_PATH) ?? ''), '/');

    return $path === trim($fallback, '/') ? $fallback : $fallback;
}

function event_registration_current_source_path(): string
{
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $path = (string) (parse_url($referer, PHP_URL_PATH) ?? '');

    return $path !== '' ? substr($path, 0, 255) : url_path('events/');
}

function event_registration_submit(array $post): array
{
    $errors = [];

    if (! verify_public_csrf_token($post['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reopen the registration form and try again.';
    }

    if (
        trim((string) ($post['event_registration_reference'] ?? '')) !== ''
        || trim((string) ($post['website'] ?? '')) !== ''
    ) {
        $errors[] = 'Unable to submit this registration.';
    }

    $name = event_registration_clean_text($post['name'] ?? '');
    $email = strtolower(event_registration_clean_text($post['email'] ?? ''));
    $phone = event_registration_clean_text($post['phone'] ?? '');
    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 160) {
        $errors[] = 'Please enter your name.';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (
        mb_strlen($phone) < 7
        || mb_strlen($phone) > 40
        || strlen($digits) < 7
        || strlen($digits) > 15
        || preg_match('/^[0-9+()\-\s.]+$/', $phone) !== 1
    ) {
        $errors[] = 'Please enter a valid phone number.';
    }

    $old = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
    ];

    if ($errors !== []) {
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    try {
        $statement = db()->prepare(
            'INSERT INTO event_registrations (name, email, phone, source_path, user_agent)
             VALUES (:name, :email, :phone, :source_path, :user_agent)'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'source_path' => event_registration_current_source_path(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500) ?: null,
        ]);
        $registrationId = (int) db()->lastInsertId();
    } catch (Throwable) {
        return [
            'ok' => false,
            'errors' => ['Unable to submit your registration right now. Please try again later.'],
            'old' => $old,
        ];
    }

    admin_notification_create([
        'type' => 'event_registration',
        'severity' => 'info',
        'title' => 'New event registration',
        'body' => $name . ' registered interest in an upcoming event.',
        'action_label' => 'Open registrations',
        'action_url' => url_path('sanchalak/event-registrations/'),
        'source_type' => 'event_registration',
        'source_id' => $registrationId,
    ]);

    return [
        'ok' => true,
        'message' => 'Thank you. Your registration has been received.',
    ];
}

function event_registration_admin_all(): array
{
    return db()->query(
        'SELECT id, name, email, phone, source_path, created_at
         FROM event_registrations
         ORDER BY created_at DESC, id DESC'
    )->fetchAll();
}

function event_registration_admin_counts(): array
{
    $counts = [
        'total' => 0,
        'today' => 0,
        'month' => 0,
        'latest' => '',
    ];

    $row = db()->query(
        'SELECT
            COUNT(*) AS total,
            SUM(created_at >= CURDATE()) AS today,
            SUM(created_at >= DATE_FORMAT(CURDATE(), "%Y-%m-01")) AS month,
            MAX(created_at) AS latest
         FROM event_registrations'
    )->fetch();

    if (is_array($row)) {
        $counts['total'] = (int) ($row['total'] ?? 0);
        $counts['today'] = (int) ($row['today'] ?? 0);
        $counts['month'] = (int) ($row['month'] ?? 0);
        $counts['latest'] = (string) ($row['latest'] ?? '');
    }

    return $counts;
}
