<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: admin_dashboard.php");
} else {
    header("location: login.php");
}
exit;
