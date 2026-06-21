CREATE TABLE IF NOT EXISTS admin_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(80) NOT NULL,
    severity ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
    title VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    action_label VARCHAR(80) NULL,
    action_url VARCHAR(500) NULL,
    source_type VARCHAR(80) NULL,
    source_id BIGINT UNSIGNED NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY admin_notifications_source_unique (source_type, source_id, type),
    KEY admin_notifications_created_index (created_at),
    KEY admin_notifications_type_index (type, created_at),
    KEY admin_notifications_source_index (source_type, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notification_reads (
    notification_id BIGINT UNSIGNED NOT NULL,
    admin_user_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id, admin_user_id),
    KEY admin_notification_reads_admin_index (admin_user_id, read_at),
    CONSTRAINT admin_notification_reads_notification_foreign
        FOREIGN KEY (notification_id) REFERENCES admin_notifications(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT admin_notification_reads_admin_foreign
        FOREIGN KEY (admin_user_id) REFERENCES admin_users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO admin_notifications
    (type, severity, title, body, action_label, action_url, source_type, source_id, created_at)
SELECT
    'booking_pending',
    'warning',
    'New meeting request',
    CONCAT(visitor_name, ' requested a meeting slot and is waiting for confirmation.'),
    'Open booking',
    '/sanchalak/bookings/',
    'meeting_booking',
    id,
    created_at
FROM meeting_bookings
WHERE status = 'pending';

INSERT IGNORE INTO admin_notifications
    (type, severity, title, body, action_label, action_url, source_type, source_id, created_at)
SELECT
    'recommendation_pending',
    'info',
    'New recommendation submitted',
    CONCAT(name, ' submitted a recommendation for review.'),
    'Review submission',
    '/sanchalak/recommendations/',
    'recommendation_submission',
    id,
    created_at
FROM recommendation_submissions
WHERE status = 'pending';
