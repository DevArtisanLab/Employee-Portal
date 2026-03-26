<?php
require_once __DIR__ . '/../config/auth_hr.php';
require_once __DIR__ . '/../config/db.php';

// ==============================
// GET LOGGED-IN USER
// ==============================
$user_id = $_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [
    'full_name' => 'Admin User',
    'position' => 'HR',
    'profile_photo' => null
];

function getInitials($name){
    $words = explode(" ", $name);
    $initials = "";
    foreach($words as $w){
        $initials .= strtoupper($w[0]);
    }
    return substr($initials,0,2);
}

$avatarInitials = getInitials($user['full_name']);
$avatarPhoto = $user['profile_photo'];


// ==============================
// DASHBOARD STATS (WEIGHTED)
// ==============================

$totalEmployees = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$activeEmployees = $pdo->query("
    SELECT COUNT(*) FROM users 
    WHERE status != 'inactive'
")->fetchColumn();

// Low Score Employees (weighted per employee)
$lowScoreEmployees = $pdo->query("
    SELECT COUNT(*) FROM (
        SELECT employee_id,
               SUM(score) / SUM(total) * 100 AS weighted_avg
        FROM exam_results
        GROUP BY employee_id
        HAVING weighted_avg < 85
    ) AS sub
")->fetchColumn();

$totalExamsTaken = $pdo->query("SELECT COUNT(*) FROM exam_results")->fetchColumn();

// ✅ Company Average (Weighted)
$companyAverage = $pdo->query("
    SELECT ROUND(SUM(score) / NULLIF(SUM(total),0) * 100, 2)
    FROM exam_results
")->fetchColumn() ?? 0;

$participants = $pdo->query("
    SELECT COUNT(DISTINCT employee_id) 
    FROM exam_results
")->fetchColumn() ?? 0;




// ==============================
// TOP PERFORMING DEPARTMENT (WEIGHTED)
// ==============================

$topDeptName = "N/A";
$topDeptScore = 0;

try {
    $stmt = $pdo->query("
        SELECT d.name,
               ROUND(SUM(er.score) / NULLIF(SUM(er.total),0) * 100, 2) AS dept_avg
        FROM exam_results er
        JOIN users u ON er.employee_id = u.id
        JOIN departments d ON u.department_id = d.id
        GROUP BY d.id
        ORDER BY dept_avg DESC
        LIMIT 1
    ");

    $topDept = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($topDept) {
        $topDeptName = $topDept['name'];
        $topDeptScore = $topDept['dept_avg'];
    }

} catch (Exception $e) {}


// ==============================
// AVERAGE SCORE PER EXAM (WEIGHTED)
// ==============================

$chartLabels = [];
$chartData = [];
$chartColors = [];

$stmt = $pdo->query("
    SELECT e.title,
           ROUND(SUM(er.score) / NULLIF(SUM(er.total),0) * 100, 2) AS exam_avg
    FROM exams e
    LEFT JOIN exam_results er ON e.id = er.exam_id
    GROUP BY e.id
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $chartLabels[] = $row['title'];
    $avg = $row['exam_avg'] ?? 0;
    $chartData[] = $avg;

    if ($avg >= 75) {
        $chartColors[] = '#10b981';
    } elseif ($avg >= 50) {
        $chartColors[] = '#f59e0b';
    } else {
        $chartColors[] = '#ef4444';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root {
--primary-color:#4f46e5; --secondary-color:#818cf8;
--bg-color:#f3f4f6; --sidebar-bg:#1f2937;
--text-light:#ffffff; --text-dark:#1f2937;
--card-bg:#ffffff; --danger:#ef4444; --success:#10b981;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{
    display:flex;
    background-color:var(--bg-color);
    min-height:100vh;
}
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
.content-area{padding:30px;}
.section-title{margin-bottom:20px;color:var(--text-dark);}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-bottom:30px;}
.card{background:var(--card-bg);padding:25px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:space-between;}
.card-info h3{font-size:28px;margin-bottom:5px;}
.card-info p{color:#6b7280;font-size:14px;}
.card-icon{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;}
.bg-blue{background:#e0e7ff;color:var(--primary-color);}
.bg-green{background:#d1fae5;color:var(--success);}
.bg-orange{background:#ffedd5;color:#f97316;}
.bg-purple{background:#f3e8ff;color:#9333ea;}
.chart-container{background:var(--card-bg);padding:20px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);}
</style>
</head>
<body>

<nav class="sidebar">
    <div class="logo"><i class="fa-solid fa-users-rectangle"></i> HR Portal</div>
    <ul class="nav-links">
        <li><a href="index.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="manage_users.php"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
        <li><a href="manage_exams.php"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
        <li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
        <li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
        <a href="../config/logout.php" class="logout-btn" onclick="return confirmLogout();">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </ul>

    <!-- Profile Footer -->
  <div class="profile-footer" style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px;">
    <div class="avatar" style="width:40px; height:40px; font-size:16px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background-color:#818cf8; color:white; border-radius:50%;">
      <?= $avatarInitials ?>
    </div>
    <div style="color:white;">
      <div style="font-size:14px; font-weight:600;"><?= htmlspecialchars($user['full_name'] ?? 'Admin User') ?></div>
      <div style="font-size:12px; color:#d1d5db;"><?= htmlspecialchars($user['position'] ?? 'HR Manager') ?></div>
    </div>
  </div>
</nav>

<div class="main-content">
<div class="content-area">
<h2 class="section-title">Overview</h2>

<div class="stats-grid">
    <div class="card">
        <div class="card-info"><h3><?= $totalEmployees ?></h3><p>Total Employees</p></div>
        <div class="card-icon bg-blue"><i class="fa-solid fa-users"></i></div>
    </div>
    <div class="card">
        <div class="card-info"><h3><?= $activeEmployees ?></h3><p>Active Employees</p></div>
        <div class="card-icon bg-green"><i class="fa-solid fa-user-check"></i></div>
    </div>
    <div class="card">
        <div class="card-info"><h3><?= $lowScoreEmployees ?></h3><p>Employees with Low Scores</p></div>
        <div class="card-icon bg-orange"><i class="fa-solid fa-circle-exclamation"></i></div>
    </div>

    <div class="card">
        <div class="card-info"><h3><?= $companyAverage ?>%</h3><p>Company Average Score</p></div>
        <div class="card-icon bg-blue"><i class="fa-solid fa-chart-line"></i></div>
    </div>

</div>

<h3 class="section-title">Average Score per Exam</h3>
<div class="chart-container">
    <canvas id="examChart" height="89"></canvas>
</div>

</div>
</div>

<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

const ctx = document.getElementById('examChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Average Score (%)',
            data: <?= json_encode($chartData) ?>,
            backgroundColor: <?= json_encode($chartColors) ?>,
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
    enabled: true,
    callbacks: {
        label: function(context) {
            return context.parsed.y.toFixed(2) + "%";
        }
    }
}
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    stepSize: 10
                }
            }
        }
    }
});
</script>
</body>
</html>