<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

$error = '';
$success = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tickets_manager.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch current ticket details
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$tkt = $stmt->fetch();

if (!$tkt) {
    header("Location: tickets_manager.php");
    exit;
}

$categories = [
    "Corporate Event", "Birthday Party", "Wedding", "Fan Card", "Concert",
    "Meet and Greet", "Private Performance", "Brand Promotion", "Charity Event",
    "Product Launch"
];

// Check if category is standard or custom
$is_custom_cat = !in_array($tkt['category'], $categories);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = trim($_POST['event_name']);
    $price = (float)$_POST['price'];
    $event_date = trim($_POST['event_date']);
    $venue = trim($_POST['venue']);
    $description = trim($_POST['description']);
    $total_qty = (int)$_POST['total_qty'];
    $status = trim($_POST['status']);
    
    // Determine category
    $category_select = trim($_POST['category_select']);
    $category_custom = trim($_POST['category_custom']);
    $category = ($category_select === 'Other') ? $category_custom : $category_select;
    
    if (empty($event_name) || $price <= 0 || empty($event_date) || empty($venue) || empty($category) || $total_qty <= 0) {
        $error = "Please fill in all required fields and ensure numeric values are valid.";
    } else {
        // Handle image upload if a new one is selected
        $image_path = $tkt['image_path'];
        if (isset($_FILES['ticket_image']) && $_FILES['ticket_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['ticket_image']['tmp_name'];
            $file_name = $_FILES['ticket_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = '../uploads/tickets/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = md5(uniqid(rand(), true)) . '.' . $file_ext;
                $dest_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Delete old image if not the default placeholder
                    if ($tkt['image_path'] !== 'assets/img/avater.jpg' && $tkt['image_path'] !== 'assets/img/perf_default.jpg' && file_exists('../' . $tkt['image_path']) && is_file('../' . $tkt['image_path'])) {
                        @unlink('../' . $tkt['image_path']);
                    }
                    $image_path = 'uploads/tickets/' . $new_file_name;
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "Invalid file type. Allowed extensions: JPG, JPEG, PNG, WEBP.";
            }
        }
        
        if (empty($error)) {
            // Calculate new available quantity
            // Fetch total booked and approved count
            $bk_stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket_bookings WHERE ticket_id = ? AND status = 'Approved'");
            $bk_stmt->execute([$id]);
            $booked_count = (int)$bk_stmt->fetchColumn();
            
            $available_qty = max(0, $total_qty - $booked_count);
            
            // Adjust status automatically if sold out
            if ($available_qty <= 0 && $status === 'Available') {
                $status = 'Sold Out';
            }
            
            try {
                $sql = "UPDATE tickets SET event_name = ?, price = ?, event_date = ?, venue = ?, description = ?, total_qty = ?, available_qty = ?, category = ?, image_path = ?, status = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $event_name,
                    $price,
                    $event_date,
                    $venue,
                    $description,
                    $total_qty,
                    $available_qty,
                    $category,
                    $image_path,
                    $status,
                    $id
                ]);
                
                header("Location: tickets_manager.php?status=updated");
                exit;
                
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Edit Ticket - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include "nav.php" ?>
    <div id="wrapper">
        <?php include "header.php" ?>
        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bolder mb-0">Edit Ticket</h4>
                        <small class="text-muted fw-bold">Ticket ID: <?php echo htmlspecialchars($tkt['ticket_id']); ?></small>
                    </div>
                    <a href="tickets_manager.php" class="btn btn-outline-secondary px-4 fw-bold" style="border-radius: 8px;"><i class="bi bi-arrow-left me-2"></i>Back</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 12px;">
                    <form action="edit_ticket.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Celebrity / Event Name *</label>
                                <input type="text" name="event_name" class="form-control" value="<?php echo htmlspecialchars($tkt['event_name']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Ticket Price ($) *</label>
                                <input type="number" name="price" step="0.01" min="0.01" class="form-control" value="<?php echo htmlspecialchars($tkt['price']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Total Quantity *</label>
                                <input type="number" name="total_qty" min="1" class="form-control" value="<?php echo htmlspecialchars($tkt['total_qty']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Event Date & Time *</label>
                                <input type="text" name="event_date" class="form-control" value="<?php echo htmlspecialchars($tkt['event_date']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Event Location / Venue *</label>
                                <input type="text" name="venue" class="form-control" value="<?php echo htmlspecialchars($tkt['venue']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Category *</label>
                                <select name="category_select" id="category_select" class="form-select" required onchange="toggleCustomCategory(this.value)">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo (!$is_custom_cat && $tkt['category'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                    <option value="Other" <?php echo $is_custom_cat ? 'selected' : ''; ?>>Other (Custom Input)</option>
                                </select>
                                <div id="custom_category_wrapper" class="mt-2" style="display: <?php echo $is_custom_cat ? 'block' : 'none'; ?>;">
                                    <input type="text" name="category_custom" id="category_custom" class="form-control" placeholder="Enter custom category name" value="<?php echo $is_custom_cat ? htmlspecialchars($tkt['category']) : ''; ?>" <?php echo $is_custom_cat ? 'required' : ''; ?>>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="Available" <?php echo $tkt['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="Sold Out" <?php echo $tkt['status'] === 'Sold Out' ? 'selected' : ''; ?>>Sold Out</option>
                                    <option value="Pending" <?php echo $tkt['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Inactive" <?php echo $tkt['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-muted small text-uppercase">Ticket Description / Details *</label>
                                <textarea name="description" rows="4" class="form-control" required><?php echo htmlspecialchars($tkt['description']); ?></textarea>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold text-muted small text-uppercase">Upload New Image (Optional)</label>
                                <input type="file" name="ticket_image" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended aspect ratio: 16:9 or square. Leave empty to keep current image.</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold text-muted d-block small text-uppercase">Current Image</label>
                                <img src="../<?php echo htmlspecialchars($tkt['image_path']); ?>" alt="Current Ticket Image" class="img-thumbnail" style="max-height: 120px;">
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-gold px-5 py-2 fw-bold" style="border-radius: 8px;">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php include "footer.php" ?>
        </div>
    </div>

    <script>
        function toggleCustomCategory(val) {
            const customWrapper = document.getElementById('custom_category_wrapper');
            const customInput = document.getElementById('category_custom');
            if (val === 'Other') {
                customWrapper.style.display = 'block';
                customInput.setAttribute('required', 'required');
            } else {
                customWrapper.style.display = 'none';
                customInput.removeAttribute('required');
                customInput.value = '';
            }
        }
    </script>
</body>
</html>
