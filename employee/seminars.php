<?php
session_start();
require_once "../config/db.php"; // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
if ($_SESSION['role'] !== 'Employee') {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

// Fetch seminars from database
$stmt = $pdo->prepare("SELECT * FROM seminars ORDER BY seminar_date ASC, start_time ASC");
$stmt->execute();
$seminars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch employee attendance
$stmt2 = $pdo->prepare("SELECT * FROM seminar_attendance WHERE employee_id=?");
$stmt2->execute([$employee_id]);
$attendance = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Map attendance by seminar_id
$attendanceMap = [];
foreach ($attendance as $a) {
    $attendanceMap[$a['seminar_id']] = $a['status']; // 'Attended' or 'Not Attended'
}

// Pass current datetime to JS
$currentDateTime = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seminars</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#ffffff; min-height:100vh; margin:0; color:#333; }
.sidebar { width:280px; height:100vh; position:fixed; top:0; left:0; background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%); color:white; padding-top:30px; box-shadow:2px 0 10px rgba(0,0,0,0.1); transition:transform 0.3s ease; z-index:1050; }
.sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background-color 0.3s ease,padding-left 0.3s ease; border-radius:8px; margin:5px 10px; }
.sidebar a:hover:not(.active){ background-color: rgba(255,255,255,0.1); padding-left:25px; }
.sidebar a i{margin-right:10px;}
.sidebar a.active { background: linear-gradient(45deg, #4dabf7, #1e88e5); padding-left:25px; }
.main-content { margin-left:280px; padding:30px; background-color:#ffffff; min-height:100vh; border-radius:20px 0 0 0; box-shadow:-5px 0 15px rgba(0,0,0,0.05); transition:margin-left 0.3s ease; }
h2 { color:#2c3e50; font-weight:600; margin-bottom:20px; }
.card{ border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.05); transition:all 0.3s ease; overflow:hidden; position:relative; border:2px solid #e9ecef; background:#ffffff; cursor:pointer; }
.card:hover{ transform:translateY(-6px); box-shadow:0 15px 35px rgba(0,0,0,0.15); }
.card-body{ padding:25px; }
.card h5{ font-weight:600; }
.card-seminar::before{ content:""; position:absolute; top:0; left:0; width:100%; height:6px; background:#667eea; }
.card-upcoming { background-color:#f0f0f0; color:#888; cursor:not-allowed; }
.status-badge { padding:5px 10px; border-radius:20px; font-size:0.8rem; font-weight:600; }
.status-attended { background-color:#d4edda; color:#155724; }
.status-not { background-color:#f8d7da; color:#721c24; }
.search-bar { margin-bottom:20px; }
.calendar-container { overflow-x:auto; }
.calendar-grid { display:grid; grid-template-columns:repeat(7,120px); gap:8px; margin-top:20px; min-width:700px; }
.calendar-header { font-weight:600; text-align:center; padding:6px; background:#f8f9fa; border-radius:6px; font-size:0.9rem; }
.calendar-day { min-height:60px; border:1px solid #ddd; border-radius:6px; padding:4px; text-align:center; background:white; font-size:0.75rem; line-height:1.2; word-wrap: break-word; }
.calendar-day.has-event { background:#e3f2fd; border-color:#667eea; }
.calendar-day.today { background:#fff3cd; }
@media(max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);}
    .main-content{margin-left:0;padding:15px;padding-top:90px;}
    .toggle-btn{display:block;position:fixed;top:20px;left:20px;z-index:1100;}
    .calendar-grid { grid-template-columns:repeat(7,100px); min-width:700px; gap:5px; }
    .calendar-header { padding:4px; font-size:0.7rem; }
    .calendar-day { min-height:50px; padding:3px; font-size:0.65rem; }
    .calendar-container { overflow-x:auto; }
    .search-bar { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>

<div id="overlay" onclick="closeSidebar()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:1040;"></div>

<div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
    <a href="seminars.php" class="active"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
    <a href="examinations.php"><i class="bi bi-pencil-square"></i> Examinations</a>
    <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
    <a href="../config/logout.php" id="logoutLink"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<button class="btn btn-primary toggle-btn d-md-none" id="menuBtn"><i class="bi bi-list"></i></button>

<div class="main-content">
<h2>Seminars</h2>
<div class="search-bar d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
    <button class="btn btn-info mb-2 mb-md-0" onclick="toggleCalendarView()">Toggle Calendar View</button>
    <h4 id="current-month" class="mt-2 mt-md-0"></h4>
</div>

<div id="seminars-list" class="row"></div>

<div id="calendar-view" style="display:none;">
<h3 id="calendar-title" class="mb-3"></h3>
<div class="calendar-container">
    <div class="calendar-grid" id="calendar-grid">
        <div class="calendar-header">Sun</div>
        <div class="calendar-header">Mon</div>
        <div class="calendar-header">Tue</div>
        <div class="calendar-header">Wed</div>
        <div class="calendar-header">Thu</div>
        <div class="calendar-header">Fri</div>
        <div class="calendar-header">Sat</div>
    </div>
</div>
</div>
</div>

<script>
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');
menuBtn.addEventListener('click', () => { sidebar.classList.add('show'); overlay.style.display='block'; menuBtn.style.display='none'; });
function closeSidebar(){ sidebar.classList.remove('show'); overlay.style.display='none'; menuBtn.style.display='block'; }
overlay.addEventListener('click', closeSidebar);
document.getElementById('logoutLink').addEventListener('click', function(e){ e.preventDefault(); if(confirm("Are you sure you want to logout?")) window.location.href="../config/logout.php"; });

const seminarsData = <?php echo json_encode($seminars); ?>;
const attendanceData = <?php echo json_encode($attendanceMap); ?>;
const now = new Date("<?php echo $currentDateTime; ?>");

function loadSeminars(){
    const container = document.getElementById('seminars-list');
    container.innerHTML = '';
    seminarsData.forEach(s => {
        const status = attendanceData[s.id] ?? false;

        // Seminar date & time
        const startDT = new Date(`${s.seminar_date}T${s.start_time}`);
        const endDT = new Date(`${s.seminar_date}T${s.end_time}`);

        // Hide past seminars
        if(endDT < now) return;

        let cardClass = 'card-seminar';
        let buttonHTML = '';
        let statusClass = status ? 'status-attended':'status-not';
        let statusText = status ? 'Attended':'Not Attended';

        if(now < startDT){ // Upcoming
            cardClass = 'card-upcoming';
            buttonHTML = `<button class="btn btn-secondary mt-2" disabled>Unavailable</button>`;
        } else { // Ongoing
            const btnText = status ? 'View Recording':'Attend';
            buttonHTML = status ? `<button class="btn btn-primary mt-3">View Recording</button>` :
                `<form method="POST" action="seminar_attendance_handler.php" class="mt-2">
                    <input type="hidden" name="seminar_id" value="${s.id}">
                    <button class="btn btn-warning mt-2">Mark Attendance</button>
                </form>`;
        }

        container.innerHTML += `
        <div class="col-md-6 mb-4">
          <div class="card ${cardClass}">
            <div class="card-body">
              <h5>${s.seminar_title ?? s.title}</h5>
              <p><i class="bi bi-calendar"></i> ${s.seminar_date ?? s.date}</p>
              <p><i class="bi bi-clock"></i> ${s.start_time ?? 'N/A'} - ${s.end_time ?? 'N/A'}</p>
              <p>${s.description ?? ''}</p>
              <span class="status-badge ${statusClass}">${statusText}</span><br>
              ${buttonHTML}
            </div>
          </div>
        </div>`;
    });
}

function toggleCalendarView(){
  const list = document.getElementById('seminars-list');
  const cal = document.getElementById('calendar-view');
  if(cal.style.display==='none'){ cal.style.display='block'; list.style.display='none'; loadCalendar(); } 
  else { cal.style.display='none'; list.style.display='block'; }
}

function loadCalendar(){
  const grid = document.getElementById('calendar-grid');
  grid.querySelectorAll('.calendar-day').forEach(el=>el.remove());
  const today = new Date();
  const year = today.getFullYear();
  const month = today.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const totalDays = new Date(year, month+1, 0).getDate();

  for(let i=0;i<firstDay;i++){ const empty = document.createElement('div'); empty.classList.add('calendar-day'); grid.appendChild(empty); }

  for(let day=1; day<=totalDays; day++){
    const dateStr = `${year}-${(month+1).toString().padStart(2,'0')}-${day.toString().padStart(2,'0')}`;
    const hasEvent = seminarsData.some(s=> s.seminar_date === dateStr );
    const events = seminarsData.filter(s=> s.seminar_date === dateStr ).map(s=> s.seminar_title ?? s.title ).join('<br>');
    const dayDiv = document.createElement('div');
    dayDiv.classList.add('calendar-day');
    if(hasEvent) dayDiv.classList.add('has-event');
    if(today.getDate()===day && today.getMonth()===month && today.getFullYear()===year) dayDiv.classList.add('today');
    dayDiv.innerHTML = `<strong>${day}</strong><br><small>${events}</small>`;
    grid.appendChild(dayDiv);
  }
}

document.addEventListener('DOMContentLoaded', loadSeminars);
</script>
</body>
</html>
