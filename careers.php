<?php
    // NOTE: All database and session logic has been removed,
    // but we retain the logic that includes site config variables
    require_once 'get_setting.php'; 

    $application_message = '';
    $application_status = ''; // 'success' or 'error'

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
        $to_email = $site_settings['contact_email'] ?? "info@vipcelebrity.com"; 
        $subject = "New Job Application: " . htmlspecialchars($_POST['job_title'] ?? 'General Application');
        
        $name = htmlspecialchars($_POST['fullName']);
        $applicant_email = htmlspecialchars($_POST['email']);
        $phone = htmlspecialchars($_POST['phone'] ?? 'N/A');
        $location = htmlspecialchars($_POST['location'] ?? 'N/A');
        $job_applied_for = htmlspecialchars($_POST['job_title'] ?? 'General Application');
        $applicant_message = htmlspecialchars($_POST['message'] ?? 'No cover letter provided.');

        // 1. Build Email Body
        $email_body_html = "
            <h2 style='color: #e63946; margin-top: 0;'>New Job Application</h2>
            <p>You have received a new job application.</p>
            <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px;'>
                <tr><td width='150'><strong>Job Applied For:</strong></td><td>{$job_applied_for}</td></tr>
                <tr><td><strong>Full Name:</strong></td><td>{$name}</td></tr>
                <tr><td><strong>Email:</strong></td><td>{$applicant_email}</td></tr>
                <tr><td><strong>Phone:</strong></td><td>{$phone}</td></tr>
                <tr><td><strong>Location:</strong></td><td>{$location}</td></tr>
                <tr><td colspan='2' style='border-top: 1px solid #eef1f3; padding-top: 15px;'><strong>Message/Cover Letter:</strong><br><br>" . nl2br($applicant_message) . "</td></tr>
            </table>
        ";

        // 2. Handle File Upload (Resume)
        $attachments = [];
        $file_uploaded = false;
        
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['resume']['tmp_name'];
            $filename = basename($_FILES['resume']['name']);
            $file_size = $_FILES['resume']['size'];
            
            // Check file size (e.g., limit to 5MB)
            if ($file_size > 5000000) {
                 $application_status = 'error';
                 $application_message = "File is too large. Maximum file size is 5MB.";
            } else {
                $attachments[] = [
                    'path' => $tmp_name,
                    'name' => $filename
                ];
                $file_uploaded = true;
            }
        } else if (!isset($_FILES['resume']) || $_FILES['resume']['error'] == UPLOAD_ERR_NO_FILE) {
             $application_status = 'error';
             $application_message = "Error: Please upload a resume file.";
        } else {
             $application_status = 'error';
             $application_message = "An error occurred during file upload. Error code: " . $_FILES['resume']['error'];
        }

        // 3. Send Mail (only if file upload was successful)
        if ($application_status !== 'error') {
            require_once 'include/email_core_functions.php';
            $smtp_config_missing = false;
            
            if (send_email_notification($site_settings, $to_email, $subject, $email_body_html, $smtp_config_missing, $attachments)) {
                $application_status = 'success';
                $application_message = "Thank you, " . $name . "! Your application has been successfully submitted.";
            } else {
                $application_status = 'error';
                $application_message = "Failed to send the application email. Please check your server mail configuration or contact us directly.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?> | Careers</title> 
        <meta name="description" content="Explore career opportunities in talent management, VIP concierge, celebrity booking, and event production with a leading celebrity agency.">
        
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
            .text-crimson {
                color: var(--bs-crimson-light) !important;
            }

            /* Custom Filter Tabs */
            .filter-container {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                flex-wrap: wrap;
                margin-bottom: 3rem;
            }
            .filter-btn {
                background: white;
                border: 2px solid #e2e8f0;
                color: var(--primary-500);
                padding: 0.6rem 1.4rem;
                border-radius: 99px;
                font-weight: 700;
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            }
            .filter-btn:hover {
                color: var(--bs-crimson-light);
                border-color: var(--bs-crimson-light);
                transform: translateY(-3px) scale(1.05);
            }
            .filter-btn.active {
                background: var(--bs-crimson-light);
                border-color: var(--bs-crimson-light);
                color: white;
                box-shadow: 0 8px 20px -6px rgba(176, 0, 0, 0.4);
            }

            /* Premium Job Card */
            .job-premium-card {
                background: white;
                border: 2px solid #f1f5f9;
                border-radius: 20px;
                box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                overflow: hidden;
                position: relative;
                border-top: 4px solid var(--bs-crimson-light);
            }
            .job-premium-card:hover {
                transform: translateY(-12px) scale(1.02);
                box-shadow: 0 30px 60px rgba(176, 0, 0, 0.15);
                border-color: var(--bs-gold);
            }
            
            .job-badge {
                font-size: 0.75rem;
                font-weight: 700;
                padding: 0.35rem 0.8rem;
                border-radius: 6px;
                display: inline-block;
                margin-right: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .badge-dept {
                background: #fdf2f2;
                color: var(--bs-crimson-light);
                border: 1px solid rgba(176, 0, 0, 0.2);
            }
            .badge-loc {
                background: #f8fafc;
                color: #64748b;
                border: 1px solid #e2e8f0;
            }
            .btn-gradient {
                background: linear-gradient(135deg, #b00000 0%, #800000 100%);
                border: none;
                border-radius: 10px;
                padding: 0.6rem 1.2rem;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            .btn-gradient:hover {
                background: linear-gradient(135deg, #800000 0%, #500000 100%);
                color: #fff;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(176, 0, 0, 0.3);
            }
            .hero-gradient-bg {
                background: linear-gradient(135deg, #1a0000 0%, #500000 100%);
                padding: 5rem 0 4rem;
            }
            .why-choose-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .why-choose-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
            }
            .drag-drop-zone {
                border: 2px dashed #cbd5e1;
                border-radius: 12px;
                padding: 2rem;
                text-align: center;
                background: #f8fafc;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .drag-drop-zone:hover {
                border-color: var(--bs-crimson-light);
                background: #fdf2f2;
            }
            .modal-content-premium {
                border-radius: 20px;
                overflow: hidden;
            }
        </style>
    </head>
    <body class="bg-light">
        
        <?php include "nav.php" ?>
        
        <header class="hero-gradient-bg text-white text-center shadow-lg">
            <div class="container">
                <div class="mx-auto text-center" style="max-width: 800px;">
                    <span class="badge bg-warning text-dark fw-bold mb-3 px-3 py-2 text-uppercase tracking-wider animate-pulse-glow" style="font-size: 0.75rem; border-radius: 8px;">We Are Hiring!</span>
                    <h1 class="fw-bolder mb-2 text-uppercase" style="font-family: 'Playfair Display', serif;">Careers: Shape Exclusive Experiences</h1>
                    <p class="lead mb-0 fs-5 text-white-50">
                        Join a world-class team committed to luxury, prestige, and seamless celebrity management. Your journey starts here.
                    </p>
                </div>
            </div>
        </header>
        
        <?php if ($application_message): ?>
        <div class="container mt-4">
            <div class="alert alert-<?php echo $application_status == 'success' ? 'success' : 'danger'; ?> text-center fade show" role="alert">
                <i class="bi bi-<?php echo $application_status == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
                <?php echo $application_message; ?>
            </div>
        </div>
        <?php endif; ?>

        <section id="our-values" class="py-5 bg-light">
            <div class="container py-4">
                <h2 class="text-center fw-bolder mb-2 text-dark">Why Build Your Career in VIP Entertainment?</h2>
                <p class="text-center text-muted mb-5">Innovate, connect, and deliver unforgettable moments with <?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Bookings'); ?></p>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card p-4 text-center why-choose-card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="mb-3 text-crimson">
                                <i class="bi bi-stars fs-1"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Global Star Access</h4>
                            <p class="text-muted mb-0">Work alongside world-renowned artists, iconic speakers, and high-profile clients on global stages.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-4 text-center why-choose-card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="mb-3 text-crimson">
                                <i class="bi bi-shield-heart-fill fs-1"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Competitive Benefits</h4>
                            <p class="text-muted mb-0">We offer comprehensive healthcare plans, generous performance incentives, and travel allowances.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card p-4 text-center why-choose-card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="mb-3 text-crimson">
                                <i class="bi bi-lightning-charge-fill fs-1"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Growth & Leadership</h4>
                            <p class="text-muted mb-0">Accelerate your career in luxury entertainment management with tailored mentorship and executive development.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="job-openings" class="py-5 bg-white">
            <div class="container py-4">
                <h2 class="text-center fw-bolder mb-2 text-crimson">Current Opportunities</h2>
                <p class="text-center text-muted mb-5">Filter by department to find the perfect role matching your expertise.</p>

                <!-- Filter Bar -->
                <div class="filter-container">
                    <button class="filter-btn active" data-filter="all">All Roles</button>
                    <button class="filter-btn" data-filter="talent">Talent Relations</button>
                    <button class="filter-btn" data-filter="production">Event Production</button>
                    <button class="filter-btn" data-filter="concierge">VIP Concierge</button>
                </div>

                <div class="row g-4" id="jobs-container">
                    <!-- Job 1 -->
                    <div class="col-md-6 col-lg-4 job-item" data-category="talent">
                        <div class="job-premium-card h-100 p-4 d-flex flex-column">
                            <div class="mb-3">
                                <span class="job-badge badge-dept">Talent Relations</span>
                                <span class="job-badge badge-loc">Hybrid</span>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">VIP Talent Coordinator</h4>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt-fill me-1"></i> Los Angeles, CA / Hybrid
                            </p>
                            <p class="text-muted small flex-grow-1">Coordinate artist riders, manage booking schedules, and liaison between celebrity management teams and private event organizers.</p>
                            <button type="button" class="btn btn-skew btn-gradient text-white mt-3 apply-job-btn" 
                                    data-bs-toggle="modal" data-bs-target="#applicationModal" 
                                    data-job-title="VIP Talent Coordinator">
                                <span class="btn-skew-text">Apply Now <i class="bi bi-arrow-right-short"></i></span>
                            </button>
                        </div>
                    </div>

                    <!-- Job 2 -->
                    <div class="col-md-6 col-lg-4 job-item" data-category="production">
                        <div class="job-premium-card h-100 p-4 d-flex flex-column">
                            <div class="mb-3">
                                <span class="job-badge badge-dept">Event Production</span>
                                <span class="job-badge badge-loc">Full-Time</span>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Celebrity Event Producer</h4>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt-fill me-1"></i> New York, NY
                            </p>
                            <p class="text-muted small flex-grow-1">Oversee end-to-end production logistics, backstage VIP hospitality, audio-visual technical compliance, and onstage artist management.</p>
                            <button type="button" class="btn btn-skew btn-gradient text-white mt-3 apply-job-btn" 
                                    data-bs-toggle="modal" data-bs-target="#applicationModal" 
                                    data-job-title="Celebrity Event Producer">
                                <span class="btn-skew-text">Apply Now <i class="bi bi-arrow-right-short"></i></span>
                            </button>
                        </div>
                    </div>

                    <!-- Job 3 -->
                    <div class="col-md-6 col-lg-4 job-item" data-category="concierge">
                        <div class="job-premium-card h-100 p-4 d-flex flex-column">
                            <div class="mb-3">
                                <span class="job-badge badge-dept">VIP Concierge</span>
                                <span class="job-badge badge-loc">Remote</span>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">High-Net-Worth Concierge Specialist</h4>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt-fill me-1"></i> Global / Remote
                            </p>
                            <p class="text-muted small flex-grow-1">Deliver personalized bespoke concierge service, private suite bookings, fan card approvals, and seamless transaction support.</p>
                            <button type="button" class="btn btn-skew btn-gradient text-white mt-3 apply-job-btn" 
                                    data-bs-toggle="modal" data-bs-target="#applicationModal" 
                                    data-job-title="High-Net-Worth Concierge Specialist">
                                <span class="btn-skew-text">Apply Now <i class="bi bi-arrow-right-short"></i></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="p-4 rounded-4 text-center border-0" style="background: linear-gradient(135deg, rgba(176,0,0,0.02) 0%, rgba(176,0,0,0.05) 100%); border: 1px solid rgba(176,0,0,0.1) !important; border-radius: 15px;">
                            <h5 class="fw-bold text-dark mb-2">Don't see your perfect fit?</h5>
                            <p class="text-muted mb-3">Send us your resume for future openings, and we'll keep you in mind.</p>
                            <button type="button" class="btn btn-skew btn-gradient text-white fw-bold px-4 py-2 apply-job-btn" 
                                    data-bs-toggle="modal" data-bs-target="#applicationModal" 
                                    data-job-title="General Application">
                                <span class="btn-skew-text">Apply Directly</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="application-form-cta" class="py-5 text-white footer-gradient-bg">
            <div class="container text-center py-4">
                <h2 class="fw-bolder mb-3 text-uppercase">Ready to Join Our Talent Community?</h2>
                <p class="lead mb-4 text-white-50">Upload your resume and details online, and we will get back to you within 3 business days.</p>
                
                <button type="button" class="btn btn-warning fw-bold text-dark btn-skew apply-job-btn" 
                        data-bs-toggle="modal" data-bs-target="#applicationModal"
                        data-job-title="General Application">
                    <span class="btn-skew-text"><i class="bi bi-send-fill me-2"></i> Apply Now</span>
                </button>
            </div>
        </section>


        <?php include "footer.php" ?>
        
        <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content modal-content-premium border-0">
                    <div class="modal-header border-0 py-3 px-4" style="background-color: #fafafa; border-bottom: 1px solid #f1f3f5 !important; position: relative; z-index: 1052;">
                        <h5 class="modal-title fw-bold text-crimson mb-0" id="applicationModalLabel">
                            <i class="bi bi-person-rolodex me-2"></i> Job Application Form
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 mb-4 rounded-3 d-flex align-items-center justify-content-between" style="background-color: rgba(176,0,0,0.08); border: 1px solid rgba(176,0,0,0.1);">
                            <span class="text-muted small fw-bold text-uppercase">Applying for Position:</span>
                            <span class="badge bg-danger text-white px-3 py-2 fw-bold" id="job-title-display" style="background-color: var(--bs-crimson-light) !important; font-size: 0.85rem; border-radius: 8px;">General Application</span>
                        </div>
                        
                        <form id="simpleApplicationForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                            
                            <input type="hidden" name="job_title" id="job-title-input" value="General Application">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullName" name="fullName" required placeholder="Enter your full name">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="e.g., +1 234 567 890">
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Current Location</label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="City, State or Country">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Upload Resume/CV (PDF, DOCX) <span class="text-danger">*</span></label>
                                <div class="drag-drop-zone" id="resume-drag-zone" tabindex="0">
                                    <i class="bi bi-file-earmark-arrow-up fs-2 text-crimson d-block mb-2" id="uploader-icon"></i>
                                    <span class="fw-bold d-block text-dark" id="resume-upload-label" style="font-size: 0.9rem;">Drag & drop your resume here</span>
                                    <span class="text-muted d-block small mb-2">or click to browse files</span>
                                    <span class="badge bg-secondary text-white font-monospace py-2 px-3 mt-2" id="file-details" style="display:none; font-size: 0.75rem;"></span>
                                </div>
                                <input class="form-control d-none" type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                <div class="form-text mt-1 text-muted" style="font-size: 0.75rem;">Allowed formats: PDF, DOCX. Max file size: 5MB.</div>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label">Cover Letter/Brief Message (Optional)</label>
                                <textarea class="form-control" id="message" name="message" rows="3" placeholder="Tell us why you are a great fit for this role..."></textarea>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="agreed" id="privacyCheck" required>
                                <label class="form-check-label small text-muted" for="privacyCheck">
                                    I confirm that I have read and agree to the <a href="privacy.php" target="_blank" class="text-crimson fw-bold text-decoration-none">Privacy Policy</a> regarding the submission of my personal data.
                                </label>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="submit_application" class="btn btn-premium-action py-3 fw-bold" style="background-color: var(--brand-crimson); color: white; width: auto; flex-grow: 1;">
                                    <i class="bi bi-check-circle me-1"></i> Submit
                                </button>
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="assets/js/jquery-3.7.1.min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <?php include "custom_script.php" ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // --- 1. Dynamic Job Filtering ---
                const filterButtons = document.querySelectorAll('.filter-btn');
                const jobItems = document.querySelectorAll('.job-item');

                filterButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        // Toggle active state
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        button.classList.add('active');

                        const filterValue = button.getAttribute('data-filter');

                        jobItems.forEach(item => {
                            const category = item.getAttribute('data-category');
                            
                            if (filterValue === 'all' || category === filterValue) {
                                // Show with smooth transition
                                item.style.display = 'block';
                                setTimeout(() => {
                                    item.style.opacity = '1';
                                    item.style.transform = 'translateY(0) scale(1)';
                                }, 10);
                            } else {
                                // Hide
                                item.style.opacity = '0';
                                item.style.transform = 'translateY(15px) scale(0.95)';
                                setTimeout(() => {
                                    item.style.display = 'none';
                                }, 300);
                            }
                        });
                    });
                });

                // Add transition style to job items
                jobItems.forEach(item => {
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0) scale(1)';
                });


                // --- 2. Modal Job Title Bind ---
                const applicationModal = document.getElementById('applicationModal');
                
                const updateModalJobTitle = function(button) {
                    const jobTitle = button.getAttribute('data-job-title');
                    const modalTitleDisplay = applicationModal.querySelector('#job-title-display');
                    const modalTitleInput = applicationModal.querySelector('#job-title-input');

                    if (modalTitleDisplay && modalTitleInput) {
                        modalTitleDisplay.textContent = jobTitle;
                        modalTitleInput.value = jobTitle;
                    }
                };

                applicationModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget; 
                    updateModalJobTitle(button);
                });


                // --- 3. Drag & Drop File Zone ---
                const dragZone = document.getElementById('resume-drag-zone');
                const fileInput = document.getElementById('resume');
                const uploaderIcon = document.getElementById('uploader-icon');
                const uploadLabel = document.getElementById('resume-upload-label');
                const fileDetails = document.getElementById('file-details');

                // Redirect click on zone to file input
                dragZone.addEventListener('click', () => {
                    fileInput.click();
                });

                // Focus/accessibility
                dragZone.addEventListener('keydown', (e) => {
                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        fileInput.click();
                    }
                });

                // Drag states
                dragZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dragZone.classList.add('drag-over');
                });

                ['dragleave', 'dragend'].forEach(type => {
                    dragZone.addEventListener(type, () => {
                        dragZone.classList.remove('drag-over');
                    });
                });

                dragZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dragZone.classList.remove('drag-over');
                    
                    if (e.dataTransfer.files.length) {
                        fileInput.files = e.dataTransfer.files;
                        validateFile(e.dataTransfer.files[0]);
                    }
                });

                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length) {
                        validateFile(fileInput.files[0]);
                    }
                });

                function validateFile(file) {
                    const allowedExts = ['pdf', 'doc', 'docx'];
                    const fileExt = file.name.split('.').pop().toLowerCase();
                    const maxSize = 5 * 1024 * 1024; // 5MB

                    if (!allowedExts.includes(fileExt)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Format',
                            text: 'Only PDF, DOC, and DOCX files are allowed.',
                            confirmButtonColor: '#6d0000'
                        });
                        resetUploader();
                        return;
                    }

                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: 'The selected file exceeds the 5MB size limit.',
                            confirmButtonColor: '#6d0000'
                        });
                        resetUploader();
                        return;
                    }

                    // Successful file selection
                    dragZone.classList.remove('is-invalid');
                    dragZone.classList.add('file-selected');
                    uploaderIcon.className = 'bi bi-file-earmark-check fs-2 text-success d-block mb-2';
                    uploadLabel.innerHTML = 'File uploaded successfully!';
                    uploadLabel.style.color = '#198754';
                    
                    // Show file name
                    fileDetails.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
                    fileDetails.style.display = 'inline-block';
                }

                function resetUploader() {
                    fileInput.value = '';
                    dragZone.classList.remove('file-selected', 'is-invalid');
                    uploaderIcon.className = 'bi bi-file-earmark-arrow-up fs-2 text-crimson d-block mb-2';
                    uploadLabel.innerHTML = 'Drag & drop your resume here';
                    uploadLabel.removeAttribute('style');
                    fileDetails.style.display = 'none';
                    fileDetails.textContent = '';
                }


                // --- 4. Submission Message Alerts ---
                <?php if ($application_status == 'error'): ?>
                    Swal.fire({
                      icon: 'error',
                      title: 'Application Error',
                      text: '<?php echo addslashes($application_message); ?>',
                      confirmButtonColor: '#6d0000'
                    });
                <?php elseif ($application_status == 'success'): ?>
                    Swal.fire({
                      icon: 'success',
                      title: 'Application Submitted!',
                      text: '<?php echo addslashes($application_message); ?>',
                      confirmButtonColor: '#6d0000'
                    });
                <?php endif; ?>
            });
        </script>
    </body>
</html>