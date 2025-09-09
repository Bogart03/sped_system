<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
    <title>Quiz View</title>

    <?php 
    $qry = $conn->query("SELECT * FROM quiz_list where id = ".$_GET['id'])->fetch_array();
    ?>

    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #fdf7ff;
            font-family: 'Baloo 2', cursive;
        }
        .alert-primary {
            font-size: 2rem;
            text-align: center;
            border-radius: 15px;
            background: linear-gradient(90deg, #7dd3fc, #a78bfa);
            color: white;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .btn-primary {
            border-radius: 30px;
            font-size: 1.2rem;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .btn-primary:hover {
            transform: scale(1.05);
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            background: #fff;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            font-size: 1.4rem;
            background: #fde68a;
            text-align: center;
            font-weight: bold;
        }
        .list-group-item {
            font-size: 1.2rem;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            background: #fef3c7;
            transition: 0.2s;
        }
        .list-group-item:hover {
            background: #d9f99d;
            transform: scale(1.02);
        }
        .edit_question, .remove_question, .remove_student {
            margin: 5px;
            border-radius: 50%;
            font-size: 1rem;
        }
        .modal-content {
            border-radius: 20px;
            background: #f0f9ff;
        }
        .modal-header {
            background: #7dd3fc;
            color: white;
            border-radius: 20px 20px 0 0;
        }
        label {
            font-size: 1.1rem;
            color: #374151;
        }
        textarea {
            border-radius: 12px;
            font-size: 1rem;
        }
        /* Fun animations */
        @keyframes pop {
            0% { transform: scale(0.9); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .alert-primary {
            animation: pop 0.6s ease-in-out;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php') ?>
    
    <div class="container-fluid admin">
        <div class="col-md-12 alert alert-primary">
            
        </div>

        <div class="text-center mb-4">
            <button class="btn btn-primary" id="new_question"><i class="fa fa-plus"></i> Add Fun Question</button>
            <button class="btn btn-primary" id="new_student"><i class="fa fa-user-plus"></i> Add Student</button>
        </div>

        <!-- Questions Card -->
        <div class="card col-md-6 mr-4" style="float:left">
            <div class="card-header">
                📚 Questions
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php
                    $qry = $conn->query("SELECT * FROM questions where qid = ".$_GET['id']." order by order_by asc");
                    while($row=$qry->fetch_array()){
                    ?>
                        <li class="list-group-item">
                            <strong><?php echo $row['question'] ?></strong><br>
                            <center>
                                <button class="btn btn-sm btn-outline-primary edit_question" data-id="<?php echo $row['id']?>" type="button"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-sm btn-outline-danger remove_question" data-id="<?php echo $row['id']?>" type="button"><i class="fa fa-trash"></i></button>
                            </center>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Students Card -->
        <div class="card col-md-5" style="float:left">
            <div class="card-header">
                👩‍🎓 Students
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php
                    $qry = $conn->query("SELECT u.*,q.id as qid FROM users u left join quiz_student_list q on u.id = q.user_id where q.quiz_id = ".$_GET['id']." order by u.name asc");
                    while($row=$qry->fetch_array()){
                    ?>
                        <li class="list-group-item">
                            <span><?php echo ucwords($row['name']) ?></span>
                            <button class="btn btn-sm btn-outline-danger remove_student pull-right" data-id="<?php echo $row['id']?>" data-qid='<?php echo $row['qid'] ?>' type="button"><i class="fa fa-trash"></i></button>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="manage_question" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title">Add New Question</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="question-frm">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <input type="hidden" name="qid" value="<?php echo $_GET['id'] ?>" />
                        <input type="hidden" name="id" />
                        <div class="form-group">
                            <label>Question</label>
                            <textarea rows="3" name="question" required class="form-control"></textarea>
                        </div>
                        <label>Options:</label>
                        <?php for($i=0; $i<4; $i++){ ?>
                            <div class="form-group">
                                <textarea rows="2" name="question_opt[<?php echo $i; ?>]" required class="form-control"></textarea>
                                <label>
                                    <input type="radio" name="is_right[<?php echo $i; ?>]" class="is_right" value="1"> Correct Answer
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="manage_student" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h4 class="modal-title">Add New Student/s</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="student-frm">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <input type="hidden" name="qid" value="<?php echo $_GET['id'] ?>" />
                        <div class="form-group">
                            <label>Student/s</label>
                            <select name="user_id[]" required multiple class="form-control select2" style="width:100%">
                                <?php 
                                $student = $conn->query("SELECT u.*,s.level_section as ls FROM users u LEFT JOIN students s ON u.id=s.user_id WHERE u.user_type=3");
                                while($row=$student->fetch_assoc()){
                                    echo "<option value='{$row['id']}'>".ucwords($row['name'])." ".$row['ls']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
$(document).ready(function(){
    $(".select2").select2({
        placeholder: "Select here",
        width: 'resolve'
    });

    // Add New Question
    $('#new_question').click(function(){
        $('#msg').html('');
        $('#manage_question .modal-title').html('Add New Question');
        $('#manage_question #question-frm').get(0).reset();
        $('#manage_question').modal('show');
    });

    // Add New Student
    $('#new_student').click(function(){
        $('#msg').html('');
        $('#manage_student').modal('show');
    });

    // Edit Question
    $('.edit_question').click(function(){
        var id = $(this).attr('data-id');
        $.ajax({
            url: './get_question.php?id=' + id,
            dataType: 'json',
            success: function(resp){
                if(resp && resp.qdata){
                    $('[name="id"]').val(resp.qdata.id);
                    $('[name="question"]').val(resp.qdata.question);
                    Object.keys(resp.odata).forEach(k=>{
                        var data = resp.odata[k];
                        $('[name="question_opt['+k+']"]').val(data.option_txt);
                        if(data.is_right == 1) {
                            $('[name="is_right['+k+']"]').prop('checked',true);
                        }
                    });
                    $('#manage_question .modal-title').html('Edit Question');
                    $('#manage_question').modal('show');
                }
            }
        });
    });

    // Single correct option
    $(document).on('click', '.is_right', function(){
        $('.is_right').prop('checked', false);
        $(this).prop('checked', true);
    });

    // Remove Question
    $('.remove_question').click(function(){
        var id = $(this).attr('data-id');
        if(confirm('Are you sure to delete this question?')){
            $.ajax({
                url:'./delete_question.php?id='+id,
                success:function(resp){
                    if(resp == 1 || (resp.status && resp.status == 1)){
                        location.reload();
                    }
                }
            });
        }
    });

    // Remove Student
    $('.remove_student').click(function(){
        var qid = $(this).attr('data-qid');
        if(confirm('Are you sure to remove this student?')){
            $.ajax({
                url:'./delete_quiz_student.php?qid='+qid,
                success:function(resp){
                    if(resp == 1 || (resp.status && resp.status == 1)){
                        location.reload();
                    }
                }
            });
        }
    });

    // Save Question
    $('#question-frm').submit(function(e){
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.attr('disabled', true).html('Saving...');
        $('#msg').html('');

        $.ajax({
            url:'./save_question.php',
            method:'POST',
            data:$(this).serialize(),
            dataType:'json',
            success:function(resp){
                if(resp.status == 1 || resp == 1){
                    alert('✅ Question saved!');
                    location.reload();
                } else {
                    $('#msg').html('<div class="alert alert-danger">'+(resp.msg || 'Saving failed.')+'</div>');
                    $btn.removeAttr('disabled').html('Save');
                }
            }
        });
    });

    // Save Student
    $('#student-frm').submit(function(e){
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.attr('disabled', true).html('Saving...');
        $('#msg').html('');

        $.ajax({
            url:'./quiz_student.php',
            method:'POST',
            data:$(this).serialize(),
            dataType:'json',
            success:function(resp){
                if(resp.status == 1 || resp == 1){
                    alert('🎉 Student added!');
                    location.reload();
                } else {
                    $('#msg').html('<div class="alert alert-danger">'+(resp.msg || 'Saving failed.')+'</div>');
                    $btn.removeAttr('disabled').html('Save');
                }
            }
        });
    });
});
</script>
</body>
</html>
