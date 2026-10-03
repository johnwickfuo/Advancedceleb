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



// --- PLATFORM STATISTICS QUERIES ---
$stats = [
    'total_celebrities' => 0,
    'total_bookings' => 0,
    'pending_bookings' => 0,
    'total_events' => 0
];

function get_table_count($pdo, $table, $status = null) {
    if ($status) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE status = :status");
        $stmt->execute(['status' => $status]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
    }
    return $stmt->fetchColumn();
}

$stats['total_celebrities'] = get_table_count($pdo, 'celebrities');
$stats['total_bookings'] = get_table_count($pdo, 'bookings');
$stats['pending_bookings'] = get_table_count($pdo, 'bookings', 'Pending');
$stats['total_events'] = get_table_count($pdo, 'events');

// Fetch recent events
$recent_events_stmt = $pdo->query("SELECT id, title, event_date, image_url FROM events ORDER BY id DESC LIMIT 5");
$recent_events = $recent_events_stmt->fetchAll(PDO::FETCH_ASSOC);
// ------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
	<link href="assets/css/style.css" rel="stylesheet">
<link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
         .text-gold {color: var(--bs-gold) !important;}
         .card-enhanced {border: none;border-radius: 15px;box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);background-color: #fff;margin-bottom: 24px; transition: transform 0.2s; }
         .card-stats { padding: 1.5rem; position: relative; overflow: hidden; color: white; min-height: 140px; display: flex; flex-direction: column; justify-content: space-between; }
         .card-stats i { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.15; transform: rotate(-15deg); }
         .card-stats .label { font-size: 0.85rem; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
         .card-stats .value { font-size: 2.2rem; font-weight: 800; line-height: 1; }
         
         .bg-stat-total { background: linear-gradient(135deg, #1e3c72, #2a5298); }
         .bg-stat-pending { background: linear-gradient(135deg, #606c88, #3f4c6b); }
         .bg-stat-transit { background: linear-gradient(135deg, #00b4db, #0083b0); }
         .bg-stat-delivered { background: linear-gradient(135deg, #11998e, #38ef7d); }
         .bg-stat-hold { background: linear-gradient(135deg, #f2994a, #f2c94c); }
         
         .form-control, .form-control:focus {border-radius: 8px;border: 1px solid #ced4da;box-shadow: none;padding: 10px 15px;}
         .form-control:focus {border-color: var(--bs-crimson-light);box-shadow: 0 0 0 0.25rem var(--bs-crimson-faded);}
         .header-bg {background: var(--crimson-gradient);color: white;}
         .custom-file-upload {border: 2px dashed #ced4da;border-radius: 12px;padding: 20px;text-align: center;cursor: pointer;transition: all 0.3s;background: #fafafa;}
         .custom-file-upload:hover {border-color: var(--bs-crimson-light); background: #fff;}


    @media (max-width: 768px) {
        .card-stats { min-height: 120px; padding: 1rem; }
        .card-stats .value { font-size: 1.8rem; }
        .table-enhanced thead { display: none; }
        .table-responsive { overflow: visible !important; border: none !important; }
        .table-enhanced, .table-enhanced tbody { display: block !important; width: 100% !important; }
        .table-enhanced { background: transparent; border: none; box-shadow: none; margin: 0 !important; }
        .table-enhanced tbody { display: flex !important; flex-direction: column; gap: 15px; }
        .table-enhanced tr { display: flex !important; flex-direction: column; border: none; border-radius: 16px; background: #fff; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); position: relative; overflow: hidden; width: 100%; }
        .table-enhanced tr::before { content: ""; position: absolute; left: 0; top: 0; width: 4px; height: 100%; background: var(--bs-crimson-light); }
        .table-enhanced td { display: flex !important; justify-content: space-between; align-items: center; border-bottom: 1px solid #f8f9fa; padding: 12px 0 !important; min-height: 50px; font-size: 0.95rem; width: 100% !important; color: #1e293b !important; }
        .table-enhanced td::before { content: attr(data-label); font-weight: 800; text-align: left; color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; flex: 0 0 100px; }
        .mobile-value-wrapper { flex: 1; display: flex; justify-content: flex-end; align-items: center; text-align: right; min-width: 0; overflow: hidden; }
        .table-enhanced td[data-label="Remove"] { border-bottom: none; justify-content: center !important; padding-top: 20px !important; }
        .table-enhanced td[data-label="Remove"]::before { display: none; }
        .table-enhanced .btn-danger { width: 100%; padding: 12px; border-radius: 10px; }
        .main-content-wrapper { padding: 20px 15px; }
    }
    </style>
</head>
<body>

     <?php include "nav.php" ?>
    
    <div id="wrapper">
        
        <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <?php if (($site_settings['enable_cargo_scroll'] ?? '0') === '1'): ?>
                <!-- Premium Admin Scroll Text -->
                <div class="cargo-scroll-banner mb-4 rounded-3 shadow-sm" style="background: #ffffff; border: 1px solid rgba(218, 165, 32, 0.2); padding: 12px 0; overflow: hidden; position: relative;">
                    <div class="cargo-scroll-wrap">
                        <div class="cargo-scroll-content">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <span class="cargo-scroll-item" style="color: #1e3c72; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; padding: 0 40px; display: inline-flex; align-items: center;">
                                    <i class="bi bi-star-fill text-warning me-2"></i>
                                    <?php echo htmlspecialchars($site_settings['cargo_scroll_text'] ?? ''); ?>
                                </span>
                            <?php endfor; ?>
                        </div>
                        <div class="cargo-scroll-content" aria-hidden="true">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <span class="cargo-scroll-item" style="color: #1e3c72; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; padding: 0 40px; display: inline-flex; align-items: center;">
                                    <i class="bi bi-star-fill text-warning me-2"></i>
                                    <?php echo htmlspecialchars($site_settings['cargo_scroll_text'] ?? ''); ?>
                                </span>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <style>
                .cargo-scroll-wrap {
                    display: flex;
                    width: max-content;
                }
                .cargo-scroll-content {
                    display: flex;
                    white-space: nowrap;
                    animation: cargo-scroll-anim 25s linear infinite;
                }
                .cargo-scroll-wrap:hover .cargo-scroll-content {
                    animation-play-state: paused;
                }
                @keyframes cargo-scroll-anim {
                    0% { transform: translate3d(0, 0, 0); }
                    100% { transform: translate3d(-50%, 0, 0); }
                }
                </style>
                <?php endif; ?>

                <h4 class="fw-bolder mb-4">Admin Dashboard</h4>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- --- STATS SECTION --- -->
                <div class="row g-4 mb-5">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card-enhanced card-stats bg-stat-total">
                            <span class="label">Total Celebrities</span>
                            <span class="value"><?php echo $stats['total_celebrities']; ?></span>
                            <i class="bi bi-person-star"></i>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card-enhanced card-stats bg-stat-transit">
                            <span class="label">Total Bookings</span>
                            <span class="value"><?php echo $stats['total_bookings']; ?></span>
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card-enhanced card-stats bg-stat-hold">
                            <span class="label">Pending Bookings</span>
                            <span class="value"><?php echo $stats['pending_bookings']; ?></span>
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card-enhanced card-stats bg-stat-delivered">
                            <span class="label">Featured Events</span>
                            <span class="value"><?php echo $stats['total_events']; ?></span>
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                </div>
                <!-- ---------------------- -->

                <!-- --- RECENT EVENTS SECTION --- -->
                <div class="row">
                    <div class="col-12">
                        <div class="card-enhanced p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Recent Events</h5>
                                <a href="add_event.php" class="btn btn-sm btn-outline-secondary">Manage Events</a>
                            </div>
                            <!-- Desktop View: Table Layout -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recent_events) > 0): ?>
                                            <?php foreach ($recent_events as $event): ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        $is_url = filter_var($event['image_url'], FILTER_VALIDATE_URL);
                                                        $img_path = $is_url ? $event['image_url'] : '../' . $event['image_url'];
                                                        $img_valid = $is_url || (!empty($event['image_url']) && file_exists($img_path));
                                                        if ($img_valid): 
                                                        ?>
                                                            <img src="<?php echo htmlspecialchars($img_path); ?>" alt="Event Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                        <?php else: ?>
                                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 8px;"><i class="bi bi-image"></i></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($event['title']); ?></td>
                                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($event['event_date']))); ?></td>
                                                    <td class="text-end">
                                                        <a href="add_event.php" onclick="confirmEdit(event, this.href);" class="btn btn-sm btn-outline-primary mb-1 me-1 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Edit Event">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <form action="add_event.php" method="POST" style="display:inline;" onsubmit="confirmDelete(event, this);">
                                                            <input type="hidden" name="action" value="delete_event">
                                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger mb-1 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Delete Event">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center text-muted py-3">No events found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile View: Premium Card Layout -->
                            <div class="d-md-none">
                                <div class="row g-3">
                                    <?php if (count($recent_events) > 0): ?>
                                        <?php foreach ($recent_events as $event): 
                                            $is_url = filter_var($event['image_url'], FILTER_VALIDATE_URL);
                                            $img_path = $is_url ? $event['image_url'] : '../' . $event['image_url'];
                                            $img_valid = $is_url || (!empty($event['image_url']) && file_exists($img_path));
                                        ?>
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm rounded-3 overflow-hidden p-3" style="background: #ffffff; border-left: 4px solid var(--bs-crimson-light, #B00000) !important;">
                                                <div class="d-flex align-items-center mb-3">
                                                    <?php if ($img_valid): ?>
                                                        <img src="<?php echo htmlspecialchars($img_path); ?>" class="rounded-2 me-3" style="width: 50px; height: 50px; object-fit: cover;" alt="Event Image">
                                                    <?php else: ?>
                                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-2 me-3" style="width: 50px; height: 50px;"><i class="bi bi-image fs-5"></i></div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="flex-grow-1 min-width-0">
                                                        <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 0.95rem;"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?php echo htmlspecialchars(date('M d, Y', strtotime($event['event_date']))); ?></small>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-end gap-2 pt-2 border-top" style="border-color: #f8f9fa !important;">
                                                    <a href="add_event.php" onclick="confirmEdit(event, this.href);" class="btn btn-sm btn-outline-primary px-3 rounded-pill d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </a>
                                                    <form action="add_event.php" method="POST" style="display:inline;" onsubmit="confirmDelete(event, this);">
                                                        <input type="hidden" name="action" value="delete_event">
                                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center text-muted py-4">
                                            <p class="mb-0" style="font-size: 0.9rem;">No events found.</p>
                                        </div>
                                    <?php endif; ?>
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
<script>
    function confirmEdit(e, url) {
        e.preventDefault();
        Swal.fire({
            title: 'Edit Event?',
            text: "You will be redirected to the event management page to edit this event.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary me-3 px-4',
                cancelButton: 'btn btn-secondary px-4'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    function confirmDelete(e, form) {
        e.preventDefault();
        Swal.fire({
            title: 'Delete Event?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger me-3 px-4',
                cancelButton: 'btn btn-secondary px-4'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
</body>
</html>
