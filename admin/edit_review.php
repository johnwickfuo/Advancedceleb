<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['message'] = "Invalid Review ID.";
    $_SESSION['message_type'] = "danger";
    header("location: admin_dashboard.php");
    exit;
}

// Fetch current review data
$stmt = $pdo->prepare("SELECT * FROM winners WHERE id = ?");
$stmt->execute([$id]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    $_SESSION['message'] = "Review not found.";
    $_SESSION['message_type'] = "danger";
    header("location: admin_dashboard.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Edit Review - <?php echo htmlspecialchars($review['winner_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
	<link href="../assets/css/google-fonts.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {--bs-crimson-faded: #b000001a;}
        body { background-color: #f8f9fb; font-family: 'Work Sans', sans-serif; }
        .form-control:focus { border-color: var(--bs-crimson-light); box-shadow: 0 0 0 0.25rem var(--bs-crimson-faded); }
        .card-enhanced { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .preview-img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper">
        
        <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid py-4">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="fw-bold mb-0">Edit Customer Review</h4>
                                <p class="text-muted small mb-0">Update the details for <strong><?php echo htmlspecialchars($review['winner_name']); ?></strong></p>
                            </div>
                            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-3 fw-bold">
                                <i class="bi bi-arrow-left me-1"></i> BACK
                            </a>
                        </div>

                        <div class="card-enhanced p-4 bg-white">
                            <form action="process_prize.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="update_review" value="1">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <?php if (!empty($review['winner_image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($review['winner_image_path']); ?>" alt="Current Avatar" class="preview-img" id="avatar-preview">
                                        <?php else: ?>
                                            <div class="preview-img bg-light d-flex align-items-center justify-content-center" id="avatar-preview">
                                                <i class="bi bi-person text-muted fs-1"></i>
                                            </div>
                                        <?php endif; ?>
                                        <label for="winner_image" class="btn btn-sm btn-crimson rounded-circle position-absolute bottom-0 end-0 p-2 shadow" style="width: 35px; height: 35px;">
                                            <i class="bi bi-camera-fill"></i>
                                            <input type="file" name="winner_image" id="winner_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                        </label>
                                    </div>
                                    <p class="small text-muted mt-2">Change Avatar (Optional)</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Full Name</label>
                                    <input type="text" name="winner_name" class="form-control py-2" value="<?php echo htmlspecialchars($review['winner_name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Location</label>
                                    <input type="text" name="winner_location" class="form-control py-2" value="<?php echo htmlspecialchars($review['winner_location']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Service Used</label>
                                    <input type="text" name="prize_won" class="form-control py-2" value="<?php echo htmlspecialchars($review['prize_won']); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-uppercase">Review Content</label>
                                    <textarea name="testimonial" class="form-control" rows="4" required><?php echo htmlspecialchars($review['testimonial']); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-crimson w-100 py-3 fw-bold rounded-3">
                                    <i class="bi bi-check-circle me-2"></i> SAVE CHANGES
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include "footer.php" ?>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // If it was a div placeholder, replace it with an img
                        const newImg = document.createElement('img');
                        newImg.id = 'avatar-preview';
                        newImg.className = 'preview-img';
                        newImg.src = e.target.result;
                        preview.parentNode.replaceChild(newImg, preview);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
