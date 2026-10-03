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

// Handle Add/Edit Celebrity
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'save_celebrity') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $name_parts = explode(' ', $name);
            $name = trim($name_parts[0]);
        }
        $booking_price = trim($_POST['booking_price'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_verified = isset($_POST['is_verified']) ? 1 : 0;
        
        $profile_picture = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/celebrities/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid('cel_') . '_' . basename($_FILES['profile_picture']['name']);
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $filename)) {
                $profile_picture = 'uploads/celebrities/' . $filename;
            }
        }
        
        if (empty($name) || empty($booking_price)) {
            $error_msg = "Name and Booking Price are required.";
        } else {
            try {
                if ($id > 0) {
                    // Update
                    if ($profile_picture) {
                        $stmt = $pdo->prepare("UPDATE celebrities SET name=?, booking_price=?, description=?, profile_picture=?, is_featured=?, is_verified=? WHERE id=?");
                        $stmt->execute([$name, $booking_price, $description, $profile_picture, $is_featured, $is_verified, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE celebrities SET name=?, booking_price=?, description=?, is_featured=?, is_verified=? WHERE id=?");
                        $stmt->execute([$name, $booking_price, $description, $is_featured, $is_verified, $id]);
                    }
                    $success_msg = "Celebrity updated successfully.";
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO celebrities (name, booking_price, description, profile_picture, is_featured, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $booking_price, $description, $profile_picture, $is_featured, $is_verified]);
                    $success_msg = "Celebrity added successfully.";
                }
            } catch (PDOException $e) {
                $error_msg = "Database error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_celebrity') {
        $id = (int)$_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM celebrities WHERE id=?");
            $stmt->execute([$id]);
            $success_msg = "Celebrity deleted successfully.";
        } catch (PDOException $e) {
            $error_msg = "Error deleting celebrity: " . $e->getMessage();
        }
    }
}

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Count total records
$total_stmt = $pdo->query("SELECT COUNT(*) FROM celebrities");
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch Celebrities
$stmt = $pdo->prepare("SELECT * FROM celebrities ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Celebrities - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Platform'); ?></title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .celebrity-img { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; }
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
    <?php include 'nav.php'; ?>
    <div id="wrapper">
        <?php include 'header.php'; ?>
        
        <div class="main-content-wrapper p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-person-star text-primary me-2"></i>Manage Celebrities</h2>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#celebrityModal" onclick="resetForm()">
                    <i class="bi bi-plus-lg me-1"></i> Add Celebrity
                </button>
            </div>
            
            <?php if($success_msg): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success!',
                        text: '<?php echo htmlspecialchars(strip_tags($success_msg)); ?>',
                        icon: 'success',
                        confirmButtonColor: '#B00000'
                    });
                });
                </script>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error!',
                        text: '<?php echo htmlspecialchars(strip_tags($error_msg)); ?>',
                        icon: 'error',
                        confirmButtonColor: '#B00000'
                    });
                });
                </script>
            <?php endif; ?>
            
            <!-- Celebrities List - Desktop View -->
            <div class="card card-custom d-none d-md-block">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Celebrity</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($celebrities) > 0): ?>
                                    <?php foreach ($celebrities as $cel): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($cel['profile_picture']): ?>
                                                        <img src="../<?php echo htmlspecialchars($cel['profile_picture']); ?>" class="celebrity-img me-3" onerror="this.outerHTML='<div class=\'celebrity-img bg-secondary text-white d-flex align-items-center justify-content-center me-3\'><i class=\'bi bi-person\'></i></div>'">
                                                    <?php else: ?>
                                                        <div class="celebrity-img bg-secondary text-white d-flex align-items-center justify-content-center me-3">
                                                            <i class="bi bi-person"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($cel['name']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo format_currency($cel['booking_price']); ?></td>
                                            <td>
                                                <?php if($cel['is_verified']): ?><span class="badge bg-primary">Verified</span><?php endif; ?>
                                                <?php if($cel['is_featured']): ?><span class="badge bg-success">Featured</span><?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-action-container">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick='editCelebrity(<?php echo json_encode($cel); ?>)' title="Edit Celebrity">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                     <form method="post" class="d-inline" onsubmit="confirmAction(event, this, 'Are you sure you want to permanently delete this celebrity performer? This action cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete_celebrity">
                                                        <input type="hidden" name="id" value="<?php echo $cel['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Celebrity"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No celebrities found. Add one to get started.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Celebrities List - Mobile Card View -->
            <div class="d-block d-md-none">
                <?php if (count($celebrities) > 0): ?>
                    <?php foreach ($celebrities as $cel): ?>
                        <div class="card border-0 shadow-sm mb-3 p-3 bg-white" style="border-radius: 12px; border-left: 5px solid <?php echo $cel['is_featured'] ? '#28a745' : '#6c757d'; ?> !important;">
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($cel['profile_picture']): ?>
                                    <img src="../<?php echo htmlspecialchars($cel['profile_picture']); ?>" class="celebrity-img" style="width: 55px; height: 55px; object-fit: cover;" onerror="this.outerHTML='<div class=\'celebrity-img bg-secondary text-white d-flex align-items-center justify-content-center\' style=\'width: 55px; height: 55px;\'><i class=\'bi bi-person\'></i></div>'">
                                <?php else: ?>
                                    <div class="celebrity-img bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($cel['name']); ?></div>
                                            <div class="fw-bold text-gold mt-1"><?php echo format_currency($cel['booking_price']); ?></div>
                                        </div>
                                        <div class="d-flex flex-column gap-1 align-items-end">
                                            <?php if($cel['is_verified']): ?><span class="badge bg-primary">Verified</span><?php endif; ?>
                                            <?php if($cel['is_featured']): ?><span class="badge bg-success">Featured</span><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-2 mt-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick='editCelebrity(<?php echo json_encode($cel); ?>)' title="Edit Celebrity">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                <form method="post" class="d-inline" onsubmit="confirmAction(event, this, 'Are you sure you want to permanently delete this celebrity performer? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_celebrity">
                                    <input type="hidden" name="id" value="<?php echo $cel['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Celebrity"><i class="bi bi-trash me-1"></i>Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card border-0 shadow-sm p-4 text-center text-muted" style="border-radius: 12px;">
                        No celebrities found. Add one to get started.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5 mb-4">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    
    <!-- Celebrity Modal -->
    <div class="modal fade" id="celebrityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-4 shadow">
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title" id="modalTitle">Add Celebrity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save_celebrity">
                        <input type="hidden" name="id" id="cel_id" value="0">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="cel_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Booking Price (<?php echo htmlspecialchars($site_settings['currency_symbol'] ?? '$'); ?>)</label>
                            <input type="number" step="0.01" class="form-control" name="booking_price" id="cel_price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description / About</label>
                            <textarea class="form-control" name="description" id="cel_desc" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                            <small class="text-muted d-block mt-1">Leave empty to keep existing picture when editing.</small>
                        </div>
                        
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_verified" id="cel_verified" value="1">
                            <label class="form-check-label" for="cel_verified">Verified Badge</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="cel_featured" value="1">
                            <label class="form-check-label" for="cel_featured">Feature on Homepage</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Celebrity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('modalTitle').innerText = 'Add Celebrity';
            document.getElementById('cel_id').value = '0';
            document.getElementById('cel_name').value = '';
            document.getElementById('cel_price').value = '';
            document.getElementById('cel_desc').value = '';
            document.getElementById('cel_verified').checked = false;
            document.getElementById('cel_featured').checked = false;
        }
        
        function editCelebrity(cel) {
            document.getElementById('modalTitle').innerText = 'Edit Celebrity';
            document.getElementById('cel_id').value = cel.id;
            document.getElementById('cel_name').value = cel.name;
            document.getElementById('cel_price').value = cel.booking_price;
            document.getElementById('cel_desc').value = cel.description;
            document.getElementById('cel_verified').checked = cel.is_verified == 1;
            document.getElementById('cel_featured').checked = cel.is_featured == 1;
            
            var modal = new bootstrap.Modal(document.getElementById('celebrityModal'));
            modal.show();
        }
    </script>
</body>
</html>
