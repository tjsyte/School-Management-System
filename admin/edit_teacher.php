<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id'];
    $full_name = $_POST['full_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $course_id = $_POST['course_id'];

    $update_query = "UPDATE teacher SET full_name = :full_name, last_name = :last_name, email = :email, 
                     password = :password, course_id = :course_id 
                     WHERE teacher_id = :teacher_id";

    $statement = oci_parse($dbconn, $update_query);
    oci_bind_by_name($statement, ":full_name", $full_name);
    oci_bind_by_name($statement, ":last_name", $last_name);
    oci_bind_by_name($statement, ":email", $email);
    oci_bind_by_name($statement, ":password", $password);
    oci_bind_by_name($statement, ":course_id", $course_id);
    oci_bind_by_name($statement, ":teacher_id", $teacher_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Teacher updated successfully!';
    } else {
        $_SESSION['message'] = 'Error updating teacher.';
    }
    header("Location: teachers.php");
    exit;
}

?>
