<?php
session_start();
if(!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])){
    header("location: login.php");
    exit;
}
include 'db_connect.php';
require('fpdf/fpdf.php'); // fpdf must exist inside /fpdf folder

// Fetch results
$results = $conn->query("
    SELECT m.id, u.name as student_name, m.score, m.total_questions, m.played_at
    FROM math_game_results m
    INNER JOIN users u ON m.user_id = u.id
    ORDER BY m.played_at DESC
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Title
$pdf->Cell(0,10,'Math Quiz Results',0,1,'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(10,10,'#',1,0,'C');
$pdf->Cell(50,10,'Student Name',1,0,'C');
$pdf->Cell(25,10,'Score',1,0,'C');
$pdf->Cell(25,10,'Total',1,0,'C');
$pdf->Cell(35,10,'Percentage',1,0,'C');
$pdf->Cell(45,10,'Played At',1,1,'C');

$pdf->SetFont('Arial','',11);

$i = 1;
if($results && $results->num_rows > 0){
    while($row = $results->fetch_assoc()){
        $percentage = ($row['total_questions'] > 0) 
            ? round(($row['score'] / $row['total_questions']) * 100, 2) 
            : 0;

        $pdf->Cell(10,10,$i++,1,0,'C');
        $pdf->Cell(50,10,$row['student_name'],1,0);
        $pdf->Cell(25,10,$row['score'],1,0,'C');
        $pdf->Cell(25,10,$row['total_questions'],1,0,'C');
        $pdf->Cell(35,10,$percentage.'%',1,0,'C');
        $pdf->Cell(45,10,date("M d, Y h:i A", strtotime($row['played_at'])),1,1,'C');
    }
}

$pdf->Output("D","math_results.pdf");
?>
