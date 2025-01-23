<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: index.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$query = "SELECT COURSE_ID FROM teacher WHERE teacher_id = :teacher_id";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":teacher_id", $teacher_id);
oci_execute($stid);

$teacher = oci_fetch_assoc($stid);
$teacher_course_id = $teacher['COURSE_ID'];
oci_free_statement($stid);

$query_students = "SELECT student_id, full_name, last_name, email, section, year 
                   FROM student 
                   WHERE course_id = :course_id";
$stid_students = oci_parse($dbconn, $query_students);
oci_bind_by_name($stid_students, ":course_id", $teacher_course_id);
oci_execute($stid_students);

$query_course = "SELECT COURSE_NAME FROM course WHERE COURSE_ID = :course_id";
$stid_course = oci_parse($dbconn, $query_course);
oci_bind_by_name($stid_course, ":course_id", $teacher_course_id);
oci_execute($stid_course);
$course = oci_fetch_assoc($stid_course);
$course_name = $course['COURSE_NAME'];
oci_free_statement($stid_course);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - <?php echo htmlspecialchars($course_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<style>
    .card-body {
        font-size: 1rem;
    }

    .content {
        margin-left: 250px;
        padding: 20px;
    }

    .table-responsive {
        margin-top: 20px;
    }
</style>

<body>

    <div class="sidebar">
        <img src="../images/logo.png" alt="Logo">
        <h4>Teacher Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="grades.php"><i class="fas fa-chart-line"></i> Grades</a>
    </div>

    <div class="content">
        <div class="header">
            <h2>Students of <?php echo htmlspecialchars($course_name); ?> Course</h2>
            <div class="action-buttons">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-section mt-4">
            <div class="row">
                <div class="col-md-8">
                    <h3>Students Management</h3>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="sectionFilter" class="form-label">Select Section</label>
                        <div class="d-flex align-items-center">
                            <select id="sectionFilter" class="form-select form-select-sm">
                                <option value="">Select Section</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                            </select>
                            <button type="button" id="addSectionBtn" class="btn btn-outline-primary ms-2 btn-sm">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="yearFilter" class="form-label">Select Year</label>
                        <div class="d-flex align-items-center">
                            <select id="yearFilter" class="form-select form-select-sm">
                                <option value="">Select Year</option>
                                <option value="1st year">1st year</option>
                                <option value="2nd year">2nd year</option>
                                <option value="3rd year">3rd year</option>
                                <option value="4th year">4th year</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table id="studentsTable" class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Section</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = oci_fetch_assoc($stid_students)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['FULL_NAME']); ?></td>
                                <td><?php echo htmlspecialchars($student['EMAIL']); ?></td>
                                <td><?php echo htmlspecialchars($student['SECTION']); ?></td>
                                <td><?php echo htmlspecialchars($student['YEAR']); ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#gradeModal"
                                        data-student-id="<?php echo $student['STUDENT_ID']; ?>"
                                        data-full-name="<?php echo htmlspecialchars($student['FULL_NAME']); ?>"
                                        data-student-year="<?php echo htmlspecialchars($student['YEAR']); ?>">
                                        Input Grade
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- grade -->
            <div class="modal fade" id="gradeModal" tabindex="-1" aria-labelledby="gradeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="gradeForm" action="save_grade.php" method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title" id="gradeModalLabel">Input Grade</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="student_id" id="modalStudentId">
                                <div class="mb-3">
                                    <label for="modalStudentName" class="form-label">Student Name</label>
                                    <input type="text" id="modalStudentName" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="subjectId" class="form-label">Subject</label>
                                    <select name="subject_id" id="subjectId" class="form-select" required>
                                        <option value="" disabled selected>No subjects available</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="grade" class="form-label">Grade</label>
                                    <input type="number" name="grade" id="grade" class="form-control" min="0" max="100" step="0.01" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save Grade</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="students.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['message'])): ?>
                alert("<?php echo $_SESSION['message']; ?>");
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>

<?php
oci_free_statement($stid_students);
oci_close($dbconn);
?>