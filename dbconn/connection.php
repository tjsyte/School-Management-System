<?php
// oracle connection details
$user = "system";
$pass = "system123";
$host = "localhost:1522/XE";

// connect to the oracle database
$dbconn = oci_connect($user, $pass, $host);

if (!$dbconn) {
    $e = oci_error();
    die("Oracle connection failed: " . htmlentities($e['message'], ENT_QUOTES));
}
?>
