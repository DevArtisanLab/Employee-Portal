<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth_hr.php';

// Logged-in user info
$user_id = $_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

$avatarPhoto = $user['profile_photo'] ?? null;
$avatarInitials = implode('', array_map(fn($w)=>strtoupper($w[0]), explode(" ", $user['full_name'] ?? "Admin User")));

// User to edit
$editId = intval($_GET['id'] ?? 0);
if($editId <= 0){ header("Location: manage_users.php"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$editId]);
$userRow = $stmt->fetch();

if(!$userRow){ header("Location: manage_users.php"); exit(); }

// Handle form submit
if($_SERVER['REQUEST_METHOD']==='POST'){

    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);
    $branch = trim($_POST['branch']);
    $position = trim($_POST['position']);
    $dateStarted = trim($_POST['date_started']);

    $password = $_POST['password'] ?? '';

    if(!empty($password)){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashedPassword = $userRow['password'];
    }

    $stmt = $pdo->prepare("UPDATE users 
        SET full_name=?, email=?, username=?, password=?, role=?, status=?, branch=?, position=?, date_started=? 
        WHERE id=?");

    $stmt->execute([
        $fullName,
        $email,
        $username,
        $hashedPassword,
        $role,
        $status,
        $branch,
        $position,
        $dateStarted,
        $editId
    ]);

    header("Location: manage_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

/* ORIGINAL COLORS */
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

/* SIDEBAR (UNCHANGED) */
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

.content-area{
padding:35px;
}

/* HEADER TITLE */
.section-title{
font-size:22px;
font-weight:600;
display:flex;
align-items:center;
gap:10px;
margin-bottom:15px;
}

/* BACK BUTTON */
.back-btn{
display:inline-flex;
align-items:center;
gap:6px;
background:linear-gradient(135deg,#4f46e5,#6366f1);
color:white;
text-decoration:none;
padding:8px 14px;
border-radius:6px;
margin-bottom:20px;
font-size:14px;
}

/* FORM CARD */
.form-card{
background:white;
padding:30px;
border-radius:14px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
max-width:850px;
}

/* GRID */
.form-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:18px;
}

.form-group{
display:flex;
flex-direction:column;
}

.form-group.full{
grid-column:1/3;
}

label{
margin-bottom:6px;
font-weight:500;
}

/* INPUTS */
input,select{
padding:10px 12px;
border-radius:6px;
border:1px solid #d1d5db;
font-size:14px;
transition:0.2s;
}

input:focus,select:focus{
outline:none;
border-color:var(--primary-color);
box-shadow:0 0 0 2px rgba(79,70,229,0.15);
}

/* BUTTON */
.btn{
margin-top:20px;
padding:10px 18px;
border:none;
border-radius:8px;
cursor:pointer;
font-size:14px;
background:linear-gradient(135deg,#4f46e5,#6366f1);
color:white;
transition:0.2s;
}

.btn:hover{
transform:translateY(-1px);
box-shadow:0 6px 12px rgba(0,0,0,0.15);
}

/* AVATAR */
.avatar{
width:40px;
height:40px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
color:white;
font-weight:bold;
background-color:var(--secondary-color);
}

.avatar img{
width:100%;
height:100%;
border-radius:50%;
object-fit:cover;
}

/* RESPONSIVE */
@media(max-width:700px){
.form-grid{
grid-template-columns:1fr;
}

.form-group.full{
grid-column:auto;
}
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<nav class="sidebar">

<div class="logo">
<i class="fa-solid fa-users-rectangle"></i> HR Portal
</div>

<ul class="nav-links">
<li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
<li><a href="manage_users.php" class="active"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
<li><a href="manage_exams.php"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
<li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
<li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
<li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>

<a href="../config/logout.php" class="logout-btn" onclick="return confirmLogout();">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a>
</ul>

<div class="profile-footer" style="margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;gap:10px;">

<div class="avatar">
<?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
<img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>">
<?php else: ?>
<?= $avatarInitials ?>
<?php endif; ?>
</div>

<div style="color:white;">
<div style="font-size:14px;font-weight:600;">
<?= htmlspecialchars($user['full_name'] ?? 'Admin User') ?>
</div>
<div style="font-size:12px;color:#9ca3af;">
<?= htmlspecialchars($user['position'] ?? 'HR Manager') ?>
</div>
</div>

</div>

</nav>

<!-- MAIN -->
<div class="main-content">

<div class="content-area">

<h2 class="section-title">
<i class="fa-solid fa-user-pen"></i>
Edit User: <?= htmlspecialchars($userRow['full_name']); ?>
</h2>

<a href="manage_users.php" class="back-btn">
<i class="fa-solid fa-arrow-left"></i> Back
</a>

<div class="form-card">

<form method="POST">

<div class="form-grid">

<div class="form-group full">
<label>Full Name</label>
<input type="text" name="full_name" value="<?= htmlspecialchars($userRow['full_name']); ?>" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($userRow['email']); ?>" required>
</div>

<div class="form-group">
<label>Username</label>
<input type="text" name="username" value="<?= htmlspecialchars($userRow['username']); ?>" required>
</div>

<div class="form-group full">
<label>Password <small>(leave blank to keep current)</small></label>
<input type="password" name="password">
</div>

<div class="form-group">
<label>Role</label>
<select name="role">
<option value="Employee" <?= $userRow['role']=='Employee'?'selected':'' ?>>Employee</option>
<option value="Admin" <?= $userRow['role']=='Admin'?'selected':'' ?>>Admin</option>
</select>
</div>

<div class="form-group">
<label>Status</label>
<select name="status">
<option value="Approved" <?= $userRow['status']=='Approved'?'selected':'' ?>>Approved</option>
<option value="Pending" <?= $userRow['status']=='Pending'?'selected':'' ?>>Pending</option>
<option value="Rejected" <?= $userRow['status']=='Rejected'?'selected':'' ?>>Rejected</option>
</select>
</div>

<div class="form-group">
<label>Branch</label>
<input type="text" name="branch" value="<?= htmlspecialchars($userRow['branch']); ?>">
</div>

<div class="form-group">
<label>Position</label>
<input type="text" name="position" value="<?= htmlspecialchars($userRow['position']); ?>">
</div>

<div class="form-group full">
<label>Date Started</label>
<input type="date" name="date_started" value="<?= htmlspecialchars($userRow['date_started']); ?>">
</div>

</div>

<button type="submit" class="btn">
<i class="fa-solid fa-floppy-disk"></i> Save Changes
</button>

</form>

</div>

</div>

</div>

<script>
function confirmLogout(){
return confirm("Are you sure you want to logout?");
}
</script>

</body>
</html>