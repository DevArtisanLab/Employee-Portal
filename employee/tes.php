<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>North Park Employee Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body {font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;margin:0;color:#333;}
.sidebar{width:280px;height:100vh;position:fixed;top:0;left:0;background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);color:white;padding-top:30px;box-shadow:2px 0 10px rgba(0,0,0,0.1);transition:transform 0.3s ease;}
.sidebar a{color:white;text-decoration:none;padding:15px 20px;display:block;transition:background-color 0.3s ease,padding-left 0.3s ease;border-radius:8px;margin:5px 10px;}
.sidebar a:hover{background-color:rgba(255,255,255,0.1);padding-left:25px;}
.sidebar a i{margin-right:10px;}
.main-content{margin-left:280px;padding:30px;background-color:rgba(255,255,255,0.95);min-height:100vh;border-radius:20px 0 0 0;box-shadow:-5px 0 15px rgba(0,0,0,0.1);transition:margin-left 0.3s ease;}
@media (max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.show{transform:translateX(0)}.main-content{margin-left:0}.toggle-btn{display:block;position:fixed;top:20px;left:20px;z-index:1000;}}
.card{border:none;border-radius:15px;box-shadow:0 8px 25px rgba(0,0,0,0.1);transition:transform 0.3s ease,box-shadow 0.3s ease;overflow:hidden;}
.card:hover{transform:translateY(-5px);box-shadow:0 12px 35px rgba(0,0,0,0.15);}
.card-body{padding:25px;}
.btn{border-radius:25px;padding:10px 20px;font-weight:600;transition:all 0.3s ease;}
.btn-primary{background:linear-gradient(45deg,#667eea,#764ba2);border:none;}
.btn-primary:hover{transform:scale(1.05);}
.btn-success{background:linear-gradient(45deg,#56ab2f,#a8e6cf);border:none;}
.btn-success:hover{transform:scale(1.05);}
.btn-warning{background:linear-gradient(45deg,#f093fb,#f5576c);border:none;}
.btn-warning:hover{transform:scale(1.05);}
.btn-info{background:linear-gradient(45deg,#74b9ff,#0984e3);border:none;}
.btn-info:hover{transform:scale(1.05);}
.section{display:none;animation:fadeIn 0.5s ease;}
.section.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
h2{color:#2c3e50;font-weight:600;margin-bottom:20px;}
.welcome-hero{text-align:center;margin-bottom:40px;}
.welcome-hero h1{font-size:2.5rem;background:linear-gradient(45deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.status-badge{padding:5px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;}
.status-attended{background-color:#d4edda;color:#155724;}
.status-not{background-color:#f8d7da;color:#721c24;}
.progress-bar{border-radius:10px;}
.notification-dropdown{max-height:300px;overflow-y:auto;}
.search-bar{margin-bottom:20px;}
.calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;margin-top:20px;}
.calendar-header{font-weight:600;text-align:center;padding:10px;background:#f8f9fa;border-radius:8px;}
.calendar-day{min-height:80px;border:1px solid #ddd;border-radius:8px;padding:5px;text-align:center;background:white;font-size:0.9rem;}
.calendar-day.has-event{background:#e3f2fd;border-color:#667eea;}
.calendar-day.today{background:#fff3cd;}
</style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
<h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
<a href="#" onclick="showSection('home')"><i class="bi bi-house-door-fill"></i> Home</a>
<a href="#" onclick="showSection('seminars')"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
<a href="#" onclick="showSection('exams')"><i class="bi bi-pencil-square"></i> Examinations</a>
<a href="#" onclick="showSection('profile')"><i class="bi bi-person-fill"></i> Profile</a>
<a href="#" onclick="logout()"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Mobile Toggle Button -->
<button class="btn btn-primary toggle-btn d-md-none" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>

<!-- Main Content -->
<div class="main-content">
<!-- Home -->
<div id="home" class="section active">
<div class="welcome-hero">
<h1>Welcome to Employee Portal!</h1>
<p>Manage your seminars, exams, and profile effortlessly.</p>
</div>
<div class="row g-3">
<div class="col-md-3"><div class="card text-center h-100"><div class="card-body"><i class="bi bi-calendar-event" style="font-size:3rem;color:#667eea;"></i><h5>Seminars Attended</h5><h3 id="seminarsCount">0</h3></div></div></div>
<div class="col-md-3"><div class="card text-center h-100"><div class="card-body"><i class="bi bi-pencil-square" style="font-size:3rem;color:#56ab2f;"></i><h5>Exams Completed</h5><h3 id="examsCount">0</h3></div></div></div>
<div class="col-md-3"><div class="card text-center h-100"><div class="card-body"><i class="bi bi-hourglass-split" style="font-size:3rem;color:#f093fb;"></i><h5>Pending Exams</h5><h3 id="pendingExams">0</h3></div></div></div>
<div class="col-md-3"><div class="card text-center h-100"><div class="card-body"><i class="bi bi-clock" style="font-size:3rem;color:#74b9ff;"></i><h5>Upcoming Events</h5><h3 id="upcomingCount">0</h3></div></div></div>
</div>
</div>

<!-- Seminars -->
<div id="seminars" class="section">
<h2>Seminars</h2>

<div class="search-bar">
    <button class="btn btn-info mt-2" onclick="toggleCalendarView()">Toggle Calendar View</button>
    <h4 id="current-month" class="mt-2"></h4>
</div>

<div id="seminars-list" class="row"></div>

<div id="calendar-view" style="display:none;">
<h3 id="calendar-title"></h3>
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

<!-- Exams -->
<div id="exams" class="section">
<h2>Examinations</h2>
<div class="search-bar">
<input type="text" id="examSearch" class="form-control" placeholder="Search exams by title...">
</div>
<div id="exams-list" class="row"></div>
</div>

<!-- Profile -->
<div id="profile" class="section">
<h2>Your Profile</h2>
<div class="card">
<div class="card-body">
<div class="text-center mb-3">
<img src="https://via.placeholder.com/100" alt="Profile Picture" class="rounded-circle" style="width:100px;height:100px;">
<br><button class="btn btn-secondary mt-2">Change Photo</button>
</div>
<form>
<div class="row">
<div class="col-md-6 mb-3">
<label for="firstName" class="form-label">First Name</label>
<input type="text" class="form-control" id="firstName" value="John">
</div>
<div class="col-md-6 mb-3">
<label for="lastName" class="form-label">Last Name</label>
<input type="text" class="form-control" id="lastName" value="Doe">
</div>
</div>
<div class="mb-3">
<label for="email" class="form-label">Email</label>
<input type="email" class="form-control" id="email" value="john.doe@example.com">
</div>
<div class="mb-3">
<label for="department" class="form-label">Department</label>
<select class="form-control" id="department">
<option>HR</option>
<option>IT</option>
<option>Finance</option>
</select>
</div>
<button type="submit" class="btn btn-primary">Save Changes</button>
</form>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let seminars = []; // <-- START WITH EMPTY SEMINARS
let exams = [
  {title:"Safety Quiz",dueDate:"2023-10-25",link:"#",completed:false,score:null},
  {title:"Compliance Test",dueDate:"2023-10-30",link:"#",completed:true,score:85}
];

function showSection(id){document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));document.getElementById(id).classList.add('active');}
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('show');}

function loadSeminars(filter=''){
  const container=document.getElementById('seminars-list');
  container.innerHTML='';
  seminars.filter(s=>s.title.toLowerCase().includes(filter.toLowerCase())).forEach((s,i)=>{
    const statusClass=s.attended?'status-attended':'status-not';
    const statusText=s.attended?'Attended':'Not Attended';
    const btnText=s.attended?'View Recording':'Attend';
    const markBtn=s.attended?'':`<button class="btn btn-warning mt-2" onclick="markAttendance(${i})">Mark Attendance</button>`;
    container.innerHTML+=`<div class="col-md-6 mb-4"><div class="card"><div class="card-body"><h5>${s.title}</h5><p><i class="bi bi-calendar"></i> ${s.date} at ${s.time}</p><p>${s.description}</p><span class="status-badge ${statusClass}">${statusText}</span><br><button class="btn btn-primary mt-3">${btnText}</button>${markBtn}</div></div></div>`;
  });
  updateDashboard();
}

function loadExams(filter=''){
  const container=document.getElementById('exams-list');
  container.innerHTML='';
  exams.filter(e=>e.title.toLowerCase().includes(filter.toLowerCase())).forEach((e)=>{
    const statusClass=e.completed?'status-attended':'status-not';
    const btnText=e.completed?`View Score (${e.score}%)`:'Take Exam';
    container.innerHTML+=`<div class="col-md-6 mb-4"><div class="card"><div class="card-body"><h5>${e.title}</h5><p><i class="bi bi-calendar"></i> Due: ${e.dueDate}</p><span class="status-badge ${statusClass}">${e.completed?'Completed':'Pending'}</span><br><a href="${e.link}" class="btn btn-primary mt-3">${btnText}</a></div></div></div>`;
  });
}

function toggleCalendarView(){
  const list=document.getElementById('seminars-list');
  const cal=document.getElementById('calendar-view');
  if(cal.style.display==='none'){cal.style.display='block';list.style.display='none';loadCalendar();}
  else{cal.style.display='none';list.style.display='block';}
}

function loadCalendar(){
  const grid=document.querySelector('.calendar-grid');
  grid.querySelectorAll('.calendar-day').forEach(el=>el.remove());
  const today=new Date();
  const year=today.getFullYear();
  const month=today.getMonth();
  const firstDay=new Date(year,month,1).getDay();
  const totalDays=new Date(year,month+1,0).getDate();
  for(let i=0;i<firstDay;i++){const empty=document.createElement('div');empty.classList.add('calendar-day');grid.appendChild(empty);}
  for(let day=1;day<=totalDays;day++){
    const dateStr=`${year}-${(month+1).toString().padStart(2,'0')}-${day.toString().padStart(2,'0')}`;
    const hasEvent=seminars.some(s=>s.date===dateStr);
    const events=seminars.filter(s=>s.date===dateStr).map(s=>s.title).join('<br>');
    const dayDiv=document.createElement('div');
    dayDiv.classList.add('calendar-day');
    if(hasEvent) dayDiv.classList.add('has-event');
    if(today.getDate()===day && today.getMonth()===month && today.getFullYear()===year) dayDiv.classList.add('today');
    dayDiv.innerHTML=`<strong>${day}</strong><br><small>${events}</small>`;
    grid.appendChild(dayDiv);
  }
}

function markAttendance(index){seminars[index].attended=true;loadSeminars();}
function updateDashboard(){
  document.getElementById('seminarsCount').innerText=seminars.filter(s=>s.attended).length;
  document.getElementById('examsCount').innerText=exams.filter(e=>e.completed).length;
  document.getElementById('pendingExams').innerText=exams.filter(e=>!e.completed).length;
  document.getElementById('upcomingCount').innerText=seminars.filter(s=>!s.attended).length+exams.filter(e=>!e.completed).length;
  updateNotifications();
}

function updateNotifications(){
  const dropdown=document.querySelector('.notification-dropdown');dropdown.innerHTML='';
  let notes=[];
  seminars.forEach(s=>{if(!s.attended) notes.push(`Upcoming Seminar: ${s.title} on ${s.date}`);});
  exams.forEach(e=>{if(!e.completed) notes.push(`Exam Due: ${e.title} on ${e.dueDate}`);});
  if(notes.length===0) notes.push("No new notifications");
  notes.forEach(n=>{const li=document.createElement('li');li.innerHTML=`<a class="dropdown-item" href="#">${n}</a>`;dropdown.appendChild(li);});
  document.getElementById('notificationBadge').innerText=notes.length;
}

document.querySelector('#profile form').addEventListener('submit',function(e){e.preventDefault();alert('Profile updated successfully!');});
function logout(){if(confirm("Are you sure you want to logout?")){alert("Logged out successfully!");}}
document.addEventListener('DOMContentLoaded',()=>{loadSeminars();loadExams();loadCalendar();updateDashboard();});
document.getElementById('examSearch').addEventListener('input',e=>loadExams(e.target.value));
</script>
</body>
</html>
