<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php'); ?>
    <?php include('auth.php'); ?>
    <?php include('db_connect.php'); ?>
    <title>Quiz List</title>
    <style>
        body { background: #f0f4ff; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 1rem; box-shadow: 0 6px 14px rgba(0,0,0,.1); }
        .btn-icon { display: inline-flex; align-items: center; gap: 8px; border-radius: 30px; transition: all 0.2s ease-in-out; }
        .btn-icon:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
        .table thead th { background: #0056b3; color: white; }
        .table-hover tbody tr:hover { background: #eaf2ff; }
        .alert-primary { border-radius: 10px; font-size: 1.2rem; }
        .icon-btn i { font-size: 1.1rem; }
    </style>
    <!-- Ensure latest Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="container-fluid admin">
    <div class="col-md-12 alert alert-primary">
        <i class="fas fa-list-check"></i> Quiz List
    </div>
    <button class="btn btn-primary btn-sm btn-icon" id="new_quiz">
            <i class="fa fa-plus"></i> Add New
        </button>
    <br><br>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover" id="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Items</th>
                    <th>Point per Item</th>
                    <?php if ($_SESSION['login_user_type'] == 1): ?>
                        <th>Faculty</th>
                    <?php endif; ?>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $where = '';
                if ($_SESSION['login_user_type'] == 2) {
                    $where = " WHERE u.id = " . $_SESSION['login_id'] . " ";
                }
                $qry = $conn->query("SELECT q.*, u.name as fname 
                                     FROM quiz_list q 
                                     LEFT JOIN users u ON q.user_id = u.id 
                                     $where 
                                     ORDER BY q.title ASC");
                $i = 1;
                while ($row = $qry->fetch_assoc()):
                    $items = $conn->query("SELECT COUNT(id) as item_count FROM questions WHERE qid = '{$row['id']}'")->fetch_assoc()['item_count'];
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= $items ?></td>
                        <td><?= htmlspecialchars($row['qpoints']) ?></td>
                        <?php if ($_SESSION['login_user_type'] == 1): ?>
                            <td><?= htmlspecialchars($row['fname']) ?></td>
                        <?php endif; ?>
                        <td>
                            <center>
                                <a class="btn btn-sm btn-outline-info btn-icon" href="./quiz_view.php?id=<?= $row['id'] ?>">
                                    <i class="fas fa-gears"></i> Manage
                                </a>
                                <button class="btn btn-sm btn-outline-warning btn-icon edit_quiz" data-id="<?= $row['id'] ?>" type="button">
                                    <i class="fas fa-pen-to-square"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-icon remove_quiz" data-id="<?= $row['id'] ?>" type="button">
                                    <i class="fas fa-trash-can"></i> Delete
                                </button>
                            </center>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="manage_quiz" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-circle-plus"></i> Add New Quiz</h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="quiz-frm">
                <div class="modal-body">
                    <div id="msg"></div>
                    <input type="hidden" name="id" />
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Points per question</label>
                        <input type="number" name="qpoints" required class="form-control" />
                    </div>
                    <?php if ($_SESSION['login_user_type'] == 1): ?>
                        <div class="form-group">
                            <label>Faculty</label>
                            <select name="user_id" required class="form-control">
                                <option value="" disabled selected>Select Here</option>
                                <?php
                                $fqry = $conn->query("SELECT * FROM users WHERE user_type = 2 ORDER BY name ASC");
                                while ($frow = $fqry->fetch_assoc()):
                                    ?>
                                    <option value="<?= $frow['id'] ?>"><?= htmlspecialchars($frow['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="user_id" value="<?= $_SESSION['login_id'] ?>" />
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-icon" type="submit">
                        <i class="fas fa-floppy-disk"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#table').DataTable();

    // Add New Quiz
    $('#new_quiz').click(function(){
        $('#msg').html('');
        $('#manage_quiz .modal-title').html('<i class="fas fa-circle-plus"></i> Add New Quiz');
        $('#quiz-frm').get(0).reset();
        $('[name="id"]').val('');
        $('#manage_quiz').modal('show');
    });

    // Edit Quiz
    $('.edit_quiz').click(function(){
        var id = $(this).data('id');
        $.ajax({
            url: './get_quiz.php',
            method: 'GET',
            data: { id: id },
            success: function(resp){
                try {
                    resp = JSON.parse(resp);
                    $('[name="id"]').val(resp.id);
                    $('[name="title"]').val(resp.title);
                    $('[name="qpoints"]').val(resp.qpoints);
                    $('[name="user_id"]').val(resp.user_id);
                    $('#manage_quiz .modal-title').html('<i class="fas fa-pen-to-square"></i> Edit Quiz');
                    $('#manage_quiz').modal('show');
                } catch(e) {
                    alert('Failed to load quiz data.');
                }
            },
            error: function(){
                alert('Error fetching quiz data.');
            }
        });
    });

    // Delete Quiz
    $('.remove_quiz').click(function(){
        var id = $(this).data('id');
        if(confirm('Are you sure you want to delete this quiz?')){
            $.ajax({
                url: './delete_quiz.php',
                method: 'POST',
                data: { id: id },
                success: function(resp){
                    if(resp.trim() === '1' || resp.trim() === 'true'){
                        alert('Quiz deleted successfully.');
                        location.reload();
                    } else {
                        alert('Failed to delete quiz.');
                    }
                },
                error: function(){
                    alert('Error deleting quiz.');
                }
            });
        }
    });

    // Save Quiz
    $('#quiz-frm').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: './save_quiz.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp){
                try {
                    resp = JSON.parse(resp);
                    if(resp.status === 1){
                        alert('Quiz saved successfully!');
                        location.replace('./quiz_view.php?id=' + resp.id);
                    } else {
                        $('#msg').html('<div class="alert alert-danger">'+resp.msg+'</div>');
                    }
                } catch(e) {
                    alert('Unexpected server response.');
                }
            },
            error: function(){
                alert('Error saving quiz.');
            }
        });
    });
});
</script>
</body>
</html>
