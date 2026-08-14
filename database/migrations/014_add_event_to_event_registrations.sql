ALTER TABLE event_registrations
    ADD COLUMN event_id BIGINT UNSIGNED NULL AFTER id,
    ADD COLUMN event_title VARCHAR(180) NULL AFTER event_id,
    ADD COLUMN event_date_label VARCHAR(120) NULL AFTER event_title,
    ADD KEY event_registrations_event_id_index (event_id);
