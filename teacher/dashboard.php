<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['last_name'])) {
    $teacher_id = $_SESSION['teacher_id'];
    $query = "SELECT full_name, last_name, email FROM teacher WHERE teacher_id = :teacher_id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":teacher_id", $teacher_id);
    oci_execute($stid);

    $row = oci_fetch_assoc($stid);
    $_SESSION['full_name'] = $row['FULL_NAME'];
    $_SESSION['last_name'] = $row['LAST_NAME'];
    $_SESSION['email'] = $row['EMAIL'];
    oci_free_statement($stid);
}

$counts = [
    'teacher' => 0,
    'student' => 0,
    'course' => 0,
    'subject' => 0,
];

try {
    $tables = ['teacher', 'student', 'course', 'subject'];
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) AS total FROM $table";
        $stid = oci_parse($dbconn, $sql);
        oci_execute($stid);
        $row = oci_fetch_assoc($stid);
        $counts[$table] = $row['TOTAL'];
        oci_free_statement($stid);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <div class="sidebar">
        <img src="../images/logo.png" alt="Logo">
        <h4>Teacher Panel</h4>
        <a href="#"><i class="fas fa-home"></i> Dashboard</a>
        <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="grades.php"><i class="fas fa-chart-line"></i> Grades</a>
    </div>

    <div class="content">
        <div class="header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
            <div class="action-buttons">
                <a href="#" class="settings-btn" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-section mt-4">
            <h3>Dashboard Overview</h3>
            <p>Manage your students, subjects, and courses here.</p>

            <div class="row">
                <!-- teachers -->
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Teachers</h5>
                            <h2 class="card-text text-primary"><?php echo $counts['teacher']; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- students -->
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-user-graduate fa-3x text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Students</h5>
                            <h2 class="card-text text-success"><?php echo $counts['student']; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- subjects -->
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-book fa-3x text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">Subjects</h5>
                            <h2 class="card-text text-warning"><?php echo $counts['subject']; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- courses -->
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-graduation-cap fa-3x text-danger mb-3"></i>
                            <h5 class="card-title fw-bold">Courses</h5>
                            <h2 class="card-text text-danger"><?php echo $counts['course']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- settings modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Edit Your Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_settings.php" method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
