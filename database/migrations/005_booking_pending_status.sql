-- Move bookings to a pending-first workflow: a new booking starts as
-- "pending" and only becomes "confirmed" when an admin approves it.
-- The generated active_slot_id (which backs the one-booking-per-slot unique
-- key) is widened so a pending booking also reserves its slot.

ALTER TABLE meeting_bookings DROP INDEX meeting_bookings_active_slot_unique;
ALTER TABLE meeting_bookings DROP COLUMN active_slot_id;

ALTER TABLE meeting_bookings
    MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending';

ALTER TABLE meeting_bookings
    ADD COLUMN active_slot_id BIGINT UNSIGNED
        AS (CASE WHEN status IN ('pending', 'confirmed') THEN meeting_slot_id ELSE NULL END) STORED
        AFTER status;

ALTER TABLE meeting_bookings
    ADD UNIQUE KEY meeting_bookings_active_slot_unique (active_slot_id);
