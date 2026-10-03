<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';
require_once '../include/email_core_functions.php';

// Auto-ensure admin_notes column exists in ticket_bookings table
try {
    $col_check = $pdo->query("SHOW COLUMNS FROM ticket_bookings LIKE 'admin_notes'")->fetch();
    if (!$col_check) {
        $pdo->exec("ALTER TABLE ticket_bookings ADD COLUMN admin_notes TEXT DEFAULT NULL AFTER status");
    }
} catch (Exception $e) {
    // Ignore if already exists or permission issues
}

$msg = '';
$msg_type = '';

// Handle actions (Approve / Reject)
if (isset($_POST['action']) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    $admin_notes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';
    
    try {
        // Fetch booking details
        $fetch_stmt = $pdo->prepare("
            SELECT tb.*, t.event_name, t.price, t.event_date, t.venue, t.ticket_id as t_id, t.image_path
            FROM ticket_bookings tb 
            JOIN tickets t ON tb.ticket_id = t.id 
            WHERE tb.id = ?
        ");
        $fetch_stmt->execute([$booking_id]);
        $booking = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            if ($action === 'approve') {
                // Check if already approved to prevent duplicate increments/decrements
                if ($booking['status'] !== 'Approved') {
                    // Fetch ticket available quantity
                    $t_stmt = $pdo->prepare("SELECT available_qty, status FROM tickets WHERE id = ?");
                    $t_stmt->execute([$booking['ticket_id']]);
                    $tkt = $t_stmt->fetch();
                    
                    if ($tkt && $tkt['available_qty'] > 0) {
                        $pdo->beginTransaction();
                        
                        // Update booking status
                        $up_stmt = $pdo->prepare("UPDATE ticket_bookings SET status = 'Approved', admin_notes = ? WHERE id = ?");
                        $up_stmt->execute([$admin_notes, $booking_id]);
                        
                        // Decrement ticket availability
                        $new_avail = $tkt['available_qty'] - 1;
                        $new_status = ($new_avail <= 0) ? 'Sold Out' : $tkt['status'];
                        
                        $up_tkt = $pdo->prepare("UPDATE tickets SET available_qty = ?, status = ? WHERE id = ?");
                        $up_tkt->execute([$new_avail, $new_status, $booking['ticket_id']]);
                        
                        $pdo->commit();
                        
                        $msg = "Booking Approved successfully! Notification email is being sent.";
                        $msg_type = "success";
                        
                        // Send Email Notification
                        $admin_email = $site_settings['contact_email'] ?? "booking@vipcelebrity.com";
                        $subject = "Ticket Booking Approved - Ref: " . $booking['booking_reference'];
                        $client_name = !empty($booking['user_name']) ? $booking['user_name'] : (!empty($booking['buyer_name']) ? $booking['buyer_name'] : 'Customer');
                        $client_email = !empty($booking['user_email']) ? $booking['user_email'] : (!empty($booking['buyer_email']) ? $booking['buyer_email'] : '');
                        
                        // Build printable ticket link for email
                        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                        $ticket_link = $base_url . "/view_ticket.php?ref=" . urlencode($booking['booking_reference']);
                        
                        $body = "
                            <h2 style='color: #28a745; margin-top: 0;'>Ticket Approved! 🎉</h2>
                            <p>Dear {$client_name},</p>
                            <p>We are pleased to inform you that your ticket booking for <strong>{$booking['event_name']}</strong> has been approved!</p>
                            <p>Your unique Ticket ID is <strong>{$booking['t_id']}</strong>.</p>
                            <p>Please click the button below to view and print your premium ticket with its secure QR code:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$ticket_link}' style='background: #c29b57; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: bold;'>VIEW & PRINT TICKET</a>
                            </div>
                            <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px;'>
                                <tr><td><strong>Booking Reference:</strong></td><td>{$booking['booking_reference']}</td></tr>
                                <tr><td><strong>Ticket ID:</strong></td><td>{$booking['t_id']}</td></tr>
                                <tr><td><strong>Venue / Location:</strong></td><td>{$booking['venue']}</td></tr>
                                <tr><td><strong>Date & Time:</strong></td><td>{$booking['event_date']}</td></tr>
                                <tr><td><strong>Price Paid:</strong></td><td>$" . number_format($booking['price'], 2) . "</td></tr>
                            </table>
                            <p>Show the QR code on your ticket at the venue entrance for validation.</p>
                        ";
                        
                        $smtp_config_missing = false;
                        if (!empty($client_email)) {
                            try {
                                send_email_notification($site_settings, $client_email, $subject, $body, $smtp_config_missing);
                            } catch (\Throwable $e) {
                                error_log("Failed to send ticket approval email: " . $e->getMessage());
                            }
                        }
                        
                    } else {
                        $msg = "Cannot approve booking. The ticket is sold out!";
                        $msg_type = "danger";
                    }
                }
            } elseif ($action === 'reject') {
                $pdo->beginTransaction();
                
                // If it was approved, increment back the ticket availability
                if ($booking['status'] === 'Approved') {
                    $t_stmt = $pdo->prepare("SELECT available_qty, total_qty, status FROM tickets WHERE id = ?");
                    $t_stmt->execute([$booking['ticket_id']]);
                    $tkt = $t_stmt->fetch();
                    if ($tkt) {
                        $new_avail = min($tkt['total_qty'], $tkt['available_qty'] + 1);
                        $new_status = ($new_avail > 0 && $tkt['status'] === 'Sold Out') ? 'Available' : $tkt['status'];
                        $up_tkt = $pdo->prepare("UPDATE tickets SET available_qty = ?, status = ? WHERE id = ?");
                        $up_tkt->execute([$new_avail, $new_status, $booking['ticket_id']]);
                    }
                }
                
                // Update status
                $up_stmt = $pdo->prepare("UPDATE ticket_bookings SET status = 'Rejected', admin_notes = ? WHERE id = ?");
                $up_stmt->execute([$admin_notes, $booking_id]);
                
                $pdo->commit();
                
                $msg = "Booking Rejected successfully.";
                $msg_type = "warning";
                
                // Send Email Notification
                $client_name = !empty($booking['user_name']) ? $booking['user_name'] : (!empty($booking['buyer_name']) ? $booking['buyer_name'] : 'Customer');
                $client_email = !empty($booking['user_email']) ? $booking['user_email'] : (!empty($booking['buyer_email']) ? $booking['buyer_email'] : '');
                $subject = "Ticket Booking Update - Ref: " . $booking['booking_reference'];
                $body = "
                    <h2 style='color: #dc3545; margin-top: 0;'>Booking Update</h2>
                    <p>Dear {$client_name},</p>
                    <p>We regret to inform you that your ticket booking request for <strong>{$booking['event_name']}</strong> has been declined or cancelled.</p>
                    <p><strong>Booking Reference:</strong> {$booking['booking_reference']}</p>
                    " . (!empty($admin_notes) ? "<p><strong>Notes:</strong> " . htmlspecialchars($admin_notes) . "</p>" : "") . "
                    <p>If you have any questions or require assistance, please contact our support team.</p>
                ";
                $smtp_config_missing = false;
                if (!empty($client_email)) {
                    try {
                        send_email_notification($site_settings, $client_email, $subject, $body, $smtp_config_missing);
                    } catch (\Throwable $e) {
                        error_log("Failed to send ticket rejection email: " . $e->getMessage());
                    }
                }
            } elseif ($action === 'delete') {
                $pdo->beginTransaction();
                
                // If it was approved, increment back the ticket availability
                if ($booking['status'] === 'Approved') {
                    $t_stmt = $pdo->prepare("SELECT available_qty, total_qty, status FROM tickets WHERE id = ?");
                    $t_stmt->execute([$booking['ticket_id']]);
                    $tkt = $t_stmt->fetch();
                    if ($tkt) {
                        $new_avail = min($tkt['total_qty'], $tkt['available_qty'] + 1);
                        $new_status = ($new_avail > 0 && $tkt['status'] === 'Sold Out') ? 'Available' : $tkt['status'];
                        $up_tkt = $pdo->prepare("UPDATE tickets SET available_qty = ?, status = ? WHERE id = ?");
                        $up_tkt->execute([$new_avail, $new_status, $booking['ticket_id']]);
                    }
                }
                
                // Delete the booking record
                $del_stmt = $pdo->prepare("DELETE FROM ticket_bookings WHERE id = ?");
                $del_stmt->execute([$booking_id]);
                
                $pdo->commit();
                
                $msg = "Booking deleted successfully.";
                $msg_type = "success";
            }
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $msg = "Error updating booking: " . $e->getMessage();
        $msg_type = "danger";
    }
}

// Filters & Pagination
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
$params = [];

if ($status_filter !== '') {
    $where_clauses[] = "tb.status = :status";
    $params[':status'] = $status_filter;
}
if ($search !== '') {
    $where_clauses[] = "(tb.booking_reference LIKE :search OR tb.user_name LIKE :search OR tb.user_email LIKE :search OR t.event_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Pagination setup
$limit = 4;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Fetch count
$count_sql = "SELECT COUNT(*) FROM ticket_bookings tb JOIN tickets t ON tb.ticket_id = t.id $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch bookings
$sql = "
    SELECT tb.*, t.event_name, t.price, t.event_date, t.venue, t.ticket_id as t_id, t.image_path
    FROM ticket_bookings tb 
    JOIN tickets t ON tb.ticket_id = t.id 
    $where_sql 
    ORDER BY tb.id DESC 
    LIMIT :offset, :limit
";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Ticket Bookings - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .btn-action-container {
            display: inline-flex !important;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }
        .btn-action-container .btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 32px !important;
            height: 32px !important;
            min-height: 32px !important;
            padding: 0 !important;
            border-radius: 6px !important;
        }
    </style>
</head>
<body>
    <?php include "nav.php" ?>
    <div id="wrapper">
        <?php include "header.php" ?>
        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bolder mb-0">Ticket Bookings Portal</h4>
                </div>

                <?php if (!empty($msg)): ?>
                    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                        <?php echo htmlspecialchars($msg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters & Search -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <form method="GET" action="ticket_bookings.php" class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted">Search Bookings</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control bg-light border-0" placeholder="Ref, Customer Name, Email, Event..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Booking Status</label>
                                <select name="status" class="form-select bg-light border-0">
                                    <option value="">-- All Statuses --</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-grid">
                                <button type="submit" class="btn btn-gold fw-bold">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings List - Desktop View -->
                <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius: 12px; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-dark text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th scope="col" class="ps-4">Booking Ref</th>
                                    <th scope="col">Event / Ticket ID</th>
                                    <th scope="col">Customer Details</th>
                                    <th scope="col">Date Booked</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($bookings) > 0): ?>
                                    <?php foreach ($bookings as $b): 
                                        $badge_class = 'bg-secondary';
                                        if ($b['status'] === 'Approved') $badge_class = 'bg-success';
                                        if ($b['status'] === 'Rejected') $badge_class = 'bg-danger';
                                        if ($b['status'] === 'Pending') $badge_class = 'bg-warning text-dark';
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">
                                                <span class="booking-ref-text"><?php echo htmlspecialchars($b['booking_reference']); ?></span>
                                                <button class="btn btn-link btn-sm p-0 ms-1 text-muted copy-btn" data-clipboard-text="<?php echo htmlspecialchars($b['booking_reference']); ?>" title="Copy Reference">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <div class="fw-bold" title="<?php echo htmlspecialchars($b['event_name']); ?>"><?php echo htmlspecialchars(mb_strimwidth($b['event_name'], 0, 22, '...')); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($b['t_id']); ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($b['user_name']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($b['user_email']); ?> | <?php echo htmlspecialchars($b['user_phone']); ?></div>
                                            </td>
                                            <td class="small text-muted"><?php echo date('M d, Y, g:i A', strtotime($b['created_at'])); ?></td>
                                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($b['status']); ?></span></td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-action-container">
                                                    <button type="button" class="btn btn-sm btn-outline-dark" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#bookingDetailsModal" 
                                                        data-id="<?php echo $b['id']; ?>"
                                                        data-ref="<?php echo htmlspecialchars($b['booking_reference']); ?>"
                                                        data-name="<?php echo htmlspecialchars($b['user_name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($b['user_email']); ?>"
                                                        data-phone="<?php echo htmlspecialchars($b['user_phone']); ?>"
                                                        data-event="<?php echo htmlspecialchars($b['event_name']); ?>"
                                                        data-price="<?php echo number_format($b['price'], 2); ?>"
                                                        data-date="<?php echo htmlspecialchars($b['event_date']); ?>"
                                                        data-venue="<?php echo htmlspecialchars($b['venue']); ?>"
                                                        data-t-id="<?php echo htmlspecialchars($b['t_id']); ?>"
                                                        data-status="<?php echo htmlspecialchars($b['status']); ?>"
                                                        data-notes="<?php echo htmlspecialchars($b['admin_notes'] ?? ''); ?>"
                                                        title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <form action="ticket_bookings.php" method="POST" class="d-inline" onsubmit="confirmAction(event, this, 'Are you sure you want to permanently delete this booking? This action cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Booking"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No ticket bookings found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bookings List - Mobile Card View -->
                <div class="d-block d-md-none">
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $b): 
                            $badge_class = 'bg-secondary';
                            if ($b['status'] === 'Approved') $badge_class = 'bg-success';
                            if ($b['status'] === 'Rejected') $badge_class = 'bg-danger';
                            if ($b['status'] === 'Pending') $badge_class = 'bg-warning text-dark';
                        ?>
                            <div class="card border-0 shadow-sm mb-3 p-3 bg-white" style="border-radius: 12px; border-left: 5px solid <?php echo $b['status'] === 'Approved' ? '#28a745' : ($b['status'] === 'Rejected' ? '#dc3545' : '#ffc107'); ?> !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">
                                        <span class="booking-ref-text"><?php echo htmlspecialchars($b['booking_reference']); ?></span>
                                        <button class="btn btn-link btn-sm p-0 ms-1 text-muted copy-btn" data-clipboard-text="<?php echo htmlspecialchars($b['booking_reference']); ?>" title="Copy Reference">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </span>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($b['status']); ?></span>
                                </div>
                                <div class="mb-2">
                                    <div class="fw-bold text-dark" title="<?php echo htmlspecialchars($b['event_name']); ?>"><?php echo htmlspecialchars(mb_strimwidth($b['event_name'], 0, 25, '...')); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($b['t_id']); ?></small>
                                </div>
                                <div class="mb-2 border-top pt-2">
                                    <div class="small"><strong>Client:</strong> <?php echo htmlspecialchars($b['user_name']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($b['user_email']); ?> | <?php echo htmlspecialchars($b['user_phone']); ?></div>
                                    <div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i><?php echo date('M d, Y, g:i A', strtotime($b['created_at'])); ?></div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#bookingDetailsModal" 
                                        data-id="<?php echo $b['id']; ?>"
                                        data-ref="<?php echo htmlspecialchars($b['booking_reference']); ?>"
                                        data-name="<?php echo htmlspecialchars($b['user_name']); ?>"
                                        data-email="<?php echo htmlspecialchars($b['user_email']); ?>"
                                        data-phone="<?php echo htmlspecialchars($b['user_phone']); ?>"
                                        data-event="<?php echo htmlspecialchars($b['event_name']); ?>"
                                        data-price="<?php echo number_format($b['price'], 2); ?>"
                                        data-date="<?php echo htmlspecialchars($b['event_date']); ?>"
                                        data-venue="<?php echo htmlspecialchars($b['venue']); ?>"
                                        data-t-id="<?php echo htmlspecialchars($b['t_id']); ?>"
                                        data-status="<?php echo htmlspecialchars($b['status']); ?>"
                                        data-notes="<?php echo htmlspecialchars($b['admin_notes'] ?? ''); ?>"
                                        title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <form action="ticket_bookings.php" method="POST" class="d-inline" onsubmit="confirmAction(event, this, 'Are you sure you want to permanently delete this booking? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Booking"><i class="bi bi-trash me-1"></i>Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm p-4 text-center text-muted" style="border-radius: 12px;">
                            No ticket bookings found.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5 mb-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="ticket_bookings.php?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
            <?php include "footer.php" ?>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #8A0000 0%, #B00000 100%); border-bottom: 3px solid var(--secondary, #FFC107);">
                    <h5 class="modal-title fw-bold" id="bookingDetailsModalLabel">Booking Reference: <span id="modal_ref" class="text-gold"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="ticket_bookings.php" method="POST">
                    <input type="hidden" name="booking_id" id="modal_booking_id">
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left: Event details -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-ticket-detailed me-2 text-gold"></i>Ticket Details</h6>
                                <table class="table table-borderless table-sm small">
                                    <tr><td class="text-muted" width="120">Event / Star:</td><td id="modal_event" class="fw-bold text-dark"></td></tr>
                                    <tr><td class="text-muted">Ticket ID:</td><td id="modal_t_id" class="fw-bold text-dark"></td></tr>
                                    <tr><td class="text-muted">Price:</td><td class="fw-bold text-gold">$<span id="modal_price"></span></td></tr>
                                    <tr><td class="text-muted">Date & Time:</td><td id="modal_date" class="text-dark"></td></tr>
                                    <tr><td class="text-muted">Venue / Venue:</td><td id="modal_venue" class="text-dark"></td></tr>
                                </table>
                                
                                <div class="text-center mt-4">
                                    <div class="p-3 bg-light rounded-3 d-inline-block shadow-sm">
                                        <!-- QR Code Preview link -->
                                        <img id="modal_qr" src="" alt="Ticket QR Code" style="max-height: 140px;">
                                        <div class="small text-muted mt-1 fw-bold">Dynamic Validation QR</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right: Customer details & action -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-person me-2 text-gold"></i>Customer Details</h6>
                                <table class="table table-borderless table-sm small mb-3">
                                    <tr><td class="text-muted" width="120">Full Name:</td><td id="modal_name" class="fw-bold text-dark"></td></tr>
                                    <tr><td class="text-muted">Email:</td><td id="modal_email" class="text-dark"></td></tr>
                                    <tr><td class="text-muted">Phone:</td><td id="modal_phone" class="text-dark"></td></tr>
                                    <tr><td class="text-muted">Status:</td><td><span id="modal_status_badge" class="badge"></span></td></tr>
                                </table>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Admin Notes / Remarks</label>
                                    <textarea name="admin_notes" id="modal_notes" class="form-control" rows="3" placeholder="Enter notes visible in notification emails..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top justify-content-between">
                        <div>
                            <button type="button" class="btn btn-secondary px-3 py-2 fw-bold" data-bs-dismiss="modal">Close</button>
                        </div>
                        <div id="modal_actions">
                            <button type="submit" name="action" value="reject" class="btn btn-outline-danger px-4 py-2 fw-bold me-2"><i class="bi bi-x-circle me-1"></i>Reject / Cancel</button>
                            <button type="submit" name="action" value="approve" class="btn btn-gold px-4 py-2 fw-bold"><i class="bi bi-check-circle me-1"></i>Approve Booking</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <!-- Modal script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bookingModal = document.getElementById('bookingDetailsModal');
            bookingModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const ref = button.getAttribute('data-ref');
                const name = button.getAttribute('data-name');
                const email = button.getAttribute('data-email');
                const phone = button.getAttribute('data-phone');
                const eventName = button.getAttribute('data-event');
                const price = button.getAttribute('data-price');
                const date = button.getAttribute('data-date');
                const venue = button.getAttribute('data-venue');
                const tId = button.getAttribute('data-t-id');
                const status = button.getAttribute('data-status');
                const notes = button.getAttribute('data-notes');
                
                document.getElementById('modal_booking_id').value = id;
                document.getElementById('modal_ref').textContent = ref;
                document.getElementById('modal_name').textContent = name;
                document.getElementById('modal_email').textContent = email;
                document.getElementById('modal_phone').textContent = phone;
                document.getElementById('modal_event').textContent = eventName;
                document.getElementById('modal_price').textContent = price;
                document.getElementById('modal_date').textContent = date;
                document.getElementById('modal_venue').textContent = venue;
                document.getElementById('modal_t_id').textContent = tId;
                document.getElementById('modal_notes').value = notes;

                // QR code generation using qrserver API
                const qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(ref + "|" + tId + "|" + name);
                document.getElementById('modal_qr').src = qrUrl;

                // Status badge
                const statusBadge = document.getElementById('modal_status_badge');
                statusBadge.textContent = status;
                statusBadge.className = 'badge';
                if (status === 'Approved') {
                    statusBadge.classList.add('bg-success');
                } else if (status === 'Rejected') {
                    statusBadge.classList.add('bg-danger');
                } else if (status === 'Pending') {
                    statusBadge.classList.add('bg-warning', 'text-dark');
                } else {
                    statusBadge.classList.add('bg-secondary');
                }

                // Show/hide action buttons depending on status
                const actionContainer = document.getElementById('modal_actions');
                if (status === 'Approved' || status === 'Rejected' || status === 'Cancelled') {
                    actionContainer.style.display = 'none';
                } else {
                    actionContainer.style.display = 'block';
                }
            });
        });
    </script>

    <!-- Copy reference script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.copy-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const text = this.getAttribute('data-clipboard-text');
                    navigator.clipboard.writeText(text).then(() => {
                        const icon = this.querySelector('i');
                        icon.className = 'bi bi-check-lg text-success';
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true,
                            icon: 'success',
                            title: 'Reference copied: ' + text
                        });
                        
                        setTimeout(() => {
                            icon.className = 'bi bi-clipboard';
                        }, 1500);
                    });
                });
            });
        });
    </script>
</body>
</html>
