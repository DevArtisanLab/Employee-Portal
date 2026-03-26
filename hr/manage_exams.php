<?php
require_once __DIR__ . '/../config/auth_hr.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// ==========================
// GET LOGGED-IN USER
// ==========================

$user_id = $_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

function getInitials($name){
    $words = explode(" ", $name);
    $initials = "";
    foreach($words as $w){ $initials .= strtoupper($w[0]); }
    return substr($initials,0,2);
}

$avatarInitials = getInitials($user['full_name'] ?? 'Admin User');
$avatarPhoto = $user['profile_photo'] ?? null;

// ==========================
// HANDLE IMPORT EXCEL
// ==========================
if (isset($_POST['import_excel']) && !empty($_FILES['excel_file']['tmp_name'])) {
    $filePath = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $examIdMap = [];

    // Import Exams
    $examsSheet = $spreadsheet->getSheetByName('Exams');
    if ($examsSheet) {
        $examsData = $examsSheet->toArray(null, true, true, true);
        foreach(array_slice($examsData,1) as $row) {
            $examOrder = isset($row['A']) ? (int) trim($row['A']) : 0;
            if ($examOrder <= 0) continue;

            $title = trim($row['B'] ?? '');
            $description = trim($row['C'] ?? '');
            $examDate = $row['D'] ?? null;
            $dueDate = $row['E'] ?? null;
            $passingScore = is_numeric($row['F']) ? (int)$row['F'] : 0;
            $duration = is_numeric($row['G']) ? (int)$row['G'] : 0;
            $status = trim($row['H'] ?? 'Active');
            $targetPosition = trim($row['I'] ?? '');

            // Fix Excel date conversion
            if ($examDate) {
                if (is_numeric($examDate)) {
                    $examDate = Date::excelToDateTimeObject($examDate)->format('Y-m-d H:i:s');
                } else {
                    $examDate = date('Y-m-d H:i:s', strtotime($examDate));
                }
            }
            if ($dueDate) {
                if (is_numeric($dueDate)) {
                    $dueDate = Date::excelToDateTimeObject($dueDate)->format('Y-m-d H:i:s');
                } else {
                    $dueDate = date('Y-m-d H:i:s', strtotime($dueDate));
                }
            }

            $stmt = $pdo->prepare("INSERT INTO exams 
                (exam_order, title, description, exam_date, due_date, passing_score, duration, status, created_by, target_position, date_created)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$examOrder, $title, $description, $examDate, $dueDate, $passingScore, $duration, $status, $user_id, $targetPosition]);

            $examIdMap[$examOrder] = $pdo->lastInsertId();
        }
    }

// Import Questions
$questionsSheet = $spreadsheet->getSheetByName('Questions');
if ($questionsSheet) {
    $questionsData = $questionsSheet->toArray(null, true, true, true);

    foreach(array_slice($questionsData,1) as $row) {

        $examOrderRef = isset($row['A']) ? (int) trim($row['A']) : 0;
        $exam_id = $examIdMap[$examOrderRef] ?? null;
        if (!$exam_id) continue;

        $question = trim($row['B'] ?? '');

        // ✅ NEW DESCRIPTION COLUMN (Column C)
        $questionDescription = trim($row['C'] ?? '');

        $questionType = trim($row['D'] ?? '') ?: 'MCQ';

        $optionA = trim($row['E'] ?? '');
        $optionB = trim($row['F'] ?? '');
        $optionC = trim($row['G'] ?? '');
        $optionD = trim($row['H'] ?? '');

        $correctAnswer = trim($row['I'] ?? '');

        $maxScore = isset($row['J']) && is_numeric($row['J']) 
            ? (int)$row['J'] 
            : ($questionType === 'Essay' ? 5 : 1);

        if($maxScore <= 0){
            $maxScore = ($questionType === 'Essay') ? 5 : 1;
        }

        $stmt = $pdo->prepare("INSERT INTO questions
            (exam_id, question, question_description, question_type, 
             option_a, option_b, option_c, option_d, correct_answer, max_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $exam_id,
            $question,
            $questionDescription, // ✅ NEW
            $questionType,
            $optionA,
            $optionB,
            $optionC,
            $optionD,
            $correctAnswer,
            $maxScore
        ]);
    }
}

    header("Location: manage_exams.php?imported=1");
    exit();
}

// ==========================
// HANDLE ARCHIVE / RESTORE / DELETE
// ==========================
if(isset($_GET['archive'])){
    $examId = intval($_GET['archive']);
    $stmt = $pdo->prepare("UPDATE exams SET status='Archived' WHERE id=?");
    $stmt->execute([$examId]);
    header("Location: manage_exams.php?archived=1");
    exit();
}
if(isset($_GET['restore'])){
    $examId = intval($_GET['restore']);
    $stmt = $pdo->prepare("UPDATE exams SET status='Active' WHERE id=?");
    $stmt->execute([$examId]);
    header("Location: manage_exams.php?restored=1");
    exit();
}
if(isset($_GET['delete'])){
    $examId = intval($_GET['delete']);

    try {
        $pdo->beginTransaction();

        // Temporarily disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

        // Delete all questions for this exam
        $stmtQ = $pdo->prepare("DELETE FROM questions WHERE exam_id=?");
        $stmtQ->execute([$examId]);

        // Delete the exam itself
        $stmtE = $pdo->prepare("DELETE FROM exams WHERE id=?");
        $stmtE->execute([$examId]);

        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

        $pdo->commit();
        header("Location: manage_exams.php?deleted=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Delete failed: " . $e->getMessage());
    }
}


// ==========================
// FILTER EXAMS
// ==========================
$filter = $_GET['filter'] ?? 'active_inactive';
$whereClause = '';
if($filter === 'active'){
    $whereClause = "WHERE e.status='Active'";
} elseif($filter === 'inactive'){
    $whereClause = "WHERE e.status='Inactive'";
} elseif($filter === 'archived'){
    $whereClause = "WHERE e.status='Archived'";
} else {
    $whereClause = "WHERE e.status!='Archived'";
}

// ==========================
// FETCH EXAMS
// ==========================
$stmt = $pdo->query("
    SELECT e.id, e.title, e.duration, e.status,
    (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id) as total_questions
    FROM exams e
    $whereClause
    ORDER BY e.exam_order ASC
");
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Exams</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary-color:#4f46e5;--secondary-color:#818cf8;--bg-color:#f3f4f6;--sidebar-bg:#1f2937;--text-light:#ffffff;--text-dark:#1f2937;--card-bg:#ffffff;--danger:#ef4444;--success:#10b981;--warning:#facc15;--archived:#9ca3af;}
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
.btn{padding:6px 10px;border:none;border-radius:6px;cursor:pointer;font-size:13px;transition:0.2s;margin-right:5px;}
.btn-primary{background:var(--primary-color);color:white;}
.btn-primary:hover{background:#4338ca;}
.btn-view{background:#10b981;color:white;}
.btn-view:hover{background:#0f9e6f;}
.btn-danger{background:#ef4444;color:white;}
.btn-danger:hover{background:#dc2626;}
.btn-warning{background:var(--warning);color:#1f1f1f;}
.btn-warning:hover{background:#eab308;}
.btn-archived{background:var(--archived);color:white;}
.status-active{color:var(--success);font-weight:bold;}
.status-inactive{color:var(--warning);font-weight:bold;}
.status-archived{color:var(--archived);font-weight:bold;}
.filter-form{margin-bottom:20px;}
.import-form{margin-bottom:20px;display:flex;gap:10px;}
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
        <a href="../config/logout.php" class="logout-btn" onclick="return confirmLogout();">
  <i class="fa-solid fa-right-from-bracket"></i> Logout
</a> </ul>

    <!-- Profile at the bottom -->
    <div class="profile-footer" style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px;">
        <div class="avatar" style="width:40px; height:40px; font-size:16px; flex-shrink:0;">
            <?php if(isset($avatarPhoto) && $avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
                <img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>" alt="Profile" style="width:100%; height:100%; border-radius:50%;">
            <?php else: ?>
                <?= $avatarInitials ?>
            <?php endif; ?>
        </div>
        <div style="color:white;">
            <div style="font-size:14px; font-weight:600;"><?= htmlspecialchars($user['full_name'] ?? 'Admin User') ?></div>
            <div style="font-size:12px; color:#9ca3af;"><?= htmlspecialchars($user['position'] ?? 'HR Manager') ?></div>
        </div>
    </div>
</nav>

<div class="main-content">
<!-- <header class="top-header"> -->
<div class="user-profile">
  <!-- <div>
    <h4><?= htmlspecialchars($user['full_name']); ?></h4>
    <p><?= htmlspecialchars($user['position']); ?></p>
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
<h2 class="section-title">Manage Exams</h2>

<!-- Import Excel Form -->
<form method="POST" enctype="multipart/form-data" class="import-form">
<input type="file" name="excel_file" accept=".xlsx,.xls" required>
<button type="submit" name="import_excel" class="btn btn-primary">Import Excel</button>
</form>

<!-- Filter Form -->
<form method="GET" class="filter-form">
<label>Filter: </label>
<select name="filter" onchange="this.form.submit()">
    <option value="active_inactive" <?= $filter=='active_inactive'?'selected':'' ?>>Active & Inactive</option>
    <option value="active" <?= $filter=='active'?'selected':'' ?>>Active</option>
    <option value="inactive" <?= $filter=='inactive'?'selected':'' ?>>Inactive</option>
    <option value="archived" <?= $filter=='archived'?'selected':'' ?>>Archived</option>
</select>
</form>

<div class="table-container">
<table>
<thead>
<tr>
<th>Exam ID</th>
<th>Title</th>
<th>Duration</th>
<th>Total Questions</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($exams as $exam): ?>
<tr>
<td><?= htmlspecialchars($exam['id']); ?></td>
<td><?= htmlspecialchars($exam['title']); ?></td>
<td><?= htmlspecialchars($exam['duration']); ?> mins</td>
<td><?= $exam['total_questions']; ?></td>
<td>
<?php
if($exam['status']=='Active') echo '<span class="status-active">Active</span>';
elseif($exam['status']=='Inactive') echo '<span class="status-inactive">Inactive</span>';
else echo '<span class="status-archived">Archived</span>';
?>
</td>
<td>
<a href="view_exam.php?id=<?= $exam['id']; ?>"><button class="btn btn-view">View</button></a>
<?php if($exam['status']!='Archived'): ?>
<a href="?archive=<?= $exam['id']; ?>" onclick="return confirm('Archive this exam?')"><button class="btn btn-warning">Archive</button></a>
<?php else: ?>
<a href="?restore=<?= $exam['id']; ?>" onclick="return confirm('Restore this exam?')"><button class="btn btn-primary">Restore</button></a>
<a href="?delete=<?= $exam['id']; ?>" onclick="return confirm('Delete this exam permanently? This cannot be undone.')"><button class="btn btn-danger">Delete</button></a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
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
