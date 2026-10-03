<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* Database credentials - handles both local and online hosting */
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';

if ($http_host === 'localhost' || $remote_addr === '127.0.0.1' || $remote_addr === '::1' || php_sapi_name() === 'cli') {
    // Local Environment (Ampps/XAMPP)
    $host = 'localhost';
    $dbname = 'celebrity';
    $db_user = 'root';
    $db_pass = 'mysql';
} else {
    // Production Online Environment (Modify these with your live localhost server details)
    $host = 'sdb-72.hosting.stackcp.net';
    $dbname = 'rema12-35303631223c';
    $db_user = 'rema12-35303631223c';
    $db_pass = 'Raziboy11*';
}

define('DB_SERVER', $host);
define('DB_USERNAME', $db_user);
define('DB_PASSWORD', $db_pass);
define('DB_NAME', $dbname);


/* Database connection using PDO */
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error_msg = htmlspecialchars($e->getMessage());
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <link href="assets/css/google-fonts.css" rel="stylesheet">
    <style>
        :root {
            --crimson: #B00000;
            --gold: #FFC107;
            --dark: #121212;
            --gray: #f8f9fa;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a0000 0%, #3a0000 100%);
            color: #fff;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: #fff;
            color: var(--dark);
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
        }
        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--crimson), var(--gold));
        }
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(176, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .icon-wrapper svg {
            width: 40px;
            height: 40px;
            fill: var(--crimson);
        }
        h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 0.5rem;
            color: var(--crimson);
        }
        p.subtitle {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .error-details {
            background: var(--gray);
            border-left: 4px solid var(--crimson);
            padding: 1rem;
            border-radius: 8px;
            text-align: left;
            font-family: monospace;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 2rem;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            background: var(--crimson);
            color: #fff;
            text-decoration: none;
            padding: 0.8rem 2.5rem;
            border-radius: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #8A0000;
            box-shadow: 0 5px 15px rgba(176, 0, 0, 0.3);
            transform: translateY(-2px);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <h1>Connection Failed</h1>
        <p class="subtitle">The platform cannot establish a connection to the database. Please verify your connection credentials.</p>
        
        <div class="error-details">
            <strong>Diagnostic Error:</strong><br><br>
            {$error_msg}
        </div>
        
        <a href="javascript:location.reload()" class="btn">Try Again</a>
    </div>
</body>
</html>
HTML;
    die($html);
}
?>