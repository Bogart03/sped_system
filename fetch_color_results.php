<?php
session_start();
if(!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type']!=1){
    http_response_code(403); exit("Not authorized");
}
include 'db_connect.php';

$qry = $conn->query("SELECT r.id,r.student_id,r.score,r.attempts,r.created_at,u.name as student_name FROM color_game_results r LEFT JOIN users u ON r.student_id=u.id ORDER BY r.created_at DESC");

$rows=[];
while($row=$qry->fetch_assoc()) $rows[]=$row;
header("Content-Type: application/json");
echo json_encode($rows);
?>
