<?php
require_once 'config.php';
require_once 'get_setting.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate Booking Reference
function generateBookingRef() {
    return 'VIP-' . strtoupper(substr(uniqid(), -6));
}

$success_msg = '';
$error_msg = '';

// Handle Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    @set_time_limit(60);
    $celebrity_id = (int)$_POST['celebrity_id'];
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $user_phone = trim($_POST['user_phone']);
    $event_date = trim($_POST['event_date']);
    $event_type = trim($_POST['event_type'] ?? '');
    $event_details = trim($_POST['event_details']);
    
    // Fetch celebrity details
    $stmt = $pdo->prepare("SELECT name, booking_price FROM celebrities WHERE id = ?");
    $stmt->execute([$celebrity_id]);
    $cel = $stmt->fetch();
    
    if ($cel && !empty($user_name) && !empty($user_email) && !empty($event_date)) {
        $amount = $cel['booking_price'];
        $booking_ref = generateBookingRef();
        
        try {
            $sql = "INSERT INTO bookings (booking_reference, celebrity_id, user_name, user_email, user_phone, event_date, event_type, event_details, amount, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$booking_ref, $celebrity_id, $user_name, $user_email, $user_phone, $event_date, $event_type, $event_details, $amount]);
            
            // Send Email Notifications (isolated in try-catch so SMTP issues never prevent booking completion)
            require_once 'include/email_core_functions.php';
            require_once 'get_setting.php';
            $smtp_config_missing = false;
            $admin_email = $site_settings['contact_email'] ?? "booking@vipcelebrity.com";
            
            // 1. Email to Client
            try {
                $client_subject = "Booking Received - Ref: " . $booking_ref;
                $client_body = "
                    <h2 style='color: #e63946; margin-top: 0;'>Booking Received</h2>
                    <p>Dear {$user_name},</p>
                    <p>Your booking request for <strong>{$cel['name']}</strong> has been received and is currently pending review.</p>
                    <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px; margin-bottom: 20px;'>
                        <tr><td width='150'><strong>Booking Ref:</strong></td><td>{$booking_ref}</td></tr>
                        <tr><td><strong>Event Type:</strong></td><td>{$event_type}</td></tr>
                        <tr><td><strong>Event Date:</strong></td><td>{$event_date}</td></tr>
                        <tr><td><strong>Amount:</strong></td><td>" . format_currency($amount) . "</td></tr>
                    </table>
                    <p>We will review your request and contact you shortly with further instructions regarding confirmation and payment.</p>
                ";
                send_email_notification($site_settings, $user_email, $client_subject, $client_body, $smtp_config_missing);
            } catch (\Throwable $e) {
                error_log("Failed to send booking confirmation email to client: " . $e->getMessage());
            }

            // 2. Email to Admin
            try {
                $admin_subject = "[NEW BOOKING] " . $booking_ref . " - " . $user_name;
                $admin_body = "
                    <h2 style='color: #e63946; margin-top: 0;'>New Booking Request</h2>
                    <p>A new booking request has been submitted.</p>
                    <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px;'>
                        <tr><td width='150'><strong>Booking Ref:</strong></td><td>{$booking_ref}</td></tr>
                        <tr><td><strong>Celebrity:</strong></td><td>{$cel['name']}</td></tr>
                        <tr><td><strong>Client Name:</strong></td><td>{$user_name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>{$user_email}</td></tr>
                        <tr><td><strong>Phone:</strong></td><td>{$user_phone}</td></tr>
                        <tr><td><strong>Event Type:</strong></td><td>{$event_type}</td></tr>
                        <tr><td><strong>Event Date:</strong></td><td>{$event_date}</td></tr>
                        <tr><td colspan='2' style='border-top: 1px solid #eef1f3; padding-top: 15px;'><strong>Event Details:</strong><br><br>" . nl2br($event_details) . "</td></tr>
                    </table>
                    <p><a href='{$site_settings['site_url']}/admin/bookings_manager.php' style='display: inline-block; padding: 10px 20px; background-color: #e63946; color: #fff; border-radius: 6px; font-weight: bold;'>View in Admin Dashboard</a></p>
                ";
                send_email_notification($site_settings, $admin_email, $admin_subject, $admin_body, $smtp_config_missing);
            } catch (\Throwable $e) {
                error_log("Failed to send new booking notification email to admin: " . $e->getMessage());
            }

            // Check if any payment options are active
            $has_payment_options = (!empty($bank_enabled) || !empty($crypto_enabled) || !empty($giftcard_enabled));

            if ($has_payment_options) {
                // Set session alert and redirect to payment page
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Celebrity Booked Successfully! 🎉',
                    'text' => 'Your booking request for ' . $cel['name'] . ' has been created. Please proceed with payment below to secure your booking.',
                    'html' => '<div style="text-align: center; padding: 10px 0;">' .
                              '<div style="width: 70px; height: 70px; background: rgba(218, 165, 32, 0.15); color: #daa520; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2.2rem;"><i class="bi bi-patch-check-fill"></i></div>' .
                              '<h4 style="font-family: \'Playfair Display\', serif; font-weight: 700; color: #1e3c72; margin-bottom: 12px;">Booking Created Successfully</h4>' .
                              '<p style="font-size: 1.05rem; color: #333; line-height: 1.6; margin-bottom: 12px;"><strong>' . htmlspecialchars($cel['name']) . '</strong> has been reserved for your event date.</p>' .
                              '<p style="font-size: 0.95rem; color: #555; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 15px;">Please select your preferred payment option below (Bank Transfer, Crypto, or Gift Cards) to submit payment proof.</p>' .
                              '<div style="font-size: 0.9rem; color: #6c757d;">Booking Reference: <strong style="color: #b00000; font-size: 1rem;">' . htmlspecialchars($booking_ref) . '</strong></div>' .
                              '</div>'
                ];
                header("Location: payment.php?ref=" . urlencode($booking_ref));
                exit;
            } else {
                // Set session alert for manual review and redirect to home
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Celebrity Booked Successfully!',
                    'text' => 'The celebrity has been successfully booked and is under review. The user who booked the celebrity will be contacted after review.',
                    'html' => '<div style="text-align: center; padding: 10px 0;">' .
                              '<div style="width: 70px; height: 70px; background: rgba(218, 165, 32, 0.15); color: #daa520; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2.2rem;"><i class="bi bi-patch-check-fill"></i></div>' .
                              '<h4 style="font-family: \'Playfair Display\', serif; font-weight: 700; color: #1e3c72; margin-bottom: 12px;">Booking Request Submitted</h4>' .
                              '<p style="font-size: 1.05rem; color: #333; line-height: 1.6; margin-bottom: 12px;"><strong>' . htmlspecialchars($cel['name']) . '</strong> has been successfully booked and your request is currently <span style="color: #d97706; font-weight: 700;">under review</span>.</p>' .
                              '<p style="font-size: 0.95rem; color: #555; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 15px;">The user who booked the celebrity will be contacted by our VIP concierge team after review.</p>' .
                              '<div style="font-size: 0.9rem; color: #6c757d;">Booking Reference: <strong style="color: #b00000; font-size: 1rem;">' . htmlspecialchars($booking_ref) . '</strong></div>' .
                              '</div>'
                ];
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_msg = "Error creating booking: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

// Fetch all celebrities for dropdown
$celebrities = [];
try {
    $stmt = $pdo->query("SELECT * FROM celebrities ORDER BY name ASC");
    $celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching celebrities: " . $e->getMessage());
}

// Pre-select celebrity if passed via GET
$selected_cel_id = isset($_GET['celebrity_id']) ? (int)$_GET['celebrity_id'] : 0;
$selected_cel_details = null;
if ($selected_cel_id > 0) {
    foreach ($celebrities as $c) {
        if ($c['id'] == $selected_cel_id) {
            $selected_cel_details = $c;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <style>
        .booking-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .booking-hero::before {
            display: none !important;
        }
    </style>
</head>
<body>
    
    <?php include "header.php"; ?>

    <?php if ($selected_cel_details): ?>
    <div class="celebrity-booking-hero" style="background: linear-gradient(135deg, rgba(176,0,0,0.85) 0%, rgba(15,23,42,0.90) 100%), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat; padding: 100px 0 60px; position: relative;">
        <div class="container position-relative" style="z-index: 2;">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-end mb-4 mb-md-0 pe-md-5">
                    <img src="<?php echo !empty($selected_cel_details['profile_picture']) ? htmlspecialchars($selected_cel_details['profile_picture']) : 'assets/img/perf_default.jpg'; ?>" alt="<?php echo htmlspecialchars($selected_cel_details['name']); ?>" class="img-fluid rounded-circle shadow-lg" style="width: 180px; height: 180px; object-fit: cover; border: 5px solid rgba(218, 165, 32, 0.7); box-shadow: 0 15px 30px rgba(0,0,0,0.4) !important;" onerror="this.src='assets/img/perf_default.jpg';">
                </div>
                <div class="col-md-8 text-center text-md-start text-white">
                    <span class="badge text-uppercase d-inline-flex align-items-center" style="background-color: var(--secondary); font-size: 0.85rem; letter-spacing: 2px; padding: 8px 16px; margin-bottom: 20px; font-weight: 700; box-shadow: 0 4px 10px rgba(218, 165, 32, 0.4);"><i class="bi bi-compass me-2"></i>Exclusive Booking</span>
                    <h1 style="font-family: 'Playfair Display', serif; font-size: 3.5rem; font-weight: 700; margin-bottom: 20px; color: #ffffff !important; text-shadow: 2px 2px 8px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($selected_cel_details['name']); ?></h1>
                    <p style="font-size: 1.15rem; opacity: 0.95; max-width: 650px; line-height: 1.7; text-shadow: 1px 1px 4px rgba(0,0,0,0.5); margin: 0 auto 0 0;" class="mx-auto mx-md-0"><?php echo htmlspecialchars($selected_cel_details['description']); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Premium Hero Section -->
    <section class="booking-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Exclusive Access</p>
            <h1>Request a Booking</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Secure world-class performers, speakers, and celebrities for your next private gala or corporate event. Our specialized handlers ensure flawless execution.
            </p>
        </div>
    </section>
    <?php endif; ?>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="booking-card shadow-lg bg-white rounded-4" style="padding: 30px; border: none !important; margin-top: -30px; position: relative; z-index: 10;">
                        <?php if($error_msg): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" class="form-premium">
                            <h3 class="mb-4" style="font-family: 'Playfair Display', serif; font-weight: 700; color: #1e3c72; border-bottom: 2px solid #eef1f3; padding-bottom: 15px;">Booking Details</h3>
                            <div class="row g-4 mb-5">
                                <div class="col-md-12">
                                    <label class="form-label">Select Celebrity</label>
                                    <select name="celebrity_id" class="form-select" required>
                                        <option value="">-- Choose Talent --</option>
                                        <?php foreach($celebrities as $cel): ?>
                                            <option value="<?php echo $cel['id']; ?>" <?php echo ($selected_cel_id == $cel['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cel['name']); ?> (Starts at <?php echo format_currency($cel['booking_price']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Booking Date</label>
                                    <input type="date" name="event_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Event Type</label>
                                    <select name="event_type" class="form-select" required>
                                        <option value="">-- Choose Event Type --</option>
                                        <option value="Corporate Event">Corporate Event</option>
                                        <option value="Birthday Party">Birthday Party</option>
                                        <option value="Wedding">Wedding</option>
                                        <option value="Concert">Concert</option>
                                        <option value="Meet and Greet">Meet and Greet</option>
                                        <option value="Private Performance">Private Performance</option>
                                        <option value="Brand Promotion">Brand Promotion</option>
                                        <option value="Charity Event">Charity Event</option>
                                        <option value="Product Launch">Product Launch</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Event Description / Requirements</label>
                                    <textarea name="event_details" class="form-control" rows="4" placeholder="Tell us about the event format, location, and what you expect from the talent..."></textarea>
                                </div>
                            </div>
                            
                            <h3 class="mb-4 mt-5" style="font-family: 'Playfair Display', serif; font-weight: 700; color: #1e3c72; border-bottom: 2px solid #eef1f3; padding-bottom: 15px;">Your Contact Information</h3>
                            <div class="row g-4 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="user_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="user_email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="user_phone" class="form-control" required>
                                </div>
                            </div>
                            
                            <button type="submit" name="submit_booking" class="btn btn-gold w-100 mt-4 py-3 fs-5" style="font-weight: 700; letter-spacing: 1px; box-shadow: 0 10px 20px rgba(218,165,32,0.3);">Book Celebrity</button>
                        </form>
                        <a href="cameo.php<?php echo $selected_cel_details ? '?celebrity_id=' . (int)$selected_cel_details['id'] : ''; ?>" class="btn btn-outline-danger w-100 mt-3 py-3 fs-5 fw-bold">
                            <i class="bi bi-camera-video me-2"></i>Request Cameo Video
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
