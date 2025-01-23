<?php
session_start();
include 'dbconn/connection.php';
require('fpdf/fpdf.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// fetch grades
$query = "SELECT g.grades, s.subject_name, s.subject_code 
          FROM grade g 
          JOIN subject s ON g.subject_id = s.subject_id 
          WHERE g.student_id = :student_id";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":student_id", $student_id);
oci_execute($stid);

$grades = [];
$total_grades = 0;
$total_subjects = 0;

while ($row = oci_fetch_assoc($stid)) {
    $grades[] = $row;
    $total_grades += $row['GRADES'];
    $total_subjects++;
}

$gwa = $total_subjects > 0 ? $total_grades / $total_subjects : 0;

// create pdf
class PDF extends FPDF
{
    // page header
    function Header()
    {
        // logo
        $this->Image('images/logo.png',10,6,30);
        $this->SetFont('Arial','B',16);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0,10,'Student Grades Report',0,1,'C');
        $this->Ln(20);
    }

    // page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(33, 37, 41);

$pdf->Cell(0,10,'Student Name: ' . htmlspecialchars($_SESSION['full_name']),0,1,'C');
$pdf->Cell(0,10,'Student ID: ' . htmlspecialchars($student_id),0,1,'C');
$pdf->Ln(20);

$tableWidth = 200;
$pdf->SetX(($pdf->GetPageWidth() - $tableWidth) / 2);

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Cell(50,10,'Subject Code',1,0,'C',true);
$pdf->Cell(100,10,'Subject Name',1,0,'C',true);
$pdf->Cell(50,10,'Grade',1,1,'C',true);

$pdf->SetFont('Arial','',10);
foreach ($grades as $grade) {
    $pdf->SetX(($pdf->GetPageWidth() - $tableWidth) / 2);
    $pdf->Cell(50,10,htmlspecialchars($grade['SUBJECT_CODE']),1,0,'C');
    $pdf->Cell(100,10,htmlspecialchars($grade['SUBJECT_NAME']),1,0,'C');
    $pdf->Cell(50,10,number_format($grade['GRADES'], 2),1,1,'C');
}
$pdf->Ln(10);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'General Weighted Average (GWA): ' . number_format($gwa, 2),0,1,'C');

$pdf->Output('D', 'Grades_Summary.pdf');
?>