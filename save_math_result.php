<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['login_id'])){
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user_id = intval($_SESSION['login_id']);
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;
$total_questions = isset($_POST['total_questions']) ? intval($_POST['total_questions']) : 0;

if($total_questions <= 0){
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid total questions"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO math_game_results (user_id, score, total_questions, played_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iii", $user_id, $score, $total_questions);

if($stmt->execute()){
    echo json_encode(["status" => "success", "message" => "Result saved"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}
?>
