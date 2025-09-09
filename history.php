<?php 
session_start();
if(!isset($_SESSION['login_user_type'])) {
    header("location: login.php");
    exit;
}
include('db_connect.php');

// Delete single quiz record (Admin only)
if (isset($_GET['delete_id']) && $_SESSION['login_user_type'] == 1) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM history WHERE id = $delete_id");
    header("Location: history.php");
    exit;
}

// Reset all quiz history (Admin only)
if (isset($_GET['reset_all']) && $_SESSION['login_user_type'] == 1) {
    $conn->query("DELETE FROM history");
    header("Location: history.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <title>Quiz History</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f0f4ff; }
        .card { border-radius: 1rem; box-shadow: 0 6px 14px rgba(0,0,0,.1); }
        .btn-icon { display: inline-flex; align-items: center; gap: 8px; border-radius: 30px; transition: all 0.2s ease-in-out; }
        .btn-icon:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.15); }
        .table thead th { background: #0056b3; color: white; }
        .table-hover tbody tr:hover { background: #eaf2ff; }
        .alert-primary { border-radius: 10px; font-size: 1.2rem; }
    </style>
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="container admin py-4">
    <div class="alert alert-primary d-flex align-items-center">
        <i class="fa fa-history me-2"></i> Quiz Records
    </div>

    <div class="card">
        <div class="card-body">

           <!-- Admin Reset All & Export Buttons -->
<?php if($_SESSION['login_user_type'] == 1): ?>
<div class="d-flex justify-content-end mb-3">
    <a href="history.php?reset_all=1" class="btn btn-danger btn-sm btn-icon"
       onclick="return confirm('Are you sure you want to reset ALL quiz history?')">
        <i class="fas fa-trash-alt"></i> Reset All
    </a>

    <a href="export_history_pdf.php" class="btn btn-secondary btn-sm btn-icon ms-2">
        <i class="fas fa-file-pdf"></i> Export PDF
    </a>
</div>
<?php endif; ?> 


            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $where = '';
                        if($_SESSION['login_user_type'] == 2){
                            $where = ' WHERE h.user_id = '.$_SESSION['login_id'].' ';
                        }

                        $qry = $conn->query("SELECT h.*, u.name as student, q.title 
                                             FROM history h 
                                             INNER JOIN users u ON h.user_id = u.id 
                                             INNER JOIN quiz_list q ON h.quiz_id = q.id 
                                             $where 
                                             ORDER BY h.id DESC");
                        $i = 1;
                        if($qry->num_rows > 0){
                            while($row = $qry->fetch_assoc()){ ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars(ucwords($row['student'])) ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['score'].'/'.$row['total_score']) ?></td>
                                    <td>
                                        <?php if($_SESSION['login_user_type'] == 1): ?>
                                            <a href="history.php?delete_id=<?= $row['id'] ?>" 
                                               class="btn btn-outline-danger btn-sm btn-icon delete-btn"
                                               onclick="return confirm('Delete this record?')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- Soft Sounds -->
<audio id="hoverSound" src="https://www.myinstants.com/media/sounds/click1.mp3" preload="auto"></audio>
<audio id="clickSound" src="https://www.myinstants.com/media/sounds/button-click.mp3" preload="auto"></audio>

<script>
$(document).ready(function(){
    $('#table').DataTable({
        pageLength: 10,
        lengthChange: false,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [4] }],
        language: { emptyTable: "No records found." }
    });

    // Sound Effects
    const hoverSound = document.getElementById('hoverSound');
    const clickSound = document.getElementById('clickSound');

    $(document).on('mouseenter', '.btn-icon', function() {
        hoverSound.currentTime = 0;
        hoverSound.play();
    });

    $(document).on('click', '.btn-icon', function() {
        clickSound.currentTime = 0;
        clickSound.play();
    });
});
</script>
</body>
</html>
