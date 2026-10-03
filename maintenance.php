<?php
require_once 'config.php';
require_once 'get_setting.php';

// If maintenance mode is OFF, redirect back to home
if (($site_settings['maintenance_mode'] ?? '0') !== '1') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI'); ?></title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
    <link href="assets/css/google-fonts.css" rel="stylesheet">
    <?php if ($use_favicon): ?>
    <link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $favicon_path; ?>">
    <?php else: ?>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <?php endif; ?>
    <style>
        :root {
            --primary-crimson: #B00000;
            --dark-crimson: #8A0000;
            --accent-gold: #FFC107;
            --surface-dark: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--surface-dark);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background Gradients */
        .bg-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(176, 0, 0, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
            filter: blur(80px);
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(-30%, -30%); }
            to { transform: translate(30%, 30%); }
        }

        .maintenance-container {
            position: relative;
            z-index: 10;
            max-width: 600px;
            width: 90%;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            text-align: center;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-crimson), var(--dark-crimson));
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 40px rgba(176, 0, 0, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 20px 40px rgba(176, 0, 0, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 25px 50px rgba(176, 0, 0, 0.5); }
            100% { transform: scale(1); box-shadow: 0 20px 40px rgba(176, 0, 0, 0.3); }
        }

        .icon-wrapper i {
            font-size: 3rem;
            color: white;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #fff, var(--accent-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 1.1rem;
            color: #94a3b8;
            line-height: 1.8;
            margin-bottom: 2.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.25rem;
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.2);
            color: var(--accent-gold);
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2rem;
        }

        .status-badge i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .contact-area {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .contact-link {
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .contact-link:hover {
            color: var(--accent-gold);
        }

        .contact-link i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .logo-small {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0.5;
        }

        .logo-small img {
            max-height: 40px;
        }

    </style>
</head>
<body>

    <div class="bg-glow"></div>

    <div class="logo-small">
        <?php if ($logo_path): ?>
            <img src="<?php echo $logo_path; ?>" alt="Logo">
        <?php else: ?>
            <span class="fw-bold fs-4"><?php echo htmlspecialchars($site_settings['site_title'] ?? 'RELI'); ?></span>
        <?php endif; ?>
    </div>

    <div class="maintenance-container">
        <div class="status-badge">
            <i class="bi bi-gear-fill"></i> System Update in Progress
        </div>
        
        <div class="icon-wrapper">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h1>Under Maintenance</h1>
        <p>We are currently upgrading our exclusive booking platform to serve you better. Our website will be back online shortly with improved features and a smoother VIP experience.</p>

        <div class="contact-area">
            <a href="mailto:<?php echo $contact_email; ?>" class="contact-link">
                <i class="bi bi-envelope-fill"></i> Email Us
            </a>
            <a href="tel:<?php echo htmlspecialchars($site_settings['whatsapp_number'] ?? ''); ?>" class="contact-link">
                <i class="bi bi-telephone-fill"></i> Support
            </a>
        </div>
    </div>

</body>
</html>
