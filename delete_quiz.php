<?php
session_start();
include 'db_connect.php';

if (!isset($_POST['id'])) {
    echo 0;
    exit;
}

$id = intval($_POST['id']);

// First, delete questions belonging to this quiz
$conn->query("DELETE FROM questions WHERE qid = $id");

// Then, delete the quiz itself
$delete = $conn->query("DELETE FROM quiz_list WHERE id = $id");

if ($delete) {
    echo 1; // ✅ success
} else {
    echo 0; // ❌ fail
}
?>
