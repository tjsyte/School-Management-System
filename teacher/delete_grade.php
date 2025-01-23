<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: index.php");
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $grade_id = $_POST['grade_id'];
    $teacher_id = $_SESSION['teacher_id'];

    $query = "DELETE FROM grade WHERE grade_id = :grade_id AND teacher_id = :teacher_id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":grade_id", $grade_id);
    oci_bind_by_name($stid, ":teacher_id", $teacher_id);

    if (oci_execute($stid)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>