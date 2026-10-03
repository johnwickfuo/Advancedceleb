<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

http_response_code(200);

// 1. Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.php");
    exit();
}

require_once 'config.php';
require_once 'get_setting.php';
require_once 'include/email_core_functions.php';

// 2. Define the recipient email address for messages
$to_email = $site_settings['contact_email'] ?? "booking@vipcelebrity.com";
$subject_prefix = "[CONTACT INQUIRY] ";

// 3. Collect and Sanitize input data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// 4. Basic Validation
if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['alert'] = [
        'type' => 'warning',
        'title' => 'Incomplete Details',
        'text' => 'Please fill in all required fields with a valid email address.'
    ];
    header("Location: contact.php");
    exit();
}

// 5. Construct the Email Body (HTML)
$email_subject = $subject_prefix . $subject;
$email_body_html = "
    <h2 style='color: #b00000; margin-top: 0;'>New Contact Message Received</h2>
    <p>You have received a new inquiry from the website contact form.</p>
    <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px;'>
        <tr><td width='120'><strong>Full Name:</strong></td><td>" . htmlspecialchars($name) . "</td></tr>
        <tr><td><strong>Email Address:</strong></td><td>" . htmlspecialchars($email) . "</td></tr>
        <tr><td><strong>Subject:</strong></td><td>" . htmlspecialchars($subject) . "</td></tr>
        <tr><td colspan='2' style='border-top: 1px solid #eef1f3; padding-top: 15px;'><strong>Message:</strong><br><br>" . nl2br(htmlspecialchars($message)) . "</td></tr>
    </table>
";

// 6. Save to Database
$saved_in_db = false;
try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);
    $saved_in_db = true;
} catch (PDOException $e) {
    // Database save failed
    $saved_in_db = false;
}

// 7. Send Email Notification
$smtp_config_missing = false;
send_email_notification($site_settings, $to_email, $email_subject, $email_body_html, $smtp_config_missing);

// 8. Set popup alert message
if ($saved_in_db) {
    $_SESSION['alert'] = [
        'type' => 'success',
        'title' => 'Message Submitted Successfully! 🎉',
        'text' => 'Thank you for contacting us. Your message has been submitted and is currently under review by our VIP concierge team. We will get back to you shortly.'
    ];
} else {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Submission Failed',
        'text' => 'We could not submit your message at this moment. Please try again or contact us directly.'
    ];
}

header("Location: contact.php");
exit();