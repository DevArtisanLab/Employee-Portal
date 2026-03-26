<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>North Park Portal | Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="login-container">
    <!-- Left Image Section -->
    <div class="login-image">
        <img src="assets/images/bg-login.jpg" alt="Company Image">
    </div>

    <!-- Right Login Section -->
    <div class="login-form">
        <div class="form-box">
            <h2>Employee Portal</h2>
            <p class="subtitle">Sign in to continue</p>

            <form action="config/login_process.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required
                        placeholder="Enter your username"
                    >
                </div>

                <div class="input-group password-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required placeholder="Enter your password">
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>

                <button type="submit" class="btn-login">Login</button>

                <p class="register-link">
                    Don’t have an account? <a href="register.php">Register</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    // Password visibility toggle
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", function() {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
        this.classList.toggle("fa-eye-slash");
    });
</script>

</body>
</html>
