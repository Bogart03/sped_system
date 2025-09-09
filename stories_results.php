<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['login_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['login_id'];
$name    = $_SESSION['login_name']; // adjust if your session variable differs

// Fetch results for this user
$stmt = $conn->prepare("
    SELECT id, correct, total_questions, played_at
    FROM stories_game_results
    WHERE user_id = ?
    ORDER BY played_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📖 Stories Game Results</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="text-center mb-4">Hello, <?php echo htmlspecialchars($name); ?> 👋</h2>
    <h4 class="text-center mb-4">Your Stories Game Results</h4>

    <div class="card shadow">
      <div class="card-body">
        <?php if ($results->num_rows > 0): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
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
            No results found yet. Play the Stories Game to see your progress!
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-3 text-center">
      <a href="home.php" class="btn btn-primary">🎮 Back to Game Hub</a>
      <a href="results_hub.php" class="btn btn-secondary">📊 Back to Results Hub</a>
    </div>
  </div>
</body>
</html>
