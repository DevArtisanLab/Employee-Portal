<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get employee info from users table
$employee_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT employee_number, full_name FROM users WHERE id = ?");
$stmt->execute([$employee_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$employee_number = $user['employee_number'] ?? '';
$employee_name = $user['full_name'] ?? '';

if (!isset($_GET['exam_id']) || empty($_GET['exam_id'])) {
    header("Location: examinations.php");
    exit();
}

$exam_id = intval($_GET['exam_id']);

// Fetch exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ? AND status = 'Active'");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die("Exam not found or not active.");
}

// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$allQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group questions by type
$groupedQuestions = [];
foreach ($allQuestions as $q) {
    $type = $q['question_type'] ?? 'OTHER';
    $groupedQuestions[$type][] = $q;
}

// Shuffle each question type separately
foreach ($groupedQuestions as $type => $questionsOfType) {
    shuffle($groupedQuestions[$type]);
}

// Merge them back while preserving type grouping
$questions = [];
foreach ($groupedQuestions as $type => $questionsOfType) {
    foreach ($questionsOfType as $q) {
        $questions[] = $q;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answer'] ?? [];

    // 1️⃣ Get all questions for this exam with max_score
    $stmtQ = $pdo->prepare("SELECT id, question_type, correct_answer, max_score FROM questions WHERE exam_id = ?");
    $stmtQ->execute([$exam_id]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $score = 0;
    $total = 0; // this will be sum of max_score

    foreach ($questions as $q) {
        $qid = $q['id'];
        $userAnswer = trim($answers[$qid] ?? '');
        $correctAnswer = trim($q['correct_answer'] ?? '');
        $type = $q['question_type'];
        $maxScore = floatval($q['max_score'] ?? 1); // default 1 if null

        $total += $maxScore; // add to total

        if ($type === 'Enumeration') {
            $correctParts = array_map('trim', explode(',', $correctAnswer));
            $userParts = array_map('trim', explode(',', $userAnswer));
            $matched = 0;
            foreach ($correctParts as $part) {
                foreach ($userParts as $u) {
                    if (strcasecmp($u, $part) === 0) {
                        $matched++;
                        break;
                    }
                }
            }
            // Score proportional to max_score
            if (count($correctParts) > 0) {
                $score += ($matched / count($correctParts)) * $maxScore;
            }
        } elseif (in_array($type, ['MCQ', 'Identification', 'True/False', 'FITB'])) {
            if (strcasecmp($userAnswer, $correctAnswer) === 0) {
                $score += $maxScore;
            }
        } elseif ($type === 'Essay') {
            // Essay can be graded later manually
        }
    }

    // Insert result
    $stmt = $pdo->prepare("
        INSERT INTO exam_results 
            (employee_id, exam_id, score, total, answers, essay_score, date_taken) 
        VALUES (?, ?, ?, ?, ?, NULL, NOW())
    ");
    $stmt->execute([
        $employee_id,
        $exam_id,
        $score,
        $total,
        json_encode($answers)
    ]);

    echo "<script>
        localStorage.removeItem('exam_{$exam_id}');
        localStorage.removeItem('exam_timer_{$exam_id}');
        alert('You have successfully completed the exam: ".addslashes($exam['title'])."');
        window.location.href = 'examinations.php';
    </script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Take Exam - <?=htmlspecialchars($exam['title'])?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f8f9fa; margin:0; padding:0; }
.sidebar { width:280px; height:100vh; position:fixed; top:0; left:0; background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%); color:white; padding-top:30px; box-shadow:2px 0 10px rgba(0,0,0,0.1); transition:transform 0.3s ease; z-index:1050; }
.sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; border-radius:8px; margin:5px 10px; transition: background-color 0.3s ease, padding-left 0.3s ease; }
.sidebar a i { margin-right:10px; }
.sidebar a:hover:not(.active){ background-color: rgba(255,255,255,0.1); padding-left:25px; }
.sidebar a.active { background: linear-gradient(45deg,#4dabf7,#1e88e5); padding-left:25px; }
.main-content { margin-left:280px; padding:30px; min-height:100vh; transition:margin-left 0.3s ease; }
.card { border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.05); padding:20px; margin-bottom:20px; background:#fff; user-select:none; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; }
h2 { color:#2c3e50; font-weight:600; margin-bottom:10px; }
.timer { font-weight:600; font-size:1.2em; color:#d9534f; margin-bottom:15px; }
.progress-container { margin-bottom:15px; }
.progress { height:20px; border-radius:10px; }
.progress-bar { font-weight:600; line-height:20px; }
.btn-primary, .btn-secondary { border-radius:25px; padding:10px 25px; font-weight:600; transition:all 0.3s ease; }
.question.completed { background-color: #e6f7ff; }
.employee-info div { margin-bottom:5px; }

/* Clickable options */
.option-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}
.option-card:hover { background-color: #f1f7ff; }
.option-card.selected { background-color: #cce5ff; border-color: #339af0; }
.option-card input[type="radio"] { display: none; }

@media(max-width:768px){
    .sidebar{ transform:translateX(-100%); }
    .sidebar.show{ transform:translateX(0); }
    .main-content{ margin-left:0; padding:20px; padding-top:100px; }
    .toggle-btn{ display:block; position:fixed; top:20px; left:20px; z-index:1100; }
}
</style>
</head>
<body>
<div id="overlay" onclick="closeSidebar()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:1040;"></div>
<div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
    <a href="seminars.php"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
    <a href="examinations.php" class="active"><i class="bi bi-pencil-square"></i> Examinations</a>
    <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
    <a href="../config/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<button class="btn btn-primary toggle-btn d-md-none" id="menuBtn"><i class="bi bi-list"></i></button>

<div class="main-content">
    <h2><?=htmlspecialchars($exam['title'])?></h2>

    <!-- Employee Info -->
    <div class="employee-info mb-3">
        <div><strong>Employee Number:</strong> <?=htmlspecialchars($employee_number)?></div>
        <div><strong>Name:</strong> <?=htmlspecialchars($employee_name)?></div>
    </div>

    <div class="timer" id="timer"></div>
    <div class="progress-container">
        <div class="progress">
            <div class="progress-bar bg-info" id="progressBar" style="width:0%">0%</div>
        </div>
    </div>

    <form method="POST" id="examForm">
    <?php foreach($questions as $index => $q): ?>
    <div class="question" data-index="<?=$index?>" style="<?=$index>0?'display:none;':''?>">
        <!-- Question Type -->
        <?php
            $typeLabel = '';
            switch($q['question_type']) {
                case 'MCQ': $typeLabel = 'Multiple Choices'; break;
                case 'Enumeration': $typeLabel = 'Enumeration'; break;
                case 'Identification': $typeLabel = 'Identification'; break;
                case 'True/False': $typeLabel = 'True/False'; break;
                case 'Essay': $typeLabel = 'Essay'; break;
                case 'FITB': $typeLabel = 'Fill in the Blanks'; break;
                default: $typeLabel = $q['question_type']; break;
            }
        ?>
        <div class="mb-2"><strong>Question <?=($index+1)?>:</strong> <?=htmlspecialchars($typeLabel)?></div>

        <div class="card">
            <?php if(!empty($q['question_description'])): ?>
    <div class="mb-2" style="font-size:14px; color:#6c757d;">
        <em><?=htmlspecialchars($q['question_description'])?></em>
    </div>
<?php endif; ?>

<p>
    <strong><?=($index+1)?>. </strong>
    <?=htmlspecialchars($q['question'])?>
</p>


            <?php 
            $type = $q['question_type'] ?? '';
            if($type === 'MCQ'): 
                $options = array_filter([$q['option_a'],$q['option_b'],$q['option_c'],$q['option_d']]);
                $letters = ['A','B','C','D'];
                foreach($options as $i => $opt): ?>
                    <div class="option-card" onclick="selectOption(this)">
                        <input class="answer-input" type="radio" data-qid="<?=$q['id']?>" name="answer[<?=$q['id']?>]" value="<?=htmlspecialchars($opt)?>" required>
                        <strong><?= $letters[$i] ?>.</strong> <?=htmlspecialchars($opt)?>
                    </div>
                <?php endforeach; 
            elseif($type === 'True/False'): 
                $choices = ['A. True','B. False'];
                foreach($choices as $opt): ?>
                    <div class="option-card" onclick="selectOption(this)">
                        <input class="answer-input" type="radio" data-qid="<?=$q['id']?>" name="answer[<?=$q['id']?>]" value="<?=substr($opt,3)?>" required>
                        <?= $opt ?>
                    </div>
                <?php endforeach;
            elseif($type === 'Enumeration' || $type === 'Identification'): ?>
                <input type="text" class="form-control answer-input" data-qid="<?=$q['id']?>" name="answer[<?=$q['id']?>]" required>
            <?php elseif($type === 'Essay' || $type === 'FITB'): ?>
                <textarea class="form-control answer-input" data-qid="<?=$q['id']?>" name="answer[<?=$q['id']?>]" rows="4" placeholder="Write your answer here..." required></textarea>
            <?php endif; ?>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary prevBtn">Previous</button>
            <button type="button" class="btn btn-primary nextBtn"><?= $index === count($questions)-1 ? 'Submit' : 'Next' ?></button>
        </div>
    </div>
    <?php endforeach; ?>
    </form>
</div>

<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');
menuBtn.addEventListener('click', toggleSidebar);
function toggleSidebar(){ sidebar.classList.toggle('show'); overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none'; menuBtn.style.display = sidebar.classList.contains('show') ? 'none' : 'block'; }
function closeSidebar(){ sidebar.classList.remove('show'); overlay.style.display='none'; menuBtn.style.display='block'; }

// Timer
const form = document.getElementById('examForm');
const timerEl = document.getElementById('timer');
const durationSeconds = <?=intval($exam['duration']) * 60?>;
let savedTime = localStorage.getItem('exam_timer_' + <?=json_encode($exam_id)?>);
if (!savedTime) savedTime = durationSeconds;
else savedTime = parseInt(savedTime);

function updateTimer() {
    let mins = Math.floor(savedTime / 60);
    let secs = savedTime % 60;
    timerEl.textContent = `Time Remaining: ${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
}
function timerTick() {
    updateTimer();
    if (savedTime <= 0) { alert("Time is up! Submitting exam."); form.submit(); return; }
    savedTime--; localStorage.setItem('exam_timer_' + <?=json_encode($exam_id)?>, savedTime);
}
setInterval(timerTick, 1000);

// Question navigation
let current = 0;
const questionsDivs = document.querySelectorAll('.question');
const progressBar = document.getElementById('progressBar');

function showQuestion(index){
    questionsDivs.forEach((q,i)=>q.style.display = i===index?'block':'none');
    current = index;
    updateProgress();
}
function updateProgress(){
    const total = questionsDivs.length;
    const percent = Math.round((current+1)/total*100);
    progressBar.style.width = percent + '%';
    progressBar.textContent = `Q${current+1} of ${total}`;
}

questionsDivs.forEach((q,index)=>{
    const nextBtn = q.querySelector('.nextBtn');
    const prevBtn = q.querySelector('.prevBtn');
    prevBtn.disabled = index===0;

    nextBtn.addEventListener('click', ()=>{
        if(index === questionsDivs.length-1){
            if(confirm("Are you sure you want to submit the exam?")){
                localStorage.removeItem('exam_' + <?=json_encode($exam_id)?>);
                localStorage.removeItem('exam_timer_' + <?=json_encode($exam_id)?>);
                form.submit();
            }
        } else showQuestion(index+1);
    });
    prevBtn.addEventListener('click', ()=>{ if(index>0) showQuestion(index-1); });
});
updateProgress();

// ============================
// Persistent LocalStorage for Answers
// ============================

// Load saved answers from localStorage
const savedAnswers = JSON.parse(localStorage.getItem('exam_' + <?=json_encode($exam_id)?>) || '{}');

// Populate inputs with saved answers on page load
document.querySelectorAll('.answer-input').forEach(input => {
    const qid = input.dataset.qid;
    if (savedAnswers[qid] !== undefined) {
        if (input.type === 'radio') {
            if (input.value === savedAnswers[qid]) {
                input.checked = true;
                input.closest('.option-card')?.classList.add('selected');
            }
        } else {
            input.value = savedAnswers[qid];
        }
    }

    // Save answers as user types/selects
    input.addEventListener('input', () => saveAnswer(qid, input));
    if(input.type === 'radio') input.addEventListener('change', () => saveAnswer(qid, input));
});

// Save answer to localStorage
function saveAnswer(qid, input){
    let value = '';
    if(input.type === 'radio'){
        value = document.querySelector(`input[name="answer[${qid}]"]:checked`)?.value || '';
        // highlight selected option
        const cards = input.closest('.card')?.querySelectorAll('.option-card') || [];
        cards.forEach(c => c.classList.remove('selected'));
        if(value !== ''){
            input.closest('.option-card')?.classList.add('selected');
        }
    } else {
        value = input.value;
    }

    savedAnswers[qid] = value;
    localStorage.setItem('exam_' + <?=json_encode($exam_id)?>, JSON.stringify(savedAnswers));
}

// Optional: auto-save every 5 seconds (extra safety)
setInterval(() => {
    localStorage.setItem('exam_' + <?=json_encode($exam_id)?>, JSON.stringify(savedAnswers));
}, 5000);


// Clickable option highlight
function selectOption(card){
    const qid = card.querySelector('input').dataset.qid;
    card.parentElement.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    const radio = card.querySelector('input');
    radio.checked = true;
    saveAnswer(qid, radio);
}

// Anti-copy
document.querySelectorAll('.question, .card').forEach(el => {
    el.addEventListener('contextmenu', e => e.preventDefault());
    el.addEventListener('copy', e => e.preventDefault());
    el.addEventListener('cut', e => e.preventDefault());
    el.addEventListener('paste', e => e.preventDefault());
});
</script>
</body>
</html>
