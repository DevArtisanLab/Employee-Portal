<?php
// Start the session if it’s not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login or homepage
header("Location: ../index.php");
exit;
?>
