<?php
    // NOTE: All database and session logic has been removed,
    // but we retain the logic that includes site config variables
    require_once 'get_setting.php'; 
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?> | FAQ</title> 
        <meta name="description" content="Find instant answers to common questions about booking, contracts, fees, scheduling, and insurance for your celebrity events.">
        
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
            /* RETAINED STYLING */
            .text-crimson {
                color: #6d0000 !important; /* RETAINED: Brand color */
            }
            /* Custom styling for FAQ accordion structure */
            .accordion-button:not(.collapsed) {
                color: #6d0000; /* Text color when open */
                background-color: #fcebeb; /* Light background for open state */
                box-shadow: none;
                font-weight: 700;
            }
            .accordion-button:focus {
                border-color: #f7d1d1;
                box-shadow: 0 0 0 0.25rem rgba(109, 0, 0, 0.25);
            }
            .accordion-item {
                border-radius: 10px;
                margin-bottom: 15px;
                border: 1px solid #dee2e6;
            }
            .accordion-body {
                background-color: #fff;
                padding-top: 5px;
                font-size: 0.95rem;
                line-height: 1.6;
            }
        </style>
    </head>
    <body>
        
        <?php include "nav.php" ?>
        
        <header class="hero-gradient-bg text-white text-center shadow-lg">
            <div class="container">
			<div class="mx-auto text-center" style="max-width: 800px;">
                <h1 class="fw-bolder mb-2" style="font-family: 'Playfair Display', serif;">FREQUENTLY ASKED QUESTIONS</h1>
                <p class="lead mb-0 fs-5 opacity-90">
                    Find instant, detailed answers about booking, availability, and contract procedures.
                </p>
			</div>	
            </div>
        </header>

        <section id="faq-content" class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">

                        <div class="mb-5 text-center">
                            <h2 class="fw-bolder mb-4 text-dark">Need an Answer Fast?</h2>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-white border-end-0" style="border: 2px #e0e0e0 solid;"><i class="bi bi-search text-crimson"></i></span>
                                <input type="text" id="faq-search" class="form-control border-start-0" placeholder="Search for keywords like 'customs', 'insurance', or 'booking time'" style="    border-top-left-radius: 0px !important; border-bottom-left-radius: 0px !important;">
                            </div>
                        </div>

                        <h3 class="fw-bold text-crimson mb-3 mt-4"><i class="bi bi-geo-alt-fill me-2"></i> Tracking & Booking Status</h3>
                        <div class="accordion" id="accordionTracking">
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Where can I track my event?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionTracking">
                                    <div class="accordion-body">
                                        You can track your event instantly on our dedicated <a href="track.php" class="text-crimson fw-semibold">Tracking Page</a>. Simply enter your Tracking Number in the provided field. Please allow up to 4 hours after receiving your confirmation email for the tracking information to become live in our system.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Why hasn't my tracking status updated?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionTracking">
                                    <div class="accordion-body">
                                        Tracking updates typically occur when a parcel is scanned at major transit points (e.g., origin depot, customs entry, final destination hub). If the status has stalled for more than 24 hours for Express events or 3 business days for Talent, it may be:
                                        <ul>
                                            <li>Currently undergoing Customs Clearance.</li>
                                            <li>On a long-haul flight or sea route where intermediate scans are limited.</li>
                                        </ul>
                                        If the delay exceeds these periods, please contact our support team with your tracking number.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        What happens if I missed my booking attempt?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionTracking">
                                    <div class="accordion-body">
                                        Our agents typically attempt booking twice. After the first failed attempt, they will leave a notification card (or send an email/SMS) with instructions on how to reschedule a booking or where to pick up the package at a local depot. If both attempts fail, the package may be held for a short period before being returned to the sender. Please follow the instructions on your notification immediately.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="fw-bold text-crimson mb-3 mt-5"><i class="bi bi-currency-dollar me-2"></i> Pricing, Customs & Duties</h3>
                        <div class="accordion" id="accordionCustoms">
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        What is the difference between DDU and DDP?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionCustoms">
                                    <div class="accordion-body">
                                        These are Incoterms defining responsibility for duties and taxes:
                                        <ul>
                                            <li>DDU (Delivered Duty Unpaid): The recipient (consignee) is responsible for paying any import duties, taxes, and customs fees upon arrival in the destination country. This is the default for many consumer events.</li>
                                            <li>DDP (Delivered Duty Paid): The sender (shipper) pays all duties, taxes, and customs fees upfront at the time of booking. This ensures faster clearance and prevents unexpected charges for the recipient, often used for e-commerce.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        How are booking costs calculated?
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionCustoms">
                                    <div class="accordion-body">
                                        Costs are calculated based on the greater of two measures: Actual Weight (the physical weight of the package) or Volumetric Weight (or Dimensional Weight).
                                        <p class="mt-2 small fst-italic">
                                            Volumetric Weight is calculated using the formula: $(Length \times Width \times Height) / \text{Divisor}$. We use the highest cost to determine the final rate. You can use our <a href="quote.php" class="text-crimson fw-semibold">Quote Calculator</a> for a precise estimate.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSix">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                        My package is held by customs. What should I do?
                                    </button>
                                </h2>
                                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionCustoms">
                                    <div class="accordion-body">
                                        Customs holds are usually due to missing or incomplete documentation, or outstanding duties/taxes (DDU events).
                                        <p class="mt-2">
                                            Action Required: Check your tracking details for a specific notification. If you are the recipient, you may need to contact the local customs office or pay the outstanding fees. If you require assistance, our dedicated Customs Brokerage Team can help expedite the release. Contact us via <a href="contact.php" class="text-crimson fw-semibold">Contact Page</a> for expert support.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="fw-bold text-crimson mb-3 mt-5"><i class="bi bi-umbrella-fill me-2"></i> Insurance & Liability</h3>
                        <div class="accordion" id="accordionInsurance">
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingSeven">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                        Is my event automatically insured?
                                    </button>
                                </h2>
                                <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#accordionInsurance">
                                    <div class="accordion-body">
                                        All events include a basic level of carrier liability coverage, which varies based on the service and the convention that governs the transport (e.g., Warsaw or Montreal Convention for air talent).
                                        <p class="mt-2">
                                            Recommendation: For high-value goods, we highly recommend purchasing All-Risk Event Insurance during the booking process. This provides comprehensive coverage up to the full declared commercial value against loss or damage.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingEight">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                        How do I file a claim for loss or damage?
                                    </button>
                                </h2>
                                <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#accordionInsurance">
                                    <div class="accordion-body">
                                        Claims must be filed by the shipper within 7 calendar days of booking (for visible damage) or 30 days of the expected event date (for loss).
                                        <p class="mt-2">
                                            Required Documents: You will need the tracking number, proof of commercial value (invoice), and photographic evidence (for damage claims). Please contact our claims department at [claims@yourcompany.com] to initiate the process.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-12">
                                <div class="p-4 rounded-3 text-white shadow-lg d-flex align-items-start" style="background-color:#0c0c0c; border: 4px solid rgba(255, 255, 255, 0.5);">
                                    <i class="bi bi-headset fs-2 me-3 flex-shrink-0 text-warning"></i>
                                    <div>
                                        <h4 class="fw-bold mb-1 text-uppercase" style="letter-spacing: 1px;">Didn't Find Your Answer?</h4>
                                        <p class="lead mb-0 fs-6">
                                            If your question is complex or relates to a specific customs issue, our expert support team is ready to help you directly.
                                            <a href="contact.php" class="text-warning fw-semibold text-decoration-underline ms-2">Contact Customer Support Now</a>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <?php include "footer.php" ?>
        <script src="assets/js/jquery-3.7.1.min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <?php include "custom_script.php" ?>
        
        <script>
            $(document).ready(function() {
                $('#faq-search').on('keyup', function() {
                    var searchTerm = $(this).val().toLowerCase();
                    
                    $('.accordion-item').each(function() {
                        var questionText = $(this).find('.accordion-button').text().toLowerCase();
                        var answerText = $(this).find('.accordion-body').text().toLowerCase();

                        if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
            });
        </script>
    </body>
</html>