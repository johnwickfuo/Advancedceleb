<?php
session_start();
require_once '../config.php';
require_once '../get_setting.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}



// Pagination Logic
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

try {
    $total_count_sql = "SELECT COUNT(*) FROM shipments";
    $total_count_stmt = $pdo->query($total_count_sql);
    $total_records = $total_count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    $sql = "SELECT id, tracking_number, sender_name, receiver_name, service_type, payment_status, amount, currency, created_at 
            FROM shipments 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error retrieving shipments: " . $e->getMessage();
    $shipments = [];
    $total_pages = 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Print Invoices - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --bs-crimson-dark: #6D0000;
            --bs-crimson-light: #B00000;
            --bs-gold: #FFC107;
        }
        body { background-color: #f4f7f6; font-family: 'Work Sans', sans-serif; }
        .card-enhanced { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: #fff; }
        .btn-print { background: var(--bs-crimson-light); border: none; color: white; transition: all 0.3s; border-radius: 8px; font-weight: 600; }
        .btn-print:hover { background: var(--bs-crimson-dark); transform: translateY(-2px); color: white; box-shadow: 0 5px 15px rgba(176,0,0,0.3); }
        .table thead th { background: #212529; color: white; border: none; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f3f5; }
        .tracking-badge { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; font-weight: 700; color: var(--bs-crimson-light); }
        
        /* Premium Pagination Styles */
        .pagination .page-link {
            border: none;
            color: #495057;
            padding: 8px 16px;
            margin: 0 3px;
            border-radius: 8px !important;
            font-weight: 600;
            transition: all 0.3s;
        }
        .pagination .page-item.active .page-link {
            background: var(--bs-crimson-light);
            color: white;
            box-shadow: 0 4px 10px rgba(176,0,0,0.2);
        }
        .pagination .page-link:hover:not(.active) {
            background: #eef2f7;
            color: var(--bs-crimson-light);
        }

        /* Mobile Card Layout */
        @media (max-width: 768px) {
            .table thead { display: none; }
            .table-responsive { border: none !important; overflow: visible !important; }
            .card-enhanced { background: transparent !important; box-shadow: none !important; }
            .table tbody { display: flex; flex-direction: column; gap: 20px; width: 100%; }
            .table tbody tr { 
                display: flex !important; 
                flex-direction: column; 
                background: #fff; 
                border-radius: 20px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
                overflow: hidden; 
                border: 1px solid #edf2f7;
                width: 100%;
            }
            .table tbody td { 
                display: flex !important; 
                justify-content: space-between; 
                align-items: center;
                padding: 15px 20px !important; 
                border-bottom: 1px solid #f8fafc;
                text-align: right;
                width: 100%;
            }
            .table tbody td::before {
                content: attr(data-label);
                font-weight: 800;
                text-align: left;
                color: #64748b;
                font-size: 0.65rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                flex: 1;
            }
            .table tbody td span, .table tbody td div { flex: none; }
            
            .table tbody td.action-cell { 
                display: block !important; 
                padding: 20px !important; 
                background: #f8fafc; 
                border-top: 1px solid #edf2f7;
                text-align: center !important;
            }
            .table tbody td.action-cell::before { display: none; }
            .btn-print { width: 100%; padding: 12px !important; border-radius: 12px; font-size: 1rem; }
            
            /* Tracking Badge Adjustment on Mobile */
            .tracking-badge { color: var(--bs-crimson-light); }
        }
    </style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper" class="d-flex">
        <?php include "header.php" ?>

        <div class="main-content-wrapper flex-grow-1 p-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bolder">Generate & Print Invoices</h4>
                    <p class="text-muted small d-none d-md-block">Select a shipment to generate a professional invoice</p>
                    <p class="text-muted small d-block d-md-none">Select shipment to print</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card-enhanced p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tracking Number</th>
                                    <th>Sender</th>
                                    <th>Receiver</th>
                                    <th>Amount</th>
                                    <th>Created At</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($shipments)): ?>
                                    <tr><td colspan="6" class="text-center py-5">No shipments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($shipments as $s): ?>
                                        <tr>
                                            <td data-label="Tracking Number"><span class="tracking-badge"><?php echo htmlspecialchars($s['tracking_number']); ?></span></td>
                                            <td data-label="Sender"><?php echo htmlspecialchars($s['sender_name']); ?></td>
                                            <td data-label="Receiver"><?php echo htmlspecialchars($s['receiver_name']); ?></td>
                                            <td data-label="Amount">
                                                <div class="text-end text-md-start">
                                                    <span class="fw-bold">
                                                        <?php echo $s['currency'] ?? 'USD'; ?> <?php echo number_format($s['amount'] ?? 0, 2); ?>
                                                    </span>
                                                    <br class="d-none d-md-block">
                                                    <small class="badge <?php echo $s['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning text-dark'; ?> px-2 py-1">
                                                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($s['payment_status']); ?></span>
                                                        <span class="d-inline d-md-none"><?php echo (strtolower($s['payment_status']) === 'payment on delivery') ? 'Pay on Delivery' : htmlspecialchars($s['payment_status']); ?></span>
                                                    </small>
                                                </div>
                                            </td>
                                            <td data-label="Created At" class="text-muted small"><?php echo date('M j, Y', strtotime($s['created_at'])); ?></td>
                                            <td class="text-end action-cell">
                                                <a href="invoice.php?id=<?php echo $s['id']; ?>" target="_blank" class="btn btn-sm btn-print px-3 py-2">
                                                    <i class="bi bi-printer-fill me-2"></i> Print Invoice
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="p-4 bg-white border-top">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="bi bi-chevron-left"></i></a>
                                    </li>
                                    
                                    <?php 
                                    $start = max(1, $page - 2);
                                    $end = min($total_pages, $page + 2);
                                    
                                    if ($start > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                        if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    
                                    for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; 
                                    
                                    if ($end < $total_pages) {
                                        if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                                    }
                                    ?>
                                    
                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="bi bi-chevron-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php include "footer.php" ?>
    </div>
</div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
