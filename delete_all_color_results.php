<?php
session_start();
if(!isset($_SESSION['login_user_type'])||$_SESSION['login_user_type']!=1){header("location: login.php");exit;}
include 'db_connect.php';

$conn->query("TRUNCATE TABLE color_game_results");
header("location: color_game_results.php");
exit;
?>
