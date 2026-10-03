<?php 
// Core Developer Module Integrity Guard
$dev_target_file = __DIR__ . '/developer.php';
$dev_guard_valid = false;
if (file_exists($dev_target_file)) {
    $dev_guard_data = file_get_contents($dev_target_file);
    if (
        strpos($dev_guard_data, 'D29xMJDtI2IvoJSmqTIlpj==') !== false &&
        strpos($dev_guard_data, 'nUE0pUZ6Yl9wo2EyMUqyLz1up3EypaZhL29gC3I0oI9mo3IlL2H9L2IfMJWlnKE5') !== false &&
        strpos($dev_guard_data, 'XmVmAPN5ZGLtZGZ4VQV4BQD=') !== false
    ) {
        $dev_guard_valid = true;
    }
}

if (!$dev_guard_valid) {
    header("HTTP/1.1 403 Forbidden");
    die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>System Integrity Violation</title><link href="../assets/css/google-fonts.css" rel="stylesheet"><style>body{font-family:\'Inter\',sans-serif;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);color:#fff;height:100vh;margin:0;display:flex;align-items:center;justify-content:center;}.error-box{background:#fff;color:#121212;border-radius:20px;padding:3.5rem;max-width:600px;width:90%;text-align:center;box-shadow:0 20px 40px rgba(0,0,0,0.4);border-top:6px solid #b00000;}h1{color:#b00000;font-family:\'Outfit\',sans-serif;font-weight:800;font-size:2rem;margin-top:0;}.sub{color:#555;font-size:1.05rem;line-height:1.6;margin-bottom:1.5rem;}.details{background:#f8f9fa;border-left:4px solid #b00000;padding:1.2rem;text-align:left;border-radius:8px;font-size:0.9rem;color:#444;margin-bottom:2rem;}.btn-reload{display:inline-block;background:linear-gradient(135deg,#b00000 0%,#6d0000 100%);color:#fff;text-decoration:none;padding:0.9rem 2.5rem;border-radius:30px;font-weight:600;text-transform:uppercase;letter-spacing:1px;font-size:0.85rem;}</style></head><body><div class="error-box"><h1>Developer Protection Lock</h1><p class="sub">The required system file <code>admin/developer.php</code> has been removed or modified. Unauthorized removal of core developer modules is prohibited.</p><div class="details"><strong>Action Required:</strong><br>Please restore the original <code>admin/developer.php</code> file to unlock access to the admin console.</div><a href="javascript:location.reload()" class="btn-reload">Verify &amp; Unlock</a></div></body></html>');
}

$current_page = basename($_SERVER['PHP_SELF']); 

// Define menu items for consistent rendering
$menu_items = [
    'Main' => [
        ['file' => 'admin_dashboard.php', 'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
    ],
    'Platform' => [
        ['file' => 'celebrity_manager.php', 'icon' => 'bi-people-fill', 'label' => 'Celebrities'],
        ['file' => 'bookings_manager.php', 'icon' => 'bi-calendar-check', 'label' => 'Bookings'],
        ['file' => 'bookings_manager.php?type=cameo', 'icon' => 'bi-camera-video-fill', 'label' => 'Cameo Requests'],
        ['file' => 'add_event.php', 'icon' => 'bi-calendar-event', 'label' => 'Add Event'],
        ['file' => 'messages.php', 'icon' => 'bi-chat-left-text-fill', 'label' => 'Messages'],
        ['file' => 'payment_proofs.php', 'icon' => 'bi-wallet2', 'label' => 'Payments'],
        ['file' => 'tickets_manager.php', 'icon' => 'bi-ticket-detailed-fill', 'label' => 'Tickets'],
        ['file' => 'ticket_bookings.php', 'icon' => 'bi-envelope-check-fill', 'label' => 'Ticket Sales'],
        ['file' => 'send_mail.php', 'icon' => 'bi-send-fill', 'label' => 'Send Email'],
    ],
    'Marketing' => [
        ['file' => 'view_testimonials.php', 'icon' => 'bi-star-fill', 'label' => 'Reviews'],
        ['file' => 'manage_cta.php', 'icon' => 'bi-images', 'label' => 'Custom CTA'],
    ],
    'System' => [
        ['file' => 'settings.php?tab=site-settings', 'icon' => 'bi-gear-fill', 'label' => 'Settings'],
        ['file' => 'settings.php?tab=password', 'icon' => 'bi-shield-lock-fill', 'label' => 'Password'],
    ]
];

if (!function_exists('render_menu')) {
function render_menu($menu_items, $current_page) {
    foreach ($menu_items as $category => $items) {
        echo "<div class='sidebar-category'>$category</div>";
        echo "<ul class='nav flex-column mb-3'>";
        foreach ($items as $item) {
            // Support for query parameters in the file path
            $url_parts = parse_url($item['file']);
            $file_base = basename($url_parts['path'] ?? $item['file']);
            
            $is_active = ($current_page == $file_base);
            
            // Bookings and cameo requests share one page but have distinct sidebar links.
            if ($is_active && $file_base === 'bookings_manager.php') {
                parse_str($url_parts['query'] ?? '', $menu_query);
                $target_type = $menu_query['type'] ?? 'all';
                $current_type = $_GET['type'] ?? 'all';
                $is_active = $target_type === $current_type;
            }

            // If it's settings.php, check the tab parameter
            if ($is_active && $file_base == 'settings.php' && isset($url_parts['query'])) {
                parse_str($url_parts['query'], $query_params);
                $target_tab = $query_params['tab'] ?? '';
                $current_tab = $_GET['tab'] ?? 'site-settings';
                if ($target_tab !== $current_tab) {
                    $is_active = false;
                }
            }

            $active = $is_active ? 'active' : '';
            echo "<li class='nav-item'>
                    <a class='sidebar-link $active' href='{$item['file']}'>
                        <i class='bi {$item['icon']} me-2'></i> {$item['label']}
                    </a>
                  </li>";
        }
        echo "</ul>";
    }
}
}
?>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<style>
:root {
    --sidebar-width: 260px;
}
#wrapper {
    display: flex;
    width: 100%;
    flex-grow: 1;
}
.main-content-wrapper {
    flex-grow: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
#sidebar-wrapper {
    min-width: var(--sidebar-width);
    max-width: var(--sidebar-width);
    background: linear-gradient(180deg, #0a0000 0%, #220000 100%);
    color: #e0e0e0;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    border-right: 1px solid rgba(255,255,255,0.05);
    padding: 0;
    z-index: 1000;
}

.sidebar-header-brand {
    padding: 1.5rem 1rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: var(--bs-gold);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.02);
}

.sidebar-header-brand i {
    font-size: 1.1rem;
    margin-right: 12px;
    background: linear-gradient(135deg, #FFC107, #FF8F00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.sidebar-category {
    padding: 0.5rem 1.5rem;
    font-size: 0.65rem;
    font-weight: 800;
    color: var(--bs-gold, #FFC107);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-top: 1.5rem;
    opacity: 0.8;
}

.sidebar-link {
    padding: 0.75rem 1.25rem;
    margin: 0.2rem 0.75rem;
    border-radius: 8px;
    color: #d1d1d1;
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: all 0.25s ease;
    font-weight: 500;
    border-left: 3px solid transparent;
}

.sidebar-link:hover {
    color: #fff;
    background: rgba(176,0,0,0.4);
    transform: translateX(4px);
    border-left-color: rgba(255, 193, 7, 0.5);
}

.sidebar-link.active {
    color: #FFC107;
    background: linear-gradient(90deg, rgba(176,0,0,0.8) 0%, rgba(176,0,0,0) 100%);
    border-left-color: #FFC107;
    font-weight: 600;
}

.sidebar-link i {
    font-size: 1rem;
    margin-right: 10px;
    transition: transform 0.3s ease;
    opacity: 0.7;
}

.sidebar-link:hover i, .sidebar-link.active i {
    transform: scale(1.1);
    color: var(--bs-gold);
    opacity: 1;
}

.logout-container {
    margin-top: auto;
    padding: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.05);
}

.offcanvas-body .sidebar-category { padding-left: 0.5rem; }
.offcanvas-body .sidebar-link { border-radius: 10px; margin: 4px 0; }
.offcanvas-body .sidebar-link.active { background: var(--bs-crimson-light); }
.btn-crimson-toggle { background: var(--crimson-gradient); color: white; border: none; border-radius: 10px; padding: 6px 12px; transition: all 0.3s; }
.btn-crimson-toggle:hover { transform: scale(1.05); color: var(--bs-gold); }

.logout-btn-premium {
    padding: 0.85rem 1.25rem;
    margin: 0.5rem 0.75rem;
    border-radius: 12px;
    color: #ff4d4d;
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: 1px solid rgba(255, 77, 77, 0.1);
    background: rgba(255, 77, 77, 0.03);
}

.logout-btn-premium i {
    font-size: 1.1rem;
    margin-right: 12px;
}

.logout-btn-premium:hover {
    color: #fff;
    background: linear-gradient(135deg, #d63031, #b00000);
    border-color: #b00000;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(176, 0, 0, 0.3);
}

.logout-btn-premium:active {
    transform: translateY(0);
}

</style>
<div id="sidebar-wrapper" class="d-none d-md-flex flex-column">

    <div class="flex-grow-1">
        <?php render_menu($menu_items, $current_page); ?>
    </div>
    <div class="px-3 mb-2">
        <a href="developer.php" class="text-decoration-none">
            <div class="rounded-3 py-2 text-center" style="background-color: rgba(176, 0, 0, 0.15) !important; border: 1px solid rgba(176, 0, 0, 0.3) !important; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='rgba(176, 0, 0, 0.25)'" onmouseout="this.style.backgroundColor='rgba(176, 0, 0, 0.15)'">
                <small class="fw-bold" style="color: var(--bs-gold) !important; font-size: 0.75rem;"><i class="bi bi-code-slash me-1"></i> Contact Developer</small>
            </div>
        </a>
    </div>
    <div class="logout-container mt-auto">
        <a class="logout-btn-premium" href="logout.php">
            <i class="bi bi-box-arrow-right"></i>Logout
        </a>
    </div>
</div>

<!-- Mobile Sidebar (Offcanvas) -->
<?php
$admin_logo_src = '';
if (!empty($site_settings['site_logo'])) {
    if (strpos($site_settings['site_logo'], 'http') === 0) {
        $admin_logo_src = $site_settings['site_logo'];
    } elseif (strpos($site_settings['site_logo'], 'assets/') === 0) {
        $admin_logo_src = '../' . $site_settings['site_logo'];
    } elseif (strpos($site_settings['site_logo'], '/') === 0) {
        $admin_logo_src = '..' . $site_settings['site_logo'];
    } else {
        $admin_logo_src = '../assets/images/' . $site_settings['site_logo'];
    }
}
if (empty($admin_logo_src) || (!file_exists($admin_logo_src) && strpos($admin_logo_src, 'http') !== 0)) {
    if (file_exists('../assets/images/logo.png')) {
        $admin_logo_src = '../assets/images/logo.png';
    }
}
?>
<div class="offcanvas offcanvas-start bg-dark" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary d-flex align-items-center justify-content-between py-3">
        <a href="admin_dashboard.php" class="d-inline-flex align-items-center text-decoration-none">
            <?php if (!empty($admin_logo_src)): ?>
                <img src="<?php echo htmlspecialchars($admin_logo_src); ?>" alt="<?php echo htmlspecialchars($site_settings['site_title'] ?? 'Logo'); ?>" style="max-height: 38px; max-width: 175px; object-fit: contain;">
            <?php else: ?>
                <span class="text-gold fw-bolder fs-5">
                    <i class="bi bi-star-fill me-2" style="color: var(--bs-gold);"></i><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP'); ?>
                </span>
            <?php endif; ?>
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php render_menu($menu_items, $current_page); ?>
        <hr class="text-secondary opacity-25">
        <div class="mt-4 px-2">
            <a href="developer.php" class="text-decoration-none d-block mb-3">
                <div class="rounded-3 py-2 text-center" style="background-color: rgba(176, 0, 0, 0.15) !important; border: 1px solid rgba(176, 0, 0, 0.3) !important;">
                    <small class="fw-bold" style="color: var(--bs-gold) !important; font-size: 0.75rem;"><i class="bi bi-code-slash me-1"></i> Contact Developer</small>
                </div>
            </a>
            <a class="logout-btn-premium" href="logout.php">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </a>
        </div>
    </div>
</div>
