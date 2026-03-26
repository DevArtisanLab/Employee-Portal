<?php
session_start();

// Restrict access to logged-in HR users
require_once __DIR__ . '/../config/auth_hr.php';
require_once __DIR__ . '/../config/db.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch user info from database
$stmtUser = $pdo->prepare("
    SELECT full_name, employee_number, branch, position, date_started, email, profile_photo
    FROM users
    WHERE id = ?
");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found!";
    exit();
}

// Avatar setup
$avatarPhoto = $user['profile_photo'];
$avatarInitials = implode('', array_map(fn($w) => strtoupper($w[0]), explode(' ', $user['full_name'])));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
--primary-color:#4f46e5;
--secondary-color:#818cf8;
--bg-color:#f3f4f6;
--sidebar-bg:#1f2937;
--text-light:#ffffff;
--text-dark:#1f2937;
--card-bg:#ffffff;
--danger:#ef4444;
--success:#10b981;
}

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}

body{
display:flex;
background-color:var(--bg-color);
height:100vh;
overflow:hidden;
}

/* SIDEBAR */
.sidebar{
width:260px;
background-color:var(--sidebar-bg);
color:var(--text-light);
display:flex;
flex-direction:column;
padding:20px;
}

.logo{
font-size:24px;
font-weight:bold;
margin-bottom:40px;
text-align:center;
color:var(--secondary-color);
display:flex;
align-items:center;
justify-content:center;
gap:10px;
}

.nav-links{
list-style:none;
flex-grow:1;
}

.nav-links li{
margin-bottom:10px;
}

.nav-links a{
text-decoration:none;
color:#9ca3af;
padding:12px 15px;
display:flex;
align-items:center;
gap:15px;
border-radius:8px;
transition:0.2s;
font-size:15px;
}

.nav-links a:hover,
.nav-links a.active{
background-color:var(--primary-color);
color:white;
}

.nav-links a i{
width:20px;
text-align:center;
}

.logout-btn{
margin-top:auto;
background-color:rgba(239,68,68,0.1);
color:var(--danger);
}

.logout-btn:hover{
background-color:var(--danger);
color:white;
}

/* MAIN CONTENT */
.main-content{
flex:1;
display:flex;
flex-direction:column;
overflow-y:auto;
}

/* TOP HEADER */
.top-header{
background-color:var(--card-bg);
padding:15px 30px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 2px 4px rgba(0,0,0,0.05);
}

.search-bar{
position:relative;
}

.search-bar input{
padding:10px 40px 10px 15px;
border:1px solid #e5e7eb;
border-radius:20px;
width:300px;
outline:none;
}

.search-bar i{
position:absolute;
right:15px;
top:50%;
transform:translateY(-50%);
color:#9ca3af;
}

.user-profile{
display:flex;
align-items:center;
gap:15px;
}

.user-info h4{ font-size:14px; }
.user-info p{ font-size:12px; color:#6b7280; }

.avatar{
width:40px;
height:40px;
background-color:var(--secondary-color);
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
color:white;
font-weight:bold;
}

/* CONTENT */
.content-area{
padding:30px;
}

.section-title{
margin-bottom:20px;
color:var(--text-dark);
}

/* PROFILE CARD */
.profile-card{
background:var(--card-bg);
padding:30px;
border-radius:12px;
box-shadow:0 4px 6px rgba(0,0,0,0.05);
max-width:700px;
}

.profile-header{
display:flex;
align-items:center;
gap:20px;
margin-bottom:25px;
}

.profile-avatar{
width:80px;
height:80px;
background:var(--secondary-color);
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
color:white;
font-size:28px;
font-weight:bold;
}

.profile-info h3{
margin-bottom:5px;
}

.profile-info p{
color:#6b7280;
font-size:14px;
}

/* FORM */
.form-group{
margin-bottom:15px;
}

label{
display:block;
margin-bottom:5px;
font-weight:500;
}

input{
width:100%;
padding:10px;
border:1px solid #e5e7eb;
border-radius:6px;
outline:none;
}

input:focus{
border-color:var(--primary-color);
}

.btn{
margin-top:10px;
padding:10px 20px;
background:var(--primary-color);
border:none;
border-radius:6px;
color:white;
cursor:pointer;
transition:0.2s;
}

.btn:hover{
background:#4338ca;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="logo">
    <i class="fa-solid fa-users-rectangle"></i> HR Portal
  </div>

  <ul class="nav-links">
    <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
    <li><a href="manage_users.php"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
    <li><a href="manage_exams.php"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
    <li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
    <li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
    <li><a href="profile.php" class="active"><i class="fa-solid fa-user"></i> My Profile</a></li>
    <a href="../config/logout.php" class="logout-btn" onclick="return confirmLogout();">
  <i class="fa-solid fa-right-from-bracket"></i> Logout
</a> </ul>

  <!-- Profile Footer at Bottom -->
  <div class="profile-footer" style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px;">
    <div class="avatar" style="width:40px; height:40px; font-size:16px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background-color:#818cf8; color:white; border-radius:50%;">
      <?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
        <img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>" alt="Profile" style="width:100%;height:100%;border-radius:50%;">
      <?php else: ?>
        <?= $avatarInitials ?>
      <?php endif; ?>
    </div>
    <div style="color:white;">
      <div style="font-size:14px; font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></div>
      <div style="font-size:12px; color:#d1d5db;"><?= htmlspecialchars($user['position']) ?></div>
    </div>
  </div>
</nav>

<!-- MAIN CONTENT -->
<div class="main-content">

<!-- TOP HEADER -->
<header class="top-header">
<div class="search-bar">
<!-- <input type="text" placeholder="Search profile..."> -->
<!-- <i class="fa-solid fa-magnifying-glass"></i> -->
</div>
<div class="user-profile">
<!-- <div class="user-info">
<h4><?= htmlspecialchars($user['full_name']) ?></h4>
<p><?= htmlspecialchars($user['position']) ?></p>
</div>
<div class="avatar"> -->
<!-- <?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
<img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>" alt="Profile">
<?php else: ?>
<?= $avatarInitials ?>
<?php endif; ?>
</div> -->
</div>
</header>

<!-- PAGE CONTENT -->
<div class="content-area">

<h2 class="section-title">My Profile</h2>

<div class="profile-card">

<div class="profile-header">
<div class="profile-avatar">
<?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
<img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>" alt="Profile" style="width:100%;height:100%;border-radius:50%;">
<?php else: ?>
<?= $avatarInitials ?>
<?php endif; ?>
</div>
<div class="profile-info">
<h3><?= htmlspecialchars($user['full_name']) ?></h3>
<p><?= htmlspecialchars($user['position']) ?></p>
<p><?= htmlspecialchars($user['branch']) ?></p>
</div>
</div>

<form>
<div class="form-group">
<label>Full Name</label>
<input type="text" value="<?= htmlspecialchars($user['full_name']) ?>">
</div>

<div class="form-group">
<label>Email Address</label>
<input type="email" value="<?= htmlspecialchars($user['email']) ?>">
</div>

<div class="form-group">
<label>Position</label>
<input type="text" value="<?= htmlspecialchars($user['position']) ?>">
</div>

<div class="form-group">
<label>Department</label>
<input type="text" value="<?= htmlspecialchars($user['branch']) ?>">
</div>

<div class="form-group">
<label>Date Started</label>
<input type="text" value="<?= htmlspecialchars($user['date_started']) ?>" readonly>
</div>

<button class="btn">Save Changes</button>
</form>

</div>

</div>
</div>
<script>
function confirmLogout() {
    // Show confirmation dialog
    if (confirm("Are you sure you want to logout?")) {
        return true; // proceed with logout
    } else {
        return false; // cancel logout
    }
}
</script>
</body>
</html>