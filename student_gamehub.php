<?php
session_start();
if (!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 3) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
  <h3>🎮 Welcome, <?= $_SESSION['username'] ?>!</h3>
  <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  <hr>
  <div class="list-group">
    <a href="game_color.php" class="list-group-item">🎨 Color Match</a>
    <a href="game_shape.php" class="list-group-item">🔺 Shape Match</a>
    <a href="game_shape_color.php" class="list-group-item">🎨+🔺 Shape + Color</a>
    <a href="game_memory.php" class="list-group-item">🃏 Memory Pairs</a>
  </div>
</body>
</html>
