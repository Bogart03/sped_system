<?php
session_start();
if(!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])){
    header("location: login.php");
    exit;
}

include 'db_connect.php';
require('fpdf/fpdf.php'); // make sure fpdf.php exists in /fpdf folder

// Fetch results
$results = $conn->query("
    SELECT m.id, u.name as student_name, m.moves, m.time_taken, m.played_at
    FROM memory_game_results m
    INNER JOIN users u ON m.user_id = u.id
    ORDER BY m.played_at DESC
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Title
$pdf->Cell(0,10,'Memory Game Results',0,1,'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(10,10,'#',1,0,'C');
$pdf->Cell(50,10,'Student Name',1,0,'C');
$pdf->Cell(25,10,'Moves',1,0,'C');
$pdf->Cell(25,10,'Time (s)',1,0,'C');
$pdf->Cell(80,10,'Played At',1,1,'C');

$pdf->SetFont('Arial','',11);

$i = 1;
if($results && $results->num_rows > 0){
    while($row = $results->fetch_assoc()){
        $pdf->Cell(10,10,$i++,1,0,'C');
        $pdf->Cell(50,10,$row['student_name'],1,0);
        $pdf->Cell(25,10,$row['moves'],1,0,'C');
        $pdf->Cell(25,10,$row['time_taken'],1,0,'C');
        $pdf->Cell(80,10,date("M d, Y h:i A", strtotime($row['played_at'])),1,1,'C');
    }
}

$pdf->Output("D","memory_results.pdf");
?>
