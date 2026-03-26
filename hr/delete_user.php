<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth_hr.php';

$user_id_to_delete = intval($_GET['id']);

// Prevent deleting yourself
if ($user_id_to_delete == $_SESSION['user_id']) {
    echo "<script>alert('You cannot delete your own account!'); window.location.href='manage_users.php';</script>";
    exit();
}

// Prevent deleting Admin accounts
$stmtCheck = $pdo->prepare("SELECT role, status FROM users WHERE id = ?");
$stmtCheck->execute([$user_id_to_delete]);
$userData = $stmtCheck->fetch();

if (!$userData) {
    echo "<script>alert('User not found!'); window.location.href='manage_users.php';</script>";
    exit();
}

if ($userData['role'] === 'Admin') {
    echo "<script>alert('You cannot delete an Admin account!'); window.location.href='manage_users.php';</script>";
    exit();
}

// Alert if user is already inactive
if ($userData['status'] === 'Inactive') {
    echo "<script>alert('This user is already inactive.'); window.location.href='manage_users.php';</script>";
    exit();
}

// ==========================
// Soft delete: set status to Inactive
// ==========================
$stmt = $pdo->prepare("UPDATE users SET status='Inactive' WHERE id = ?");
$stmt->execute([$user_id_to_delete]);

// Redirect back with success message
header("Location: manage_users.php?deleted=1");
exit();
