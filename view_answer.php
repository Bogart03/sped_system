<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    include('header.php'); 
    include('auth.php'); 
    include('db_connect.php'); 

    $quiz_id = $_GET['id'];
    $user_id = $_SESSION['login_id'];

    $quiz = $conn->query("SELECT * FROM quiz_list WHERE id = $quiz_id")->fetch_array();
    $hist = $conn->query("SELECT * FROM history WHERE quiz_id = $quiz_id AND user_id = $user_id")->fetch_array();
    ?>
    <title><?php echo htmlspecialchars($quiz['title']); ?> | Answer Sheet</title>
    <style>
        li.answer input:checked {
            background: #00c4ff3d;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="container-fluid admin">
        <div class="col-md-12 alert alert-primary">
            <?php echo htmlspecialchars($quiz['title']); ?> | <?php echo $quiz['qpoints'] . ' Points Each Question'; ?>
        </div>
        <div class="col-md-12 alert alert-success">
            SCORE : <?php echo $hist['score'] . ' / ' .  $hist['total_score']; ?>
        </div>
        <br>

        <div class="card">
            <div class="card-body">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                <input type="hidden" name="qpoints" value="<?php echo $quiz['qpoints']; ?>">

                <?php
                $question = $conn->query("SELECT * FROM questions WHERE qid = $quiz_id ORDER BY id DESC");
                $i = 1;

                while ($row = $question->fetch_assoc()) {
                    $opt = $conn->query("SELECT * FROM question_opt WHERE question_id = '".$row['id']."' ORDER BY RAND()");
                    $answer = $conn->query("SELECT * FROM answers WHERE quiz_id = $quiz_id AND user_id= $user_id AND question_id = '".$row['id']."'")->fetch_array();
                ?>

                <ul class="q-items list-group mt-4 mb-4">
                    <li class="q-field list-group-item">
                        <strong><?php echo $i++ . '. '; ?><?php echo htmlspecialchars($row['question']); ?></strong>
                        <input type="hidden" name="question_id[<?php echo $row['id']; ?>]" value="<?php echo $row['id']; ?>">

                        <ul class="list-group mt-4 mb-4">
                            <?php while ($orow = $opt->fetch_assoc()) {

                                // Determine option class
                                if ($answer['option_id'] == $orow['id'] && $answer['is_right'] == 1) {
                                    $option_class = "bg-success";
                                } elseif ($orow['is_right'] == 1) {
                                    $option_class = "bg-success";
                                } else {
                                    $option_class = "bg-danger";
                                }

                                // Determine if option is checked
                                $checked = ($answer['option_id'] == $orow['id']) ? "checked='checked'" : "";
                            ?>
                                <li class="answer list-group-item <?php echo $option_class; ?>">
                                    <label>
                                        <input type="radio" name="option_id[<?php echo $row['id']; ?>]" value="<?php echo $orow['id']; ?>" <?php echo $checked; ?>>
                                        <?php echo htmlspecialchars($orow['option_txt']); ?>
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                </ul>

                <?php } ?>
            </div>  
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('input').attr('readonly', true);
        });
    </script>
</body>
</html>
