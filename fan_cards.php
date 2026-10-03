<?php
require_once 'config.php';
require_once 'get_setting.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pagination setup
$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

$celebrities = [];
$total_pages = 1;
$hero_bg_image = 'assets/img/fancards_hero_bg.jpg';
try {
    // Count total records
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM celebrities");
    $total_records = $total_stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Fetch celebrities to display in the Fan Cards page
    $stmt = $pdo->prepare("SELECT * FROM celebrities ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching celebrities for fan cards: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fan Cards | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .fan-card-container {
            background-color: #faf8f5;
            padding: 40px 0;
        }
        .premium-fan-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
            height: 100%;
        }
        .premium-fan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .fan-card-img-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
        }
        .fan-card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .fan-card-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #f39c12;
            color: #fff;
            padding: 4px 10px;
            font-size: 0.7rem;
            font-weight: 800;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 2;
        }
        .fan-card-body {
            padding: 20px;
        }
        .fan-card-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fan-card-rating {
            color: #f39c12;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .fan-card-location {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
            margin-bottom: 8px;
        }
        .fan-card-price {
            color: #0088ff;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .fan-btn-blue {
            background-color: #0088ff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 0;
        }
        .fan-btn-blue:hover { background-color: #0077e6; color: white; }
        
        .fan-btn-orange {
            background-color: #f39c12;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 0;
        }
        .fan-btn-orange:hover { background-color: #e67e22; color: white; }
        
        .fan-btn-outline {
            background-color: transparent;
            color: #0088ff;
            border: 1px solid #0088ff;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 8px 0;
            margin-top: 10px;
        }
        .fan-btn-outline:hover {
            background-color: #0088ff;
            color: white;
        }

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
        .fancards-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .fancards-hero::before {
            display: none !important;
        }
    </style>
</head>
<body>
    
    <?php include "header.php"; ?>

    <!-- Premium Hero Section -->
    <section class="fancards-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Exclusive Access</p>
            <h1>Official Fan Cards</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Unlock VIP perks, secure priority bookings, and show your support for the world's most exclusive talents.
            </p>
        </div>
    </section>

    <!-- Fan Cards Grid -->
    <section class="fan-card-container py-4">
        <div class="container">
            <div class="row g-4">
                <?php if (count($celebrities) > 0): ?>
                    <?php foreach ($celebrities as $cel): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card premium-fan-card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div class="fan-card-img-wrapper position-relative" style="height: 200px;">
                                <?php if ($cel['is_featured']): ?>
                                <span class="fan-card-badge position-absolute d-flex align-items-center" style="top: 15px; left: 15px; background-color: #daa520; color: #fff; padding: 4px 10px; font-size: 0.7rem; font-weight: 800; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; z-index: 2;"><i class="bi bi-patch-check-fill" style="color: #1877F2; background: white; border-radius: 50%; font-size: 0.85rem; margin-right: 5px; display: inline-flex; justify-content: center; align-items: center; width: 14px; height: 14px; line-height: 1;"></i>FEATURED</span>
                                <?php endif; ?>
                                <img src="<?php echo !empty($cel['profile_picture']) ? htmlspecialchars($cel['profile_picture']) : 'assets/img/perf_default.jpg'; ?>" alt="<?php echo htmlspecialchars($cel['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;" onerror="this.src='assets/img/perf_default.jpg';">
                                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 45%; background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0) 100%); z-index: 2; pointer-events: none;"></div>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h5 class="fan-card-title fw-bold text-dark mb-0" style="font-family: 'Playfair Display', serif; font-size: 1.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($cel['name']); ?></h5>
                                </div>
                                <div class="fan-card-location text-muted" style="font-size: 0.85rem; margin-bottom: 8px;"><i class="bi bi-geo-alt me-1"></i>United States</div>
                                <div class="fan-card-price fw-bold" style="color: #b00000; font-size: 0.95rem; margin-bottom: 15px;"><i class="bi bi-tags me-1"></i><?php echo format_currency($cel['booking_price']); ?></div>
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <a href="book.php?celebrity_id=<?php echo $cel['id']; ?>" class="btn w-100 d-flex align-items-center justify-content-center" style="background-color: #b00000; color: white; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 700; padding: 8px 0; transition: background 0.2s;"><i class="bi bi-calendar3 me-2"></i> Book Now</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="donation.php" class="btn w-100 d-flex align-items-center justify-content-center" style="background-color: #daa520; color: white; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 700; padding: 8px 0; transition: background 0.2s;"><i class="bi bi-heart me-2"></i> Donate</a>
                                    </div>
                                </div>
                                <a href="book.php?celebrity_id=<?php echo $cel['id']; ?>" class="btn w-100 d-flex align-items-center justify-content-center" style="background-color: transparent; color: #b00000; border: 1px solid #b00000; border-radius: 6px; font-size: 0.85rem; font-weight: 700; padding: 8px 0; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#b00000'; this.style.color='white';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#b00000';"><i class="bi bi-person-vcard me-2"></i> Fan Card</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No fan cards available at the moment.</p>
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
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="bi bi-chevron-left"></i></a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="bi bi-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
