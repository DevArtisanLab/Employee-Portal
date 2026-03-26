<?php
require_once __DIR__ . '/../config/auth_hr.php'; // blocks access if not logged in
require_once __DIR__ . '/../config/db.php';

// Get employee ID from GET
$employee_id = $_GET['id'] ?? null;
if (!$employee_id) {
    header("Location: results.php");
    exit();
}

// Fetch employee info
$stmtEmp = $pdo->prepare("SELECT id, employee_number, full_name, branch, position, date_started FROM users WHERE id = ?");
$stmtEmp->execute([$employee_id]);
$employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "Employee not found!";
    exit();
}

// Fetch exam results
$stmtResults = $pdo->prepare("
    SELECT 
        er.id AS result_id,
        e.id AS exam_id,
        e.title AS exam_title,
        er.score,
        er.total,
        e.passing_score,
        er.date_taken
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.id
    WHERE er.employee_id = ?
    ORDER BY er.date_taken DESC
");
$stmtResults->execute([$employee_id]);
$results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

/* ===== ENTERPRISE KPI COMPUTATION ===== */
$totalExams = count($results);

$totalScore = 0;        // sum of raw scores
$totalMaxScore = 0;     // sum of total possible points
$passedCount = 0;

foreach ($results as $r) {
    if ($r['total'] > 0) {
        // accumulate total raw score and max score
        $totalScore += $r['score'];
        $totalMaxScore += $r['total'];

        // check pass/fail per exam
        $percentage = ($r['score'] / $r['total']) * 100;
        if ($percentage >= $r['passing_score']) {
            $passedCount++;
        }
    }
}

// compute overall average score as (total score / total max score) * 100
$averageScore = $totalMaxScore > 0 ? round(($totalScore / $totalMaxScore) * 100, 2) : 0;

// pass rate remains per exam
$passRate = $totalExams > 0 ? round(($passedCount / $totalExams) * 100, 1) : 0;
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Exam Results</title>

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

/* SIDEBAR (UNCHANGED) */
.sidebar{width:260px;background-color:var(--sidebar-bg);color:var(--text-light);display:flex;flex-direction:column;padding:20px;}
.logo{font-size:24px;font-weight:bold;margin-bottom:40px;text-align:center;color:var(--secondary-color);display:flex;align-items:center;justify-content:center;gap:10px;}
.nav-links{list-style:none;flex-grow:1;}
.nav-links li{margin-bottom:10px;}
.nav-links a{text-decoration:none;color:#9ca3af;padding:12px 15px;display:flex;align-items:center;gap:15px;border-radius:8px;transition:0.2s;font-size:15px;}
.nav-links a:hover,.nav-links a.active{background-color:var(--primary-color);color:white;}
.nav-links a i{width:20px;text-align:center;}
.logout-btn{margin-top:auto;background-color:rgba(239,68,68,0.1);color:var(--danger);}
.logout-btn:hover{background-color:var(--danger);color:white;}

/* MAIN CONTENT */
.main-content{flex:1;display:flex;flex-direction:column;overflow-y:auto;}
.top-header{background-color:var(--card-bg);padding:15px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
.content-area{padding:30px;}
.section-title{margin-bottom:20px;color:var(--text-dark);}
.table-container{background:var(--card-bg);padding:20px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th, td{text-align:left;padding:15px;border-bottom:1px solid #f3f4f6;}
th{color:#6b7280;font-weight:600;font-size:13px;}
.export-btn,.back-btn,.view-btn{padding:8px 12px;border-radius:6px;text-decoration:none;font-size:13px;}
.back-btn{background:var(--secondary-color);color:white;display:inline-block;margin-bottom:15px;}
.view-btn{background:#6366f1;color:white;}
.score-good{color:var(--success);font-weight:600;}
.score-low{color:var(--danger);font-weight:600;}

/* ENTERPRISE ADDITION */
.kpi-wrapper{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin:20px 0;}
.kpi-card{background:var(--card-bg);padding:20px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);}
.kpi-title{font-size:12px;text-transform:uppercase;color:#6b7280;margin-bottom:8px;}
.kpi-value{font-size:26px;font-weight:600;color:var(--primary-color);}

.progress-wrapper{background:#e5e7eb;border-radius:20px;height:8px;overflow:hidden;}
.progress-fill{height:100%;}
.progress-pass{background:var(--success);}
.progress-fail{background:var(--danger);}
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
        <li><a href="manage_exams.php"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
        <li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
        <li><a href="results.php" class="active"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
         <!-- LOGOUT -->
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
            <?php
            $user_id = $_SESSION['user_id'];
            $stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
            $stmtUser->execute([$user_id]);
            $sidebarUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

            $name = $sidebarUser['full_name'] ?? 'Admin User';
            $position = $sidebarUser['position'] ?? 'HR Manager';
            $photo = $sidebarUser['profile_photo'] ?? null;

            $initials = '';
            foreach(explode(" ", $name) as $w){
                $initials .= strtoupper($w[0]);
            }
            $initials = substr($initials,0,2);
            ?>

            <?php if($photo && file_exists("../uploads/{$photo}")): ?>
                <img src="../uploads/<?= htmlspecialchars($photo); ?>" alt="Profile">
            <?php else: ?>
                <?= $initials ?>
            <?php endif; ?>
        </div>

        <div style="color:white;">
            <div style="font-size:14px;font-weight:600;">
                <?= htmlspecialchars($name); ?>
            </div>
            <div style="font-size:12px;color:#9ca3af;">
                <?= htmlspecialchars($position); ?>
            </div>
        </div>
    </div>

   

</nav>

<div class="main-content">
<!-- <header class="top-header"><h3>Exam Results Overview</h3></header> -->
<div class="content-area">

<a href="results.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Results</a>

<h2 class="section-title">
<?= htmlspecialchars($employee['full_name']) ?> (<?= htmlspecialchars($employee['employee_number']) ?>)
</h2>
<p>
Branch: <?= htmlspecialchars($employee['branch']) ?> |
Position: <?= htmlspecialchars($employee['position']) ?> |
Date Started: <?= htmlspecialchars($employee['date_started']) ?>
</p>

<!-- KPI SECTION -->
<div class="kpi-wrapper">
<div class="kpi-card">
<div class="kpi-title">Total Exams</div>
<div class="kpi-value"><?= $totalExams ?></div>
</div>

<div class="kpi-card">
<div class="kpi-title">Average Score</div>
<div class="kpi-value"><?= $averageScore ?>%</div>
</div>

<div class="kpi-card">
<div class="kpi-title">Pass Rate</div>
<div class="kpi-value"><?= $passRate ?>%</div>
</div>

<div class="kpi-card">
<div class="kpi-title">Exams Passed</div>
<div class="kpi-value"><?= $passedCount ?></div>
</div>
</div>

<div class="table-container">
<table>
<thead>
<tr>
<th>Exam Title</th>
<th>Score</th>
<th>Total</th>
<th>Percentage</th>
<th>Performance</th>
<th>Passing Score</th>
<th>Date Taken</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php if($results): ?>
<?php foreach($results as $row): 
$percentage = ($row['total'] > 0) ? round(($row['score'] / $row['total']) * 100, 2) : 0;
$class = ($percentage >= $row['passing_score']) ? "score-good" : "score-low";
?>

<tr>
<td><?= htmlspecialchars($row['exam_title']) ?></td>
<td><?= htmlspecialchars($row['score']) ?></td>
<td><?= htmlspecialchars($row['total']) ?></td>
<td class="<?= $class ?>"><?= $percentage ?>%</td>

<td>
<div class="progress-wrapper">
<div class="progress-fill <?= $percentage >= $row['passing_score'] ? 'progress-pass' : 'progress-fail' ?>"
     style="width: <?= $percentage ?>%;">
</div>
</div>
</td>

<td><?= htmlspecialchars($row['passing_score']) ?>%</td>
<td><?= htmlspecialchars($row['date_taken']) ?></td>
<td>
<a href="view_employee_exam.php?employee_id=<?= $employee_id ?>&exam_id=<?= $row['exam_id'] ?>" class="view-btn">
View Answers
</a>
</td>
</tr>

<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="8">No exam results found for this employee.</td></tr>
<?php endif; ?>

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
