<?php
session_start();
if (!isset($_SESSION['login_user_type']) || !in_array($_SESSION['login_user_type'], [1, 2])) {
    header("location: login.php");
    exit;
}

include 'db_connect.php';
require('fpdf/fpdf.php'); // Make sure FPDF library is installed in fpdf/ folder

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Word Game Results Report',0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->Cell(10,10,'#',1,0,'C');
        $this->Cell(50,10,'Student',1,0,'C');
        $this->Cell(25,10,'Score',1,0,'C');
        $this->Cell(25,10,'Total',1,0,'C');
        $this->Cell(30,10,'Percentage',1,0,'C');
        $this->Cell(50,10,'Played At',1,1,'C');
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$query = "
    SELECT u.name as student_name, w.correct, w.total_questions, w.played_at 
    FROM word_game_results w
    INNER JOIN users u ON w.user_id = u.id
    ORDER BY w.played_at DESC
";
$result = $conn->query($query);

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

$i = 1;
while ($row = $result->fetch_assoc()) {
    $percentage = ($row['total_questions'] > 0)
        ? round(($row['correct'] / $row['total_questions']) * 100, 2)
        : 0;

    $pdf->Cell(10,10,$i++,1,0,'C');
    $pdf->Cell(50,10,$row['student_name'],1,0,'C');
    $pdf->Cell(25,10,$row['correct'],1,0,'C');
    $pdf->Cell(25,10,$row['total_questions'],1,0,'C');
    $pdf->Cell(30,10,$percentage.'%',1,0,'C');
    $pdf->Cell(50,10,date("M d, Y h:i A", strtotime($row['played_at'])),1,1,'C');
}

$pdf->Output('D','word_results.pdf'); // D = Download
