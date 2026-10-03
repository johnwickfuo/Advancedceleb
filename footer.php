<?php
if (!isset($use_logo) || !isset($logo_path)) {
    require_once __DIR__ . '/get_setting.php';
}
?>

<?php if (($site_settings['enable_cargo_scroll'] ?? '0') === '1'): ?>
<!-- Premium Footer Cargo Scroll -->
<div class="cargo-scroll-banner">
    <div class="cargo-scroll-wrap">
        <div class="cargo-scroll-content">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <span class="cargo-scroll-item">
                    <i class="bi bi-star-fill text-gold me-2"></i>
                    <?php echo htmlspecialchars($site_settings['cargo_scroll_text'] ?? ''); ?>
                </span>
            <?php endfor; ?>
        </div>
        <div class="cargo-scroll-content" aria-hidden="true">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <span class="cargo-scroll-item">
                    <i class="bi bi-star-fill text-gold me-2"></i>
                    <?php echo htmlspecialchars($site_settings['cargo_scroll_text'] ?? ''); ?>
                </span>
            <?php endfor; ?>
        </div>
    </div>
</div>
<style>
.cargo-scroll-banner {
    background: #0b1120;
    color: #f8f9fa;
    overflow: hidden;
    border-bottom: 2px solid var(--secondary, #daa520);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    padding: 12px 0;
    font-family: 'Inter', sans-serif;
    position: relative;
    z-index: 10;
}
.cargo-scroll-wrap {
    display: flex;
    width: max-content;
}
.cargo-scroll-content {
    display: flex;
    white-space: nowrap;
    animation: cargo-scroll-anim 35s linear infinite;
}
.cargo-scroll-item {
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 1px;
    padding: 0 50px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
}
.cargo-scroll-item .text-gold {
    color: var(--secondary, #daa520) !important;
}
.cargo-scroll-wrap:hover .cargo-scroll-content {
    animation-play-state: paused;
}
@keyframes cargo-scroll-anim {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-50%, 0, 0);
    }
}
</style>
<?php endif; ?>

<!-- Premium Footer -->
<footer style="background-color: #1e3c72; color: #f8f9fa; padding-top: 80px; padding-bottom: 30px;">
    <div class="container">
        <div class="row gy-5 mb-5 border-bottom border-light pb-5" style="border-color: rgba(255,255,255,0.1) !important;">
            <div class="col-lg-4 col-md-6 pe-lg-5">
                <a class="navbar-brand text-white serif-font fs-2 fw-bold d-block mb-3" href="index.php">
                    <?php if ($use_logo): ?>
                        <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Booking'); ?>" style="max-height: 45px;">
                    <?php else: ?>
                        <i class="bi bi-star-fill" style="color: var(--secondary); margin-right: 8px;"></i>
                        VIP<span style="font-weight: 300;">Booking</span>
                    <?php endif; ?>
                </a>
                <p style="color: #ced4da; line-height: 1.8; font-size: 0.95rem;">
                    The premier platform for booking world-class talent, celebrities, and speakers for exclusive private and corporate events globally. Experience true luxury, reliability, and seamless management.
                </p>
                
                <div class="d-flex gap-3 mt-4">
                    <?php if (isset($site_settings['use_instagram']) && $site_settings['use_instagram'] === '1'): ?>
                    <a href="<?php echo htmlspecialchars($site_settings['instagram_url'] ?? '#'); ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: var(--secondary); color: #fff; transition: all 0.3s;" onmouseover="this.style.background='#fff';this.style.color='var(--secondary)';this.style.transform='translateY(-3px)';" onmouseout="this.style.background='var(--secondary)';this.style.color='#fff';this.style.transform='none';">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (isset($site_settings['use_twitter']) && $site_settings['use_twitter'] === '1'): ?>
                    <a href="<?php echo htmlspecialchars($site_settings['twitter_url'] ?? '#'); ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: var(--secondary); color: #fff; transition: all 0.3s;" onmouseover="this.style.background='#fff';this.style.color='var(--secondary)';this.style.transform='translateY(-3px)';" onmouseout="this.style.background='var(--secondary)';this.style.color='#fff';this.style.transform='none';">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (isset($site_settings['use_facebook']) && $site_settings['use_facebook'] === '1'): ?>
                    <a href="<?php echo htmlspecialchars($site_settings['facebook_url'] ?? '#'); ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: var(--secondary); color: #fff; transition: all 0.3s;" onmouseover="this.style.background='#fff';this.style.color='var(--secondary)';this.style.transform='translateY(-3px)';" onmouseout="this.style.background='var(--secondary)';this.style.color='#fff';this.style.transform='none';">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (isset($site_settings['use_linkedin']) && $site_settings['use_linkedin'] === '1'): ?>
                    <a href="<?php echo htmlspecialchars($site_settings['linkedin_url'] ?? '#'); ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: var(--secondary); color: #fff; transition: all 0.3s;" onmouseover="this.style.background='#fff';this.style.color='var(--secondary)';this.style.transform='translateY(-3px)';" onmouseout="this.style.background='var(--secondary)';this.style.color='#fff';this.style.transform='none';">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-4 fw-bold" style="color: var(--secondary); letter-spacing: 1px; font-family: 'Outfit', sans-serif;">Quick Links</h5>
                <ul class="list-unstyled" style="line-height: 2.2; font-family: 'Outfit', sans-serif;">
                    <li><a href="index.php" class="text-decoration-none" style="color: #ced4da; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ced4da'">Home</a></li>
                    <li><a href="about.php" class="text-decoration-none" style="color: #ced4da; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ced4da'">About Us</a></li>
                    <li><a href="services.php" class="text-decoration-none" style="color: #ced4da; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ced4da'">Services</a></li>
                    <li><a href="performers.php" class="text-decoration-none" style="color: #ced4da; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ced4da'">Performers</a></li>
                    <li><a href="fan_cards.php" class="text-decoration-none" style="color: #ced4da; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ced4da'">Fan Cards</a></li>
                    <li><a href="contact.php" class="text-decoration-none" style="color: #ced4da; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ced4da'">Contact</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-4 fw-bold" style="color: var(--secondary); letter-spacing: 1px; font-family: 'Outfit', sans-serif;">Contact Us</h5>
                <ul class="list-unstyled" style="color: #ced4da; line-height: 2; font-family: 'Outfit', sans-serif;">
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-envelope me-3 mt-1" style="color: var(--secondary);"></i> 
                        <span><?php echo htmlspecialchars(!empty($site_settings['contact_email']) ? $site_settings['contact_email'] : 'info@vipcelebrity.com'); ?></span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-telephone me-3 mt-1" style="color: var(--secondary);"></i> 
                        <span><?php echo htmlspecialchars(!empty($site_settings['contact_phone']) ? $site_settings['contact_phone'] : '+1 (310) 555-0199'); ?></span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="bi bi-geo-alt me-3 mt-1" style="color: var(--secondary);"></i> 
                        <span><?php echo nl2br(htmlspecialchars(!empty($site_settings['contact_address']) ? $site_settings['contact_address'] : "100 VIP Avenue\nBeverly Hills, CA 90210")); ?></span>
                    </li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="mb-4 fw-bold" style="color: var(--secondary); letter-spacing: 1px; font-family: 'Outfit', sans-serif;">Newsletter</h5>
                <p style="color: #ced4da; font-size: 0.9rem; margin-bottom: 20px; font-family: 'Outfit', sans-serif;">Subscribe to get the latest updates on new exclusive talent joining our roster.</p>
                <form action="#" method="POST" class="d-flex bg-white rounded p-1" style="box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <input type="email" class="form-control border-0 shadow-none" placeholder="Your Email Address" required style="font-size: 0.9rem;">
                    <button type="submit" class="btn btn-gold rounded" style="padding: 0.5rem 1.2rem; font-size: 0.9rem; margin-left: 5px;">JOIN</button>
                </form>
            </div>
        </div>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="mb-2 mb-md-0" style="color: #adb5bd; font-size: 0.85rem;">&copy; <?php echo date('Y'); ?> VIP Celebrity Bookings. All Rights Reserved.</p>
            <div class="d-flex gap-4">
                <a href="privacy.php" class="text-decoration-none" style="color: #adb5bd; font-size: 0.85rem; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#adb5bd'">Privacy Policy</a>
                <a href="terms.php" class="text-decoration-none" style="color: #adb5bd; font-size: 0.85rem; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#adb5bd'">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Premium Navbar Scroll Effect (For pages using the premium navbar)
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-premium');
        if(navbar) {
            if (window.scrollY > 50) {
                navbar.style.padding = '0.8rem 0';
                navbar.style.background = 'rgba(176, 0, 0, 0.98)'; // Cargo Crimson with opacity
                navbar.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
            } else {
                navbar.style.padding = '1.2rem 0';
                navbar.style.background = '#b00000'; // Solid Cargo Crimson
                navbar.style.boxShadow = '0 4px 15px rgba(0,0,0,0.15)';
            }
        }
    });
</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/swiper-bundle.min.js"></script>
<script>
    // Explicitly initialize the hero carousel to ensure auto-cycling
    document.addEventListener('DOMContentLoaded', function() {
        var myCarouselEl = document.querySelector('#heroCarousel');
        if (myCarouselEl) {
            var carousel = new bootstrap.Carousel(myCarouselEl, {
                interval: 5000,
                ride: 'carousel',
                wrap: true
            });
            carousel.cycle();
        }
    });
</script>

<?php if (($site_settings['enable_back_to_top'] ?? '1') === '1'): ?>
<!-- Back to Top Button -->
<a href="#" class="back-to-top d-flex align-items-center justify-content-center" aria-label="Scroll to top"><i class="bi bi-arrow-up-short"></i></a>

<style>
    .back-to-top {
        position: fixed;
        visibility: hidden;
        opacity: 0;
        left: 20px;
        bottom: 20px;
        z-index: 996;
        background: var(--secondary, #daa520);
        width: 45px;
        height: 45px;
        border-radius: 5px;
        transition: all 0.4s;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .back-to-top i {
        font-size: 28px;
        color: #fff;
        line-height: 0;
    }
    .back-to-top:hover {
        background: var(--primary, #b00000);
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    }
    .back-to-top.active {
        visibility: visible;
        opacity: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let backtotop = document.querySelector('.back-to-top');
        if (backtotop) {
            const toggleBacktotop = () => {
                if (window.scrollY > 200) {
                    backtotop.classList.add('active');
                } else {
                    backtotop.classList.remove('active');
                }
            }
            window.addEventListener('load', toggleBacktotop);
            window.addEventListener('scroll', toggleBacktotop);
            
            backtotop.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    });
</script>
<?php endif; ?>
<?php if (($site_settings['enable_translator'] ?? '0') === '1'): include 'custom_script.php'; endif; ?>

<?php if (($site_settings['use_smartsupp'] ?? '0') === '1' && !empty($site_settings['smartsupp_code'])): ?>
<!-- Live Chat Integration -->
<?php echo $site_settings['smartsupp_code']; ?>
<?php endif; ?>

<?php if (($site_settings['use_whatsapp'] ?? '0') === '1' || ($site_settings['use_telegram'] ?? '0') === '1'): ?>
<div class="floating-social-sidebar">
    <?php if (($site_settings['use_whatsapp'] ?? '0') === '1' && !empty($site_settings['whatsapp_number'])): ?>
    <a href="https://wa.me/<?php echo urlencode(preg_replace('/[^0-9]/', '', $site_settings['whatsapp_number'])); ?>" target="_blank" class="social-bubble whatsapp-bubble" aria-label="WhatsApp">
        <i class="bi bi-whatsapp"></i>
        <i class="bi bi-whatsapp hover-icon"></i>
    </a>
    <?php endif; ?>
    <?php if (($site_settings['use_telegram'] ?? '0') === '1' && !empty($site_settings['telegram_username'])): ?>
    <?php
        $telegram_username = ltrim($site_settings['telegram_username'], '@');
    ?>
    <a href="https://t.me/<?php echo htmlspecialchars($telegram_username); ?>" target="_blank" class="social-bubble telegram-bubble" aria-label="Telegram">
        <i class="bi bi-telegram"></i>
        <i class="bi bi-telegram hover-icon"></i>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php 
$show_desktop = ($site_settings['enable_live_bookings_desktop'] ?? '0') === '1';
$show_mobile = ($site_settings['enable_live_bookings_mobile'] ?? '0') === '1';

if ($show_desktop || $show_mobile): 
    $widget_class = "live-booking-card";
    if ($show_desktop && !$show_mobile) {
        $widget_class .= " d-none d-md-block";
    } elseif (!$show_desktop && $show_mobile) {
        $widget_class .= " d-block d-md-none";
    } else {
        $widget_class .= " d-block";
    }
?>
<!-- Premium Live Booking Activity Widget -->
<div id="live-bookings-widget" class="<?php echo $widget_class; ?>" style="display: none;">
    <div class="live-booking-body">
        <div class="live-booking-icon-wrap">
            <span class="live-booking-icon-pulse"></span>
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="live-booking-content">
            <span class="live-booking-tag">Verified Activity</span>
            <p id="live-booking-text" class="mb-0"></p>
        </div>
    </div>
</div>

<style>
.live-booking-card {
    position: fixed;
    bottom: 25px;
    left: 25px;
    z-index: 10000;
    max-width: 310px;
    width: calc(100% - 50px);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    padding: 10px 15px;
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform: translateY(100px);
    opacity: 0;
}
.live-booking-card.show {
    transform: translateY(0);
    opacity: 1;
}
.live-booking-body {
    display: flex;
    align-items: center;
    gap: 12px;
}
.live-booking-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: rgba(176, 0, 0, 0.08);
    border-radius: 50%;
    color: #b00000;
    flex-shrink: 0;
    box-shadow: 0 3px 6px rgba(176, 0, 0, 0.1);
}
.live-booking-icon-wrap i {
    font-size: 1.05rem;
    z-index: 1;
    animation: liveIconPulse 2s infinite ease-in-out;
}
.live-booking-icon-pulse {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    background: rgba(176, 0, 0, 0.15);
    animation: livePulse 2s infinite ease-in-out;
}
.live-booking-content {
    flex-grow: 1;
    font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    line-height: 1.3;
}
.live-booking-tag {
    display: inline-block;
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #B00000;
    margin-bottom: 1px;
}
.live-booking-content p {
    color: #1e293b;
    font-size: 0.8rem;
    line-height: 1.35;
    margin: 0;
}
.live-booking-highlight {
    font-weight: 700;
    color: #0f172a;
}
.live-booking-action {
    color: #c1121f;
    font-weight: 600;
}

@keyframes livePulse {
    0% {
        transform: scale(0.95);
        opacity: 0.8;
    }
    100% {
        transform: scale(1.6);
        opacity: 0;
    }
}
@keyframes liveIconPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

@media (max-width: 576px) {
    .live-booking-card {
        bottom: 20px;
        left: 20px;
        width: calc(100% - 40px);
        padding: 12px 16px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifications = [
        "<span class='live-booking-highlight'>Jennifer</span> from <span class='fw-semibold text-muted'>Islamabad</span> just booked a <span class='live-booking-action'>meet & greet</span>",
        "<span class='live-booking-highlight'>Ayesha</span> from <span class='fw-semibold text-muted'>Dubai</span> just requested a <span class='live-booking-action'>celebrity appearance</span>",
        "<span class='live-booking-highlight'>Michael</span> from <span class='fw-semibold text-muted'>London</span> just booked a <span class='live-booking-action'>private event</span>",
        "<span class='live-booking-highlight'>Sarah</span> from <span class='fw-semibold text-muted'>Toronto</span> just confirmed a <span class='live-booking-action'>celebrity booking</span>",
        "<span class='live-booking-highlight'>Jessica</span> from <span class='fw-semibold text-muted'>United State</span> just booked a <span class='live-booking-action'>VIP table reservation</span>",
        "<span class='live-booking-highlight'>Elena</span> from <span class='fw-semibold text-muted'>Paris</span> just reserved a <span class='live-booking-action'>private concert performance</span>",
        "<span class='live-booking-highlight'>Andrew</span> from <span class='fw-semibold text-muted'>Tokyo</span> just requested a <span class='live-booking-action'>custom birthday shoutout</span>",
        "<span class='live-booking-highlight'>Mason</span> from <span class='fw-semibold text-muted'>Lahore</span> just booked an <span class='live-booking-action'>executive event booking</span>",
        "<span class='live-booking-highlight'>Oliver</span> from <span class='fw-semibold text-muted'>New York</span> just confirmed a <span class='live-booking-action'>celebrity brand partnership</span>",
        "<span class='live-booking-highlight'>Chloe</span> from <span class='fw-semibold text-muted'>Sydney</span> just booked a <span class='live-booking-action'>keynote speaker appearance</span>",
        "<span class='live-booking-highlight'>Omar</span> from <span class='fw-semibold text-muted'>Doha</span> just requested a <span class='live-booking-action'>luxury yacht performance</span>",
        "<span class='live-booking-highlight'>Sophia</span> from <span class='fw-semibold text-muted'>Rome</span> just booked a <span class='live-booking-action'>private fashion showcase</span>",
        "<span class='live-booking-highlight'>Liam</span> from <span class='fw-semibold text-muted'>Dublin</span> just confirmed a <span class='live-booking-action'>festival headliner slot</span>",
        "<span class='live-booking-highlight'>Nisha</span> from <span class='fw-semibold text-muted'>Mumbai</span> just booked a <span class='live-booking-action'>red carpet meet & greet</span>",
        "<span class='live-booking-highlight'>Lucas</span> from <span class='fw-semibold text-muted'>Berlin</span> just requested a <span class='live-booking-action'>private wedding performance</span>",
        "<span class='live-booking-highlight'>Isabella</span> from <span class='fw-semibold text-muted'>Madrid</span> just confirmed a <span class='live-booking-action'>corporate team meet-up</span>",
        "<span class='live-booking-highlight'>Tariq</span> from <span class='fw-semibold text-muted'>Abu Dhabi</span> just booked a <span class='live-booking-action'>celebrity appearance</span>",
        "<span class='live-booking-highlight'>Amara</span> from <span class='fw-semibold text-muted'>United State</span> just requested a <span class='live-booking-action'>club hosting appearance</span>",
        "<span class='live-booking-highlight'>Mateo</span> from <span class='fw-semibold text-muted'>United State</span> just booked a <span class='live-booking-action'>private DJ set</span>",
        "<span class='live-booking-highlight'>Emily</span> from <span class='fw-semibold text-muted'>Chicago</span> just confirmed a <span class='live-booking-action'>corporate keynote speaking slot</span>"
    ];

    let currentIndex = 0;
    const widget = document.getElementById('live-bookings-widget');
    const textEl = document.getElementById('live-booking-text');

    if (!widget || !textEl) return;

    // Show widget in layout initially
    widget.style.display = 'block';

    function showNextNotification() {
        // Update content
        textEl.innerHTML = notifications[currentIndex];

        // Slide in
        widget.classList.add('show');

        // Wait 4-5 seconds, then slide out
        setTimeout(function() {
            widget.classList.remove('show');
            
            // Wait brief transition delay, then queue next
            setTimeout(function() {
                currentIndex = (currentIndex + 1) % notifications.length;
                showNextNotification();
            }, 1000); // 1s cooldown between entries
        }, 4500); // 4.5s visibility duration
    }

    // Initial delay before starting the cycle
    setTimeout(showNextNotification, 2000);
});
</script>
<?php endif; ?>

<?php
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
    
    $alert_type = json_encode($alert['type'] ?? 'info');
    $alert_title = json_encode($alert['title'] ?? '');
    $alert_text = json_encode($alert['text'] ?? '');
    $alert_html = isset($alert['html']) ? json_encode($alert['html']) : 'null';
    $alert_redirect = isset($alert['redirect']) ? json_encode($alert['redirect']) : 'null';
    $confirm_btn_text = json_encode($alert['confirmButtonText'] ?? 'OK');
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var alertConfig = {
                icon: {$alert_type},
                title: {$alert_title},
                confirmButtonColor: '#b00000',
                confirmButtonText: {$confirm_btn_text}
            };
            var alertHtml = {$alert_html};
            if (alertHtml) {
                alertConfig.html = alertHtml;
            } else {
                alertConfig.text = {$alert_text};
            }
            Swal.fire(alertConfig).then(function(result) {
                var redirectUrl = {$alert_redirect};
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
        });
    </script>";
}
?>

<?php if ((($site_settings['disable_right_click_copy'] ?? '0') === '1') && strpos($_SERVER['SCRIPT_NAME'], '/admin/') === false): ?>
<script>
if (!window.__copy_protection_active) {
    (function() {
        window.__copy_protection_active = true;
        var style = document.createElement('style');
        style.innerHTML = "*:not(input):not(textarea):not(select):not([contenteditable='true']){-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;user-select:none!important;} img,picture,svg,video{-webkit-user-drag:none!important;user-drag:none!important;-webkit-touch-callout:none!important;} input,textarea,select{-webkit-user-select:auto!important;user-select:auto!important;}";
        document.head.appendChild(style);
        function isInput(t){ return t && (t.tagName==='INPUT'||t.tagName==='TEXTAREA'||t.isContentEditable); }
        document.addEventListener('contextmenu', function(e){ if(!isInput(e.target)) e.preventDefault(); }, true);
        document.addEventListener('selectstart', function(e){ if(!isInput(e.target)) e.preventDefault(); }, true);
        document.addEventListener('dragstart', function(e){ e.preventDefault(); }, true);
        document.addEventListener('copy', function(e){ if(!isInput(e.target)) e.preventDefault(); }, true);
        document.addEventListener('cut', function(e){ if(!isInput(e.target)) e.preventDefault(); }, true);
    })();
}
</script>
<?php endif; ?>
