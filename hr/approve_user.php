<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth_hr.php';

$id = intval($_GET['id'] ?? 0);

if($id > 0){
    $stmt = $pdo->prepare("UPDATE users SET status='Approved' WHERE id=?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = "User approved successfully.";
}

header("Location: manage_users.php");
exit();
?>