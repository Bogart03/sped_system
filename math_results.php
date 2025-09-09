<?php
session_start();
// Only allow Admin (1) and Faculty (2)
if(!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])){
    header("location: login.php");
    exit;
}
include 'db_connect.php';

// Delete single result
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM math_game_results WHERE id = $delete_id");
    header("Location: math_results.php");
    exit;
}

// Reset all results
if (isset($_GET['reset_all'])) {
    $conn->query("DELETE FROM math_game_results");
    header("Location: math_results.php");
    exit;
}

// Fetch results
$results = $conn->query("
    SELECT m.id, u.name as student_name, m.score, m.total_questions, m.played_at
    FROM math_game_results m
    INNER JOIN users u ON m.user_id = u.id
    ORDER BY m.played_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <title>Admin Math Quiz Results</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
        body { background: #f0f4ff; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 1rem; box-shadow: 0 6px 14px rgba(0,0,0,.1); }
        .btn-icon { display: inline-flex; align-items: center; gap: 8px; border-radius: 30px; transition: all 0.2s ease-in-out; }
        .btn-icon:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
        .table thead th { background: #0056b3; color: white; }
        .table-hover tbody tr:hover { background: #eaf2ff; }
        .alert-primary { border-radius: 10px; font-size: 1.2rem; }
        .perfect-score { background: #d4edda !important; font-weight: bold; }
        .progress { height: 20px; border-radius: 10px; }
        .progress-bar { font-size: 12px; font-weight: bold; }
    </style>
</head>
<body class="with-fixed-nav">
<?php include('nav_bar.php'); ?>

<div class="container admin py-4">
    <div class="alert alert-primary fs-5">📊 Math Quiz Results (Admin)</div>

    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="math_results.php?reset_all=1" class="btn btn-danger btn-sm btn-icon"
               onclick="return confirm('Are you sure you want to reset ALL math results?')">
                <i class="fas fa-trash"></i> Reset All
            </a>
            <a href="export_math_results_pdf.php" class="btn btn-secondary btn-sm btn-icon">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
        <div class="form-inline">
            <label for="searchName" class="mr-2 font-weight-bold">Search Student:</label>
            <input type="text" id="searchName" class="form-control form-control-sm" placeholder="Type name...">
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="table">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Score</th>
                            <th>Total</th>
                            <th>Percentage</th>
                            <th>Played At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    if($results && $results->num_rows > 0):
                        while($row = $results->fetch_assoc()):
                            $percentage = ($row['total_questions'] > 0) 
                                ? round(($row['score'] / $row['total_questions']) * 100, 2) 
                                : 0;

                            // 🔴🟡🔵🟢 Pick color
                            if ($percentage < 50) {
                                $progressColor = "bg-danger"; // red
                            } elseif ($percentage < 80) {
                                $progressColor = "bg-warning"; // yellow
                            } elseif ($percentage < 100) {
                                $progressColor = "bg-info"; // blue
                            } else {
                                $progressColor = "bg-success"; // green
                            }

                            $rowClass = ($percentage == 100) ? "perfect-score" : "";
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['score']) ?></td>
                            <td><?= htmlspecialchars($row['total_questions']) ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar <?= $progressColor ?>" role="progressbar"
                                         style="width: <?= $percentage ?>%">
                                         <?= $percentage ?>%
                                    </div>
                                </div>
                            </td>
                            <td><?= date("M d, Y h:i A", strtotime($row['played_at'])) ?></td>
                            <td>
                                <a href="math_results.php?delete_id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-outline-danger btn-icon"
                                   onclick="return confirm('Delete this result?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🎆 Confetti Fireworks & 🎵 Sound -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<audio id="celebrationSound" src="https://www.myinstants.com/media/sounds/tada-fanfare.mp3"></audio>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function(){
    var table = $('#table').DataTable({
        pageLength: 10,
        lengthChange: false,
        order: [[5, 'desc']],
        columnDefs: [{ orderable: false, targets: [0, 6] }],
        language: {
            emptyTable: '<div class="alert alert-secondary mb-0">No results found.</div>'
        }
    });

    $('#searchName').on('keyup', function(){
        table.column(1).search(this.value).draw();
    });

    // 🎉 Fireworks + sound for perfect scores
    if ($('.perfect-score').length > 0) {
        var audio = document.getElementById("celebrationSound");
        audio.play();

        function firework(x, y) {
            confetti({
                particleCount: 120,
                spread: 70,
                startVelocity: 60,
                origin: { x, y }
            });
        }

        // 💥 Burst from 4 corners
        setTimeout(() => {
            firework(0.1, 0.1); // top-left
            firework(0.9, 0.1); // top-right
            firework(0.1, 0.9); // bottom-left
            firework(0.9, 0.9); // bottom-right
        }, 500);
    }
});
</script>
</body>
</html>
