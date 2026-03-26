<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    // Not logged in or not an admin → redirect to login
    header("Location: ../login.php");
    exit;
}
?>
<!-- <?php include '../config/auth_admin.php'; ?> -->