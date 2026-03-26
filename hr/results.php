<?php
require_once __DIR__ . '/../config/auth_hr.php';
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: ['full_name'=>'Admin User','position'=>'HR','profile_photo'=>null];

function getInitials($name){
    $words = explode(" ", $name);
    $initials = "";
    foreach($words as $w){ $initials .= strtoupper($w[0]); }
    return substr($initials,0,2);
}
$avatarInitials = getInitials($user['full_name']);
$avatarPhoto = $user['profile_photo'];

/* =============================== 
   EXPORT SECTION
=================================*/
if(isset($_GET['export'])) {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=report.csv');

    $output = fopen("php://output", "w");

    /* ===== EXPORT ALL SCORES ===== */
if($_GET['export'] === "all_scores") {

    // Get all employees (only role = 'employee')
    $stmtEmp = $pdo->query("SELECT id, employee_number, full_name, branch, position, date_started 
                            FROM users 
                            WHERE role = 'employee' 
                            ORDER BY full_name");
    $employees = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

    // Get all exams
    $stmtExams = $pdo->query("SELECT id, title, passing_score FROM exams ORDER BY id");
    $exams = $stmtExams->fetchAll(PDO::FETCH_ASSOC);

    // CSV HEADER
    $header = ['Employee Number', 'Full Name', 'Branch', 'Position', 'Date Started'];
    foreach($exams as $e) {
        $header[] = $e['title']; // RAW score per exam
    }
    $header[] = 'Average (%)';
    $header[] = 'Status';
    fputcsv($output, $header);

    // Get date filter from URL if set
    $startDate = $_GET['start'] ?? null;
    $endDate = $_GET['end'] ?? null;

    foreach($employees as $emp) {

        $row = [
            $emp['employee_number'],
            $emp['full_name'],
            $emp['branch'],
            $emp['position'],
            $emp['date_started']
        ];

        $totalScore = 0;
        $totalMax = 0;
        $examPassingScores = [];

        foreach($exams as $exam) {
            // Build query with optional date filter
            $sql = "SELECT score, total FROM exam_results WHERE employee_id = ? AND exam_id = ?";
            $params = [$emp['id'], $exam['id']];

            if($startDate) {
                $sql .= " AND date_taken >= ?";
                $params[] = $startDate;
            }
            if($endDate) {
                $sql .= " AND date_taken <= ?";
                $params[] = $endDate;
            }

            $stmtScore = $pdo->prepare($sql);
            $stmtScore->execute($params);
            $res = $stmtScore->fetch(PDO::FETCH_ASSOC);

            if($res) {
                $row[] = $res['score'];
                $totalScore += $res['score'];
                $totalMax += $res['total'];
                $examPassingScores[] = $exam['passing_score'];
            } else {
                $row[] = '-';
            }
        }

        $average = ($totalMax > 0) ? round(($totalScore / $totalMax) * 100, 2) : 0;
        $row[] = $average . '%';

        $status = 'Failed';
        if(count($examPassingScores) > 0 && $average >= min($examPassingScores)) {
            $status = 'Passed';
        }
        $row[] = $status;

        fputcsv($output, $row);
    }
}
   /* ===== EXPORT PER EXAM ===== */
elseif($_GET['export'] === "per_exam") {

    fputcsv($output, ['Employee Number','Employee Name','Branch','Examination','Score','Date Taken']);

    // Get date filter from URL if set
    $startDate = $_GET['start'] ?? null;
    $endDate = $_GET['end'] ?? null;

    $sql = "
        SELECT 
            u.employee_number,
            u.full_name,
            u.branch,
            e.title AS exam_title,
            er.score,
            er.date_taken
        FROM exam_results er
        JOIN users u ON er.employee_id = u.id
        JOIN exams e ON er.exam_id = e.id
    ";

    $params = [];
    $conditions = [];

    if ($startDate) {
        $conditions[] = "er.date_taken >= ?";
        $params[] = $startDate;
    }
    if ($endDate) {
        $conditions[] = "er.date_taken <= ?";
        $params[] = $endDate;
    }

    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY e.title, u.full_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Only include employees with scores in the filtered date range
        fputcsv($output, $row);
    }
}
/* ===== EXPORT ANALYTICS PER QUESTION ===== */
elseif($_GET['export'] === "analytics") {

    fputcsv($output, [
        'Exam Title',
        'Question Number',
        'Question Type',
        'Question',
        'Correct',
        'Wrong',
        'Percentage Correct'
    ]);

    $startDate = $_GET['start'] ?? null;
    $endDate   = $_GET['end'] ?? null;

    // Convert to proper MySQL DATETIME
    if($startDate) $startDate = (new DateTime($startDate))->format('Y-m-d H:i:s');
    if($endDate)   $endDate   = (new DateTime($endDate))->format('Y-m-d H:i:s');

    $stmtQ = $pdo->query("
        SELECT 
            q.id,
            q.exam_id,
            q.question,
            q.question_type,
            q.correct_answer,
            e.title AS exam_title
        FROM questions q
        JOIN exams e ON q.exam_id = e.id
    ");

    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    foreach($questions as $q) {

        $total = 0;
        $correct = 0;

        if($q['question_type'] === 'Essay') {
            $sql = "SELECT essay_score FROM exam_results WHERE exam_id = ?";
            $params = [$q['exam_id']];

            if($startDate && $endDate){
                $sql .= " AND date_taken BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }

            $stmtEssay = $pdo->prepare($sql);
            $stmtEssay->execute($params);

            while($row = $stmtEssay->fetch(PDO::FETCH_ASSOC)) {
                if(isset($row['essay_score'])) { 
                    $total++; 
                    if($row['essay_score']>0) $correct++; 
                }
            }
        } else {
            $sql = "SELECT answers FROM exam_results WHERE exam_id = ?";
            $params = [$q['exam_id']];

            if($startDate && $endDate){
                $sql .= " AND date_taken BETWEEN ? AND ?";
                $params[] = $startDate;
                $params[] = $endDate;
            }

            $stmtTotal = $pdo->prepare($sql);
            $stmtTotal->execute($params);

            while($row = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
                $answers = json_decode($row['answers'], true);
                if(isset($answers[$q['id']])) { 
                    $total++; 
                    if(strtolower(trim($answers[$q['id']])) === strtolower(trim($q['correct_answer']))) $correct++; 
                }
            }
        }

        if($total > 0){
            $wrong = $total - $correct;
            $percentage = round(($correct / $total) * 100, 2);

            fputcsv($output, [
                $q['exam_title'],
                $q['id'],
                $q['question_type'],
                $q['question'],
                $correct,
                $wrong,
                $percentage.'%'
            ]);
        }
    }
}

fclose($output);
exit();
}

/* ===============================
   FETCH RESULTS FOR TABLE
=================================*/
$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;

$sql = "
    SELECT 
        u.id AS employee_id,
        u.employee_number,
        u.full_name AS employee_name,
        u.branch,
        u.position,
        u.date_started,
        e.title AS exam_title,
        er.score,
        er.total,
        e.passing_score,
        er.date_taken,
        er.essay_score,
        er.answers
    FROM exam_results er
    JOIN users u ON er.employee_id = u.id
    JOIN exams e ON er.exam_id = e.id
";

$params = [];
$conditions = [];
if($startDate) { $conditions[] = "er.date_taken >= ?"; $params[] = $startDate; }
if($endDate) { $conditions[] = "er.date_taken <= ?"; $params[] = $endDate; }

if(count($conditions) > 0) $sql .= " WHERE " . implode(" AND ", $conditions);
$sql .= " ORDER BY er.date_taken DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$searchQuery = $_GET['search'] ?? '';
if ($searchQuery !== '') {
    $searchQueryLower = strtolower($searchQuery);
    $results = array_filter($results, function($r) use ($searchQueryLower) {
        return strpos(strtolower($r['employee_number']), $searchQueryLower) !== false
            || strpos(strtolower($r['employee_name']), $searchQueryLower) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Results</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary-color:#4f46e5;--secondary-color:#818cf8;--bg-color:#f3f4f6;--sidebar-bg:#1f2937;--text-light:#ffffff;--text-dark:#1f2937;--card-bg:#ffffff;--danger:#ef4444;--success:#10b981;}
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
.content-area{padding:30px;}
.section-title{margin-bottom:20px;color:var(--text-dark);}
.table-container{background:var(--card-bg);padding:20px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th, td{text-align:left;padding:15px;border-bottom:1px solid #f3f4f6;}
th{color:#6b7280;font-weight:600;font-size:13px;}
.export-btn{background:var(--primary-color);color:white;padding:8px 12px;border-radius:6px;text-decoration:none;font-size:13px;margin-right:8px;}
.score-good{color:var(--success);font-weight:600;}
.score-low{color:var(--danger);font-weight:600;}
</style>
</head>
<body>
<nav class="sidebar">
<div class="logo"><i class="fa-solid fa-users-rectangle"></i> HR Portal</div>
<ul class="nav-links">
<li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
<li><a href="manage_users.php"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
<li><a href="manage_exams.php"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
<li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
<li><a href="results.php" class="active"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
<li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
<a href="../config/logout.php" class="logout-btn" onclick="return confirmLogout();" style="margin-top:15px;">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a>
</ul>
<div class="profile-footer" style="margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;gap:10px;">
<div class="avatar" style="width:40px;height:40px;font-size:16px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background-color:#818cf8;color:white;border-radius:50%;"><?= $avatarInitials ?></div>
<div style="color:white;">
<div style="font-size:14px;font-weight:600;"><?= htmlspecialchars($user['full_name'] ?? 'Admin User') ?></div>
<div style="font-size:12px;color:#d1d5db;"><?= htmlspecialchars($user['position'] ?? 'HR Manager') ?></div>
</div>
</div>
</nav>

<div class="main-content">
<div class="content-area">
<h2 class="section-title">Exam Results</h2>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;flex-wrap:wrap;gap:10px;">

    <!-- Filter Inputs (Left) -->
    <div style="display:flex;align-items:center;gap:10px;">
        <label>From: 
            <input type="datetime-local" id="startDate" value="<?= $_GET['start'] ?? '' ?>" style="padding:6px;border-radius:6px;border:1px solid #d1d5db;">
        </label>
        <label>To: 
            <input type="datetime-local" id="endDate" value="<?= $_GET['end'] ?? '' ?>" style="padding:6px;border-radius:6px;border:1px solid #d1d5db;">
        </label>
        <button id="filterBtn" style="padding:6px 12px;border-radius:6px;border:none;background-color:var(--primary-color);color:white;">Filter</button>
    </div>

    <!-- Export Buttons (Right) -->
    <div style="display:flex;align-items:center;gap:8px;">
        <a href="?export=all_scores<?= ($startDate || $endDate) ? '&start='.$startDate.'&end='.$endDate : '' ?>" class="export-btn">Export All Scores</a>
        <a href="?export=per_exam<?= ($startDate || $endDate) ? '&start='.$startDate.'&end='.$endDate : '' ?>" class="export-btn">Export Per Exam</a>
        <a href="?export=analytics<?= ($startDate || $endDate) ? '&start='.$startDate.'&end='.$endDate : '' ?>" class="export-btn">Export Analytics</a>
    </div>

</div>

<div class="table-container">
<table>
<thead>
<tr>
<th>Employee Number</th>
<th>Employee Name</th>
<th>Branch</th>
<th>Position</th>
<th>Date Started</th>
<th>Average (%)</th>
<th>Latest Date Taken</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
$employees = [];
foreach($results as $row){
    $empId = $row['employee_id'];
    if(!isset($employees[$empId])){
        $employees[$empId] = [
            'employee_number'=>$row['employee_number'],
            'employee_name'=>$row['employee_name'],
            'branch'=>$row['branch'],
            'position'=>$row['position'],
            'date_started'=>$row['date_started'],
            'scores'=>[],
            'totals'=>[],
            'latest_date_taken'=>$row['date_taken']
        ];
    }
    $employees[$empId]['scores'][] = $row['score'];
    $employees[$empId]['totals'][] = $row['total'];
    if(strtotime($row['date_taken']) > strtotime($employees[$empId]['latest_date_taken'])){
        $employees[$empId]['latest_date_taken'] = $row['date_taken'];
    }
}

foreach($employees as $empId => $emp):
$totalScore = array_sum($emp['scores']);
$totalPoints = array_sum($emp['totals']);
$average = ($totalPoints > 0) ? round(($totalScore/$totalPoints)*100,2) : 0;
$class = ($average >= 85) ? "score-good" : "score-low";
?>
<tr>
<td><?= htmlspecialchars($emp['employee_number']) ?></td>
<td><?= htmlspecialchars($emp['employee_name']) ?></td>
<td><?= htmlspecialchars($emp['branch']) ?></td>
<td><?= htmlspecialchars($emp['position']) ?></td>
<td><?= htmlspecialchars($emp['date_started']) ?></td>
<td class="<?= $class ?>"><?= $average ?>%</td>
<td><?= htmlspecialchars($emp['latest_date_taken']) ?></td>
<td><a href="view_employee_results.php?id=<?= $empId ?>" class="export-btn">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>

<script>
const searchInput = document.getElementById('liveSearch');
searchInput.addEventListener('input', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('.table-container tbody tr');
    rows.forEach(row=>{
        const empNumber=row.cells[0].textContent.toLowerCase();
        const empName=row.cells[1].textContent.toLowerCase();
        row.style.display = (empNumber.includes(filter)||empName.includes(filter)) ? '' : 'none';
    });
});
document.getElementById('filterBtn').addEventListener('click',function(){
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    const url = new URL(window.location.href);
    if(start) url.searchParams.set('start',start); else url.searchParams.delete('start');
    if(end) url.searchParams.set('end',end); else url.searchParams.delete('end');
    window.location.href = url.toString();
});
function confirmLogout(){ return confirm("Are you sure you want to logout?"); }
</script>
</body>
</html>