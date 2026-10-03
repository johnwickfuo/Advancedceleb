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

// Helper function to generate unique Ticket ID
function generateUniqueTicketID($pdo) {
    $exists = true;
    $ticket_id = '';
    while ($exists) {
        $ticket_id = 'TKT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_id = ?");
        $stmt->execute([$ticket_id]);
        $exists = ($stmt->fetchColumn() > 0);
    }
    return $ticket_id;
}

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
    
    // Basic validation
    if (empty($event_name) || $price <= 0 || empty($event_date) || empty($venue) || empty($category) || $total_qty <= 0) {
        $error = "Please fill in all required fields and ensure numeric values are valid.";
    } else {
        // Handle image upload
        $image_path = 'assets/img/avater.jpg'; // default avatar placeholder
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
                    $image_path = 'uploads/tickets/' . $new_file_name;
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "Invalid file type. Allowed extensions: JPG, JPEG, PNG, WEBP.";
            }
        }
        
        if (empty($error)) {
            // Generate unique Ticket ID
            $ticket_id = generateUniqueTicketID($pdo);
            
            try {
                $sql = "INSERT INTO tickets (ticket_id, event_name, price, event_date, venue, description, total_qty, available_qty, category, image_path, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $ticket_id,
                    $event_name,
                    $price,
                    $event_date,
                    $venue,
                    $description,
                    $total_qty,
                    $total_qty, // available_qty initially equals total_qty
                    $category,
                    $image_path,
                    $status
                ]);
                
                header("Location: tickets_manager.php?status=created");
                exit;
                
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

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
<title>Create Ticket - Admin Dashboard</title>
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
                    <h4 class="fw-bolder mb-0">Create New Ticket</h4>
                    <a href="tickets_manager.php" class="btn btn-outline-secondary px-4 fw-bold" style="border-radius: 8px;"><i class="bi bi-arrow-left me-2"></i>Back</a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm p-4 bg-white" style="border-radius: 12px;">
                    <form action="create_ticket.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Celebrity / Event Name *</label>
                                <input type="text" name="event_name" class="form-control" placeholder="e.g. Ariana Grande Live" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Ticket Price ($) *</label>
                                <input type="number" name="price" step="0.01" min="0.01" class="form-control" placeholder="e.g. 150.00" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Total Quantity *</label>
                                <input type="number" name="total_qty" min="1" class="form-control" placeholder="e.g. 100" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Event Date & Time *</label>
                                <input type="text" name="event_date" class="form-control" placeholder="e.g. Oct 25, 2026, 8:00 PM" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Event Location / Venue *</label>
                                <input type="text" name="venue" class="form-control" placeholder="e.g. Wembley Stadium, London" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Category *</label>
                                <select name="category_select" id="category_select" class="form-select" required onchange="toggleCustomCategory(this.value)">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                    <option value="Other">Other (Custom Input)</option>
                                </select>
                                <div id="custom_category_wrapper" class="mt-2" style="display: none;">
                                    <input type="text" name="category_custom" id="category_custom" class="form-control" placeholder="Enter custom category name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small text-uppercase">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="Available">Available</option>
                                    <option value="Sold Out">Sold Out</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-muted small text-uppercase">Ticket Description / Details *</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Provide information about the ticket benefits, VIP access, seat categories, etc." required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-muted small text-uppercase">Upload Ticket / Event Image</label>
                                <input type="file" name="ticket_image" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended aspect ratio: 16:9 or square. Format: JPG, PNG, WEBP.</small>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-gold px-5 py-2 fw-bold" style="border-radius: 8px;">Create Ticket & Generate QR</button>
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
            }
        }
    </script>
</body>
</html>
