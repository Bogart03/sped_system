<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['id'])) exit;

$user_id = $_SESSION['id'];
$question = $_POST['question'] ?? '';
$user_answer = $_POST['user_answer'] ?? '';
$correct_answer = $_POST['correct_answer'] ?? '';

$stmt = $conn->prepare("INSERT INTO math_wrong_answers (user_id, question, user_answer, correct_answer) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $question, $user_answer, $correct_answer);
$stmt->execute();
$stmt->close();
$conn->close();
?>
