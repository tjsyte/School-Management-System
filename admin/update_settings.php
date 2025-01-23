<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $update_query = "UPDATE admin SET username = :username, email = :email, password = :password WHERE admin_id = :admin_id";
    $stid = oci_parse($dbconn, $update_query);

    oci_bind_by_name($stid, ":username", $username);
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":password", $password);
    oci_bind_by_name($stid, ":admin_id", $_SESSION['admin_id']);

    if (oci_execute($stid)) {
        $_SESSION['USERNAME'] = $username;
        $_SESSION['email'] = $email;
        
        header("Location: dashboard.php");
        exit;
    } else {

        $error = "Failed to update settings. Please try again.";
    }
}
?>
