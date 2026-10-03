<?php
session_start();
require_once 'config.php'; 

$upload_dir = 'uploads/id_proofs/';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: enter_giveaway.php");
    exit();
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function handle_id_upload(string $file_key, string $upload_dir, string $entry_uuid) {
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload failed for {$file_key}: Error code " . ($_FILES[$file_key]['error'] ?? 'N/A'));
    }

    $file = $_FILES[$file_key];
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type for {$file_key}. Only JPG, PNG, and PDF are allowed.");
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        throw new Exception("Invalid file extension for {$file_key}. Only JPG, JPEG, PNG, and PDF are allowed.");
    }

    if ($file['size'] > $max_size) {
        throw new Exception("File size for {$file_key} exceeds the 5MB limit.");
    }
    
    $safe_filename = $entry_uuid . '_' . $file_key . '.' . $extension;
    $target_file = $upload_dir . $safe_filename;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        throw new Exception("Failed to move uploaded file for {$file_key} to its permanent location.");
    }

    return $target_file;
}

$prize_id = filter_var($_POST['prize_id'] ?? null, FILTER_VALIDATE_INT);
$prize_name = sanitize_input($_POST['prize_name'] ?? '');
$full_name = sanitize_input($_POST['fullName'] ?? '');
$phone_number = sanitize_input($_POST['phoneNumber'] ?? '');
$age = filter_var($_POST['age'] ?? null, FILTER_VALIDATE_INT);
$street_address = sanitize_input($_POST['streetAddress'] ?? '');
$city = sanitize_input($_POST['city'] ?? '');
$zip_code = sanitize_input($_POST['zipCode'] ?? '');
$heard_about = sanitize_input($_POST['heardAbout'] ?? '');

// Capture the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

if (!$prize_id || empty($prize_name) || empty($full_name) || empty($phone_number) || $age < 18 || empty($street_address) || empty($city) || empty($zip_code) || empty($heard_about)) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'We could not process your submission. Please ensure all personal details, including "How you heard about us", are filled correctly and you are 18 or older.'
    ];
    header("Location: enter_giveaway.php");
    exit();
}


$check_sql = "SELECT COUNT(*) FROM entries WHERE phone_number = :phone_number OR ip_address = :ip_address";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([
    'phone_number' => $phone_number,
    'ip_address' => $user_ip
]);
$submission_count = $check_stmt->fetchColumn();

if ($submission_count > 0) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Submission failed. Our system detected an issue with the entry. Please ensure you have not previously submitted an entry for this giveaway. Only one submission is allowed.'
    ];
    header("Location: enter_giveaway.php");
    exit();
}
// --- END NEW SUBMISSION CHECK ---


$id_front_path = '';
$id_back_path = '';
$entry_uuid = uniqid('', true);

try {
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory: " . $upload_dir);
        }
    }

    $id_front_path = handle_id_upload('idFront', $upload_dir, $entry_uuid);
    $id_back_path = handle_id_upload('idBack', $upload_dir, $entry_uuid);


    $sql = "INSERT INTO entries (
        prize_id, prize_name, full_name, phone_number, age, street_address, city, zip_code, heard_about,
        id_front_path, id_back_path, entry_date, ip_address 
    ) VALUES (
        :prize_id, :prize_name, :full_name, :phone_number, :age, :street_address, :city, :zip_code, :heard_about,
        :id_front_path, :id_back_path, NOW(), :ip_address 
    )";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        'prize_id' => $prize_id,
        'prize_name' => $prize_name,
        'full_name' => $full_name,
        'phone_number' => $phone_number,
        'age' => $age,
        'street_address' => $street_address,
        'city' => $city,
        'zip_code' => $zip_code,
        'heard_about' => $heard_about,
        'id_front_path' => $id_front_path,
        'id_back_path' => $id_back_path,
        'ip_address' => $user_ip 
    ]);

    
    if ($stmt->rowCount() > 0) {
        
        
        setcookie('giveaway_submitted', 'true', time() + (86400 * 30), "/"); 
        // --------------------------------------------------

        $_SESSION['message'] = [
            'type' => 'success',
            'text' => "Your official entry for the <strong>" . htmlspecialchars($prize_name) . "</strong> prize has been successfully submitted! Your ID is under review. We'll contact you if you win."
        ];
    } else {
        throw new Exception("Database failed to insert the entry.");
    }

} catch (Exception $e) {
    
    error_log("Entry Processing Error: " . $e->getMessage());

    if (!empty($id_front_path) && file_exists($id_front_path)) {
        unlink($id_front_path);
    }
    if (!empty($id_back_path) && file_exists($id_back_path)) {
        unlink($id_back_path);
    }
    
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'A system error occurred during submission. Details: ' . htmlspecialchars($e->getMessage())
    ];
}

header("Location: enter_giveaway.php");
exit();