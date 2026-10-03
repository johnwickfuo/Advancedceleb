<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>About Us | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .about-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .about-hero::before {
            display: none !important;
        }

        /* VIP Experiences Section Styles */
        .experiences-section {
            background-color: #ffffff;
            padding: 5rem 0;
            position: relative;
        }
        .experience-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.03);
            border-radius: 20px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .experience-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .experience-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
            border-color: rgba(218, 165, 32, 0.2);
        }
        .experience-card:hover::before {
            opacity: 1;
        }
        .experience-icon-box {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.08) 0%, rgba(176, 0, 0, 0.05) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            border: 1px solid rgba(218, 165, 32, 0.2);
            transition: all 0.4s ease;
        }
        .experience-card:hover .experience-icon-box {
            transform: scale(1.1);
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
        }
        .experience-icon-box i {
            font-size: 2.2rem;
            color: var(--secondary);
            transition: color 0.4s ease;
        }
        .experience-card:hover .experience-icon-box i {
            color: #ffffff;
        }
        .experience-card h3 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.6rem;
            color: #1e3c72;
            margin-bottom: 1.25rem;
        }
        .experience-card p {
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
    <section class="about-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Who We Are</p>
            <h1>About Our Agency</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                The Premier Destination for Exclusive Talent. We connect you with high-profile icons, artists, and speakers to elevate your events to extraordinary heights.
            </p>
        </div>
    </section>

    <section class="about-content section-padding bg-pattern">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <img src="assets/img/about_exclusive.jpg" alt="Exclusive Events" class="about-image">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="mb-4">Elevating Events to Extraordinary</h2>
                    <p class="text-muted mb-4 fs-5" style="line-height: 1.8;">
                        At VIP Celebrity Bookings, we specialize in connecting high-profile clients, corporations, and event organizers with the world's most sought-after talent. Our platform is built on trust, exclusivity, and seamless execution.
                    </p>
                    <p class="text-muted mb-5 fs-5" style="line-height: 1.8;">
                        With years of experience in the entertainment and booking industry, we have cultivated direct relationships with leading management agencies, ensuring transparent communication and guaranteed appearances for your most important occasions.
                    </p>
                    
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="stat-card">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Global Talent</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat-card">
                                <div class="stat-number">10k+</div>
                                <div class="stat-label">Successful Events</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- VIP Experiences Section -->
    <section class="experiences-section">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <p style="color: var(--secondary) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Prestige Access</p>
                <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; color: #1e3c72;">Signature VIP Experiences</h2>
                <div style="width: 80px; height: 3px; background-color: var(--secondary); margin: 1.5rem auto 0;"></div>
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-lg-4 col-md-6">
                    <div class="experience-card">
                        <div class="experience-icon-box">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3>Meet & Greets</h3>
                        <p>Intimate, high-end private meet-and-greet sessions allowing you and your guests to mingle, take professional photographs, and interact directly with A-list icons.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="experience-card">
                        <div class="experience-icon-box">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <h3>Private Appearances</h3>
                        <p>Elevate your private galas, luxury brand launches, corporate summits, or intimate dinners with an exclusive appearance and hosting by world-renowned stars.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="experience-card">
                        <div class="experience-icon-box">
                            <i class="bi bi-ticket-perforated-fill"></i>
                        </div>
                        <h3>Backstage Access</h3>
                        <p>Gain unprecedented behind-the-scenes credentials, exclusive soundcheck viewings, and green room access for the ultimate, unrestricted entertainment experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Premium VIP Standard Section -->
    <style>
        .vip-standard {
            background: linear-gradient(145deg, var(--primary-dark) 0%, #1a0000 100%);
            color: #fff;
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }
        .vip-standard::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--bs-crimson-light));
        }
        .vip-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--secondary);
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            height: 100%;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }
        .vip-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        }
        .vip-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(176,0,0,0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 1px solid rgba(218, 165, 32, 0.3);
        }
        .vip-icon-wrapper i {
            font-size: 2rem;
            background: -webkit-linear-gradient(45deg, var(--secondary), #FDB931);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .vip-title-main {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.75rem, 4vw, 2.8rem);
            font-weight: 700;
            margin-bottom: 3rem;
            text-align: center;
            color: #fff;
        }
        @media (max-width: 768px) {
            .vip-title-main {
                font-size: 1.75rem !important;
                margin-bottom: 2rem !important;
            }
            .experiences-section h2 {
                font-size: 1.75rem !important;
            }
            .vip-standard {
                padding: 3.5rem 0 !important;
            }
        }
        .vip-card h4 {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
        .vip-card p {
            color: #aaa;
            font-size: 0.95rem;
            line-height: 1.7;
        }
    </style>
    <section class="vip-standard">
        <div class="container">
            <h2 class="vip-title-main">The VIP Standard</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="vip-card">
                        <div class="vip-icon-wrapper">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h4>Absolute Discretion</h4>
                        <p>We uphold the highest standards of privacy and confidentiality for our high-net-worth clients, ensuring seamless and discreet event execution.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="vip-card">
                        <div class="vip-icon-wrapper">
                            <i class="bi bi-globe-americas"></i>
                        </div>
                        <h4>Global Procurement</h4>
                        <p>Our expansive global network allows us to source exclusive A-list talent, speakers, and performers from any corner of the world.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="vip-card">
                        <div class="vip-icon-wrapper">
                            <i class="bi bi-gem"></i>
                        </div>
                        <h4>Bespoke Execution</h4>
                        <p>Every booking includes our signature white-glove concierge service, meticulously tailoring every detail of the experience to your exact specifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
</body>
</html>