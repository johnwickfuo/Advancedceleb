<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["admin_id"])) {
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

$password_msg = '';
$password_err = '';
$settings_msg = '';
$settings_err = '';
$branding_msg = '';
$branding_err = '';
$payments_msg = ''; 
$payments_err = '';
$smtp_msg = '';
$smtp_err = '';
$smtp_test_msg = '';
$smtp_test_err = '';
$smtp_test_log = '';
$active_tab = 'site-settings'; // Default changed to site-settings for general access

// Logic to set active tab based on GET parameter (for direct links)
if (isset($_GET['tab'])) {
    $allowed_tabs = ['password', 'site-settings', 'branding', 'payments', 'smtp'];
    if (in_array($_GET['tab'], $allowed_tabs)) {
        $active_tab = $_GET['tab'];
    }
}

// Logic to set active tab based on which form was submitted (overrides GET)
if (isset($_POST['update_password'])) {
    $active_tab = 'password';
} elseif (isset($_POST['update_settings'])) {
    $active_tab = 'site-settings';
} elseif (isset($_POST['update_branding'])) {
    $active_tab = 'branding';
} elseif (isset($_POST['update_payments'])) { 
    $active_tab = 'payments';
} elseif (isset($_POST['update_smtp'])) {
    $active_tab = 'smtp';
} elseif (isset($_POST['test_smtp'])) {
    $active_tab = 'smtp';
}


/**
 * Helper function to determine the base URL of the site, correctly handling
 * both production environments and localhost (with or without a port).
 * */
function get_dynamic_site_url() {
    // Determine Protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
    
    $host = $_SERVER['HTTP_HOST'];
    
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    $base_path = str_replace('/admin', '', $script_dir);

    // Clean up any remaining trailing slash to ensure consistency.
    $base_path = rtrim($base_path, '/');
    
    return $protocol . $host . $base_path;
}




// Function to save or update a single setting
function save_site_setting($pdo, $key, $value) {
    $sql = "INSERT INTO site_config (setting_key, setting_value) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE setting_value = :value";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":key", $key, PDO::PARAM_STR);
        $stmt->bindParam(":value", $value, PDO::PARAM_STR);
        return $stmt->execute();
    }
    return false;
}

$site_settings = get_site_settings($pdo);

// --- PASSWORD UPDATE HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $new_password = trim($_POST['new_password'] ?? '');
    
    if (empty($new_password)) {
        $password_err = "Please enter a new password.";
    } elseif (strlen($new_password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    if (empty($password_err)) {
        // 2. CRITICAL SECURITY FIX: Use password_hash()
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        if ($hashed_password === false) {
            $password_err = "Error: Failed to hash password.";
        } else {
            // Use the actual admin ID
            $admin_id = $_SESSION["admin_id"]; 
            $sql = "UPDATE admins SET password_hash = :password_hash WHERE admin_id = :id";
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":password_hash", $hashed_password, PDO::PARAM_STR);
                $stmt->bindParam(":id", $admin_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $password_msg = "Password updated successfully.";
                } else {
                    $password_err = "Error: Could not update password. Please try again later.";
                }
                unset($stmt);
            }
        }
    }
}

// --- SITE SETTINGS HANDLER (Live Chat, SMTP, Language, TWILIO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    
    // Data Retrieval and Sanitation
    $smartsupp_code = $_POST['smartsupp_code'] ?? '';
    $use_smartsupp = isset($_POST['use_smartsupp']) ? '1' : '0';
    $contact_email = trim($_POST['contact_email'] ?? 'info@vipcelebrity.com');
    
    $default_language = trim($_POST['default_language'] ?? 'en');
    
    // --- SITE_URL FETCH AND SAVE ---
    $site_url = get_dynamic_site_url();
    
    $settings_to_save = [
        'site_title' => trim($_POST['site_title'] ?? $site_settings['site_title'] ?? ''),
        'site_url' => $site_url, 
        'smartsupp_code' => $smartsupp_code,
        'use_smartsupp' => $use_smartsupp,
        'contact_email' => $contact_email,
        'contact_phone' => trim($_POST['contact_phone'] ?? ''),
        'contact_address' => trim($_POST['contact_address'] ?? ''),
        'default_language' => $default_language,
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
        'enable_cargo_scroll' => isset($_POST['enable_cargo_scroll']) ? '1' : '0',
        'cargo_scroll_text' => trim($_POST['cargo_scroll_text'] ?? ''),
        'enable_back_to_top' => isset($_POST['enable_back_to_top']) ? '1' : '0',
        'enable_translator' => isset($_POST['enable_translator']) ? '1' : '0',
        'disable_right_click_copy' => isset($_POST['disable_right_click_copy']) ? '1' : '0',
        'enable_live_bookings_desktop' => isset($_POST['enable_live_bookings_desktop']) ? '1' : '0',
        'enable_live_bookings_mobile' => isset($_POST['enable_live_bookings_mobile']) ? '1' : '0',
        'currency_code' => trim($_POST['currency_code'] ?? 'USD'),
        'currency_symbol' => trim($_POST['currency_symbol'] ?? '$'),
        'currency_position' => trim($_POST['currency_position'] ?? 'prefix'),
    ];
    
    // --- Input Validation (Best Practice) ---
    $errors = [];
    
    // 1. Live Chat Code Validation
    $code_lower = strtolower($smartsupp_code);
    if (!empty($smartsupp_code) && strpos($code_lower, '<script') === false) {
        $errors[] = "Invalid Live Chat code format. Must contain a valid <script> tag.";
    }

    // 2. Contact Email Validation
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid contact email address format.";
    }

    
    if (empty($errors)) {
        $success = true;
        
        // Loop and save ALL settings for this tab
        foreach ($settings_to_save as $key => $value) {
            
            if (!save_site_setting($pdo, $key, $value)) {
                $errors[] = "Error saving **$key** setting.";
                $success = false;
            } else {
                // Update the current settings array
                $site_settings[$key] = $value;
            }
        }
        
        if ($success) {
            $settings_msg = "Site settings updated successfully.";
        } else {
            $settings_err = implode('<br>', $errors) . " Check the 'site_config' table and PDO connection.";
        }
    } else {
        $settings_err = implode('<br>', $errors);
    }
}

// --- BRANDING UPDATE HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_branding'])) {
    $errors = [];
    $messages = [];
    $upload_dir = '../assets/images/';

    // --- SITE_URL FETCH AND SAVE ---
    $site_url = get_dynamic_site_url();
    if (!save_site_setting($pdo, 'site_url', $site_url)) {
        $errors[] = "Error saving dynamic site_url.";
    } else {
        $site_settings['site_url'] = $site_url;
    }
    // ---------------------------------------

    // Handle text settings first (Title, Toggles, Chat Links, Social Links)
    $branding_settings_to_save = [
        'site_title' => trim($_POST['site_title'] ?? $site_settings['site_title'] ?? ''),
        'use_site_logo' => isset($_POST['use_site_logo']) ? '1' : '0',
        'telegram_username' => trim($_POST['telegram_username'] ?? ''),
        'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
        'use_telegram' => isset($_POST['use_telegram']) ? '1' : '0',
        'use_whatsapp' => isset($_POST['use_whatsapp']) ? '1' : '0',
        'twitter_url' => trim($_POST['twitter_url'] ?? ''),
        'facebook_url' => trim($_POST['facebook_url'] ?? ''),
        'youtube_url' => trim($_POST['youtube_url'] ?? ''),
        'instagram_url' => trim($_POST['instagram_url'] ?? ''),
        'linkedin_url' => trim($_POST['linkedin_url'] ?? ''),
        'use_twitter' => isset($_POST['use_twitter']) ? '1' : '0',
        'use_facebook' => isset($_POST['use_facebook']) ? '1' : '0',
        'use_youtube' => isset($_POST['use_youtube']) ? '1' : '0',
        'use_instagram' => isset($_POST['use_instagram']) ? '1' : '0',
        'use_linkedin' => isset($_POST['use_linkedin']) ? '1' : '0',
    ];

    foreach ($branding_settings_to_save as $key => $value) {
        if (!save_site_setting($pdo, $key, $value)) {
            $errors[] = "Error saving $key.";
        } else {
            $site_settings[$key] = $value;
        }
    }

    // Handle File Uploads
    $file_keys = [
        'logo_file' => 'site_logo', 
        'favicon_file' => 'site_favicon',
        'slider_bg_1_file' => 'slider_bg_1',
        'slider_bg_2_file' => 'slider_bg_2',
        'slider_bg_3_file' => 'slider_bg_3'
    ];

    foreach ($file_keys as $file_input_name => $setting_key) {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$file_input_name];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = in_array($setting_key, ['site_logo', 'slider_bg_1', 'slider_bg_2', 'slider_bg_3']) ? ['jpg', 'jpeg', 'png', 'svg', 'webp'] : ['ico', 'png', 'svg'];
            $max_size = 8 * 1024 * 1024; // 8MB

            if (!in_array($ext, $allowed_exts)) {
                $errors[] = ucfirst(str_replace('_', ' ', $setting_key)) . " upload failed: Only " . implode(', ', $allowed_exts) . " files are allowed.";
                continue;
            }
            if ($file['size'] > $max_size) {
                $errors[] = ucfirst(str_replace('_', ' ', $setting_key)) . " upload failed: File size exceeds 8MB limit.";
                continue;
            }

            $unique_filename = $setting_key . '_' . time() . '.' . $ext;
            $target_file = $upload_dir . $unique_filename;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                
                // Delete old file if it exists
                if (!empty($site_settings[$setting_key] ?? '') && file_exists($upload_dir . $site_settings[$setting_key])) {
                    unlink($upload_dir . $site_settings[$setting_key]);
                }

                if (save_site_setting($pdo, $setting_key, $unique_filename)) {
                    $site_settings[$setting_key] = $unique_filename;
                    $messages[] = ucfirst(str_replace('_', ' ', $setting_key)) . " updated successfully.";
                } else {
                    $errors[] = "Error: Could not save " . ucfirst(str_replace('_', ' ', $setting_key)) . " to the database.";
                    unlink($target_file); 
                }
            } else {
                $errors[] = "Error uploading " . ucfirst(str_replace('_', ' ', $setting_key)) . ". Check file permissions on " . $upload_dir;
            }
        }
    }
    
    // Set final messages/errors
    if (empty($errors)) {
        $branding_msg = empty($messages) ? "Branding settings saved successfully." : implode('<br>', $messages);
    } else {
        $branding_err = implode('<br>', $errors);
    }
}
// --- SMTP SETTINGS HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_smtp'])) {
    $errors = [];
    $use_smtp = isset($_POST['use_smtp']) ? '1' : '0';
    $smtp_settings_to_save = [
        'use_smtp' => $use_smtp,
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => trim($_POST['smtp_port'] ?? ''),
        'smtp_username' => trim($_POST['smtp_username'] ?? ''),
        'smtp_password' => trim($_POST['smtp_password'] ?? ''),
        'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
        'smtp_from_name' => trim($_POST['smtp_from_name'] ?? ''),
        'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
    ];

    foreach ($smtp_settings_to_save as $key => $value) {
        if (!save_site_setting($pdo, $key, $value)) {
            $errors[] = "Error saving $key.";
        }
        $site_settings[$key] = $value;
    }

    if (empty($errors)) {
        $smtp_msg = "SMTP settings updated successfully.";
    } else {
        $smtp_err = implode('<br>', $errors);
    }
}

// --- SMTP TEST HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
    $recipient = trim($_POST['test_email'] ?? '');
    
    if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $smtp_test_err = "Please enter a valid recipient email address.";
    } else {
        require_once '../include/email_core_functions.php';

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $smtp_test_log = '';

        // Capture SMTP logs
        $mail->SMTPDebug = 3; 
        $mail->Debugoutput = function($str, $level) use (&$smtp_test_log) {
            $smtp_test_log .= $str . "\n";
        };

        try {
            $host = $site_settings['smtp_host'] ?? '';
            $port = $site_settings['smtp_port'] ?? '';
            $username = $site_settings['smtp_username'] ?? '';
            $password = $site_settings['smtp_password'] ?? '';
            $encryption = $site_settings['smtp_encryption'] ?? '';
            $from_email = $site_settings['smtp_from_email'] ?? '';
            $from_name = $site_settings['smtp_from_name'] ?? '';

            if (empty($host) || empty($username) || empty($password)) {
                throw new \Exception("SMTP configuration is incomplete. Please fill and save Host, Username, and Password first.");
            }

            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            
            $enc = strtolower($encryption);
            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($enc === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = '';
            }
            
            $mail->Port = !empty($port) ? (int)$port : 465;
            $final_from_email = !empty($from_email) ? $from_email : $username;
            $final_from_name  = !empty($from_name) ? $from_name : ($site_settings['site_title'] ?? 'VIP Celebrity Booking');

            $mail->setFrom($final_from_email, $final_from_name);
            
            if (!empty($from_email) && strtolower($from_email) !== strtolower($username)) {
                $mail->addReplyTo($from_email, $final_from_name);
            }
            
            $mail->addAddress($recipient);
            
            $mail->isHTML(true);
            $mail->Subject = 'SMTP Diagnostic Test';
            
            $inner_html = "
            <h2 style='color: #6D0000; font-size: 22px; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #FFC107; padding-bottom: 5px; display: inline-block;'>SMTP Diagnostic Test</h2>
            <p style='margin-bottom: 25px;'>Hello,</p>
            <p style='margin-bottom: 25px;'>This is a diagnostic test email sent from the Celebrity Booking system settings page. If you are reading this, your SMTP server settings are working perfectly!</p>
            
            <div style='background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 6px; margin-bottom: 30px; font-weight: bold; text-align: center;'>
                Connection Verified Successfully!
            </div>

            <h3 style='font-size: 16px; color: #2e3b4e; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eef1f3; padding-bottom: 8px;'>Diagnostic Details</h3>
            <table width='100%' cellpadding='8' cellspacing='0' border='0' style='font-size: 14px; margin-bottom: 15px;'>
                <tr style='background-color: #f8fafb;'>
                    <td style='color: #8a9ba8; font-weight: bold;'>SMTP Host</td>
                    <td style='font-family: monospace; color: #2e3b4e;'>{$host}</td>
                </tr>
                <tr>
                    <td style='color: #8a9ba8; font-weight: bold;'>SMTP Port</td>
                    <td style='font-family: monospace; color: #2e3b4e;'>{$port}</td>
                </tr>
                <tr style='background-color: #f8fafb;'>
                    <td style='color: #8a9ba8; font-weight: bold;'>Encryption</td>
                    <td style='color: #2e3b4e;'>".($encryption ?: 'None')."</td>
                </tr>
                <tr>
                    <td style='color: #8a9ba8; font-weight: bold;'>Sender (From)</td>
                    <td style='color: #2e3b4e;'>{$final_from_email}</td>
                </tr>
            </table>
            ";
            
            $mail->Body = get_premium_email_html('SMTP Connection Test', $inner_html, $site_settings);
            
            // Generate plain text AltBody
            $text_body = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>'], "\n", $mail->Body));
            $text_body = html_entity_decode($text_body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $mail->AltBody = trim(preg_replace("/\n{3,}/", "\n\n", $text_body));
            if ($mail->send()) {
                $smtp_test_msg = "Test email sent successfully to " . htmlspecialchars($recipient) . "!";
            } else {
                $smtp_test_err = "Failed to send test email: " . ($mail->ErrorInfo ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $smtp_test_err = "SMTP Connection Failed: " . $e->getMessage();
        }
    }
}

// --- PAYMENT SETTINGS HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payments'])) {
    $errors = [];
    
    // Data Retrieval and Sanitation
    $bank_name = trim($_POST['bank_name'] ?? '');
    $bank_account_name = trim($_POST['bank_account_name'] ?? '');
    $bank_account_number = trim($_POST['bank_account_number'] ?? '');
    $bank_sort_code = trim($_POST['bank_sort_code'] ?? '');

    $crypto_ltc = trim($_POST['crypto_ltc'] ?? '');
    $crypto_eth = trim($_POST['crypto_eth'] ?? '');
    $crypto_usdt = trim($_POST['crypto_usdt'] ?? '');
    $crypto_btc = trim($_POST['crypto_btc'] ?? '');
    
    $giftcard_note = trim($_POST['giftcard_note'] ?? '');

    // Toggles
    $use_bank = isset($_POST['use_bank']) ? '1' : '0';
    $use_crypto = isset($_POST['use_crypto']) ? '1' : '0';
    $use_giftcard = isset($_POST['use_giftcard']) ? '1' : '0';

    $settings_to_save = [
        'bank_name' => $bank_name,
        'bank_account_name' => $bank_account_name,
        'bank_account_number' => $bank_account_number,
        'bank_sort_code' => $bank_sort_code,
        'crypto_ltc' => $crypto_ltc,
        'crypto_eth' => $crypto_eth,
        'crypto_usdt' => $crypto_usdt,
        'crypto_btc' => $crypto_btc,
        'giftcard_note' => $giftcard_note,
        'use_bank' => $use_bank,
        'use_crypto' => $use_crypto,
        'use_giftcard' => $use_giftcard,
    ];
    
    foreach ($settings_to_save as $key => $value) {
        if (!save_site_setting($pdo, $key, $value)) {
            $errors[] = "Error saving $key.";
        }
        $site_settings[$key] = $value;
    }

    if (empty($errors)) {
        $payments_msg = "Payment settings updated successfully.";
    } else {
        $payments_err = implode('<br>', $errors);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
    <title><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Celebrity Booking'); ?> - Admin Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <?php if (!empty($site_settings['site_favicon'])): ?>
        <link rel="icon" href="../assets/images/<?php echo htmlspecialchars($site_settings['site_favicon']); ?>">
    <?php endif; ?>
    <style>
    :root {
        --bs-crimson-light: #B00000;
        --bs-crimson-dark: #6D0000;
        --bs-crimson-faded: #b000001a;
        --crimson-gradient: linear-gradient(135deg, var(--bs-crimson-light), var(--bs-crimson-dark));
    }
    .brand-crimson {color: var(--bs-crimson-light) !important;}
    .btn-crimson { 
        background: var(--bs-crimson-light) !important; 
        background: var(--crimson-gradient) !important; 
        color: #ffffff !important; 
        box-shadow: 0 4px 15px rgba(176, 0, 0, 0.3) !important; 
        border: none !important; 
    } 
    .btn-crimson:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 8px 20px rgba(176, 0, 0, 0.4) !important; 
        background: linear-gradient(135deg, #C60000, #8A0000) !important; 
        color: #ffffff !important; 
    } 
    .btn-crimson i { color: #ffffff !important; }
    .nav-tabs {border-bottom: 1px solid #dee2e6;}
    .nav-tabs .nav-link {border: none;color: #6c757d;margin-bottom: 0;padding: 0.5rem 1rem;transition: all 0.3s ease;position: relative;text-transform:uppercase !important;}
    .nav-tabs .nav-link:hover {color: #495057;background-color: #f8f9fa;border-radius: 0.25rem 0.25rem 0 0;}
    .nav-tabs .nav-link.active {font-weight: 700;color: var(--bs-crimson-light) !important;background-color: #fff;}
    .nav-tabs .nav-link.active::after {content: "";position: absolute;bottom: -1px;left: 0;right: 0;height: 3px;background-color: var(--bs-crimson-light);border-radius: 2px 2px 0 0;z-index: 10;}
    .nav-tabs .nav-link i {color: inherit;}
    .text-gold {color: #d4af37;}
    .form-label {font-weight: 600;color: #343a40;margin-bottom: 5px;}
    .form-switch .form-check-input {width: 2.5em;height: 1.5em;margin-left: -2.5em;margin-right: 0.5rem;}
    .form-switch .form-check-input:checked {background-color: var(--bs-crimson-light);border-color: var(--bs-crimson-light);}
    .form-switch .form-check-input:focus {border-color: rgba(0,0,0,0.25);box-shadow: 0 0 0 0.25rem var(--bs-crimson-faded);}
    </style>
</head>
<body>

    <?php include "nav.php" ?>
    
    <div id="wrapper">
        
        <?php include "header.php" ?>

        <div class="main-content-wrapper">
            <div id="main-content" class="container-fluid p-4">
                <h4 class="fw-bolder mb-4">Admin Settings</h4>
                
                <div class="card-enhanced border-0">
                    <div class="card-body p-4 p-md-5">

                        <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($active_tab === 'password' ? 'active' : ''); ?>" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="<?php echo ($active_tab === 'password' ? 'true' : 'false'); ?>">
                                    <i class="bi bi-lock me-1"></i> Password
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($active_tab === 'site-settings' ? 'active' : ''); ?>" id="site-tab" data-bs-toggle="tab" data-bs-target="#site-settings" type="button" role="tab" aria-controls="site-settings" aria-selected="<?php echo ($active_tab === 'site-settings' ? 'true' : 'false'); ?>">
                                    <i class="bi bi-gear me-1"></i> Site Settings
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($active_tab === 'branding' ? 'active' : ''); ?>" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab" aria-controls="branding" aria-selected="<?php echo ($active_tab === 'branding' ? 'true' : 'false'); ?>">
                                    <i class="bi bi-palette me-1"></i> Branding
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($active_tab === 'payments' ? 'active' : ''); ?>" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab" aria-controls="payments" aria-selected="<?php echo ($active_tab === 'payments' ? 'true' : 'false'); ?>">
                                    <i class="bi bi-credit-card me-1"></i> Payments
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($active_tab === 'smtp' ? 'active' : ''); ?>" id="smtp-tab" data-bs-toggle="tab" data-bs-target="#smtp" type="button" role="tab" aria-controls="smtp" aria-selected="<?php echo ($active_tab === 'smtp' ? 'true' : 'false'); ?>">
                                    <i class="bi bi-envelope-at me-1"></i> Email & SMTP
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="settingsTabContent">
                            
                            <!-- TAB 1: PASSWORD -->
                            <div class="tab-pane fade <?php echo ($active_tab === 'password' ? 'show active' : ''); ?>" id="password" role="tabpanel" aria-labelledby="password-tab">
                                <h5 class="fw-bold mb-3">Admin Password</h5>

                                <?php if ($password_msg): ?>
                                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $password_msg; ?></div>
                                <?php endif; ?>
                                <?php if ($password_err): ?>
                                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $password_err; ?></div>
                                <?php endif; ?>

                                <form action="settings.php" method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="update_password" value="1">

                                    <div class="mb-4">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="New password">
                                        <div class="invalid-feedback">
                                            Please provide a new password with at least 6 characters.
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-crimson fw-bold px-4 py-2">
                                            <i class="bi bi-shield-lock me-1"></i>Save Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- TAB 2: SITE SETTINGS -->
                            <div class="tab-pane fade <?php echo ($active_tab === 'site-settings' ? 'show active' : ''); ?>" id="site-settings" role="tabpanel" aria-labelledby="site-tab">
                                <h5 class="fw-bold mb-3">Site Configuration</h5>

                                <?php if ($settings_msg): ?>
                                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $settings_msg; ?></div>
                                <?php endif; ?>
                                <?php if ($settings_err): ?>
                                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $settings_err; ?></div>
                                <?php endif; ?>

                                <form action="settings.php" method="POST">
                                    <input type="hidden" name="update_settings" value="1">

                                    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-gear-fill me-2 text-primary"></i>General Information</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label for="site_title_setting" class="form-label">Website Title</label>
                                            <input type="text" class="form-control" id="site_title_setting" name="site_title" value="<?php echo htmlspecialchars($site_settings['site_title'] ?? ''); ?>" placeholder="Enter website title">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="contact_email" class="form-label">Contact Email</label>
                                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($site_settings['contact_email'] ?? ''); ?>" placeholder="info@example.com">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="contact_phone" class="form-label">Contact Phone</label>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($site_settings['contact_phone'] ?? ''); ?>" placeholder="+1 (555) 000-0000">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="contact_address" class="form-label">Contact Address</label>
                                            <input type="text" class="form-control" id="contact_address" name="contact_address" value="<?php echo htmlspecialchars($site_settings['contact_address'] ?? ''); ?>" placeholder="Address">
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-currency-dollar me-2 text-success"></i>Currency & Pricing</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-12 mb-3">
                                            <label for="currency_preset_select" class="form-label"><i class="bi bi-globe me-1 text-primary"></i> Currency Preset</label>
                                            <select class="form-select border-primary" id="currency_preset_select" onchange="applyCurrencyPreset(this)">
                                                <option value="">-- Choose Currency Preset --</option>
                                                <option value='{"code":"USD","symbol":"$","position":"prefix"}'>USD - US Dollar ($)</option>
                                                <option value='{"code":"EUR","symbol":"€","position":"suffix"}'>EUR - Euro (€)</option>
                                                <option value='{"code":"GBP","symbol":"£","position":"prefix"}'>GBP - British Pound (£)</option>
                                                <option value='{"code":"JPY","symbol":"¥","position":"prefix"}'>JPY - Japanese Yen (¥)</option>
                                                <option value='{"code":"CAD","symbol":"CA$","position":"prefix"}'>CAD - Canadian Dollar (CA$)</option>
                                                <option value='{"code":"AUD","symbol":"A$","position":"prefix"}'>AUD - Australian Dollar (A$)</option>
                                                <option value='{"code":"CHF","symbol":"CHF","position":"prefix"}'>CHF - Swiss Franc (CHF)</option>
                                                <option value='{"code":"CNY","symbol":"¥","position":"prefix"}'>CNY - Chinese Yuan (¥)</option>
                                                <option value='{"code":"INR","symbol":"₹","position":"prefix"}'>INR - Indian Rupee (₹)</option>
                                                <option value='{"code":"BRL","symbol":"R$","position":"prefix"}'>BRL - Brazilian Real (R$)</option>
                                                <option value='{"code":"RUB","symbol":"₽","position":"suffix"}'>RUB - Russian Ruble (₽)</option>
                                                <option value='{"code":"ZAR","symbol":"R","position":"prefix"}'>ZAR - South African Rand (R)</option>
                                                <option value='{"code":"NGN","symbol":"₦","position":"prefix"}'>NGN - Nigerian Naira (₦)</option>
                                                <option value='{"code":"GHS","symbol":"GH₵","position":"prefix"}'>GHS - Ghanaian Cedi (GH₵)</option>
                                                <option value='{"code":"KES","symbol":"KSh","position":"prefix"}'>KES - Kenyan Shilling (KSh)</option>
                                                <option value='{"code":"EGP","symbol":"E£","position":"prefix"}'>EGP - Egyptian Pound (E£)</option>
                                                <option value='{"code":"AED","symbol":"AED","position":"prefix"}'>AED - UAE Dirham (AED)</option>
                                                <option value='{"code":"SAR","symbol":"SAR","position":"prefix"}'>SAR - Saudi Riyal (SAR)</option>
                                                <option value='{"code":"KRW","symbol":"₩","position":"prefix"}'>KRW - South Korean Won (₩)</option>
                                                <option value='{"code":"MXN","symbol":"$","position":"prefix"}'>MXN - Mexican Peso ($)</option>
                                                <option value='{"code":"SGD","symbol":"S$","position":"prefix"}'>SGD - Singapore Dollar (S$)</option>
                                                <option value='{"code":"NZD","symbol":"NZ$","position":"prefix"}'>NZD - New Zealand Dollar (NZ$)</option>
                                                <option value='{"code":"HKD","symbol":"HK$","position":"prefix"}'>HKD - Hong Kong Dollar (HK$)</option>
                                                <option value='{"code":"SEK","symbol":"kr","position":"suffix"}'>SEK - Swedish Krona (kr)</option>
                                                <option value='{"code":"NOK","symbol":"kr","position":"suffix"}'>NOK - Norwegian Krone (kr)</option>
                                                <option value='{"code":"DKK","symbol":"kr","position":"suffix"}'>DKK - Danish Krone (kr)</option>
                                                <option value='{"code":"TRY","symbol":"₺","position":"prefix"}'>TRY - Turkish Lira (₺)</option>
                                                <option value='{"code":"THB","symbol":"฿","position":"prefix"}'>THB - Thai Baht (฿)</option>
                                                <option value='{"code":"IDR","symbol":"Rp","position":"prefix"}'>IDR - Indonesian Rupiah (Rp)</option>
                                                <option value='{"code":"MYR","symbol":"RM","position":"prefix"}'>MYR - Malaysian Ringgit (RM)</option>
                                                <option value='{"code":"PHP","symbol":"₱","position":"prefix"}'>PHP - Philippine Peso (₱)</option>
                                                <option value='{"code":"PLN","symbol":"zł","position":"suffix"}'>PLN - Polish Zloty (zł)</option>
                                            </select>
                                            <div class="form-text text-muted">Select a preset or enter values manually.</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="currency_code" class="form-label">Currency Code</label>
                                            <input type="text" class="form-control" id="currency_code" name="currency_code" value="<?php echo htmlspecialchars($site_settings['currency_code'] ?? 'USD'); ?>" placeholder="e.g. USD, EUR, GBP">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                            <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?php echo htmlspecialchars($site_settings['currency_symbol'] ?? '$'); ?>" placeholder="e.g. $, €, £">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="currency_position" class="form-label">Symbol Position</label>
                                            <select class="form-select" id="currency_position" name="currency_position">
                                                <option value="prefix" <?php echo (($site_settings['currency_position'] ?? 'prefix') === 'prefix' ? 'selected' : ''); ?>>Prefix ($100)</option>
                                                <option value="suffix" <?php echo (($site_settings['currency_position'] ?? '') === 'suffix' ? 'selected' : ''); ?>>Suffix (100 €)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-chat-dots-fill me-2 text-info"></i>Live Chat Widget</h6>
                                    <div class="mb-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="use_smartsupp" name="use_smartsupp" value="1" <?php echo (($site_settings['use_smartsupp'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                            <label class="form-check-label fw-bold" for="use_smartsupp">Enable Live Chat</label>
                                        </div>
                                        <label for="smartsupp_code" class="form-label">Chat Script / Code</label>
                                        <textarea class="form-control font-monospace" id="smartsupp_code" name="smartsupp_code" rows="3" placeholder="Paste chat widget script snippet here"><?php echo htmlspecialchars($site_settings['smartsupp_code'] ?? ''); ?></textarea>
                                    </div>

                                    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-sliders me-2 text-secondary"></i>Feature Toggles</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?php echo (($site_settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-danger" for="maintenance_mode"><i class="bi bi-exclamation-octagon me-1"></i> Maintenance Mode</label>
                                                    <div class="form-text text-muted">Redirect visitors to maintenance page.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="enable_translator" name="enable_translator" value="1" <?php echo (($site_settings['enable_translator'] ?? '0') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-primary" for="enable_translator"><i class="bi bi-translate me-1"></i> Google Translator</label>
                                                    <div class="form-text text-muted">Display language selector in navbar.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="enable_live_bookings_desktop" name="enable_live_bookings_desktop" value="1" <?php echo (($site_settings['enable_live_bookings_desktop'] ?? '0') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-success" for="enable_live_bookings_desktop"><i class="bi bi-bell-fill me-1"></i> Live Bookings (Desktop)</label>
                                                    <div class="form-text text-muted">Display live booking popups on desktop.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="enable_live_bookings_mobile" name="enable_live_bookings_mobile" value="1" <?php echo (($site_settings['enable_live_bookings_mobile'] ?? '0') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-success" for="enable_live_bookings_mobile"><i class="bi bi-phone-vibrate me-1"></i> Live Bookings (Mobile)</label>
                                                    <div class="form-text text-muted">Display live booking popups on mobile.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="enable_cargo_scroll" name="enable_cargo_scroll" value="1" <?php echo (($site_settings['enable_cargo_scroll'] ?? '0') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-dark" for="enable_cargo_scroll"><i class="bi bi-badge-ad me-1"></i> Announcement Ticker</label>
                                                    <div class="form-text text-muted">Display scrolling ticker bar across homepage.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="enable_back_to_top" name="enable_back_to_top" value="1" <?php echo (($site_settings['enable_back_to_top'] ?? '1') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-dark" for="enable_back_to_top"><i class="bi bi-arrow-up-circle me-1"></i> Back To Top Button</label>
                                                    <div class="form-text text-muted">Display floating back-to-top scroll button.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch card p-3 border shadow-sm h-100">
                                                <input class="form-check-input ms-0 me-3" type="checkbox" id="disable_right_click_copy" name="disable_right_click_copy" value="1" <?php echo (($site_settings['disable_right_click_copy'] ?? '0') === '1' ? 'checked' : ''); ?> style="width: 3rem; height: 1.5rem;">
                                                <div>
                                                    <label class="form-check-label fw-bold text-danger" for="disable_right_click_copy"><i class="bi bi-shield-lock-fill me-1"></i> Disable Right-Click & Copy</label>
                                                    <div class="form-text text-muted">Prevent right-click context menu and copying.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="cargo_scroll_text" class="form-label">Announcement Ticker Text</label>
                                        <input type="text" class="form-control" id="cargo_scroll_text" name="cargo_scroll_text" value="<?php echo htmlspecialchars($site_settings['cargo_scroll_text'] ?? ''); ?>" placeholder="Enter announcement text">
                                    </div>

                                    <div class="mt-4 border-top pt-3">
                                        <button type="submit" class="btn btn-crimson fw-bold px-4 py-2 shadow-sm">
                                            <i class="bi bi-check2-circle me-1"></i>Save Settings
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- TAB 3: BRANDING & SOCIAL -->
                            <div class="tab-pane fade <?php echo ($active_tab === 'branding' ? 'show active' : ''); ?>" id="branding" role="tabpanel" aria-labelledby="branding-tab">
                                <h5 class="fw-bold mb-3">Branding & Social</h5>

                                <?php if ($branding_msg): ?>
                                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $branding_msg; ?></div>
                                <?php endif; ?>
                                <?php if ($branding_err): ?>
                                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $branding_err; ?></div>
                                <?php endif; ?>

                                <form action="settings.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="update_branding" value="1">

                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <h6 class="fw-bold mb-3 border-bottom pb-2">Brand Identity</h6>
                                            <div class="mb-3">
                                                <label for="site_title_branding" class="form-label">Site Title / Brand Name</label>
                                                <input type="text" class="form-control" id="site_title_branding" name="site_title" value="<?php echo htmlspecialchars($site_settings['site_title'] ?? ''); ?>" placeholder="Enter website title">
                                                <div class="form-text">Appears in browser tabs and page titles.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3 border-bottom pb-2">Site Logo</h6>
                                            <div class="mb-3">
                                                <label for="logo_file" class="form-label">Upload Logo (JPG, PNG, SVG)</label>
                                                <input class="form-control" type="file" id="logo_file" name="logo_file" accept=".jpg, .jpeg, .png, .svg">
                                                <div class="form-text">
                                                    Current Logo: 
                                                    <?php if (!empty($site_settings['site_logo'])): ?>
                                                        <a href="../assets/images/<?php echo htmlspecialchars($site_settings['site_logo']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($site_settings['site_logo']); ?>
                                                        </a>
                                                        <br>
                                                        <img src="../assets/images/<?php echo htmlspecialchars($site_settings['site_logo']); ?>" style="max-height: 45px; margin-top: 8px; border: 1px solid #ddd; padding: 4px; border-radius: 6px;">
                                                    <?php else: ?>
                                                        None uploaded.
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label d-block">Logo Display</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_site_logo" name="use_site_logo" value="1" <?php echo (($site_settings['use_site_logo'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="use_site_logo">Display Image Logo</label>
                                                </div>
                                                <div class="form-text">
                                                    Disable to display text brand name instead of image logo.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3 border-bottom pb-2">Site Favicon</h6>
                                            <div class="mb-3">
                                                <label for="favicon_file" class="form-label">Upload Favicon (ICO, PNG, SVG)</label>
                                                <input class="form-control" type="file" id="favicon_file" name="favicon_file" accept=".ico, .png, .svg">
                                                <div class="form-text">
                                                    Current Favicon: 
                                                    <?php if (!empty($site_settings['site_favicon'])): ?>
                                                        <a href="../assets/images/<?php echo htmlspecialchars($site_settings['site_favicon']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars($site_settings['site_favicon']); ?>
                                                        </a>
                                                        <br>
                                                        <img src="../assets/images/<?php echo htmlspecialchars($site_settings['site_favicon']); ?>" style="width: 28px; height: 28px; margin-top: 8px; border: 1px solid #ddd; padding: 2px; border-radius: 4px;">
                                                    <?php else: ?>
                                                        None uploaded.
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 border-bottom pb-2 mt-4"><i class="bi bi-images me-2 text-primary"></i>Hero Slider Images</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <div class="card p-3 border shadow-sm h-100 bg-light">
                                                <label for="slider_bg_1_file" class="form-label fw-bold"><i class="bi bi-image me-1"></i> Slide 1 Image</label>
                                                <input class="form-control mb-2" type="file" id="slider_bg_1_file" name="slider_bg_1_file" accept=".jpg, .jpeg, .png, .webp, .svg">
                                                <div class="form-text">
                                                    <?php if (!empty($site_settings['slider_bg_1'])): ?>
                                                        <a href="../assets/images/<?php echo htmlspecialchars($site_settings['slider_bg_1']); ?>" target="_blank" class="d-block mb-1 text-truncate fw-semibold">
                                                            <?php echo htmlspecialchars($site_settings['slider_bg_1']); ?>
                                                        </a>
                                                        <img src="../assets/images/<?php echo htmlspecialchars($site_settings['slider_bg_1']); ?>" class="img-fluid rounded border shadow-sm mt-1" style="max-height: 90px; width: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary d-block mb-1">Default (slide1.jpg)</span>
                                                        <img src="../assets/images/slide1.jpg" class="img-fluid rounded border shadow-sm mt-1" style="max-height: 90px; width: 100%; object-fit: cover;" onerror="this.style.display='none';">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card p-3 border shadow-sm h-100 bg-light">
                                                <label for="slider_bg_2_file" class="form-label fw-bold"><i class="bi bi-image me-1"></i> Slide 2 Image</label>
                                                <input class="form-control mb-2" type="file" id="slider_bg_2_file" name="slider_bg_2_file" accept=".jpg, .jpeg, .png, .webp, .svg">
                                                <div class="form-text">
                                                    <?php if (!empty($site_settings['slider_bg_2'])): ?>
                                                        <a href="../assets/images/<?php echo htmlspecialchars($site_settings['slider_bg_2']); ?>" target="_blank" class="d-block mb-1 text-truncate fw-semibold">
                                                            <?php echo htmlspecialchars($site_settings['slider_bg_2']); ?>
                                                        </a>
                                                        <img src="../assets/images/<?php echo htmlspecialchars($site_settings['slider_bg_2']); ?>" class="img-fluid rounded border shadow-sm mt-1" style="max-height: 90px; width: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary d-block mb-1">Default (slide2.jpg)</span>
                                                        <img src="../assets/images/slide2.jpg" class="img-fluid rounded border shadow-sm mt-1" style="max-height: 90px; width: 100%; object-fit: cover;" onerror="this.style.display='none';">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="card p-3 border shadow-sm h-100 bg-light">
                                                <label for="slider_bg_3_file" class="form-label fw-bold"><i class="bi bi-image me-1"></i> Slide 3 Image</label>
                                                <input class="form-control mb-2" type="file" id="slider_bg_3_file" name="slider_bg_3_file" accept=".jpg, .jpeg, .png, .webp, .svg">
                                                <div class="form-text">
                                                    <?php if (!empty($site_settings['slider_bg_3'])): ?>
                                                        <a href="../assets/images/<?php echo htmlspecialchars($site_settings['slider_bg_3']); ?>" target="_blank" class="d-block mb-1 text-truncate fw-semibold">
                                                            <?php echo htmlspecialchars($site_settings['slider_bg_3']); ?>
                                                        </a>
                                                        <img src="../assets/images/<?php echo htmlspecialchars($site_settings['slider_bg_3']); ?>" class="img-fluid rounded border shadow-sm mt-1" style="max-height: 90px; width: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary d-block mb-1">Default (slide3.jpg)</span>
                                                        <img src="../assets/images/slide3.jpg" class="img-fluid rounded border shadow-sm mt-1" style="max-height: 90px; width: 100%; object-fit: cover;" onerror="this.style.display='none';">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Social Links</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="telegram_username" class="form-label"><i class="bi bi-telegram me-1"></i> Telegram Username</label>
                                                <input type="text" class="form-control" id="telegram_username" name="telegram_username" value="<?php echo htmlspecialchars($site_settings['telegram_username'] ?? ''); ?>" placeholder="e.g. @username">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_telegram" name="use_telegram" value="1" <?php echo (($site_settings['use_telegram'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_telegram">Enable Telegram</label>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="twitter_url" class="form-label"><i class="bi bi-twitter-x me-1"></i> Twitter / X URL</label>
                                                <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($site_settings['twitter_url'] ?? ''); ?>" placeholder="https://twitter.com/profile">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_twitter" name="use_twitter" value="1" <?php echo (($site_settings['use_twitter'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_twitter">Enable Twitter / X</label>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="youtube_url" class="form-label"><i class="bi bi-youtube me-1"></i> YouTube URL</label>
                                                <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($site_settings['youtube_url'] ?? ''); ?>" placeholder="https://youtube.com/@channel">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_youtube" name="use_youtube" value="1" <?php echo (($site_settings['use_youtube'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_youtube">Enable YouTube</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="whatsapp_number" class="form-label"><i class="bi bi-whatsapp me-1"></i> WhatsApp Number</label>
                                                <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="<?php echo htmlspecialchars($site_settings['whatsapp_number'] ?? ''); ?>" placeholder="e.g. +1234567890">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_whatsapp" name="use_whatsapp" value="1" <?php echo (($site_settings['use_whatsapp'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_whatsapp">Enable WhatsApp</label>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="facebook_url" class="form-label"><i class="bi bi-facebook me-1"></i> Facebook URL</label>
                                                <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($site_settings['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/page">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_facebook" name="use_facebook" value="1" <?php echo (($site_settings['use_facebook'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_facebook">Enable Facebook</label>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="instagram_url" class="form-label"><i class="bi bi-instagram me-1"></i> Instagram URL</label>
                                                <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($site_settings['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/profile">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_instagram" name="use_instagram" value="1" <?php echo (($site_settings['use_instagram'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_instagram">Enable Instagram</label>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="linkedin_url" class="form-label"><i class="bi bi-linkedin me-1"></i> LinkedIn URL</label>
                                                <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($site_settings['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/profile">
                                            </div>
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="use_linkedin" name="use_linkedin" value="1" <?php echo (($site_settings['use_linkedin'] ?? '0') === '1' ? 'checked' : ''); ?>>
                                                    <label class="form-check-label fw-bold" for="use_linkedin">Enable LinkedIn</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 border-top pt-3">
                                        <button type="submit" class="btn btn-crimson fw-bold px-4 py-2 shadow-sm">
                                            <i class="bi bi-check2-circle me-1"></i>Save Branding
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- TAB 4: PAYMENTS -->
                            <div class="tab-pane fade <?php echo ($active_tab === 'payments' ? 'show active' : ''); ?>" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                                <h5 class="fw-bold mb-3">Manage Payment Options</h5>
                                
                                <?php if ($payments_msg): ?>
                                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $payments_msg; ?></div>
                                <?php endif; ?>
                                <?php if ($payments_err): ?>
                                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $payments_err; ?></div>
                                <?php endif; ?>

                                <form action="settings.php" method="POST">
                                    <input type="hidden" name="update_payments" value="1">
                                    
                                    <h6 class="fw-bold mb-3 border-bottom pb-2 mt-4">Bank Transfer</h6>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-8">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input payment-toggle" type="checkbox" id="use_bank" name="use_bank" value="1" <?php echo (($site_settings['use_bank'] ?? '0') === '1' ? 'checked' : ''); ?> data-target="bank-inputs">
                                                <label class="form-check-label fw-bold" for="use_bank">Enable Bank Transfer</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="bank-inputs" class="input-group-area mb-4 border p-3 rounded" style="display: <?php echo (($site_settings['use_bank'] ?? '0') === '1' ? 'block' : 'none'); ?>">
                                        <p class="fw-bold text-muted border-bottom pb-2">Account Details</p>
                                        <div class="mb-3">
                                            <label for="bank_name" class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($site_settings['bank_name'] ?? ''); ?>" placeholder="e.g., Bank of America">
                                        </div>
                                        <div class="mb-3">
                                            <label for="bank_account_name" class="form-label">Account Name</label>
                                            <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" value="<?php echo htmlspecialchars($site_settings['bank_account_name'] ?? ''); ?>" placeholder="e.g., John Smith">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_sort_code" class="form-label">Sort Code / Routing No.</label>
                                            <input type="text" class="form-control" id="bank_sort_code" name="bank_sort_code" value="<?php echo htmlspecialchars($site_settings['bank_sort_code'] ?? ''); ?>" placeholder="e.g., 12-34-56 (UK) or 123456789 (US)">
                                            <small class="form-text text-muted">Branch / routing code (Sort Code, ABA, SWIFT).</small>
                                        </div>
                                        
                                        <div class="mb-0">
                                            <label for="bank_account_number" class="form-label">Account Number / IBAN</label>
                                            <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" value="<?php echo htmlspecialchars($site_settings['bank_account_number'] ?? ''); ?>" placeholder="e.g., 1234567890">
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 border-bottom pb-2 mt-4">Crypto Payments</h6>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-8">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input payment-toggle" type="checkbox" id="use_crypto" name="use_crypto" value="1" <?php echo (($site_settings['use_crypto'] ?? '0') === '1' ? 'checked' : ''); ?> data-target="crypto-inputs">
                                                <label class="form-check-label fw-bold" for="use_crypto">Enable Crypto Payments (LTC, ETH, USDT, BTC)</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="crypto-inputs" class="input-group-area mb-4 border p-3 rounded" style="display: <?php echo (($site_settings['use_crypto'] ?? '0') === '1' ? 'block' : 'none'); ?>">
                                        <p class="fw-bold text-muted border-bottom pb-2">Wallet Addresses</p>
                                        <div class="mb-3">
                                            <label for="crypto_ltc" class="form-label"><i class="bi bi-currency-bitcoin me-1"></i> Litecoin (LTC)</label>
                                            <input type="text" class="form-control" id="crypto_ltc" name="crypto_ltc" value="<?php echo htmlspecialchars($site_settings['crypto_ltc'] ?? ''); ?>" placeholder="Enter LTC address">
                                        </div>
                                        <div class="mb-3">
                                            <label for="crypto_eth" class="form-label"><i class="bi bi-currency-bitcoin me-1"></i> Ethereum (ETH)</label>
                                            <input type="text" class="form-control" id="crypto_eth" name="crypto_eth" value="<?php echo htmlspecialchars($site_settings['crypto_eth'] ?? ''); ?>" placeholder="Enter ETH address">
                                        </div>
                                        <div class="mb-3">
                                            <label for="crypto_usdt" class="form-label"><i class="bi bi-currency-dollar me-1"></i> Tether (USDT)</label>
                                            <input type="text" class="form-control" id="crypto_usdt" name="crypto_usdt" value="<?php echo htmlspecialchars($site_settings['crypto_usdt'] ?? ''); ?>" placeholder="Enter USDT address">
                                        </div>
                                        <div class="mb-0">
                                            <label for="crypto_btc" class="form-label"><i class="bi bi-currency-bitcoin me-1"></i> Bitcoin (BTC)</label>
                                            <input type="text" class="form-control" id="crypto_btc" name="crypto_btc" value="<?php echo htmlspecialchars($site_settings['crypto_btc'] ?? ''); ?>" placeholder="Enter BTC address">
                                        </div>
                                    </div>
                                    
                                    <h6 class="fw-bold mb-3 border-bottom pb-2 mt-4">Gift Card Payments</h6>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-8">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input payment-toggle" type="checkbox" id="use_giftcard" name="use_giftcard" value="1" <?php echo (($site_settings['use_giftcard'] ?? '0') === '1' ? 'checked' : ''); ?> data-target="giftcard-inputs">
                                                <label class="form-check-label fw-bold" for="use_giftcard">Enable Gift Cards</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="giftcard-inputs" class="input-group-area mb-4 border p-3 rounded" style="display: <?php echo (($site_settings['use_giftcard'] ?? '0') === '1' ? 'block' : 'none'); ?>">
                                        <div class="mb-0">
                                            <label for="giftcard_note" class="form-label">Gift Card Instructions</label>
                                            <textarea class="form-control" id="giftcard_note" name="giftcard_note" rows="3" placeholder="e.g., Contact support with your gift card claim code."><?php echo htmlspecialchars($site_settings['giftcard_note'] ?? ''); ?></textarea>
                                            <div class="form-text">
                                                Displayed to clients during gift card checkout.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 border-top pt-3">
                                        <button type="submit" class="btn btn-crimson fw-bold px-4 py-2 shadow-sm">
                                            <i class="bi bi-check2-circle me-1"></i>Save Payments
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- TAB 5: SMTP -->
                            <div class="tab-pane fade <?php echo ($active_tab === 'smtp' ? 'show active' : ''); ?>" id="smtp" role="tabpanel" aria-labelledby="smtp-tab">
                                <h5 class="fw-bold mb-3">Email & SMTP Setup</h5>
                                <p class="text-muted small mb-4">Configure your SMTP mail server for automated booking notifications and confirmation emails.</p>
                                
                                <?php if ($smtp_msg): ?>
                                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $smtp_msg; ?></div>
                                <?php endif; ?>
                                <?php if ($smtp_err): ?>
                                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $smtp_err; ?></div>
                                <?php endif; ?>

                                <form action="settings.php" method="POST">
                                    <input type="hidden" name="update_smtp" value="1">
                                    
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch card p-3 border-primary-subtle bg-primary-subtle bg-opacity-10">
                                                <div class="d-flex align-items-center">
                                                    <input class="form-check-input payment-toggle ms-0 me-3" type="checkbox" id="use_smtp" name="use_smtp" value="1" <?php echo (($site_settings['use_smtp'] ?? '0') === '1' ? 'checked' : ''); ?> data-target="smtp-inputs" style="width: 3rem; height: 1.5rem;">
                                                    <div>
                                                        <label class="form-check-label fw-bold text-primary" for="use_smtp">Enable SMTP Emails</label>
                                                        <div class="form-text text-muted">Send automated booking receipts and ticket confirmations.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="smtp-inputs" class="input-group-area mb-4 border p-4 rounded shadow-sm bg-light" style="display: <?php echo (($site_settings['use_smtp'] ?? '0') === '1' ? 'block' : 'none'); ?>">
                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-server me-2"></i>Mail Server Details</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label for="smtp_host" class="form-label">SMTP Host</label>
                                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($site_settings['smtp_host'] ?? ''); ?>" placeholder="e.g., mail.yourdomain.com">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="smtp_port" class="form-label">SMTP Port</label>
                                                <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($site_settings['smtp_port'] ?? '465'); ?>" placeholder="e.g., 465 or 587">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="smtp_username" class="form-label">SMTP Username</label>
                                                <input type="email" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($site_settings['smtp_username'] ?? ''); ?>" placeholder="info@yourdomain.com">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="smtp_password" class="form-label">SMTP Password</label>
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($site_settings['smtp_password'] ?? ''); ?>" placeholder="Your email password">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="smtp_from_email" class="form-label">Sender Email</label>
                                                <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" value="<?php echo htmlspecialchars($site_settings['smtp_from_email'] ?? ''); ?>" placeholder="no-reply@yourdomain.com">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="smtp_from_name" class="form-label">Sender Name</label>
                                                <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?php echo htmlspecialchars($site_settings['smtp_from_name'] ?? ''); ?>" placeholder="VIP Celebrity Booking">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="smtp_encryption" class="form-label">Encryption</label>
                                                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                                    <option value="ssl" <?php echo (($site_settings['smtp_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : ''); ?>>SSL (Port 465)</option>
                                                    <option value="tls" <?php echo (($site_settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''); ?>>TLS (Port 587)</option>
                                                    <option value="" <?php echo (($site_settings['smtp_encryption'] ?? '') === '' ? 'selected' : ''); ?>>None</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 border-top pt-3">
                                        <button type="submit" class="btn btn-crimson fw-bold px-4 py-2 shadow-sm">
                                            <i class="bi bi-check2-circle me-1"></i>Save SMTP
                                        </button>
                                    </div>
                                </form>

                                <hr class="my-4">

                                <div class="card border-warning-subtle bg-warning-subtle bg-opacity-10 p-4 rounded shadow-sm">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-send-check-fill me-2"></i>SMTP Test Connection</h6>
                                    <p class="text-muted small mb-3">Send a test email to verify your SMTP mail server configuration.</p>
                                    
                                    <?php if ($smtp_test_msg): ?>
                                        <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $smtp_test_msg; ?></div>
                                    <?php endif; ?>
                                    <?php if ($smtp_test_err): ?>
                                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $smtp_test_err; ?></div>
                                    <?php endif; ?>

                                    <form action="settings.php" method="POST">
                                        <input type="hidden" name="test_smtp" value="1">
                                        <div class="row align-items-end">
                                            <div class="col-md-8 mb-3 mb-md-0">
                                                <label for="test_email" class="form-label">Recipient Email</label>
                                                <input type="email" class="form-control" id="test_email" name="test_email" required placeholder="recipient@example.com" value="<?php echo htmlspecialchars($_POST['test_email'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-warning fw-bold w-100 py-2">
                                                    <i class="bi bi-send me-1"></i>Send Test
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <?php if ($smtp_test_log): ?>
                                        <div class="mt-4">
                                            <label class="form-label fw-bold text-dark">Connection Debug Log:</label>
                                            <pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap; word-break: break-all;"><?php echo htmlspecialchars($smtp_test_log); ?></pre>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <?php include "footer.php" ?>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyCurrencyPreset(selectEl) {
            if (!selectEl.value) return;
            try {
                const data = JSON.parse(selectEl.value);
                if (data.code) document.getElementById('currency_code').value = data.code;
                if (data.symbol) document.getElementById('currency_symbol').value = data.symbol;
                if (data.position) document.getElementById('currency_position').value = data.position;
            } catch(e) {
                console.error("Invalid currency preset data", e);
            }
        }

        const activeTabId = "<?php echo htmlspecialchars($active_tab); ?>";
        if (activeTabId) {
            const triggerEl = document.querySelector('button[data-bs-target="#' + activeTabId + '"]');
            if (triggerEl) {
                new bootstrap.Tab(triggerEl).show(); 
            }
        }
        
        // --- Payment Toggle Logic ---
        document.querySelectorAll('.payment-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const targetEl = document.getElementById(targetId);
                if (targetEl) {
                    targetEl.style.display = this.checked ? 'block' : 'none';
                }
            });
        });

        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
