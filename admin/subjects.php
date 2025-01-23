<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject_code']) && isset($_POST['subject_name']) && isset($_POST['course_name']) && isset($_POST['year'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $course_name = $_POST['course_name'];
    $year = $_POST['year'];

    $course_query = oci_parse($dbconn, "SELECT COURSE_ID FROM course WHERE COURSE_NAME = :course_name");
    oci_bind_by_name($course_query, ":course_name", $course_name);
    oci_execute($course_query);
    $course_row = oci_fetch_assoc($course_query);
    $course_id = $course_row['COURSE_ID'];

    $insert_query = "INSERT INTO subject (subject_id, subject_code, subject_name, course_id, year)
                     VALUES (subject_id_seq.NEXTVAL, :subject_code, :subject_name, :course_id, :year)";

    $statement = oci_parse($dbconn, $insert_query);

    oci_bind_by_name($statement, ":subject_code", $subject_code);
    oci_bind_by_name($statement, ":subject_name", $subject_name);
    oci_bind_by_name($statement, ":course_id", $course_id);
    oci_bind_by_name($statement, ":year", $year);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Subject added successfully!';
    } else {
        $_SESSION['message'] = 'Error adding subject.';
    }
    header("Location: subjects.php");
    exit;
}

$courses_query = oci_parse($dbconn, "SELECT COURSE_NAME FROM course ORDER BY COURSE_NAME ASC");
oci_execute($courses_query);

// deleting subject
if (isset($_GET['delete_id'])) {
    $subject_id = $_GET['delete_id'];

    $delete_query = "DELETE FROM subject WHERE subject_id = :subject_id";
    $statement = oci_parse($dbconn, $delete_query);
    oci_bind_by_name($statement, ":subject_id", $subject_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Subject deleted successfully!';
    } else {
        $_SESSION['message'] = 'Error deleting subject.';
    }
    header("Location: subjects.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar">
        <img src="../images/logo.png" alt="Logo">
        <h4>Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="teachers.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a>
        <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="#"><i class="fas fa-book"></i> Subjects</a>
        <a href="courses.php"><i class="fas fa-graduation-cap"></i> Courses</a>
    </div>

    <div class="content">
        <div class="header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['USERNAME']); ?>!</h2>
            <div class="action-buttons">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-section mt-4">
            <div class="row">
                <div class="col-md-8">
                    <h3>Subjects Management</h3>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="fas fa-plus"></i> Add Subject
                    </button>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="courseFilter" class="form-label">Select Course</label>
                        <select id="courseFilter" class="form-select form-select-sm">
                            <option value="">Select Course</option>
                            <?php
                            // fetch courses for the dropdown
                            $course_query = "SELECT * FROM course";
                            $course_stmt = oci_parse($dbconn, $course_query);
                            oci_execute($course_stmt);
                            while ($course_row = oci_fetch_assoc($course_stmt)) {
                                echo "<option value='" . $course_row['COURSE_NAME'] . "'>" . $course_row['COURSE_NAME'] . "</option>";
                            }
                            ?>
                        </select>
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
                <table id="subjectsTable" class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Subject Code</th>
                            <th scope="col">Subject Name</th>
                            <th scope="col">Course Name</th>
                            <th scope="col">Year</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = oci_parse($dbconn, "SELECT s.*, c.COURSE_NAME FROM subject s JOIN course c ON s.COURSE_ID = c.COURSE_ID ORDER BY s.subject_id ASC");
                        oci_execute($query);
                        $count = 1;
                        while ($row = oci_fetch_assoc($query)) {
                            echo "<tr>";
                            echo "<td>" . $count++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['SUBJECT_CODE']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['SUBJECT_NAME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['COURSE_NAME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['YEAR']) . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-warning me-2' data-bs-toggle='modal' data-bs-target='#editSubjectModal' data-id='" . $row['SUBJECT_ID'] . "' data-code='" . htmlspecialchars($row['SUBJECT_CODE']) . "' data-name='" . htmlspecialchars($row['SUBJECT_NAME']) . "' data-course='" . htmlspecialchars($row['COURSE_NAME']) . "' data-year='" . htmlspecialchars($row['YEAR']) . "'><i class='fas fa-edit'></i> Edit</button>";
                            echo "<a href='subjects.php?delete_id=" . $row['SUBJECT_ID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this subject?\");'><i class='fas fa-trash'></i> Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- add modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubjectModalLabel">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="subjects.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subjectCode" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subjectCode" name="subject_code" required>
                        </div>
                        <div class="mb-3">
                            <label for="subjectName" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subjectName" name="subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="courseName" class="form-label">Course Name</label>
                            <select class="form-control" id="courseName" name="course_name" required>
                                <?php while ($course = oci_fetch_assoc($courses_query)) { ?>
                                    <option value="<?php echo $course['COURSE_NAME']; ?>"><?php echo $course['COURSE_NAME']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="year" class="form-label">Year</label>
                            <input type="text" class="form-control" id="year" name="year" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_subject.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="subject_id" id="editSubjectId">
                        <div class="mb-3">
                            <label for="editSubjectCode" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="editSubjectCode" name="subject_code" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSubjectName" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="editSubjectName" name="subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCourseName" class="form-label">Course Name</label>
                            <select class="form-control" id="editCourseName" name="course_name" required>
                                <?php
                                // Fetch and display courses for editing
                                $courses_query = oci_parse($dbconn, "SELECT COURSE_NAME FROM course ORDER BY COURSE_NAME ASC");
                                oci_execute($courses_query);
                                while ($course = oci_fetch_assoc($courses_query)) {
                                    echo "<option value='" . $course['COURSE_NAME'] . "'>" . $course['COURSE_NAME'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editYear" class="form-label">Year</label>
                            <input type="text" class="form-control" id="editYear" name="year" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#subjectsTable').DataTable({
                paging: true,
                searching: true,
                order: [
                    [0, 'asc']
                ]
            });

            $(document).ready(function() {
                var table = $('#subjectsTable').DataTable();

                $('#courseFilter').on('change', function() {
                    table.column(3).search(this.value).draw();
                });

                $('#yearFilter').on('change', function() {
                    table.column(4).search(this.value).draw();
                });
            });

            $('#editSubjectModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var subjectId = button.data('id');
                var subjectCode = button.data('code');
                var subjectName = button.data('name');
                var courseName = button.data('course');
                var year = button.data('year');

                var modal = $(this);
                modal.find('#editSubjectId').val(subjectId);
                modal.find('#editSubjectCode').val(subjectCode);
                modal.find('#editSubjectName').val(subjectName);
                modal.find('#editCourseName').val(courseName);
                modal.find('#editYear').val(year);
            });
        });
    </script>
</body>

</html>