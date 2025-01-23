<?php
session_start();
include '../dbconn/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $grade = $_POST['grade'];
    $teacher_id = $_SESSION['teacher_id'];

    $query_check = "SELECT COUNT(*) AS COUNT FROM grade WHERE STUDENT_ID = :student_id AND SUBJECT_ID = :subject_id";
    $stid_check = oci_parse($dbconn, $query_check);
    oci_bind_by_name($stid_check, ":student_id", $student_id);
    oci_bind_by_name($stid_check, ":subject_id", $subject_id);
    oci_execute($stid_check);
    $row = oci_fetch_assoc($stid_check);

    if ($row['COUNT'] > 0) {
        $_SESSION['message'] = "Grade already exists for this subject.";
    } else {
        $query = "INSERT INTO grade (GRADE_ID, STUDENT_ID, SUBJECT_ID, TEACHER_ID, GRADES) 
                  VALUES (GRADE_SEQ.NEXTVAL, :student_id, :subject_id, :teacher_id, :grade)";
        $stid = oci_parse($dbconn, $query);
        oci_bind_by_name($stid, ":student_id", $student_id);
        oci_bind_by_name($stid, ":subject_id", $subject_id);
        oci_bind_by_name($stid, ":teacher_id", $teacher_id);
        oci_bind_by_name($stid, ":grade", $grade);

        if (oci_execute($stid)) {
            $_SESSION['message'] = "Grade saved successfully.";
        } else {
            $_SESSION['message'] = "Failed to save grade.";
        }

        oci_free_statement($stid);
    }

    oci_free_statement($stid_check);
    oci_close($dbconn);
    header("Location: students.php");
    exit;
}
?>