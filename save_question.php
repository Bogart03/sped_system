<?php
include 'db_connect.php';
extract($_POST);

header('Content-Type: application/json');

$response = ['status' => 0, 'msg' => 'Saving failed.'];

if (empty($id)) {
    // New question
    $last_order_query = $conn->query("SELECT * FROM questions WHERE qid = $qid ORDER BY order_by DESC LIMIT 1");
    $last_order = $last_order_query && $last_order_query->num_rows > 0
        ? $last_order_query->fetch_array()['order_by']
        : 0;

    $order_by = $last_order > 0 ? $last_order + 1 : 0;

    $data = 'question = "'.$conn->real_escape_string($question).'" ';
    $data .= ', order_by = "'.$order_by.'" ';
    $data .= ', qid = "'.$qid.'" ';

    $insert_question = $conn->query("INSERT INTO questions SET ".$data);
    if ($insert_question) {
        $question_id = $conn->insert_id;
        $inserted_count = 0;

        for ($i = 0; $i < count($question_opt); $i++) {
            $is_right_val = isset($is_right[$i]) ? 1 : 0;
            $opt = $conn->real_escape_string($question_opt[$i]);
            if ($conn->query("INSERT INTO question_opt SET question_id = $question_id, option_txt = '$opt', is_right = $is_right_val")) {
                $inserted_count++;
            }
        }

        if ($inserted_count === count($question_opt)) {
            $response = ['status' => 1, 'msg' => 'Question added successfully.'];
        } else {
            $conn->query("DELETE FROM questions WHERE id = $question_id");
            $conn->query("DELETE FROM question_opt WHERE question_id = $question_id");
            $response['msg'] = 'Failed to insert all options.';
        }
    } else {
        $response['msg'] = 'Database insert error: '.$conn->error;
    }
} else {
    // Update existing question
    $data = 'question = "'.$conn->real_escape_string($question).'" ';
    $data .= ', qid = "'.$qid.'" ';

    $update = $conn->query("UPDATE questions SET ".$data." WHERE id = ".$id);
    if ($update) {
        $conn->query("DELETE FROM question_opt WHERE question_id = ".$id);
        $inserted_count = 0;

        for ($i = 0; $i < count($question_opt); $i++) {
            $is_right_val = isset($is_right[$i]) ? 1 : 0;
            $opt = $conn->real_escape_string($question_opt[$i]);
            if ($conn->query("INSERT INTO question_opt SET question_id = $id, option_txt = '$opt', is_right = $is_right_val")) {
                $inserted_count++;
            }
        }

        if ($inserted_count === count($question_opt)) {
            $response = ['status' => 1, 'msg' => 'Question updated successfully.'];
        } else {
            $conn->query("DELETE FROM questions WHERE id = $id");
            $conn->query("DELETE FROM question_opt WHERE question_id = $id");
            $response['msg'] = 'Failed to insert all options.';
        }
    } else {
        $response['msg'] = 'Database update error: '.$conn->error;
    }
}

echo json_encode($response);
?>
