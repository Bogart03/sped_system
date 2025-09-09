<?php
include 'db_connect.php';
include 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <title>Home | Simple Online Quiz System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { 
            background: linear-gradient(135deg, #fefefe, #e9f7fd);
        }
        .card { 
            border-radius: 1rem; 
            box-shadow: 0 8px 20px rgba(0,0,0,.1); 
            border: none;
        }
        .card-header { 
            font-weight: 600; 
            letter-spacing: .5px; 
            background: linear-gradient(90deg, #007bff, #00c6ff);
        }
        .table { border-radius: .75rem; overflow: hidden; }
        .table thead th { 
            background: #f1f8ff; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: .95rem; 
            color: #0056b3;
        }
        .table tbody tr:hover { 
            background: rgba(0,198,255,.08); 
            transition: .2s; 
        }
        .table td, .table th { vertical-align: middle; padding: 14px 12px; }
        .badge { 
            padding: .6em .85em; 
            font-size: .9rem; 
            border-radius: .5rem; 
        }
        .big-icon {
            font-size: 1.5rem; 
            vertical-align: middle; 
            transition: transform 0.2s;
            cursor: pointer;
        }
        .big-icon:hover {
            transform: scale(1.2) rotate(-5deg);
        }
        .quiz-icon { color: #17a2b8; }
        .item-icon { color: #ffc107; }
        .status-icon { color: #28a745; }
        .pending-icon { color: #fd7e14; }
    </style>
</head>
<body>
    <?php include 'nav_bar.php'; ?>

    <!-- Sound Effects -->
    <audio id="hoverSound" src="https://actions.google.com/sounds/v1/cartoon/pop.ogg" preload="auto"></audio>
    <audio id="clickSound" src="https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg" preload="auto"></audio>

    <div class="container py-5">
        <div class="card col-lg-8 mx-auto">
            <div class="card-header text-white text-center fs-4">
                <i class="fa-solid fa-star big-icon me-2"></i> Dashboard
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-center align-middle">
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-puzzle-piece big-icon quiz-icon"></i> Quiz</th>
                                <th><i class="fa-solid fa-cubes big-icon item-icon"></i> Items</th>
                                <?php if($_SESSION['login_user_type'] == 3): ?>
                                    <th>⏳ <i class="fa-solid fa-hourglass-half big-icon status-icon"></i> Status</th>
                                <?php else: ?>
                                    <th><i class="fa-solid fa-check-circle big-icon status-icon"></i> Taken</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $where = '';
                                if($_SESSION['login_user_type'] == 3){
                                    $where = " WHERE q.id IN (SELECT quiz_id FROM quiz_student_list WHERE user_id = '".$_SESSION['login_id']."') ";
                                }

                                // Fetch all quizzes
                                $qry = $conn->query("SELECT q.*, u.name as fname FROM quiz_list q LEFT JOIN users u ON q.user_id = u.id $where ORDER BY q.title ASC");
                                while($row = $qry->fetch_assoc()):

                                    // Total number of questions in quiz
                                    $total_questions = $conn->query("SELECT COUNT(id) as cnt FROM questions WHERE qid = '".$row['id']."'")->fetch_array()['cnt'];

                                    $taken_count = 0;
                                    if($_SESSION['login_user_type'] == 3){
                                        // Number of answers submitted by the student
                                        $taken_count = $conn->query("SELECT COUNT(id) as cnt FROM answers WHERE quiz_id = '".$row['id']."' AND user_id = '".$_SESSION['login_id']."'")->fetch_array()['cnt'];
                                    }

                                    // Determine status
                                    $status = ($taken_count == $total_questions && $total_questions > 0) ? 'taken' : 'pending';
                            ?>
                            <tr>
                                <td class="fw-semibold text-start ps-4">
                                    📖 <i class="fa-solid fa-book-open big-icon quiz-icon me-2"></i>
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </td>
                                <td>🔢 <i class="fa-solid fa-circle-dot item-icon me-1"></i> <?php echo $total_questions; ?></td>
                                <?php if($_SESSION['login_user_type'] == 3): ?>
                                    <td>
                                        <?php if($status == 'taken'): ?>
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-smile status-icon me-1"></i> Taken
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fa-solid fa-hourglass-start pending-icon me-1"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td><?php echo $taken_count; ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>  
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const hoverSound = document.getElementById('hoverSound');
        const clickSound = document.getElementById('clickSound');

        document.querySelectorAll('.big-icon').forEach(icon => {
            icon.addEventListener('mouseenter', () => {
                hoverSound.currentTime = 0;
                hoverSound.play();
            });
            icon.addEventListener('click', () => {
                clickSound.currentTime = 0;
                clickSound.play();
            });
        });
    </script>
</body>
</html>
