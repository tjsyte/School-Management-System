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

// fetch grades with student and subject information
$query_grades = "
    SELECT g.grade_id, s.full_name AS student_name, s.section, s.year, subj.subject_name, g.grades
    FROM grade g
    JOIN student s ON g.student_id = s.student_id
    JOIN subject subj ON g.subject_id = subj.subject_id
    WHERE subj.course_id = :course_id AND g.teacher_id = :teacher_id
";
$stid_grades = oci_parse($dbconn, $query_grades);
oci_bind_by_name($stid_grades, ":course_id", $teacher_course_id);
oci_bind_by_name($stid_grades, ":teacher_id", $teacher_id);
oci_execute($stid_grades);

// fetch course name
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
    <title>Grades - <?php echo htmlspecialchars($course_name); ?></title>
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
        <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="#"><i class="fas fa-chart-line"></i> Grades</a>
    </div>

    <div class="content">
        <div class="header">
            <h2>Grades for <?php echo htmlspecialchars($course_name); ?> Course</h2>
            <div class="action-buttons">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-section mt-4">
            <div class="row">
                <div class="col-md-8">
                    <h3>Grades Management</h3>
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
                <table id="gradesTable" class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th>
                            <th>Section</th>
                            <th>Year</th>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($grade = oci_fetch_assoc($stid_grades)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['STUDENT_NAME']); ?></td>
                                <td><?php echo htmlspecialchars($grade['SECTION']); ?></td>
                                <td><?php echo htmlspecialchars($grade['YEAR']); ?></td>
                                <td><?php echo htmlspecialchars($grade['SUBJECT_NAME']); ?></td>
                                <td class="grade-cell"><?php echo htmlspecialchars(number_format($grade['GRADES'], 2)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-2 btn-edit" data-id="<?php echo $grade['GRADE_ID']; ?>" data-grade="<?php echo $grade['GRADES']; ?>" data-bs-toggle="modal" data-bs-target="#editGradeModal">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger me-2 btn-delete" data-id="<?php echo $grade['GRADE_ID']; ?>">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- edit grade modal -->
    <div class="modal fade" id="editGradeModal" tabindex="-1" aria-labelledby="editGradeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editGradeForm" method="post" action="update_grade.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editGradeModalLabel">Edit Grade</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="grade_id" id="grade_id">
                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <input type="number" class="form-control" id="grade" name="grade" step="0.01" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="grades.js"></script>
</body>

</html>

<?php
oci_free_statement($stid_grades);
oci_close($dbconn);
?>