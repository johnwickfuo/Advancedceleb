<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid ticket ID.";
    exit;
}

$id = (int)$_GET['id'];

// Fetch ticket details
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) {
    echo "Ticket not found.";
    exit;
}

// Generate the QR Code URL
$qr_payload = $t['ticket_id'] . "|TEMPLATE|" . $t['event_name'];
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_payload);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Print Ticket - <?php echo htmlspecialchars($t['ticket_id']); ?></title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: 'Montserrat', sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .ticket-container {
            max-width: 800px;
            margin: 40px auto;
            border: 2px dashed #999;
            border-radius: 12px;
            padding: 0;
            background: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .ticket-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #c29b57;
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
        }
        .ticket-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
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
        .ticket-bg-watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../assets/img/ticket_watermark.svg') center center / cover no-repeat;
            pointer-events: none;
            z-index: 1;
            opacity: 0.95;
        }
        .ticket-header {
            position: relative;
            z-index: 2;
        }
        .ticket-body {
            position: relative;
            z-index: 2;
        }
        .no-print {
            text-align: center;
            margin-top: 30px;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .ticket-container {
                margin: 15px auto;
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
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                background: #fff !important;
            }
            .ticket-container {
                margin: 0;
                border: 2px dashed #c29b57 !important;
                box-shadow: none;
                background: #fff !important;
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
                background: url('../assets/img/ticket_watermark.svg') center center / cover no-repeat !important;
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
        }
    </style>
</head>
<body>

    <div class="ticket-container">
        <!-- Watermark Background for Display and Print -->
        <div class="ticket-bg-watermark"></div>

        <!-- Header -->
        <div class="ticket-header">
            <div class="ticket-brand">
                VIP<span>Booking</span>
            </div>
            <div class="ticket-id-badge">
                TICKET: <?php echo htmlspecialchars($t['ticket_id']); ?>
            </div>
        </div>
        
        <!-- Body -->
        <div class="ticket-body">
            <img src="../<?php echo htmlspecialchars($t['image_path']); ?>" alt="Event Logo" class="ticket-img">
            
            <div class="ticket-info">
                <div class="category-badge"><?php echo htmlspecialchars($t['category']); ?></div>
                <div class="event-name"><?php echo htmlspecialchars($t['event_name']); ?></div>
                
                <div class="ticket-details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Date & Time</span>
                        <span class="detail-value"><?php echo htmlspecialchars($t['event_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Venue</span>
                        <span class="detail-value"><?php echo htmlspecialchars($t['venue']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Admission Price</span>
                        <span class="detail-value text-success"><?php echo format_currency($t['price']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Security Code</span>
                        <span class="detail-value"><?php echo htmlspecialchars($t['ticket_id']); ?></span>
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
            <span>Powered by VIP Booking Concierge Platform</span>
            <strong>Status: <?php echo htmlspecialchars($t['status']); ?></strong>
        </div>
    </div>

    <!-- Print control -->
    <div class="no-print">
        <button onclick="window.print();" class="btn btn-dark px-5 py-2 fw-bold rounded-pill"><i class="bi bi-printer me-2"></i>Print Ticket</button>
    </div>

    <script>
        // Auto-open print dialog
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
