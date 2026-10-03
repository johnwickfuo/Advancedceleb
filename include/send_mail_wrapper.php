<?php
// NOTE: This file requires get_payment_status_email_body() to be defined
// and the send_email_notification() (from your process_shipment.php) to be available.

/**
 * Wrapper function to send the payment proof status email.
 * @param array $site_settings Global site settings array.
 * @param string $recipientEmail User's email address.
 * @param string $status 'Approved' or 'Declined'.
 * @param array $shipmentData All required data.
 * @param bool &$smtp_config_missing Flag passed by reference (for status message).
 * @return bool True on success, false on failure.
 */
function sendPaymentProofStatusEmail($site_settings, $recipientEmail, $status, $shipmentData, &$smtp_config_missing) {
    
    if (empty($recipientEmail)) {
        // No email, return success to prevent crashing, but log the issue
        error_log("Attempted to send payment status email, but recipient email was empty.");
        return true; 
    }
    
    // 1. Generate the subject
    $subject = "Payment Proof Status Update: " . $status . " for Tracking # " . ($shipmentData['tracking_number'] ?? 'N/A');
    
    // 2. Generate the body using the new function
    $html_body = get_payment_status_email_body($shipmentData, $site_settings, $status);
    
    if (empty($html_body)) {
        error_log("Failed to generate payment status email body. Sending failed.");
        return false;
    }

    // 3. Call the existing, robust notification function
    return send_email_notification($site_settings, $recipientEmail, $subject, $html_body, $smtp_config_missing);
}

/**
 * Alias function with optional smtp_config_missing parameter
 */
if (!function_exists('send_payment_status_email')) {
    function send_payment_status_email($site_settings, $recipientEmail, $status, $shipmentData, &$smtp_config_missing = false) {
        return sendPaymentProofStatusEmail($site_settings, $recipientEmail, $status, $shipmentData, $smtp_config_missing);
    }
}
?>