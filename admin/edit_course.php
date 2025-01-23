<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id']) && isset($_POST['course_name'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];

    $update_query = "UPDATE course SET course_name = :course_name WHERE course_id = :course_id";

    $statement = oci_parse($dbconn, $update_query);

    oci_bind_by_name($statement, ":course_id", $course_id);
    oci_bind_by_name($statement, ":course_name", $course_name);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Course updated successfully!';
    } else {
        $_SESSION['message'] = 'Error updating course.';
    }
    header("Location: courses.php");
    exit;
}
?>
