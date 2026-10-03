<?php
session_start();
require '../config.php';
require '../get_setting.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$success_msg = '';
$error_msg = '';

// Handle Image Deletion
if (isset($_POST['delete_image'])) {
    $id = (int)$_POST['image_id'];
    // Get path
    $stmt = $pdo->prepare("SELECT image_path FROM cta_images WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    
    if ($img) {
        // If it's a local file, try to delete it
        if (strpos($img, 'http') === false) {
            $local_path = '../' . $img;
            if (file_exists($local_path)) {
                @unlink($local_path);
            }
        }
        $pdo->prepare("DELETE FROM cta_images WHERE id = ?")->execute([$id]);
        $success_msg = "Image deleted successfully.";
    }
}

// Handle Text Update
if (isset($_POST['update_text'])) {
    $title = trim($_POST['cta_title']);
    $desc = trim($_POST['cta_desc']);
    $btn_text = trim($_POST['cta_btn_text']);
    $btn_link = trim($_POST['cta_btn_link']);
    
    $stmt = $pdo->prepare("UPDATE site_config SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$title, 'cta_title']);
    $stmt->execute([$desc, 'cta_desc']);
    $stmt->execute([$btn_text, 'cta_btn_text']);
    $stmt->execute([$btn_link, 'cta_btn_link']);
    
    // Refresh settings variable
    $site_settings['cta_title'] = $title;
    $site_settings['cta_desc'] = $desc;
    $site_settings['cta_btn_text'] = $btn_text;
    $site_settings['cta_btn_link'] = $btn_link;
    
    $success_msg = "Text updated successfully.";
}

// Handle Image Upload
if (isset($_POST['upload_image'])) {
    // Check limit
    $count = $pdo->query("SELECT COUNT(*) FROM cta_images")->fetchColumn();
    if ($count >= 4) {
        $error_msg = "Maximum of 4 images allowed. Please delete an image first.";
    } else {
        if (isset($_FILES['cta_image']) && $_FILES['cta_image']['error'] == 0) {
            $target_dir = "../assets/images/cta/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["cta_image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_extension, $allowed_types)) {
                if (move_uploaded_file($_FILES["cta_image"]["tmp_name"], $target_file)) {
                    $db_path = "assets/images/cta/" . $new_filename;
                    $pdo->prepare("INSERT INTO cta_images (image_path) VALUES (?)")->execute([$db_path]);
                    $success_msg = "Image uploaded successfully.";
                } else {
                    $error_msg = "Error uploading file.";
                }
            } else {
                $error_msg = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
            }
        } else {
            $error_msg = "Please select a valid image file.";
        }
    }
}

// Fetch all images
$images = $pdo->query("SELECT * FROM cta_images ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Custom Section - <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Admin'); ?></title>
    
    <!-- Standard Admin Panel Styles -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/font-awesome-pro.css">
    
    <style>
        .btn-crimson {
            background: linear-gradient(135deg, #b00000 0%, #8a0000 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 10px rgba(176,0,0,0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-crimson:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(176,0,0,0.3);
            color: #FFC107; /* Gold */
        }
        .text-crimson { color: #b00000 !important; }
        .premium-alert {
            border: none;
            border-left: 5px solid #b00000;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
            font-weight: 500;
        }
        .premium-alert-success { border-left-color: #198754; }
        .premium-alert-danger { border-left-color: #dc3545; }
        .form-control:focus {
            border-color: #b00000;
            box-shadow: 0 0 0 0.25rem rgba(176,0,0,0.15);
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03) !important;
        }
        .header-title {
            color: #2c3e50;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div id="wrapper">
    <?php include 'header.php'; ?>
    
    <div class="main-content-wrapper">
        <div id="main-content" class="container-fluid p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0 header-title">Custom Section Settings</h2>
            </div>
            
            <?php if ($success_msg): ?>
                <div class="alert premium-alert premium-alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2 text-success"></i> <?php echo htmlspecialchars($success_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert premium-alert premium-alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> <?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Text Settings Form -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="m-0 fw-bold text-crimson"><i class="bi bi-fonts me-2"></i>Edit Text Content</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Title</label>
                                    <input type="text" class="form-control" name="cta_title" value="<?php echo htmlspecialchars($site_settings['cta_title'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="cta_desc" rows="4" required><?php echo htmlspecialchars($site_settings['cta_desc'] ?? ''); ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Button Text</label>
                                        <input type="text" class="form-control" name="cta_btn_text" value="<?php echo htmlspecialchars($site_settings['cta_btn_text'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Button Link</label>
                                        <input type="text" class="form-control" name="cta_btn_link" value="<?php echo htmlspecialchars($site_settings['cta_btn_link'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <button type="submit" name="update_text" class="btn btn-crimson mt-2 px-4 py-2"><i class="bi bi-check2-circle me-1"></i> Save Text</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Image Upload Form -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-crimson"><i class="bi bi-images me-2"></i>Carousel Images</h6>
                            <span class="badge bg-secondary rounded-pill px-3"><?php echo count($images); ?> / 4</span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">Upload up to 4 images for the homepage carousel.</p>
                            
                            <?php if (count($images) < 4): ?>
                                <form method="POST" enctype="multipart/form-data" class="mb-4 p-3 border rounded bg-light">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-secondary">Select Image to Add</label>
                                        <input class="form-control" type="file" name="cta_image" accept="image/jpeg, image/png, image/webp" required>
                                    </div>
                                    <button type="submit" name="upload_image" class="btn btn-crimson px-4 py-2"><i class="bi bi-upload me-1"></i> Upload</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 mb-4 fw-medium">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Maximum 4 images reached.
                                </div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <?php foreach ($images as $img): ?>
                                    <div class="col-6">
                                        <div class="position-relative rounded overflow-hidden shadow-sm" style="height: 120px;">
                                            <img src="<?php echo strpos($img['image_path'], 'http') !== false ? htmlspecialchars($img['image_path']) : '../' . htmlspecialchars($img['image_path']); ?>" class="w-100 h-100 object-fit-cover" alt="CTA Image">
                                            <form method="POST" class="position-absolute top-0 end-0 p-1">
                                                <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                                <input type="hidden" name="delete_image" value="1">
                                                <button type="button" class="btn btn-danger btn-sm p-1 lh-1" onclick="confirmAction(event, this.closest('form'), 'Are you sure you want to delete this carousel image?')" title="Delete Image">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <?php include "footer.php"; ?>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
