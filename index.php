<?php
require_once 'config.php';
require_once 'get_setting.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch Featured Celebrities
$featured_celebrities = [];
try {
    $stmt = $pdo->query("SELECT * FROM celebrities WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 6");
    $featured_celebrities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching featured celebrities: " . $e->getMessage());
}

// Fetch Active Events
$active_events = [];
try {
    $stmt = $pdo->query("SELECT * FROM events WHERE is_active = 1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 4");
    $active_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
}

// Fetch Reviews
$reviews = [];
try {
    $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY rating DESC, submission_date DESC LIMIT 12");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching reviews: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?> | Exclusive Talent</title>
    <?php include "head.php"; ?>
</head>
<body>
    
    <?php include "header.php"; ?>

    <!-- Hero Section Carousel -->
    <header id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <!-- Indicators -->
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="hero-section">
                    <?php $slide1_bg = !empty($site_settings['slider_bg_1']) ? 'assets/images/' . $site_settings['slider_bg_1'] : 'assets/images/slide1.jpg'; ?>
                    <div class="hero-bg" style="background-image: url('<?php echo htmlspecialchars($slide1_bg); ?>');"></div>
                    <div class="hero-overlay"></div>
                    <div class="container position-relative z-1">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="hero-content">
                                    <span class="d-inline-block py-1 px-3 rounded-pill mb-4" style="background: rgba(218, 165, 32, 0.15); color: #ffffff; font-weight: 700; font-size: 0.85rem; letter-spacing: 1px; border: 1px solid rgba(218, 165, 32, 0.4);">THE ULTIMATE VIP EXPERIENCE</span>
                                    <h1>Book Global Icons</h1>
                                    <p>Elevate your exclusive events with world-renowned celebrities, artists, and speakers.</p>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <a href="tickets.php" class="btn btn-gold">Book Ticket</a>
                                        <a href="my_tickets.php" class="btn btn-outline-gold">Track Ticket</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="hero-section">
                    <?php $slide2_bg = !empty($site_settings['slider_bg_2']) ? 'assets/images/' . $site_settings['slider_bg_2'] : 'assets/images/slide2.jpg'; ?>
                    <div class="hero-bg" style="background-image: url('<?php echo htmlspecialchars($slide2_bg); ?>');"></div>
                    <div class="hero-overlay"></div>
                    <div class="container position-relative z-1">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="hero-content">
                                    <span class="d-inline-block py-1 px-3 rounded-pill mb-4" style="background: rgba(218, 165, 32, 0.15); color: #ffffff; font-weight: 700; font-size: 0.85rem; letter-spacing: 1px; border: 1px solid rgba(218, 165, 32, 0.4);">PREMIUM ENTERTAINMENT</span>
                                    <h1>Live Performances</h1>
                                    <p>Secure top-tier musicians and bands for unforgettable live experiences.</p>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <a href="tickets.php" class="btn btn-gold">View Roster</a>
                                        <a href="contact.php" class="btn btn-outline-gold">Contact Us</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <div class="hero-section">
                    <?php $slide3_bg = !empty($site_settings['slider_bg_3']) ? 'assets/images/' . $site_settings['slider_bg_3'] : 'assets/images/slide3.jpg'; ?>
                    <div class="hero-bg" style="background-image: url('<?php echo htmlspecialchars($slide3_bg); ?>');"></div>
                    <div class="hero-overlay"></div>
                    <div class="container position-relative z-1">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="hero-content">
                                    <span class="d-inline-block py-1 px-3 rounded-pill mb-4" style="background: rgba(218, 165, 32, 0.15); color: #ffffff; font-weight: 700; font-size: 0.85rem; letter-spacing: 1px; border: 1px solid rgba(218, 165, 32, 0.4);">EXECUTIVE BOOKINGS</span>
                                    <h1>Keynote Speakers</h1>
                                    <p>Transform your summits with insights from global industry leaders.</p>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <a href="services.php" class="btn btn-gold">Our Services</a>
                                        <a href="book.php" class="btn btn-outline-gold">Book Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </header>

    <?php if (($site_settings['enable_cargo_scroll'] ?? '0') === '1'): ?>
    <!-- Cargo Scroll Marquee -->
    <style>
    .cargo-marquee-container {
        background: var(--primary-dark);
        color: #fff;
        padding: 12px 0;
        overflow: hidden;
        position: relative;
        border-top: 1px solid var(--secondary);
        border-bottom: 1px solid var(--secondary);
        z-index: 10;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .cargo-marquee-content {
        display: flex;
        white-space: nowrap;
        animation: cargoMarquee 35s linear infinite;
        width: fit-content;
    }
    .cargo-marquee-container:hover .cargo-marquee-content {
        animation-play-state: paused;
    }
    .cargo-marquee-text {
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 2px;
        padding-right: 3rem;
        display: inline-flex;
        align-items: center;
    }
    .cargo-marquee-text i {
        color: var(--secondary);
        margin: 0 15px;
        font-size: 0.8rem;
    }
    @keyframes cargoMarquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    </style>
    <div class="cargo-marquee-container">
        <div class="cargo-marquee-content">
            <?php 
                $marquee_text = htmlspecialchars($site_settings['cargo_scroll_text'] ?? 'Exclusive Talents Available Worldwide!');
                // Repeat text multiple times to create a seamless infinite scrolling loop
                for ($i = 0; $i < 10; $i++) {
                    echo '<span class="cargo-marquee-text"><i class="bi bi-diamond-fill"></i>' . $marquee_text . '</span>';
                }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Celebrities Section -->
    <section id="celebrities" class="section-padding bg-pattern" style="background-color: #f5f2f0;">
        <div class="container">
            <div class="section-title">
                <p>Exclusive Roster</p>
                <h2>Featured Celebrities</h2>
            </div>
            
            <div class="row g-4">
                <?php if (count($featured_celebrities) > 0): ?>
                    <?php foreach ($featured_celebrities as $cel): ?>
                    <div class="col-lg-4 col-md-6">
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
                                <a href="cameo.php?celebrity_id=<?php echo (int)$cel['id']; ?>" class="btn btn-outline-danger w-100 py-2 mt-2 fw-bold">
                                    <i class="bi bi-camera-video me-2"></i>Request Cameo Video<?php if (isset($cel['cameo_price']) && (float)$cel['cameo_price'] > 0): ?> — <?php echo format_currency($cel['cameo_price']); ?><?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 px-3">
                        <div style="max-width:520px; margin:0 auto; padding:3rem 2rem; background:linear-gradient(135deg,rgba(109,0,0,0.06),rgba(255,193,7,0.06)); border:1px solid rgba(255,193,7,0.2); border-radius:20px; box-shadow:0 8px 40px rgba(109,0,0,0.08);">
                            <div style="width:72px;height:72px;margin:0 auto 1.5rem;background:linear-gradient(135deg,#6D0000,#B00000);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(176,0,0,0.35);">
                                <i class="bi bi-stars" style="font-size:1.8rem;color:#FFC107;"></i>
                            </div>
                            <h5 style="font-weight:800;letter-spacing:2px;text-transform:uppercase;color:#2a0000;font-size:0.9rem;margin-bottom:0.5rem;">EXCLUSIVE TALENT ROSTER</h5>
                            <div style="width:40px;height:2px;background:linear-gradient(90deg,#B00000,#FFC107);margin:0.75rem auto 1.25rem;border-radius:2px;"></div>
                            <p style="font-size:1.05rem;color:#555;font-weight:500;line-height:1.7;margin-bottom:1.5rem;">
                                Our curated talent roster is currently being refined.<br>
                                <span style="font-size:0.9rem;color:#888;font-weight:400;">Exceptional acts are added by invitation only.</span>
                            </p>
                            <a href="contact.php" style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#8A0000,#B00000);color:#FFC107;font-weight:700;font-size:0.8rem;letter-spacing:1.5px;text-transform:uppercase;padding:0.65rem 1.75rem;border-radius:50px;text-decoration:none;box-shadow:0 4px 16px rgba(176,0,0,0.3);transition:all 0.3s;">
                                <i class="bi bi-envelope-fill"></i> Enquire About Talent
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5 pt-3">
                <a href="performers.php" class="btn btn-gold">View Full Roster</a>
            </div>
        </div>
    </section>

    <!-- Exclusive Access CTA Section -->
    <style>
    .parallax-cta {
        position: relative;
        padding: 7rem 0;
        min-height: 550px;
        background: url('assets/img/celebrity_club_bg.jpg') center/cover fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
    }
    .parallax-cta::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(176,0,0,0.85) 0%, rgba(218,165,32,0.6) 100%);
        z-index: 1;
    }
    .parallax-content {
        position: relative;
        z-index: 2;
    }
    .cta-title {
        font-family: 'Playfair Display', serif;
        font-size: 4rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 1.5rem;
        letter-spacing: -1px;
    }
    .cta-subtitle {
        font-family: 'Outfit', sans-serif;
        color: #ffffff;
        font-size: 1.1rem;
        letter-spacing: 5px;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 2rem;
    }
    .btn-gold-glow {
        display: inline-block;
        background: var(--secondary);
        color: var(--primary-dark) !important;
        border: 1px solid var(--secondary);
        padding: 1.25rem 3.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        margin-top: 1rem;
        z-index: 1;
        box-shadow: 0 0 30px rgba(218, 165, 32, 0.4);
        transition: all 0.3s ease;
        border-radius: 4px;
    }
    .btn-gold-glow:hover {
        background: #ffffff;
        color: var(--primary-dark) !important;
        border-color: #ffffff;
        box-shadow: 0 0 40px rgba(255, 255, 255, 0.6);
        transform: translateY(-2px);
    }
    @media (max-width: 768px) {
        .cta-title { font-size: 2rem; margin-bottom: 1rem; line-height: 1.2; }
        .cta-subtitle { font-size: 0.9rem; letter-spacing: 2px; margin-bottom: 1rem; }
        .parallax-cta { padding: 5rem 1.5rem; min-height: 420px; }
        .parallax-cta p.lead { font-size: 1rem; padding: 0 1rem; }
        .btn-gold-glow { padding: 0.8rem 2rem; font-size: 0.85rem; }
    }
    </style>

    <section class="parallax-cta">
        <div class="container parallax-content">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="cta-subtitle"><i class="bi bi-stars me-2"></i>The Ultimate Prestige<i class="bi bi-stars ms-2"></i></div>
                    <h2 class="cta-title">Make The Impossible, Possible.</h2>
                    <p class="lead mb-4 mx-auto" style="color: rgba(255, 255, 255, 0.9); max-width: 600px; font-weight: 300;">Secure the world's most sought-after talent for your next exclusive event. Our dedicated concierge is waiting to bring your vision to life.</p>
                    <a href="book.php" class="btn btn-gold-glow"><span style="position: relative; z-index: 2;">Begin Your Inquiry</span></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Events Section -->
    <section id="events" class="section-padding" style="background-color: #f5f2f5;">
        <div class="container">
            <div class="section-title text-center">
                <p>Global Occasions</p>
                <h2>Featured Events</h2>
            </div>
            
            <div class="row g-4 mt-2">
                <?php if (count($active_events) > 0): ?>
                    <?php foreach ($active_events as $event): ?>
                    <div class="col-lg-6">
                        <div class="event-card">
                            <img src="<?php echo !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'assets/img/about_exclusive.jpg'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-img">
                            <div class="event-overlay"></div>
                            <div class="event-content">
                                <span class="event-date"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-location">
                                    <i class="bi bi-geo-alt-fill me-2" style="color: var(--secondary);"></i>
                                    <?php echo htmlspecialchars($event['location'] ?? 'Exclusive Location'); ?>
                                </div>
                                <div class="event-btn-container">
                                    <a href="book.php?event_id=<?php echo $event['id']; ?>" class="event-book-btn">
                                        <i class="bi bi-calendar-check me-2"></i>Book Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Static Premium Events if Database is Empty -->
                    <div class="col-lg-6">
                        <div class="event-card">
                            <img src="assets/img/event_monaco.jpg" alt="Monaco Grand Prix" class="event-img">
                            <div class="event-overlay"></div>
                            <div class="event-content">
                                <span class="event-date">MAY 25, 2027</span>
                                <h3 class="event-title">Monaco Grand Prix VIP Afterparty</h3>
                                <div class="event-location">
                                    <i class="bi bi-geo-alt-fill me-2" style="color: var(--secondary);"></i> Monte Carlo, Monaco
                                </div>
                                <div class="event-btn-container">
                                    <a href="book.php?event_title=Monaco%20Grand%20Prix%20VIP%20Afterparty" class="event-book-btn">
                                        <i class="bi bi-calendar-check me-2"></i>Book Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="event-card">
                            <img src="assets/img/event_dubai.jpg" alt="Dubai Gala" class="event-img">
                            <div class="event-overlay"></div>
                            <div class="event-content">
                                <span class="event-date">NOV 12, 2027</span>
                                <h3 class="event-title">Royal Dubai Charity Gala</h3>
                                <div class="event-location">
                                    <i class="bi bi-geo-alt-fill me-2" style="color: var(--secondary);"></i> Burj Al Arab, Dubai
                                </div>
                                <div class="event-btn-container">
                                    <a href="book.php?event_title=Royal%20Dubai%20Charity%20Gala" class="event-book-btn">
                                        <i class="bi bi-calendar-check me-2"></i>Book Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="event-card">
                            <img src="assets/img/event_cannes.jpg" alt="Cannes Film Festival" class="event-img">
                            <div class="event-overlay"></div>
                            <div class="event-content">
                                <span class="event-date">MAY 18, 2027</span>
                                <h3 class="event-title">Cannes Film Festival VIP Gala</h3>
                                <div class="event-location">
                                    <i class="bi bi-geo-alt-fill me-2" style="color: var(--secondary);"></i> Boulevard de la Croisette, Cannes
                                </div>
                                <div class="event-btn-container">
                                    <a href="book.php?event_title=Cannes%20Film%20Festival%20VIP%20Gala" class="event-book-btn">
                                        <i class="bi bi-calendar-check me-2"></i>Book Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="event-card">
                            <img src="assets/img/event_paris.jpg" alt="Paris Fashion Week" class="event-img">
                            <div class="event-overlay"></div>
                            <div class="event-content">
                                <span class="event-date">OCT 04, 2027</span>
                                <h3 class="event-title">Paris Fashion Week Afterparty</h3>
                                <div class="event-location">
                                    <i class="bi bi-geo-alt-fill me-2" style="color: var(--secondary);"></i> Le Marais, Paris
                                </div>
                                <div class="event-btn-container">
                                    <a href="book.php?event_title=Paris%20Fashion%20Week%20Afterparty" class="event-book-btn">
                                        <i class="bi bi-calendar-check me-2"></i>Book Event
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5 pt-3">
                <a href="book.php" class="btn btn-outline-gold px-4 px-md-5 py-2.5 py-md-3 fw-bold" style="letter-spacing: 0.5px;"><i class="bi bi-calendar-event me-2"></i>INQUIRE NOW</a>
            </div>
        </div>
    </section>

    <!-- VIP Concierge Services Section -->
    <style>
    .vip-concierge-section {
        background: #ffca7a;
        position: relative;
        overflow: hidden;
        color: var(--primary-dark);
        padding: 6rem 0;
        min-height: 550px;
        display: flex;
        align-items: center;
    }
    .vip-concierge-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--secondary), transparent);
        opacity: 0.3;
    }
    .concierge-card {
        padding: 3rem 2rem;
        border: 1px solid rgba(176, 0, 0, 0.1);
        background: #ffffff;
        border-radius: 16px;
        transition: all 0.4s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(176, 0, 0, 0.05);
    }
    .concierge-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 3px;
        background: var(--secondary);
        transition: width 0.4s ease;
    }
    .concierge-card:hover {
        transform: translateY(-10px);
        background: #ffffff;
        border-color: var(--secondary);
        box-shadow: 0 20px 40px rgba(176, 0, 0, 0.15);
    }
    .concierge-card:hover::after {
        width: 100%;
    }
    .concierge-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: var(--primary-dark);
        border: 1px solid var(--secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
        color: #fff;
        font-size: 2rem;
        transition: all 0.4s ease;
    }
    .concierge-card:hover .concierge-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }
    .concierge-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 1rem;
        letter-spacing: 3px;
        color: var(--primary-dark) !important;
    }
    @media (max-width: 768px) {
        .concierge-title {
            font-size: 1.1rem;
            letter-spacing: 1.5px;
        }
    }
    .concierge-text {
        color: #555;
        font-weight: 500;
        line-height: 1.7;
        font-size: 0.95rem;
    }
    /* Subtle background glow */
    .vip-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.5) 0%, transparent 70%);
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: 0;
    }
    </style>

    <section class="vip-concierge-section">
        <div class="vip-glow"></div>
        <div class="container position-relative z-1">
            <div class="row mb-5 justify-content-center text-center">
                <div class="col-lg-8">
                    <span class="text-gold fw-bold text-uppercase mb-3 d-block" style="letter-spacing: 4px; font-size: 0.75rem;"><i class="bi bi-diamond-fill me-2" style="font-size: 0.5rem; vertical-align: middle;"></i>Exclusive Access<i class="bi bi-diamond-fill ms-2" style="font-size: 0.5rem; vertical-align: middle;"></i></span>
                    <h2 class="display-4 fw-bold mb-4" style="color: var(--primary-dark); font-family: 'Playfair Display', serif;">The VIP Concierge</h2>
                    <p class="lead mx-auto" style="color: #444; font-weight: 500; max-width: 700px;">Experience white-glove service tailored for the world's most discerning clients. We handle every detail, so you don't have to.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="concierge-card text-center text-md-start">
                        <div class="concierge-icon-wrapper mx-auto mx-md-0">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h4 class="concierge-title text-white">Discreet & Secure</h4>
                        <p class="concierge-text">Ironclad NDAs, private jet tarmac access, and elite close-protection security details. Your privacy is our absolute highest priority.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="concierge-card text-center text-md-start">
                        <div class="concierge-icon-wrapper mx-auto mx-md-0">
                            <i class="bi bi-globe2"></i>
                        </div>
                        <h4 class="concierge-title text-white">Global Reach</h4>
                        <p class="concierge-text">From private islands in the Maldives to exclusive chateaus in France, our network ensures seamless talent booking anywhere on Earth.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="concierge-card text-center text-md-start">
                        <div class="concierge-icon-wrapper mx-auto mx-md-0">
                            <i class="bi bi-gem"></i>
                        </div>
                        <h4 class="concierge-title text-white">Bespoke Demands</h4>
                        <p class="concierge-text">Specific rider requirements? Midnight caviar cravings? Rare vintage champagne? We fulfill every intricate detail to exact specifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Custom Experience Split CTA -->
    <style>
        .split-cta-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            background-color: var(--primary-dark);
        }
        .split-cta-bg {
            background: linear-gradient(135deg, #b00000 0%, #FFC107 100%);
            padding: 4rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        .split-cta-img {
            background: url('assets/img/cta_bg.jpg') center/cover;
            height: 100%;
            min-height: 350px;
            position: relative;
        }
        .split-cta-img::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 70%;
            background: linear-gradient(to top, rgba(176,0,0,0.85) 0%, transparent 100%);
            pointer-events: none;
        }
        .split-cta-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            line-height: 1.3;
            letter-spacing: 1px;
            color: #ffffff !important;
        }
        .split-cta-text {
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .btn-cta-white {
            background-color: white;
            color: var(--primary-dark);
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            width: fit-content;
        }
        .btn-cta-white:hover {
            background-color: var(--secondary);
            color: white;
            transform: translateY(-2px);
        }
        .btn-cta-white i {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        /* Premium Carousel Controls */
        #ctaCarousel .carousel-control-prev,
        #ctaCarousel .carousel-control-next {
            width: 12%;
            opacity: 0;
            transition: all 0.4s ease;
        }
        #ctaCarousel:hover .carousel-control-prev,
        #ctaCarousel:hover .carousel-control-next {
            opacity: 1;
        }
        .premium-nav-icon {
            background-color: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 193, 7, 0.6);
            color: #FFC107;
            font-size: 1.4rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        #ctaCarousel .carousel-control-prev:hover .premium-nav-icon,
        #ctaCarousel .carousel-control-next:hover .premium-nav-icon {
            background-color: #FFC107;
            color: #000;
            transform: scale(1.15);
            box-shadow: 0 5px 20px rgba(255,193,7,0.4);
        }
        #ctaCarousel .carousel-indicators {
            margin-bottom: 2rem;
            z-index: 10;
        }
        #ctaCarousel .carousel-indicators button {
            width: 25px;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.4);
            border: none;
            border-radius: 4px;
            margin: 0 6px;
            transition: all 0.4s ease;
        }
        #ctaCarousel .carousel-indicators button.active {
            background-color: #FFC107;
            width: 40px;
            box-shadow: 0 0 8px rgba(255,193,7,0.6);
        }

        @media (max-width: 991px) {
            .split-cta-bg {
                padding: 2.5rem 1.5rem;
            }
            .split-cta-img {
                min-height: 250px;
            }
            .split-cta-title {
                font-size: 1.8rem;
            }
        }
    </style>
    <?php
    try {
        $cta_images = $pdo->query("SELECT image_path FROM cta_images ORDER BY id DESC LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $cta_images = [];
    }
    if (empty($cta_images)) {
        // Fallback default images
        $cta_images = [
            'assets/img/cta_img_1.jpg',
            'assets/img/cta_img_2.jpg',
            'assets/img/cta_img_3.jpg',
            'assets/img/cta_img_4.jpg'
        ];
    }
    ?>
    <section class="py-5" style="background-color: var(--primary);">
        <div class="container py-4">
            <div class="row g-0 split-cta-card">
                <div class="col-lg-6">
                    <div class="split-cta-bg">
                        <h2 class="split-cta-title"><?php echo htmlspecialchars($site_settings['cta_title'] ?? 'Need a Custom Celebrity Experience?'); ?></h2>
                        <p class="split-cta-text">
                            <?php echo htmlspecialchars($site_settings['cta_desc'] ?? 'Our team specializes in creating bespoke celebrity experiences tailored to your specific needs. Whether you\'re planning a corporate event, private party, or special occasion, we can help you create unforgettable memories.'); ?>
                        </p>
                        <a href="<?php echo htmlspecialchars($site_settings['cta_btn_link'] ?? 'book.php'); ?>" class="btn-cta-white">
                            <i class="bi bi-cursor-fill me-2"></i> Book Celebrity
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <?php if(count($cta_images) > 1): ?>
                        <div id="ctaCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="3500">
                            <div class="carousel-indicators" style="z-index: 10;">
                                <?php foreach($cta_images as $index => $img_path): ?>
                                    <button type="button" data-bs-target="#ctaCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $index + 1; ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-inner h-100">
                                <?php foreach($cta_images as $index => $img_path): ?>
                                    <div class="carousel-item h-100 <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <div class="split-cta-img" style="background-image: url('<?php echo strpos($img_path, 'http') !== false ? htmlspecialchars($img_path) : htmlspecialchars($img_path); ?>');"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#ctaCarousel" data-bs-slide="prev" style="z-index: 10;">
                                <div class="premium-nav-icon">
                                    <i class="bi bi-chevron-left"></i>
                                </div>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#ctaCarousel" data-bs-slide="next" style="z-index: 10;">
                                <div class="premium-nav-icon">
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="split-cta-img" style="background-image: url('<?php echo strpos($cta_images[0], 'http') !== false ? htmlspecialchars($cta_images[0]) : htmlspecialchars($cta_images[0]); ?>');"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>


    <!-- Reviews Section (Cargo Design - Light) -->
    <style>
     .testimonials-light-wrap {
        background-color: #fceded;
        position: relative;
        overflow: hidden;
    }

    .testimonial-glass-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid var(--secondary);
        border-radius: 24px;
        padding: 40px;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        position: relative;
        box-shadow: 0 10px 30px rgba(176, 0, 0, 0.05);
    }

    .testimonial-glass-card:hover {
        background: rgba(255, 255, 255, 0.9);
        border-color: var(--secondary);
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 15px 40px rgba(176, 0, 0, 0.1);
    }

    .testimonial-quote-bubble {
        color: var(--primary-dark);
        font-size: 1.1rem;
        line-height: 1.8;
        font-weight: 500;
        margin-bottom: 30px;
        position: relative;
        z-index: 2;
        min-height: 120px;
    }

    .testimonial-client-meta {
        display: flex;
        align-items: center;
        border-top: 1px solid rgba(176, 0, 0, 0.1);
        padding-top: 25px;
    }

    .client-avatar-wrap {
        position: relative;
    }

    .client-avatar-wrap::after {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        padding: 2px;
        background: linear-gradient(135deg, var(--secondary), var(--primary-dark));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
    }

    .testimonial-client-img {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        object-fit: cover;
    }

    .client-info-text {
        margin-left: 15px;
    }

    .client-info-text h6 {
        color: var(--primary-dark);
        margin: 0;
        font-weight: 700;
        font-size: 1rem;
    }

    .client-info-text span {
        color: var(--secondary);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .testimonial-stars-gold {
        color: var(--secondary);
        font-size: 0.85rem;
        margin-bottom: 15px;
    }
    .swiper-pagination-premium .swiper-pagination-bullet {
        background: rgba(176, 0, 0, 0.3);
        opacity: 1;
    }
    .swiper-pagination-premium .swiper-pagination-bullet-active {
        background: var(--primary-dark);
    }
    </style>

    <section class="py-5 testimonials-light-wrap">
        <div class="container py-5 position-relative z-1">
            <div class="text-center mb-5">
                <span class="text-gold fw-bold text-uppercase mb-2 d-block" style="letter-spacing: 4px; font-size: 0.7rem;">Client Experiences</span>
                <h2 class="display-5 fw-bold mb-0" style="color: var(--primary-dark); font-family: 'Playfair Display', serif;">What Our Clients Say</h2>
                <div class="mx-auto mt-3" style="width: 60px; height: 3px; background: var(--secondary); border-radius: 10px;"></div>
            </div>

            <?php if (count($reviews) > 0): ?>
                <div class="swiper testimonialsSwiper px-2 py-4">
                    <div class="swiper-wrapper">
                        <?php foreach ($reviews as $review): 
                            $has_img = !empty($review['client_image_path']);
                            $img_url = $has_img ? htmlspecialchars($review['client_image_path']) : '';
                            
                            $default_svg = '<img src="assets/images/default-avatar.svg" class="testimonial-client-img" style="background: #fff;" alt="Default Avatar">';
                        ?>
                            <div class="swiper-slide h-auto">
                                <div class="testimonial-glass-card">
                                    <div class="testimonial-stars-gold">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star-fill <?php echo $i <= $review['rating'] ? '' : 'text-muted opacity-25'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="testimonial-quote-bubble">
                                        "<?php echo htmlspecialchars($review['testimonial_text']); ?>"
                                    </div>

                                    <div class="testimonial-client-meta mt-auto">
                                        <div class="client-avatar-wrap">
                                            <?php if ($has_img): ?>
                                                <img src="<?php echo $img_url; ?>" class="testimonial-client-img" alt="Client">
                                            <?php else: ?>
                                                <?php echo $default_svg; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="client-info-text text-start">
                                            <h6><?php echo htmlspecialchars($review['client_name']); ?></h6>
                                            <span><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($review['client_location']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Pagination -->
                    <div class="swiper-pagination swiper-pagination-premium mt-5 position-relative"></div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof Swiper !== 'undefined') {
                            new Swiper(".testimonialsSwiper", {
                                slidesPerView: 1,
                                spaceBetween: 30,
                                grabCursor: true,
                                loop: true,
                                autoplay: {
                                    delay: 4000,
                                    disableOnInteraction: false,
                                },
                                pagination: {
                                    el: ".swiper-pagination",
                                    clickable: true,
                                },
                                breakpoints: {
                                    768: { slidesPerView: 2 },
                                    1024: { slidesPerView: 3 }
                                }
                            });
                        }
                    });
                </script>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="d-inline-block p-4 rounded-circle mb-3" style="background: rgba(176,0,0,0.05); border: 1px solid rgba(176,0,0,0.1);">
                        <i class="bi bi-chat-quote fs-1 text-muted opacity-50"></i>
                    </div>
                    <h4 class="fw-bold" style="color: var(--primary-dark);">No reviews yet</h4>
                    <p class="text-muted opacity-75">Check back soon to see what our clients have to say!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Global Impact / Numbers Section -->
    <style>
    .impact-section {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #4a0000 100%);
        padding: 4.5rem 0;
        position: relative;
        overflow: hidden;
    }
    .impact-section::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><filter id="noiseFilter"><feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23noiseFilter)"/></svg>');
        opacity: 0.04;
        pointer-events: none;
    }
    .impact-number {
        font-family: 'Playfair Display', serif;
        font-size: 4.5rem;
        font-weight: 700;
        color: var(--secondary);
        line-height: 1;
        margin-bottom: 0.75rem;
        text-shadow: 0 10px 30px rgba(0,0,0,0.4);
    }
    .impact-label {
        font-family: 'Outfit', sans-serif;
        color: #fff;
        font-weight: 600;
        letter-spacing: 4px;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .impact-divider {
        width: 1px;
        height: 120px;
        background: linear-gradient(to bottom, transparent, rgba(218, 165, 32, 0.4), transparent);
        margin: 0 auto;
    }
    @media (max-width: 768px) {
        .impact-divider {
            width: 80%;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(218, 165, 32, 0.4), transparent);
            margin: 2.5rem auto;
        }
    }
    </style>

    <section class="impact-section">
        <div class="container position-relative z-1">
            <div class="row align-items-center text-center">
                <div class="col-md-3">
                    <div class="impact-number">500<span style="font-size: 3rem; vertical-align: top; color: rgba(218,165,32,0.7);">+</span></div>
                    <div class="impact-label">Exclusive Talents</div>
                </div>
                
                <div class="col-md-1 d-none d-md-block">
                    <div class="impact-divider"></div>
                </div>
                <div class="col-md-12 d-block d-md-none"><div class="impact-divider"></div></div>
                
                <div class="col-md-4">
                    <div class="impact-number">50<span style="font-size: 3rem; vertical-align: top; color: rgba(218,165,32,0.7);">+</span></div>
                    <div class="impact-label">Countries Served</div>
                </div>
                
                <div class="col-md-1 d-none d-md-block">
                    <div class="impact-divider"></div>
                </div>
                <div class="col-md-12 d-block d-md-none"><div class="impact-divider"></div></div>
                
                <div class="col-md-3">
                    <div class="impact-number">24<span style="font-size: 3rem; color: rgba(218,165,32,0.7);">/</span>7</div>
                    <div class="impact-label">Concierge Support</div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
