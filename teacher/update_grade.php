<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $grade_id = $_POST['grade_id'];
    $grade = $_POST['grade'];
    $teacher_id = $_SESSION['teacher_id'];

    $query = "UPDATE grade SET grades = :grade WHERE grade_id = :grade_id AND teacher_id = :teacher_id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":grade", $grade);
    oci_bind_by_name($stid, ":grade_id", $grade_id);
    oci_bind_by_name($stid, ":teacher_id", $teacher_id);

    if (oci_execute($stid)) {
        $_SESSION['message'] = "Grade updated successfully.";
        header("Location: grades.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update grade.";
        header("Location: grades.php");
        exit;
    }
}
?>