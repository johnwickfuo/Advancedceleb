<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php'; 
require_once '../get_setting.php';

$table_name = 'testimonials';
$image_column = 'client_image_path';
$base_upload_dir = '../uploads/'; 

// --- DELETE LOGIC ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    
    $image_path_for_deletion = null;
    $sql_select = "SELECT $image_column FROM $table_name WHERE id = :id";
    
    if ($stmt_select = $pdo->prepare($sql_select)) {
        $stmt_select->bindParam(":id", $id_to_delete, PDO::PARAM_INT);
        if ($stmt_select->execute()) {
            if ($row = $stmt_select->fetch(PDO::FETCH_ASSOC)) {
                $image_path_for_deletion = $row[$image_column];
            }
        }
        unset($stmt_select);
    }

    $sql_delete = "DELETE FROM $table_name WHERE id = :id";
    
    if ($stmt_delete = $pdo->prepare($sql_delete)) {
        $stmt_delete->bindParam(":id", $id_to_delete, PDO::PARAM_INT);
        
        if ($stmt_delete->execute()) {
            
            if (!empty($image_path_for_deletion)) {
                $full_path = $base_upload_dir . basename($image_path_for_deletion);
                
                if (file_exists($full_path) && is_file($full_path)) {
                    unlink($full_path);
                }
            }

            
            $current_page_query = http_build_query(array_diff_key($_GET, array('delete_id' => '')));
            $_SESSION['delete_status'] = 'success';
            header("location: view_testimonials.php?" . $current_page_query);
            exit();
        } else {
            $_SESSION['delete_status'] = 'error';
        }
        unset($stmt_delete);
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("location: login.php");
    exit;
}


$records_per_page = 5; 

$total_records_sql = "SELECT COUNT(id) FROM $table_name";
$total_records_stmt = $pdo->query($total_records_sql);
$total_records = $total_records_stmt->fetchColumn();

$total_pages = ceil($total_records / $records_per_page);

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}
if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $records_per_page;

$testimonials = [];

$sql = "SELECT id, client_name, client_location, product_service, rating, submission_date, client_image_path, testimonial_text FROM $table_name ORDER BY id DESC LIMIT :limit OFFSET :offset";

if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindValue(':limit', (int) $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Oops! Something went wrong while fetching data. Please try again later.";
    }
    unset($stmt);
}

$delete_status = $_SESSION['delete_status'] ?? null;
unset($_SESSION['delete_status']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>View Testimonials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <script src="../assets/js/sweetalert2.all.min.js"></script>
	
	<link href="../assets/css/google-fonts.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">
	<link rel="stylesheet" href="../assets/css/font-awesome-pro.css">
    
    <style>
        /* Custom styles for the star rating in the table */
        .testimonial-stars {
            color: #ffc107; /* Bootstrap's 'warning' yellow for stars */
            font-size: 0.85rem;
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
                    <h4 class="fw-bolder mb-0">Customer Testimonials</h4>
                    <a href="add_testimonial.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Add New</a>
                </div>
				
                <?php if ($delete_status): ?>
                    <script>
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: '<?php echo ($delete_status === "success") ? "success" : "error"; ?>',
                            title: '<?php echo ($delete_status === "success") ? "Testimonial deleted successfully!" : "Error deleting testimonial."; ?>'
                        });
                    </script>
                <?php endif; ?>
                
                <div class="card-enhanced border-0">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center py-3 px-4">
                        <div class="d-flex align-items-center">
                            <span>Showing Records <?php echo $offset + 1; ?>-<?php echo min($offset + $records_per_page, $total_records); ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-2 small">Total:</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $total_records; ?></span>
                        </div>
                    </div>
                
                    <div class="card-body">
                        <?php if (!empty($testimonials)): ?>
                        
                        <!-- Desktop View -->
                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table class="table-enhanced table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">Client Name</th>
                                            <th scope="col">Location</th>
                                            <th scope="col">Service Used</th>
                                            <th scope="col">Rating</th>
                                            <th scope="col">Submitted On</th>
                                            <th scope="col" class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($testimonials as $testimonial): 
                                            $rating = (int)$testimonial['rating'];
                                            $stars = '';
                                            for ($i = 1; $i <= 3; $i++) {
                                                $stars .= ($i <= $rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($testimonial['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['client_location']); ?></td>
                                            <td><?php echo htmlspecialchars($testimonial['product_service']); ?></td>
                                            <td><span class="testimonial-stars"><?php echo $stars; ?></span></td>
                                            <td><?php echo date('M d, Y', strtotime($testimonial['submission_date'])); ?></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary view-details-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#testimonialDetailModal" 
                                                        data-id="<?php echo $testimonial['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($testimonial['client_name']); ?>"
                                                        data-location="<?php echo htmlspecialchars($testimonial['client_location']); ?>"
                                                        data-product="<?php echo htmlspecialchars($testimonial['product_service']); ?>"
                                                        data-rating="<?php echo htmlspecialchars($testimonial['rating']); ?>"
                                                        data-text="<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"
                                                        data-date="<?php echo date('M d, Y', strtotime($testimonial['submission_date'])); ?>"
                                                        data-image="<?php echo htmlspecialchars($testimonial['client_image_path']); ?>">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </button>
                                                    <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="confirmDeleteTestimonial(<?php echo $testimonial['id']; ?>, <?php echo $page; ?>)" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fa-regular fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile View (Premium Card Layout) -->
                        <div class="d-md-none">
                            <div class="row g-3">
                                <?php foreach ($testimonials as $testimonial): 
                                    $rating = (int)$testimonial['rating'];
                                    $stars = '';
                                    for ($i = 1; $i <= 3; $i++) {
                                        $stars .= ($i <= $rating) ? '<i class="fa-solid fa-star text-warning"></i>' : '<i class="fa-regular fa-star text-muted opacity-50"></i>';
                                    }
                                    $img_url = '../assets/images/default-avatar.svg';
                                    if (!empty($testimonial['client_image_path'])) {
                                        if (strpos($testimonial['client_image_path'], 'http') === 0) {
                                            $img_url = htmlspecialchars($testimonial['client_image_path']);
                                        } else {
                                            $img_url = '../' . htmlspecialchars($testimonial['client_image_path']);
                                        }
                                    }
                                ?>
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border-left: 5px solid var(--bs-crimson) !important;">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="<?php echo $img_url; ?>" class="rounded-circle me-3 shadow-sm border border-2 border-white" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($testimonial['client_name']); ?></h6>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger small"></i> <?php echo htmlspecialchars($testimonial['client_location']); ?></small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="small"><?php echo $stars; ?></div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($testimonial['submission_date'])); ?></small>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-light rounded-3 p-3 mb-4">
                                                <small class="text-muted d-block text-uppercase fw-bold mb-2" style="font-size: 0.65rem; letter-spacing: 1px;">Service Utilized</small>
                                                <div class="fw-semibold text-secondary small"><?php echo htmlspecialchars($testimonial['product_service']); ?></div>
                                                <hr class="my-2 opacity-10">
                                                <p class="mb-0 text-muted fst-italic small">"<?php echo mb_strimwidth(htmlspecialchars($testimonial['testimonial_text']), 0, 120, '...'); ?>"</p>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-2 fw-bold view-details-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#testimonialDetailModal" 
                                                        data-id="<?php echo $testimonial['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($testimonial['client_name']); ?>"
                                                        data-location="<?php echo htmlspecialchars($testimonial['client_location']); ?>"
                                                        data-product="<?php echo htmlspecialchars($testimonial['product_service']); ?>"
                                                        data-rating="<?php echo htmlspecialchars($testimonial['rating']); ?>"
                                                        data-text="<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"
                                                        data-date="<?php echo date('M d, Y', strtotime($testimonial['submission_date'])); ?>"
                                                        data-image="<?php echo htmlspecialchars($testimonial['client_image_path']); ?>">
                                                        <i class="bi bi-eye me-1"></i> VIEW
                                                    </button>
                                                </div>
                                                <div class="col-4">
                                                    <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-outline-primary btn-sm w-100 rounded-3 py-2 fw-bold">
                                                        <i class="bi bi-pencil-square me-1"></i> EDIT
                                                    </a>
                                                </div>
                                                <div class="col-4">
                                                    <a href="javascript:void(0)" onclick="confirmDeleteTestimonial(<?php echo $testimonial['id']; ?>, <?php echo $page; ?>)" class="btn btn-outline-danger btn-sm w-100 rounded-3 py-2 fw-bold">
                                                        <i class="bi bi-trash3 me-1"></i> DEL
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php else: ?>
                            <div class="alert alert-info shadow-sm mb-0">No testimonials have been submitted yet.</div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer border-0 bg-white pt-0 pb-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-end mb-0">

                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
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

<div class="modal fade" id="testimonialDetailModal" tabindex="-1" aria-labelledby="testimonialDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimonialDetailModalLabel">Testimonial Details: <span id="modalTestimonialId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="testimonialDetailsContent">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailModal = document.getElementById('testimonialDetailModal');
    
    // Function to generate star icons
    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 3; i++) {
            stars += (i <= rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
        }
        return `<span class="testimonial-stars">${stars}</span>`;
    }

    detailModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Retrieve data attributes
        const testimonialId = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const location = button.getAttribute('data-location');
        const product = button.getAttribute('data-product');
        const rating = parseInt(button.getAttribute('data-rating'));
        const text = button.getAttribute('data-text');
        const date = button.getAttribute('data-date');
        const imagePath = button.getAttribute('data-image');
        
        // Construct the image URL. 
        let imageUrl = '../assets/images/default-avatar.svg';
        if (imagePath) {
            imageUrl = imagePath.startsWith('http') ? imagePath : `../${imagePath}`;
        }

        // Update modal title
        document.getElementById('modalTestimonialId').textContent = testimonialId;

        // Generate HTML content for the modal body
        const detailsHtml = `
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex align-items-start mb-4">
                        <img src="${imageUrl}" class="rounded-circle me-3 border border-dark" style="width: 100px; height: 100px; object-fit: cover;" alt="${name} Profile Image">
                        <div>
                            <h5 class="fw-bold mb-1">${name}</h5>
                            <p class="text-muted mb-1">${location}</p>
                            <div class="text-warning">${generateStars(rating)} (${rating}/3)</div>
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary">Testimonial Content</h5>
                    <blockquote class="blockquote border-start border-primary border-4 ps-3 mb-4 bg-light p-3 rounded"  style="font-size: 15px;">
                        <p class="mb-0 fst-italic">“${text}”</p>
                        <footer class="blockquote-footer mt-2">Service Used: <cite title="Product">${product}</cite></footer>
                    </blockquote>

                    <h5 class="mb-3 text-secondary">Submission Details</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Date Submitted: <span class="fw-bold">${date}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Client Image: 
                            <span>
                                ${imagePath ? `<a href="${imageUrl}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> View Image
                                </a>` : '<span class="text-danger small">No File</span>'}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        `;

        document.getElementById('testimonialDetailsContent').innerHTML = detailsHtml;
    });
});

function confirmDeleteTestimonial(id, page) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone and will delete the image file!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#B00000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: '#fff',
        borderRadius: '16px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete_id=${id}&page=${page}`;
        }
    })
}
</script>
</body>
</html>
