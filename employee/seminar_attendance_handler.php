<?php
session_start();
require_once "../config/db.php";
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'),true);
$seminar_id = $input['seminar_id'] ?? null;
$action = $input['action'] ?? null;
$employee_id = $_SESSION['user_id'];

if(!$seminar_id || !$action){
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit();
}

// Check if attendance row exists
$stmt = $pdo->prepare("SELECT * FROM seminar_attendance WHERE seminar_id=? AND employee_id=?");
$stmt->execute([$seminar_id,$employee_id]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

$now = date('Y-m-d H:i:s');

if(!$attendance){
    $stmtInsert = $pdo->prepare("INSERT INTO seminar_attendance (seminar_id, employee_id, time_in) VALUES (?,?,?)");
    if($action==='time_in'){
        $stmtInsert->execute([$seminar_id,$employee_id,$now]);
        echo json_encode(['success'=>true]); exit();
    } else {
        echo json_encode(['success'=>false,'message'=>'You must Time In first']); exit();
    }
} else {
    $fields = ['break_in','break_out','time_out'];
    if(!in_array($action,$fields)){
        echo json_encode(['success'=>false,'message'=>'Invalid action']); exit();
    }
    $stmtUpdate = $pdo->prepare("UPDATE seminar_attendance SET $action=? WHERE id=?");
    $stmtUpdate->execute([$now,$attendance['id']]);
    echo json_encode(['success'=>true]); exit();
}
?>
