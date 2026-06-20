CREATE TABLE IF NOT EXISTS meeting_day_locks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lock_date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY meeting_day_locks_date_unique (lock_date),
    KEY meeting_day_locks_created_by_index (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
