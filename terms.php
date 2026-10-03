<?php
require_once 'config.php';
require_once 'get_setting.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Terms of Service | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></title>
    <?php include "head.php"; ?>
    <style>
        .terms-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .terms-hero::before {
            display: none !important;
        }
        .terms-section {
            background-color: #faf8f5;
            padding: 5rem 0;
        }
        .terms-nav-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.03);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            position: sticky;
            top: 100px;
        }
        .terms-nav-link {
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
        .terms-nav-link:hover, .terms-nav-link.active {
            color: var(--primary);
            background-color: rgba(176, 0, 0, 0.05);
            font-weight: 600;
        }
        .terms-nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            color: var(--secondary);
        }
        .terms-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.03);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.02);
            margin-bottom: 2rem;
        }
        .terms-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #1e3c72;
            margin-bottom: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .terms-card h2 i {
            color: var(--secondary);
            margin-right: 15px;
            font-size: 1.8rem;
        }
        .terms-card p {
            color: #555555;
            font-size: 1rem;
            line-height: 1.8;
        }
        .terms-card ul {
            padding-left: 20px;
            color: #555555;
        }
        .terms-card li {
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

    <!-- Terms Hero Section -->
    <section class="terms-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Legal Agreement</p>
            <h1>Terms of Service</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Understand the contractual standards, guidelines, and responsibilities governing celebrity procurement.
            </p>
        </div>
    </section>

    <!-- Terms Content Section -->
    <section class="terms-section">
        <div class="container">
            <div class="row g-5">
                
                <!-- Navigation Sidebar -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="terms-nav-card">
                        <h5 class="fw-bold mb-4" style="color: #1e3c72; font-family: 'Playfair Display', serif; border-bottom: 1px solid #eee; padding-bottom: 10px;">Quick Navigation</h5>
                        <a href="#intro" class="terms-nav-link"><i class="bi bi-shield-check"></i> 1. Scope of Service</a>
                        <a href="#booking" class="terms-nav-link"><i class="bi bi-calendar-event"></i> 2. Booking Requests</a>
                        <a href="#escrow" class="terms-nav-link"><i class="bi bi-wallet2"></i> 3. Escrow &amp; Payments</a>
                        <a href="#riders" class="terms-nav-link"><i class="bi bi-airplane"></i> 4. Riders &amp; Logistics</a>
                        <a href="#cancel" class="terms-nav-link"><i class="bi bi-x-circle"></i> 5. Cancellation Policy</a>
                        <a href="#media" class="terms-nav-link"><i class="bi bi-camera-video"></i> 6. Media &amp; NDAs</a>
                        <a href="#law" class="terms-nav-link"><i class="bi bi-file-earmark-gavel"></i> 7. Governing Law</a>
                    </div>
                </div>

                <!-- Terms Content -->
                <div class="col-lg-9 col-md-12">
                    
                    <div id="intro" class="terms-card">
                        <h2><i class="bi bi-shield-check"></i> 1. Scope of Service</h2>
                        <p>
                            These Terms of Service ("Agreement") govern the relationship between <strong><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></strong> and you ("Client" or "User") regarding the procurement, brokerage, and staging of celebrities, public speakers, musicians, and performers (collectively, "Talent").
                        </p>
                        <p>
                            We act as a specialized talent brokerage and event concierge platform, coordinating directly with Talent management, representatives, and authorized legal agents to secure performance contracts for Client events.
                        </p>
                    </div>

                    <div id="booking" class="terms-card">
                        <h2><i class="bi bi-calendar-event"></i> 2. Booking Requests &amp; Quotes</h2>
                        <p>
                            All bookings initiated through this website are preliminary requests and do not constitute a binding contract:
                        </p>
                        <ul>
                            <li><strong>Evaluation</strong>: Booking quotes and availability figures are subject to confirmation, Talent schedule flexibility, and final contract terms.</li>
                            <li><strong>Roster Accuracy</strong>: While we maintain direct paths to listed Talent, listed prices are estimated base starting rates. Final pricing depends on location, date, event nature, and specific rider details.</li>
                            <li><strong>Approval</strong>: Talent reserves the right to decline any booking request or event format without explanation.</li>
                        </ul>
                    </div>

                    <div id="escrow" class="terms-card">
                        <h2><i class="bi bi-wallet2"></i> 3. Escrow &amp; Financial Terms</h2>
                        <p>
                            To protect both Client investments and Talent commitments, all booking clearances must follow strict financial guidelines:
                        </p>
                        <ul>
                            <li><strong>Escrow Deposit</strong>: Upon initial booking approval, a contract deposit (typically 50% of the total booking fee) must be wired to our secure escrow trust account.</li>
                            <li><strong>Balance Payment</strong>: The remaining balance is due no later than 30 business days prior to the scheduled event date, unless otherwise specified in individual Talent riders.</li>
                            <li><strong>Compliance</strong>: Payments must clear through designated banks before Talent is deployed or travel/logistics are formally booked.</li>
                        </ul>
                    </div>

                    <div id="riders" class="terms-card">
                        <h2><i class="bi bi-airplane"></i> 4. Performance Riders &amp; Logistics</h2>
                        <p>
                            The Client is responsible for fulfilling all contractual performance and hospitality requirements ("Riders") specified by the Talent:
                        </p>
                        <ul>
                            <li><strong>Travel &amp; Stay</strong>: Clients must provide first-class/charter flight travel, luxury hotel suites, and premium dressing rooms as requested in the Talent rider agreements.</li>
                            <li><strong>On-Site Security</strong>: Clients must fund and coordinate professional security details to ensure the absolute physical safety of the Talent from arrival to departure.</li>
                            <li><strong>Technical Riders</strong>: Sound, lighting, staging, and backstage logistics must meet the exact technical configurations requested by Talent audio/visual coordinators.</li>
                        </ul>
                    </div>

                    <div id="cancel" class="terms-card">
                        <h2><i class="bi bi-x-circle"></i> 5. Cancellation &amp; Force Majeure</h2>
                        <p>
                            Cancellation procedures are governed strictly by the following parameters:
                        </p>
                        <ul>
                            <li><strong>Client Cancellations</strong>: Deposit terms are non-refundable unless specified otherwise in the final contract. If Client cancels after the final balance due date, the full booking fee is forfeited.</li>
                            <li><strong>Talent Non-Appearance</strong>: In the event of documented medical emergency or travel failure preventing Talent from appearing, Client is issued a 100% refund of all escrowed fees, or a rescheduled date.</li>
                            <li><strong>Force Majeure</strong>: Neither party is liable for failure to perform due to war, natural disaster, government travel ban, or other extreme events beyond logical control.</li>
                        </ul>
                    </div>

                    <div id="media" class="terms-card">
                        <h2><i class="bi bi-camera-video"></i> 6. Media, Recording &amp; NDAs</h2>
                        <p>
                            Protecting the branding and IP rights of our Talent is of critical importance:
                        </p>
                        <p>
                            No professional audio or video recording of the performance, meet-and-greet, or event is permitted without the express written consent of the Talent's management. Standardized Non-Disclosure Agreements (NDAs) govern all booking negotiations, contract values, and routing details. Any breach of confidentiality grants the Talent the right to terminate the contract immediately without refund.
                        </p>
                    </div>

                    <div id="law" class="terms-card">
                        <h2><i class="bi bi-file-earmark-gavel"></i> 7. Governing Law &amp; Arbitration</h2>
                        <p>
                            This Agreement and all contracts generated through our brokerage are governed by local and federal entertainment law guidelines. Any dispute arising out of or in connection with this booking agreement shall be resolved through binding, confidential arbitration under standard legal frameworks.
                        </p>
                        <p class="mt-4">
                            By proceeding with any booking requests, deposit transfers, or contract signatures, you confirm that you have read, understood, and agreed to be bound by these Terms of Service.
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
            const navLinks = document.querySelectorAll(".terms-nav-link");
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
