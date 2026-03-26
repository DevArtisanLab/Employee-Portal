<div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
    <a href="seminars.php"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
    <a href="examinations.php"><i class="bi bi-pencil-square"></i> Examinations</a>
    <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
    <a href="../config/logout.php" onclick="logout()"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Mobile Toggle Button -->
<button class="btn btn-primary toggle-btn d-md-none" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
<script>
function logout() {
    // Show confirmation dialog
    const confirmLogout = confirm("Are you sure you want to logout?");
    if (confirmLogout) {
        // Redirect to logout.php if confirmed
        window.location.href = "../config/logout.php";
    }
    // If canceled, do nothing
}
</script>