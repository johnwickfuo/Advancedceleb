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

// --- EVENT MANAGEMENT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_event') {
        // Enforce max 2 events
        $countStmt = $pdo->query("SELECT COUNT(*) FROM events");
        $eventCount = $countStmt->fetchColumn();
        
        if ($eventCount >= 4) {
            $_SESSION['message'] = "Cannot add event. Maximum of 4 events allowed.";
            $_SESSION['message_type'] = "danger";
        } else {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $event_date = $_POST['event_date'];
            $location = $_POST['location'];
            $image_url = 'assets/img/about_exclusive.jpg';

            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/img/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filename = time() . '_' . basename($_FILES['event_image']['name']);
                $targetFile = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetFile)) {
                    $image_url = 'assets/img/' . $filename;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, image_url) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $description, $event_date, $location, $image_url])) {
                $_SESSION['message'] = "Event added successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error adding event.";
                $_SESSION['message_type'] = "danger";
            }
        }
        header("Location: add_event.php");
        exit;
    } elseif ($action === 'edit_event') {
        $id = $_POST['event_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $location = $_POST['location'];
        
        $stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $existing_event = $stmt->fetch(PDO::FETCH_ASSOC);
        $image_url = $existing_event['image_url'];

        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/img/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . '_' . basename($_FILES['event_image']['name']);
            $targetFile = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetFile)) {
                $image_url = 'assets/img/' . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_date=?, location=?, image_url=? WHERE id=?");
        if ($stmt->execute([$title, $description, $event_date, $location, $image_url, $id])) {
            $_SESSION['message'] = "Event updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating event.";
            $_SESSION['message_type'] = "danger";
        }
        header("Location: add_event.php");
        exit;
    } elseif ($action === 'delete_event') {
        $id = $_POST['event_id'];
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['message'] = "Event deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting event.";
            $_SESSION['message_type'] = "danger";
        }
        header("Location: add_event.php");
        exit;
    }
}
// ------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Manage Events - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
	<link href="assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
         .text-gold {color: var(--bs-gold, #FFC107) !important;}
         .bg-gold {background-color: var(--bs-gold, #FFC107) !important;}
         .card-enhanced {border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background-color: #fff; margin-bottom: 24px;}
         .form-control, .form-control:focus {border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: none; padding: 10px 15px;}
         .form-control:focus {border-color: var(--bs-crimson-light, #B00000); box-shadow: 0 0 0 0.25rem rgba(176, 0, 0, 0.15);}
         
         @media (max-width: 768px) {
             .main-content-wrapper { padding: 15px 10px; }
             #main-content { padding: 12px !important; }
         }
    </style>
</head>
<body>

     <?php include "nav.php" ?>
    
    <div id="wrapper">
        
        <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bolder mb-1"><i class="bi bi-calendar-event me-2 text-gold"></i> Manage Events</h4>
                        <p class="text-muted small mb-0">Create and curate exclusive upcoming celebrity events</p>
                    </div>
                    <button class="btn btn-gold px-4 py-2 w-100 w-sm-auto shadow-sm" data-bs-toggle="modal" data-bs-target="#eventModal" onclick="resetEventForm()">
                        <i class="bi bi-plus-circle me-2"></i>Add New Event
                    </button>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            Swal.fire({
                                icon: '<?php echo $_SESSION['message_type'] === "danger" ? "error" : "success"; ?>',
                                title: '<?php echo $_SESSION['message_type'] === "danger" ? "Error!" : "Success!"; ?>',
                                text: '<?php echo addslashes($_SESSION['message']); ?>',
                                customClass: {
                                    confirmButton: 'btn btn-gold px-4'
                                },
                                buttonsStyling: false
                            });
                        });
                    </script>
                    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                <?php endif; ?>

                <?php
                $eventsStmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
                $all_events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
                $total_events_count = count($all_events);
                ?>
                <div class="row g-4 mb-5">
                    <!-- Events List -->
                    <div class="col-12 mb-4">
                        <div class="card-enhanced p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0" style="color: #1e3c72;"><i class="bi bi-list-stars me-2"></i>Existing Events (<?php echo $total_events_count; ?>)</h5>
                            </div>
                            
                            <!-- Desktop Table View -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px;">Image</th>
                                            <th>Event Details</th>
                                            <th>Date & Location</th>
                                            <th class="text-end" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($total_events_count > 0): ?>
                                            <?php foreach ($all_events as $ev): ?>
                                            <tr>
                                                <td>
                                                    <?php $img_src = (strpos($ev['image_url'], 'http') === 0) ? htmlspecialchars($ev['image_url']) : '../' . htmlspecialchars($ev['image_url']); ?>
                                                    <img src="<?php echo $img_src; ?>" alt="Event Image" class="rounded shadow-sm" style="width: 80px; height: 60px; object-fit: cover;" onerror="this.src='../assets/img/about_exclusive.jpg';">
                                                </td>
                                                <td>
                                                    <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($ev['title']); ?></h6>
                                                    <small class="text-muted d-block text-truncate" style="max-width: 320px;"><?php echo htmlspecialchars($ev['description']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="mb-1 fw-semibold text-dark"><i class="bi bi-calendar-event me-2 text-gold"></i><?php echo htmlspecialchars(date('M d, Y', strtotime($ev['event_date']))); ?></div>
                                                    <small class="text-muted"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($ev['location']); ?></small>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-outline-primary me-1" title="Edit" onclick='editEvent(<?php echo htmlspecialchars(json_encode($ev), ENT_QUOTES, "UTF-8"); ?>)'>
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <form method="POST" style="display:inline;" onsubmit="confirmDelete(event, this);">
                                                        <input type="hidden" name="action" value="delete_event">
                                                        <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No events found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Card View -->
                            <div class="d-block d-md-none">
                                <?php if ($total_events_count > 0): ?>
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($all_events as $ev): 
                                            $img_src = (strpos($ev['image_url'], 'http') === 0) ? htmlspecialchars($ev['image_url']) : '../' . htmlspecialchars($ev['image_url']);
                                        ?>
                                        <div class="card border border-light-subtle shadow-sm rounded-4 overflow-hidden bg-white">
                                            <div class="position-relative" style="height: 140px; background: #0f172a;">
                                                <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" class="w-100 h-100 object-fit-cover" onerror="this.src='../assets/img/about_exclusive.jpg';">
                                                <div class="position-absolute bottom-0 start-0 w-100 p-2" style="background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);">
                                                    <span class="badge bg-gold text-dark fw-bold px-2.5 py-1" style="font-size: 0.75rem;"><i class="bi bi-calendar-event me-1"></i><?php echo htmlspecialchars(date('M d, Y', strtotime($ev['event_date']))); ?></span>
                                                </div>
                                            </div>
                                            <div class="p-3">
                                                <h6 class="fw-bold text-dark mb-1 fs-6"><?php echo htmlspecialchars($ev['title']); ?></h6>
                                                <div class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-gold me-1"></i><?php echo htmlspecialchars($ev['location']); ?></div>
                                                <p class="text-muted small mb-3" style="line-height: 1.5;"><?php echo htmlspecialchars(mb_strimwidth($ev['description'], 0, 110, '...')); ?></p>
                                                
                                                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                                    <button class="btn btn-sm btn-outline-primary px-3 py-1.5 rounded-pill fw-semibold" onclick='editEvent(<?php echo htmlspecialchars(json_encode($ev), ENT_QUOTES, "UTF-8"); ?>)'>
                                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                                    </button>
                                                    <form method="POST" class="d-inline" onsubmit="confirmDelete(event, this);">
                                                        <input type="hidden" name="action" value="delete_event">
                                                        <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-pill fw-semibold">
                                                            <i class="bi bi-trash me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">No events found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Edit Event Modal -->
                    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold" id="eventFormTitle" style="color: #1e3c72;"><i class="bi bi-plus-circle me-2"></i>Add New Event</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-3 p-sm-4">
                                    <?php if ($total_events_count >= 4): ?>
                                        <div id="maxEventsWarning" class="alert alert-warning mb-4">
                                            <i class="bi bi-exclamation-triangle me-2"></i> You have reached the maximum limit of 4 events. You must delete an existing event before adding a new one.
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" enctype="multipart/form-data" id="eventForm" <?php echo ($total_events_count >= 4) ? 'style="display:none;"' : ''; ?>>
                                        <input type="hidden" name="action" id="eventAction" value="add_event">
                                        <input type="hidden" name="event_id" id="eventId" value="">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-uppercase text-secondary">Event Title</label>
                                            <input type="text" name="title" id="eventTitle" class="form-control" required placeholder="e.g. VIP Gala Night">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-uppercase text-secondary">Event Date</label>
                                            <input type="date" name="event_date" id="eventDate" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-uppercase text-secondary">Location</label>
                                            <input type="text" name="location" id="eventLocation" class="form-control" required placeholder="e.g. Monte Carlo, Monaco">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-uppercase text-secondary">Description</label>
                                            <textarea name="description" id="eventDescription" class="form-control" rows="3" required placeholder="Brief description of the event..."></textarea>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-uppercase text-secondary">Display Image (Optional for Edit)</label>
                                            <input type="file" name="event_image" id="eventImage" class="form-control" accept="image/*" <?php echo ($total_events_count >= 4) ? '' : 'required'; ?>>
                                            <small class="text-muted mt-1 d-block" id="eventImageHelp">Select a high-quality image for the event.</small>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                            <button type="button" class="btn btn-secondary px-3 px-sm-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-gold px-3 px-sm-4 rounded-pill" id="submitEventBtn"><i class="bi bi-plus-circle me-2"></i>Add Event</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function editEvent(ev) {
                    document.getElementById('eventFormTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Event';
                    document.getElementById('eventAction').value = 'edit_event';
                    document.getElementById('eventId').value = ev.id;
                    document.getElementById('eventTitle').value = ev.title;
                    document.getElementById('eventDate').value = ev.event_date;
                    document.getElementById('eventLocation').value = ev.location;
                    document.getElementById('eventDescription').value = ev.description;
                    
                    document.getElementById('eventImage').required = false;
                    document.getElementById('eventImageHelp').innerText = "Leave empty to keep existing image.";
                    
                    document.getElementById('submitEventBtn').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Update Event';
                    
                    document.getElementById('eventForm').style.display = 'block';
                    let maxWarning = document.getElementById('maxEventsWarning');
                    if (maxWarning) maxWarning.style.display = 'none';
                    
                    var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                    eventModal.show();
                }

                function resetEventForm() {
                    document.getElementById('eventForm').reset();
                    document.getElementById('eventFormTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add New Event';
                    document.getElementById('eventAction').value = 'add_event';
                    document.getElementById('eventId').value = '';
                    document.getElementById('submitEventBtn').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Add Event';
                    
                    <?php if ($total_events_count >= 4): ?>
                    document.getElementById('eventForm').style.display = 'none';
                    document.getElementById('eventImage').required = false;
                    document.getElementById('maxEventsWarning').style.display = 'block';
                    <?php else: ?>
                    document.getElementById('eventImage').required = true;
                    document.getElementById('eventImageHelp').innerText = "Select a high-quality image for the event.";
                    <?php endif; ?>
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
            </div>
            <?php include "footer.php"; ?>
        </div>
    </div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
