<?php
require_once 'config.php';
require_once 'get_setting.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function generateDonationRef() {
    return 'DON-' . strtoupper(substr(uniqid(), -6));
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation'])) {
    $amount = (float)$_POST['donation_amount'];
    $custom_amount = (float)($_POST['custom_amount'] ?? 0);
    
    if ($amount === 0.0 && $custom_amount > 0) {
        $amount = $custom_amount;
    }
    
    $cause = isset($_POST['charity_cause']) && !empty($_POST['charity_cause']) ? trim($_POST['charity_cause']) : 'General Charity Fund';
    $user_name = trim($_POST['donor_name']);
    $user_email = trim($_POST['donor_email']);
    
    if ($amount > 0 && !empty($user_name) && !empty($user_email)) {
        $booking_ref = generateDonationRef();
        $celebrity_id = 0; // 0 indicates a charitable donation
        $event_date = date('Y-m-d');
        $event_details = "Charitable Donation: " . $cause;
        $user_phone = 'N/A'; // Optional for donations
        
        try {
            $sql = "INSERT INTO bookings (booking_reference, celebrity_id, user_name, user_email, user_phone, event_date, event_details, amount, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$booking_ref, $celebrity_id, $user_name, $user_email, $user_phone, $event_date, $event_details, $amount]);
            
            // Send Email Notifications (isolated in try-catch so SMTP issues never prevent donation completion)
            require_once 'include/email_core_functions.php';
            require_once 'get_setting.php';
            $smtp_config_missing = false;
            $admin_email = $site_settings['contact_email'] ?? "info@vipcelebrity.com";

            // 1. Email to Donor
            try {
                $donor_subject = "Donation Pledged - Ref: " . $booking_ref;
                $donor_body = "
                    <h2 style='color: #e63946; margin-top: 0;'>Thank You for Your Generosity</h2>
                    <p>Dear {$user_name},</p>
                    <p>Your pledge to support <strong>{$cause}</strong> has been recorded and is pending completion.</p>
                    <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px; margin-bottom: 20px;'>
                        <tr><td width='150'><strong>Reference Code:</strong></td><td>{$booking_ref}</td></tr>
                        <tr><td><strong>Cause:</strong></td><td>{$cause}</td></tr>
                        <tr><td><strong>Amount:</strong></td><td>" . format_currency($amount) . "</td></tr>
                        <tr><td><strong>Date:</strong></td><td>" . date('M d, Y') . "</td></tr>
                    </table>
                    <p>Please complete your contribution using the payment instructions provided on the portal.</p>
                ";
                send_email_notification($site_settings, $user_email, $donor_subject, $donor_body, $smtp_config_missing);
            } catch (\Throwable $e) {
                error_log("Failed to send donation confirmation email to donor: " . $e->getMessage());
            }

            // 2. Email to Admin
            try {
                $admin_subject = "[NEW DONATION] " . $booking_ref . " - " . $user_name;
                $admin_body = "
                    <h2 style='color: #e63946; margin-top: 0;'>New Charitable Donation</h2>
                    <p>A new charitable donation pledge has been submitted.</p>
                    <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px;'>
                        <tr><td width='150'><strong>Reference Code:</strong></td><td>{$booking_ref}</td></tr>
                        <tr><td><strong>Donor Name:</strong></td><td>{$user_name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>{$user_email}</td></tr>
                        <tr><td><strong>Cause:</strong></td><td>{$cause}</td></tr>
                        <tr><td><strong>Amount:</strong></td><td>" . format_currency($amount) . "</td></tr>
                    </table>
                    <p><a href='{$site_settings['site_url']}/admin/bookings_manager.php' style='display: inline-block; padding: 10px 20px; background-color: #e63946; color: #fff; border-radius: 6px; font-weight: bold;'>View in Admin Dashboard</a></p>
                ";
                send_email_notification($site_settings, $admin_email, $admin_subject, $admin_body, $smtp_config_missing);
            } catch (\Throwable $e) {
                error_log("Failed to send donation notification email to admin: " . $e->getMessage());
            }

            // Check if any payment options are active
            $bank_enabled = !empty($site_settings['bank_transfer_enabled']) && $site_settings['bank_transfer_enabled'] == '1';
            $crypto_enabled = !empty($site_settings['crypto_enabled']) && $site_settings['crypto_enabled'] == '1';
            $giftcard_enabled = !empty($site_settings['giftcard_enabled']) && $site_settings['giftcard_enabled'] == '1';
            $has_payment_options = ($bank_enabled || $crypto_enabled || $giftcard_enabled);

            if ($has_payment_options) {
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Donation Request Created! 💖',
                    'text' => 'Thank you for your generous contribution towards ' . $cause . '. Please complete your donation via one of the payment options below.',
                    'html' => '<div style="text-align: center; padding: 10px 0;">' .
                              '<div style="width: 70px; height: 70px; background: rgba(218, 165, 32, 0.15); color: #daa520; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2.2rem;"><i class="bi bi-heart-fill"></i></div>' .
                              '<h4 style="font-family: \'Playfair Display\', serif; font-weight: 700; color: #1e3c72; margin-bottom: 12px;">Donation Pledged</h4>' .
                              '<p style="font-size: 1.05rem; color: #333; line-height: 1.6; margin-bottom: 12px;">Thank you <strong>' . htmlspecialchars($user_name) . '</strong> for contributing <strong>' . format_currency($amount) . '</strong> to <strong>' . htmlspecialchars($cause) . '</strong>.</p>' .
                              '<p style="font-size: 0.95rem; color: #555; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 15px;">Please select your preferred payment method below to complete your transfer.</p>' .
                              '<div style="font-size: 0.9rem; color: #6c757d;">Reference Code: <strong style="color: #b00000; font-size: 1rem;">' . htmlspecialchars($booking_ref) . '</strong></div>' .
                              '</div>'
                ];
                header("Location: payment.php?ref=" . urlencode($booking_ref));
                exit;
            } else {
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'title' => 'Donation Request Received!',
                    'text' => 'Thank you for your generous contribution. Your request has been received and is under review.',
                    'html' => '<div style="text-align: center; padding: 10px 0;">' .
                              '<div style="width: 70px; height: 70px; background: rgba(218, 165, 32, 0.15); color: #daa520; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2.2rem;"><i class="bi bi-heart-fill"></i></div>' .
                              '<h4 style="font-family: \'Playfair Display\', serif; font-weight: 700; color: #1e3c72; margin-bottom: 12px;">Donation Request Received</h4>' .
                              '<p style="font-size: 1.05rem; color: #333; line-height: 1.6; margin-bottom: 12px;">Thank you <strong>' . htmlspecialchars($user_name) . '</strong> for your contribution towards <strong>' . htmlspecialchars($cause) . '</strong>.</p>' .
                              '<p style="font-size: 0.95rem; color: #555; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 15px;">Our VIP concierge team will review your request and contact you shortly.</p>' .
                              '<div style="font-size: 0.9rem; color: #6c757d;">Reference Code: <strong style="color: #b00000; font-size: 1rem;">' . htmlspecialchars($booking_ref) . '</strong></div>' .
                              '</div>'
                ];
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_msg = "Error processing donation: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please provide your name, email, and a valid donation amount.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Make a Difference | <?php echo htmlspecialchars($site_settings['site_title'] ?? 'Celebrity Booking Platform'); ?></title>
    <?php include "head.php"; ?>
    <style>
        /* Donation Page Specific Styles */
        .donation-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/charity_hero.jpg') center/cover no-repeat;
        }
        .urgent-support-section {
            background-color: #f5f2f5;
            padding: 5rem 0;
        }
        .urgent-img-wrapper {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .urgent-img-wrapper img {
            width: 100%;
            height: auto;
            transition: transform 0.5s ease;
        }
        .urgent-img-wrapper:hover img {
            transform: scale(1.03);
        }
        .charity-card {
            background: #fff;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .charity-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border-color: var(--secondary);
        }
        .charity-logo-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--secondary);
            padding: 10px;
        }
        .charity-logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .impact-section {
            background: linear-gradient(145deg, var(--primary-dark) 0%, #1a0000 100%);
            color: #fff;
            padding: 6rem 0;
            position: relative;
        }
        .impact-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--bs-crimson-light));
        }
        .impact-icon {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        .donation-form-wrapper {
            background: #fff;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border-top: 4px solid var(--secondary);
            max-width: 800px;
            margin: 0 auto;
        }
        .amount-btn {
            border: 2px solid #e0e0e0;
            background: #fff;
            color: #333;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
        }
        .amount-btn:hover, .amount-btn.active {
            border-color: var(--secondary);
            background: rgba(218, 165, 32, 0.05);
            color: var(--secondary);
        }
        .custom-amount-wrapper {
            display: none;
            margin-top: 1rem;
        }
        .custom-amount-wrapper.active {
            display: block;
        }
        
        .donation-form-wrapper input, .donation-form-wrapper select {
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 100%;
            margin-bottom: 1rem;
        }
        .donation-form-wrapper input:focus, .donation-form-wrapper select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.25rem rgba(218, 165, 32, 0.25);
            outline: none;
        }
        .donation-hero {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('assets/img/booking_hero_bg.jpg') center/cover no-repeat !important;
            position: relative;
        }
        .donation-hero::before {
            display: none !important;
        }
        @media (max-width: 768px) {
            .donation-form-wrapper {
                padding: 1.75rem 1.25rem;
                border-radius: 12px;
            }
            .urgent-support-section {
                padding: 3rem 0;
            }
            .impact-section {
                padding: 3.5rem 0;
            }
            .impact-section h2 {
                font-size: 1.75rem !important;
                margin-bottom: 2rem !important;
            }
            #donate-form h2 {
                font-size: 1.75rem !important;
            }
            .charity-card {
                padding: 1.5rem 1rem;
            }
            .amount-btn {
                padding: 0.75rem 0.25rem;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>

    <!-- Hero Section -->
    <section class="donation-hero">
        <div class="container">
            <p style="color: var(--secondary, #dfa92a) !important; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 0.5rem;">Charity & Giving</p>
            <h1>Make a Difference</h1>
            <p class="lead fw-light mx-auto" style="max-width: 700px; font-size: 1.2rem;">
                Support a Cause That Matters. We proudly partner with carefully selected charitable organizations dedicated to providing medical care, food, and education to children and families in need.
            </p>
        </div>
    </section>

    <!-- Urgent Support Section -->
    <section class="urgent-support-section" id="urgent-support">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h2 class="mb-4" style="font-family: 'Playfair Display', serif; font-weight: 700;">Give Hope. Give Today.</h2>
                    <p class="fs-5 text-muted mb-4">
                        Your donation can help provide urgent medical treatment to a child, support a child's education, or give a family access to basic necessities.
                    </p>
                    <p class="fw-bold text-dark fs-5 mb-5">Every contribution matters. No donation is too small.</p>
                    
                    <div class="p-4 bg-white rounded-4 shadow-sm border-start border-4 border-danger">
                        <h3 class="h4 fw-bold text-danger mb-3">Children Who Need Your Support</h3>
                        <p>
                            Some of the children supported by our partner charities are facing serious health conditions and urgently need funding for medical treatment.
                        </p>
                        <p class="mb-4">
                            Help give children access to the treatment and care they desperately need. Your donation could help cover medical expenses, medication, hospital care, transportation, or other essential treatment costs.
                        </p>
                        <a href="#donate-form" class="btn btn-gold btn-lg px-4 px-md-5 py-2 py-md-3 rounded-pill fw-bold shadow-sm" onclick="selectCause('Urgent Medical Support')">Donate Now</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="urgent-img-wrapper">
                        <img src="assets/img/urgent_medical.jpg" alt="Child requiring urgent treatment" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Charity Partners Section -->
    <section class="section-padding bg-white" id="charity-partners">
        <div class="container">
            <div class="section-title text-center mb-5">
                <p>Trusted Organizations</p>
                <h2>Our Charity Partners</h2>
                <p class="text-muted mx-auto mt-3" style="max-width: 600px;">
                    We work with charitable organizations committed to creating meaningful and lasting change. You can choose the charity or cause you would personally like to support.
                </p>
            </div>
            
            <div class="row g-4">
                <!-- Partner 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="charity-card">
                        <div class="charity-logo-wrapper">
                            <img src="assets/img/charity_partner_1.jpg" alt="Global Heart & Health Foundation">
                        </div>
                        <h4 class="fw-bold mb-3">Global Heart & Health</h4>
                        <p class="text-muted small mb-3">Mission: Medical care for vulnerable children.</p>
                        <p class="fw-bold" style="color: var(--secondary);">Cause: Medical Care</p>
                        <p class="small text-danger fw-bold mb-4">Needs: Medical Supplies</p>
                        <a href="#donate-form" class="btn btn-outline-gold w-100 rounded-pill" onclick="selectCause('Global Heart & Health - Medical Care')">Donate</a>
                    </div>
                </div>
                <!-- Partner 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="charity-card">
                        <div class="charity-logo-wrapper">
                            <img src="assets/img/charity_partner_2.jpg" alt="Knowledge & Growth Foundation">
                        </div>
                        <h4 class="fw-bold mb-3">Knowledge & Growth</h4>
                        <p class="text-muted small mb-3">Mission: Providing essential educational resources.</p>
                        <p class="fw-bold" style="color: var(--secondary);">Cause: Education</p>
                        <p class="small text-danger fw-bold mb-4">Needs: Scholarships</p>
                        <a href="#donate-form" class="btn btn-outline-gold w-100 rounded-pill" onclick="selectCause('Knowledge & Growth - Education')">Donate</a>
                    </div>
                </div>
                <!-- Partner 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="charity-card">
                        <div class="charity-logo-wrapper">
                            <img src="assets/img/charity_partner_3.jpg" alt="Harvest Hope Initiative">
                        </div>
                        <h4 class="fw-bold mb-3">Harvest Hope Initiative</h4>
                        <p class="text-muted small mb-3">Mission: Ensuring access to basic necessities.</p>
                        <p class="fw-bold" style="color: var(--secondary);">Cause: Food & Shelter</p>
                        <p class="small text-danger fw-bold mb-4">Needs: Food & Shelter</p>
                        <a href="#donate-form" class="btn btn-outline-gold w-100 rounded-pill" onclick="selectCause('Harvest Hope - Food & Shelter')">Donate</a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="#donate-form" class="btn btn-gold px-4 px-md-5 py-2.5 py-md-3 rounded-pill fw-bold shadow-sm">Donate to a Cause</a>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section class="impact-section text-center">
        <div class="container">
            <h2 class="mb-5" style="font-family: 'Playfair Display', serif; font-size: clamp(1.75rem, 4vw, 2.75rem); color: #fff;">Donation Makes an Impact</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-6">
                    <i class="bi bi-heart-pulse-fill impact-icon"></i>
                    <h4 class="fw-bold text-white mb-3">Medical Care</h4>
                    <p class="text-light" style="opacity: 0.8; font-size: 0.9rem;">Help children and families access urgent medical treatment, medication, and healthcare.</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <i class="bi bi-book-fill impact-icon"></i>
                    <h4 class="fw-bold text-white mb-3">Education</h4>
                    <p class="text-light" style="opacity: 0.8; font-size: 0.9rem;">Help provide children with educational opportunities and the resources they need to learn and grow.</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <i class="bi bi-house-heart-fill impact-icon"></i>
                    <h4 class="fw-bold text-white mb-3">Food & Basic Needs</h4>
                    <p class="text-light" style="opacity: 0.8; font-size: 0.9rem;">Help provide nourishing food, safe drinking water, proper clothing, safe shelter, and other vital everyday needs.</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <i class="bi bi-shield-fill-plus impact-icon"></i>
                    <h4 class="fw-bold text-white mb-3">Emergency Support</h4>
                    <p class="text-light" style="opacity: 0.8; font-size: 0.9rem;">Help families facing urgent and unexpected circumstances receive timely assistance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Donation Form Section -->
    <section class="section-padding bg-light" id="donate-form">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700;">Ready to Make a Difference?</h2>
                <p class="fs-5 text-muted mt-3">Your generosity can turn hope into opportunity.</p>
                <p class="text-muted">Choose a cause, select your donation amount, and continue securely to one of the available payment methods supported by the platform.</p>
            </div>
            
            <div class="donation-form-wrapper">
                <?php if ($error_msg): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
                <?php endif; ?>
                
                <form action="donation.php#donate-form" method="POST">
                    <h4 class="fw-bold mb-4 border-bottom pb-2">1. Select a Cause</h4>
                    <select name="charity_cause" id="charity_cause" required>
                        <option value="General Charity Fund">General Fund</option>
                        <option value="Urgent Medical Support">Urgent Medical</option>
                        <option value="Global Heart & Health - Medical Care">Global Health</option>
                        <option value="Knowledge & Growth - Education">Education</option>
                        <option value="Harvest Hope - Food & Shelter">Food & Shelter</option>
                    </select>

                    <h4 class="fw-bold mb-4 mt-5 border-bottom pb-2">2. Select Amount</h4>
                    <div class="row g-3 mb-3">
                        <div class="col-4 col-md-2">
                            <div class="amount-btn" data-amount="25"><?php echo format_currency(25, null, 0); ?></div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="amount-btn" data-amount="50"><?php echo format_currency(50, null, 0); ?></div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="amount-btn active" data-amount="100"><?php echo format_currency(100, null, 0); ?></div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="amount-btn" data-amount="250"><?php echo format_currency(250, null, 0); ?></div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="amount-btn" data-amount="500"><?php echo format_currency(500, null, 0); ?></div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="amount-btn" data-amount="0">Custom</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="donation_amount" id="donation_amount" value="100">
                    
                    <div class="custom-amount-wrapper" id="custom-amount-wrapper">
                        <label class="form-label fw-bold">Enter Custom Amount (<?php echo htmlspecialchars($site_settings['currency_symbol'] ?? '$'); ?>)</label>
                        <input type="number" name="custom_amount" id="custom_amount" min="1" step="0.01" placeholder="e.g. 150.00">
                    </div>

                    <h4 class="fw-bold mb-4 mt-5 border-bottom pb-2">3. Your Details</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name *</label>
                            <input type="text" name="donor_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address *</label>
                            <input type="email" name="donor_email" required>
                        </div>
                    </div>
                    
                    <div class="text-center mt-5">
                        <button type="submit" name="submit_donation" class="btn btn-gold btn-lg px-4 px-md-5 py-3 rounded-pill fw-bold shadow-lg w-100">
                            <i class="bi bi-lock-fill me-2"></i> Donate Now
                        </button>
                        <p class="text-muted small mt-3"><i class="bi bi-shield-check text-success"></i> Secure, encrypted payment processing.</p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include "footer.php"; ?>

    <script>
        // Handle Amount Selection
        const amountBtns = document.querySelectorAll('.amount-btn');
        const donationAmountInput = document.getElementById('donation_amount');
        const customAmountWrapper = document.getElementById('custom-amount-wrapper');
        const customAmountInput = document.getElementById('custom_amount');

        amountBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all
                amountBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked
                this.classList.add('active');
                
                const amount = this.getAttribute('data-amount');
                donationAmountInput.value = amount;
                
                if (amount === '0') {
                    customAmountWrapper.classList.add('active');
                    customAmountInput.setAttribute('required', 'required');
                } else {
                    customAmountWrapper.classList.remove('active');
                    customAmountInput.removeAttribute('required');
                    customAmountInput.value = '';
                }
            });
        });

        // Pre-select cause based on clicking Charity cards
        function selectCause(causeName) {
            const select = document.getElementById('charity_cause');
            for(let i=0; i < select.options.length; i++) {
                if(select.options[i].value === causeName) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }
    </script>
</body>
</html>
