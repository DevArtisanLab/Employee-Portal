<?php
session_start();
require_once "../config/db.php";

// =====================
// 🔐 SECURITY CHECK
// =====================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

// =====================
// Get employee position from users table
// =====================
$stmtPos = $pdo->prepare("SELECT position FROM users WHERE id = ?");
$stmtPos->execute([$employee_id]);
$employee_position = $stmtPos->fetchColumn();

// =====================
// Fetch exams for this employee based on their position
// =====================
$stmt = $pdo->prepare("
    SELECT * FROM exams 
    WHERE status='Active' 
      AND (target_position = 'All' OR target_position = :position)
    ORDER BY exam_order ASC
");
$stmt->execute(['position' => $employee_position]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =====================
// Fetch completed exams for this employee
// =====================
$stmt2 = $pdo->prepare("SELECT exam_id FROM exam_results WHERE employee_id = ?");
$stmt2->execute([$employee_id]);
$completedExams = $stmt2->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Examinations</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body { font-family:'Poppins',sans-serif; background:#ffffff; min-height:100vh; margin:0; color:#333; }

/* SIDEBAR */
.sidebar { width:280px; height:100vh; position:fixed; top:0; left:0; background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%); color:white; padding-top:30px; box-shadow:2px 0 10px rgba(0,0,0,0.1); transition:transform 0.3s ease; z-index:1050; }
.sidebar a { color:white;text-decoration:none;padding:15px 20px; display:block; border-radius:8px; margin:5px 10px; transition: background-color 0.3s ease, padding-left 0.3s ease; }
.sidebar a:hover:not(.active) { background-color: rgba(255,255,255,0.1); padding-left:25px; }
.sidebar a i{ margin-right:10px; }
.sidebar a.active{background: linear-gradient(45deg, #4dabf7, #1e88e5); padding-left:25px;}

/* MAIN CONTENT */
.main-content { margin-left:280px; padding:30px; background-color:#ffffff; min-height:100vh; border-radius:20px 0 0 0; box-shadow:-5px 0 15px rgba(0,0,0,0.05); transition:margin-left 0.3s ease; }

/* EXAM CARDS */
.card{ border:none; border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.05); transition:transform 0.3s ease,box-shadow 0.3s ease; overflow:hidden; position:relative; }
.card::before{ content:""; position:absolute; top:0; left:0; width:100%; height:6px; background:#56ab2f; }
.card.disabled{ pointer-events:none; opacity:0.8; }
.card-body{padding:25px;}

.btn{border-radius:25px;padding:10px 20px;font-weight:600;transition:all 0.3s ease;}
.btn-info{background:linear-gradient(45deg,#74b9ff,#0984e3);border:none;}
.btn-info:hover{transform:scale(1.05);}
.btn-secondary{background:gray;border:none;}
.btn-secondary:hover{transform:none; cursor:not-allowed;}

h2{color:#2c3e50;font-weight:600;margin-bottom:20px;}

/* MOBILE */
@media (max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);box-shadow:2px 0 15px rgba(0,0,0,0.3);}
    .main-content{margin-left:0;padding:20px;padding-top:80px;}
    .toggle-btn{display:block;position:fixed;top:20px;left:20px;z-index:1100;}
}
</style>
</head>
<body>

<div id="overlay" onclick="closeSidebar()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:1040;"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
    <a href="seminars.php"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
    <a href="examinations.php" class="active"><i class="bi bi-pencil-square"></i> Examinations</a>
    <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
    <a href="logout.php" id="logoutLink"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Mobile toggle button -->
<button class="btn btn-primary toggle-btn d-md-none" id="menuBtn"><i class="bi bi-list"></i></button>

<div class="main-content">
<h2>Examinations</h2>
<div id="exams-list" class="row g-3"></div>
</div>

<script>
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

document.querySelectorAll('.sidebar a').forEach(link=>{
    link.addEventListener('click', ()=>{
        if(window.innerWidth <= 768){
            closeSidebar();
        }
    });
});

// ✅ Logout confirmation
document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.getElementById('logoutLink');
    logoutLink.addEventListener('click', function(event) {
        event.preventDefault();
        if(confirm("Are you sure you want to logout?")){
            window.location.href = "../config/logout.php";
        }
    });
});

// =====================
// Exams data from PHP
// =====================
const examsData = <?php echo json_encode($exams); ?>;
const completedExams = <?php echo json_encode($completedExams); ?>;

function loadExams(){
    const container = document.getElementById('exams-list');
    container.innerHTML = '';

    let canTakeNext = true; // Restrict by exam_order

    examsData.forEach((exam) => {
        const now = new Date();
        const start = new Date(exam.exam_date);
        const due = new Date(exam.due_date);

        // Skip exam if past due
        if(now > due) return;

        let disabled = false;
        let btnClass = 'btn-info';
        let btnText = 'Take Exam';

        if(now < start) {
            disabled = true;
            btnText = 'Unavailable';
            btnClass = 'btn-secondary';
        }

        if(!canTakeNext) {
            disabled = true;
            btnText = 'Unavailable';
            btnClass = 'btn-secondary';
        }

        // ✅ Completed exams: keep btn-info color, not clickable
        if(completedExams.includes(exam.id)){
            disabled = true;           // lock it
            btnText = 'Completed';
            btnClass = 'btn-info';     // keep normal color
        } else if(!disabled) {
            canTakeNext = false; // lock next exams
        }

        container.innerHTML += `
        <div class="col-md-6">
            <div class="card ${disabled ? 'disabled' : ''}">
                <div class="card-body">
                    <h5>${exam.title}</h5>
                    <p><i class="bi bi-calendar-event"></i> Start: ${start.toLocaleString()}</p>
                    <p><i class="bi bi-calendar-event"></i> Due: ${due.toLocaleString()}</p>
                    <a href="${disabled ? '#' : 'take_exam.php?exam_id=' + exam.id}" class="btn ${btnClass} mt-2" ${disabled ? 'onclick="return false;"' : ''}>${btnText}</a>
                </div>
            </div>
        </div>`;
    });
}

document.addEventListener('DOMContentLoaded', loadExams);
</script>
</body>
</html>
