<?php
// Set a specific response status code for form processing files
http_response_code(200);

// 1. Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: quote.php?status=error_access");
    exit();
}

// 2. Define the recipient email address for quote requests
require_once 'get_setting.php';
$to_email = $site_settings['contact_email'] ?? "booking@vipcelebrity.com";
$subject_prefix = "[QUOTE REQUEST] ";
$redirect_success = "quote.php?status=success";
$redirect_error = "quote.php?status=error";

// 3. Collect and Sanitize input data
$service_type        = htmlspecialchars(trim($_POST['service_type'] ?? ''));
$origin_country      = htmlspecialchars(trim($_POST['origin_country'] ?? ''));
$destination_country = htmlspecialchars(trim($_POST['destination_country'] ?? ''));
$weight              = htmlspecialchars(trim($_POST['weight'] ?? ''));
$length              = htmlspecialchars(trim($_POST['length'] ?? ''));
$width               = htmlspecialchars(trim($_POST['width'] ?? ''));
$height              = htmlspecialchars(trim($_POST['height'] ?? ''));

// 4. Basic Validation
if (empty($service_type) || empty($origin_country) || empty($destination_country) || !is_numeric($weight) || $weight <= 0) {
    header("Location: " . $redirect_error . "&msg=validation_failed");
    exit();
}

// --- Placeholder for Actual Quote Logic ---
// NOTE: In a real-world scenario, you would integrate a logistics API 
// (like FedEx, DHL, or a 3rd party TMS) here to calculate an actual price based on the inputs.
$estimated_price = "Awaiting Manual Review"; // Default placeholder
$transit_time = "3-7 Days"; // Default placeholder

// Example of simple logic (Highly simplified for demonstration):
if ($service_type === 'express' && $weight <= 30) {
    $estimated_price = "Starts from " . format_currency(75);
    $transit_time = "1-3 Days";
} elseif ($service_type === 'freight' && $weight > 100) {
    $estimated_price = "A sales rep will contact you.";
    $transit_time = "5-15 Days";
}
// ------------------------------------------

// 5. Construct the Email Body (for sales review)
$email_subject = $subject_prefix . $service_type . " Quote Request";
$email_body_html = "
    <h2 style='color: #e63946; margin-top: 0;'>New Quote Request</h2>
    <p>A new quote request has been submitted.</p>
    <table cellpadding='10' cellspacing='0' width='100%' style='background-color: #f8fafb; border: 1px solid #eef1f3; border-radius: 8px; margin-top: 20px;'>
        <tr><td width='150'><strong>Service Type:</strong></td><td>" . strtoupper($service_type) . "</td></tr>
        <tr><td><strong>Route:</strong></td><td>{$origin_country} to {$destination_country}</td></tr>
        <tr><td><strong>Weight:</strong></td><td>{$weight} kg</td></tr>
        <tr><td><strong>Dimensions:</strong></td><td>{$length} x {$width} x {$height} cm</td></tr>
        <tr><td colspan='2' style='border-top: 1px solid #eef1f3; padding-top: 15px;'><strong>--- Estimated Calculation (Internal Use) ---</strong></td></tr>
        <tr><td><strong>Estimated Price:</strong></td><td>{$estimated_price}</td></tr>
        <tr><td><strong>Transit Time:</strong></td><td>{$transit_time}</td></tr>
    </table>
";

// 6. Send the Email
require_once 'include/email_core_functions.php';
$smtp_config_missing = false;

if (send_email_notification($site_settings, $to_email, $email_subject, $email_body_html, $smtp_config_missing)) {
    header("Location: " . $redirect_success);
} else {
    header("Location: " . $redirect_error . "&msg=mail_failed");
}
exit();
?>