<?php
// This file centralizes the PHPMailer setup and the main email sending logic.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../admin/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../admin/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../admin/vendor/PHPMailer/src/SMTP.php';

/**
 * Wraps content in a premium, responsive HTML email template.
 */
function get_premium_email_html($subject, $content_html, $settings) {
    $logo_url = !empty($settings['site_logo']) ? $settings['site_url'] . '/assets/images/' . $settings['site_logo'] : '';
    $site_title = htmlspecialchars($settings['site_title'] ?? 'VIP Celebrity Booking');
    $site_url = htmlspecialchars($settings['site_url'] ?? '#');
    
    // Logo element
    $logo_html = '';
    if (!empty($logo_url)) {
        $logo_html = "<img src='{$logo_url}' alt='{$site_title} Logo' style='max-height: 45px; border: 0; outline: none; text-decoration: none;'>";
    } else {
        $logo_html = "<span style='color: #ffffff; font-size: 22px; font-weight: bold; letter-spacing: 1px;'>{$site_title}</span>";
    }

    $year = date('Y');

    return "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>{$subject}</title>
        <style type='text/css'>
        body { margin: 0; padding: 0; background-color: #f7f9fa; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        img { -ms-interpolation-mode: bicubic; }
        a { text-decoration: none; color: #e63946; }
        
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .content-padding { padding: 30px 20px !important; }
        }
    </style>
</head>
<body style='margin: 0; padding: 0; background-color: #f7f9fa;'>
    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f7f9fa;'>
        <tr>
            <td align='center' style='padding: 40px 10px;'>
                <table border='0' cellpadding='0' cellspacing='0' class='email-container' width='600' style='background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.05); border: 1px solid #eef1f3;'>
                    <!-- Header -->
                    <tr>
                        <td align='center' style='background: linear-gradient(135deg, #e63946 0%, #c1121f 100%); padding: 35px 20px;'>
                            {$logo_html}
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td class='content-padding' style='padding: 45px 40px; color: #333333; font-size: 16px; line-height: 1.7;'>
                            {$content_html}
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align='center' style='background-color: #f8fafb; padding: 30px 20px; border-top: 1px solid #eef1f3; color: #6c757d; font-size: 13px;'>
                            <p style='margin: 0 0 10px 0; font-weight: 600; color: #495057;'>&copy; {$year} {$site_title}. All rights reserved.</p>
                            <p style='margin: 0 0 20px 0;'>This is an automated notification. Replies to this email are routed to our support team.</p>
                            <table border='0' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td style='border-radius: 6px; background-color: #eef1f3; padding: 8px 18px;'>
                                        <a href='{$site_url}' target='_blank' style='color: #495057; font-size: 12px; font-weight: bold;'>Visit Our Website</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";
}

function send_email_notification($settings, $recipient_email, $subject, $html_body, &$smtp_config_missing, $attachments = []) {
    @set_time_limit(30);
    $smtp_config_missing = false;

    // Check if SMTP is active
    $smtp_active = (!empty($settings['use_smtp']) && $settings['use_smtp'] === '1' && !empty($settings['smtp_host']));

    if (!$smtp_active) {
        // If SMTP is turned off from admin, we skip sending email entirely and return true.
        // This prevents hanging on PHP mail() and ensures the user can still book / submit forms.
        $smtp_config_missing = true;
        return true;
    }

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 12; // Set connection timeout to 12 seconds for reliable delivery
    if ($mail->getSMTPInstance()) {
        $mail->getSMTPInstance()->Timeout = 12;
        $mail->getSMTPInstance()->Timelimit = 12;
    }

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // 1. Resolve From Email/Name for DMARC alignment
    // To satisfy SPF/DMARC, the From address should match the sending SMTP domain.
    // If using SMTP, we enforce the From address to be the authenticated SMTP username,
    // and route user replies via Reply-To.
    $from_name = !empty($settings['smtp_from_name']) ? $settings['smtp_from_name'] : ($settings['site_title'] ?? 'VIP Celebrity Booking');
    $reply_to_email = !empty($settings['smtp_from_email']) ? $settings['smtp_from_email'] : '';
    $from_email = $settings['smtp_username'];

    try {
        $mail->isSMTP();
        $mail->Host       = $settings['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_username'];
        $mail->Password   = $settings['smtp_password'];
        
        $encryption = strtolower($settings['smtp_encryption'] ?? '');
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPAutoTLS = false;
            $mail->SMTPSecure = '';
        }
        $mail->Port = !empty($settings['smtp_port']) ? (int)$settings['smtp_port'] : 465;

        // Set Sender and Recipients
        $mail->setFrom($from_email, $from_name);
        
        // Add Reply-To if it's configured and different from the From email
        if (!empty($reply_to_email) && strtolower($reply_to_email) !== strtolower($from_email)) {
            $mail->addReplyTo($reply_to_email, $from_name);
        }

        $mail->addAddress($recipient_email);

        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $att) {
                if (isset($att['path']) && isset($att['name'])) {
                    $mail->addAttachment($att['path'], $att['name']);
                }
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $premium_html_body = get_premium_email_html($subject, $html_body, $settings);
        $mail->Body    = $premium_html_body;

        // Generate clean text-mode alternative body (important for spam filters)
        $text_body = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>'], "\n", $html_body));
        $text_body = html_entity_decode($text_body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text_body = preg_replace("/\n{3,}/", "\n\n", $text_body);
        $mail->AltBody = trim($text_body);

        // Spam prevention headers
        $mail->addCustomHeader('X-Priority', '3');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log("Primary SMTP Mailer Error: " . ($mail->ErrorInfo ?? '') . ". Details: " . $e->getMessage());
        return false;
    }
}
?>