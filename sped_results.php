<?php
session_start();
// Allow Admin (1) and Faculty (2)
if (!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';

// Delete single result
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM sped_study_results WHERE id = $delete_id");
    header("Location: sped_results.php");
    exit;
}

// Reset all results
if (isset($_GET['reset_all'])) {
    $conn->query("DELETE FROM sped_study_results");
    header("Location: sped_results.php");
    exit;
}

// Fetch available subjects dynamically
$subjects = [];
$subject_query = $conn->query("SELECT DISTINCT subject FROM sped_study_results");
while ($row = $subject_query->fetch_assoc()) {
    $subjects[] = $row['subject'];
}

// Selected subject filter
$filter_subject = $_GET['subject'] ?? '';

// Build query
$sql = "
    SELECT r.id, u.name as student_name, r.subject, r.correct, r.total_questions, r.played_at 
    FROM sped_study_results r
    INNER JOIN users u ON r.user_id = u.id
";

if ($filter_subject) {
    $sql .= " WHERE r.subject = '".$conn->real_escape_string($filter_subject)."'";
}

$sql .= " ORDER BY r.played_at DESC";
$results = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <title>SPED Study Game Results</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f7faff; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 1rem; box-shadow: 0 6px 14px rgba(0,0,0,.1); }
        .btn-icon { display: inline-flex; align-items: center; gap: 8px; border-radius: 30px; transition: all 0.2s ease-in-out; }
        .btn-icon:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
        .table thead th { background: #0066cc; color: white; }
        .table-hover tbody tr:hover { background: #eef6ff; }
        .alert-primary { border-radius: 10px; font-size: 1.2rem; }
    </style>
</head>
<body class="with-fixed-nav">
<?php include('nav_bar.php'); ?>

<div class="container admin py-4">
    <div class="alert alert-primary">📘 SPED Study Game Results (Teacher/Admin View)</div>

    <div class="d-flex justify-content-between mb-3">
        <a href="sped_results.php?reset_all=1" class="btn btn-danger btn-sm btn-icon"
           onclick="return confirm('Are you sure you want to reset ALL study game results?')">
            <i class="fas fa-trash"></i> Reset All
        </a>

        <!-- 🔍 Subject Filter -->
        <form method="get" class="form-inline">
            <label for="subject" class="mr-2 font-weight-bold">Filter by Subject:</label>
            <select name="subject" id="subject" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">All Subjects</option>
                <?php foreach($subjects as $sub): ?>
                    <option value="<?= htmlspecialchars($sub) ?>" <?= ($filter_subject == $sub ? 'selected' : '') ?>>
                        <?= htmlspecialchars($sub) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Subject</th>
                            <th>Correct</th>
                            <th>Total</th>
                            <th>Score (%)</th>
                            <th>Played At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($results && $results->num_rows > 0): ?>
                        <?php $i = 1; ?>
                        <?php while($row = $results->fetch_assoc()):
                            $percentage = ($row['total_questions'] > 0)
                                ? round(($row['correct'] / $row['total_questions']) * 100, 2)
                                : 0; ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['subject']) ?></td>
                                <td><?= htmlspecialchars($row['correct']) ?></td>
                                <td><?= htmlspecialchars($row['total_questions']) ?></td>
                                <td><?= $percentage ?>%</td>
                                <td><?= date("M d, Y h:i A", strtotime($row['played_at'])) ?></td>
                                <td>
                                    <a href="sped_results.php?delete_id=<?= $row['id'] ?>"
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

<!-- DataTables Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function(){
    $('#table').DataTable({
        pageLength: 10,
        lengthChange: false,
        order: [[6, 'desc']], // sort by Played At desc
        columnDefs: [{ orderable: false, targets: [0, 7] }],
        language: {
            emptyTable: '<div class="alert alert-secondary mb-0">No results found.</div>'
        }
    });
});
</script>
</body>
</html>
