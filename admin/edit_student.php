<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student_id'])) {
    $student_id = $_POST['edit_student_id'];
    $full_name = $_POST['edit_full_name'];
    $last_name = $_POST['edit_last_name'];
    $email = $_POST['edit_email'];
    $password = $_POST['edit_password'];
    $course_id = $_POST['edit_course_name'];
    $section = $_POST['edit_section'];
    $year = $_POST['edit_year'];

    $update_query = "UPDATE student 
                     SET full_name = :full_name, 
                         last_name = :last_name, 
                         email = :email, 
                         password = :password, 
                         course_id = :course_id, 
                         section = :section,
                         year = :year 
                     WHERE student_id = :student_id";

    $statement = oci_parse($dbconn, $update_query);
    oci_bind_by_name($statement, ":full_name", $full_name);
    oci_bind_by_name($statement, ":last_name", $last_name);
    oci_bind_by_name($statement, ":email", $email);
    oci_bind_by_name($statement, ":password", $password);
    oci_bind_by_name($statement, ":course_id", $course_id);
    oci_bind_by_name($statement, ":section", $section);
    oci_bind_by_name($statement, ":year", $year);
    oci_bind_by_name($statement, ":student_id", $student_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Student updated successfully!';
    } else {
        $_SESSION['message'] = 'Error updating student: ' . oci_error($statement)['message'];
    }
    header("Location: students.php");
    exit;
}

?>