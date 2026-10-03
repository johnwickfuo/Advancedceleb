<?php
/**
 * Generates the HTML body for the Payment Proof Status email.
 * @param array $shipmentData Details about the proof/shipment.
 * @param array $settings Site configuration (to get site_url, site_name, etc.).
 * @param string $status 'Approved' or 'Declined'.
 * @return string The completed HTML email body.
 */
function get_payment_status_email_body($shipmentData, $settings, $status) {
    
    // 1. Define placeholders and corresponding values
    $tracking_number = htmlspecialchars($shipmentData['tracking_number'] ?? 'N/A');
    $proof_id = htmlspecialchars($shipmentData['proof_id'] ?? 'N/A');
    $amount_usd = '$' . number_format($shipmentData['amount'] ?? 0, 2);
    $payment_method = htmlspecialchars(ucfirst($shipmentData['payment_method'] ?? 'N/A'));
    $user_id = htmlspecialchars($shipmentData['user_id'] ?? 'N/A');
    $user_name = htmlspecialchars($shipmentData['user_name'] ?? 'Client'); 

    // Retrieve settings from the array, matching the process_shipment.php style
    $company_name = $settings['site_title'] ?? 'Your Company Name';
    $base_url = $settings['site_url'] ?? 'http://default-domain.com';
    
    $status_text = $status === 'Approved' ? 'APPROVED' : 'DECLINED';
    $status_class = $status === 'Approved' ? 'status-approved' : 'status-declined';
    $message_body = "";
    
    if ($status === 'Approved') {
        $message_body = "Good news! Your payment proof has been <strong>approved</strong>. Your VIP booking is now marked as <strong>PAID</strong>. Our VIP concierge team has secured your reservation and will contact you with all access and itinerary details.";
    } else { // Declined
        $message_body = "Attention: Your payment proof was <strong>declined</strong>. This may occur if the receipt was unreadable, the amount did not match, or incorrect details were provided. Please visit your booking page to <strong>resubmit a valid payment proof</strong>.";
    }

    $shipment_link = $base_url . "/payment.php?ref=" . urlencode($tracking_number);
    
    // 2. Load the HTML template content
    // __DIR__ ensures the path is relative to the current file (this file)
    $template_file = __DIR__ . '/../templates/email_payment_status.html'; 
    if (!file_exists($template_file)) {
        error_log("Email template file not found: " . $template_file);
        return "";
    }
    $template = file_get_contents($template_file);

    // 3. Replace placeholders
    $replacements = [
        '**[USER_NAME]**' => $user_name,
        '**[USER_ID]**' => $user_id,
        '**[TRACKING_NUMBER]**' => $tracking_number,
        '**[PROOF_ID]**' => $proof_id,
        '**[AMOUNT_USD]**' => $amount_usd,
        '**[PAYMENT_METHOD]**' => $payment_method,
        '**[STATUS_CLASS]**' => $status_class,
        '**[STATUS_TEXT]**' => $status_text,
        '**[MESSAGE_BODY]**' => $message_body,
        '**[SHIPMENT_LINK]**' => $shipment_link,
        '[YEAR]' => date('Y'),
        '[COMPANY_NAME]' => $company_name, 
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $template);
}
?>