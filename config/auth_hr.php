<?php
// ==========================
// HR AUTHENTICATION MIDDLEWARE
// ==========================

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1️⃣ Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2️⃣ Must have role set
if (!isset($_SESSION['role'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// 3️⃣ Must be HR
if ($_SESSION['role'] !== 'HR') {
    header("Location: ../index.php");
    exit();
}