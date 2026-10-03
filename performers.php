<?php
require_once 'config.php';
require_once 'get_setting.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pagination & Search setup
$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
$params = [];

if ($search) {
    $whereClause = "WHERE name LIKE :search";
    $params[':search'] = "%{$search}%";
}

$celebrities = [];
try {
    // Count total records
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM celebrities $whereClause");
    $total_stmt->execute($params);
    $total_records = $total_stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Fetch records
    $stmt = $pdo->prepare("SELECT * FROM celebrities $whereClause ORDER BY name ASC LIMIT :limit OFFSET :offset");
    
    // Bind parameters manually due to mixed types
    if ($search) {
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching celebrities: " . $e->getMessage());
    $total_pages = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Talent Roster | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        /* Custom Pagination Theme - Premium */
        .pagination-container {
            text-align: center;
            width: 100%;
        }
        .pagination {
            gap: 8px;
            align-items: center;
            justify-content: center;
            border-radius: 50px;
            padding: 10px 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            display: inline-flex;
            margin: 20px auto;
        }
        .pagination .page-item {
            margin: 0;
        }
        .pagination .page-link {
            color: #1d3557;
            font-weight: 600;
            border: none;
            background: transparent;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: none;
        }
        .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
            color: #b00000;
            background-color: #fdf2f3;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(176, 0, 0, 0.15);
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #b00000 0%, #800000 100%);
            color: #fff;
            box-shadow: 0 6px 20px rgba(176, 0, 0, 0.35);
            transform: scale(1.1);
        }
        .pagination .page-item.disabled .page-link {
            background: transparent;
            color: #ced4da;
        }

        /* Booking Standards Section Styles */
        .standards-section {
            background-color: #faf8f5;
            padding: 6rem 0;
            border-top: 1px solid rgba(0, 0, 0, 0.03);
        }
        .standard-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.03);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            transition: all 0.4s ease;
        }
        .standard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(176, 0, 0, 0.06);
            border-color: rgba(176, 0, 0, 0.2);
        }
        .standard-icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(176, 0, 0, 0.08) 0%, rgba(218, 165, 32, 0.08) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 1px solid rgba(218, 165, 32, 0.2);
        }
        .standard-icon-wrapper i {
            font-size: 2rem;
            color: var(--primary);
        }
        .standard-card h4 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        .standard-card p {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    
    <?php include "header.php"; ?>

    <style>
        .performers-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .performers-hero::before {
            display: none !important;
        }
    </style>
    
    <!-- Hero Section -->
    <section class="performers-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Our Exclusive Celebrity</p>
            <h1>Celebrities Performers</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Browse our premium selection of world-class talents and secure the perfect star for your next exclusive event.
            </p>
        </div>
    </section>

    <section class="py-5" style="background: var(--dark);">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-6">
                    <form method="GET" action="performers.php" class="search-box">
                        <input type="text" name="search" id="searchInput" class="search-input w-100" placeholder="Search for a celebrity by name..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn"><i class="bi bi-search"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="row g-4" id="rosterGrid">
                <?php if (count($celebrities) > 0): ?>
                    <?php foreach ($celebrities as $cel): ?>
                    <div class="col-lg-4 col-md-6 mb-4 performer-col">
                        <div class="cel-card">
                            <?php if ($cel['is_verified']): ?>
                            <div class="cel-badge"><i class="bi bi-patch-check-fill me-1"></i> Verified</div>
                            <?php endif; ?>
                            
                            <div class="cel-img-wrapper">
                                <img src="<?php echo !empty($cel['profile_picture']) ? htmlspecialchars($cel['profile_picture']) : 'assets/img/perf_default.jpg'; ?>" alt="<?php echo htmlspecialchars($cel['name']); ?>" class="cel-img" onerror="this.src='assets/img/perf_default.jpg';">
                                <div class="cel-overlay"></div>
                            </div>
                            <div class="cel-info">
                                <h3 class="cel-name"><?php echo htmlspecialchars($cel['name']); ?></h3>
                                <div class="cel-price">Starts at <?php echo format_currency($cel['booking_price']); ?></div>
                                <p class="cel-desc"><?php echo htmlspecialchars($cel['description']); ?></p>
                                <a href="book.php?celebrity_id=<?php echo $cel['id']; ?>" class="btn btn-gold w-100 py-2"><i class="bi bi-calendar-check me-2"></i>Book <?php echo htmlspecialchars(explode(' ', trim($cel['name']))[0]); ?></a>
                                <?php if ((int)($cel['cameo_enabled'] ?? 0) === 1 && (float)($cel['cameo_price'] ?? 0) > 0): ?>
                                <a href="cameo.php?celebrity_id=<?php echo (int)$cel['id']; ?>" class="btn btn-outline-danger w-100 py-2 mt-2 fw-bold"><i class="bi bi-camera-video me-2"></i>Request Cameo Video — <?php echo format_currency($cel['cameo_price']); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-5">
                        <p>No celebrities found in the database.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination UI -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="row mt-5">
                <div class="col-12 pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Booking Standards Section -->
    <section class="standards-section">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <p style="color: var(--secondary) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Elite Commitments</p>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; color: #1e3c72;">Our Booking Guarantees</h2>
                <div style="width: 80px; height: 3px; background-color: var(--secondary); margin: 1.5rem auto 0;"></div>
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-lg-3 col-md-6">
                    <div class="standard-card">
                        <div class="standard-icon-wrapper">
                            <i class="bi bi-patch-check animate-pulse"></i>
                        </div>
                        <h4>Direct Sourcing</h4>
                        <p>We work directly with primary representation and management, ensuring direct communication channels and guaranteed quotes.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="standard-card">
                        <div class="standard-icon-wrapper">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4>Discreet Management</h4>
                        <p>All inquiries, private contracts, and bookings are treated with absolute client confidentiality and strict NDA compliance.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="standard-card">
                        <div class="standard-icon-wrapper">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h4>Contractual Escrow</h4>
                        <p>Fees are securely escrowed and contractually protected under standard entertainment law guidelines to secure bookings.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="standard-card">
                        <div class="standard-icon-wrapper">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h4>24/7 VIP Concierge</h4>
                        <p>From initial booking to on-site execution, you have a dedicated senior coordinator supervising logistics around the clock.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <?php include "footer.php"; ?>
    <script>

    </script>
</body>
</html>
