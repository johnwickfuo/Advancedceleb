<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';
require_once '../get_setting.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$success_msg = '';
$error_msg = '';

// Handle Booking Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
        $id = (int)$_POST['id'];
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
            $stmt->execute([$status, $id]);
            $success_msg = "Booking status updated to " . htmlspecialchars($status) . ".";
            
            // Trigger Email Notification if accepted or cancelled
            if (in_array($status, ['Accepted', 'Cancelled'])) {
                $b_stmt = $pdo->prepare("SELECT b.*, c.name as celebrity_name FROM bookings b LEFT JOIN celebrities c ON b.celebrity_id = c.id WHERE b.id=?");
                $b_stmt->execute([$id]);
                $booking = $b_stmt->fetch();
                
                if ($booking && !empty($booking['user_email'])) {
                    require_once '../include/email_core_functions.php';
                    $smtp_config_missing = false;
                    
                    $service_label = ($booking['booking_type'] ?? 'event') === 'cameo' ? 'Cameo Request' : 'Booking';
                    if ($status === 'Accepted') {
                        $subj = $service_label . " Accepted - Ref: " . $booking['booking_reference'];
                        $body = "
                            <h2 style='color: #e63946; margin-top: 0;'>Booking Accepted!</h2>
                            <p>Great news, {$booking['user_name']}!</p>
                            <p>Your booking request for <strong>{$booking['celebrity_name']}</strong> has been <strong>ACCEPTED</strong>.</p>
                            <p>Please proceed to track your booking and complete your payment to finalize the arrangement.</p>
                            <p><a href='{$site_settings['site_url']}/payment.php?ref={$booking['booking_reference']}' style='display: inline-block; padding: 10px 20px; background-color: #e63946; color: #fff; border-radius: 6px; font-weight: bold;'>Complete Payment Now</a></p>
                        ";
                    } else {
                        $subj = $service_label . " Cancelled - Ref: " . $booking['booking_reference'];
                        $body = "
                            <h2 style='color: #e63946; margin-top: 0;'>Booking Cancelled</h2>
                            <p>Dear {$booking['user_name']},</p>
                            <p>We regret to inform you that your booking request for <strong>{$booking['celebrity_name']}</strong> has been cancelled.</p>
                            <p>If you have any questions, please contact our support team.</p>
                        ";
                    }
                    try {
                        send_email_notification($site_settings, $booking['user_email'], $subj, $body, $smtp_config_missing);
                    } catch (\Throwable $e) {
                        error_log("Failed to send booking status email: " . $e->getMessage());
                    }
                }
            }
            
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_booking') {
        $id = (int)$_POST['id'];
        try {
            // Delete associated payment proofs and their files from the disk
            $p_stmt = $pdo->prepare("SELECT bank_proof_path, crypto_proof_path, giftcard_proof_front_path, giftcard_proof_back_path FROM payment_proofs WHERE booking_id = ?");
            $p_stmt->execute([$id]);
            $proofs_to_delete = $p_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($proofs_to_delete as $p) {
                foreach (['bank_proof_path', 'crypto_proof_path', 'giftcard_proof_front_path', 'giftcard_proof_back_path'] as $path_key) {
                    if (!empty($p[$path_key])) {
                        $file_path = '../' . $p[$path_key];
                        if (file_exists($file_path)) {
                            @unlink($file_path);
                        }
                    }
                }
            }
            $del_proofs = $pdo->prepare("DELETE FROM payment_proofs WHERE booking_id = ?");
            $del_proofs->execute([$id]);

            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id=?");
            $stmt->execute([$id]);
            $success_msg = "Booking deleted successfully.";
        } catch (PDOException $e) {
            $error_msg = "Error deleting booking: " . $e->getMessage();
        }
    }
}

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Filter cameo requests separately without hiding existing event bookings.
$view_type = $_GET['type'] ?? 'all';
if (!in_array($view_type, ['all', 'event', 'cameo'], true)) {
    $view_type = 'all';
}
$filter_sql = $view_type === 'all' ? '' : ' WHERE booking_type = :booking_type';
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings" . $filter_sql);
if ($view_type !== 'all') {
    $total_stmt->bindValue(':booking_type', $view_type);
}
$total_stmt->execute();
$total_records = (int)$total_stmt->fetchColumn();
$total_pages = (int)ceil($total_records / $limit);

// Fetch bookings with celebrity and cameo information.
$booking_filter_sql = $view_type === 'all' ? '' : ' WHERE b.booking_type = :booking_type';
$stmt = $pdo->prepare("
    SELECT b.*, c.name as celebrity_name, c.profile_picture
    FROM bookings b
    LEFT JOIN celebrities c ON b.celebrity_id = c.id
    " . $booking_filter_sql . "
    ORDER BY b.created_at DESC
    LIMIT :limit OFFSET :offset
");
if ($view_type !== 'all') {
    $stmt->bindValue(':booking_type', $view_type);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Platform'); ?></title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .cel-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div id="wrapper">
        <?php include 'header.php'; ?>
        
        <div class="main-content-wrapper p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-calendar-check text-primary me-2"></i><?php echo $view_type === 'cameo' ? 'Cameo Requests' : 'Manage Bookings'; ?></h2>
            </div>
            
            <?php if($success_msg): ?>
                <div class="alert alert-success shadow-sm rounded-3"><i class="bi bi-check-circle me-2"></i><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="alert alert-danger shadow-sm rounded-3"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <div class="d-flex gap-2 flex-wrap mb-3" aria-label="Booking filters">
                <a class="btn btn-sm <?php echo $view_type === 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="bookings_manager.php">All Orders</a>
                <a class="btn btn-sm <?php echo $view_type === 'event' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="bookings_manager.php?type=event">Celebrity Bookings</a>
                <a class="btn btn-sm <?php echo $view_type === 'cameo' ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="bookings_manager.php?type=cameo">Cameo Videos</a>
            </div>
            <div class="card card-custom">
                <div class="card-body p-0">
                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Celebrity</th>
                                    <th>Client</th>
                                    <th>Event / Order Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($bookings) > 0): ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($booking['profile_picture'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($booking['profile_picture']); ?>" class="cel-thumb me-2 shadow-sm" onerror="this.outerHTML='<div class=\'cel-thumb bg-secondary text-white d-flex align-items-center justify-content-center me-2 shadow-sm\' style=\'width:40px; height:40px; border-radius:8px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;\'><i class=\'bi bi-person-fill fs-5\'></i></div>'">
                                                    <?php else: ?>
                                                        <div class="cel-thumb text-white d-flex align-items-center justify-content-center me-2 shadow-sm" style="width:40px; height:40px; border-radius:8px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;">
                                                            <i class="bi bi-person-fill fs-5"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <span>
                                                        <?php echo htmlspecialchars($booking['celebrity_name'] ?? 'Celebrity'); ?>
                                                        <?php if (($booking['booking_type'] ?? 'event') === 'cameo'): ?>
                                                            <span class="badge bg-info text-dark d-block mt-1">Cameo Video</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime(($booking['booking_type'] ?? 'event') === 'cameo' ? $booking['created_at'] : $booking['event_date'])); ?>
                                                <?php if (($booking['booking_type'] ?? 'event') === 'cameo'): ?><small class="d-block text-muted">Ordered</small><?php endif; ?>
                                            </td>
                                            <td><?php echo format_currency($booking['amount']); ?></td>
                                            <td>
                                                <?php 
                                                $badgeClass = 'bg-secondary';
                                                if($booking['status'] == 'Pending') $badgeClass = 'bg-warning text-dark';
                                                if($booking['status'] == 'Accepted') $badgeClass = 'bg-success';
                                                if($booking['status'] == 'Cancelled') $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($booking['status']); ?></span>
                                                <small class="d-block mt-1 text-muted">Payment: <?php echo htmlspecialchars($booking['payment_status'] ?? 'Unpaid'); ?></small>
                                            </td>
                                            <td class="text-end pe-4 text-nowrap">
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    <button class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" onclick='viewBooking(<?php echo htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8'); ?>)' title="View Details & Manage Status">
                                                        <i class="bi bi-eye text-primary fs-6"></i>
                                                    </button>
                                                    <form method="post" class="m-0" onsubmit="confirmDelete(event, this);">
                                                        <input type="hidden" name="action" value="delete_booking">
                                                        <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Delete">
                                                            <i class="bi bi-trash text-danger fs-6"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No bookings found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-block d-md-none p-3 bg-light">
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <div class="card mb-3 shadow-sm border-0 rounded-4 overflow-hidden">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($booking['profile_picture'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($booking['profile_picture']); ?>" class="cel-thumb me-3 shadow-sm" style="width:55px; height:55px; border-radius:12px;" onerror="this.outerHTML='<div class=\'cel-thumb text-white d-flex align-items-center justify-content-center me-3 shadow-sm\' style=\'width:55px; height:55px; border-radius:12px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;\'><i class=\'bi bi-person-fill fs-3\'></i></div>'">
                                                <?php else: ?>
                                                    <div class="cel-thumb text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width:55px; height:55px; border-radius:12px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;">
                                                        <i class="bi bi-person-fill fs-3"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($booking['celebrity_name'] ?? 'Celebrity'); ?></h6>
                                                    <small class="text-muted fw-semibold">#<?php echo htmlspecialchars($booking['booking_reference']); ?></small>
                                                    <?php if (($booking['booking_type'] ?? 'event') === 'cameo'): ?><span class="badge bg-info text-dark d-block mt-1">Cameo Video</span><?php endif; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <?php 
                                                $badgeClass = 'bg-secondary';
                                                if($booking['status'] == 'Pending') $badgeClass = 'bg-warning text-dark';
                                                if($booking['status'] == 'Accepted') $badgeClass = 'bg-success';
                                                if($booking['status'] == 'Cancelled') $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?> px-2 py-1"><?php echo htmlspecialchars($booking['status']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                            <div class="mb-2">
                                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Client Info</small>
                                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;"><?php echo ($booking['booking_type'] ?? 'event') === 'cameo' ? 'Order Date' : 'Event Date'; ?></small>
                                                    <div class="fw-bold text-dark"><?php echo date('M d, Y', strtotime(($booking['booking_type'] ?? 'event') === 'cameo' ? $booking['created_at'] : $booking['event_date'])); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Amount</small>
                                                    <div class="fw-bold text-success"><?php echo format_currency($booking['amount']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <button class="btn btn-light border shadow-sm rounded-pill px-3 py-2 d-flex align-items-center justify-content-center flex-grow-1 me-2 fw-semibold text-primary" onclick='viewBooking(<?php echo htmlspecialchars(json_encode($booking), ENT_QUOTES, 'UTF-8'); ?>)' title="View Details">
                                                <i class="bi bi-eye me-2"></i> View Details & Status
                                            </button>
                                            <form method="post" class="m-0" onsubmit="confirmDelete(event, this);">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-light border shadow-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Delete">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 bg-white rounded-3 shadow-sm text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                No bookings found.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <div class="px-4 py-3 border-top">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?type=<?php echo urlencode($view_type); ?>&amp;page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?type=<?php echo urlencode($view_type); ?>&amp;page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?type=<?php echo urlencode($view_type); ?>&amp;page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    
    <!-- View Booking Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="modal-title fw-bold mb-0">Booking Details <span id="v_ref" class="text-primary fw-bold"></span></h5>
                        <span id="v_status_badge" class="badge"></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <small class="text-uppercase text-muted fw-bold mb-2 d-block" style="font-size: 0.75rem;">Celebrity Booked</small>
                                <div class="d-flex align-items-center mb-2">
                                    <div id="v_cel_avatar" class="me-2"></div>
                                    <div class="fs-5 fw-bold text-dark" id="v_cel_name"></div>
                                </div>
                                <div class="fw-bold text-success fs-5">Amount: $<span id="v_amount"></span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 border">
                                <small class="text-uppercase text-muted fw-bold mb-2 d-block" id="v_info_heading" style="font-size: 0.75rem;">Event Information</small>
                                <div class="mb-1"><strong id="v_date_label">Date:</strong> <span id="v_date" class="fw-semibold text-dark"></span></div>
                                <div class="mb-1"><strong>Event Type:</strong> <span id="v_type" class="badge bg-secondary"></span></div>
                                <div><strong>Current Status:</strong> <span id="v_status_text" class="fw-bold"></span></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-uppercase text-muted fw-bold mb-2 d-block" style="font-size: 0.75rem;">Client Information</small>
                                <div class="row g-2">
                                    <div class="col-md-4"><strong>Name:</strong> <span id="v_client_name" class="text-dark d-block"></span></div>
                                    <div class="col-md-4"><strong>Email:</strong> <span id="v_email" class="text-dark d-block"></span></div>
                                    <div class="col-md-4"><strong>Phone:</strong> <span id="v_phone" class="text-dark d-block"></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="v_delivery_block" style="display:none;">
                            <div class="p-3 border rounded-3" style="background:#fff9e9;">
                                <small class="text-uppercase text-muted fw-bold d-block mb-2">Completed Video Delivery</small>
                                <div><strong>Email:</strong> <span id="v_delivery_email" class="text-break"></span></div>
                                <div><strong>WhatsApp:</strong> <span id="v_delivery_whatsapp"></span></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-uppercase text-muted fw-bold mb-2 d-block" id="v_details_label" style="font-size: 0.75rem;">Event Details & Requirements</small>
                                <p id="v_details" class="mb-0 text-break text-muted"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <form method="post" id="modalStatusForm" class="d-flex gap-2 align-items-center m-0 flex-wrap">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" id="modal_booking_id" value="">
                        <input type="hidden" name="status" id="modal_booking_status" value="">

                        <span class="small fw-bold text-muted me-1">Set Status:</span>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="submitModalStatus('Pending', 'warning')">
                            <i class="bi bi-clock me-1"></i> Pending
                        </button>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" onclick="submitModalStatus('Accepted', 'success')">
                            <i class="bi bi-check-lg me-1"></i> Accept
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="submitModalStatus('Cancelled', 'danger')">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <script>
        function viewBooking(booking) {
            document.getElementById('modal_booking_id').value = booking.id;
            document.getElementById('v_ref').innerText = '#' + booking.booking_reference;
            document.getElementById('v_cel_name').innerText = booking.celebrity_name || 'Celebrity';
            if (booking.profile_picture) {
                document.getElementById('v_cel_avatar').innerHTML = '<img src="../' + booking.profile_picture + '" class="cel-thumb shadow-sm" style="width:40px; height:40px; border-radius:8px;">';
            } else {
                document.getElementById('v_cel_avatar').innerHTML = '<div class="cel-thumb text-white d-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px; border-radius:8px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;"><i class="bi bi-person-fill fs-5"></i></div>';
            }
            document.getElementById('v_amount').innerText = Number(booking.amount).toLocaleString('en-US');
            var isCameo = booking.booking_type === 'cameo';
            document.getElementById('v_info_heading').innerText = isCameo ? 'Cameo Request Information' : 'Event Information';
            document.getElementById('v_date_label').innerText = isCameo ? 'Ordered:' : 'Date:';
            document.getElementById('v_date').innerText = isCameo ? booking.created_at : booking.event_date;
            document.getElementById('v_type').innerText = isCameo ? 'Cameo Video' : (booking.event_type || 'N/A');
            document.getElementById('v_client_name').innerText = booking.user_name;
            document.getElementById('v_email').innerText = booking.user_email;
            document.getElementById('v_phone').innerText = booking.user_phone;
            document.getElementById('v_details_label').innerText = isCameo ? 'Requested Words / Script' : 'Event Details & Requirements';
            document.getElementById('v_details').innerText = isCameo ? (booking.cameo_script || '') : (booking.event_details || 'No additional details provided.');
            document.getElementById('v_delivery_block').style.display = isCameo ? '' : 'none';
            document.getElementById('v_delivery_email').innerText = isCameo ? (booking.delivery_email || '') : '';
            document.getElementById('v_delivery_whatsapp').innerText = isCameo ? (booking.delivery_whatsapp || '') : '';
            
            // Status Badge & Text in Modal
            var statusBadge = document.getElementById('v_status_badge');
            var statusText = document.getElementById('v_status_text');
            var badgeClass = 'bg-secondary';
            if (booking.status === 'Pending') badgeClass = 'bg-warning text-dark';
            if (booking.status === 'Accepted') badgeClass = 'bg-success text-white';
            if (booking.status === 'Cancelled') badgeClass = 'bg-danger text-white';
            if (statusBadge) {
                statusBadge.className = 'badge ' + badgeClass + ' px-3 py-1';
                statusBadge.innerText = booking.status;
            }
            if (statusText) {
                statusText.innerText = booking.status;
                statusText.className = (booking.status === 'Accepted' ? 'text-success fw-bold' : (booking.status === 'Cancelled' ? 'text-danger fw-bold' : 'text-warning fw-bold'));
            }

            var modal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
            modal.show();
        }

        function submitModalStatus(statusName, colorClass) {
            var form = document.getElementById('modalStatusForm');
            document.getElementById('modal_booking_status').value = statusName;
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Set to ' + statusName + '?',
                    text: 'Update this booking status to ' + statusName,
                    icon: statusName === 'Accepted' ? 'success' : (statusName === 'Cancelled' ? 'warning' : 'info'),
                    showCancelButton: true,
                    confirmButtonText: 'Yes, update',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: statusName === 'Accepted' ? '#198754' : (statusName === 'Cancelled' ? '#dc3545' : '#eab308')
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm("Set status to " + statusName + "?")) {
                    form.submit();
                }
            }
        }

        function confirmDelete(e, form) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Booking?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3 px-4',
                        cancelButton: 'btn btn-secondary px-4'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm("Delete this booking? This action cannot be undone!")) {
                    form.submit();
                }
            }
        }
    </script>
</body>
</html>
