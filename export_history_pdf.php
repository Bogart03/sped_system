<?php
session_start();
if(!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 1){
    header("location: login.php");
    exit;
}

include('db_connect.php');
require('fpdf/fpdf.php'); // make sure you have /fpdf/fpdf.php

// Fetch history records
$qry = $conn->query("
    SELECT h.*, u.name as student, q.title 
    FROM history h
    INNER JOIN users u ON h.user_id = u.id
    INNER JOIN quiz_list q ON h.quiz_id = q.id
    ORDER BY h.id DESC
");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Title
$pdf->Cell(0,10,'Quiz History Records',0,1,'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(10,10,'#',1,0,'C');
$pdf->Cell(60,10,'Student Name',1,0,'C');
$pdf->Cell(70,10,'Quiz Title',1,0,'C');
$pdf->Cell(40,10,'Score',1,1,'C');

$pdf->SetFont('Arial','',11);

$i = 1;
if($qry && $qry->num_rows > 0){
    while($row = $qry->fetch_assoc()){
        $pdf->Cell(10,10,$i++,1,0,'C');
        $pdf->Cell(60,10,ucwords($row['student']),1,0);
        $pdf->Cell(70,10,$row['title'],1,0);
        $pdf->Cell(40,10,$row['score'].'/'.$row['total_score'],1,1,'C');
    }
}

$pdf->Output("D","quiz_history.pdf");
?>
