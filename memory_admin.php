<?php 
session_start();

// Allow only Admin (1) and Faculty (2)
if(!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])){ 
    header("location: login.php");
    exit;
}

include 'db_connect.php';

// Handle reset (only Admin can reset)
if(isset($_POST['reset'])){
    if($_SESSION['login_user_type'] == 1){ // Restrict reset to Admin only
        $conn->query("DELETE FROM memory_results");
        $conn->query("DELETE FROM math_game_results");
        $conn->query("DELETE FROM word_game_results");
        $_SESSION['message'] = "All results (Memory, Math, Word) have been reset.";
    } else {
        $_SESSION['message'] = "Only Admin can reset results.";
    }
    header("Location: results_admin.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <title>All Game Results - Admin</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <?php include('nav_bar.php') ?>

    <div class="container-fluid admin">
        <div class="col-md-12 alert alert-primary">All Games - Admin Panel</div>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <!-- Buttons -->
        <form method="post">
            <button type="submit" name="reset" class="btn btn-danger mb-2">
                <i class="fas fa-trash"></i> Reset All Results
            </button>
            <a href="export_all_pdf.php" class="btn btn-danger mb-2">
                <i class="fas fa-file-pdf"></i> Export All to PDF
            </a>
        </form>
        
        <div class="card mt-3">
            <div class="card-body">
                <table class="table table-bordered" id="resultsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Game</th>
                            <th>Result</th>
                            <th>Date Played</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;

                        // Memory Game
                        $memory = $conn->query("SELECT r.id, u.name as student_name, r.moves as result, r.date_played 
                                                 FROM memory_results r 
                                                 JOIN users u ON u.id = r.user_id 
                                                 ORDER BY r.date_played DESC");
                        while($row = $memory->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['student_name']; ?></td>
                            <td>Memory</td>
                            <td><?php echo $row['result']; ?> moves</td>
                            <td><?php echo $row['date_played']; ?></td>
                        </tr>
                        <?php endwhile; ?>

                        <!-- Math Game -->
                        <?php
                        $math = $conn->query("SELECT r.id, u.name as student_name, r.score, r.total_questions, r.played_at 
                                              FROM math_game_results r 
                                              JOIN users u ON u.id = r.user_id 
                                              ORDER BY r.played_at DESC");
                        while($row = $math->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['student_name']; ?></td>
                            <td>Math</td>
                            <td><?php echo $row['score'].' / '.$row['total_questions']; ?></td>
                            <td><?php echo $row['played_at']; ?></td>
                        </tr>
                        <?php endwhile; ?>

                        <!-- Word Game -->
                        <?php
                       $word = $conn->query("SELECT r.id, u.name as student_name, r.score, r.played_at 
                      FROM word_game_results r 
                      JOIN users u ON u.id = r.user_id 
                      ORDER BY r.played_at DESC");
while($row = $word->fetch_assoc()):
?>
<tr>
    <td><?php echo $i++; ?></td>
    <td><?php echo $row['student_name']; ?></td>
    <td>Word</td>
    <td><?php echo $row['score']; ?> points</td>
    <td><?php echo $row['played_at']; ?></td>
</tr>
<?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
$(document).ready(function(){
    $('#resultsTable').DataTable();
});
</script>

</body>
</html>
