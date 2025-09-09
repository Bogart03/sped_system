<?php
session_start();
// Only Admin can access
if (!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 1) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';

// Delete single result
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($conn->query("DELETE FROM color_game_results WHERE id = $delete_id") === TRUE) {
        $_SESSION['message'] = "🗑️ Result deleted successfully.";
    } else {
        $_SESSION['message'] = "⚠️ Error deleting: " . $conn->error;
    }
    header("Location: color_results.php");
    exit;
}

// Reset all results (Admin only)
if (isset($_GET['reset_all'])) {
    if ($conn->query("TRUNCATE TABLE color_game_results") === TRUE) {
        $_SESSION['message'] = "✅ All color game results have been reset.";
    } else {
        $_SESSION['message'] = "⚠️ Error: " . $conn->error;
    }
    header("Location: color_results.php");
    exit;
}

// Fetch results
$results = $conn->query("
    SELECT c.id, u.name as student_name, c.score, c.attempts, c.created_at
    FROM color_game_results c
    INNER JOIN users u ON c.student_id = u.id
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <title>Admin - Color Game Results</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f9f9ff; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 1rem; box-shadow: 0 6px 14px rgba(0,0,0,.1); }
        .btn-icon { display: inline-flex; align-items: center; gap: 8px; border-radius: 30px; transition: all 0.2s ease-in-out; }
        .btn-icon:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
        .table thead th { background: #6c63ff; color: white; }
        .table-hover tbody tr:hover { background: #eef2ff; }
        .alert-primary { border-radius: 10px; font-size: 1.2rem; }
        .perfect-score { background: #d4edda !important; font-weight: bold; }
        .progress { height: 20px; border-radius: 10px; }
        .progress-bar { font-size: 12px; font-weight: bold; }
    </style>
</head>
<body class="with-fixed-nav">
<?php include('nav_bar.php'); ?>

<div class="container admin py-4">
    <div class="alert alert-primary">🎨 Color Game Results (Admin)</div>

    <!-- ✅ Show session messages -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-info">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="color_results.php?reset_all=1" class="btn btn-danger btn-sm btn-icon"
               onclick="return confirm('Are you sure you want to reset ALL color game results?')">
                <i class="fas fa-trash"></i> Reset All
            </a>
            <a href="export_color_results_pdf.php" class="btn btn-sm btn-secondary btn-icon">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>

        <!-- 🔍 Student Search -->
        <div class="form-inline">
            <label for="searchName" class="mr-2 font-weight-bold">Search by Student:</label>
            <input type="text" id="searchName" class="form-control form-control-sm" placeholder="Enter student name">
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Attempts</th>
                            <th>Percentage</th>
                            <th>Played At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($results && $results->num_rows > 0): ?>
                        <?php $i = 1; ?>
                        <?php while($row = $results->fetch_assoc()):
                            $percentage = ($row['attempts'] > 0)
                                ? round(($row['score'] / $row['attempts']) * 100, 2)
                                : 0;

                            $rowClass = ($percentage == 100) ? "perfect-score" : "";

                            if ($percentage < 50) {
                                $barClass = "bg-danger";
                            } elseif ($percentage < 80) {
                                $barClass = "bg-warning";
                            } elseif ($percentage < 100) {
                                $barClass = "bg-info";
                            } else {
                                $barClass = "bg-success";
                            }
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['score']) ?></td>
                                <td><?= htmlspecialchars($row['attempts']) ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar <?= $barClass ?>" role="progressbar"
                                             style="width: <?= $percentage ?>%">
                                             <?= $percentage ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?= date("M d, Y h:i A", strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="color_results.php?delete_id=<?= $row['id'] ?>"
                                       class="btn btn-sm btn-outline-danger btn-icon"
                                       onclick="return confirm('Delete this result?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🎉 Confetti & Sound for Perfect Scores -->
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
        order: [[5, 'desc']], // sort by Played At
        columnDefs: [{ orderable: false, targets: [0, 6] }],
        language: {
            emptyTable: '<div class="alert alert-secondary mb-0">No color game results found.</div>'
        }
    });

    // 🔍 Search
    $('#searchName').on('keyup', function(){
        table.column(1).search(this.value).draw();
    });

    // 🎆 Celebrate perfect scores
    if ($('.perfect-score').length > 0) {
        var audio = document.getElementById("celebrationSound");
        audio.play();

        function fireworkBurst(x, y) {
            confetti({
                particleCount: 100,
                spread: 70,
                startVelocity: 60,
                origin: { x, y }
            });
        }

        setTimeout(() => {
            fireworkBurst(0.2, 0.7);
            fireworkBurst(0.8, 0.7);
            fireworkBurst(0.5, 0.3);
            fireworkBurst(0.5, 0.9);
        }, 400);
    }
});
</script>
</body>
</html>
