<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
</head>
<body>
    <?php include "header.php"; ?>

    <style>
        .contact-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .contact-hero::before {
            display: none !important;
        }
    </style>
    
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Get in Touch</p>
            <h1>Contact Us</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Experience world-class service. Whether you're looking to book a global icon or need assistance planning your exclusive event, our dedicated VIP concierge team is available 24/7.
            </p>
        </div>
    </section>

    <section class="contact-section py-5 my-5">
        <div class="container">
            <div class="row g-5 align-items-center">
                <!-- Contact Information Cards -->
                <div class="col-lg-5">
                    <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #1e3c72; margin-bottom: 20px;">We're Here for You</h2>
                    <p class="text-muted mb-5" style="font-size: 1.1rem; line-height: 1.8;">Experience world-class service. Whether you're looking to book a global icon or need assistance planning your exclusive event, our dedicated VIP concierge team is available 24/7.</p>
                    
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="contact-card-premium text-center">
                                <div class="contact-icon-wrapper">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <h4 class="mb-2" style="color: #1e3c72; font-family: 'Outfit', sans-serif; font-weight: 700;">Headquarters</h4>
                                <p class="text-muted mb-0 small"><?php echo nl2br(htmlspecialchars($site_settings['contact_address'] ?? "100 VIP Avenue\nBeverly Hills, CA 90210")); ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="contact-card-premium text-center">
                                <div class="contact-icon-wrapper">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <h4 class="mb-2" style="color: #1e3c72; font-family: 'Outfit', sans-serif; font-weight: 700;">Email Us</h4>
                                <p class="text-muted mb-0 small"><?php echo htmlspecialchars($site_settings['contact_email'] ?? 'info@vipcelebrity.com'); ?></p>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="contact-card-premium text-center d-flex align-items-center text-start p-4">
                                <div class="contact-icon-wrapper mb-0 me-4 flex-shrink-0" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1" style="color: #1e3c72; font-family: 'Outfit', sans-serif; font-weight: 700;">Direct Line</h4>
                                    <p class="text-muted mb-0 fs-5 fw-bold" style="color: var(--secondary) !important;"><?php echo htmlspecialchars($site_settings['contact_phone'] ?? '+1 (310) 555-0199'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-lg-7">
                    <div class="contact-card-premium p-5" style="border-radius: 30px;">
                        <h3 style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #1e3c72; margin-bottom: 30px; font-size: 2rem;">Send a Message</h3>
                        <form method="post" action="process_contact.php" class="form-premium">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12 mt-5">
                                    <button type="submit" class="btn btn-gold w-100 py-3 fw-bold" style="font-size: 1.05rem; letter-spacing: 1px; border-radius: 50px;"><i class="bi bi-send-fill me-2"></i>SEND MESSAGE</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Map Section -->
            <div class="row mt-5 pt-4">
                <div class="col-12">
                    <div class="map-container">
                        <!-- Dynamic Map Location -->
                        <?php 
                            // Get the configured address or fallback to default
                            $map_address = $site_settings['contact_address'] ?? '100 VIP Avenue, Beverly Hills, CA 90210';
                            // Encode the address for the URL
                            $encoded_address = urlencode(str_replace("\n", ", ", $map_address));
                        ?>
                        <iframe src="https://maps.google.com/maps?q=<?php echo $encoded_address; ?>&t=&z=14&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating CTA Banner -->
    <style>
        .confidence-cta {
            background: linear-gradient(135deg, #2b0000 0%, #0d0000 100%);
            border: 2px solid rgba(255,193,7,0.4);
            border-radius: 12px;
            padding: 2.5rem 3rem;
            margin: 2rem auto 5rem;
            max-width: 950px;
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            box-shadow: 0 20px 40px rgba(176,0,0,0.15);
        }
        .confidence-cta .cta-icon {
            color: #FFC107;
            font-size: 3rem;
            line-height: 1;
            margin-top: 0.2rem;
        }
        .confidence-cta .cta-content h3 {
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            font-size: 1.9rem;
            text-transform: uppercase;
            margin-bottom: 0.8rem;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .confidence-cta .cta-content p {
            color: #d4d4d4;
            font-size: 1.1rem;
            margin-bottom: 0.4rem;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
        }
        .confidence-cta .cta-content a {
            color: #FFC107;
            text-decoration: underline;
            text-decoration-color: #FFC107;
            text-decoration-thickness: 2px;
            text-underline-offset: 4px;
            font-weight: 600;
        }
        .confidence-cta .cta-content a:hover {
            color: #fff;
            text-decoration-color: #fff;
        }
        @media (max-width: 768px) {
            .confidence-cta {
                flex-direction: column;
                padding: 1.75rem 1.25rem;
                text-align: left;
                align-items: flex-start;
                margin: 2rem auto 4rem;
                width: 100%;
            }
        }
    </style>
    <div class="container">
        <div class="confidence-cta">
            <div class="cta-icon">
                <i class="bi bi-star-fill"></i>
            </div>
            <div class="cta-content">
                <h3>READY TO START BOOKING WITH<br>CONFIDENCE?</h3>
                <p>Connect your event to the world with a partner built on trust and exclusivity.</p>
                <p>Visit our <a href="performers.php">Book Celebrity</a> or request a consultation to begin.</p>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>