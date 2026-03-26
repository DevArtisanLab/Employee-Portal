<?php
require_once __DIR__ . '/db.php'; // uses $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name        = trim($_POST['full_name']);
    $employee_number  = trim($_POST['employee_number']);
    $branch           = trim($_POST['branch']);
    $position         = trim($_POST['position']);
    $date_started     = $_POST['date_started'];
    $email            = trim($_POST['email']);       // NEW FIELD
    $username         = trim($_POST['username']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role             = 'Employee';

    // Empty check
    if (
        empty($full_name) ||
        empty($employee_number) ||
        empty($branch) ||
        empty($position) ||
        empty($date_started) ||
        empty($email) ||         // check email
        empty($username) ||
        empty($password) ||
        empty($confirm_password)
    ) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    // Password match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check duplicates: username, employee_number, or email
    $check = $pdo->prepare("
        SELECT id 
        FROM users 
        WHERE username = :username 
           OR employee_number = :employee_number
           OR email = :email
    ");
    $check->execute([
        ':username' => $username,
        ':employee_number' => $employee_number,
        ':email' => $email
    ]);

    if ($check->rowCount() > 0) {
        echo "<script>alert('Username, Employee Number, or Email already exists!'); window.history.back();</script>";
        exit;
    }

    // Insert user with email
    $stmt = $pdo->prepare("
        INSERT INTO users
        (full_name, employee_number, branch, position, date_started, email, username, password, role, status)
        VALUES
        (:full_name, :employee_number, :branch, :position, :date_started, :email, :username, :password, :role, 'Pending')
    ");

    if ($stmt->execute([
        ':full_name' => $full_name,
        ':employee_number' => $employee_number,
        ':branch' => $branch,
        ':position' => $position,
        ':date_started' => $date_started,
        ':email' => $email,
        ':username' => $username,
        ':password' => $hashed_password,
        ':role' => $role
    ])) {
        echo "<script>
            alert('Registration submitted! Please wait for admin approval.');
            window.location='../login.php';
        </script>";
    } else {
        echo "<script>alert('Registration failed.'); window.history.back();</script>";
    }
}
