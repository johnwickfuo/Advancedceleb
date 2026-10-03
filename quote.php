<?php
    // We assume 'get_setting.php' still defines site configuration variables
    require_once 'get_setting.php'; 
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?> | Get an Instant Quote</title> 
        
        <?php if (isset($use_favicon) && $use_favicon): ?>
        <link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $favicon_path; ?>">
        <?php else: ?>
        <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
        <?php endif; ?>
        
        <link href="assets/css/style.css" rel="stylesheet">
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
        <link rel="stylesheet" href="assets/css/font-awesome-pro.css">
        <link rel="stylesheet" href="assets/css/gtranslate.css">
        <script src="assets/js/sweetalert2.all.min.js"></script>
        
        <style>
            .text-crimson { color: #6d0000 !important; }
            /* Add any specific styles for the quote form here */
            .quote-form-section {
                background-color:#fff; /* Light background for the form area */
                border-radius: 10px;
            }
        </style>
    </head>
    <body>
        
        <?php include "nav.php" ?>
        
        <header class="hero-gradient-bg text-white text-center shadow-lg">
            <div class="container">
                <h1 class="fw-bolder mb-2" style="font-family: 'Playfair Display', serif;">INSTANT BOOKING QUOTE</h1>
                <p class="lead mb-0 fs-5 opacity-90">Calculate your estimated booking cost and availability in seconds.</p>
            </div>
        </header>

        <section id="quote-calculator" class="py-5" style="background-color: #F5FBFF;">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="quote-form-section p-4 p-md-5">
                            <h4 class="text-center fw-bold text-crimson mb-4">Event Details</h4>
                            
                            <form action="process_quote.php" method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">1. Select Service Type</label>
                                    <select class="form-select" name="service_type" required>
                                        <option value="">Choose Service...</option>
                                        <option value="express">International Priority Express</option>
                                        <option value="talent">Air & Sea Talent</option>
                                        <option value="domestic">Local Celebrity Booking</option>
                                        </select>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-bold">2. Origin Country</label>
                                        <input type="text" class="form-control" name="origin_country" placeholder="e.g., United States" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Destination Country</label>
                                        <input type="text" class="form-control" name="destination_country" placeholder="e.g., France" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">3. Estimated Weight (kg)</label>
                                    <input type="number" step="0.1" min="0.1" class="form-control" name="weight" placeholder="e.g., 5.5" required>
                                </div>
                                <div class="row mb-5">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-label fw-bold">Length (cm)</label>
                                        <input type="number" min="1" class="form-control" name="length" placeholder="10">
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-label fw-bold">Width (cm)</label>
                                        <input type="number" min="1" class="form-control" name="width" placeholder="10">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Height (cm)</label>
                                        <input type="number" min="1" class="form-control" name="height" placeholder="10">
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-lg fw-bold text-white mt-3 btn-gradient shadow-lg" style="">
                                        <i class="bi bi-calculator me-2"></i> CALCULATE QUOTE
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php include "footer.php" ?>
        <script src="assets/js/jquery-3.7.1.min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <?php include "custom_script.php" ?>
    </body>
</html>
