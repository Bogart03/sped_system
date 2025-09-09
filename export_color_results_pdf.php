<?php
session_start();
if(!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 1){
    header("location: login.php");
    exit;
}

include 'db_connect.php';
require('fpdf/fpdf.php'); // Make sure FPDF library is installed in fpdf/ folder

// Fetch Color Game results
$qry = $conn->query("
    SELECT r.id, r.score, r.attempts, r.created_at, u.name as student_name
    FROM color_game_results r
    LEFT JOIN users u ON r.student_id = u.id
    ORDER BY r.score DESC, r.attempts ASC, r.created_at DESC
");

// Create new PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Color Game Results',0,1,'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(200,200,200);
$pdf->Cell(10,10,'#',1,0,'C',true);
$pdf->Cell(60,10,'Student',1,0,'C',true);
$pdf->Cell(30,10,'Score',1,0,'C',true);
$pdf->Cell(30,10,'Attempts',1,0,'C',true);
$pdf->Cell(50,10,'Date',1,1,'C',true);

// Table Rows
$pdf->SetFont('Arial','',12);
$index = 1;
while($row = $qry->fetch_assoc()){
    $pdf->Cell(10,10,$index,1,0,'C');
    $pdf->Cell(60,10,$row['student_name'] ?? 'Student '.$row['student_id'],1,0);
    $pdf->Cell(30,10,$row['score'],1,0,'C');
    $pdf->Cell(30,10,$row['attempts'],1,0,'C');
    $pdf->Cell(50,10,$row['created_at'],1,1,'C');
    $index++;
}

// Output PDF to browser
$pdf->Output('D','color_game_results.pdf'); // D = Download
?>
