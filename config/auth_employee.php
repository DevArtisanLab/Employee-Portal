<?php
session_start();

// Check if user is logged in and is an Employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') {
    // Not logged in or not an employee → redirect to login
    header("Location: ../login.php");
    exit;
}
$employeeName = $_SESSION['full_name'] ?? 'Employee';
?>
<!-- <?php include '../config/auth_employee.php'; 
$employeeName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Employee';
?> -->