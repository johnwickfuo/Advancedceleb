# Cameo video feature

## Deploy to existing site

1. Back up the live database before updating PHP files.
2. If you ALREADY ran the two direct SQL commands that add cameo_price,
   cameo_enabled, booking_type, cameo_script, delivery_email and
   delivery_whatsapp, **do not run them again**. No additional SQL is required
   for the always-visible cameo request buttons.
3. Deploy the updated PHP files from feature/cameo-video-requests.
4. The 'Request Cameo Video' button is present for every celebrity, with no
   availability toggle. In Admin > Celebrities, set a positive individual
   **Cameo Video Price** before that celebrity can accept a paid cameo order.
   A missing price shows 'Price not yet set' and blocks checkout; the ordinary
   event booking price is never silently reused as a cameo price.
5. In Admin > Settings, configure at least one existing payment method.

For a database that has NOT yet had the cameo columns added, use the one-time
statements documented at database/migrations/20261003_add_cameo.sql, or enter
those SQL statements directly in phpMyAdmin. Never import a blank starter
database over the existing celebrity.sql data.

## Customer flow

- The 'Request Cameo Video' link appears below Book Celebrity for all talent on
  the homepage, roster and main booking page.
- The form displays every celebrity and their admin-set cameo price if present.
  Unpriced talent are listed, but cannot proceed to payment until priced.
- The user supplies their requested spoken words (maximum 5,000 characters),
  name, delivery email and international WhatsApp number.
- Server-side validation retrieves the current positive cameo_price. It creates
  a CAM- reference in bookings with booking_type='cameo' and redirects to
  payment.php?ref=CAM-... using the existing bank, crypto and gift card proof
  submission flows.
- A submitted proof is not proof of an automatically verified payment.
- Admin > Cameo Requests displays the exact script, delivery email and
  WhatsApp, can update the order, and may mark it Delivered after manually
  sending the video to the supplied contacts.

## Compatibility

- Existing bookings default to booking_type='event'.
- The legacy cameo_enabled column is no longer used to hide talent or prevent
  checkout. Admin saves new/edited celebrities with its value set to 1.
  You do not need another migration to show the button for existing celebrities.
- The event_date field remains required in the existing bookings table. Cameo
  orders use their creation date for this compatibility field.
- config.php, DB passwords, and the production database are unchanged by the
  GitHub branch. Verify the deployed PHP syntax and payment flow on your server.
