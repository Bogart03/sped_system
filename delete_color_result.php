<?php
session_start();
if(!isset($_SESSION['login_user_type'])||$_SESSION['login_user_type']!=1){header("location: login.php");exit;}
include 'db_connect.php';

if(isset($_GET['id'])){
    $id=intval($_GET['id']);
    $stmt=$conn->prepare("DELETE FROM color_game_results WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
}
header("location: color_game_results.php");
exit;
?>
