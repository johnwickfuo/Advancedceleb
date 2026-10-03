<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

// Handle delete action
$msg = '';
$msg_type = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        $msg = "Ticket created successfully.";
        $msg_type = "success";
    } elseif ($_GET['status'] === 'updated') {
        $msg = "Ticket updated successfully.";
        $msg_type = "success";
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        // Fetch ticket details first to remove its image
        $fetch_stmt = $pdo->prepare("SELECT image_path FROM tickets WHERE id = ?");
        $fetch_stmt->execute([$id]);
        $tkt = $fetch_stmt->fetch();
        if ($tkt) {
            $img = '../' . $tkt['image_path'];
            if (!empty($tkt['image_path']) && file_exists($img) && is_file($img)) {
                @unlink($img);
            }
            
            // Delete associated bookings
            $del_bookings = $pdo->prepare("DELETE FROM ticket_bookings WHERE ticket_id = ?");
            $del_bookings->execute([$id]);
            
            // Delete the ticket
            $del_stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
            $del_stmt->execute([$id]);
            
            $msg = "Ticket and all associated bookings deleted successfully.";
            $msg_type = "success";
        }
    } catch (PDOException $e) {
        $msg = "Error deleting ticket: " . $e->getMessage();
        $msg_type = "danger";
    }
}

// Stats queries
$total_tkt_types = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$total_available_qty = $pdo->query("SELECT SUM(available_qty) FROM tickets WHERE status = 'Available'")->fetchColumn() ?: 0;
$total_booked_qty = $pdo->query("SELECT COUNT(*) FROM ticket_bookings WHERE status = 'Approved'")->fetchColumn();

// Filter & Search configuration
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(event_name LIKE :search OR venue LIKE :search OR ticket_id LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($category !== '') {
    $where_clauses[] = "category = :category";
    $params[':category'] = $category;
}
if ($status !== '') {
    $where_clauses[] = "status = :status";
    $params[':status'] = $status;
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
$count_sql = "SELECT COUNT(*) FROM tickets $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch records
$sql = "SELECT * FROM tickets $where_sql ORDER BY id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [
    "Corporate Event", "Birthday Party", "Wedding", "Fan Card", "Concert",
    "Meet and Greet", "Private Performance", "Brand Promotion", "Charity Event",
    "Product Launch"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Manage Tickets - Admin Dashboard</title>
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
        .btn-outline-info {
            background: transparent !important;
            color: #0dcaf0 !important;
            border: 2px solid #0dcaf0 !important;
        }
        .btn-outline-info:hover {
            background: #0dcaf0 !important;
            color: #fff !important;
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
                    <h4 class="fw-bolder mb-0">Ticket Management</h4>
                    <a href="create_ticket.php" class="btn btn-gold px-4 py-2 fw-bold" style="border-radius: 8px;"><i class="bi bi-plus-lg me-2"></i>Create Ticket</a>
                </div>

                <?php if (!empty($msg)): ?>
                    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                        <?php echo htmlspecialchars($msg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-3 bg-white" style="border-left: 5px solid #FFC107 !important; border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3 text-warning">
                                    <i class="bi bi-ticket-perforated-fill fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small fw-bold">Ticket Types</h6>
                                    <h4 class="mb-0 fw-bold"><?php echo $total_tkt_types; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-3 bg-white" style="border-left: 5px solid #28a745 !important; border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3 text-success">
                                    <i class="bi bi-check-circle-fill fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small fw-bold">Available Qty (Active)</h6>
                                    <h4 class="mb-0 fw-bold"><?php echo $total_available_qty; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-3 bg-white" style="border-left: 5px solid #17a2b8 !important; border-radius: 12px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3 text-info">
                                    <i class="bi bi-people-fill fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted mb-1 small fw-bold">Bookings Approved</h6>
                                    <h4 class="mb-0 fw-bold"><?php echo $total_booked_qty; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <form method="GET" action="tickets_manager.php" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Search Tickets</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control bg-light border-0" placeholder="ID, Name, Venue..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Category</label>
                                <select name="category" class="form-select bg-light border-0">
                                    <option value="">-- All Categories --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                    <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Status</label>
                                <select name="status" class="form-select bg-light border-0">
                                    <option value="">-- All Statuses --</option>
                                    <option value="Available" <?php echo $status === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="Sold Out" <?php echo $status === 'Sold Out' ? 'selected' : ''; ?>>Sold Out</option>
                                    <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Inactive" <?php echo $status === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-gold fw-bold">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tickets List - Desktop View -->
                <div class="card border-0 shadow-sm d-none d-md-block" style="border-radius: 12px; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-dark text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th scope="col" class="ps-4">Event Details</th>
                                    <th scope="col">Schedule & Venue</th>
                                    <th scope="col">Price</th>
                                    <th scope="col" class="text-center">Available / Total</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($tickets) > 0): ?>
                                    <?php foreach ($tickets as $t): 
                                        $badge_class = 'bg-secondary';
                                        if ($t['status'] === 'Available') $badge_class = 'bg-success';
                                        if ($t['status'] === 'Sold Out') $badge_class = 'bg-danger';
                                        if ($t['status'] === 'Pending') $badge_class = 'bg-warning text-dark';
                                    ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="../<?php echo htmlspecialchars(!empty($t['image_path']) ? $t['image_path'] : 'assets/img/avater.jpg'); ?>" alt="Event image" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='../assets/img/avater.jpg';">
                                                    <div>
                                                        <div class="fw-bold text-dark" title="<?php echo htmlspecialchars($t['event_name']); ?>"><?php echo htmlspecialchars(mb_strimwidth($t['event_name'], 0, 20, '...')); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($t['ticket_id']); ?> | <?php echo htmlspecialchars($t['category']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($t['event_date']); ?></div>
                                                <small class="text-muted" title="<?php echo htmlspecialchars($t['venue']); ?>"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars(mb_strimwidth($t['venue'], 0, 25, '...')); ?></small>
                                            </td>
                                            <td class="fw-bold text-gold"><?php echo format_currency($t['price']); ?></td>
                                            <td class="text-center fw-bold">
                                                <span class="text-success"><?php echo $t['available_qty']; ?></span> / <span class="text-secondary"><?php echo $t['total_qty']; ?></span>
                                            </td>
                                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($t['status']); ?></span></td>
                                            <td class="pe-4 text-end">
                                                <div class="btn-action-container">
                                                    <a href="print_ticket.php?id=<?php echo $t['id']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Print Ticket Layout"><i class="bi bi-printer"></i></a>
                                                    <a href="edit_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Ticket"><i class="bi bi-pencil-square"></i></a>
                                                    <form action="tickets_manager.php" method="POST" class="d-inline" onsubmit="confirmAction(event, this, 'Are you sure you want to permanently delete this ticket and all its associated bookings? This action cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Ticket"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No tickets found. Create a new one to get started!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tickets List - Mobile Card View -->
                <div class="d-block d-md-none">
                    <?php if (count($tickets) > 0): ?>
                        <?php foreach ($tickets as $t): 
                            $badge_class = 'bg-secondary';
                            if ($t['status'] === 'Available') $badge_class = 'bg-success';
                            if ($t['status'] === 'Sold Out') $badge_class = 'bg-danger';
                            if ($t['status'] === 'Pending') $badge_class = 'bg-warning text-dark';
                        ?>
                            <div class="card border-0 shadow-sm mb-3 p-3 bg-white" style="border-radius: 12px; border-left: 5px solid <?php echo $t['status'] === 'Available' ? '#28a745' : ($t['status'] === 'Sold Out' ? '#dc3545' : '#ffc107'); ?> !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($t['ticket_id']); ?></span>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($t['status']); ?></span>
                                </div>
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <img src="../<?php echo htmlspecialchars(!empty($t['image_path']) ? $t['image_path'] : 'assets/img/avater.jpg'); ?>" alt="Event image" class="rounded shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='../assets/img/avater.jpg';">
                                    <div>
                                        <div class="fw-bold text-dark" title="<?php echo htmlspecialchars($t['event_name']); ?>"><?php echo htmlspecialchars(mb_strimwidth($t['event_name'], 0, 25, '...')); ?></div>
                                        <span class="badge bg-light text-dark border small mt-1"><?php echo htmlspecialchars($t['category']); ?></span>
                                        <div class="fw-bold text-gold mt-1"><?php echo format_currency($t['price']); ?></div>
                                    </div>
                                </div>
                                <div class="mb-2 border-top pt-2 small text-muted">
                                    <div><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($t['venue']); ?></div>
                                    <div class="mt-1"><i class="bi bi-calendar-event me-1"></i><?php echo htmlspecialchars($t['event_date']); ?></div>
                                    <div class="mt-1"><i class="bi bi-ticket-perforated me-1"></i>Available: <span class="text-success fw-bold"><?php echo $t['available_qty']; ?></span> / <?php echo $t['total_qty']; ?></div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                                    <a href="print_ticket.php?id=<?php echo $t['id']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Print Ticket Layout"><i class="bi bi-printer me-1"></i>Print</a>
                                    <a href="edit_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Ticket"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                                    <form action="tickets_manager.php" method="POST" class="d-inline" onsubmit="confirmAction(event, this, 'Are you sure you want to permanently delete this ticket and all its associated bookings? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Ticket"><i class="bi bi-trash me-1"></i>Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm p-4 text-center text-muted" style="border-radius: 12px;">
                            No tickets found.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5 mb-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="tickets_manager.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
            <?php include "footer.php" ?>
            <?php if (!empty($msg)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '<?php echo $msg_type === 'success' ? 'Success!' : 'Error!'; ?>',
                        text: '<?php echo htmlspecialchars($msg); ?>',
                        icon: '<?php echo $msg_type === 'success' ? 'success' : 'error'; ?>',
                        confirmButtonColor: '#B00000'
                    });
                });
            </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
