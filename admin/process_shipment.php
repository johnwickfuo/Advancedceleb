<?php
//process_shipment.php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once '../config.php';
require_once '../get_setting.php';
require_once '../include/email_core_functions.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$upload_dir = '../uploads/shipment/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/**
 * Generates the HTML for the beautiful tracking email (Paid/COD).
 */
function get_tracking_email_body($shipment_data, $settings) {
    $payment_status_text = ($shipment_data['payment_status'] == 'Collect on Delivery') ? 'Collect on Delivery (Payment Due)' : 'Paid';
    $tracking_url = $settings['site_url'] . '/track.php?awb=' . urlencode($shipment_data['tracking_number']);
    
    $inner_html = "
    <h2 style='color: #6D0000; font-size: 22px; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #FFC107; padding-bottom: 5px; display: inline-block;'>Shipment Confirmed!</h2>
    <p style='margin-bottom: 25px;'>Hello,</p>
    <p style='margin-bottom: 25px;'>We are pleased to inform you that your shipment has been registered successfully. You can track your package in real-time using the tracking number below:</p>
    
    <div style='background-color: #f8fafb; border-left: 4px solid #B00000; padding: 15px 20px; border-radius: 4px; margin-bottom: 30px; text-align: center;'>
        <span style='color: #8a9ba8; font-size: 12px; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 5px;'>Tracking Number</span>
        <strong style='font-family: monospace; font-size: 26px; color: #2e3b4e; letter-spacing: 1px;'>{$shipment_data['tracking_number']}</strong>
    </div>

    <table width='100%' cellpadding='10' cellspacing='0' style='border-collapse: collapse; margin-bottom: 35px; border: 1px solid #eef1f3;'>
        <tr style='background-color: #f8fafb;'>
            <td style='font-weight: bold; color: #5c7080; border-bottom: 1px solid #eef1f3;'>Estimated Delivery</td>
            <td style='text-align: right; font-weight: bold; color: #2e3b4e; border-bottom: 1px solid #eef1f3;'>{$shipment_data['estimated_delivery']}</td>
        </tr>
        <tr>
            <td style='font-weight: bold; color: #5c7080; border-bottom: 1px solid #eef1f3;'>Payment Status</td>
            <td style='text-align: right; font-weight: bold; color: #B00000; border-bottom: 1px solid #eef1f3;'>{$payment_status_text}</td>
        </tr>
        <tr style='background-color: #f8fafb;'>
            <td style='font-weight: bold; color: #5c7080; border-bottom: 1px solid #eef1f3;'>Recipient Address</td>
            <td style='text-align: right; color: #2e3b4e; border-bottom: 1px solid #eef1f3;'>{$shipment_data['receiver_address']}</td>
        </tr>
        <tr>
            <td style='font-weight: bold; color: #5c7080;'>Current Status</td>
            <td style='text-align: right; font-weight: bold; color: #28a745;'>{$shipment_data['status']}</td>
        </tr>
    </table>

    <div style='text-align: center; margin-bottom: 15px;'>
        <a href='{$tracking_url}' target='_blank' style='display: inline-block; padding: 14px 35px; background: linear-gradient(135deg, #B00000 0%, #6D0000 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 6px 20px rgba(176,0,0,0.25);'>
            Track Your Package
        </a>
    </div>
    ";
    
    return get_premium_email_html("Shipment Confirmed - " . $shipment_data['tracking_number'], $inner_html, $settings);
}

/**
 * Generates the HTML for the beautiful payment invoice email (Unpaid).
 */
function get_invoice_email_body($shipment_data, $settings) {
    $invoice_amount = number_format((float)$shipment_data['amount'], 2);
    $invoice_currency = $shipment_data['currency'] ?? 'USD';
    $item_quantity = $shipment_data['quantity'] ?? 1;
    
    $currency_symbols = [
        'USD'=>'$','EUR'=>'€','GBP'=>'£','NGN'=>'₦','CAD'=>'C$','AUD'=>'A$','NZD'=>'NZ$','JPY'=>'¥','CNY'=>'¥','INR'=>'₹','ZAR'=>'R',
        'CHF'=>'CHF','SEK'=>'kr','NOK'=>'kr','DKK'=>'kr','RUB'=>'₽','BRL'=>'R$','MXN'=>'MX$','SAR'=>'﷼','AED'=>'د.إ','EGP'=>'E£',
        'KES'=>'KSh','GHS'=>'₵','UGX'=>'USh','TZS'=>'TSh','PKR'=>'₨','BDT'=>'৳','LKR'=>'Rs','THB'=>'฿','MYR'=>'RM','IDR'=>'Rp',
        'KRW'=>'₩','HKD'=>'HK$','SGD'=>'S$','TRY'=>'₺','PLN'=>'zł','CZK'=>'Kč','HUF'=>'Ft','RON'=>'lei','ILS'=>'₪','KWD'=>'KD',
        'QAR'=>'﷼','OMR'=>'﷼','BHD'=>'BD','JOD'=>'JD','MAD'=>'DH','DZD'=>'DA','TND'=>'DT','ETB'=>'Br','AOA'=>'Kz','MWK'=>'MK',
        'MZN'=>'MT','ZMW'=>'ZK','BWP'=>'P','NAD'=>'N$','XOF'=>'CFA','XAF'=>'FCFA','LRD'=>'L$','SLL'=>'Le','GMD'=>'D','CVE'=>'$'
    ];
    $currency_symbol = $currency_symbols[$invoice_currency] ?? $invoice_currency . ' ';
    $due_date = date('Y-m-d', strtotime('+7 days'));
    $payment_url = $settings['site_url'] . '/payment.php?invoice=' . urlencode($shipment_data['tracking_number']);

    $inner_html = "
    <h2 style='color: #6D0000; font-size: 22px; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #FFC107; padding-bottom: 5px; display: inline-block;'>Shipment Invoice</h2>
    <p style='margin-bottom: 25px;'>Hello,</p>
    <p style='margin-bottom: 25px;'>A shipping invoice has been generated for your shipment. Please find the summary of charges below:</p>
    
    <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom: 25px; border-bottom: 1px dashed #eef1f3; padding-bottom: 15px;'>
        <tr>
            <td style='vertical-align: top; width: 50%; font-size: 14px;'>
                <strong style='color: #5c7080; display: block; margin-bottom: 5px;'>Invoice For:</strong>
                <span style='color: #2e3b4e; font-weight: 600;'>{$shipment_data['receiver_name']}</span><br>
                <span style='color: #8a9ba8;'>{$shipment_data['receiver_address']}</span>
            </td>
            <td style='vertical-align: top; width: 50%; text-align: right; font-size: 14px;'>
                <strong style='color: #5c7080; display: block; margin-bottom: 5px;'>Invoice ID:</strong>
                <span style='color: #B00000; font-family: monospace; font-weight: bold; font-size: 16px;'>#{$shipment_data['tracking_number']}</span><br>
                <span style='color: #8a9ba8;'>Due Date: {$due_date}</span>
            </td>
        </tr>
    </table>

    <table width='100%' cellpadding='10' cellspacing='0' style='border-collapse: collapse; margin-bottom: 25px; border: 1px solid #eef1f3;'>
        <thead>
            <tr style='background-color: #f8fafb;'>
                <th style='text-align: left; color: #5c7080; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #eef1f3;'>Description</th>
                <th style='text-align: right; color: #5c7080; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #eef1f3;'>Qty</th>
                <th style='text-align: right; color: #5c7080; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #eef1f3;'>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style='font-size: 14px; border-bottom: 1px solid #eef1f3;'>Shipping charges for package #{$shipment_data['tracking_number']} ({$shipment_data['weight']} kg)</td>
                <td style='text-align: right; font-size: 14px; border-bottom: 1px solid #eef1f3;'>{$item_quantity}</td>
                <td style='text-align: right; font-size: 14px; font-weight: 600; border-bottom: 1px solid #eef1f3;'>{$currency_symbol}{$invoice_amount}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan='2' style='text-align: right; padding: 15px 10px 5px; font-weight: bold; color: #5c7080;'>Total Amount Due:</td>
                <td style='text-align: right; padding: 15px 10px 5px; font-weight: bold; font-size: 20px; color: #B00000;'>{$currency_symbol}{$invoice_amount}</td>
            </tr>
        </tfoot>
    </table>

    <p style='text-align: center; margin-bottom: 30px; font-size: 15px;'>
        Your shipment is currently pending payment confirmation. Please click below to complete your payment securely.
    </p>

    <div style='text-align: center; margin-bottom: 15px;'>
        <a href='{$payment_url}' target='_blank' style='display: inline-block; padding: 14px 35px; background: linear-gradient(135deg, #B00000 0%, #6D0000 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 6px 20px rgba(176,0,0,0.25);'>
            Pay Invoice Now
        </a>
    </div>
    ";
    
    return get_premium_email_html("Shipping Invoice - " . $shipment_data['tracking_number'], $inner_html, $settings);
}

// The send_email_notification function is now loaded from include/email_core_functions.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tracking_number = trim($_POST['tracking_number'] ?? '');
    $sender_name = trim($_POST['sender_name'] ?? '');
    $sender_phone = trim($_POST['sender_phone'] ?? '');
    $sender_address = trim($_POST['sender_address'] ?? '');
    $receiver_name = trim($_POST['receiver_name'] ?? '');
    $receiver_email = trim($_POST['receiver_email'] ?? '');
    $receiver_phone = trim($_POST['receiver_phone'] ?? '');
    $receiver_address = trim($_POST['receiver_address'] ?? '');
    $service_type = trim($_POST['service_type'] ?? '');
    $weight = (isset($_POST['weight']) && trim($_POST['weight']) !== '') ? trim($_POST['weight']) : NULL;
    $dimensions = trim($_POST['dimensions'] ?? '');
    $package_description = trim($_POST['package_description'] ?? '');
    $estimated_delivery = trim($_POST['estimated_delivery'] ?? '');
    $payment_status = trim($_POST['payment_status'] ?? 'Unpaid');
    $quantity = (isset($_POST['quantity']) && trim($_POST['quantity']) !== '') ? (int)trim($_POST['quantity']) : 1;
    $amount = (isset($_POST['amount']) && trim($_POST['amount']) !== '') ? trim($_POST['amount']) : NULL;
    $currency = (isset($_POST['currency']) && trim($_POST['currency']) !== '') ? trim($_POST['currency']) : NULL;
    $status = trim($_POST['status'] ?? $_POST['initial_status'] ?? 'Information Received');
    $handling_instructions = trim($_POST['handling_instructions'] ?? '');

    $uploaded_paths = [];
    $files_uploaded = 0;

    if (isset($_FILES['package_images']) && is_array($_FILES['package_images']['name'])) {
        $file_count = count($_FILES['package_images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['package_images']['error'][$i] !== UPLOAD_ERR_OK) { continue; }
            $file_name = $_FILES['package_images']['name'][$i];
            $file_tmp_name = $_FILES['package_images']['tmp_name'][$i];
            $file_type = $_FILES['package_images']['type'][$i];

            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($file_type, $allowed_types)) { continue; }

            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed_extensions)) { continue; }

            $new_file_name = $tracking_number . '_' . uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp_name, $target_file)) {
                $files_uploaded++;
                $db_path = str_replace('../', '', $target_file);
                $uploaded_paths[] = $db_path;
            }
        }
    }
    $package_images_csv = !empty($uploaded_paths) ? implode(',', $uploaded_paths) : NULL;

    if (isset($_POST['update_shipment'])) {
        $shipment_id = $_POST['shipment_id'];
        try {
            $pdo->beginTransaction();
            $sql = "UPDATE shipments SET 
                    tracking_number = ?,
                    sender_name = ?, sender_phone = ?, sender_address = ?, 
                    receiver_name = ?, receiver_email = ?, receiver_phone = ?, receiver_address = ?, 
                    service_type = ?, estimated_delivery = ?, payment_status = ?, 
                    amount = ?, currency = ?, quantity = ?, weight = ?, dimensions = ?, 
                    package_description = ?, handling_instructions = ?, status = ?" . ($package_images_csv ? ", package_images = ?" : "") . "
                    WHERE id = ?";
            $params = [$tracking_number, $sender_name, $sender_phone, $sender_address, $receiver_name, $receiver_email, $receiver_phone, $receiver_address, $service_type, $estimated_delivery, $payment_status, $amount, $currency, $quantity, $weight, $dimensions, $package_description, $handling_instructions, $status];
            if ($package_images_csv) { $params[] = $package_images_csv; }
            $params[] = $shipment_id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Update tracking number on payment proofs if any exist
            $sql_proofs = "UPDATE payment_proofs SET tracking_number = ? WHERE shipment_id = ?";
            $stmt_proofs = $pdo->prepare($sql_proofs);
            $stmt_proofs->execute([$tracking_number, $shipment_id]);

            // 2. Insert into Tracking History so it shows on the user tracking page
            $update_location = trim($_POST['update_location'] ?? '');
            if (empty($update_location)) {
                $update_location = $receiver_address; // Fallback to destination if no current location provided
            }
            
            $update_datetime = trim($_POST['update_datetime'] ?? '');
            if (!empty($update_datetime)) {
                $formatted_datetime = date('Y-m-d H:i:s', strtotime($update_datetime));
                $sql_history = "INSERT INTO tracking_history (shipment_id, location, status_update, updated_at) VALUES (?, ?, ?, ?)";
                $stmt_history = $pdo->prepare($sql_history);
                $stmt_history->execute([$shipment_id, $update_location, $status, $formatted_datetime]);
            } else {
                $sql_history = "INSERT INTO tracking_history (shipment_id, location, status_update) VALUES (?, ?, ?)";
                $stmt_history = $pdo->prepare($sql_history);
                $stmt_history->execute([$shipment_id, $update_location, $status]);
            }

            $pdo->commit();
            $_SESSION['message'] = "Shipment <strong>#$tracking_number</strong> has been updated successfully!";
            $_SESSION['message_type'] = 'success';
            header("location: shipment_manager.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = "Critical error during update: " . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header("location: edit_shipment.php?id=$shipment_id");
            exit();
        }
    } else {
        // CREATE NEW
        $pdo->beginTransaction();
        try {
            $sql1 = "INSERT INTO shipments (tracking_number, sender_name, sender_address, sender_phone, receiver_name, receiver_address, receiver_email, receiver_phone, package_description, handling_instructions, weight, dimensions, service_type, quantity, estimated_delivery, status, package_images, payment_status, amount, currency) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([$tracking_number, $sender_name, $sender_address, $sender_phone, $receiver_name, $receiver_address, $receiver_email, $receiver_phone, $package_description, $handling_instructions, $weight, $dimensions, $service_type, $quantity, $estimated_delivery, $status, $package_images_csv, $payment_status, $amount, $currency]);
            $shipment_id = $pdo->lastInsertId();
            
            $sql2 = "INSERT INTO tracking_history (shipment_id, location, status_update) VALUES (?, ?, ?)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([$shipment_id, $sender_address, $status]);
            $pdo->commit();
            
            $site_settings = get_site_settings($pdo);
            if (!empty($receiver_email)) {
                $shipment_data = [
                    'tracking_number' => $tracking_number,
                    'receiver_name' => $receiver_name,
                    'receiver_address' => $receiver_address,
                    'status' => $status,
                    'estimated_delivery' => date('l, M d Y \a\t h:i A', strtotime($estimated_delivery)),
                    'payment_status' => $payment_status,
                    'amount' => $amount,
                    'currency' => $currency,
                    'quantity' => $quantity,
                    'weight' => $weight
                ];
                $smtp_config_missing = false;
                if ($payment_status == 'Unpaid' || $payment_status == 'Collect on Delivery') {
                    $html_body = get_invoice_email_body($shipment_data, $site_settings);
                    $subject = "Invoice for Shipment #" . $tracking_number;
                } else {
                    $html_body = get_tracking_email_body($shipment_data, $site_settings);
                    $subject = "Shipment Confirmed #" . $tracking_number;
                }
                send_email_notification($site_settings, $receiver_email, $subject, $html_body, $smtp_config_missing);
            }

            $_SESSION['message'] = "Shipment created successfully!";
            $_SESSION['message_type'] = 'success';
            header("location: create_shipment.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header("location: create_shipment.php");
            exit();
        }
    }
}
?>
