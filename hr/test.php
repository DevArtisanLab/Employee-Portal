<?php
require_once __DIR__ . '/../config/auth_hr.php'; // ensures user must be logged in
require_once __DIR__ . '/../config/db.php';      // PDO connection

// ==========================
// GET EXAM DATA
// ==========================
if (!isset($_GET['id'])) {
    header("Location: manage_exams.php");
    exit();
}

$exam_id = intval($_GET['id']);

// Fetch exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    header("Location: manage_exams.php");
    exit();
}

// ==========================
// UPDATE EXAM
// ==========================
if (isset($_POST['update_exam'])) {
    $exam_order = $_POST['exam_order'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $exam_date = $_POST['exam_date'];
    $due_date = $_POST['due_date'];
    $passing_score = $_POST['passing_score'];
    $duration = $_POST['duration'];
    $status = $_POST['status'];
    $target_position = $_POST['target_position'];

    $stmt = $pdo->prepare("UPDATE exams SET 
        exam_order = ?, title = ?, description = ?, 
        exam_date = ?, due_date = ?, passing_score = ?, 
        duration = ?, status = ?, target_position = ?
        WHERE id = ?");

    $stmt->execute([
        $exam_order, $title, $description, 
        $exam_date, $due_date, $passing_score, 
        $duration, $status, $target_position, $exam_id
    ]);

    header("Location: manage_exams.php");
    exit();
}

// ==========================
// FETCH LOGGED-IN USER PROFILE
// ==========================
$stmt_user = $pdo->prepare("SELECT profile_photo, username as full_name FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id'] ?? 1]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC) ?: ['profile_photo' => null, 'full_name' => 'Admin User'];

// ==========================
// FUNCTION: Get Initials
// ==========================
function getInitials($name){
    $words = explode(" ", $name);
    $initials = "";
    foreach($words as $w){ $initials .= strtoupper($w[0]); }
    return substr($initials, 0, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Exam</title>
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
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{display:flex;background-color:var(--bg-color);height:100vh;overflow:hidden;}
.sidebar{width:260px;background-color:var(--sidebar-bg);color:var(--text-light);display:flex;flex-direction:column;padding:20px;}
.logo{font-size:24px;font-weight:bold;margin-bottom:40px;text-align:center;color:var(--secondary-color);display:flex;align-items:center;justify-content:center;gap:10px;}
.nav-links{list-style:none;flex-grow:1;}
.nav-links li{margin-bottom:10px;}
.nav-links a{text-decoration:none;color:#9ca3af;padding:12px 15px;display:flex;align-items:center;gap:15px;border-radius:8px;transition:0.2s;font-size:15px;}
.nav-links a:hover,.nav-links a.active{background-color:var(--primary-color);color:white;}
.nav-links a i{width:20px;text-align:center;}
.logout-btn{margin-top:auto;background-color:rgba(239,68,68,0.1);color:var(--danger);}
.logout-btn:hover{background-color:var(--danger);color:white;}
.main-content{flex:1;display:flex;flex-direction:column;overflow-y:auto;}
.top-header{background-color:var(--card-bg);padding:15px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
.user-profile{display:flex;align-items:center;gap:15px;}
.avatar{width:40px;height:40px;background-color:var(--secondary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;}
.avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;}
.content-area{padding:30px;}
.section-title{margin-bottom:20px;color:var(--text-dark);}
.form-box{background:var(--card-bg);padding:20px;border-radius:10px;margin-bottom:20px;}
input,select,textarea{width:100%;padding:8px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;}
.btn{padding:8px 16px;border:none;border-radius:6px;cursor:pointer;font-size:13px;transition:0.2s;}
.btn-primary{background:var(--primary-color);color:white;}
.btn-primary:hover{background:#4338ca;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<nav class="sidebar">
<div class="logo"><i class="fa-solid fa-users-rectangle"></i> HR Portal</div>
<ul class="nav-links">
<li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
<li><a href="manage_users.php"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
<li><a href="manage_exams.php" class="active"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
<li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
<li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
<li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
<li><a href="../config/logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
</ul>
<!-- PROFILE FOOTER -->
    <div class="profile-footer" style="margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;gap:10px;">
        <div class="avatar" style="width:40px;height:40px;font-size:16px;flex-shrink:0;">
            <?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
                <img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>" alt="Profile" style="width:100%;height:100%;border-radius:50%;">
            <?php else: ?>
                <?= $avatarInitials ?>
            <?php endif; ?>
        </div>
        <div style="color:white;">
            <div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></div>
            <div style="font-size:12px;color:#9ca3af;"><?= htmlspecialchars($user['position']) ?></div>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="main-content">
<header class="top-header">
<div class="user-profile">
<div>
<h4><?= htmlspecialchars($user['full_name']); ?></h4>
<p>HR Manager</p>
</div>
<div class="avatar">
<?php if(!empty($user['profile_photo']) && file_exists("../uploads/{$user['profile_photo']}")): ?>
<img src="../uploads/<?= htmlspecialchars($user['profile_photo']); ?>" alt="Profile">
<?php else: ?>
<?= getInitials($user['full_name']); ?>
<?php endif; ?>
</div>
</div>
</header>

<div class="content-area">
<h2 class="section-title">Edit Exam</h2>

<div class="form-box">
<form method="POST">
<label>Exam Order</label>
<input type="number" name="exam_order" value="<?= htmlspecialchars($exam['exam_order']); ?>" required>

<label>Title</label>
<input type="text" name="title" value="<?= htmlspecialchars($exam['title']); ?>" required>

<label>Description</label>
<textarea name="description"><?= htmlspecialchars($exam['description']); ?></textarea>

<label>Exam Date</label>
<input type="datetime-local" name="exam_date" value="<?= date('Y-m-d\TH:i', strtotime($exam['exam_date'])); ?>" required>

<label>Due Date</label>
<input type="datetime-local" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime($exam['due_date'])); ?>" required>

<label>Passing Score</label>
<input type="number" name="passing_score" value="<?= htmlspecialchars($exam['passing_score']); ?>" required>

<label>Duration (minutes)</label>
<input type="number" name="duration" value="<?= htmlspecialchars($exam['duration']); ?>" required>

<label>Status</label>
<select name="status" required>
<option value="Active" <?= $exam['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
<option value="Inactive" <?= $exam['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
</select>

<label>Target Position</label>
<input type="text" name="target_position" value="<?= htmlspecialchars($exam['target_position']); ?>" required>

<button type="submit" name="update_exam" class="btn btn-primary">Update Exam</button>
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
