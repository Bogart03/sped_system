<?php
session_start();
include("db_connect.php");

// Allow only Admin (1) and Faculty (2)
if (!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])) {
    header("location: login.php");
    exit;
}

// Fetch all results with student info
$query = "
    SELECT s.id, u.name AS student_name, r.correct, r.total_questions, r.played_at
    FROM stories_game_results r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.played_at DESC
";
$results = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📖 Stories Game Results (All Students)</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">📖 Stories Game Results</h2>
    <h5 class="text-center mb-4">Admin / Faculty View</h5>

    <div class="card shadow">
      <div class="card-body">
        <?php if ($results->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Student Name</th>
                  <th>Score</th>
                  <th>Mistakes</th>
                  <th>Total Questions</th>
                  <th>Date Played</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $i = 1;
                while ($row = $results->fetch_assoc()): 
                    $mistakes = $row['total_questions'] - $row['correct'];
                ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo $row['correct']; ?></td>
                    <td><?php echo $mistakes; ?></td>
                    <td><?php echo $row['total_questions']; ?></td>
                    <td><?php echo date("M d, Y h:i A", strtotime($row['played_at'])); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info text-center">
            No Stories Game results found yet.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-3 text-center">
      <a href="home.php" class="btn btn-primary">🏠 Back to Dashboard</a>
      <a href="results_hub.php" class="btn btn-secondary">📊 Back to Results Hub</a>
    </div>
  </div>
</body>
</html>
