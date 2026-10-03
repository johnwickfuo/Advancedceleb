<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Page Not Found | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
</head>
<body>
    <?php include "header.php"; ?>

    <section class="d-flex align-items-center justify-content-center" style="min-height: 85vh; background: #fafafa; padding-top: 120px; padding-bottom: 60px;">
        <div class="container text-center">
            <i class="bi bi-star-fill" style="font-size: 5rem; color: #FFC107; margin-bottom: 10px; filter: drop-shadow(0 4px 6px rgba(255, 193, 7, 0.4));"></i>
            <h1 class="serif-font fw-bold" style="font-size: 8rem; color: #B00000; line-height: 1; text-shadow: 2px 4px 10px rgba(176, 0, 0, 0.1);">404</h1>
            <h2 class="mb-4" style="color: #111; font-weight: 800; text-transform: uppercase; letter-spacing: 2px;">Talent Not Found</h2>
            <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 600px;">
                The page you are looking for might have been removed, had its name changed, or is temporarily unavailable on our exclusive roster.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="index.php" class="btn btn-crimson py-3 px-5 fw-bold text-white rounded-pill shadow" style="letter-spacing: 1px; background: #B00000;">RETURN HOME</a>
                <a href="performers.php" class="btn btn-outline-crimson py-3 px-5 fw-bold rounded-pill" style="letter-spacing: 1px; color: #B00000; border: 2px solid #B00000;">VIEW ROSTER</a>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>
