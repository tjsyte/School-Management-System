<?php
include '../dbconn/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();
    $year = $_POST['year'];
    $teacher_id = $_SESSION['teacher_id'];

    $query = "SELECT COURSE_ID FROM teacher WHERE teacher_id = :teacher_id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":teacher_id", $teacher_id);
    oci_execute($stid);

    $teacher = oci_fetch_assoc($stid);
    $teacher_course_id = $teacher['COURSE_ID'];
    oci_free_statement($stid);

    $query_subjects = "SELECT SUBJECT_ID, SUBJECT_NAME FROM subject WHERE COURSE_ID = :course_id AND YEAR = :year";
    $stid_subjects = oci_parse($dbconn, $query_subjects);
    oci_bind_by_name($stid_subjects, ":course_id", $teacher_course_id);
    oci_bind_by_name($stid_subjects, ":year", $year);
    oci_execute($stid_subjects);

    $subjects = [];
    while ($subject = oci_fetch_assoc($stid_subjects)) {
        $subjects[] = $subject;
    }

    oci_free_statement($stid_subjects);
    oci_close($dbconn);

    header('Content-Type: application/json');
    echo json_encode($subjects);
}
?>