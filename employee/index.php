<?php
session_start();
require_once "../config/db.php";

/* =====================
   🔐 SECURITY CHECK
===================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

/* =====================
   📊 DASHBOARD COUNTS
===================== */

// ✅ Exams Completed
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM exam_results 
    WHERE employee_id = ?
");
$stmt->execute([$employee_id]);
$completedExams = $stmt->fetchColumn() ?? 0;

// ✅ Pending Exams (active + currently running + not completed)
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM exams e
    WHERE e.status = 'active'
    AND CURDATE() BETWEEN e.exam_date AND e.due_date
    AND NOT EXISTS (
        SELECT 1 
        FROM exam_results r
        WHERE r.exam_id = e.id
        AND r.employee_id = ?
    )
");
$stmt->execute([$employee_id]);
$pendingExams = $stmt->fetchColumn() ?? 0;

// ✅ Average Score
$stmt = $pdo->prepare("
    SELECT AVG(score / total * 100) 
    FROM exam_results 
    WHERE employee_id = ?
");
$stmt->execute([$employee_id]);
$averageScore = round($stmt->fetchColumn() ?? 0, 2);

// ✅ Seminars Attended
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM seminar_attendance 
    WHERE employee_id = ?
");
$stmt->execute([$employee_id]);
$seminarsAttended = $stmt->fetchColumn() ?? 0;

// ✅ Upcoming Seminars (future only)
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM seminars 
    WHERE seminar_date > CURDATE()
");
$upcomingSeminars = $stmt->fetchColumn() ?? 0;

// ✅ Upcoming Exams (future + active + not completed)
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM exams e
    WHERE e.status = 'active'
    AND e.exam_date > CURDATE()
    AND NOT EXISTS (
        SELECT 1 
        FROM exam_results r
        WHERE r.exam_id = e.id
        AND r.employee_id = ?
    )
");
$stmt->execute([$employee_id]);
$upcomingExams = $stmt->fetchColumn() ?? 0;

$upcomingEvents = $upcomingSeminars + $upcomingExams;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh;
    margin:0;
    color:#333;
}

/* SIDEBAR */
.sidebar {
    width:280px;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
    background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);
    color:white;
    padding-top:30px;
    box-shadow:2px 0 10px rgba(0,0,0,0.1);
    transition:transform 0.3s ease;
    z-index:1050;
}
.sidebar a {
    color:white;
    text-decoration:none;
    padding:15px 20px;
    display:block;
    transition:background-color 0.3s ease,padding-left 0.3s ease;
    border-radius:8px;
    margin:5px 10px;
}
.sidebar a i{margin-right:10px;}
.sidebar a:hover:not(.active){
    background-color: rgba(255,255,255,0.1);
    padding-left:25px;
}
.sidebar a.active {
    background: linear-gradient(45deg, #4dabf7, #1e88e5);
    padding-left:25px;
}

/* MAIN CONTENT */
.main-content {
    margin-left:280px;
    padding:30px;
    background-color:rgba(255,255,255,0.95);
    min-height:100vh;
    border-radius:20px 0 0 0;
    box-shadow:-5px 0 15px rgba(0,0,0,0.1);
    transition:margin-left 0.3s ease;
}

/* DASHBOARD CARDS */
.card{
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
    transition:all 0.3s ease;
    overflow:hidden;
    border:2px solid #e9ecef;
    position:relative;
    background:#ffffff;
}
.card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 35px rgba(0,0,0,0.15);
}
.card-body{
    padding:25px;
}
.card h3{
    font-weight:700;
    font-size:2rem;
}
.card-seminar::before,
.card-exam::before,
.card-pending::before,
.card-upcoming::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:6px;
}
.card-seminar::before{ background:#667eea; }
.card-exam::before{ background:#56ab2f; }
.card-pending::before{ background:#f093fb; }
.card-upcoming::before{ background:#74b9ff; }
h2{
    color:#2c3e50;
    font-weight:600;
    margin-bottom:20px;
}

/* MOBILE */
@media(max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);box-shadow:2px 0 15px rgba(0,0,0,0.3);}
    .main-content{margin-left:0;padding:20px;}
    .toggle-btn{
        display:block;
        position:fixed;
        top:20px;
        left:20px;
        z-index:1100;
    }
}
</style>
</head>
<body>

<!-- Overlay -->
<div id="overlay" onclick="closeSidebar()" 
style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:1040;"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
    <a href="index.php" class="active"><i class="bi bi-house-door-fill" ></i> Home</a>
    <a href="seminars.php"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
    <a href="examinations.php"><i class="bi bi-pencil-square"></i> Examinations</a>
    <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
    <a href="../config/logout.php" id="logoutLink"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Mobile toggle button -->
<button class="btn btn-primary toggle-btn d-md-none" id="menuBtn">
    <i class="bi bi-list"></i>
</button>

<div class="main-content">

    <div class="text-center mb-5">
        <h1>Welcome to Employee Portal!</h1>
        <p>Manage your seminars, exams, and profile effortlessly.</p>
    </div>

    <h2>Dashboard Overview</h2>

    <div class="row g-3">

        <div class="col-md-3">
            <div class="card card-seminar text-center h-100">
                <div class="card-body">
                    <i class="bi bi-calendar-event" style="font-size:3rem;color:#667eea;"></i>
                    <h5 class="mt-3">Seminars Attended</h5>
                    <h3><?= $seminarsAttended ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-exam text-center h-100">
                <div class="card-body">
                    <i class="bi bi-pencil-square" style="font-size:3rem;color:#56ab2f;"></i>
                    <h5 class="mt-3">Exams Completed</h5>
                    <h3><?= $completedExams ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-pending text-center h-100">
                <div class="card-body">
                    <i class="bi bi-hourglass-split" style="font-size:3rem;color:#f093fb;"></i>
                    <h5 class="mt-3">Pending Exams</h5>
                    <h3><?= $pendingExams ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-upcoming text-center h-100">
                <div class="card-body">
                    <i class="bi bi-clock" style="font-size:3rem;color:#74b9ff;"></i>
                    <h5 class="mt-3">Upcoming Events</h5>
                    <h3><?= $upcomingEvents ?></h3>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');

menuBtn.addEventListener('click', () => {
    sidebar.classList.add('show');
    overlay.style.display='block';
    menuBtn.style.display='none';
});

function closeSidebar(){
    sidebar.classList.remove('show');
    overlay.style.display='none';
    menuBtn.style.display='block';
}

overlay.addEventListener('click', closeSidebar);

// Logout confirmation
document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.getElementById('logoutLink');
    logoutLink.addEventListener('click', function(event) {
        event.preventDefault();
        const confirmLogout = confirm("Are you sure you want to logout?");
        if (confirmLogout) {
            window.location.href = "../config/logout.php";
        }
    });
});
</script>

</body>
</html>
