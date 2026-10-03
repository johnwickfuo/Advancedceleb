<?php
if (!isset($use_logo) || !isset($logo_path)) {
    require_once __DIR__ . '/get_setting.php';
}
?>
<!-- Premium Navbar -->
<nav class="navbar navbar-expand-lg navbar-premium fixed-top">
    <div class="container">
        <a class="navbar-brand text-white serif-font fs-3 fw-bold d-flex align-items-center" href="index.php">
            <?php if ($use_logo): ?>
                <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Booking'); ?>" style="max-height: 38px; margin-right: 10px;">
            <?php else: ?>
                <i class="bi bi-star-fill" style="color: var(--secondary); font-size: 1.5rem; margin-right: 8px;"></i>
                VIP<span style="font-weight: 300;">Booking</span>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list text-white fs-1"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>" href="services.php">Services</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['performers.php', 'fan_cards.php']) ? 'active' : ''; ?>" href="#" id="talentDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Talent
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg" aria-labelledby="talentDropdown" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px);">
                        <li><a class="dropdown-item py-2" href="performers.php" style="font-size: 0.85rem; letter-spacing: 0.5px;"><i class="bi bi-people me-2 text-gold"></i>Performers</a></li>
                        <li><a class="dropdown-item py-2" href="fan_cards.php" style="font-size: 0.85rem; letter-spacing: 0.5px;"><i class="bi bi-card-image me-2 text-gold"></i>Fan Cards</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'donation.php' ? 'active' : ''; ?>" href="donation.php">Donation</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['tickets.php', 'my_tickets.php', 'book_ticket.php', 'view_ticket.php', 'privacy.php', 'terms.php']) ? 'active' : ''; ?>" href="#" id="ticketsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Tickets
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg" aria-labelledby="ticketsDropdown" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px);">
                        <li><a class="dropdown-item py-2" href="tickets.php" style="font-size: 0.85rem; letter-spacing: 0.5px;"><i class="bi bi-ticket-perforated me-2 text-gold"></i>Book Event Tickets</a></li>
                        <li><a class="dropdown-item py-2" href="my_tickets.php" style="font-size: 0.85rem; letter-spacing: 0.5px;"><i class="bi bi-person-badge me-2 text-gold"></i>My Booked Tickets</a></li>
                        <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                        <li><a class="dropdown-item py-2" href="privacy.php" style="font-size: 0.85rem; letter-spacing: 0.5px;"><i class="bi bi-shield-lock me-2 text-gold"></i>Privacy Policy</a></li>
                        <li><a class="dropdown-item py-2" href="terms.php" style="font-size: 0.85rem; letter-spacing: 0.5px;"><i class="bi bi-file-earmark-text me-2 text-gold"></i>Terms of Service</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
            </ul>
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-3 mt-4 mt-lg-0 mb-3 mb-lg-0">
                <?php if (($site_settings['enable_translator'] ?? '0') === '1'): ?>
                    <?php include "gtranslate.php"; ?>
                <?php endif; ?>
                <a href="book.php" class="btn btn-gold btn-sm py-2 px-4" style="border-radius: 4px; text-decoration: none;">BOOK NOW</a>
            </div>
        </div>
    </div>
</nav>
