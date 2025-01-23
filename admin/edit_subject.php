<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject_id']) && isset($_POST['subject_code']) && isset($_POST['subject_name']) && isset($_POST['course_name']) && isset($_POST['year'])) {
    $subject_id = $_POST['subject_id'];
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $course_name = $_POST['course_name'];
    $year = $_POST['year'];

    $course_query = oci_parse($dbconn, "SELECT COURSE_ID FROM course WHERE COURSE_NAME = :course_name");
    oci_bind_by_name($course_query, ":course_name", $course_name);
    oci_execute($course_query);
    $course_row = oci_fetch_assoc($course_query);
    $course_id = $course_row['COURSE_ID'];

    $update_query = "UPDATE subject SET subject_code = :subject_code, subject_name = :subject_name, course_id = :course_id, year = :year WHERE subject_id = :subject_id";

    $statement = oci_parse($dbconn, $update_query);

    oci_bind_by_name($statement, ":subject_id", $subject_id);
    oci_bind_by_name($statement, ":subject_code", $subject_code);
    oci_bind_by_name($statement, ":subject_name", $subject_name);
    oci_bind_by_name($statement, ":course_id", $course_id);
    oci_bind_by_name($statement, ":year", $year);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Subject updated successfully!';
    } else {
        $_SESSION['message'] = 'Error updating subject.';
    }
    header("Location: subjects.php");
    exit;
}
?>
