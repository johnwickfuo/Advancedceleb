<?php
session_start();
require_once '../config.php';
require_once '../get_setting.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$shipment_id = $_GET['id'] ?? null;
if (!$shipment_id) {
    die("Error: Shipment ID is required.");
}

$site_settings = get_site_settings($pdo);

try {
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->execute([$shipment_id]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shipment) {
        die("Error: Shipment not found.");
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$logo_path = !empty($site_settings['site_logo']) ? '../assets/images/' . $site_settings['site_logo'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Packing Slip - #<?php echo htmlspecialchars($shipment['tracking_number']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
        :root {
            --slip-primary: #B00000;
            --slip-secondary: #212529;
            --slip-gray: #f8f9fa;
            --slip-text: #343a40;
        }

        body {
            background-color: #e9ecef;
            font-family: 'Inter', sans-serif;
            color: var(--slip-text);
            margin: 0;
            padding: 40px 0;
        }

        .slip-container {
            background: white;
            max-width: 850px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.15);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border-top: 8px solid var(--slip-primary);
        }

        /* Subtle Security Pattern */
        .slip-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(rgba(0,0,0,0.03) 1px, transparent 1px),
                radial-gradient(rgba(0,0,0,0.03) 1px, transparent 1px);
            background-size: 24px 24px;
            background-position: 0 0, 12px 12px;
            pointer-events: none;
            z-index: 1;
        }

        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid var(--slip-gray);
            padding-bottom: 20px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .logo-area img {
            max-height: 45px;
            width: auto;
            margin-bottom: 10px;
            filter: brightness(0.1);
        }

        .logo-area h2 {
            color: var(--slip-primary);
        }

        .slip-title {
            text-align: right;
        }

        .slip-title h1 {
            color: var(--slip-primary);
            font-weight: 800;
            font-size: 2.8rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: -1px;
            line-height: 0.85;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .section-title {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 2px;
            color: #adb5bd;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .info-box p {
            margin-bottom: 5px;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .info-box strong {
            display: block;
            font-size: 1.05rem;
            color: var(--slip-secondary);
        }

        .item-table {
            width: 100%;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .item-table th {
            background: var(--slip-secondary);
            color: white;
            padding: 12px 15px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .item-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .item-table td.desc {
            width: 50%;
        }

        .notes-area {
            background: #fffdf6;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .notes-title {
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #856404;
            margin-bottom: 5px;
        }

        .notes-content {
            font-size: 0.85rem;
            color: #664d03;
            line-height: 1.5;
            margin: 0;
        }

        .official-seal {
            position: absolute;
            bottom: 140px;
            left: 80px;
            width: 150px;
            height: 150px;
            border: 4px double var(--slip-primary);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0.15;
            transform: rotate(-15deg);
            pointer-events: none;
            z-index: 10;
            color: var(--slip-primary);
            text-align: center;
            padding: 12px;
            mix-blend-mode: multiply;
        }

        .seal-inner {
            border: 1px solid var(--slip-primary);
            border-radius: 50%;
            width: 90%;
            height: 90%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .seal-text-top {
            font-size: 0.55rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
            opacity: 0.8;
        }

        .seal-title {
            font-size: 0.85rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
            margin: 4px 0;
            text-align: center;
        }

        .seal-status {
            font-size: 0.6rem;
            border-top: 1px solid var(--slip-primary);
            border-bottom: 1px solid var(--slip-primary);
            padding: 2px 8px;
            margin: 4px 0;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .slip-signature-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 50px;
            padding-top: 20px;
            position: relative;
            z-index: 2;
        }

        .sig-box {
            text-align: center;
            width: 250px;
        }

        .sig-line {
            border-bottom: 2px solid var(--slip-secondary);
            margin-bottom: 10px;
            height: 45px;
            position: relative;
        }
        
        .sig-line::after {
            content: 'AUTHORIZED SIGNATURE';
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.3;
        }

        .receiver-sig-line {
            border-bottom: 2px dashed #adb5bd;
            margin-bottom: 10px;
            height: 45px;
            position: relative;
        }

        .receiver-sig-line::after {
            content: 'RECEIVER SIGNATURE';
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.3;
        }

        .sig-text {
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--slip-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .date-display {
            font-family: 'JetBrains Mono', monospace;
            color: var(--slip-primary);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .slip-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--slip-gray);
            text-align: center;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .btn-print-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .tracking-label {
            font-family: 'JetBrains Mono', monospace;
            background: #eee;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 10px;
            font-weight: 700;
            color: var(--slip-primary);
            display: inline-block;
        }

        /* Watermark */
        .slip-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 5rem;
            color: rgba(0, 0, 0, 0.02);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 12px;
            user-select: none;
            width: 100%;
            text-align: center;
        }

        @media (max-width: 768px) {
            body { 
                padding: 10px; 
                background-color: #fff;
            }
            .slip-container { 
                padding: 20px; 
                box-shadow: none;
                border-radius: 0;
            }
            .slip-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }
            .slip-title {
                text-align: center;
                width: 100%;
            }
            .slip-title h1 {
                font-size: 2.2rem;
            }
            .details-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .btn-print-fixed {
                position: static;
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                justify-content: center;
            }
            .btn-print-fixed .btn {
                flex: 1;
                font-size: 0.8rem;
                padding: 10px 5px !important;
            }
            .item-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .slip-signature-area {
                flex-direction: column;
                gap: 50px;
                align-items: center;
            }
            .sig-box {
                width: 100%;
            }
            .official-seal {
                position: relative;
                bottom: 0;
                left: 0;
                margin: 40px auto;
                opacity: 0.3;
            }
            .slip-watermark {
                font-size: 3.5rem;
                letter-spacing: 5px;
            }
        }

        @media print {
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important;
            }
            .slip-container { 
                box-shadow: none !important; 
                padding: 30px !important; 
                max-width: 100% !important; 
                width: 100% !important;
                border: none !important;
                margin: 0 !important;
            }
            .btn-print-fixed { display: none !important; }
            .slip-header { margin-bottom: 20px !important; }
            .details-grid { margin-bottom: 30px !important; }
            .item-table { margin-bottom: 20px !important; }
            .slip-signature-area { margin-top: 50px !important; }
            .slip-footer { margin-top: 30px !important; }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="btn-print-fixed">
        <button onclick="window.print()" class="btn btn-dark shadow-lg px-4 py-2 fw-bold">
            <i class="bi bi-printer-fill me-2"></i> PRINT SLIP
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary bg-white shadow-sm px-4 py-2 fw-bold ms-2">
            CLOSE
        </button>
    </div>

    <div class="slip-container shadow-sm">
        <div class="slip-watermark">PACKING SLIP</div>
        
        <div class="slip-header">
            <div class="logo-area">
                <?php if ($logo_path): ?>
                    <img src="<?php echo $logo_path; ?>" alt="Company Logo">
                <?php else: ?>
                    <h2 class="fw-bold m-0"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI'); ?></h2>
                <?php endif; ?>
                <div class="tracking-label">Tracking Number: #<?php echo htmlspecialchars($shipment['tracking_number']); ?></div>
            </div>
            
            <div class="slip-title">
                <h1>PACKING SLIP</h1>
                <p class="text-muted m-0">Date: <?php echo date('F j, Y', strtotime($shipment['created_at'])); ?></p>
                <p class="text-muted m-0 small">Service: <?php echo htmlspecialchars($shipment['service_type']); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="info-box">
                <div class="section-title">Shipper / Sender</div>
                <strong><?php echo htmlspecialchars($shipment['sender_name']); ?></strong>
                <p>
                    <?php echo nl2br(htmlspecialchars($shipment['sender_address'])); ?><br>
                    Phone: <?php echo htmlspecialchars($shipment['sender_phone']); ?>
                </p>
            </div>
            
            <div class="info-box">
                <div class="section-title">Consignee / Receiver</div>
                <strong><?php echo htmlspecialchars($shipment['receiver_name']); ?></strong>
                <p>
                    <?php echo nl2br(htmlspecialchars($shipment['receiver_address'])); ?><br>
                    Email: <?php echo htmlspecialchars($shipment['receiver_email'] ?? 'N/A'); ?><br>
                    Phone: <?php echo htmlspecialchars($shipment['receiver_phone']); ?>
                </p>
            </div>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th class="text-start">Description of Goods</th>
                    <th class="text-center">Dimensions</th>
                    <th class="text-center">Weight</th>
                    <th class="text-center">Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="desc">
                        <strong><?php echo htmlspecialchars($shipment['package_description']); ?></strong>
                        <div class="text-muted small mt-1">Status: <?php echo htmlspecialchars($shipment['status']); ?></div>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars($shipment['dimensions']); ?> cm</td>
                    <td class="text-center"><?php echo htmlspecialchars($shipment['weight']); ?> kg</td>
                    <td class="text-center fw-bold"><?php echo htmlspecialchars($shipment['quantity']); ?> pcs</td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($shipment['handling_instructions'])): ?>
            <div class="notes-area">
                <div class="notes-title"><i class="bi bi-exclamation-triangle-fill me-1"></i> Special Handling Instructions / Notes</div>
                <p class="notes-content"><?php echo nl2br(htmlspecialchars($shipment['handling_instructions'])); ?></p>
            </div>
        <?php else: ?>
            <div class="notes-area" style="background-color: #f8fafc; border-left-color: #cbd5e1;">
                <div class="notes-title" style="color: #475569;"><i class="bi bi-info-circle-fill me-1"></i> Special Instructions</div>
                <p class="notes-content" style="color: #475569;">Standard cargo handling procedures apply. Keep dry, stack with care, and check integrity of packaging upon receipt.</p>
            </div>
        <?php endif; ?>

        <div class="official-seal">
            <div class="seal-inner">
                <span class="seal-text-top">Official Seal</span>
                <span class="seal-title"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI PARCEL'); ?></span>
                <span class="seal-status">DISPATCHED</span>
                <span class="seal-text-top">Logistics Center</span>
            </div>
        </div>
        
        <div class="slip-signature-area">
            <div class="sig-box">
                <div class="date-display"><?php echo date('Y / m / d'); ?></div>
                <div class="sig-line" style="height: 10px;"></div>
                <div class="sig-text">Date of Issue</div>
            </div>
            
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-text">Authorized Signature</div>
            </div>

            <div class="sig-box">
                <div class="receiver-sig-line"></div>
                <div class="sig-text">Recipient Signature</div>
            </div>
        </div>

        <div class="slip-footer">
            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI PARCEL LOGISTICS'); ?></p>
            <p>Thank you for trusting us with your logistics needs.</p>
        </div>
    </div>

</body>
</html>
