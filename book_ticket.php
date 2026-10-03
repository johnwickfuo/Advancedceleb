<?php
require_once 'config.php';
require_once 'get_setting.php';
require_once 'include/email_core_functions.php';

if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tickets.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch ticket details
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$t || $t['status'] !== 'Available' || $t['available_qty'] <= 0) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Unavailable',
        'text' => 'This ticket is currently not available for booking.'
    ];
    header("Location: tickets.php");
    exit;
}

$error = '';
$success = '';
$booking_ref = '';

// Helper function to generate unique booking reference
function generateUniqueBookingRef($pdo) {
    $exists = true;
    $ref = '';
    while ($exists) {
        $ref = 'TKB-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket_bookings WHERE booking_reference = ?");
        $stmt->execute([$ref]);
        $exists = ($stmt->fetchColumn() > 0);
    }
    return $ref;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $user_phone = trim($_POST['user_phone']);
    
    if (empty($user_name) || empty($user_email) || empty($user_phone)) {
        $error = "Please fill in all details.";
    } else {
        // Generate booking reference
        $booking_ref = generateUniqueBookingRef($pdo);
        
        try {
            $amount = (float)($t['price'] ?? 0);
            $stmt_insert = $pdo->prepare("INSERT INTO ticket_bookings (booking_reference, ticket_id, buyer_name, buyer_email, buyer_phone, user_name, user_email, user_phone, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt_insert->execute([$booking_ref, $id, $user_name, $user_email, $user_phone, $user_name, $user_email, $user_phone, $amount]);
            
            // Set session alert
            $_SESSION['alert'] = [
                'type' => 'success',
                'title' => 'Booking Submitted! 🎉',
                'text' => 'Your ticket booking request has been submitted successfully and is awaiting review.'
            ];
            
            // Send email confirmation
            $subject = "Ticket Booking Received - Ref: " . $booking_ref;
            $body = "
                <h2 style='color: #c29b57; margin-top: 0;'>Ticket Booking Submitted</h2>
                <p>Dear {$user_name},</p>
                <p>Thank you for booking tickets with us. Your request for <strong>{$t['event_name']}</strong> has been received and is currently **Pending Approval** by our admin team.</p>
                <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px; margin-bottom: 20px;'>
                    <tr><td width='150'><strong>Booking Reference:</strong></td><td>{$booking_ref}</td></tr>
                    <tr><td><strong>Event Name:</strong></td><td>{$t['event_name']}</td></tr>
                    <tr><td><strong>Category:</strong></td><td>{$t['category']}</td></tr>
                    <tr><td><strong>Venue:</strong></td><td>{$t['venue']}</td></tr>
                    <tr><td><strong>Date & Time:</strong></td><td>{$t['event_date']}</td></tr>
                    <tr><td><strong>Total Due:</strong></td><td>" . format_currency($t['price']) . "</td></tr>
                </table>
                <p>Once approved, you will receive another email with access to your premium printable ticket containing its unique QR code.</p>
            ";
            
            $smtp_config_missing = false;
            try {
                send_email_notification($site_settings, $user_email, $subject, $body, $smtp_config_missing);
            } catch (\Throwable $e) {
                error_log("Failed to send ticket booking email: " . $e->getMessage());
            }
            
            header("Location: my_tickets.php?ref=" . urlencode($booking_ref));
            exit;
            
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Ticket | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .book-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .book-hero::before {
            display: none !important;
        }
        .ticket-summary-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(194, 155, 87, 0.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .ticket-summary-header {
            background: #0f172a;
            color: #fff;
            padding: 20px;
            border-bottom: 3px solid #c29b57;
        }
        .ticket-summary-header h4 {
            color: #fff !important;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            line-height: 1.4;
        }
        .form-premium-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            padding: 30px;
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Premium Hero Section -->
    <section class="book-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Ticket Registration</p>
            <h1>Confirm Your Booking</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Secure your exclusive access pass for <strong><?php echo htmlspecialchars($t['event_name']); ?></strong>. Complete your registration below to generate your pass.
            </p>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <!-- Left: Booking Form -->
                <div class="col-lg-7">
                    <div class="form-premium-card">
                        <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; color: #0f172a; margin-bottom: 25px;">Registrant Information</h3>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger shadow-sm mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="book_ticket.php?id=<?php echo $id; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Full Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" name="user_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="user_email" class="form-control" required>
                                </div>
                                <small class="text-muted">Your secure printable ticket link will be sent to this email.</small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark">Phone Number *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="user_phone" class="form-control" required>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-gold btn-lg fw-bold rounded-pill text-uppercase py-3" style="font-size: 1rem; letter-spacing: 1px;">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right: Ticket Info Summary -->
                <div class="col-lg-5">
                    <div class="ticket-summary-card">
                        <div class="ticket-summary-header text-center">
                            <span class="badge bg-warning text-dark fw-bold mb-2"><?php echo htmlspecialchars($t['category']); ?></span>
                            <h4 class="fw-bold mb-0" style="font-family: 'Montserrat', sans-serif;"><?php echo htmlspecialchars($t['event_name']); ?></h4>
                        </div>
                        <img src="<?php echo htmlspecialchars(!empty($t['image_path']) ? $t['image_path'] : 'assets/img/avater.jpg'); ?>" alt="Event image" class="img-fluid w-100" style="height: 200px; object-fit: cover; object-position: center top;" onerror="this.src='assets/img/avater.jpg';">
                        <div class="p-4">
                            <table class="table table-borderless mb-0 small">
                                <tr>
                                    <td class="text-muted" width="120"><i class="bi bi-calendar-event me-2"></i>Date & Time:</td>
                                    <td class="fw-bold text-dark text-end"><?php echo htmlspecialchars($t['event_date']); ?></td>
                                    <hr class="my-1">
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="bi bi-geo-alt me-2"></i>Venue / Location:</td>
                                    <td class="fw-bold text-dark text-end"><?php echo htmlspecialchars($t['venue']); ?></td>
                                    <hr class="my-1">
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="bi bi-wallet2 me-2"></i>Admission Price:</td>
                                    <td class="fw-bold text-gold text-end fs-5"><?php echo format_currency($t['price']); ?></td>
                                    <hr class="my-1">
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="bi bi-shield-check me-2"></i>Availability:</td>
                                    <td class="fw-bold text-success text-end"><?php echo $t['available_qty']; ?> Remaining</td>
                                </tr>
                            </table>
                            <div class="mt-4 p-3 bg-light rounded-3 small text-muted">
                                <h6><strong>Booking Terms & Policies:</strong></h6>
                                <p class="mb-0">Ticket booking submissions are subject to manual verification. Once the admin approves, an access link will be emailed to you immediately.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
