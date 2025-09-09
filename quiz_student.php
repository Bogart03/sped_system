<?php 
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['user_id']) || !isset($_POST['qid'])) {
    echo json_encode(['status' => 0, 'msg' => 'Missing parameters']);
    exit;
}

$qid = intval($_POST['qid']);
$user_ids = $_POST['user_id'];
$inserted = 0;

foreach ($user_ids as $val) {
    $uid = intval($val);
    if ($conn->query("INSERT INTO quiz_student_list SET quiz_id = $qid, user_id = $uid")) {
        $inserted++;
    }
}

if ($inserted === count($user_ids)) {
    echo json_encode(['status' => 1, 'msg' => 'Students successfully added']);
} else {
    echo json_encode(['status' => 0, 'msg' => 'Some students failed to save']);
}
?>
