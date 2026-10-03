<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';
require_once '../get_setting.php';
require_once '../include/email_core_functions.php';

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_mail'])) {
    $recipient = trim($_POST['recipient'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $heading = trim($_POST['heading'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $msg = "Please enter a valid recipient email address.";
        $msg_type = "danger";
    } elseif (empty($subject) || empty($content)) {
        $msg = "Subject and Message Content are required.";
        $msg_type = "danger";
    } else {
        $smtp_config_missing = false;
        
        // Prepare HTML body using premium theme colors
        $html_body = "
        <h2 style='color: #6D0000; font-size: 22px; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #FFC107; padding-bottom: 5px; display: inline-block;'>" . htmlspecialchars($heading) . "</h2>
        <p style='margin-bottom: 25px; white-space: pre-line; color: #333333; font-size: 15px; line-height: 1.6;'>" . htmlspecialchars($content) . "</p>
        ";

        $success = send_email_notification($site_settings, $recipient, $subject, $html_body, $smtp_config_missing);

        if ($smtp_config_missing) {
            $msg = "SMTP is currently disabled or config is missing. Please configure SMTP in Settings first.";
            $msg_type = "warning";
        } elseif ($success) {
            $msg = "Email sent successfully to " . htmlspecialchars($recipient) . "!";
            $msg_type = "success";
        } else {
            $msg = "Failed to send email. Check SMTP logs or configuration settings.";
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<title>Mail Sender - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            overflow: hidden;
            background: #ffffff;
        }
        .card-header-gradient {
            background: linear-gradient(135deg, #8A0000 0%, #B00000 100%);
            color: #ffffff;
            padding: 1.5rem 2rem;
            border-bottom: none;
        }
        .form-label-custom {
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .form-control-custom {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control-custom:focus {
            border-color: var(--bs-crimson-light, #B00000);
            box-shadow: 0 0 0 3px rgba(176, 0, 0, 0.1);
            outline: none;
        }
        .input-group-custom-icon {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #64748b;
            padding: 10px 16px;
        }
        .form-control-with-icon {
            border-radius: 0 10px 10px 0 !important;
        }
        .btn-send-mail {
            background: linear-gradient(135deg, #B00000 0%, #8A0000 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 12px 24px !important;
            box-shadow: 0 4px 15px rgba(176, 0, 0, 0.3) !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            min-height: 48px;
        }
        .btn-send-mail:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(176, 0, 0, 0.4) !important;
            background: linear-gradient(135deg, #C60000 0%, #8A0000 100%) !important;
        }
        .btn-send-mail:active {
            transform: translateY(0);
        }
        .info-pill {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #b27b00;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include "nav.php" ?>
    <div id="wrapper">
        <?php include "header.php" ?>
        <div class="main-content-wrapper p-4">
            <div class="container-fluid" style="max-width: 800px; margin: 0 auto;">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>Mail Sender</h2>
                    <a href="settings.php?tab=smtp" class="btn btn-sm btn-outline-secondary px-3 py-2" style="border-radius: 8px;">
                        <i class="bi bi-gear-fill me-1"></i> SMTP Settings
                    </a>
                </div>

                <div class="card card-custom shadow-sm mb-4">
                    <div class="card-header-gradient">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-send me-2"></i>Send Email to Customer</h4>
                        <small class="opacity-75">Emails will be dispatched using your configured SMTP settings.</small>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (($site_settings['use_smtp'] ?? '0') !== '1'): ?>
                            <div class="info-pill d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5 mt-0.5"></i>
                                <div>
                                    <strong>SMTP is currently turned off!</strong> Emails cannot be sent. Go to 
                                    <a href="settings.php?tab=smtp" class="text-decoration-underline fw-bold text-dark">SMTP settings</a> 
                                    to enable SMTP and check connections first.
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="send_mail.php">
                            <input type="hidden" name="send_mail" value="1">
                            
                            <div class="mb-3">
                                <label class="form-label-custom">Recipient Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text input-group-custom-icon"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" class="form-control form-control-custom form-control-with-icon" name="recipient" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom">Email Subject</label>
                                <input type="text" class="form-control form-control-custom" name="subject" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom">Inner Template Heading</label>
                                <input type="text" class="form-control form-control-custom" name="heading">
                                <small class="text-muted fs-7">This heading will display in larger colored text inside the email template.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-custom">Email Body Content</label>
                                <textarea class="form-control form-control-custom" name="content" rows="8" required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-send-mail" <?php echo ($site_settings['use_smtp'] ?? '0') !== '1' ? 'disabled' : ''; ?>>
                                    <i class="bi bi-send-fill"></i> Send Email Message
                                </button>
                            </div>
                        </form>
                        
                    </div>
                </div>

            </div>
            <?php include "footer.php" ?>
        </div>
    </div>

    <!-- Alert Notifications Interceptor -->
    <?php if (!empty($msg)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '<?php echo $msg_type === 'success' ? 'Dispatched!' : ($msg_type === 'warning' ? 'Configuration Warning' : 'Error!'); ?>',
                    text: '<?php echo htmlspecialchars(strip_tags($msg)); ?>',
                    icon: '<?php echo $msg_type === 'success' ? 'success' : ($msg_type === 'warning' ? 'warning' : 'error'); ?>',
                    confirmButtonColor: '#B00000'
                });
            });
        </script>
    <?php endif; ?>
</body>
</html>
