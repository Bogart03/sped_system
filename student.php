<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student List</title>
    <?php include('header.php') ?>
    <?php include('auth.php') ?>
    <?php include('db_connect.php') ?>
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
</head>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<body>
    <?php include('nav_bar.php') ?>
    <div class="container-fluid admin">
        <div class="col-md-12 alert alert-primary">
            <i class="fa fa-users"></i> Student List
        </div>
        <button class="btn btn-primary btn-sm btn-icon" id="new_student">
            <i class="fa fa-plus"></i> Add New
        </button>
        <br><br>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="table">
                    <colgroup>
                        <col width="10%">
                        <col width="40%">
                        <col width="30%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qry = $conn->query("SELECT s.*,u.name FROM students s LEFT JOIN users u ON s.user_id = u.id ORDER BY u.name ASC");
                        $i = 1;
                        if($qry->num_rows > 0){
                            while($row = $qry->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $i++ ?></td>
                            <td><?php echo $row['name'] ?></td>
                            <td><?php echo $row['level_section'] ?></td>
                            <td>
                                <center>
                                    <button class="btn btn-sm btn-outline-primary edit_student icon-btn" data-id="<?php echo $row['id'] ?>" type="button">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger remove_student icon-btn" data-id="<?php echo $row['id'] ?>" type="button">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </center>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="manage_student" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModallabel">Add New Student</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="student-frm">
                    <div class="modal-body">
                        <div id="msg"></div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="hidden" name="id" />
                            <input type="hidden" name="uid" />
                            <input type="hidden" name="user_type" value="3" />
                            <input type="text" name="name" required class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Level-Section</label>
                            <input type="text" name="level_section" required class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" required class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required class="form-control" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" name="save"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
$(document).ready(function(){
    $('#table').DataTable();

    $('#new_student').click(function(){
        $('#msg').html('');
        $('#manage_student .modal-title').html('Add New Student');
        $('#student-frm')[0].reset();
        $('#manage_student').modal('show');
    });

    $('.edit_student').click(function(){
        var id = $(this).data('id');
        $.ajax({
            url:'./get_student.php?id='+id,
            success:function(resp){
                if(resp){
                    resp = JSON.parse(resp);
                    $('[name="id"]').val(resp.id);
                    $('[name="uid"]').val(resp.uid);
                    $('[name="name"]').val(resp.name);
                    $('[name="level_section"]').val(resp.level_section);
                    $('[name="username"]').val(resp.username);
                    $('[name="password"]').val(resp.password);
                    $('#manage_student .modal-title').html('Edit Student');
                    $('#manage_student').modal('show');
                }
            }
        });
    });

    $('.remove_student').click(function(){
        var id = $(this).data('id');
        if(confirm('Are you sure to delete this student?')){
            $.ajax({
                url:'./delete_student.php?id='+id,
                success:function(resp){
                    if(resp == true) location.reload();
                }
            });
        }
    });

    $('#student-frm').submit(function(e){
        e.preventDefault();
        $.ajax({
            url:'./save_student.php',
            method:'POST',
            data:$(this).serialize(),
            success:function(resp){
                resp = JSON.parse(resp);
                if(resp.status == 1){
                    alert('Data successfully saved');
                    location.reload();
                } else {
                    $('#msg').html('<div class="alert alert-danger">'+resp.msg+'</div>');
                }
            }
        });
    });
});
</script>
</body>
</html>
