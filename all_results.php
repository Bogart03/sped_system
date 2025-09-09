<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['login_id'])){
    header("Location: login.php");
    exit;
}

// Only admin (type = 1) can access
if($_SESSION['login_user_type'] != 1){
    echo "Access denied.";
    exit;
}

// Fetch all math results (you can union with other games later)
$query = "SELECT r.id, s.name as student_name, r.score, r.total_questions, r.played_at
          FROM math_game_results r
          INNER JOIN students s ON r.user_id = s.id
          ORDER BY r.played_at DESC";
$results = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include('header.php'); ?>
  <title>All Quiz Results</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include('nav_bar.php'); ?>
<div class="container mt-5">
  <h3 class="mb-3"><i class="fas fa-chart-line"></i> All Student Quiz Results</h3>
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Student</th>
        <th>Score</th>
        <th>Total Questions</th>
        <th>Percentage</th>
        <th>Played At</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $i = 1;
      while($row = $results->fetch_assoc()): 
        $percent = $row['total_questions'] > 0 ? round(($row['score'] / $row['total_questions']) * 100, 2) : 0;
      ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($row['student_name']) ?></td>
        <td><?= $row['score'] ?></td>
        <td><?= $row['total_questions'] ?></td>
        <td><?= $percent ?>%</td>
        <td><?= $row['played_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
