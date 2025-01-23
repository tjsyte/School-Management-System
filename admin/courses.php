<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_name'])) {
    $course_name = $_POST['course_name'];

    $insert_query = "INSERT INTO course (course_id, course_name)
    VALUES (course_id_seq.NEXTVAL, :course_name)";

    $statement = oci_parse($dbconn, $insert_query);

    oci_bind_by_name($statement, ":course_name", $course_name);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Course added successfully!';
    } else {
        $_SESSION['message'] = 'Error adding course.';
    }
    header("Location: courses.php");
    exit;
}

// deleting a course
if (isset($_GET['delete_id'])) {
    $course_id = $_GET['delete_id'];

    $delete_query = "DELETE FROM course WHERE course_id = :course_id";
    $statement = oci_parse($dbconn, $delete_query);
    oci_bind_by_name($statement, ":course_id", $course_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Course deleted successfully!';
    } else {
        $_SESSION['message'] = 'Error deleting course.';
    }
    header("Location: courses.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management</title>
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
        <a href="subjects.php"><i class="fas fa-book"></i> Subjects</a>
        <a href="#"><i class="fas fa-graduation-cap"></i> Courses</a>
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
                    <h3>Courses Management</h3>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fas fa-plus"></i> Add Course
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table id="coursesTable" class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Course Name</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = oci_parse($dbconn, "SELECT * FROM course ORDER BY course_id ASC");
                        oci_execute($query);
                        $count = 1;
                        while ($row = oci_fetch_assoc($query)) {
                            echo "<tr>";
                            echo "<td>" . $count++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['COURSE_NAME']) . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-warning me-2' data-bs-toggle='modal' data-bs-target='#editCourseModal' data-id='" . $row['COURSE_ID'] . "' data-name='" . htmlspecialchars($row['COURSE_NAME']) . "'><i class='fas fa-edit'></i> Edit</button>";
                            echo "<a href='courses.php?delete_id=" . $row['COURSE_ID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this course?\");'><i class='fas fa-trash'></i> Delete</a>";                            
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
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="courses.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="courseName" class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="courseName" name="course_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_course.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="editCourseId" name="course_id">
                        <div class="mb-3">
                            <label for="editCourseName" class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="editCourseName" name="course_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
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
            $('#coursesTable').DataTable({
                paging: true,
                searching: true,
                order: [
                    [0, 'asc']
                ],
            });

            $('#editCourseModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var courseId = button.data('id');
                var courseName = button.data('name');

                var modal = $(this);
                modal.find('#editCourseId').val(courseId);
                modal.find('#editCourseName').val(courseName);
            });
        });
    </script>
</body>

</html>