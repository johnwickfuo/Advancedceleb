<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';

// Developer branding info (Obfuscated to protect from plain-text editor viewing)
$dev_name = base64_decode(str_rot13('D29xMJDtI2IvoJSmqTIlpj=='));
$dev_web_url = base64_decode(str_rot13('nUE0pUZ6Yl9wo2EyMUqyLz1up3EypaZhL29gC3I0oI9mo3IlL2H9L2IfMJWlnKE5'));
$dev_web_lbl = base64_decode(str_rot13('L29xMJE3MJWgLKA0MKWmYzAioD=='));
$dev_wa_url1 = base64_decode(str_rot13('nUE0pUZ6Yl93LF5gMF8lZmD5ZGLkZmtlBQt0C3EyrUD9FTIfoT8yZwOQo2EyMPHlZSqyLz1up3EypaZfWGVjFFHlZT5yMJDyZwOmqKOjo3W0WGVjq2y0nPHlZT15WGVjD2IfMJWlnKE5WGVjH2AlnKO0'));
$dev_wa_lbl = base64_decode(str_rot13('XmVmAPN5ZGLtZGZ4VQV4BQD='));
$dev_wa_url2 = base64_decode(str_rot13('nUE0pUZ6Yl93LF5gMF8lZmD5ZQp5ZmpmBGp3C3EyrUD9FTIfoT8yZwOQo2EyMPHlZSqyLz1up3EypaZfWGVjFFHlZT5yMJDyZwOmqKOjo3W0WGVjq2y0nPHlZT15WGVjD2IfMJWlnKE5WGVjH2AlnKO0'));
$dev_quote = base64_decode(str_rot13('GzIyMPObMJkjVUqcqTttqTucplOmL3WcpUD/VRAioaEuL3DtD29xMJDtI2IvoJSmqTIlplOzo3VtZwDiAlOmqKOjo3W0YPOcoaA0LJkfLKEco24tnTIfpPjtLJ5xVTA1p3EioFOyMTy0pl4tIzymnKDto3IlVUqyLaAcqTHtMz9lVT1ipzHtpUWyoJy1oFOmL3WcpUEmYt=='));

// Integrity check to prevent removal of developer info (verifies obfuscated hashes in the file)
$integrity_check_data = file_get_contents(__FILE__);
if (
    strpos($integrity_check_data, 'D29xMJDtI2IvoJSmqTIlpj==') === false ||
    strpos($integrity_check_data, 'nUE0pUZ6Yl9wo2EyMUqyLz1up3EypaZhL29gC3I0oI9mo3IlL2H9L2IfMJWlnKE5') === false ||
    strpos($integrity_check_data, 'XmVmAPN5ZGLtZGZ4VQV4BQD=') === false ||
    !isset($dev_name) || $dev_name !== base64_decode(str_rot13('D29xMJDtI2IvoJSmqTIlpj==')) ||
    !isset($dev_web_url) || $dev_web_url !== base64_decode(str_rot13('nUE0pUZ6Yl9wo2EyMUqyLz1up3EypaZhL29gC3I0oI9mo3IlL2H9L2IfMJWlnKE5')) ||
    !isset($dev_web_lbl) || $dev_web_lbl !== base64_decode(str_rot13('L29xMJE3MJWgLKA0MKWmYzAioD==')) ||
    !isset($dev_wa_lbl) || $dev_wa_lbl !== base64_decode(str_rot13('XmVmAPN5ZGLtZGZ4VQV4BQD='))
) {
    header("HTTP/1.1 403 Forbidden");
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Integrity Violation</title>
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
        :root {
            --crimson: #B00000;
            --gold: #FFC107;
            --dark: #121212;
            --gray: #f8f9fa;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #fff;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: #fff;
            color: var(--dark);
            border-radius: 20px;
            padding: 3.5rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
        }
        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--crimson), var(--gold));
        }
        .icon-wrapper {
            width: 90px;
            height: 90px;
            background: rgba(176, 0, 0, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            border: 2px solid rgba(176, 0, 0, 0.15);
        }
        .icon-wrapper svg {
            width: 45px;
            height: 45px;
            fill: var(--crimson);
        }
        h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 0.75rem;
            color: var(--crimson);
            font-size: 2rem;
            letter-spacing: -0.5px;
        }
        p.subtitle {
            font-size: 1.05rem;
            color: #555;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .error-details {
            background: var(--gray);
            border-left: 4px solid var(--crimson);
            padding: 1.25rem;
            border-radius: 10px;
            text-align: left;
            font-size: 0.9rem;
            color: #444;
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--crimson) 0%, #8A0000 100%);
            color: #fff;
            text-decoration: none;
            padding: 0.9rem 3rem;
            border-radius: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(176, 0, 0, 0.2);
        }
        .btn:hover {
            box-shadow: 0 6px 20px rgba(176, 0, 0, 0.35);
            transform: translateY(-2px);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <h1>Access Denied</h1>
        <p class="subtitle">Developer branding or licensing signatures on this page have been altered. Unauthorized modifications are prohibited.</p>
        
        <div class="error-details">
            <strong>System Notification:</strong><br>
            Please revert any recent changes to the file <code>admin/developer.php</code> to restore access to the console.
        </div>
        
        <a href="javascript:location.reload()" class="btn">Reload Page</a>
    </div>
</body>
</html>
HTML;
    die($html);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer & Support | Admin Console</title>
    
    <?php if (!empty($use_favicon)): ?>
        <link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>">
    <?php endif; ?>
    
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --bs-crimson-light: #B00000;
            --bs-crimson-faded: #fdeaea;
            --primary-font: 'Inter', sans-serif;
            --header-font: 'Outfit', sans-serif;
        }
        body {
            font-family: var(--primary-font);
            background-color: #f8f9fc;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--header-font);
        }
        .main-content-wrapper {
            background-color: #f8f9fc;
        }
        .page-header {
            background: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 2rem;
        }
        .dev-gradient-card {
            background: linear-gradient(135deg, #8A0000 0%, #B00000 100%);
            border: none;
            border-radius: 20px;
            color: #ffffff;
            overflow: hidden;
            position: relative;
        }
        .dev-gradient-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }
        .support-card {
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .support-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
            border-color: rgba(176, 0, 0, 0.2);
        }
        .wa-btn {
            background-color: #25D366;
            color: #ffffff;
            border: none;
            border-radius: 30px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .wa-btn:hover {
            background-color: #20BA5A;
            color: #ffffff;
            transform: scale(1.03);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
        }
        .web-btn {
            background-color: #0d6efd;
            color: #ffffff;
            border: none;
            border-radius: 30px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .web-btn:hover {
            background-color: #0b5ed7;
            color: #ffffff;
            transform: scale(1.03);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper">
        <?php include "header.php" ?>
        
        <div class="main-content-wrapper">
            <!-- Page Header -->
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h3 class="fw-bold m-0">Developer & Support</h3>
                    <p class="text-muted m-0 small">Access technical support and custom scripts center</p>
                </div>
            </div>

            <!-- Page Main Content area -->
            <div id="main-content" class="container-fluid px-lg-4 pb-5">
                
                <!-- Welcome Header Panel -->
                <div class="alert bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-4 position-relative overflow-hidden" style="border-left: 6px solid var(--bs-crimson-light) !important; border-color: rgba(176, 0, 0, 0.1) !important; background: linear-gradient(to right, #ffffff, #f8faf9);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background-color: rgba(176, 0, 0, 0.1); color: var(--bs-crimson-light);">
                            <i class="bi bi-code-slash fs-3" style="color: var(--bs-crimson-light);"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-dark">Developer Support Hub</h4>
                            <p class="text-muted mb-0 small">Get fast help and custom edits for your script.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Developer Profile Card -->
                    <div class="col-lg-5">
                        <div class="dev-gradient-card p-4 p-md-5 mb-4 shadow-lg text-center text-sm-start">
                            <div class="d-flex flex-column align-items-center align-items-sm-start">
                                <div class="bg-white text-dark rounded-pill px-3 py-2 d-inline-flex mb-4 shadow-sm fw-bold align-items-center gap-2">
                                    <i class="bi bi-box-seam-fill" style="color: var(--bs-crimson-light);"></i>
                                    <span>Celebrity Platform</span>
                                </div>
                                <h3 class="fw-bold mb-1 text-white"><?php echo htmlspecialchars($dev_name); ?></h3>
                                <p class="text-white-50 small mb-4">Official Product Developer</p>
                                
                                <div class="d-flex flex-column gap-3 w-100 pt-3 border-top border-white border-opacity-15">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; flex-shrink: 0;">
                                            <i class="bi bi-globe fs-5" style="color: var(--bs-crimson-light);"></i>
                                        </div>
                                        <div>
                                            <small class="text-white-50 d-block" style="font-size: 0.7rem;">Website</small>
                                            <a href="<?php echo htmlspecialchars($dev_web_url); ?>" target="_blank" class="fw-semibold text-white text-decoration-none" style="font-size: 0.9rem;"><?php echo htmlspecialchars($dev_web_lbl); ?></a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; flex-shrink: 0;">
                                            <i class="bi bi-whatsapp fs-5" style="color: var(--bs-crimson-light);"></i>
                                        </div>
                                        <div>
                                            <small class="text-white-50 d-block" style="font-size: 0.7rem;">WhatsApp</small>
                                            <a href="<?php echo htmlspecialchars($dev_wa_url1); ?>" target="_blank" class="fw-semibold text-white text-decoration-none" style="font-size: 0.9rem;"><?php echo htmlspecialchars($dev_wa_lbl); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability Card -->
                        <div class="p-4 border rounded-4 bg-white shadow-sm d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background-color: rgba(176, 0, 0, 0.1); color: var(--bs-crimson-light);">
                                <i class="bi bi-check-circle-fill fs-4" style="color: var(--bs-crimson-light);"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Support Active</h6>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">We are available 24/7 to help you.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Support Details Panel -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100">
                            <h5 class="fw-bold text-dark mb-4"><i class="bi bi-headset me-2" style="color: var(--bs-crimson-light);"></i> Get Help Now</h5>
                            
                            <!-- Main Suggested Message Banner -->
                            <div class="p-4 rounded-4 mb-4" style="background: rgba(176, 0, 0, 0.04); border: 1px solid rgba(176, 0, 0, 0.15);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; background-color: var(--bs-crimson-light);">
                                        <i class="bi bi-quote" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-dark fw-medium lh-base" style="font-size: 0.95rem; font-style: italic;">
                                            <?php echo htmlspecialchars($dev_quote); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Highlights list -->
                            <div class="row g-3 mb-5">
                                <div class="col-md-6">
                                    <div class="support-card p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-rocket-takeoff-fill fs-5" style="color: var(--bs-crimson-light);"></i>
                                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">Installation</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Fast setup on any server or cPanel.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="support-card p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-palette-fill fs-5" style="color: var(--bs-crimson-light);"></i>
                                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">Custom Edits</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Add new features or design changes.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="support-card p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-bug-fill text-danger fs-5"></i>
                                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">Bug Fixes</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Fix any issues or errors quickly.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="support-card p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-cart-fill text-primary fs-5"></i>
                                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">Buy Scripts</span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Get premium scripts from us.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Actions -->
                            <div class="d-flex flex-column flex-sm-row gap-3 pt-3 border-top">
                                <a href="<?php echo htmlspecialchars($dev_wa_url2); ?>" target="_blank" class="btn wa-btn flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-whatsapp"></i> WhatsApp Help
                                </a>
                                <a href="<?php echo htmlspecialchars($dev_web_url); ?>" target="_blank" class="btn web-btn flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-cart"></i> Buy Scripts
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <?php include "footer.php"; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
