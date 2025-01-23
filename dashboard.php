<?php
session_start();
include 'dbconn/connection.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// fetch grades
$query = "SELECT g.grades, s.subject_name, s.subject_code, t.full_name AS teacher_name 
          FROM grade g 
          JOIN subject s ON g.subject_id = s.subject_id 
          JOIN teacher t ON g.teacher_id = t.teacher_id
          WHERE g.student_id = :student_id";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":student_id", $student_id);
oci_execute($stid);

$grades = [];
$total_grades = 0;
$total_subjects = 0;

while ($row = oci_fetch_assoc($stid)) {
    $grades[] = $row;
    $total_grades += $row['GRADES'];
    $total_subjects++;
}

$gwa = $total_subjects > 0 ? $total_grades / $total_subjects : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
</head>

<body>
    <div class="sidebar">
        <img src="images/logo.png" alt="Logo">
        <h4>Student Panel</h4>
        <a href="generate_pdf.php" class="print-grades-btn"><i class="fas fa-print"></i> Print your grades</a>
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
            <p>Manage your courses and grades here.</p>
            <h4>Your Grades</h4>
            <table id="gradesTable" class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Teacher</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grades)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['TEACHER_NAME']); ?></td>
                                <td><?php echo htmlspecialchars($grade['SUBJECT_CODE']); ?></td>
                                <td><?php echo htmlspecialchars($grade['SUBJECT_NAME']); ?></td>
                                <td class="<?php echo $grade['GRADES'] >= 75 ? 'grade-green' : 'grade-red'; ?>">
                                    <?php echo number_format($grade['GRADES'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table><br>
            <h4>Your General Weighted Average (GWA): <?php echo number_format($gwa, 2); ?></h4>
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
    <script>
        $(document).ready(function() {
            $('#gradesTable').DataTable({
                paging: true,
                searching: true,
                order: [
                    [0, 'asc']
                ],
            });
        });
    </script>
</body>

</html>