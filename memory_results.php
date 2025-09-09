<?php
session_start();

// Allow only Admin (1) and Faculty (2)
if (!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';

// Reset all results (Only Admin can reset)
if (isset($_GET['reset_all'])) {
    if ($_SESSION['login_user_type'] == 1) { // Only Admin allowed
        $conn->query("DELETE FROM memory_game_results");
        $_SESSION['message'] = "✅ All memory game results have been reset.";
        header("Location: memory_results.php");
        exit;
    } else {
        $_SESSION['message'] = "⚠️ Only Admin can reset all results.";
        header("Location: memory_results.php");
        exit;
    }
}

// Delete single record (Admin & Faculty can delete individual records)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM memory_game_results WHERE id = $id");
    $_SESSION['message'] = "🗑️ Record deleted successfully.";
    header("Location: memory_results.php");
    exit;
}

// Fetch results
$results = $conn->query("
    SELECT m.id, u.name as student_name, m.moves, m.time_taken, m.played_at
    FROM memory_game_results m
    INNER JOIN users u ON m.user_id = u.id
    ORDER BY m.played_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <title>Memory Game Results</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f0f4ff; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 1rem; box-shadow: 0 6px 14px rgba(0,0,0,.1); }
        .btn-icon { display: inline-flex; align-items: center; gap: 8px; border-radius: 30px; transition: all 0.2s ease-in-out; }
        .btn-icon:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
        .table thead th { background: #0056b3; color: white; }
        .table-hover tbody tr:hover { background: #eaf2ff; }
        .alert-primary { border-radius: 10px; font-size: 1.2rem; }
        .best-score { background: #d4edda !important; font-weight: bold; }
        .badge-moves { background: #17a2b8; }
        .badge-time { background: #ffc107; color: #000; }
        #searchName { padding-left: 30px; }
        .search-wrapper { position: relative; }
        .search-wrapper i { position: absolute; top: 50%; left: 10px; transform: translateY(-50%); color: #888; }
    </style>
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="container admin py-4">
    <div class="alert alert-primary d-flex align-items-center">
        <i class="fa fa-gamepad me-2"></i> Memory Game Results
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">

           <!-- Controls -->
<div class="d-flex justify-content-between flex-wrap mb-3">
    <div>
        <?php if ($_SESSION['login_user_type'] == 1): ?>
        <a href="?reset_all=1" class="btn btn-danger btn-sm btn-icon"
           onclick="return confirm('Are you sure you want to reset ALL memory game results?')">
            <i class="fas fa-broom"></i> Reset All
        </a>
        <?php endif; ?>

        <a href="export_memory_results_pdf.php" class="btn btn-secondary btn-sm btn-icon">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>

    <div class="form-inline mt-2 mt-md-0 search-wrapper">
        <i class="fas fa-search"></i>
        <input type="text" id="searchName" class="form-control form-control-sm rounded-pill" placeholder="Search student...">
    </div>
</div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center" id="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Moves</th>
                            <th>Time (s)</th>
                            <th>Played At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; if ($results && $results->num_rows > 0): ?>
                            <?php while($row = $results->fetch_assoc()): ?>
                                <?php
                                // Highlight rows with best (lowest) moves or time
                                $rowClass = ($row['moves'] <= 20 || $row['time_taken'] <= 30) ? "best-score" : "";
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                                    <td><span class="badge badge-moves"><?= htmlspecialchars($row['moves']) ?></span></td>
                                    <td><span class="badge badge-time"><?= htmlspecialchars($row['time_taken']) ?></span></td>
                                    <td><?= date("M d, Y h:i A", strtotime($row['played_at'])) ?></td>
                                    <td>
                                        <a href="?delete_id=<?= $row['id'] ?>"
                                           class="btn btn-outline-danger btn-sm btn-icon delete-btn"
                                           onclick="return confirm('Delete this record?')">
                                            <i class="fas fa-trash-alt"></i> Delete
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

<!-- Sound + Mini Confetti -->
<audio id="hoverSound" src="https://www.myinstants.com/media/sounds/click1.mp3" preload="auto"></audio>
<audio id="clickSound" src="https://www.myinstants.com/media/sounds/button-click.mp3" preload="auto"></audio>
<audio id="successSound" src="https://www.myinstants.com/media/sounds/success-fanfare-trumpets.mp3"></audio>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function(){
    var table = $('#table').DataTable({
        pageLength: 10,
        lengthChange: false,
        order: [[4, 'desc']],
        columnDefs: [{ orderable: false, targets: [0,5] }],
        language: {
            emptyTable: "No memory game results found."
        }
    });

    $('#searchName').on('keyup', function(){
        table.column(1).search(this.value).draw();
    });

    // Sound Effects
    const hoverSound = document.getElementById('hoverSound');
    const clickSound = document.getElementById('clickSound');
    const successSound = document.getElementById('successSound');

    $(document).on('mouseenter', '.btn-icon', function() {
        hoverSound.currentTime = 0;
        hoverSound.play();
    });

    $(document).on('click', '.btn-icon', function() {
        clickSound.currentTime = 0;
        clickSound.play();
    });

    // 🎉 Play success sound + confetti if message exists
    <?php if(isset($_SESSION['message'])): ?>
        successSound.play();
        confetti({ particleCount: 120, spread: 70, origin: { x: 0.5, y: 0.5 } });
    <?php endif; ?>
});
</script>
</body>
</html>
