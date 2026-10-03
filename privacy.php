<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Privacy Policy | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .privacy-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .privacy-hero::before {
            display: none !important;
        }
        .privacy-section {
            background-color: #faf8f5;
            padding: 5rem 0;
        }
        .policy-nav-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.03);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            position: sticky;
            top: 100px;
        }
        .policy-nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: #495057;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }
        .policy-nav-link:hover, .policy-nav-link.active {
            color: var(--primary);
            background-color: rgba(176, 0, 0, 0.05);
            font-weight: 600;
        }
        .policy-nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            color: var(--secondary);
        }
        .policy-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.03);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.02);
            margin-bottom: 2rem;
        }
        .policy-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #1e3c72;
            margin-bottom: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .policy-card h2 i {
            color: var(--secondary);
            margin-right: 15px;
            font-size: 1.8rem;
        }
        .policy-card p {
            color: #555555;
            font-size: 1rem;
            line-height: 1.8;
        }
        .policy-card ul {
            padding-left: 20px;
            color: #555555;
        }
        .policy-card li {
            margin-bottom: 0.8rem;
            line-height: 1.7;
        }
        .badge-premium {
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(176,0,0,0.1));
            border: 1px solid rgba(218, 165, 32, 0.3);
            color: var(--secondary);
            font-weight: 700;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    
    <?php include "header.php"; ?>

    <!-- Privacy Hero Section -->
    <section class="privacy-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Trust &amp; Transparency</p>
            <h1>Privacy Policy</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                How we protect, manage, and secure your personal and booking information at the highest standard.
            </p>
        </div>
    </section>

    <!-- Privacy Content Section -->
    <section class="privacy-section">
        <div class="container">
            <div class="row g-5">
                
                <!-- Navigation Sidebar -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="policy-nav-card">
                        <h5 class="fw-bold mb-4" style="color: #1e3c72; font-family: 'Playfair Display', serif; border-bottom: 1px solid #eee; padding-bottom: 10px;">Quick Navigation</h5>
                        <a href="#intro" class="policy-nav-link"><i class="bi bi-info-circle-fill"></i> Scope &amp; Consent</a>
                        <a href="#collect" class="policy-nav-link"><i class="bi bi-collection-fill"></i> Data We Collect</a>
                        <a href="#use" class="policy-nav-link"><i class="bi bi-gear-wide-connected"></i> How We Use Data</a>
                        <a href="#nda" class="policy-nav-link"><i class="bi bi-shield-lock-fill"></i> Confidentiality &amp; NDAs</a>
                        <a href="#security" class="policy-nav-link"><i class="bi bi-safe-fill"></i> Security &amp; Escrow</a>
                        <a href="#rights" class="policy-nav-link"><i class="bi bi-person-check-fill"></i> Client Rights</a>
                        <a href="#contact" class="policy-nav-link"><i class="bi bi-envelope-paper-fill"></i> Contact Counsel</a>
                    </div>
                </div>

                <!-- Policy Content -->
                <div class="col-lg-9 col-md-12">
                    
                    <div id="intro" class="policy-card">
                        <h2><i class="bi bi-info-circle-fill"></i> 1. Scope &amp; Consent</h2>
                        <p>
                            Welcome to <strong><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></strong>. We operate at the intersection of high-end entertainment management and luxury events. We understand that discretion, confidentiality, and data protection are paramount.
                        </p>
                        <p>
                            This Privacy Policy details how we collect, store, share, and protect your information when you engage our booking platform, request concierge services, or execute talent agreements. By using our website and services, you consent to the practices outlined in this policy.
                        </p>
                    </div>

                    <div id="collect" class="policy-card">
                        <h2><i class="bi bi-collection-fill"></i> 2. Information We Collect</h2>
                        <p>
                            To coordinate A-list appearances and custom bookings, we collect the necessary detailed information from clients, corporate representatives, and talent agents:
                        </p>
                        <ul>
                            <li><strong>Client Details</strong>: Full name, official corporate entity, professional email address, phone number, and physical billing address.</li>
                            <li><strong>Booking Specifications</strong>: Event date, venue location, target budget, event format, guest count, and specific performance/rider requirements.</li>
                            <li><strong>Financial Transactions</strong>: Secure bank wire information, credit verification, or escrow account details necessary for transaction clearance (payment details are handled under strict SSL/TLS encryption).</li>
                            <li><strong>Verification Data</strong>: Official identification documents (e.g. passports) when coordinating international travel, VIP clearances, and custom logistics.</li>
                        </ul>
                    </div>

                    <div id="use" class="policy-card">
                        <h2><i class="bi bi-gear-wide-connected"></i> 3. How We Use Your Data</h2>
                        <p>
                            We use your data strictly to facilitate talent procurement and secure bookings:
                        </p>
                        <ul>
                            <li>Negotiating booking contracts directly with celebrity management and legal representatives.</li>
                            <li>Coordinating event logistics, including private aviation, ground transport, red carpet layouts, and private security details.</li>
                            <li>Securing financial transaction compliance and processing escrow payments under strict legal guidelines.</li>
                            <li>Sending essential booking updates, confirmations, and security notifications.</li>
                        </ul>
                    </div>

                    <div id="nda" class="policy-card">
                        <h2><i class="bi bi-shield-lock-fill"></i> 4. Confidentiality &amp; NDAs</h2>
                        <p>
                            Due to the high-profile nature of our roster, client confidentiality is central to our mission:
                        </p>
                        <p>
                            All information regarding private bookings, negotiations, fee structures, and event formats is treated as strictly confidential. We execute standardized Non-Disclosure Agreements (NDAs) with talent and event coordinators prior to final contract signing. We will never sell, lease, or distribute your private event details to third-party marketing networks.
                        </p>
                    </div>

                    <div id="security" class="policy-card">
                        <h2><i class="bi bi-safe-fill"></i> 5. Data Security &amp; Escrow</h2>
                        <p>
                            Our digital environment employs state-of-the-art security mechanisms to prevent unauthorized access, alteration, or disclosure of data:
                        </p>
                        <ul>
                            <li><strong>SSL Encryption</strong>: All browser sessions, booking inquiries, and administrative logins are protected with secure SSL/TLS channels.</li>
                            <li><strong>Escrow Compliance</strong>: Financial transactions are governed by secure escrow protocols, shielding banking details and protecting client funds.</li>
                            <li><strong>Access Controls</strong>: Access to sensitive booking dossiers is restricted strictly to senior agents and handlers assigned to your event.</li>
                        </ul>
                    </div>

                    <div id="rights" class="policy-card">
                        <h2><i class="bi bi-person-check-fill"></i> 6. Client Rights &amp; Choices</h2>
                        <p>
                            You maintain full authority over your personal information:
                        </p>
                        <ul>
                            <li>You may request a copy of the specific data we hold regarding your corporate entity or past events.</li>
                            <li>You may request correction of any inaccurate or outdated information.</li>
                            <li>You may request complete erasure of your registration profile or history (subject to legal, auditing, or tax retention requirements).</li>
                        </ul>
                    </div>

                    <div id="contact" class="policy-card">
                        <h2><i class="bi bi-envelope-paper-fill"></i> 7. Contact Our Counsel</h2>
                        <p>
                            If you have questions regarding this Privacy Policy, wish to enforce your data rights, or need clarification on our NDA frameworks, you can contact our privacy desk directly:
                        </p>
                        <p class="mt-4">
                            <strong>Email</strong>: legal@vipcelebrity.com<br>
                            <strong>Direct Helpline</strong>: +1 (800) 555-PRIVACY<br>
                            <strong>Corporate Address</strong>: Legal Counsel Division, VIP Celebrity Bookings Inc.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>
    
    <!-- Simple JS to add active class to sidebar links on click/scroll -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const navLinks = document.querySelectorAll(".policy-nav-link");
            const currentHash = window.location.hash;
            
            if (currentHash) {
                navLinks.forEach(link => {
                    if (link.getAttribute("href") === currentHash) {
                        link.classList.add("active");
                    }
                });
            } else {
                if (navLinks.length > 0) navLinks[0].classList.add("active");
            }
            
            navLinks.forEach(link => {
                link.addEventListener("click", function() {
                    navLinks.forEach(l => l.classList.remove("active"));
                    this.classList.add("active");
                });
            });
        });
    </script>
</body>
</html>
