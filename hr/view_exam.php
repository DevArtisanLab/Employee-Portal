<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth_hr.php';
$user_id = $_SESSION['user_id'] ?? 1;

// User info
$stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();
$avatarInitials = implode('', array_map(fn($w)=>strtoupper($w[0]), explode(" ", $user['full_name'] ?? "Admin User")));
$avatarPhoto = $user['profile_photo'] ?? null;

// Exam ID
$exam_id = $_GET['id'] ?? null;
if(!$exam_id) { header("Location: manage_exams.php"); exit(); }

// Fetch exam
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id=?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();
if(!$exam){ header("Location: manage_exams.php"); exit(); }

// Fetch questions
$stmtQ = $pdo->prepare("SELECT * FROM questions WHERE exam_id=?");
$stmtQ->execute([$exam_id]);
$questions = $stmtQ->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Exam</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Same CSS as manage_exams.php */
:root {--primary-color:#4f46e5;--secondary-color:#818cf8;--bg-color:#f3f4f6;--sidebar-bg:#1f2937;--text-light:#ffffff;--text-dark:#1f2937;--card-bg:#ffffff;--danger:#ef4444;--success:#10b981;}
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
.top-header{background-color:var(--card-bg);padding:15px 30px;display:flex;justify-content:flex-end;align-items:center;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
.user-profile{display:flex;align-items:center;gap:15px;}
.avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;background-color:var(--secondary-color);}
.avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;}
.content-area{padding:30px;}
.section-title{margin-bottom:20px;color:var(--text-dark);}
.table-container{background:var(--card-bg);padding:20px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);margin-top:20px;}
table{width:100%;border-collapse:collapse;}
th, td{text-align:left;padding:15px;border-bottom:1px solid #f3f4f6;}
th{color:#6b7280;font-weight:600;font-size:13px;}
</style>
</head>
<body>
<nav class="sidebar">

    <!-- Logo -->
    <div class="logo">
        <i class="fa-solid fa-users-rectangle"></i> HR Portal
    </div>

    <!-- Navigation Links -->
    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="manage_users.php"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
        <li><a href="manage_exams.php" class="active"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
        <li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
        <li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
        
    <!-- LOGOUT (VERY BOTTOM) -->
    <a href="../config/logout.php"
       class="logout-btn"
       onclick="return confirmLogout();"
       style="margin-top:15px;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
    </ul>

    <!-- LOGIN INFO (ABOVE LOGOUT) -->
    <div class="profile-footer" style="
        margin-top:auto;
        padding-top:20px;
        border-top:1px solid rgba(255,255,255,0.2);
        display:flex;
        align-items:center;
        gap:10px;
    ">
        <div class="avatar" style="width:40px;height:40px;font-size:16px;flex-shrink:0;">
            <?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
                <img src="../uploads/<?= htmlspecialchars($avatarPhoto); ?>" alt="Profile">
            <?php else: ?>
                <?= $avatarInitials; ?>
            <?php endif; ?>
        </div>

        <div style="color:white;">
            <div style="font-size:14px;font-weight:600;">
                <?= htmlspecialchars($user['full_name'] ?? 'Admin User'); ?>
            </div>
            <div style="font-size:12px;color:#9ca3af;">
                <?= htmlspecialchars($user['position'] ?? 'HR Manager'); ?>
            </div>
        </div>
    </div>


</nav>

<div class="main-content">
<!-- <header class="top-header"> -->
<div class="user-profile">
  <!-- <div>
    <h4><?= htmlspecialchars($user['full_name'] ?? 'Admin User'); ?></h4>
    <p><?= htmlspecialchars($user['position'] ?? 'HR Manager'); ?></p>
  </div>
  <div class="avatar">
      <?php if($avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
          <img src="../uploads/<?= htmlspecialchars($avatarPhoto); ?>" alt="Profile">
      <?php else: ?>
          <?= $avatarInitials; ?>
      <?php endif; ?>
  </div> -->
</div>
</header>

<div class="content-area">
<h2 class="section-title">View Exam: <?= htmlspecialchars($exam['title']); ?></h2>
<p><strong>Duration:</strong> <?= htmlspecialchars($exam['duration']); ?> mins</p>
<p><strong>Passing Score:</strong> <?= htmlspecialchars($exam['passing_score']); ?></p>
<p><strong>Exam Date:</strong> <?= htmlspecialchars($exam['exam_date']); ?> | <strong>Due Date:</strong> <?= htmlspecialchars($exam['due_date']); ?></p>
<p><strong>Target Position:</strong> <?= htmlspecialchars($exam['target_position']); ?></p>

<h3>Questions</h3>
<div class="table-container">
<table>
<thead>
<tr>
<th>#</th>
<th>Question</th>
<th>Type</th>
<th>Options</th>
<th>Correct Answer</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($questions as $index => $q): ?>
<tr>
<td><?= $index+1; ?></td>
<td><?= htmlspecialchars($q['question']); ?></td>
<td><?= htmlspecialchars($q['question_type']); ?></td>
<td>
A: <?= htmlspecialchars($q['option_a']); ?><br>
B: <?= htmlspecialchars($q['option_b']); ?><br>
C: <?= htmlspecialchars($q['option_c']); ?><br>
D: <?= htmlspecialchars($q['option_d']); ?>
</td>
<td><?= htmlspecialchars($q['correct_answer']); ?></td>
<td>
    <a href="edit_question.php?id=<?= $q['id']; ?>&exam_id=<?= $exam_id; ?>"
       style="color:#4f46e5;font-weight:600;">
       Edit
    </a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="manage_exams.php" class="btn btn-primary" style="margin-top:15px;">Back</a>
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
