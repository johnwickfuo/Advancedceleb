# Cameo video feature

## Install on the existing database

1. Back up your live database first.
2. In phpMyAdmin select the actual site database (e.g. admin_ibook), then import
   database/migrations/20261003_add_cameo.sql **once**. Do not import a new
   generated starter database over the existing celebrity.sql data.
3. Deploy the PHP changes from this feature branch together with the migration.
   The celebrity manager, public listings and booking admin require the new columns.
4. In Admin > Celebrities, edit an authorized celebrity and set a positive
   **Cameo Video Price**; select **Accept Cameo Video Requests** and save.
   Existing celebrities start with cameo availability disabled.
5. In Admin > Settings enable and configure at least one of the payment methods
   already supported by ordinary celebrity bookings.

## Customer flow

- The 'Request Cameo Video' link appears directly below the celebrity booking
  button in the featured celebrities, talent roster and booking page when enabled.
- The cameo form requires celebrity, the exact words/script, customer name,
  delivery email, and WhatsApp number with international country code.
- The server reads the current cameo price from the database, assigns a CAM-
  reference, stores the script and contacts on a bookings record with
  booking_type='cameo', and redirects to payment.php?ref=CAM-...
- The payment options are the SAME bank / crypto / gift-card proof submission
  and admin review as an ordinary celebrity booking. The form prevents creating
  a cameo order if every payment method is turned off.
- A submitted payment proof is NOT automatically a verified payment.
- Admin > Cameo Requests filters cameo orders. Open a request to read the exact
  script and delivery email / WhatsApp, manage status and coordinate delivery.
  Completed video delivery is handled manually through those contacts; the
  feature does not impersonate celebrities, generate their likeness, or claim
  that an unauthorized celebrity has agreed to record a video.

## Notes

- The legacy bookings table requires event_date even for a video order; cameo
  inserts use the order date in that field. The admin shows booking creation
  date (rather than suggesting it is a scheduled event).
- Existing bookings default to booking_type='event' without rewriting records.
- The production config.php and its database credentials are not changed.
- The migration uses ADD COLUMN, so re-running it after success will produce
  duplicate-column errors. It is intentionally a run-once migration.
