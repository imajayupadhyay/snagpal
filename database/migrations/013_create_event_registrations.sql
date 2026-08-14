CREATE TABLE IF NOT EXISTS event_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    source_path VARCHAR(255) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY event_registrations_created_index (created_at),
    KEY event_registrations_email_index (email),
    KEY event_registrations_phone_index (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
