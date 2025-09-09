<?php
session_start();
if (!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 3) {
    http_response_code(403);
    echo "Not allowed";
    exit;
}

include("db_connect.php");

$student_id = $_SESSION['login_id']; // make sure login_id is stored in session
$score = isset($_POST['score']) ? intval($_POST['score']) : 0;
$attempts = isset($_POST['attempts']) ? intval($_POST['attempts']) : 0;

if ($score >= 0 && $attempts > 0) {
    $stmt = $conn->prepare("INSERT INTO color_game_results (student_id, score, attempts) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $student_id, $score, $attempts);
    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "DB Error: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Invalid data";
}
$conn->close();
?>
