<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

$search_query = isset($_GET['ref']) ? trim($_GET['ref']) : '';
$bookings = [];

if ($search_query !== '') {
    try {
        $stmt = $pdo->prepare("
            SELECT tb.*, t.event_name, t.price, t.event_date, t.venue, t.image_path, t.ticket_id
            FROM ticket_bookings tb 
            JOIN tickets t ON tb.ticket_id = t.id 
            WHERE tb.booking_reference = ?
        ");
        $stmt->execute([$search_query]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Tickets | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .lookup-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .lookup-hero::before {
            display: none !important;
        }
        .lookup-hero h1 span {
            color: var(--secondary, #dfa92a);
        }
        .lookup-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
            border: 1px solid rgba(218, 165, 32, 0.15);
            padding: 40px;
            position: relative;
            z-index: 10;
        }
        .form-control-premium {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            letter-spacing: 1.5px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .form-control-premium:focus {
            border-color: #daa520;
            box-shadow: 0 0 0 4px rgba(218, 165, 32, 0.15);
            outline: none;
        }
        .booking-row {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }
        .booking-row:hover {
            box-shadow: 0 8px 25px rgba(176, 0, 0, 0.06);
            border-color: rgba(218, 165, 32, 0.4);
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Hero Section -->
    <section class="lookup-hero shadow-sm">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.8rem; font-size: 0.9rem;">Secure Portal</p>
            <h1>My Booked <span>Tickets</span></h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Track, view, and manage your confirmed VIP event passes and tickets in real-time.
            </p>
        </div>
    </section>

    <!-- SweetAlert trigger if session alert exists -->
    <?php
        if (isset($_SESSION['alert'])) {
            $alert = $_SESSION['alert'];
            unset($_SESSION['alert']);
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: '{$alert['type']}',
                        title: '{$alert['title']}',
                        text: '{$alert['text']}',
                        confirmButtonColor: '#c29b57',
                    });
                });
            </script>";
        }
    ?>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Search Forms -->
                <div class="col-lg-8 mb-5">
                    <div class="lookup-card">
                        <h4 class="fw-bold mb-4 text-center text-dark" style="font-family: 'Outfit', sans-serif; text-transform: uppercase; font-size: 1.3rem; letter-spacing: 0.5px;"><i class="bi bi-search text-gold me-2"></i>Find Your Tickets</h4>
                        
                        <div class="row justify-content-center">
                            <!-- Look up by Booking reference -->
                            <div class="col-md-10">
                                <form method="GET" action="my_tickets.php">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-muted small text-uppercase d-block text-center mb-2" style="letter-spacing: 0.5px;">Booking Reference Number</label>
                                        <input type="text" name="ref" class="form-control form-control-premium text-center" placeholder="e.g. TKB-A3E5B1" value="<?php echo htmlspecialchars($search_query); ?>" required>
                                    </div>
                                    <div class="d-grid col-md-8 mx-auto mt-4">
                                        <button type="submit" class="btn btn-gold fw-bold rounded-pill py-2.5" style="letter-spacing: 0.5px; min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"><i class="bi bi-search"></i>Search Reference</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display Results -->
                <?php if ($search_query !== ''): ?>
                    <div class="col-lg-10">
                        <h4 class="fw-bold mb-4 text-center text-dark" style="font-family: 'Outfit', sans-serif; text-transform: uppercase; font-size: 1.3rem; letter-spacing: 0.5px;">Booking Status Results</h4>
                        
                        <?php if (count($bookings) > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($bookings as $b): 
                                    $badge_class = 'bg-secondary';
                                    if ($b['status'] === 'Approved') $badge_class = 'bg-success';
                                    if ($b['status'] === 'Rejected') $badge_class = 'bg-danger';
                                    if ($b['status'] === 'Pending') $badge_class = 'bg-warning text-dark';
                                ?>
                                    <div class="booking-row bg-white p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo htmlspecialchars(!empty($b['image_path']) ? $b['image_path'] : 'assets/img/avater.jpg'); ?>" alt="Event logo" class="rounded shadow-sm" style="width: 70px; height: 70px; object-fit: cover;" onerror="this.src='assets/img/avater.jpg';">
                                            <div>
                                                <h5 class="fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;"><?php echo htmlspecialchars($b['event_name']); ?></h5>
                                                <div class="small text-muted mb-1">Booking Ref: <strong><?php echo htmlspecialchars($b['booking_reference']); ?></strong></div>
                                                <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i><?php echo htmlspecialchars($b['event_date']); ?> | <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($b['venue']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center gap-3 justify-content-between">
                                            <div class="text-md-end">
                                                <div class="fw-bold text-gold fs-5 mb-1"><?php echo format_currency($b['price']); ?></div>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($b['status']); ?></span>
                                            </div>
                                            
                                            <div>
                                                <?php if ($b['status'] === 'Approved'): ?>
                                                    <a href="view_ticket.php?ref=<?php echo urlencode($b['booking_reference']); ?>" class="btn btn-outline-gold px-3 px-md-4 py-2 fw-bold" style="border-radius: 50px; font-size: 0.85rem;">VIEW TICKET</a>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary px-3 px-md-4 py-2 fw-bold" style="border-radius: 50px; font-size: 0.85rem;" disabled>PENDING</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm p-5 text-center bg-white" style="border-radius: 16px; border: 1px solid rgba(176, 0, 0, 0.15) !important;">
                                <i class="bi bi-exclamation-circle text-danger display-4 mb-3"></i>
                                <h5 class="fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">No Active Bookings Found</h5>
                                <p class="text-muted mb-0">No active ticket bookings were found matching the reference code <strong><?php echo htmlspecialchars($search_query); ?></strong>. Please double-check your booking reference or contact support.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
