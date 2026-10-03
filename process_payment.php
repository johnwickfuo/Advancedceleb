<?php
//process_payment.php
session_start();

require_once('config.php');

define('UPLOAD_DIR', 'uploads/payment_proofs/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Ensure uploads/payment_proofs/ exists
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0777, true);
}

// Auto-ensure tracking_number column exists in payment_proofs table
try {
    $col_check = $pdo->query("SHOW COLUMNS FROM payment_proofs LIKE 'tracking_number'")->fetch();
    if (!$col_check) {
        $pdo->exec("ALTER TABLE payment_proofs ADD COLUMN tracking_number VARCHAR(100) DEFAULT NULL AFTER booking_id");
    }
} catch (Exception $e) {
    // Ignore if already exists or permission issues
}

function handle_upload(array $file, string $method) {
    global $pdo;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    
    $allowed_mimes = ['image/jpeg', 'image/png', 'application/pdf'];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
    if ($method === 'gc_front' || $method === 'gc_back') {
        
        $allowed_mimes = ['image/jpeg', 'image/png'];
        $allowed_exts = ['jpg', 'jpeg', 'png'];
    }

    if (!in_array($mime_type, $allowed_mimes)) {
        return false;
    }

    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) {
        return false;
    }

    $filename = $method . '_' . md5($file['name']) . '_' . uniqid() . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;
    }

    return false;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['payment_method'])) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Invalid Request',
        'text' => 'The payment form was submitted incorrectly.'
    ];
    header('Location: index.php');
    exit;
}

$method = $_POST['payment_method'];

try {
    // 1. EXTRACT BOOKING DETAILS
    $booking_id = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT);
    $tracking_number = filter_var($_POST['tracking_number'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (!$booking_id || empty($tracking_number)) {
        throw new Exception('Missing or invalid booking identification fields.');
    }
    
    // Confirm that the posted booking ID really belongs to the reference on the payment form.
    $booking_check = $pdo->prepare("SELECT booking_reference, booking_type, amount, status FROM bookings WHERE id = ? LIMIT 1");
    $booking_check->execute([$booking_id]);
    $linked_booking = $booking_check->fetch(PDO::FETCH_ASSOC);
    if (!$linked_booking || !hash_equals((string)$linked_booking['booking_reference'], (string)$tracking_number)) {
        throw new Exception('Booking reference does not match the submitted payment.');
    }
    if ($linked_booking['status'] === 'Cancelled') {
        throw new Exception('Payments cannot be submitted for cancelled requests.');
    }

    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    if ($amount === false || $amount <= 0) {
        throw new Exception('Invalid payment amount specified.');
    }
    if (($linked_booking['booking_type'] ?? 'event') === 'cameo'
        && abs((float)$linked_booking['amount'] - $amount) >= 0.01) {
        throw new Exception('The cameo payment amount must match the displayed video price.');
    }

    // ===============================================
    //           *** DUPLICATE SUBMISSION CHECK ***
    // Checks if a proof is already waiting for review or approved (NOT Declined)
    // ===============================================
    $check_query = "SELECT COUNT(*) FROM payment_proofs 
                    WHERE booking_id = :booking_id 
                    AND proof_status IN ('Pending Review', 'Approved')";
    $stmt = $pdo->prepare($check_query);
    $stmt->bindParam(':booking_id', $booking_id);
    $stmt->execute();
    $proof_count = $stmt->fetchColumn();

    if ($proof_count > 0) {
        // Blocked because a non-declined proof already exists.
        $_SESSION['alert'] = [
            'type' => 'warning',
            'title' => 'Submission Pending 🔒',
            'text' => 'A payment proof for this booking is already being reviewed or has been approved. Please wait for an update.'
        ];
        header('Location: payment.php?ref=' . urlencode($tracking_number));
        exit; // Stop script execution
    }
    
    // ===============================================
    //        *** END DUPLICATE CHECK ***
    // ===============================================

    // 2. INSERT INTO payment_proofs TABLE (Updated to include proof_status)
    $insert_query = "
        INSERT INTO payment_proofs
        (booking_id, tracking_number, payment_method, amount, proof_status,
         bank_proof_path, crypto_currency, crypto_amount_raw, crypto_proof_path,
         giftcard_value_usd, giftcard_proof_front_path, giftcard_proof_back_path)
        VALUES
        (:booking_id, :tracking_number, :method, :amount, :proof_status,
         :bank_path, :crypto_curr, :crypto_raw, :crypto_path,
         :gc_value, :gc_front, :gc_back)
    ";

    $params = [
        ':booking_id' => $booking_id,
        ':tracking_number' => $tracking_number,
        ':method' => $method,
        ':amount' => $amount,
        ':proof_status' => 'Pending Review', // Set initial status
        ':bank_path' => null,
        ':crypto_curr' => null,
        ':crypto_raw' => null,
        ':crypto_path' => null,
        ':gc_value' => null,
        ':gc_front' => null,
        ':gc_back' => null
    ];

    switch ($method) {
        case 'bank':
            if (empty($_FILES['payment_proof']['name'])) {
                throw new Exception('Bank proof file is required.');
            }
            $bank_path = handle_upload($_FILES['payment_proof'], 'bank');
            if (!$bank_path) {
                throw new Exception('Failed to process bank proof file.');
            }
            $params[':bank_path'] = $bank_path;
            break;

        case 'crypto':
            if (empty($_FILES['payment_proof']['name'])) {
                throw new Exception('Crypto transaction proof is required.');
            }
            $crypto_path = handle_upload($_FILES['payment_proof'], 'crypto');
            $currency = filter_var($_POST['currency'], FILTER_SANITIZE_SPECIAL_CHARS);
            $amount_raw = filter_var($_POST['amount'], FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$crypto_path) {
                throw new Exception('Failed to process crypto proof file.');
            }
            if (empty($currency) || !in_array($currency, ['btc', 'eth', 'ltc', 'usdt'])) {
                throw new Exception('Invalid cryptocurrency selected.');
            }

            $params[':crypto_curr'] = $currency;
            $params[':crypto_raw'] = $amount_raw;
            $params[':crypto_path'] = $crypto_path;
            break;

        case 'giftcard':
            if (empty($_FILES['proof_front']['name']) || empty($_FILES['proof_back']['name'])) {
                throw new Exception('Both front and back gift card proofs are required.');
            }
            $front_path = handle_upload($_FILES['proof_front'], 'gc_front');
            $back_path = handle_upload($_FILES['proof_back'], 'gc_back');

            if (!$front_path || !$back_path) {
                throw new Exception('Failed to process one or both gift card proofs.');
            }

            $params[':gc_value'] = $amount;
            $params[':gc_front'] = $front_path;
            $params[':gc_back'] = $back_path;
            break;

        default:
            throw new Exception('Invalid payment method selected.');
    }
    
    // Execute INSERT for payment_proofs
    $stmt = $pdo->prepare($insert_query);
    $stmt->execute($params);

    $_SESSION['alert'] = [
        'type' => 'success',
        'title' => 'Proof Submitted! 🎉',
        'text' => 'Your payment proof has been successfully submitted and is pending administrative review.',
        'redirect' => 'index.php',
        'confirmButtonText' => 'OK'
    ];
    
    // Redirect back to the payment page for this booking (modal will popup and redirect to home on OK)
    header('Location: payment.php?ref=' . urlencode($tracking_number));
    exit;

} catch (Exception $e) {
    error_log("Payment Processing Error (" . $method . "): " . $e->getMessage());
    $_SESSION['alert'] = [
        'type' => 'error',
        'title' => 'Submission Failed 😢',
        'text' => 'Error: ' . $e->getMessage()
    ];
    if (!empty($tracking_number)) {
        header('Location: payment.php?ref=' . urlencode($tracking_number));
    } else {
        header('Location: index.php');
    }
    exit;
}
?>