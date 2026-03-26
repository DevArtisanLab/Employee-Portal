<?php
session_start();
require_once 'db.php'; // uses $pdo

// Enable for debugging (remove later)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        // ✅ Account approval check
        if ($user['status'] !== 'Approved') {
            echo "<script>
                alert('Your account is pending admin approval. Please wait for approval.');
                window.location.href = '../login.php';
            </script>";
            exit;
        }

        // ✅ Password check
        if (password_verify($password, $user['password'])) {
            // Store session data
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            // ✅ Redirect based on role
            if ($user['role'] === 'Administrator') {
                header("Location: ../admin/index.php");
            } elseif ($user['role'] === 'HR') {
                header("Location: ../hr/index.php");
            } else {
                header("Location: ../employee/index.php");
            }
            exit;

        } else {
            echo "<script>
                alert('Invalid password.');
                window.location.href = '../login.php';
            </script>";
            exit;
        }

    } else {
        echo "<script>
            alert('User not found.');
            window.location.href = '../login.php';
        </script>";
        exit;
    }
}