-- Photo galleries for cohorts and events.
--
-- One shared table drives both modules: a cohort/event can now have a video,
-- a photo gallery, both, or neither. Files live under
--   public/uploads/cohorts/gallery/{cohort_id}/
--   public/uploads/events/gallery/{event_id}/
-- which the deploy pipeline preserves (public/uploads is excluded from the
-- rsync --delete and symlinked into public_html).
CREATE TABLE IF NOT EXISTS gallery_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_type ENUM('cohort', 'event') NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    caption VARCHAR(255) NULL,
    alt_text VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY gallery_photos_owner_index (owner_type, owner_id, sort_order, id),
    KEY gallery_photos_created_by_index (created_by),
    KEY gallery_photos_updated_by_index (updated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
