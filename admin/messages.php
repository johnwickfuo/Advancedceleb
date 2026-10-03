<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = "Message deleted successfully.";
    $_SESSION['message_type'] = "success";
    header("Location: messages.php");
    exit;
}

// Handle Mark as Read
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'Read' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: messages.php");
    exit;
}

// Pagination Settings
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Fetch Total Counts
$total_count_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$total_count = $total_count_stmt->fetchColumn();
$total_pages = ceil($total_count / $limit);

$unread_count_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'New' OR status IS NULL");
$unread_count = $unread_count_stmt->fetchColumn();

// Fetch Paginated Messages
$stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Messages | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <style>
        :root {
            --bs-crimson-light: #B00000;
            --bs-crimson-faded: #fdeaea;
            --primary-font: 'Inter', sans-serif;
            --header-font: 'Outfit', sans-serif;
        }
        body {
            font-family: var(--primary-font);
            background-color: #f8f9fc;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--header-font);
        }
        .main-content-wrapper {
            background-color: #f8f9fc;
        }
        .page-header {
            background: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 2rem;
        }
        .card-inquiry {
            background: white;
            border-radius: 16px;
            border: 1px solid #edf2f7;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .card-inquiry:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-color: var(--bs-crimson-light);
        }
        .card-inquiry .status-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #cbd5e0;
        }
        .card-inquiry.status-new .status-indicator {
            background: var(--bs-crimson-light);
        }
        .avatar-box {
            width: 45px;
            height: 45px;
            background: #f1f5f9;
            color: #475569;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .card-inquiry.status-new .avatar-box {
            background: var(--bs-crimson-faded);
            color: var(--bs-crimson-light);
        }
        .msg-subject {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 10px;
            display: inline-block;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .msg-text {
            color: #475569;
            line-height: 1.6;
            font-size: 0.95rem;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .action-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            transition: all 0.2s;
            text-decoration: none;
        }
        .action-icon-btn:hover {
            background: var(--bs-crimson-light);
            color: white;
            border-color: var(--bs-crimson-light);
        }
        .badge-unread {
            background: var(--bs-crimson-light);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            margin-left: 8px;
        }
        .empty-state {
            padding: 5rem 2rem;
            text-align: center;
            background: white;
            border-radius: 20px;
            border: 2px dashed #e2e8f0;
        }
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1.5rem;
        }

        /* Premium Pagination */
        .pagination .page-link {
            border: none;
            color: #64748b;
            padding: 10px 18px;
            margin: 0 4px;
            border-radius: 12px !important;
            font-weight: 600;
            transition: all 0.3s;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .pagination .page-item.active .page-link {
            background: var(--bs-crimson-light);
            color: white;
            box-shadow: 0 4px 12px rgba(176,0,0,0.25);
        }
        .pagination .page-link:hover:not(.active) {
            background: #f1f5f9;
            color: var(--bs-crimson-light);
            transform: translateY(-2px);
        }
        .pagination .page-item.disabled .page-link {
            background: transparent;
            opacity: 0.5;
        }

        @media (max-width: 991px) {
            .page-header { padding: 1.25rem; }
        }
    </style>
</head>
<body>
    <?php include "nav.php" ?>
    
    <div id="wrapper">
        <?php include "header.php" ?>
        
        <div class="main-content-wrapper">
            <!-- Simplified Header -->
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h3 class="fw-bold m-0">Inbound Messages</h3>
                    <p class="text-muted m-0 small">Manage customer inquiries from contact form</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="border rounded-pill px-3 py-2 bg-white shadow-sm small fw-bold">
                        Total: <?php echo $total_count; ?>
                    </div>
                    <?php if($unread_count > 0): ?>
                    <div class="badge bg-danger rounded-pill px-3 py-2 shadow-sm small fw-bold">
                        <?php echo $unread_count; ?> New
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="main-content" class="container-fluid px-lg-4 pb-5">
                
                <?php if (isset($_SESSION['message'])): ?>
                    <script>
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });
                        Toast.fire({
                            icon: '<?php echo $_SESSION['message_type']; ?>',
                            title: '<?php echo $_SESSION['message']; ?>'
                        });
                    </script>
                    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                <?php endif; ?>

                <?php if (!empty($messages)): ?>
                    <div class="row g-4">
                        <?php foreach ($messages as $msg): 
                            $is_new = ($msg['status'] ?? 'New') == 'New';
                            $initial = strtoupper(substr($msg['name'] ?? 'U', 0, 1));
                        ?>
                            <div class="col-xl-4 col-lg-6">
                                <div class="card-inquiry <?php echo $is_new ? 'status-new' : ''; ?> p-4">
                                    <div class="status-indicator"></div>
                                    
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-box">
                                                <?php echo $initial; ?>
                                            </div>
                                            <div>
                                                <h6 class="m-0 fw-bold"><?php echo htmlspecialchars($msg['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($msg['email']); ?></small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if($is_new): ?>
                                            <a href="?mark_read=<?php echo $msg['id']; ?>" class="action-icon-btn" title="Mark as read">
                                                <i class="bi bi-check2"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $msg['id']; ?>)" class="action-icon-btn text-danger" title="Delete">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="flex-grow-1">
                                        <span class="msg-subject">
                                            <i class="bi bi-tag-fill me-1"></i> <?php echo htmlspecialchars($msg['subject']); ?>
                                        </span>
                                        <p class="msg-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar3 me-1"></i> <?php echo date('M d, Y', strtotime($msg['created_at'])); ?>
                                        </div>
                                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo htmlspecialchars($msg['subject']); ?>" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">
                                            <i class="bi bi-reply-fill me-1"></i> Reply
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination UI -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            
                            for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; 
                            
                            if ($end < $total_pages) {
                                if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                            }
                            ?>

                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-envelope-x"></i>
                        <h4 class="fw-bold">No messages found</h4>
                        <p class="text-muted">Inquiries from the contact form will appear here.</p>
                    </div>
                <?php endif; ?>

            </div>
            <?php include "footer.php" ?>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#B00000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                background: '#fff',
                borderRadius: '16px',
                customClass: {
                    popup: 'premium-swal-popup',
                    title: 'premium-swal-title',
                    confirmButton: 'premium-swal-confirm',
                    cancelButton: 'premium-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete=' + id;
                }
            })
        }
    </script>
    <style>
        .premium-swal-popup {
            border-radius: 20px !important;
            padding: 2rem !important;
        }
        .premium-swal-title {
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
        }
        .premium-swal-confirm {
            padding: 10px 25px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
        }
        .premium-swal-cancel {
            padding: 10px 25px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
        }
    </style>
</body>
</html>
