<?php

declare(strict_types=1);

function admin_notification_create(array $data): ?int
{
    $type = admin_notification_clean_text($data['type'] ?? '', 80);
    $title = admin_notification_clean_text($data['title'] ?? '', 190);
    $body = admin_notification_clean_multiline($data['body'] ?? '', 1200);

    if ($type === '' || $title === '' || $body === '') {
        return null;
    }

    $severity = admin_notification_clean_text($data['severity'] ?? 'info', 20);
    $allowedSeverities = ['info', 'success', 'warning', 'danger'];

    if (! in_array($severity, $allowedSeverities, true)) {
        $severity = 'info';
    }

    $actionLabel = admin_notification_clean_text($data['action_label'] ?? '', 80);
    $actionUrl = admin_notification_clean_url($data['action_url'] ?? '');
    $sourceType = admin_notification_clean_text($data['source_type'] ?? '', 80);
    $sourceId = filter_var($data['source_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    try {
        $statement = db()->prepare(
            'INSERT INTO admin_notifications
                (type, severity, title, body, action_label, action_url, source_type, source_id)
             VALUES
                (:type, :severity, :title, :body, :action_label, :action_url, :source_type, :source_id)
             ON DUPLICATE KEY UPDATE
                id = LAST_INSERT_ID(id),
                severity = VALUES(severity),
                title = VALUES(title),
                body = VALUES(body),
                action_label = VALUES(action_label),
                action_url = VALUES(action_url),
                updated_at = CURRENT_TIMESTAMP'
        );
        $statement->execute([
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'body' => $body,
            'action_label' => $actionLabel !== '' ? $actionLabel : null,
            'action_url' => $actionUrl !== '' ? $actionUrl : null,
            'source_type' => $sourceType !== '' ? $sourceType : null,
            'source_id' => is_int($sourceId) ? $sourceId : null,
        ]);

        return (int) db()->lastInsertId();
    } catch (Throwable $exception) {
        error_log('[admin-notifications] create failed: ' . $exception->getMessage());

        return null;
    }
}

function admin_notifications_recent(int $adminId, int $limit = 8): array
{
    if ($adminId <= 0) {
        return [];
    }

    $limit = max(1, min($limit, 20));

    try {
        $statement = db()->prepare(
            'SELECT n.*, r.read_at
             FROM admin_notifications n
             LEFT JOIN admin_notification_reads r
                ON r.notification_id = n.id AND r.admin_user_id = :admin_id
             WHERE n.expires_at IS NULL OR n.expires_at > CURRENT_TIMESTAMP
             ORDER BY r.read_at IS NULL DESC, n.created_at DESC, n.id DESC
             LIMIT ' . $limit
        );
        $statement->execute(['admin_id' => $adminId]);

        return $statement->fetchAll();
    } catch (Throwable $exception) {
        error_log('[admin-notifications] recent failed: ' . $exception->getMessage());

        return [];
    }
}

function admin_notifications_unread_count(int $adminId): int
{
    if ($adminId <= 0) {
        return 0;
    }

    try {
        $statement = db()->prepare(
            'SELECT COUNT(*)
             FROM admin_notifications n
             LEFT JOIN admin_notification_reads r
                ON r.notification_id = n.id AND r.admin_user_id = :admin_id
             WHERE r.notification_id IS NULL
                AND (n.expires_at IS NULL OR n.expires_at > CURRENT_TIMESTAMP)'
        );
        $statement->execute(['admin_id' => $adminId]);

        return (int) $statement->fetchColumn();
    } catch (Throwable $exception) {
        error_log('[admin-notifications] unread count failed: ' . $exception->getMessage());

        return 0;
    }
}

function admin_notification_mark_read(int $notificationId, int $adminId): void
{
    if ($notificationId <= 0 || $adminId <= 0) {
        return;
    }

    try {
        $statement = db()->prepare(
            'INSERT IGNORE INTO admin_notification_reads (notification_id, admin_user_id)
             VALUES (:notification_id, :admin_user_id)'
        );
        $statement->execute([
            'notification_id' => $notificationId,
            'admin_user_id' => $adminId,
        ]);
    } catch (Throwable $exception) {
        error_log('[admin-notifications] mark read failed: ' . $exception->getMessage());
    }
}

function admin_notifications_mark_all_read(int $adminId): void
{
    if ($adminId <= 0) {
        return;
    }

    try {
        $statement = db()->prepare(
            'INSERT IGNORE INTO admin_notification_reads (notification_id, admin_user_id)
             SELECT n.id, :admin_id
             FROM admin_notifications n
             LEFT JOIN admin_notification_reads r
                ON r.notification_id = n.id AND r.admin_user_id = :admin_id_join
             WHERE r.notification_id IS NULL
                AND (n.expires_at IS NULL OR n.expires_at > CURRENT_TIMESTAMP)'
        );
        $statement->execute([
            'admin_id' => $adminId,
            'admin_id_join' => $adminId,
        ]);
    } catch (Throwable $exception) {
        error_log('[admin-notifications] mark all read failed: ' . $exception->getMessage());
    }
}

function admin_notification_mark_source_read(string $sourceType, int $sourceId, int $adminId): void
{
    $sourceType = admin_notification_clean_text($sourceType, 80);

    if ($sourceType === '' || $sourceId <= 0 || $adminId <= 0) {
        return;
    }

    try {
        $statement = db()->prepare(
            'SELECT id FROM admin_notifications
             WHERE source_type = :source_type AND source_id = :source_id'
        );
        $statement->execute([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $notificationId) {
            admin_notification_mark_read((int) $notificationId, $adminId);
        }
    } catch (Throwable $exception) {
        error_log('[admin-notifications] mark source read failed: ' . $exception->getMessage());
    }
}

function admin_notification_safe_redirect(mixed $target): string
{
    $fallback = url_path('sanchalak/dashboard/');
    $target = trim((string) $target);

    if ($target === '') {
        return $fallback;
    }

    $parts = parse_url($target);

    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $fallback;
    }

    $path = (string) ($parts['path'] ?? '');
    $adminBase = trim(url_path('sanchalak/'), '/');
    $normalizedPath = trim($path, '/');

    if ($normalizedPath === $adminBase || str_starts_with($normalizedPath, $adminBase . '/')) {
        return $target;
    }

    return $fallback;
}

function admin_notification_type_label(string $type): string
{
    return match ($type) {
        'booking_pending' => 'Booking',
        'recommendation_pending' => 'Recommendation',
        default => 'Notice',
    };
}

function admin_notification_relative_time(mixed $value): string
{
    $raw = trim((string) $value);

    if ($raw === '') {
        return '';
    }

    try {
        $created = new DateTimeImmutable($raw);
        $now = new DateTimeImmutable('now');
    } catch (Throwable) {
        return $raw;
    }

    $seconds = max(0, $now->getTimestamp() - $created->getTimestamp());

    if ($seconds < 60) {
        return 'Just now';
    }

    $minutes = intdiv($seconds, 60);

    if ($minutes < 60) {
        return $minutes . ' min ago';
    }

    $hours = intdiv($minutes, 60);

    if ($hours < 24) {
        return $hours . ' hr ago';
    }

    $days = intdiv($hours, 24);

    if ($days < 7) {
        return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
    }

    return $created->format('M j, Y');
}

function admin_notification_clean_text(mixed $value, int $maxLength): string
{
    $value = trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');

    return mb_substr($value, 0, $maxLength);
}

function admin_notification_clean_multiline(mixed $value, int $maxLength): string
{
    $value = trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
    $value = preg_replace('/\n{3,}/', "\n\n", $value) ?? $value;

    return mb_substr($value, 0, $maxLength);
}

function admin_notification_clean_url(mixed $value): string
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    $parts = parse_url($value);

    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return '';
    }

    return $value;
}
