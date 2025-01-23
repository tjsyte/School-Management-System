<?php
session_start();
include '../dbconn/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $check_query = "SELECT * FROM admin WHERE email = :email";
    $stid = oci_parse($dbconn, $check_query);
    oci_bind_by_name($stid, ":email", $email);
    oci_execute($stid);

    if (oci_fetch_assoc($stid)) {
        $error = "Email is already registered.";
    } else {
        $insert_query = "INSERT INTO admin (admin_id, username, email, password) 
                         VALUES (admin_id_seq.NEXTVAL, :full_name, :email, :password)";
        $stid = oci_parse($dbconn, $insert_query);
        oci_bind_by_name($stid, ":full_name", $full_name);
        oci_bind_by_name($stid, ":email", $email);
        oci_bind_by_name($stid, ":password", $password);

        if (oci_execute($stid)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color:#810100;">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
            <div class="text-center mb-4">
                <img src="../images/logo.png" alt="Logo" class="img-fluid" style="max-width: 150px;">
            </div>
            <h2 class="text-center mb-4">Admin Register</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <a href="index.php" class="text-decoration-none">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
