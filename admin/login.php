<?php
session_start();
require_once '../config.php';
require_once '../get_setting.php';

$username = $password = '';
$login_err = '';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: admin_dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT admin_id, username, password_hash FROM admins WHERE username = :username";

    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $id = $row["admin_id"];
                    $db_username = $row["username"];
                    $stored_hash = $row["password_hash"];

                    $auth_success = false;
                    
                    // 1. Try modern password_verify
                    if (password_verify($password, $stored_hash)) {
                        $auth_success = true;
                    } 
                    // 2. Fallback to legacy plain-text comparison (for old accounts)
                    elseif ($password === $stored_hash) {
                        $auth_success = true;
                        
                        // AUTO-MIGRATE: Update the plain-text password to a hash for security
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $update_sql = "UPDATE admins SET password_hash = :new_hash WHERE admin_id = :id";
                        if ($update_stmt = $pdo->prepare($update_sql)) {
                            $update_stmt->execute(['new_hash' => $new_hash, 'id' => $id]);
                        }
                    }

                    if ($auth_success) {
                        session_regenerate_id();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["admin_id"] = $id;
                        $_SESSION["username"] = $db_username;
                        header("location: admin_dashboard.php");
                        exit;
                    } else {
                        $login_err = "Invalid username or password.";
                    }
                }
            } else {
                $login_err = "Invalid username or password.";
            }
        } else {
            $login_err = "Oops! Something went wrong. Please try again later.";
        }

        unset($stmt);
    }
}
unset($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <?php if (!empty($use_favicon)): ?><link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $admin_favicon_path; ?>"><?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.min.css">
    
    <link href="../assets/css/google-fonts.css" rel="stylesheet">
    <style>
        :root {
            --bs-crimson-primary: #B00000;
            --bs-crimson-dark: #6D0000;
            --bs-dark-background: #1a1a2e;
            --bs-card-surface: #ffffff;
            --crimson-gradient: linear-gradient(135deg, #B00000, #6D0000);
        }
        body {
            background-color: var(--bs-dark-background);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Work Sans', sans-serif;
            margin: 0;
        }
        .login-card {
            background-color: var(--bs-card-surface);
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5); 
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.55);
        }
        .card-title {
            color: var(--bs-dark-background);
            font-size: 1.75rem;
            font-weight: 700;
        }
        .btn-crimson-accent {
            background: var(--crimson-gradient);
            border: none;
            color: white;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 12px 25px;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(176, 0, 0, 0.3);
        }
        .btn-crimson-accent:hover {
            background: linear-gradient(135deg, #C60000, #8A0000);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(176, 0, 0, 0.4);
            color: white;
        }
        .form-control {
            border-radius: 6px;
        }
        .form-control:focus {
            border-color: var(--bs-crimson-primary);
            box-shadow: 0 0 0 0.25rem rgba(176, 0, 0, 0.3);
        }
        .text-crimson { color: var(--bs-crimson-primary) !important; }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            border-color: #ced4da;
            color: #6c757d;
        }
        .return-btn-container {
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
        .btn-return-site {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-block;
        }
        .btn-return-site:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: rgba(255, 255, 255, 0.8);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .login-container {
            max-width: 480px; 
            width: 90%;
            padding: 15px;
        }

        @media (max-width: 576px) {
            .login-container {
                width: 100%;
                padding: 15px;
            }
            .login-card {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
            .input-group-lg > .form-control, 
            .input-group-lg > .input-group-text, 
            .input-group-lg > .btn {
                padding: 0.6rem 0.75rem; 
                font-size: 0.95rem;
            }
            .btn-lg {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container"> 
        <div class="card login-card p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-person-lock fs-1 text-crimson"></i>
            </div>
            
            <h1 class="card-title text-center mb-2">Admin Login</h1>
            <p class="text-center text-muted mb-4">Access the secured management dashboard</p>

            <?php if (!empty($login_err)): ?>
                <div class="alert alert-danger small" role="alert">
                    <i class="bi bi-x-octagon-fill me-2"></i><?php echo $login_err; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label visually-hidden">Username</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label visually-hidden">Password</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-crimson-accent btn-lg shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-2"></i> LOG IN
                    </button>
                </div>
            </form>
        </div>
        
        <div class="return-btn-container">
            <a href="../index.php" class="btn btn-return-site">
                <i class="bi bi-arrow-left-circle-fill me-1"></i> Return to Site
            </a>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        if (togglePassword && password && toggleIcon) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                if (type === 'text') {
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                } else {
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                }
            });
        }
    </script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
