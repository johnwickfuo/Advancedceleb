<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_GET['ref']) || empty($_GET['ref'])) {
    header("Location: my_tickets.php");
    exit;
}

$ref = trim($_GET['ref']);

// Fetch booking details
try {
    $stmt = $pdo->prepare("
        SELECT tb.*, t.event_name, t.price, t.event_date, t.venue, t.image_path, t.ticket_id as t_id, t.category
        FROM ticket_bookings tb 
        JOIN tickets t ON tb.ticket_id = t.id 
        WHERE tb.booking_reference = ?
    ");
    $stmt->execute([$ref]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $booking = null;
}

if (!$booking || $booking['status'] !== 'Approved') {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Access Denied',
        'text' => 'This ticket booking is either not approved yet or does not exist.'
    ];
    header("Location: my_tickets.php");
    exit;
}

// Generate the QR Code URL
$qr_payload = $booking['booking_reference'] . "|" . $booking['t_id'] . "|" . $booking['user_name'];
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_payload);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Ticket - <?php echo htmlspecialchars($booking['booking_reference']); ?></title>
    <?php include "head.php"; ?>
    <style>
        .ticket-view-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .ticket-view-hero::before {
            display: none !important;
        }
        .ticket-outer {
            max-width: 800px;
            margin: 40px auto;
            border: 2px dashed #c29b57;
            border-radius: 16px;
            padding: 0;
            background: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .ticket-bg-watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/img/ticket_watermark.svg') center center / cover no-repeat;
            pointer-events: none;
            z-index: 1;
            opacity: 0.95;
        }
        .ticket-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #c29b57;
            position: relative;
            z-index: 2;
        }
        .ticket-brand {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .ticket-brand span {
            color: #c29b57;
        }
        .ticket-id-badge {
            background-color: rgba(194, 155, 87, 0.2);
            border: 1px solid #c29b57;
            color: #c29b57;
            padding: 6px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 1px;
        }
        .ticket-body {
            padding: 30px;
            display: flex;
            gap: 30px;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .ticket-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            object-position: center top;
            border-radius: 10px;
            border: 3px solid #c29b57;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .ticket-info {
            flex-grow: 1;
        }
        .event-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-family: 'Playfair Display', serif;
        }
        .category-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            margin-bottom: 15px;
        }
        .ticket-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 0.85rem;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 0.65rem;
            letter-spacing: 0.5px;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 700;
        }
        .ticket-qr-section {
            border-left: 2px dashed #e2e8f0;
            padding-left: 30px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .qr-code {
            width: 130px;
            height: 130px;
            margin-bottom: 8px;
        }
        .qr-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ticket-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 12px 30px;
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .ticket-outer {
                margin: 25px auto;
                border-radius: 12px;
            }
            .ticket-header {
                padding: 16px 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .ticket-id-badge {
                font-size: 0.85rem;
                padding: 4px 10px;
            }
            .ticket-body {
                padding: 20px;
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            .ticket-img {
                width: 130px;
                height: 130px;
                margin: 0 auto;
            }
            .ticket-info {
                width: 100%;
            }
            .event-name {
                font-size: 1.35rem;
            }
            .ticket-details-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                text-align: left;
            }
            .ticket-qr-section {
                border-left: none;
                border-top: 2px dashed #e2e8f0;
                padding-left: 0;
                padding-top: 20px;
                width: 100%;
            }
            .ticket-footer {
                padding: 14px 20px;
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .ticket-view-hero {
                padding: 5.5rem 1rem 2.5rem;
            }
            .ticket-view-hero h1 {
                font-size: 2.2rem;
            }
            .ticket-details-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .detail-item {
                align-items: center;
            }
        }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            body * {
                visibility: hidden;
            }
            .ticket-outer, .ticket-outer * {
                visibility: visible;
            }
            .ticket-outer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                border: 2px dashed #c29b57 !important;
                box-shadow: none;
                background-color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .ticket-bg-watermark {
                display: block !important;
                visibility: visible !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: url('assets/img/ticket_watermark.svg') center center / cover no-repeat !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                opacity: 0.95 !important;
                z-index: 1 !important;
            }
            .ticket-header {
                background: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: #fff !important;
                border-bottom: 4px solid #c29b57 !important;
            }
            .ticket-body, .ticket-footer {
                position: relative !important;
                z-index: 2 !important;
            }
            nav, footer, .ticket-view-hero, .no-print-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Premium Hero Section -->
    <section class="ticket-view-hero no-print-btn">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Access Pass</p>
            <h1>Your Premium Ticket</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Below is your unique, secure ticket pass. Print this page or save it on your mobile device to present at the event entrance.
            </p>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            
            <div class="ticket-outer">
                <!-- Watermark Background for Display and Print -->
                <div class="ticket-bg-watermark"></div>

                <!-- Header -->
                <div class="ticket-header">
                    <div class="ticket-brand">
                        VIP<span>Booking</span>
                    </div>
                    <div class="ticket-id-badge">
                        TICKET ID: <?php echo htmlspecialchars($booking['t_id']); ?>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="ticket-body">
                    <img src="<?php echo htmlspecialchars(!empty($booking['image_path']) ? $booking['image_path'] : 'assets/img/avater.jpg'); ?>" alt="Event Logo" class="ticket-img" onerror="this.src='assets/img/avater.jpg';">
                    
                    <div class="ticket-info">
                        <div class="category-badge"><?php echo htmlspecialchars($booking['category']); ?></div>
                        <div class="event-name"><?php echo htmlspecialchars($booking['event_name']); ?></div>
                        
                        <div class="ticket-details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Date & Time</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['event_date']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Venue</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['venue']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Attendee</span>
                                <span class="detail-value text-dark"><?php echo htmlspecialchars($booking['user_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Booking Reference</span>
                                <span class="detail-value text-dark"><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Admission Price</span>
                                <span class="detail-value text-success"><?php echo format_currency($booking['price']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR code -->
                    <div class="ticket-qr-section">
                        <img src="<?php echo $qr_code_url; ?>" alt="QR Verification" class="qr-code">
                        <span class="qr-label">Scan to Verify</span>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="ticket-footer">
                    <span>Presented by VIP Booking Concierge Platform</span>
                    <strong class="text-success"><i class="bi bi-patch-check-fill me-1"></i>VERIFIED PASS</strong>
                </div>
            </div>

            <!-- Print Control -->
            <div class="text-center mt-4 no-print-btn">
                <button onclick="window.print();" class="btn btn-gold px-5 py-3 fw-bold rounded-pill shadow-sm"><i class="bi bi-printer me-2"></i>PRINT YOUR TICKET</button>
            </div>
            
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
