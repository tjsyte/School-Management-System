<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = $_POST['full_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $update_query = "UPDATE teacher SET full_name = :full_name, last_name = :last_name, email = :email, password = :password WHERE teacher_id = :teacher_id";
    $stid = oci_parse($dbconn, $update_query);

    oci_bind_by_name($stid, ":full_name", $full_name);
    oci_bind_by_name($stid, ":last_name", $last_name);
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":password", $password);
    oci_bind_by_name($stid, ":teacher_id", $_SESSION['teacher_id']);

    if (oci_execute($stid)) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['last_name'] =  $last_name;
        $_SESSION['email'] = $email;

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Failed to update settings. Please try again.";
    }
}
?>
