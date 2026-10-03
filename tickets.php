<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Filter and Search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$where_clauses = ["status = 'Available' AND available_qty > 0"];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(event_name LIKE :search OR venue LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($category !== '') {
    $where_clauses[] = "category = :category";
    $params[':category'] = $category;
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Count total records
$count_sql = "SELECT COUNT(*) FROM tickets $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch records with limit and offset
$sql = "SELECT * FROM tickets $where_sql ORDER BY id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for filter dropdown
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM tickets WHERE status = 'Available' AND available_qty > 0");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Premium Tickets | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
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

        .tickets-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .tickets-hero::before {
            display: none !important;
        }
        .tickets-hero h1 span {
            color: var(--secondary, #dfa92a);
        }
        .ticket-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease-in-out;
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .ticket-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(176, 0, 0, 0.12);
            border-color: rgba(194, 155, 87, 0.3);
        }
        .ticket-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
        }
        .ticket-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            transition: transform 0.5s ease;
        }
        .ticket-card:hover .ticket-img {
            transform: scale(1.08);
        }
        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(5px);
            color: #daa520;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 50px;
            border: 1px solid rgba(218, 165, 32, 0.4);
            letter-spacing: 1px;
            z-index: 2;
        }
        .price-tag {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: linear-gradient(135deg, #daa520 0%, #b8860b 100%);
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 0.5px;
            padding: 6px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.4);
            border: 1px solid rgba(255,255,255,0.2);
        }
        /* Luxury Filter Bar Styles */
        .filter-card-premium {
            background: #0f172a;
            color: #fff;
            border: 1px solid rgba(194, 155, 87, 0.25) !important;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .filter-label-premium {
            color: #daa520 !important;
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .filter-input-premium {
            background-color: #1e293b !important;
            color: #fff !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            border-radius: 8px !important;
            font-size: 0.95rem;
        }
        .filter-input-premium::placeholder {
            color: #94a3b8 !important;
        }
        .filter-input-premium:focus {
            border-color: #c29b57 !important;
            box-shadow: 0 0 0 0.25rem rgba(194, 155, 87, 0.2) !important;
        }
        .input-group-text-premium {
            background-color: #1e293b !important;
            color: #daa520 !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            border-right: none !important;
            border-radius: 8px 0 0 8px !important;
        }
        .filter-input-premium-right {
            border-radius: 0 8px 8px 0 !important;
        }
        .avail-badge-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        @keyframes blink {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }
        .ticket-details {
            padding: 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.35;
        }
        .ticket-meta {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .ticket-meta i {
            color: #c29b57;
            margin-right: 8px;
            font-size: 1rem;
        }
        .ticket-desc {
            font-size: 0.9rem;
            color: #475569;
            margin: 15px 0;
            line-height: 1.5;
            flex-grow: 1;
        }
        .avail-badge {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .btn-book {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            border: 2px solid #c29b57;
            transition: all 0.3s;
            font-weight: 700;
            border-radius: 50px;
            padding: 10px 24px;
        }
        .btn-book:hover {
            background: #c29b57;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(194, 155, 87, 0.4);
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Hero Section -->
    <section class="tickets-hero shadow-sm">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.8rem; font-size: 0.9rem;">Exclusive Access</p>
            <h1>Premium <span>Event Tickets</span></h1>
            <p class="lead fw-light">
                Book secure tickets for the most anticipated corporate events, concerts, private performances, and exclusive fan meets worldwide.
            </p>
        </div>
    </section>

    <!-- Content & Search Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <!-- Filter Bar -->
            <div class="card border-0 filter-card-premium p-4 mb-5">
                <form method="GET" action="tickets.php" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold filter-label-premium mb-2"><i class="bi bi-search me-1"></i>Search Events / Venues</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-premium"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control filter-input-premium filter-input-premium-right" placeholder="Type event name or venue..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label small fw-bold filter-label-premium mb-2"><i class="bi bi-tags me-1"></i>Event Category</label>
                            <?php if ($search !== '' || $category !== ''): ?>
                                <a href="tickets.php" class="text-gold small fw-bold text-decoration-none mb-2"><i class="bi bi-x-circle me-1"></i>Clear</a>
                            <?php endif; ?>
                        </div>
                        <select name="category" class="form-select filter-input-premium">
                            <option value="" style="background: #0f172a; color: #fff;">-- All Categories --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?> style="background: #0f172a; color: #fff;"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" class="btn btn-gold py-2.5 fw-bold text-uppercase rounded-pill" style="letter-spacing: 1px;"><i class="bi bi-funnel me-2"></i>Filter Tickets</button>
                    </div>
                </form>
            </div>

            <!-- Tickets Grid -->
            <div class="row g-4">
                <?php if (count($tickets) > 0): ?>
                    <?php foreach ($tickets as $t): 
                        $qty = (int)$t['available_qty'];
                        if ($qty <= 5) {
                            $avail_class = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10';
                            $avail_indicator = '<span class="avail-badge-indicator bg-danger me-2" style="animation: blink 1.5s infinite;"></span>';
                            $avail_text = "Only {$qty} Tickets Left!";
                        } else {
                            $avail_class = 'bg-success bg-opacity-10 text-success border border-success border-opacity-10';
                            $avail_indicator = '<span class="avail-badge-indicator bg-success me-2"></span>';
                            $avail_text = "{$qty} Tickets Available";
                        }
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="ticket-card">
                                <div class="ticket-img-wrapper">
                                    <span class="category-badge"><?php echo htmlspecialchars($t['category']); ?></span>
                                    <img src="<?php echo htmlspecialchars(!empty($t['image_path']) ? $t['image_path'] : 'assets/img/avater.jpg'); ?>" alt="<?php echo htmlspecialchars($t['event_name']); ?>" class="ticket-img" onerror="this.src='assets/img/avater.jpg';">
                                    <span class="price-tag"><?php echo format_currency($t['price']); ?></span>
                                </div>
                                <div class="ticket-details">
                                    <h3 class="event-title"><?php echo htmlspecialchars($t['event_name']); ?></h3>
                                    
                                    <div class="ticket-meta">
                                        <i class="bi bi-calendar-event"></i>
                                        <span><?php echo htmlspecialchars($t['event_date']); ?></span>
                                    </div>
                                    <div class="ticket-meta">
                                        <i class="bi bi-geo-alt"></i>
                                        <span><?php echo htmlspecialchars($t['venue']); ?></span>
                                    </div>
                                    
                                    <p class="ticket-desc"><?php echo htmlspecialchars($t['description']); ?></p>
                                    
                                    <div class="mt-auto">
                                        <div class="avail-badge <?php echo $avail_class; ?>"><?php echo $avail_indicator . $avail_text; ?></div>
                                        <div class="d-grid">
                                            <a href="book_ticket.php?id=<?php echo $t['id']; ?>" class="btn btn-book text-center">BOOK TICKET</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-lg-8 mx-auto text-center py-5">
                        <div class="card border-0 shadow-lg p-5" style="border-radius: 24px; border: 1px solid rgba(194, 155, 87, 0.25) !important; background: #fff;">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 100px; height: 100px; background: rgba(194, 155, 87, 0.1); border: 1px dashed rgba(194, 155, 87, 0.4); animation: pulse 2s infinite;">
                                    <i class="bi bi-ticket-perforated text-gold fs-1"></i>
                                </div>
                            </div>
                            <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; color: #0f172a; margin-bottom: 15px;">Exclusive Access Coming Soon</h3>
                            <p class="text-muted mx-auto mb-4" style="max-width: 550px; font-size: 1rem; line-height: 1.6;">
                                We are currently preparing our next release of exclusive event tickets and VIP access passes. Please check back soon or contact our concierge for private booking inquiries.
                            </p>
                            <div class="d-flex justify-content-center">
                                <a href="contact.php" class="btn btn-gold px-4 py-2.5 fw-bold" style="border-radius: 50px; letter-spacing: 0.5px; font-size: 0.9rem;"><i class="bi bi-chat-left-text me-2"></i>CONTACT CONCIERGE</a>
                            </div>
                        </div>
                    </div>
                    
                    <style>
                        @keyframes pulse {
                            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(194, 155, 87, 0.4); }
                            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(194, 155, 87, 0); }
                            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(194, 155, 87, 0); }
                        }
                    </style>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12 text-center">
                        <div class="pagination-container">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="tickets.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>"><i class="bi bi-chevron-left"></i></a></li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="tickets.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item"><a class="page-link" href="tickets.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>"><i class="bi bi-chevron-right"></i></a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
