<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config.php';

$upload_dir = '../uploads/';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- UPDATE REVIEW LOGIC ---
    if (isset($_POST['update_review'])) {
        $review_id = filter_var($_POST['review_id'], FILTER_VALIDATE_INT);
        $winner_name = trim($_POST['winner_name'] ?? '');
        $winner_location = trim($_POST['winner_location'] ?? '');
        $prize_won = trim($_POST['prize_won'] ?? '');
        $testimonial = trim($_POST['testimonial'] ?? '');
        
        if (!$review_id || empty($winner_name)) {
            $_SESSION['message'] = "Error: Invalid data provided.";
            $_SESSION['message_type'] = "danger";
            header("location: admin_dashboard.php");
            exit;
        }

        $pdo->beginTransaction();
        try {
            // Get current image path for potential deletion
            $stmt = $pdo->prepare("SELECT winner_image_path FROM winners WHERE id = ?");
            $stmt->execute([$review_id]);
            $current_image = $stmt->fetchColumn();

            $image_path = $current_image;

            // Handle new image upload
            if (isset($_FILES['winner_image']) && $_FILES['winner_image']['error'] == 0) {
                $file_tmp = $_FILES['winner_image']['tmp_name'];
                $file_name = $_FILES['winner_image']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_ext, $allowed)) {
                    $new_file_name = uniqid('winner_', true) . '.' . $file_ext;
                    $destination = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $image_path = $destination;
                        // Delete old image if it exists
                        if ($current_image && file_exists($current_image)) {
                            unlink($current_image);
                        }
                    }
                }
            }

            $sql = "UPDATE winners SET winner_name = ?, winner_location = ?, prize_won = ?, testimonial = ?, winner_image_path = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$winner_name, $winner_location, $prize_won, $testimonial, $image_path, $review_id]);

            $pdo->commit();
            $_SESSION['message'] = "Review updated successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
        
        header("location: admin_dashboard.php");
        exit;
    }

    // --- ADD REVIEW LOGIC (Original) ---
    $winner_name     = trim($_POST['winner_name'] ?? '');
    $winner_location = trim($_POST['winner_location'] ?? '');
    $prize_won       = trim($_POST['prize_won'] ?? '');
    $testimonial     = trim($_POST['testimonial'] ?? '');
    
    // The image path will be set during the upload process
    $winner_image_path = NULL; 

    // 2. Handle File Upload
    if (isset($_FILES['winner_image']) && $_FILES['winner_image']['error'] == 0) {
        
        $file_tmp  = $_FILES['winner_image']['tmp_name'];
        $file_name = $_FILES['winner_image']['name'];
        $file_size = $_FILES['winner_image']['size'];

        // Security and Validation Checks
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 2097152; 

        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['message'] = "Error: Only JPG, JPEG, PNG, & GIF files are allowed.";
            $_SESSION['message_type'] = "danger";
            header("location: admin_dashboard.php");
            exit;
        }

        if ($file_size > $max_file_size) {
            $_SESSION['message'] = "Error: File size must be less than 2MB.";
            $_SESSION['message_type'] = "danger";
            header("location: admin_dashboard.php");
            exit;
        }

        
        $new_file_name = uniqid('winner_', true) . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;

        
        if (move_uploaded_file($file_tmp, $destination)) {
            $winner_image_path = $destination; 
        } else {
            $_SESSION['message'] = "Error: Failed to move the uploaded file.";
            $_SESSION['message_type'] = "danger";
            header("location: admin_dashboard.php");
            exit;
        }
    } else {
        
         $_SESSION['message'] = "Error: Please upload a winner image.";
         $_SESSION['message_type'] = "danger";
         header("location: admin_dashboard.php");
         exit;
    }

    // 3. Sql statement to insert data 
    $sql = "INSERT INTO winners (winner_name, winner_location, prize_won, winner_image_path, testimonial) 
            VALUES (:winner_name, :winner_location, :prize_won, :winner_image_path, :testimonial)";

    if ($stmt = $pdo->prepare($sql)) {
        
        $stmt->bindParam(":winner_name", $winner_name, PDO::PARAM_STR);
        $stmt->bindParam(":winner_location", $winner_location, PDO::PARAM_STR);
        $stmt->bindParam(":prize_won", $prize_won, PDO::PARAM_STR);
        $stmt->bindParam(":testimonial", $testimonial, PDO::PARAM_STR);
        $stmt->bindParam(":winner_image_path", $winner_image_path, PDO::PARAM_STR);

        
        if ($stmt->execute()) {
            $_SESSION['message'] = "New winner and image added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: Could not save the prize details to the database.";
            $_SESSION['message_type'] = "danger";
        }

        unset($stmt);
    }

    
    header("location: admin_dashboard.php");
    exit;
}

// ----------------------------------------------------------------------------------

// --- DELETE PRIZE ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    
    $sql_select = "SELECT winner_image_path FROM winners WHERE id = :id";
    if ($stmt_select = $pdo->prepare($sql_select)) {
        $stmt_select->bindParam(":id", $id, PDO::PARAM_INT);
        if ($stmt_select->execute()) {
            $row = $stmt_select->fetch(PDO::FETCH_ASSOC);
            $image_to_delete = $row['winner_image_path'] ?? NULL;

            // Delete the physical file from the server
            if ($image_to_delete && file_exists($image_to_delete)) {
                unlink($image_to_delete);
            }
        }
        unset($stmt_select);
    }

    // 2. Delete the record from the database
    $sql_delete = "DELETE FROM winners WHERE id = :id";
    
    if ($stmt_delete = $pdo->prepare($sql_delete)) {
        $stmt_delete->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($stmt_delete->execute()) {
            $_SESSION['message'] = "Winner and associated image deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: Could not delete the winner record.";
            $_SESSION['message_type'] = "danger";
        }
        
        unset($stmt_delete);
    }
    
    header("location: admin_dashboard.php");
    exit;
}

// Close connection
unset($pdo);
?>
