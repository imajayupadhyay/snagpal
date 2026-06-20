CREATE TABLE IF NOT EXISTS site_contents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(80) NOT NULL,
    content_json LONGTEXT NOT NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY site_contents_key_unique (content_key),
    KEY site_contents_updated_by_index (updated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
