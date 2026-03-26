<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth_hr.php';

$question_id = $_GET['id'] ?? null;
$exam_id = $_GET['exam_id'] ?? null;

if(!$question_id || !$exam_id){
    header("Location: manage_exams.php");
    exit();
}

// Fetch question
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id=?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$question){
    header("Location: manage_exams.php");
    exit();
}

$success = "";
$error = "";

// Define supported types
$types = [
    "multiple_choice",
    "true_false",
    "identification",
    "enumeration",
    "fill_in_the_blank",
    "essay"
];


// ================= UPDATE =================
if(isset($_POST['update_question'])){

    $question_text = trim($_POST['question']);
    $question_type = $_POST['question_type'];

    $option_a = $_POST['option_a'] ?? null;
    $option_b = $_POST['option_b'] ?? null;
    $option_c = $_POST['option_c'] ?? null;
    $option_d = $_POST['option_d'] ?? null;
    $correct_answer = $_POST['correct_answer'] ?? null;

    // ================= TYPE CONVERSION =================
    if($question_type == "multiple_choice"){
        $db_type = "MCQ";
    }
    elseif($question_type == "true_false"){
        $db_type = "True/False";
    }
    elseif($question_type == "identification"){
        $db_type = "Identification";
    }
    elseif($question_type == "enumeration"){
        $db_type = "Enumeration";
    }
    elseif($question_type == "fill_in_the_blank"){
        $db_type = "FITB";
    }
    elseif($question_type == "essay"){
        $db_type = "Essay";
    }
    else{
        $db_type = $question_type;
    }


    // TYPE RULES
    if($question_type == "essay"){
        $option_a = $option_b = $option_c = $option_d = null;
        $correct_answer = null;
    }

    if($question_type == "true_false"){
        $option_a = "True";
        $option_b = "False";
        $option_c = $option_d = null;
    }

    if($question_type == "fill_in_the_blank" || $question_type == "identification" || $question_type == "enumeration"){
        $option_a = $option_b = $option_c = $option_d = null;
    }

    if(empty($question_text)){
        $error = "Question cannot be empty.";
    } else {

        $stmt = $pdo->prepare("
            UPDATE questions SET
                question=?,
                question_type=?,
                option_a=?,
                option_b=?,
                option_c=?,
                option_d=?,
                correct_answer=?
            WHERE id=?
        ");

        $stmt->execute([
            $question_text,
            $db_type,
            $option_a,
            $option_b,
            $option_c,
            $option_d,
            $correct_answer,
            $question_id
        ]);

        $success = "Question updated successfully!";
        
        // Refresh question data
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE id=?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


// ================= DELETE =================
if(isset($_POST['delete_question'])){
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id=?");
    $stmt->execute([$question_id]);

    header("Location: view_exam.php?id=".$exam_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Question</title>

<style>
body{
font-family:Segoe UI;
padding:40px;
background:#f3f4f6;
}

.card{
background:white;
padding:30px;
border-radius:12px;
max-width:750px;
margin:auto;
}

input, select, textarea{
width:100%;
padding:10px;
margin-bottom:15px;
border-radius:8px;
border:1px solid #ccc;
}

button{
padding:10px 20px;
border:none;
border-radius:8px;
cursor:pointer;
}

.update-btn{
background:#4f46e5;
color:white;
}

.delete-btn{
background:#ef4444;
color:white;
margin-left:10px;
}

.success{
background:#10b981;
color:white;
padding:10px;
border-radius:6px;
margin-bottom:15px;
}

.error{
background:#ef4444;
color:white;
padding:10px;
border-radius:6px;
margin-bottom:15px;
}

.hidden{
display:none;
}
</style>

</head>
<body>

<div class="card">

<h2>Edit Question</h2>

<?php if(!empty($success)): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<?php if(!empty($error)): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<label>Question</label>
<textarea name="question" required><?= htmlspecialchars($question['question']); ?></textarea>

<label>Question Type</label>

<select name="question_type" id="question_type" required>

<?php foreach($types as $type): 

$display = ucfirst(str_replace('_',' ',$type));

$current = strtolower($question['question_type']);

$selected = "";

if($type == "multiple_choice" && $current == "mcq") $selected="selected";
if($type == "true_false" && $current == "true/false") $selected="selected";
if($type == "identification" && $current == "identification") $selected="selected";
if($type == "enumeration" && $current == "enumeration") $selected="selected";
if($type == "fill_in_the_blank" && $current == "fitb") $selected="selected";
if($type == "essay" && $current == "essay") $selected="selected";

?>

<option value="<?= $type ?>" <?= $selected ?>>
<?= $display ?>
</option>

<?php endforeach; ?>

</select>


<div id="options_section">

<div id="mc_options">

<label>Option A</label>
<input type="text" name="option_a" value="<?= htmlspecialchars($question['option_a']); ?>">

<label>Option B</label>
<input type="text" name="option_b" value="<?= htmlspecialchars($question['option_b']); ?>">

<label>Option C</label>
<input type="text" name="option_c" value="<?= htmlspecialchars($question['option_c']); ?>">

<label>Option D</label>
<input type="text" name="option_d" value="<?= htmlspecialchars($question['option_d']); ?>">

</div>


<div id="correct_section">

<label>Correct Answer</label>
<input type="text" name="correct_answer"
value="<?= htmlspecialchars($question['correct_answer']); ?>">

</div>

</div>


<button type="submit" name="update_question" class="update-btn">
Update Question
</button>

<button type="submit" name="delete_question"
class="delete-btn"
onclick="return confirm('Are you sure you want to delete this question?')">
Delete Question
</button>

</form>

<br>

<a href="view_exam.php?id=<?= $exam_id; ?>">← Back to Exam</a>

</div>


<script>

const typeSelect = document.getElementById('question_type');
const mcOptions = document.getElementById('mc_options');
const correctSection = document.getElementById('correct_section');

function updateUI(){

const type = typeSelect.value;

if(type === "multiple_choice"){
mcOptions.style.display = "block";
correctSection.style.display = "block";
}

else if(type === "true_false"){
mcOptions.style.display = "none";
correctSection.style.display = "block";
}

else if(type === "fill_in_the_blank" || type === "identification" || type === "enumeration"){
mcOptions.style.display = "none";
correctSection.style.display = "block";
}

else if(type === "essay"){
mcOptions.style.display = "none";
correctSection.style.display = "none";
}

}

typeSelect.addEventListener("change", updateUI);

updateUI();

</script>

</body>
</html>