<?php
session_start();
// Allow only Admin (1) and Faculty/Teacher (2)
if(!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])){
    header("location: login.php");
    exit;
}

include("db_connect.php");

// Fetch overall results
$sql_results = "
    SELECT mgr.id, u.name as student_name, mgr.moves, mgr.time_taken, mgr.played_at
    FROM math_game_results mgr
    INNER JOIN users u ON mgr.student_id = u.id
    ORDER BY mgr.played_at DESC
";
$results = $conn->query($sql_results);

// Fetch wrong answers
$sql_wrong = "
    SELECT mwa.id, u.name as student_name, mwa.question, mwa.user_answer, mwa.correct_answer, mwa.played_at
    FROM math_wrong_answers mwa
    INNER JOIN users u ON mwa.user_id = u.id
    ORDER BY mwa.played_at DESC
";
$wrong_answers = $conn->query($sql_wrong);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include('header.php'); ?>
  <title>Math Teacher Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="container py-4">
  <div class="alert alert-primary fw-bold">📘 Math Teacher/Admin Dashboard</div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="results-tab" data-bs-toggle="tab" data-bs-target="#results" type="button" role="tab">📊 Overall Results</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="wrong-tab" data-bs-toggle="tab" data-bs-target="#wrong" type="button" role="tab">❌ Wrong Answers</button>
    </li>
  </ul>

  <div class="tab-content mt-3" id="dashboardTabsContent">
    <!-- Overall Results -->
    <div class="tab-pane fade show active" id="results" role="tabpanel">
      <div class="card shadow">
        <div class="card-body">
          <table class="table table-bordered table-hover" id="resultsTable">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Moves</th>
                <th>Time Taken (sec)</th>
                <th>Played At</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($results && $results->num_rows > 0): ?>
                <?php $i = 1; ?>
                <?php while($row = $results->fetch_assoc()): ?>
                  <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td class="text-center"><?= $row['moves'] ?></td>
                    <td class="text-center"><?= $row['time_taken'] ?></td>
                    <td class="text-center"><?= date("M d, Y h:i A", strtotime($row['played_at'])) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No game results yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Wrong Answers -->
    <div class="tab-pane fade" id="wrong" role="tabpanel">
      <div class="mb-3">
        <label for="searchName" class="form-label fw-bold">🔍 Search by Student:</label>
        <input type="text" id="searchName" class="form-control" placeholder="Type student name...">
      </div>
      <div class="card shadow">
        <div class="card-body">
          <table class="table table-bordered table-hover" id="wrongAnswersTable">
            <thead class="table-dark text-center">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Question</th>
                <th>Student Answer</th>
                <th>Correct Answer</th>
                <th>Played At</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($wrong_answers && $wrong_answers->num_rows > 0): ?>
                <?php $i = 1; ?>
                <?php while($row = $wrong_answers->fetch_assoc()): ?>
                  <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['question']) ?></td>
                    <td class="text-danger text-center"><b><?= htmlspecialchars($row['user_answer']) ?></b></td>
                    <td class="text-success text-center"><b><?= htmlspecialchars($row['correct_answer']) ?></b></td>
                    <td class="text-center"><?= date("M d, Y h:i A", strtotime($row['played_at'])) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No wrong answers recorded yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
  $('#resultsTable').DataTable({ pageLength: 10, lengthChange: false, order: [[4,'desc']] });

  var wrongTable = $('#wrongAnswersTable').DataTable({
    pageLength: 10,
    lengthChange: false,
    order: [[5,'desc']]
  });

  // 🔍 Filter wrong answers by student
  $('#searchName').on('keyup', function(){
    wrongTable.column(1).search(this.value).draw();
  });
});
</script>
</body>
</html>
