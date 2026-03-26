<?php
require_once __DIR__ . '/../config/auth_hr.php'; // ensures user must be logged in
require_once __DIR__ . '/../config/db.php';      // PDO connection

// ==========================
// Logged-in user info
// ==========================
$user_id = $_SESSION['user_id'];
$stmtUser = $pdo->prepare("SELECT full_name, position, profile_photo FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [
    'full_name'=>'Admin User',
    'position'=>'HR Manager',
    'profile_photo'=>null
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


// ==========================
// HANDLE GRADING
// ==========================
if(isset($_POST['grade_submit'])){

    $result_id = $_POST['result_id'] ?? null;
    $grades = $_POST['grades'] ?? [];

    if($result_id){

        $stmt = $pdo->prepare("SELECT answers, score, essay_score FROM exam_results WHERE id=?");
        $stmt->execute([$result_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result){

            $answers = json_decode($result['answers'], true);
            $currentScore = floatval($result['score']);
            $oldEssayScore = floatval($result['essay_score'] ?? 0);

            $totalEssayScore = 0;

            foreach($answers as $qid => $answer){

                $stmtQ = $pdo->prepare("SELECT question_type, max_score FROM questions WHERE id=?");
                $stmtQ->execute([$qid]);
                $question = $stmtQ->fetch(PDO::FETCH_ASSOC);

                if($question && $question['question_type'] === 'Essay'){

                    $inputScore = floatval($grades[$qid] ?? 0);
                    $maxScore = floatval($question['max_score']);

                    // VALIDATION
                    if($inputScore > $maxScore){
                        $inputScore = $maxScore;
                    }
                    if($inputScore < 0){
                        $inputScore = 0;
                    }

                    $totalEssayScore += $inputScore;
                }
            }

            // Recalculate final score
            $finalScore = $currentScore - $oldEssayScore + $totalEssayScore;

            $stmtUpdate = $pdo->prepare("
                UPDATE exam_results
                SET score = ?, essay_score = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$finalScore, $totalEssayScore, $result_id]);
        }
    }

    header("Location: essay_grading.php?graded=1");
    exit();
}


// ==========================
// FETCH UNGRADED ESSAYS
// ==========================
$stmt = $pdo->query("
    SELECT er.id as result_id, er.employee_id, er.exam_id, er.answers, er.date_taken,
           er.score, er.essay_score,
           u.full_name as employee_name,
           e.title as exam_title
    FROM exam_results er
    JOIN users u ON er.employee_id = u.id
    JOIN exams e ON er.exam_id = e.id
    WHERE er.essay_score IS NULL
    ORDER BY er.date_taken DESC
");
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================
// GET ESSAY QUESTIONS
// ==========================
function getEssayQuestions($answers, $pdo){

    $essayQuestions = [];

    foreach($answers as $qid => $answer){

        $stmt = $pdo->prepare("SELECT question, question_type, max_score FROM questions WHERE id=?");
        $stmt->execute([$qid]);
        $q = $stmt->fetch(PDO::FETCH_ASSOC);

        if($q && $q['question_type']==='Essay'){
            $essayQuestions[$qid] = [
                'question' => $q['question'],
                'answer' => $answer,
                'max_score' => $q['max_score']
            ];
        }
    }

    return $essayQuestions;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Essay Grading</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* === YOUR ORIGINAL CSS (UNCHANGED) === */
:root {
    --primary-color: #4f46e5;
    --secondary-color: #818cf8;
    --bg-color: #f9fafb;
    --sidebar-bg: #1f2937;
    --text-light: #ffffff;
    --text-dark: #1f2937;
    --card-bg: #ffffff;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{display:flex;background-color:var(--bg-color);height:100vh;overflow:hidden;}
.sidebar{width:260px;background-color:var(--sidebar-bg);color:var(--text-light);display:flex;flex-direction:column;padding:20px;}
.logo{font-size:24px;font-weight:bold;margin-bottom:40px;text-align:center;color:var(--secondary-color);}
.nav-links{list-style:none;flex-grow:1;}
.nav-links li{margin-bottom:10px;}
.nav-links a{text-decoration:none;color:#9ca3af;padding:12px 15px;display:flex;align-items:center;gap:15px;border-radius:8px;transition:0.2s;font-size:15px;}
.nav-links a:hover,.nav-links a.active{background-color:var(--primary-color);color:white;}
.logout-btn{margin-top:auto;background-color:rgba(239,68,68,0.1);color:#ef4444;}
.main-content{flex:1;display:flex;flex-direction:column;overflow-y:auto;}
.top-header{background-color:#fff;padding:15px 30px;display:flex;justify-content:flex-end;align-items:center;}
.user-profile{display:flex;align-items:center;gap:15px;}
.avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;background-color:var(--secondary-color);}
.content-area{padding:30px;}
.section-title{margin-bottom:20px;color:var(--text-dark);font-size:22px;font-weight:600;}
.card{border-radius:15px;background:var(--card-bg);box-shadow:0 8px 25px rgba(0,0,0,0.06);padding:20px;margin-bottom:20px;}
.essay-question{background:#f3f4f6;border-left:4px solid var(--primary-color);padding:15px;margin:15px 0;border-radius:8px;}
input[type=number]{width:120px;padding:7px;border-radius:5px;border:1px solid #ccc;}
.btn-elegant{width:100%;background:linear-gradient(135deg,#4f46e5,#818cf8);color:#fff;padding:10px 0;border-radius:12px;font-weight:600;font-size:15px;margin-top:15px;border:none;cursor:pointer;}
.empty-msg{text-align:center;padding:50px;font-size:18px;color:#6b7280;}
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
        <li><a href="essay_grading.php" class="active"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
        <li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
        
    <!-- Logout Button (NOW AT VERY BOTTOM) -->
    <a href="../config/logout.php" 
       class="logout-btn"
       onclick="return confirmLogout();"
       style="margin-top:15px;">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
    </ul>

    <!-- Login Info (NOW ABOVE LOGOUT) -->
    <div class="profile-footer" style="padding-top:20px; border-top:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px; margin-top:auto;">
        <div class="avatar" style="width:40px; height:40px; font-size:16px; flex-shrink:0;">
            <?php if(isset($avatarPhoto) && $avatarPhoto && file_exists("../uploads/{$avatarPhoto}")): ?>
                <img src="../uploads/<?= htmlspecialchars($avatarPhoto) ?>" alt="Profile" style="width:100%; height:100%; border-radius:50%;">
            <?php else: ?>
                <?= $avatarInitials ?>
            <?php endif; ?>
        </div>
        <div style="color:white;">
            <div style="font-size:14px; font-weight:600;">
                <?= htmlspecialchars($user['full_name'] ?? 'Admin User') ?>
            </div>
            <div style="font-size:12px; color:#9ca3af;">
                <?= htmlspecialchars($user['position'] ?? 'HR Manager') ?>
            </div>
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
<div class="avatar"><?= $avatarInitials; ?></div>
</div> -->
</header>

<div class="content-area">
<h2 class="section-title">Essay Grading</h2>

<?php if(empty($submissions)): ?>
<div class="empty-msg">No ungraded essay submissions found!</div>
<?php else: ?>

<?php foreach($submissions as $submission): 
$answers = json_decode($submission['answers'], true);
$essayQuestions = getEssayQuestions($answers, $pdo);
if(empty($essayQuestions)) continue;
?>

<form method="POST">
<input type="hidden" name="result_id" value="<?= $submission['result_id'] ?>">

<div class="card">
<h3><?= htmlspecialchars($submission['employee_name']) ?> - <?= htmlspecialchars($submission['exam_title']) ?></h3>
<p>Current Score: <?= htmlspecialchars($submission['score']) ?></p>

<?php foreach($essayQuestions as $qid => $q): ?>
<div class="essay-question">
<p><strong>Question:</strong> <?= htmlspecialchars($q['question']) ?></p>
<p><strong>Answer:</strong> <?= nl2br(htmlspecialchars($q['answer'])) ?></p>

<label>
Score (Max <?= $q['max_score'] ?>):
<input type="number"
       name="grades[<?= $qid ?>]"
       min="0"
       max="<?= $q['max_score'] ?>"
       required>
</label>
</div>
<?php endforeach; ?>

<button type="submit" name="grade_submit" class="btn-elegant">Save Grade</button>
</div>
</form>

<?php endforeach; ?>
<?php endif; ?>

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
