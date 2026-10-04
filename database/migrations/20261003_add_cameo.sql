-- Advancedceleb: add cameo video ordering to the EXISTING celebrity.sql schema.
-- Run ONCE in phpMyAdmin against the site's current database, after taking a backup.
-- Does not DROP tables or modify existing booking/payment records.
-- Existing celebrities cannot receive cameo requests until an admin sets a
-- positive cameo_price and explicitly enables cameo_enabled.

ALTER TABLE celebrities
    ADD COLUMN cameo_price DECIMAL(10,2) DEFAULT NULL AFTER booking_price,
    ADD COLUMN cameo_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER cameo_price;

ALTER TABLE bookings
    ADD COLUMN booking_type VARCHAR(20) NOT NULL DEFAULT 'event' AFTER booking_reference,
    ADD COLUMN cameo_script TEXT NULL AFTER event_details,
    ADD COLUMN delivery_email VARCHAR(255) DEFAULT NULL AFTER cameo_script,
    ADD COLUMN delivery_whatsapp VARCHAR(50) DEFAULT NULL AFTER delivery_email,
    ADD KEY idx_bookings_booking_type (booking_type);

-- Existing records receive booking_type='event' from the column default.
-- Cameo purchases reuse bookings.id and payment_proofs.booking_id, so the
-- existing payment-proof submission and admin verification flow is preserved.
