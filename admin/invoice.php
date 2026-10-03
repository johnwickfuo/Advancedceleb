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
<title>Invoice - #<?php echo htmlspecialchars($shipment['tracking_number']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
        :root {
            --invoice-primary: #B00000;
            --invoice-secondary: #212529;
            --invoice-gray: #f8f9fa;
            --invoice-text: #343a40;
        }

        body {
            background-color: #e9ecef;
            font-family: 'Inter', sans-serif;
            color: var(--invoice-text);
            margin: 0;
            padding: 40px 0;
        }

        .invoice-container {
            background: white;
            max-width: 850px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.15);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border-top: 8px solid var(--invoice-primary);
        }

        /* Subtle Security Pattern */
        .invoice-container::before {
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

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid var(--invoice-gray);
            padding-bottom: 20px;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .logo-area img {
            max-height: 45px;
            width: auto;
            margin-bottom: 10px;
            /* Filter to ensure white/light logos show up on white BG */
            filter: brightness(0.1);
        }

        .logo-area h2 {
            color: var(--invoice-primary);
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            color: var(--invoice-primary);
            font-weight: 800;
            font-size: 3.5rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: -2px;
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
            font-size: 0.65rem;
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
            color: var(--invoice-secondary);
        }

        .item-table {
            width: 100%;
            margin-bottom: 40px;
        }

        .item-table th {
            background: var(--invoice-secondary);
            color: white;
            padding: 12px 15px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .item-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .item-table td.desc {
            width: 50%;
        }

        .item-table td.amount {
            font-weight: 700;
            text-align: right;
        }

        .summary-area {
            display: flex;
            justify-content: flex-end;
            position: relative;
        }

        .official-seal {
            position: absolute;
            bottom: 100px;
            left: 80px;
            width: 150px;
            height: 150px;
            border: 4px double var(--invoice-primary);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0.2;
            transform: rotate(-15deg);
            pointer-events: none;
            z-index: 10;
            color: var(--invoice-primary);
            text-align: center;
            padding: 12px;
            mix-blend-mode: multiply;
        }

        .seal-inner {
            border: 1px solid var(--invoice-primary);
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
            border-top: 1px solid var(--invoice-primary);
            border-bottom: 1px solid var(--invoice-primary);
            padding: 2px 8px;
            margin: 4px 0;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .invoice-signature-area {
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            margin-top: 40px;
            padding-top: 20px;
            position: relative;
            z-index: 2;
        }

        .sig-box {
            text-align: center;
            width: 250px;
        }

        .sig-line {
            border-bottom: 2px solid var(--invoice-secondary);
            margin-bottom: 10px;
            height: 45px;
            position: relative;
        }
        
        .sig-line::after {
            content: 'SIGNATURE & STAMP';
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
            font-size: 0.9rem;
            font-weight: 800;
            color: var(--invoice-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .date-display {
            font-family: 'JetBrains Mono', monospace;
            color: var(--invoice-primary);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .summary-box {
            width: 300px;
            position: relative;
            z-index: 5;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 0.95rem;
        }

        .summary-row.total {
            background: var(--invoice-primary);
            color: white;
            padding: 15px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 1.25rem;
            margin-top: 20px;
        }

        .invoice-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--invoice-gray);
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
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 10px;
            display: inline-block;
        }

        /* Watermark */
        .invoice-watermark {
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 6rem;
            color: rgba(0, 0, 0, 0.03);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 15px;
            user-select: none;
            width: 100%;
            text-align: center;
        }

        @media (max-width: 768px) {
            body { 
                padding: 10px; 
                background-color: #fff;
            }
            .invoice-container { 
                padding: 20px; 
                box-shadow: none;
                border-radius: 0;
            }
            .invoice-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }
            .invoice-title {
                text-align: center;
                width: 100%;
            }
            .invoice-title h1 {
                font-size: 2.5rem;
            }
            .details-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .summary-box {
                width: 100%;
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
            .invoice-signature-area {
                justify-content: center;
            }
            .sig-box {
                width: 100%;
            }
            .official-seal {
                position: relative;
                bottom: 0;
                left: 0;
                margin: 40px auto;
                opacity: 0.4;
            }
            .invoice-watermark {
                font-size: 4rem;
                letter-spacing: 5px;
            }
        }

        @media print {
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important;
            }
            .invoice-container { 
                box-shadow: none !important; 
                padding: 30px !important; 
                max-width: 100% !important; 
                width: 100% !important;
                border: none !important;
                margin: 0 !important;
            }
            .btn-print-fixed { display: none !important; }
            .invoice-header { margin-bottom: 20px !important; }
            .details-grid { margin-bottom: 30px !important; }
            .item-table { margin-bottom: 20px !important; }
            .invoice-signature-area { margin-top: 50px !important; }
            .invoice-footer { margin-top: 30px !important; }
            
            /* Ensure background colors and patterns print */
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
            <i class="bi bi-printer-fill me-2"></i> PRINT DOCUMENT
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary bg-white shadow-sm px-4 py-2 fw-bold ms-2">
            CLOSE
        </button>
    </div>

    <div class="invoice-container shadow-sm">
        <div class="invoice-watermark"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI'); ?></div>
        <div class="invoice-header">
            <div class="logo-area">
                <?php if ($logo_path): ?>
                    <img src="<?php echo $logo_path; ?>" alt="Company Logo">
                <?php else: ?>
                    <h2 class="fw-bold m-0"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI'); ?></h2>
                <?php endif; ?>
                <div class="tracking-label">Tracking: #<?php echo htmlspecialchars($shipment['tracking_number']); ?></div>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p class="text-muted m-0">Date: <?php echo date('F j, Y', strtotime($shipment['created_at'])); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="info-box">
                <div class="section-title">Sent From</div>
                <strong><?php echo htmlspecialchars($shipment['sender_name']); ?></strong>
                <p>
                    <?php echo nl2br(htmlspecialchars($shipment['sender_address'])); ?><br>
                    Phone: <?php echo htmlspecialchars($shipment['sender_phone']); ?>
                </p>
            </div>
            <div class="info-box">
                <div class="section-title">Invoiced To</div>
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
                    <th>Service Type</th>
                    <th>Qty</th>
                    <th>Weight</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="desc">
                        <strong>Package Contents:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($shipment['package_description']); ?></span><br>
                        <small>Dimensions: <?php echo htmlspecialchars($shipment['dimensions']); ?> cm</small>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars($shipment['service_type']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($shipment['quantity']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($shipment['weight']); ?> kg</td>
                    <td class="amount"><?php echo $shipment['currency'] ?? 'USD'; ?> <?php echo number_format($shipment['amount'] ?? 0, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="official-seal">
            <div class="seal-inner">
                <span class="seal-text-top">Official Seal</span>
                <span class="seal-title"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI PARCEL'); ?></span>
                <span class="seal-status">VERIFIED</span>
                <span class="seal-text-top">Logistics Center</span>
            </div>
        </div>
        
        <div class="summary-area">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo $shipment['currency'] ?? 'USD'; ?> <?php echo number_format($shipment['amount'] ?? 0, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping fee</span>
                    <span>Included</span>
                </div>
                <div class="summary-row">
                    <span>Tax (0%)</span>
                    <span>0.00</span>
                </div>
                <div class="summary-row total">
                    <span>TOTAL DUE</span>
                    <span><?php echo $shipment['currency'] ?? 'USD'; ?> <?php echo number_format($shipment['amount'] ?? 0, 2); ?></span>
                </div>
                <div class="text-end mt-2">
                    <span class="badge <?php echo $shipment['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                        Status: <?php echo strtoupper(htmlspecialchars($shipment['payment_status'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="invoice-signature-area">
            <div class="sig-box">
                <div class="date-display"><?php echo date('Y / m / d'); ?></div>
                <div class="sig-line" style="height: 10px;"></div>
                <div class="sig-text">Date of Issue</div>
            </div>
        </div>

        <div class="invoice-footer">
            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI PARCEL SERVICES'); ?></p>
            <p>This is a computer-generated document and is valid without a signature.</p>
            <p class="mt-3">Thank you for choosing our logistics services.</p>
        </div>
    </div>

</body>
</html>
