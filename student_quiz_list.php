<?php
include('header.php');
include('auth.php');
include('db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quiz List</title>

    <!-- Bootstrap 4 + DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
		body { background: #f8f9fa; }
        .card { border-radius: .75rem; box-shadow: 0 4px 10px rgba(0,0,0,.06); }
        .btn-icon { display: inline-flex; align-items: center; gap: 6px; }
        table.dataTable th, table.dataTable td { vertical-align: middle; }
        .dataTables_empty {
            text-align: center !important;
            padding: 20px !important;
        }
        /* body { background: #f8f9fa; }
        body.with-fixed-nav { padding-top: 70px; }
        .card { border-radius: .75rem; box-shadow: 0 4px 10px rgba(0,0,0,.06); }
        .admin .alert { margin-bottom: 1rem; }
        table.dataTable th, table.dataTable td { vertical-align: middle; }
        #table thead th { background: #fff; }
        .btn-icon { display: inline-flex; align-items: center; gap: 6px; }
        .dataTables_empty { text-align: center !important; padding: 20px !important; } */
    </style>
</head>
<body class="with-fixed-nav">
<?php include('nav_bar.php'); ?>

<div class="container admin py-4">
    <div class="col-12 alert alert-primary">
        <i class="fas fa-list-check"></i> My Quiz List
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="table">
                    <colgroup>
                        <col width="10%">
                        <col width="30%">
                        <col width="20%">
                        <col width="20%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $qry = $conn->query("SELECT * FROM quiz_list 
                                         WHERE id IN (SELECT quiz_id FROM quiz_student_list WHERE user_id ='".$_SESSION['login_id']."') 
                                         ORDER BY title ASC");
                    $i = 1;
                    if($qry->num_rows > 0){
                        while($row= $qry->fetch_assoc()){
                            $status = $conn->query("SELECT * FROM history WHERE quiz_id = '".$row['id']."' AND user_id ='".$_SESSION['login_id']."' ");
                            $hist = $status->fetch_array();
                            $taken = $status->num_rows > 0;
                        ?>
                        <tr>
                            <td><?php echo $i++ ?></td>
                            <td><?php echo htmlspecialchars($row['title']) ?></td>
                            <td><?php echo $taken ? $hist['score'].'/'.$hist['total_score'] : 'N/A' ?></td>
                            <td>
                                <?php if($taken): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check"></i> Taken</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass-half"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!$taken): ?>
                                    <a class="btn btn-sm btn-primary btn-icon" href="./answer_sheet.php?id=<?php echo $row['id']?>">
                                        <i class="fa-solid fa-pencil"></i> Take Quiz
                                    </a>
                                <?php else: ?>
                                    <a class="btn btn-sm btn-info text-white btn-icon" href="./view_answer.php?id=<?php echo $row['id']?>">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                <?php endif; ?>
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
</div>

<!-- jQuery and DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
  $(function(){
    $('#table').DataTable({
      pageLength: 10,
      lengthChange: false,
      order: [[1, 'asc']], // sort by Quiz Name
      columnDefs: [
        { orderable: false, targets: [0, 4] }
      ],
      language: {
        emptyTable: '<div class="alert alert-secondary mb-0">No quizzes found.</div>'
      }
    });
  });
</script>
</body>
</html>
