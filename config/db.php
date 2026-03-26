<?php
// ===== DATABASE CREDENTIALS =====
// $servername = "sql105.infinityfree.com";
// $username = "if0_40916519";
// $password = "NpA73737";
// $dbname = "if0_40916519_northpa1_ees";

// $servername = "localhost";
// $username = "northpa1_northparkMIS";
// $password = "MIS@np73737";
// $dbname = "northpa1_ees";

$host = "localhost";
$dbname = "employee_portal";
$username = "root";
$password = "";

// ===== PDO CONNECTION =====
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>