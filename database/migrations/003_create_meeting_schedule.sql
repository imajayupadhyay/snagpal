CREATE TABLE IF NOT EXISTS meeting_slots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    notes VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY meeting_slots_unique_slot (slot_date, start_time, end_time),
    KEY meeting_slots_lookup_index (slot_date, status, start_time),
    KEY meeting_slots_created_by_index (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS meeting_bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meeting_slot_id BIGINT UNSIGNED NOT NULL,
    visitor_name VARCHAR(120) NOT NULL,
    visitor_email VARCHAR(190) NOT NULL,
    visitor_phone VARCHAR(40) NULL,
    message TEXT NULL,
    status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    active_slot_id BIGINT UNSIGNED AS (CASE WHEN status = 'confirmed' THEN meeting_slot_id ELSE NULL END) STORED,
    cancelled_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY meeting_bookings_active_slot_unique (active_slot_id),
    KEY meeting_bookings_slot_index (meeting_slot_id),
    KEY meeting_bookings_email_index (visitor_email),
    KEY meeting_bookings_status_index (status),
    CONSTRAINT meeting_bookings_slot_foreign
        FOREIGN KEY (meeting_slot_id) REFERENCES meeting_slots(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
