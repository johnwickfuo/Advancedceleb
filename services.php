<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Services | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .services-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .services-hero::before {
            display: none !important;
        }

        /* Booking Process Section Styles */
        .process-section {
            background-color: #faf8f5;
            padding: 5rem 0;
            position: relative;
        }
        .process-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.03);
            border-radius: 16px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
        }
        .process-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(218, 165, 32, 0.08);
            border-color: rgba(218, 165, 32, 0.3);
        }
        .process-step-num {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--secondary) 0%, #b8860b 100%);
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 800;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(218, 165, 32, 0.3);
        }
        .process-card h4 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.35rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }
        .process-card p {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Premium Hero Section -->
    <section class="services-hero">
        <div class="container">
            <p style="color: var(--secondary) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">What We Offer</p>
            <h1>Our Services</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Bespoke Talent Procurement Solutions. We handle the curation, booking, and logistics of world-class performers, speakers, and celebrities for exclusive events.
            </p>
        </div>
    </section>

    <section class="services-section">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <i class="bi bi-mic-fill service-icon"></i>
                        <h3 class="service-title">Corporate Speaking</h3>
                        <p class="service-desc">Inspire your team or audience with industry leaders, motivational speakers, and visionary thinkers tailored for your corporate retreats or conferences.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <i class="bi bi-music-note-beamed service-icon"></i>
                        <h3 class="service-title">Private Concerts</h3>
                        <p class="service-desc">Bring Grammy-winning artists and world-renowned bands to your private party, wedding, or exclusive gathering for an unforgettable musical experience.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <i class="bi bi-star service-icon"></i>
                        <h3 class="service-title">Brand Endorsements</h3>
                        <p class="service-desc">Connect your brand with A-list celebrities and influencers to elevate your marketing campaigns and build powerful, recognizable partnerships.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <i class="bi bi-camera-reels service-icon"></i>
                        <h3 class="service-title">Media Appearances</h3>
                        <p class="service-desc">Book renowned actors and television personalities for guest appearances, red carpet hosting, or exclusive promotional events.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <i class="bi bi-gem service-icon"></i>
                        <h3 class="service-title">VIP Meet & Greets</h3>
                        <p class="service-desc">Organize exclusive, intimate meet-and-greet sessions that offer fans or top-tier clients a once-in-a-lifetime opportunity to mingle with their idols.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <i class="bi bi-globe service-icon"></i>
                        <h3 class="service-title">International Tours</h3>
                        <p class="service-desc">We handle complex logistics and routing for talent participating in multi-city or international tour circuits seamlessly.</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5 pt-4">
                <a href="book.php" class="btn btn-gold px-4 px-md-5 py-2.5 py-md-3">REQUEST BOOKING</a>
            </div>
        </div>
    </section>

    <!-- Signature VIP Experiences Section -->
    <style>
        .signature-experiences {
            position: relative;
            padding: 7rem 0;
            background: url('assets/img/services_bg.jpg') center/cover fixed;
            color: #fff;
            text-align: center;
        }
        .signature-experiences::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(176,0,0,0.92) 0%, rgba(20,0,0,0.85) 100%);
            z-index: 1;
        }
        .exp-content {
            position: relative;
            z-index: 2;
        }
        .exp-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.75rem, 4vw, 3rem);
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffffff !important;
            text-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .exp-subtitle {
            font-family: 'Outfit', sans-serif;
            color: #FFC107;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4rem;
        }
        .exp-grid .col-md-4 {
            padding: 0 2rem;
            border-right: 1px solid rgba(255,193,7,0.2);
        }
        .exp-grid .col-md-4:last-child {
            border-right: none;
        }
        @media (max-width: 767px) {
            .exp-title {
                font-size: 1.75rem !important;
            }
            .exp-subtitle {
                margin-bottom: 2rem !important;
            }
            .process-section h2 {
                font-size: 1.75rem !important;
            }
            .exp-grid .col-md-4 {
                border-right: none;
                border-bottom: 1px solid rgba(255,193,7,0.2);
                padding-bottom: 2rem;
                margin-bottom: 2rem;
            }
            .exp-grid .col-md-4:last-child {
                border-bottom: none;
                padding-bottom: 0;
                margin-bottom: 0;
            }
        }
        .exp-icon {
            font-size: 2.5rem;
            color: #FFC107;
            margin-bottom: 1rem;
        }
        .exp-item h4 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #ffffff !important;
        }
        .exp-item p {
            color: rgba(255,255,255,0.8);
            font-size: 0.95rem;
            line-height: 1.6;
        }
    </style>
    <section class="signature-experiences">
        <div class="container exp-content">
            <h2 class="exp-title">Signature VIP Upgrades</h2>
            <p class="exp-subtitle">Elevate Your Booking Experience</p>
            
            <div class="row exp-grid mt-5">
                <div class="col-md-4">
                    <div class="exp-item">
                        <i class="bi bi-airplane-fill exp-icon"></i>
                        <h4>Private Jet Logistics</h4>
                        <p>We coordinate seamless, ultra-luxurious private jet transportation to ensure talent arrives refreshed and exactly on schedule.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="exp-item">
                        <i class="bi bi-camera-fill exp-icon"></i>
                        <h4>Red Carpet Coordination</h4>
                        <p>From press walls to media management, our team handles all red carpet logistics for a flawless, high-profile arrival.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="exp-item">
                        <i class="bi bi-shield-check exp-icon"></i>
                        <h4>Elite Security Detail</h4>
                        <p>We provide top-tier, discreet security personnel specialized in high-net-worth individual protection for absolute peace of mind.</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 pt-4">
                <a href="contact.php" class="btn btn-outline-gold px-4 px-md-5 py-2.5 py-md-3 rounded-pill fw-bold" style="background: rgba(255,193,7,0.1);">CONTACT CONCIERGE</a>
            </div>
        </div>
    </section>

    <!-- Booking Process Section -->
    <section class="process-section">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <p style="color: var(--secondary) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Our Workflow</p>
                <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; color: #1e3c72;">Prestige Booking Process</h2>
                <div style="width: 80px; height: 3px; background-color: var(--secondary); margin: 1.5rem auto 0;"></div>
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-lg-3 col-md-6">
                    <div class="process-card">
                        <div class="process-step-num">01</div>
                        <h4>Consultation</h4>
                        <p>Share your vision, budget, and event format with our dedicated talent specialists to identify the perfect match.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card">
                        <div class="process-step-num">02</div>
                        <h4>Talent Sourcing</h4>
                        <p>We approach target management directly, checking availability, negotiating fees, and securing initial expressions of interest.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card">
                        <div class="process-step-num">03</div>
                        <h4>Securing Contract</h4>
                        <p>We handle all corporate legal agreements, rider checks, luxury travel arrangements, and technical requirements flawlessly.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="process-card">
                        <div class="process-step-num">04</div>
                        <h4>Event Execution</h4>
                        <p>Our on-site logistics team supervises the appearance, coordinates security, and ensures a spectacular, error-free experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>