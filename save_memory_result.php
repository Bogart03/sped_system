<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['login_id'])){
    http_response_code(403);
    exit("Not logged in");
}

$user_id = $_SESSION['login_id'];
$moves = isset($_POST['moves']) ? intval($_POST['moves']) : 0;
$time_taken = isset($_POST['time_taken']) ? intval($_POST['time_taken']) : 0;

if($moves > 0 && $time_taken > 0){
    $stmt = $conn->prepare("INSERT INTO memory_game_results (user_id, moves, time_taken) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $moves, $time_taken);
    $stmt->execute();
    echo "success";
} else {
    echo "invalid data";
}
?>
