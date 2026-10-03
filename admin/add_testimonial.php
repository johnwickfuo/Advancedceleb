<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

// Handle Add Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_testimonial'])) {
    $client_name = trim($_POST['client_name']);
    $client_location = trim($_POST['client_location']);
    $product_service = trim($_POST['product_service']);
    $rating = (int)$_POST['rating'];
    $testimonial_text = trim($_POST['testimonial_text']);
    
    $upload_dir = '../uploads/';
    $image_path = null;

    // Handle Image Upload
    if (isset($_FILES['client_image']) && $_FILES['client_image']['error'] == 0) {
        $file_name = $_FILES['client_image']['name'];
        $file_tmp = $_FILES['client_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_file_name = uniqid('testimonial_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $image_path = 'uploads/' . $new_file_name;
            }
        }
    }

    $sql = "INSERT INTO testimonials (client_name, client_location, product_service, rating, testimonial_text, client_image_path) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$client_name, $client_location, $product_service, $rating, $testimonial_text, $image_path])) {
        $_SESSION['delete_status'] = "success";
        header("location: view_testimonials.php");
        exit;
    } else {
        $error = "Failed to add testimonial.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Add Testimonial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <?php if (isset($use_favicon) && $use_favicon): ?>
        " href="../<?php echo $favicon_path; ?>">
    <?php endif; ?>
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --bs-crimson: #B00000;
            --bs-crimson-light: #FF3B3B;
            --bs-crimson-faded: rgba(176, 0, 0, 0.1);
        }
        body { 
            background-color: #f8f9fa; 
            font-family: 'Work Sans', sans-serif; 
            color: #334155;
        }
        .main-content-wrapper { padding: 40px 20px; }
        .card-premium {
            background: #ffffff;
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.3s;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: var(--bs-crimson);
            box-shadow: 0 0 0 4px var(--bs-crimson-faded);
        }
        .preview-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }
        .preview-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 30px;
            border: 4px solid #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .upload-btn {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--bs-crimson);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid #fff;
            transition: transform 0.2s;
        }
        .upload-btn:hover { transform: scale(1.1); color: white; }
        .btn-premium {
            background: var(--bs-crimson);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(176, 0, 0, 0.2);
        }
        .btn-premium:hover {
            background: #800000;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(176, 0, 0, 0.3);
        }
        .rating-stars {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .rating-stars input { display: none; }
        .rating-stars label {
            font-size: 2rem;
            color: #e2e8f0;
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-stars input:checked ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #fbbf24;
        }
        /* Fix for star rating logic to work from right to left or vice versa */
        .rating-stars {
            flex-direction: row-reverse;
        }
    </style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper">
        <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h3 class="fw-bold mb-1">Add Testimonial</h3>
                                <p class="text-muted">Add a new client review</p>
                            </div>
                            <a href="view_testimonials.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                                <i class="bi bi-arrow-left me-2"></i> Back
                            </a>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="card-premium p-4 p-md-5">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="add_testimonial" value="1">

                                <div class="preview-container">
                                    <img src="../assets/images/default-avatar.svg" alt="Preview" class="preview-img" id="imagePreview">
                                    <label for="client_image" class="upload-btn">
                                        <i class="bi bi-camera-fill"></i>
                                        <input type="file" name="client_image" id="client_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                    </label>
                                </div>

                                <div class="text-center mb-4">
                                    <label class="form-label d-block">Client Rating</label>
                                    <div class="rating-stars">
                                        <?php for($i=5; $i>=1; $i--): ?>
                                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo ($i == 5) ? 'checked' : ''; ?>>
                                            <label for="star<?php echo $i; ?>"><i class="bi bi-star-fill"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Client Name</label>
                                        <input type="text" name="client_name" class="form-control" value="" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="client_location" class="form-control" value="" placeholder="e.g. London, UK">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Product / Service Used</label>
                                        <input type="text" name="product_service" class="form-control" value="" placeholder="e.g. VIP Celebrity Booking">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Testimonial Text</label>
                                        <textarea name="testimonial_text" class="form-control" rows="5" required></textarea>
                                    </div>
                                    <div class="col-12 mt-5">
                                        <button type="submit" class="btn btn-premium w-100 fs-5">
                                            <i class="bi bi-check2-circle me-2"></i> Add Testimonial
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include "footer.php" ?>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
