<?php
include 'db_connect.php';

$student_id = $_POST['student_id'];
$memory_game_id = $_POST['memory_game_id'];
$score = $_POST['score'];

$sql = "INSERT INTO student_memory_game (student_id, memory_game_id, score, completed_at) 
        VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $student_id, $memory_game_id, $score);
$stmt->execute();

header("Location: history.php");
?>
