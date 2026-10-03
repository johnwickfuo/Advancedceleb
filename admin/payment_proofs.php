<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Required PHPMailer includes
require_once 'vendor/PHPMailer/src/Exception.php';
require_once 'vendor/PHPMailer/src/PHPMailer.php';

require_once '../config.php';
require_once '../get_setting.php'; 
require_once '../include/email_core_functions.php'; 
require_once '../include/get_payment_status_email_body.php';
require_once '../include/send_mail_wrapper.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("location: login.php");
    exit;
}

// Auto-ensure tracking_number column in payment_proofs
try {
    $col_check = $pdo->query("SHOW COLUMNS FROM payment_proofs LIKE 'tracking_number'")->fetch();
    if (!$col_check) {
        $pdo->exec("ALTER TABLE payment_proofs ADD COLUMN tracking_number VARCHAR(100) DEFAULT NULL AFTER booking_id");
    }
} catch (Exception $e) {}

// --- START APPROVE / DECLINE LOGIC ---
if (isset($_GET['action']) && isset($_GET['proof_id']) && is_numeric($_GET['proof_id'])) {
    $action = $_GET['action'];
    $proof_id = (int)$_GET['proof_id'];
    $status_success = false;
    $status_error = "";
    $status_message = "";
    
    try {
        $sql_fetch_data = "
            SELECT 
                pp.*, 
                COALESCE(b.booking_reference, pp.tracking_number) AS tracking_number, 
                b.user_email AS email, 
                b.user_name AS user_name
            FROM payment_proofs pp
            LEFT JOIN bookings b ON pp.booking_id = b.id
            WHERE pp.proof_id = :id
        ";
        $stmt_fetch_data = $pdo->prepare($sql_fetch_data);
        $stmt_fetch_data->bindParam(':id', $proof_id, PDO::PARAM_INT);
        $stmt_fetch_data->execute();
        $proof_data = $stmt_fetch_data->fetch(PDO::FETCH_ASSOC);

        if ($proof_data) {
            $booking_id = $proof_data['booking_id'];
            $recipient_email = $proof_data['email'];
            $ref_no = $proof_data['tracking_number'];
            $email_status = ($action === 'approve') ? 'Approved' : 'Declined';
            
            $pdo->beginTransaction();

            if ($action === 'approve') {
                $sql_proof_update = "UPDATE payment_proofs SET proof_status = 'Approved', status = 'approved', processed_date = NOW() WHERE proof_id = :id";
                $stmt_proof_update = $pdo->prepare($sql_proof_update);
                $stmt_proof_update->bindParam(':id', $proof_id, PDO::PARAM_INT);
                $stmt_proof_update->execute();

                if ($booking_id) {
                    $sql_booking_update = "UPDATE bookings SET status = 'Approved', payment_status = 'Paid' WHERE id = :booking_id";
                    $stmt_booking_update = $pdo->prepare($sql_booking_update);
                    $stmt_booking_update->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
                    $stmt_booking_update->execute();
                }
                
                $status_success = true;
                $status_message = "Payment proof #{$proof_id} has been Approved and Booking #{$ref_no} marked as Paid.";

            } elseif ($action === 'decline') {
                $sql_proof_update = "UPDATE payment_proofs SET proof_status = 'Declined', status = 'rejected', processed_date = NOW() WHERE proof_id = :id";
                $stmt_proof_update = $pdo->prepare($sql_proof_update);
                $stmt_proof_update->bindParam(':id', $proof_id, PDO::PARAM_INT);
                $stmt_proof_update->execute();
                
                $status_success = true;
                $status_message = "Payment proof #{$proof_id} was Declined. Client may resubmit a valid proof.";
            } else {
                $status_error = "Invalid action specified.";
            }
            
            if ($status_success) {
                $pdo->commit();
                
                if (!empty($recipient_email)) {
                    $email_data = [
                        'proof_id' => $proof_data['proof_id'],
                        'tracking_number' => $ref_no,
                        'amount' => $proof_data['amount'],
                        'payment_method' => $proof_data['payment_method'],
                        'user_name' => $proof_data['user_name'] ?? 'Valued Client',
                    ];
                    $smtp_missing = false;
                    try {
                        if (function_exists('sendPaymentProofStatusEmail')) {
                            sendPaymentProofStatusEmail($site_settings, $recipient_email, $email_status, $email_data, $smtp_missing);
                        } elseif (function_exists('send_payment_status_email')) {
                            send_payment_status_email($site_settings, $recipient_email, $email_status, $email_data, $smtp_missing);
                        }
                    } catch (Throwable $mailEx) {
                        error_log("Payment status email notice: " . $mailEx->getMessage());
                    }
                }
            }

        } else {
            $status_error = "Payment proof record not found.";
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $status_error = "Database error: " . $e->getMessage();
    }
    
    $redirect_url = "payment_proofs.php?page=" . (isset($_GET['page']) ? (int)$_GET['page'] : 1);
    if ($status_success) {
        $redirect_url .= "&status=success&msg=" . urlencode($status_message);
    } elseif ($status_error) {
        $redirect_url .= "&status=error&msg=" . urlencode($status_error);
    }
    header("Location: " . $redirect_url);
    exit;
}
// --- END APPROVE / DECLINE LOGIC ---

// --- START DELETE LOGIC ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $delete_success = false;
    $delete_error = "";
    
    try {
        $sql_select = "SELECT * FROM payment_proofs WHERE proof_id = :id";
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt_select->execute();
        $proof_data = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($proof_data) {
            $file_paths = [];
            if (!empty($proof_data['bank_proof_path'])) $file_paths[] = '../' . $proof_data['bank_proof_path'];
            if (!empty($proof_data['crypto_proof_path'])) $file_paths[] = '../' . $proof_data['crypto_proof_path'];
            if (!empty($proof_data['giftcard_proof_front_path'])) $file_paths[] = '../' . $proof_data['giftcard_proof_front_path'];
            if (!empty($proof_data['giftcard_proof_back_path'])) $file_paths[] = '../' . $proof_data['giftcard_proof_back_path'];

            $sql_delete = "DELETE FROM payment_proofs WHERE proof_id = :id";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindParam(':id', $delete_id, PDO::PARAM_INT);
            
            if ($stmt_delete->execute()) {
                foreach ($file_paths as $file_path) {
                    if (file_exists($file_path) && is_file($file_path)) {
                        @unlink($file_path);
                    }
                }
                $delete_success = true;
            } else {
                $delete_error = "Could not delete record from database.";
            }
        } else {
            $delete_error = "Payment proof record not found.";
        }
    } catch (PDOException $e) {
        $delete_error = "Database error: " . $e->getMessage();
    }
    
    $redirect_url = "payment_proofs.php?page=" . (isset($_GET['page']) ? (int)$_GET['page'] : 1);
    if ($delete_success) {
        $redirect_url .= "&status=deleted";
    } elseif ($delete_error) {
        $redirect_url .= "&status=error&msg=" . urlencode($delete_error);
    }
    header("Location: " . $redirect_url);
    exit;
}
// --- END DELETE LOGIC ---

// --- STATS COUNTERS ---
$stat_total = (int)$pdo->query("SELECT COUNT(*) FROM payment_proofs")->fetchColumn();
$stat_pending = (int)$pdo->query("SELECT COUNT(*) FROM payment_proofs WHERE proof_status = 'Pending Review' OR status = 'pending'")->fetchColumn();
$stat_approved = (int)$pdo->query("SELECT COUNT(*) FROM payment_proofs WHERE proof_status = 'Approved' OR status = 'approved'")->fetchColumn();
$stat_declined = (int)$pdo->query("SELECT COUNT(*) FROM payment_proofs WHERE proof_status = 'Declined' OR status = 'rejected'")->fetchColumn();

// --- FILTER & PAGINATION ---
$filter_status = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
$params = [];

if ($filter_status === 'pending') {
    $where_clauses[] = "(pp.proof_status = 'Pending Review' OR pp.status = 'pending')";
} elseif ($filter_status === 'approved') {
    $where_clauses[] = "(pp.proof_status = 'Approved' OR pp.status = 'approved')";
} elseif ($filter_status === 'declined') {
    $where_clauses[] = "(pp.proof_status = 'Declined' OR pp.status = 'rejected')";
}

if ($search !== '') {
    $where_clauses[] = "(pp.tracking_number LIKE :search OR b.booking_reference LIKE :search OR b.user_name LIKE :search OR b.user_email LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$records_per_page = 10;
$count_query = "SELECT COUNT(pp.proof_id) FROM payment_proofs pp LEFT JOIN bookings b ON pp.booking_id = b.id $where_sql";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = (int)$count_stmt->fetchColumn();

$total_pages = max(1, ceil($total_records / $records_per_page));
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $records_per_page;

$sql = "
    SELECT 
        pp.*, 
        COALESCE(b.booking_reference, pp.tracking_number, CONCAT('VIP-', pp.booking_id)) AS tracking_ref,
        b.user_name,
        b.user_email,
        b.event_date
    FROM payment_proofs pp 
    LEFT JOIN bookings b ON pp.booking_id = b.id 
    $where_sql
    ORDER BY pp.submission_date DESC 
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', (int)$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
    <title>Payment Proofs | Admin Dashboard</title> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.css">
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <style>
        .proof-stat-card {
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            border: 1px solid rgba(0,0,0,0.08);
            background: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .proof-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .stat-card-total { border-left: 4px solid #1e293b !important; }
        .stat-card-pending { border-left: 4px solid #f59e0b !important; }
        .stat-card-approved { border-left: 4px solid #10b981 !important; }
        .stat-card-declined { border-left: 4px solid #ef4444 !important; }

        .stat-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-icon-total {
            background-color: rgba(30, 41, 59, 0.1) !important;
            color: #1e293b !important;
        }
        .stat-icon-pending {
            background-color: rgba(245, 158, 11, 0.12) !important;
            color: #d97706 !important;
        }
        .stat-icon-approved {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
        }
        .stat-icon-declined {
            background-color: rgba(239, 68, 68, 0.12) !important;
            color: #dc2626 !important;
        }

        /* Scoped Method Badges */
        .badge-custom-method {
            display: inline-flex;
            align-items: center;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.32rem 0.7rem;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
        }
        .badge-method-bank {
            background-color: #e0f2fe !important;
            color: #0284c7 !important;
            border: 1px solid #bae6fd !important;
        }
        .badge-method-crypto {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }
        .badge-method-giftcard {
            background-color: #f0fdf4 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }
        .badge-method-default {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #cbd5e1 !important;
        }

        /* Thumbnails */
        .proof-thumb {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            background: #f8fafc;
        }
        .proof-thumb:hover {
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-color: #c29b57;
        }
        .proof-thumb-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
            font-size: 1.25rem;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
        }
        .proof-thumb-icon:hover {
            background: #c29b57;
            color: #fff;
            border-color: #c29b57;
            transform: scale(1.1);
        }

        /* Preview Modal Box */
        .preview-pane-box {
            background: #0f172a;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 380px;
            position: relative;
        }
        .preview-img-full {
            max-height: 480px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        /* Mobile Card Layout */
        .mobile-proof-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .mobile-proof-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .mobile-proof-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 0.65rem;
            margin-bottom: 0.75rem;
        }
        .mobile-proof-body {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .mobile-proof-info {
            flex-grow: 1;
        }
        .mobile-proof-actions {
            border-top: 1px solid #f1f5f9;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            display: flex;
            gap: 8px;
        }

        .meta-list-group .list-group-item {
            padding: 10px 14px;
            font-size: 0.88rem;
            border-color: #f1f5f9;
        }
        .meta-label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 700;
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            #main-content {
                padding: 1rem 0.75rem !important;
            }
            .proof-stat-card {
                padding: 0.75rem 0.85rem;
            }
            .proof-stat-card .fs-4 {
                font-size: 1.25rem !important;
            }
            .stat-icon-box {
                width: 36px;
                height: 36px;
                font-size: 1.05rem;
            }
            .preview-pane-box {
                min-height: 240px;
                max-height: 350px;
            }
            .preview-img-full {
                max-height: 320px;
            }
            .modal-dialog {
                margin: 0.5rem;
            }
            .modal-body {
                padding: 1rem !important;
            }
            .modal-header, .modal-footer {
                padding: 0.75rem 1rem !important;
            }
        }
    </style>
</head>
<body>

    <?php include "nav.php"; ?>
    
    <div id="wrapper">
        <?php include "header.php"; ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                
                <!-- Page Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">
                            <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Payment Proofs
                        </h4>
                        <p class="text-muted small mb-0">Review, preview, and verify customer payment slips and receipts.</p>
                    </div>
                    
                    <div class="d-flex gap-2 mt-2 mt-sm-0">
                        <a href="payment_proofs.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </a>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] === 'deleted'): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> Payment proof and associated files were successfully deleted.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif ($_GET['status'] === 'success' && isset($_GET['msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars(urldecode($_GET['msg'])); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif ($_GET['status'] === 'error' && isset($_GET['msg'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Error:</strong> <?php echo htmlspecialchars(urldecode($_GET['msg'])); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Quick Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <a href="payment_proofs.php" class="text-decoration-none">
                            <div class="proof-stat-card shadow-sm stat-card-total">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small fw-bold">TOTAL PROOFS</div>
                                        <div class="fs-4 fw-bold text-dark mt-1"><?php echo number_format($stat_total); ?></div>
                                    </div>
                                    <div class="stat-icon-box stat-icon-total">
                                        <i class="bi bi-collection-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="payment_proofs.php?filter=pending" class="text-decoration-none">
                            <div class="proof-stat-card shadow-sm stat-card-pending">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-warning small fw-bold">PENDING REVIEW</div>
                                        <div class="fs-4 fw-bold text-dark mt-1"><?php echo number_format($stat_pending); ?></div>
                                    </div>
                                    <div class="stat-icon-box stat-icon-pending">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="payment_proofs.php?filter=approved" class="text-decoration-none">
                            <div class="proof-stat-card shadow-sm stat-card-approved">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-success small fw-bold">APPROVED</div>
                                        <div class="fs-4 fw-bold text-dark mt-1"><?php echo number_format($stat_approved); ?></div>
                                    </div>
                                    <div class="stat-icon-box stat-icon-approved">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="payment_proofs.php?filter=declined" class="text-decoration-none">
                            <div class="proof-stat-card shadow-sm stat-card-declined">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-danger small fw-bold">DECLINED</div>
                                        <div class="fs-4 fw-bold text-dark mt-1"><?php echo number_format($stat_declined); ?></div>
                                    </div>
                                    <div class="stat-icon-box stat-icon-declined">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Main Container -->
                <div class="card-enhanced border-0 shadow-sm">
                    <div class="card-header border-0 bg-white py-3 px-3 px-md-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="payment_proofs.php<?php echo $search ? '?search='.urlencode($search) : ''; ?>" 
                                       class="btn btn-sm rounded-pill <?php echo empty($filter_status) ? 'btn-dark' : 'btn-light border'; ?>">
                                        All (<?php echo $stat_total; ?>)
                                    </a>
                                    <a href="payment_proofs.php?filter=pending<?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                                       class="btn btn-sm rounded-pill <?php echo $filter_status === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-light border'; ?>">
                                        Pending (<?php echo $stat_pending; ?>)
                                    </a>
                                    <a href="payment_proofs.php?filter=approved<?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                                       class="btn btn-sm rounded-pill <?php echo $filter_status === 'approved' ? 'btn-success text-white fw-bold' : 'btn-light border'; ?>">
                                        Approved (<?php echo $stat_approved; ?>)
                                    </a>
                                    <a href="payment_proofs.php?filter=declined<?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                                       class="btn btn-sm rounded-pill <?php echo $filter_status === 'declined' ? 'btn-danger text-white fw-bold' : 'btn-light border'; ?>">
                                        Declined (<?php echo $stat_declined; ?>)
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <form method="GET" action="payment_proofs.php" class="d-flex justify-content-md-end gap-2">
                                    <?php if ($filter_status): ?>
                                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter_status); ?>">
                                    <?php endif; ?>
                                    <div class="input-group input-group-sm w-100" style="max-width: 280px;">
                                        <input type="text" name="search" class="form-control" placeholder="Search Ref, Client..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
                                        <?php if ($search): ?>
                                            <a href="payment_proofs.php<?php echo $filter_status ? '?filter='.urlencode($filter_status) : ''; ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (!empty($proofs)): ?>

                        <!-- 1. Desktop Table View (>= 768px) -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Ref #</th>
                                        <th>Client</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th style="width: 90px; text-align: center;">Proof Slip</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th style="width: 140px; text-align: right;" class="pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $row_number = $offset + 1;
                                    foreach ($proofs as $proof): 
                                        $raw_status = $proof['proof_status'] ?? $proof['status'] ?? 'Pending Review';
                                        $is_pending = ($raw_status === 'Pending Review' || $raw_status === 'pending');
                                        $is_approved = ($raw_status === 'Approved' || $raw_status === 'approved');
                                        $is_declined = ($raw_status === 'Declined' || $raw_status === 'rejected');
                                        
                                        $status_badge = '<span class="badge bg-secondary">Unknown</span>';
                                        if ($is_pending) $status_badge = '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
                                        if ($is_approved) $status_badge = '<span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Approved</span>';
                                        if ($is_declined) $status_badge = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Declined</span>';

                                        // Resolve Primary Proof File Path
                                        $method = strtolower($proof['payment_method']);
                                        $proof_file = '';
                                        if ($method === 'bank') {
                                            $proof_file = $proof['bank_proof_path'] ?? '';
                                        } elseif ($method === 'crypto') {
                                            $proof_file = $proof['crypto_proof_path'] ?? '';
                                        } elseif ($method === 'giftcard') {
                                            $proof_file = $proof['giftcard_proof_front_path'] ?? $proof['giftcard_proof_back_path'] ?? '';
                                        }
                                        
                                        $file_ext = strtolower(pathinfo($proof_file, PATHINFO_EXTENSION));
                                        $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                        $is_pdf = ($file_ext === 'pdf');
                                        $full_file_url = !empty($proof_file) ? '../' . $proof_file : '';
                                    ?>
                                    <tr>
                                        <td class="text-muted fw-bold"><?php echo $row_number++; ?></td>
                                        <td>
                                            <span class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($proof['tracking_ref']); ?>
                                            </span>
                                            <?php if (!empty($proof['booking_id'])): ?>
                                                <div class="small"><a href="bookings_manager.php?id=<?php echo $proof['booking_id']; ?>" class="text-muted text-decoration-none">Booking #<?php echo $proof['booking_id']; ?></a></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($proof['user_name'] ?? 'Guest Client'); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($proof['user_email'] ?? 'No email'); ?></div>
                                        </td>
                                        <td>
                                            <?php 
                                                switch ($method) {
                                                    case 'bank': 
                                                        echo '<span class="badge-custom-method badge-method-bank"><i class="fas fa-university me-1"></i>Bank</span>'; 
                                                        break;
                                                    case 'crypto': 
                                                        echo '<span class="badge-custom-method badge-method-crypto"><i class="fab fa-btc me-1"></i>' . strtoupper($proof['crypto_currency'] ?? 'Crypto') . '</span>'; 
                                                        break;
                                                    case 'giftcard': 
                                                        echo '<span class="badge-custom-method badge-method-giftcard"><i class="fas fa-gift me-1"></i>Gift Card</span>'; 
                                                        break;
                                                    default: 
                                                        echo '<span class="badge-custom-method badge-method-default">' . ucfirst($method) . '</span>'; 
                                                        break;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark fs-6"><?php echo format_currency($proof['amount']); ?></span>
                                            <?php if ($method === 'crypto' && !empty($proof['crypto_amount_raw'])): ?>
                                                <div class="small text-muted"><?php echo htmlspecialchars($proof['crypto_amount_raw']); ?> <?php echo strtoupper($proof['crypto_currency'] ?? ''); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($proof_file) && $is_image): ?>
                                                <img src="<?php echo $full_file_url; ?>" 
                                                     alt="Proof Thumbnail" 
                                                     class="proof-thumb view-preview-btn" 
                                                     title="Click to Preview Proof"
                                                     data-id="<?php echo $proof['proof_id']; ?>"
                                                     data-ref="<?php echo htmlspecialchars($proof['tracking_ref']); ?>"
                                                     data-client="<?php echo htmlspecialchars($proof['user_name'] ?? 'Client'); ?>"
                                                     data-email="<?php echo htmlspecialchars($proof['user_email'] ?? ''); ?>"
                                                     data-method="<?php echo $method; ?>"
                                                     data-amount="<?php echo format_currency($proof['amount']); ?>"
                                                     data-status="<?php echo htmlspecialchars($raw_status); ?>"
                                                     data-date="<?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?>"
                                                     data-bank-path="<?php echo htmlspecialchars($proof['bank_proof_path'] ?? ''); ?>"
                                                     data-crypto-curr="<?php echo htmlspecialchars($proof['crypto_currency'] ?? ''); ?>"
                                                     data-crypto-raw="<?php echo htmlspecialchars($proof['crypto_amount_raw'] ?? ''); ?>"
                                                     data-crypto-path="<?php echo htmlspecialchars($proof['crypto_proof_path'] ?? ''); ?>"
                                                     data-gc-value="<?php echo htmlspecialchars($proof['giftcard_value_usd'] ?? ''); ?>"
                                                     data-gc-front="<?php echo htmlspecialchars($proof['giftcard_proof_front_path'] ?? ''); ?>"
                                                     data-gc-back="<?php echo htmlspecialchars($proof['giftcard_proof_back_path'] ?? ''); ?>">
                                            <?php elseif (!empty($proof_file) && $is_pdf): ?>
                                                <button type="button" 
                                                        class="proof-thumb-icon view-preview-btn" 
                                                        title="Click to Preview PDF"
                                                        data-id="<?php echo $proof['proof_id']; ?>"
                                                        data-ref="<?php echo htmlspecialchars($proof['tracking_ref']); ?>"
                                                        data-client="<?php echo htmlspecialchars($proof['user_name'] ?? 'Client'); ?>"
                                                        data-email="<?php echo htmlspecialchars($proof['user_email'] ?? ''); ?>"
                                                        data-method="<?php echo $method; ?>"
                                                        data-amount="<?php echo format_currency($proof['amount']); ?>"
                                                        data-status="<?php echo htmlspecialchars($raw_status); ?>"
                                                        data-date="<?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?>"
                                                        data-bank-path="<?php echo htmlspecialchars($proof['bank_proof_path'] ?? ''); ?>"
                                                        data-crypto-curr="<?php echo htmlspecialchars($proof['crypto_currency'] ?? ''); ?>"
                                                        data-crypto-raw="<?php echo htmlspecialchars($proof['crypto_amount_raw'] ?? ''); ?>"
                                                        data-crypto-path="<?php echo htmlspecialchars($proof['crypto_proof_path'] ?? ''); ?>"
                                                        data-gc-value="<?php echo htmlspecialchars($proof['giftcard_value_usd'] ?? ''); ?>"
                                                        data-gc-front="<?php echo htmlspecialchars($proof['giftcard_proof_front_path'] ?? ''); ?>"
                                                        data-gc-back="<?php echo htmlspecialchars($proof['giftcard_proof_back_path'] ?? ''); ?>">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">No file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td>
                                            <div class="small text-dark fw-bold"><?php echo date('M d, Y', strtotime($proof['submission_date'])); ?></div>
                                            <div class="small text-muted"><?php echo date('g:i A', strtotime($proof['submission_date'])); ?></div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-dark view-preview-btn" 
                                                    title="Preview Full Proof"
                                                    data-id="<?php echo $proof['proof_id']; ?>"
                                                    data-ref="<?php echo htmlspecialchars($proof['tracking_ref']); ?>"
                                                    data-client="<?php echo htmlspecialchars($proof['user_name'] ?? 'Client'); ?>"
                                                    data-email="<?php echo htmlspecialchars($proof['user_email'] ?? ''); ?>"
                                                    data-method="<?php echo $method; ?>"
                                                    data-amount="<?php echo format_currency($proof['amount']); ?>"
                                                    data-status="<?php echo htmlspecialchars($raw_status); ?>"
                                                    data-date="<?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?>"
                                                    data-bank-path="<?php echo htmlspecialchars($proof['bank_proof_path'] ?? ''); ?>"
                                                    data-crypto-curr="<?php echo htmlspecialchars($proof['crypto_currency'] ?? ''); ?>"
                                                    data-crypto-raw="<?php echo htmlspecialchars($proof['crypto_amount_raw'] ?? ''); ?>"
                                                    data-crypto-path="<?php echo htmlspecialchars($proof['crypto_proof_path'] ?? ''); ?>"
                                                    data-gc-value="<?php echo htmlspecialchars($proof['giftcard_value_usd'] ?? ''); ?>"
                                                    data-gc-front="<?php echo htmlspecialchars($proof['giftcard_proof_front_path'] ?? ''); ?>"
                                                    data-gc-back="<?php echo htmlspecialchars($proof['giftcard_proof_back_path'] ?? ''); ?>">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>

                                                <?php if ($is_pending): ?>
                                                    <a href="javascript:void(0)" 
                                                       class="btn btn-outline-success" 
                                                       title="Approve Payment"
                                                       onclick="handleApprove(<?php echo $proof['proof_id']; ?>, '<?php echo htmlspecialchars($proof['tracking_ref']); ?>');">
                                                        <i class="fa-regular fa-check"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" 
                                                       class="btn btn-outline-warning text-dark" 
                                                       title="Decline Payment"
                                                       onclick="handleDecline(<?php echo $proof['proof_id']; ?>, '<?php echo htmlspecialchars($proof['tracking_ref']); ?>');">
                                                        <i class="fa-regular fa-times"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="javascript:void(0)" 
                                                       class="btn btn-outline-danger" 
                                                       title="Delete Record"
                                                       onclick="handleDelete(<?php echo $proof['proof_id']; ?>);">
                                                        <i class="fa-regular fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. Mobile Layout Card View (< 768px) -->
                        <div class="d-block d-md-none p-3">
                            <?php 
                            foreach ($proofs as $proof): 
                                $raw_status = $proof['proof_status'] ?? $proof['status'] ?? 'Pending Review';
                                $is_pending = ($raw_status === 'Pending Review' || $raw_status === 'pending');
                                $is_approved = ($raw_status === 'Approved' || $raw_status === 'approved');
                                $is_declined = ($raw_status === 'Declined' || $raw_status === 'rejected');
                                
                                $status_badge = '<span class="badge bg-secondary">Unknown</span>';
                                if ($is_pending) $status_badge = '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>';
                                if ($is_approved) $status_badge = '<span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Approved</span>';
                                if ($is_declined) $status_badge = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Declined</span>';

                                $method = strtolower($proof['payment_method']);
                                $proof_file = '';
                                if ($method === 'bank') {
                                    $proof_file = $proof['bank_proof_path'] ?? '';
                                } elseif ($method === 'crypto') {
                                    $proof_file = $proof['crypto_proof_path'] ?? '';
                                } elseif ($method === 'giftcard') {
                                    $proof_file = $proof['giftcard_proof_front_path'] ?? $proof['giftcard_proof_back_path'] ?? '';
                                }
                                
                                $file_ext = strtolower(pathinfo($proof_file, PATHINFO_EXTENSION));
                                $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                $is_pdf = ($file_ext === 'pdf');
                                $full_file_url = !empty($proof_file) ? '../' . $proof_file : '';
                            ?>
                            <div class="mobile-proof-card">
                                <div class="mobile-proof-header">
                                    <div>
                                        <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($proof['tracking_ref']); ?></span>
                                        <span class="text-muted small ms-1">(#<?php echo $proof['proof_id']; ?>)</span>
                                    </div>
                                    <div>
                                        <?php echo $status_badge; ?>
                                    </div>
                                </div>
                                
                                <div class="mobile-proof-body">
                                    <!-- Thumbnail -->
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($proof_file) && $is_image): ?>
                                            <img src="<?php echo $full_file_url; ?>" 
                                                 alt="Slip" 
                                                 class="proof-thumb view-preview-btn" 
                                                 data-id="<?php echo $proof['proof_id']; ?>"
                                                 data-ref="<?php echo htmlspecialchars($proof['tracking_ref']); ?>"
                                                 data-client="<?php echo htmlspecialchars($proof['user_name'] ?? 'Client'); ?>"
                                                 data-email="<?php echo htmlspecialchars($proof['user_email'] ?? ''); ?>"
                                                 data-method="<?php echo $method; ?>"
                                                 data-amount="<?php echo format_currency($proof['amount']); ?>"
                                                 data-status="<?php echo htmlspecialchars($raw_status); ?>"
                                                 data-date="<?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?>"
                                                 data-bank-path="<?php echo htmlspecialchars($proof['bank_proof_path'] ?? ''); ?>"
                                                 data-crypto-curr="<?php echo htmlspecialchars($proof['crypto_currency'] ?? ''); ?>"
                                                 data-crypto-raw="<?php echo htmlspecialchars($proof['crypto_amount_raw'] ?? ''); ?>"
                                                 data-crypto-path="<?php echo htmlspecialchars($proof['crypto_proof_path'] ?? ''); ?>"
                                                 data-gc-value="<?php echo htmlspecialchars($proof['giftcard_value_usd'] ?? ''); ?>"
                                                 data-gc-front="<?php echo htmlspecialchars($proof['giftcard_proof_front_path'] ?? ''); ?>"
                                                 data-gc-back="<?php echo htmlspecialchars($proof['giftcard_proof_back_path'] ?? ''); ?>">
                                        <?php elseif (!empty($proof_file) && $is_pdf): ?>
                                            <button type="button" 
                                                    class="proof-thumb-icon view-preview-btn" 
                                                    data-id="<?php echo $proof['proof_id']; ?>"
                                                    data-ref="<?php echo htmlspecialchars($proof['tracking_ref']); ?>"
                                                    data-client="<?php echo htmlspecialchars($proof['user_name'] ?? 'Client'); ?>"
                                                    data-email="<?php echo htmlspecialchars($proof['user_email'] ?? ''); ?>"
                                                    data-method="<?php echo $method; ?>"
                                                    data-amount="<?php echo format_currency($proof['amount']); ?>"
                                                    data-status="<?php echo htmlspecialchars($raw_status); ?>"
                                                    data-date="<?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?>"
                                                    data-bank-path="<?php echo htmlspecialchars($proof['bank_proof_path'] ?? ''); ?>"
                                                    data-crypto-curr="<?php echo htmlspecialchars($proof['crypto_currency'] ?? ''); ?>"
                                                    data-crypto-raw="<?php echo htmlspecialchars($proof['crypto_amount_raw'] ?? ''); ?>"
                                                    data-crypto-path="<?php echo htmlspecialchars($proof['crypto_proof_path'] ?? ''); ?>"
                                                    data-gc-value="<?php echo htmlspecialchars($proof['giftcard_value_usd'] ?? ''); ?>"
                                                    data-gc-front="<?php echo htmlspecialchars($proof['giftcard_proof_front_path'] ?? ''); ?>"
                                                    data-gc-back="<?php echo htmlspecialchars($proof['giftcard_proof_back_path'] ?? ''); ?>">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                            </button>
                                        <?php else: ?>
                                            <div class="proof-thumb-icon"><i class="bi bi-file-x text-muted"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Info -->
                                    <div class="mobile-proof-info">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($proof['user_name'] ?? 'Guest Client'); ?></div>
                                        <div class="small text-muted mb-1"><?php echo htmlspecialchars($proof['user_email'] ?? 'No email'); ?></div>
                                        
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-dark fs-6"><?php echo format_currency($proof['amount']); ?></span>
                                            <?php 
                                                switch ($method) {
                                                    case 'bank': 
                                                        echo '<span class="badge-custom-method badge-method-bank"><i class="fas fa-university me-1"></i>Bank</span>'; 
                                                        break;
                                                    case 'crypto': 
                                                        echo '<span class="badge-custom-method badge-method-crypto"><i class="fab fa-btc me-1"></i>' . strtoupper($proof['crypto_currency'] ?? 'Crypto') . '</span>'; 
                                                        break;
                                                    case 'giftcard': 
                                                        echo '<span class="badge-custom-method badge-method-giftcard"><i class="fas fa-gift me-1"></i>Gift Card</span>'; 
                                                        break;
                                                    default: 
                                                        echo '<span class="badge-custom-method badge-method-default">' . ucfirst($method) . '</span>'; 
                                                        break;
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                                    <span><i class="bi bi-clock me-1"></i><?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?></span>
                                    <?php if (!empty($proof['booking_id'])): ?>
                                        <span><a href="bookings_manager.php?id=<?php echo $proof['booking_id']; ?>" class="text-decoration-none">Booking #<?php echo $proof['booking_id']; ?></a></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions Bar -->
                                <div class="mobile-proof-actions">
                                    <button type="button" class="btn btn-sm btn-outline-dark flex-grow-1 view-preview-btn rounded-pill" 
                                        data-id="<?php echo $proof['proof_id']; ?>"
                                        data-ref="<?php echo htmlspecialchars($proof['tracking_ref']); ?>"
                                        data-client="<?php echo htmlspecialchars($proof['user_name'] ?? 'Client'); ?>"
                                        data-email="<?php echo htmlspecialchars($proof['user_email'] ?? ''); ?>"
                                        data-method="<?php echo $method; ?>"
                                        data-amount="<?php echo format_currency($proof['amount']); ?>"
                                        data-status="<?php echo htmlspecialchars($raw_status); ?>"
                                        data-date="<?php echo date('M d, Y, g:i A', strtotime($proof['submission_date'])); ?>"
                                        data-bank-path="<?php echo htmlspecialchars($proof['bank_proof_path'] ?? ''); ?>"
                                        data-crypto-curr="<?php echo htmlspecialchars($proof['crypto_currency'] ?? ''); ?>"
                                        data-crypto-raw="<?php echo htmlspecialchars($proof['crypto_amount_raw'] ?? ''); ?>"
                                        data-crypto-path="<?php echo htmlspecialchars($proof['crypto_proof_path'] ?? ''); ?>"
                                        data-gc-value="<?php echo htmlspecialchars($proof['giftcard_value_usd'] ?? ''); ?>"
                                        data-gc-front="<?php echo htmlspecialchars($proof['giftcard_proof_front_path'] ?? ''); ?>"
                                        data-gc-back="<?php echo htmlspecialchars($proof['giftcard_proof_back_path'] ?? ''); ?>">
                                        <i class="fa-regular fa-eye me-1"></i> Preview
                                    </button>

                                    <?php if ($is_pending): ?>
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" 
                                            onclick="handleApprove(<?php echo $proof['proof_id']; ?>, '<?php echo htmlspecialchars($proof['tracking_ref']); ?>');">
                                            <i class="fa-regular fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3" 
                                            onclick="handleDecline(<?php echo $proof['proof_id']; ?>, '<?php echo htmlspecialchars($proof['tracking_ref']); ?>');">
                                            <i class="fa-regular fa-times"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                            onclick="handleDelete(<?php echo $proof['proof_id']; ?>);">
                                            <i class="fa-regular fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-3 text-muted">
                                    <i class="bi bi-receipt fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No Payment Proofs Found</h6>
                                <p class="text-muted small">No payment slips match your current filter criteria.</p>
                                <a href="payment_proofs.php" class="btn btn-sm btn-outline-primary rounded-pill">Reset Filters</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination Footer -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer border-0 bg-white py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="small text-muted">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> records
                        </span>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $filter_status ? '&filter='.$filter_status : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_status ? '&filter='.$filter_status : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $filter_status ? '&filter='.$filter_status : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
            <?php include "footer.php"; ?>
        </div>
    </div>

<!-- Interactive Payment Proof Preview Modal -->
<div class="modal fade" id="proofPreviewModal" tabindex="-1" aria-labelledby="proofPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-bottom: 3px solid #c29b57;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center" id="proofPreviewModalLabel">
                    <i class="bi bi-file-earmark-check-fill text-warning me-2"></i>
                    Payment Proof Preview: Ref <span id="modalRefNo" class="ms-1 text-warning"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 p-md-4 bg-light">
                <div class="row g-4">
                    <!-- Left: Full Visual Proof Preview -->
                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small text-uppercase">
                                <i class="bi bi-image me-1 text-primary"></i> Uploaded Slip / Receipt
                            </span>
                            <div id="previewActionLinks" class="d-flex gap-2"></div>
                        </div>
                        
                        <div class="preview-pane-box shadow-sm" id="previewContainer">
                            <!-- Injected dynamically via JS -->
                        </div>
                    </div>

                    <!-- Right: Metadata & Action Options -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-3 p-md-4">
                                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex justify-content-between align-items-center">
                                    <span>Transaction Details</span>
                                    <span id="modalStatusBadge"></span>
                                </h6>
                                
                                <ul class="list-group list-group-flush meta-list-group mb-4">
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="meta-label">Booking Ref</span>
                                        <span class="meta-val text-primary" id="modalMetaRef">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="meta-label">Client Name</span>
                                        <span class="meta-val" id="modalMetaClient">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="meta-label">Client Email</span>
                                        <span class="meta-val text-muted small" id="modalMetaEmail">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="meta-label">Payment Method</span>
                                        <span class="meta-val text-capitalize" id="modalMetaMethod">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="meta-label">Amount Paid</span>
                                        <span class="meta-val text-success fs-6" id="modalMetaAmount">-</span>
                                    </li>
                                    <li id="cryptoMetaRow" class="list-group-item d-flex justify-content-between align-items-center bg-transparent" style="display: none;">
                                        <span class="meta-label">Crypto Details</span>
                                        <span class="meta-val" id="modalMetaCrypto">-</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                        <span class="meta-label">Submitted At</span>
                                        <span class="meta-val small" id="modalMetaDate">-</span>
                                    </li>
                                </ul>

                                <div id="modalActionButtons" class="d-grid gap-2">
                                    <!-- Injected dynamically based on pending/approved -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-white border-top py-2 px-4 justify-content-between">
                <span class="small text-muted">Proof ID: <strong id="modalProofIdText"></strong></span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
let currentProofId = null;
let currentRefNo = '';
const currentPage = <?php echo $page; ?>;

document.addEventListener('DOMContentLoaded', function () {
    const previewModalEl = document.getElementById('proofPreviewModal');
    const previewModal = new bootstrap.Modal(previewModalEl);

    // Attach click listener to all preview trigger elements
    document.querySelectorAll('.view-preview-btn').forEach(button => {
        button.addEventListener('click', function () {
            currentProofId = this.getAttribute('data-id');
            currentRefNo = this.getAttribute('data-ref');
            const client = this.getAttribute('data-client');
            const email = this.getAttribute('data-email');
            const method = this.getAttribute('data-method');
            const amount = this.getAttribute('data-amount');
            const status = this.getAttribute('data-status');
            const date = this.getAttribute('data-date');

            const bankPath = this.getAttribute('data-bank-path');
            const cryptoCurr = this.getAttribute('data-crypto-curr');
            const cryptoRaw = this.getAttribute('data-crypto-raw');
            const cryptoPath = this.getAttribute('data-crypto-path');
            const gcValue = this.getAttribute('data-gc-value');
            const gcFront = this.getAttribute('data-gc-front');
            const gcBack = this.getAttribute('data-gc-back');

            // Populate Meta Fields
            document.getElementById('modalRefNo').textContent = currentRefNo;
            document.getElementById('modalProofIdText').textContent = currentProofId;
            document.getElementById('modalMetaRef').textContent = currentRefNo;
            document.getElementById('modalMetaClient').textContent = client;
            document.getElementById('modalMetaEmail').textContent = email || 'N/A';
            document.getElementById('modalMetaMethod').textContent = method;
            document.getElementById('modalMetaAmount').textContent = amount;
            document.getElementById('modalMetaDate').textContent = date;

            // Crypto special field
            const cryptoRow = document.getElementById('cryptoMetaRow');
            if (method === 'crypto' && cryptoRaw) {
                cryptoRow.style.display = 'flex';
                document.getElementById('modalMetaCrypto').textContent = `${cryptoRaw} ${cryptoCurr.toUpperCase()}`;
            } else {
                cryptoRow.style.display = 'none';
            }

            // Status Badge
            const isPending = (status === 'Pending Review' || status === 'pending');
            const isApproved = (status === 'Approved' || status === 'approved');
            const isDeclined = (status === 'Declined' || status === 'rejected');
            
            let statusHtml = '<span class="badge bg-secondary">Unknown</span>';
            if (isPending) statusHtml = '<span class="badge bg-warning text-dark">Pending Review</span>';
            if (isApproved) statusHtml = '<span class="badge bg-success">Approved</span>';
            if (isDeclined) statusHtml = '<span class="badge bg-danger">Declined</span>';
            document.getElementById('modalStatusBadge').innerHTML = statusHtml;

            // Render Preview Files
            const previewContainer = document.getElementById('previewContainer');
            const previewActionLinks = document.getElementById('previewActionLinks');
            previewContainer.innerHTML = '';
            previewActionLinks.innerHTML = '';

            let fileList = [];
            if (method === 'bank' && bankPath) {
                fileList.push({ label: 'Bank Slip', path: bankPath });
            } else if (method === 'crypto' && cryptoPath) {
                fileList.push({ label: 'Crypto Receipt', path: cryptoPath });
            } else if (method === 'giftcard') {
                if (gcFront) fileList.push({ label: 'Card Front', path: gcFront });
                if (gcBack) fileList.push({ label: 'Card Back', path: gcBack });
            }

            if (fileList.length === 0) {
                previewContainer.innerHTML = `
                    <div class="text-center text-muted p-4">
                        <i class="bi bi-file-earmark-x fs-1 mb-2 d-block text-secondary"></i>
                        <p class="mb-0">No proof file attached for this transaction.</p>
                    </div>
                `;
            } else {
                let htmlContent = '';
                fileList.forEach((file, index) => {
                    const ext = file.path.split('.').pop().toLowerCase();
                    const fullUrl = `../${file.path}`;

                    // Action link (Open Full File)
                    previewActionLinks.innerHTML += `
                        <a href="${fullUrl}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ${file.label}
                        </a>
                    `;

                    if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                        htmlContent += `
                            <div class="text-center w-100 p-2 ${fileList.length > 1 ? 'mb-3 border-bottom border-secondary' : ''}">
                                ${fileList.length > 1 ? `<div class="text-warning small fw-bold mb-2">${file.label}</div>` : ''}
                                <a href="${fullUrl}" target="_blank" title="Click to view full size">
                                    <img src="${fullUrl}" alt="${file.label}" class="preview-img-full shadow">
                                </a>
                            </div>
                        `;
                    } else if (ext === 'pdf') {
                        htmlContent += `
                            <div class="w-100 h-100 p-2">
                                <iframe src="${fullUrl}" width="100%" height="420px" class="rounded shadow border-0" style="background:#fff;"></iframe>
                            </div>
                        `;
                    } else {
                        htmlContent += `
                            <div class="text-center text-white p-4">
                                <i class="bi bi-file-earmark-arrow-down fs-1 mb-2 d-block text-warning"></i>
                                <p class="mb-2">Document file (${ext.toUpperCase()})</p>
                                <a href="${fullUrl}" target="_blank" class="btn btn-sm btn-warning fw-bold">Download File</a>
                            </div>
                        `;
                    }
                });
                previewContainer.innerHTML = htmlContent;
            }

            // Action Buttons in Modal
            const actionBtnBox = document.getElementById('modalActionButtons');
            if (isPending) {
                actionBtnBox.innerHTML = `
                    <button type="button" class="btn btn-success fw-bold py-2 rounded-pill shadow-sm" onclick="handleApprove(${currentProofId}, '${currentRefNo}')">
                        <i class="fa-regular fa-check me-2"></i> Approve Payment
                    </button>
                    <button type="button" class="btn btn-outline-warning text-dark fw-bold py-2 rounded-pill" onclick="handleDecline(${currentProofId}, '${currentRefNo}')">
                        <i class="fa-regular fa-times me-2"></i> Decline Payment
                    </button>
                `;
            } else {
                actionBtnBox.innerHTML = `
                    <div class="alert alert-light border text-center small text-muted mb-2">
                        Status is finalized (${status}).
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="handleDelete(${currentProofId})">
                        <i class="fa-regular fa-trash me-1"></i> Delete Proof Record
                    </button>
                `;
            }

            previewModal.show();
        });
    });
});

// Confirmation Handlers
function handleApprove(proofId, refNo) {
    Swal.fire({
        title: 'Approve Payment Proof?',
        html: `Are you sure you want to <strong>APPROVE</strong> proof <strong>#${proofId}</strong> for booking <strong>${refNo}</strong>?<br><br>This will mark the booking as <span class="badge bg-success">Paid</span>.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa-regular fa-check me-1"></i> Yes, Approve'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `payment_proofs.php?action=approve&proof_id=${proofId}&page=${currentPage}`;
        }
    });
}

function handleDecline(proofId, refNo) {
    Swal.fire({
        title: 'Decline Payment Proof?',
        html: `Are you sure you want to <strong>DECLINE</strong> proof <strong>#${proofId}</strong> for booking <strong>${refNo}</strong>?<br><br>The client will be permitted to upload a revised payment slip.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa-regular fa-times me-1"></i> Yes, Decline'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `payment_proofs.php?action=decline&proof_id=${proofId}&page=${currentPage}`;
        }
    });
}

function handleDelete(proofId) {
    Swal.fire({
        title: 'Delete Payment Proof?',
        text: `Are you sure you want to permanently delete proof #${proofId} and its uploaded files? This action cannot be undone.`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa-regular fa-trash me-1"></i> Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `payment_proofs.php?delete_id=${proofId}&page=${currentPage}`;
        }
    });
}
</script>
</body>
</html>
