<?php
require_once __DIR__ . '/../config/auth_hr.php'; // ensures user must be logged in
require_once __DIR__ . '/../config/db.php';

$employee_id = $_GET['employee_id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;

if (!$employee_id || !$exam_id) {
    header("Location: results.php");
    exit();
}

// ... rest of your existing code

/* =========================
   FETCH EMPLOYEE
========================= */
$stmtEmp = $pdo->prepare("SELECT id, employee_number, full_name FROM users WHERE id = ?");
$stmtEmp->execute([$employee_id]);
$employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "Employee not found!";
    exit();
}

/* =========================
   FETCH EXAM
========================= */
$stmtExam = $pdo->prepare("SELECT id, title, passing_score FROM exams WHERE id = ?");
$stmtExam->execute([$exam_id]);
$exam = $stmtExam->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    echo "Exam not found!";
    exit();
}

/* =========================
   FETCH RESULT
========================= */
$stmtResult = $pdo->prepare("SELECT id, score, total, answers, date_taken FROM exam_results WHERE employee_id = ? AND exam_id = ?");
$stmtResult->execute([$employee_id, $exam_id]);
$result = $stmtResult->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo "No results found for this exam.";
    exit();
}

$employee_answers = json_decode($result['answers'], true);

/* =========================
   FETCH QUESTIONS
========================= */
$stmtQ = $pdo->prepare("SELECT id, question, question_type, correct_answer, max_score, option_a, option_b, option_c, option_d FROM questions WHERE exam_id = ?");
$stmtQ->execute([$exam_id]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   HANDLE OVERRIDE
========================= */
if (isset($_POST['correct_scores'])) {

    foreach ($_POST['override'] as $qid => $action) {

        if ($action === 'correct') {
            foreach ($questions as $q) {
                if ($q['id'] == $qid) {
                    $employee_answers[$qid] = $q['correct_answer'];
                    break;
                }
            }
        }

        if ($action === 'incorrect') {
            $employee_answers[$qid] = '';
        }
    }

    $new_score = 0;

    foreach ($questions as $q) {
        $ans = $employee_answers[$q['id']] ?? '';
        if (trim(strtolower($ans)) === trim(strtolower($q['correct_answer']))) {
            $new_score += $q['max_score'];
        }
    }

    $stmtUpdate = $pdo->prepare("UPDATE exam_results SET score = ?, answers = ? WHERE id = ?");
    $stmtUpdate->execute([$new_score, json_encode($employee_answers), $result['id']]);

    header("Location: view_employee_exam.php?employee_id={$employee_id}&exam_id={$exam_id}");
    exit();
}

/* =========================
   COMPUTE PERCENTAGE
========================= */
$percentage = $result['total'] > 0
    ? round(($result['score'] / $result['total']) * 100, 2)
    : 0;

$statusText = $percentage >= $exam['passing_score'] ? "PASSED" : "FAILED";
$statusClass = $percentage >= $exam['passing_score'] ? "pass" : "fail";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Exam Review</title>

<style>
body{
background:#f1f5f9;
font-family:'Segoe UI',sans-serif;
padding:40px;
}

.exam-container{
max-width:900px;
margin:auto;
background:white;
padding:40px;
border-radius:12px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.exam-header{
border-bottom:2px solid #e5e7eb;
padding-bottom:20px;
margin-bottom:30px;
}

.exam-header h1{
margin-bottom:10px;
}

.exam-meta{
display:flex;
justify-content:space-between;
font-size:14px;
color:#6b7280;
margin-bottom:10px;
flex-wrap:wrap;
}

.score-display{
font-weight:bold;
font-size:16px;
}

.pass{ color:#10b981; }
.fail{ color:#ef4444; }

.question-block{
margin-bottom:30px;
padding-bottom:20px;
border-bottom:1px solid #e5e7eb;
position:relative;
}

.question-title{
font-weight:600;
margin-bottom:15px;
}

.points{
position:absolute;
top:0;
right:0;
font-size:13px;
color:#6b7280;
}

.option{
display:flex;
align-items:center;
gap:10px;
padding:10px;
border:1px solid #e5e7eb;
border-radius:8px;
margin-bottom:8px;
background:#f9fafb;
}

.option.selected{
border:2px solid #6366f1;
background:#eef2ff;
}

.option.correct{
background:#d1fae5;
border-color:#10b981;
}

.option.wrong{
background:#fee2e2;
border-color:#ef4444;
}

.essay-answer{
padding:12px;
border-radius:8px;
margin-bottom:10px;
}

.essay-answer.correct{
background:#d1fae5;
}

.essay-answer.wrong{
background:#fee2e2;
}

.correct-answer{
background:#f3f4f6;
padding:12px;
border-radius:8px;
margin-bottom:10px;
}

.override-section{
margin-top:10px;
}

.override-section select{
padding:6px 10px;
border-radius:6px;
border:1px solid #d1d5db;
}

.submit-btn{
margin-top:20px;
background:#4f46e5;
color:white;
border:none;
padding:12px 18px;
border-radius:8px;
cursor:pointer;
font-weight:600;
}

.submit-btn:hover{
background:#4338ca;
}

.back-btn{
display:inline-block;
margin-bottom:20px;
text-decoration:none;
color:#4f46e5;
font-weight:600;
}
</style>
</head>

<body>

<div class="exam-container">

<a href="results.php" class="back-btn">← Back to Results</a>

<div class="exam-header">
    <h1><?= htmlspecialchars($exam['title']) ?></h1>

    <div class="exam-meta">
        <span><strong>Employee:</strong> <?= htmlspecialchars($employee['full_name']) ?> (<?= htmlspecialchars($employee['employee_number']) ?>)</span>
        <span><strong>Date Taken:</strong> <?= htmlspecialchars($result['date_taken']) ?></span>
    </div>

    <div class="score-display <?= $statusClass ?>">
        Score: <?= $result['score'] ?> / <?= $result['total'] ?>
        (<?= $percentage ?>%) - <?= $statusText ?>
    </div>
</div>

<form method="POST">

<?php $counter=1; foreach ($questions as $q):

    $emp_answer = $employee_answers[$q['id']] ?? '';
    $is_correct = (trim(strtolower($emp_answer)) === trim(strtolower($q['correct_answer'])));
?>

<div class="question-block">

    <div class="question-title">
        <?= $counter ?>. <?= htmlspecialchars($q['question']) ?>
    </div>

    <div class="points"><?= $q['max_score'] ?> pts</div>

    <?php if($q['question_type'] === 'MC'): ?>

        <?php foreach (['option_a','option_b','option_c','option_d'] as $opt):
            if(!empty($q[$opt])):

                $value = $q[$opt];
                $selected = ($emp_answer === $value);
                $correct = (trim(strtolower($value)) === trim(strtolower($q['correct_answer'])));
        ?>

        <div class="option 
            <?= $selected ? 'selected' : '' ?>
            <?= $correct ? 'correct' : '' ?>
            <?= ($selected && !$is_correct) ? 'wrong' : '' ?>">

            <input type="radio" disabled <?= $selected ? 'checked' : '' ?>>
            <label><?= htmlspecialchars($value) ?></label>

        </div>

        <?php endif; endforeach; ?>

    <?php else: ?>

        <div class="essay-answer <?= $is_correct ? 'correct' : 'wrong' ?>">
            <strong>Employee Answer:</strong><br>
            <?= htmlspecialchars($emp_answer ?: '[No Answer]') ?>
        </div>

        <div class="correct-answer">
            <strong>Correct Answer:</strong><br>
            <?= htmlspecialchars($q['correct_answer']) ?>
        </div>

    <?php endif; ?>

    <div class="override-section">
        <label>Override Decision:</label>
        <select name="override[<?= $q['id'] ?>]">
            <option value="nochange">No Change</option>
            <option value="correct">Mark as Correct</option>
            <option value="incorrect">Mark as Incorrect</option>
        </select>
    </div>

</div>

<?php $counter++; endforeach; ?>

<button type="submit" name="correct_scores" class="submit-btn">
Save Corrections
</button>

</form>

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
