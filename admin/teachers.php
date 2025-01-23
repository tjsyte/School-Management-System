<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['full_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['course_id'])) {
    $full_name = $_POST['full_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $course_id = $_POST['course_id'];

    $insert_query = "INSERT INTO teacher (teacher_id, full_name, last_name, email, password, course_id)
    VALUES (teacher_id_seq.NEXTVAL, :full_name, :last_name, :email, :password, :course_id)";

    $statement = oci_parse($dbconn, $insert_query);
    oci_bind_by_name($statement, ":full_name", $full_name);
    oci_bind_by_name($statement, ":last_name", $last_name);
    oci_bind_by_name($statement, ":email", $email);
    oci_bind_by_name($statement, ":password", $password);
    oci_bind_by_name($statement, ":course_id", $course_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Teacher added successfully!';
    } else {
        $_SESSION['message'] = 'Error adding teacher.';
    }
    header("Location: teachers.php");
    exit;
}

// deleting a teacher
if (isset($_GET['delete_id'])) {
    $teacher_id = $_GET['delete_id'];

    $delete_query = "DELETE FROM teacher WHERE teacher_id = :teacher_id";
    $statement = oci_parse($dbconn, $delete_query);
    oci_bind_by_name($statement, ":teacher_id", $teacher_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Teacher deleted successfully!';
    } else {
        $_SESSION['message'] = 'Error deleting teacher.';
    }
    header("Location: teachers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers Management</title>
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
                    <h3>Teachers Management</h3>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                        <i class="fas fa-plus"></i> Add Teacher
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
            </div>

            <div class="table-responsive mt-4">
                <table id="teachersTable" class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Course Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = oci_parse($dbconn, "SELECT t.teacher_id, t.full_name, t.last_name, t.email, t.password, c.course_name
                                                      FROM teacher t
                                                      JOIN course c ON t.course_id = c.course_id
                                                      ORDER BY t.teacher_id ASC");
                        oci_execute($query);
                        $count = 1;
                        while ($row = oci_fetch_assoc($query)) {
                            echo "<tr>";
                            echo "<td>" . $count++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['FULL_NAME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['LAST_NAME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['PASSWORD']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['COURSE_NAME']) . "</td>";
                            echo "<td>";
                            echo "<button class='btn btn-sm btn-warning me-2' data-bs-toggle='modal' data-bs-target='#editTeacherModal' 
                            data-id='" . $row['TEACHER_ID'] . "' 
                            data-full_name='" . htmlspecialchars($row['FULL_NAME']) . "' 
                            data-last_name='" . htmlspecialchars($row['LAST_NAME']) . "' 
                            data-email='" . htmlspecialchars($row['EMAIL']) . "' 
                            data-password='" . htmlspecialchars($row['PASSWORD']) . "'
                            data-course_name='" . htmlspecialchars($row['COURSE_NAME']) . "'>
                            <i class='fas fa-edit'></i> Edit
                            </button>";
                            echo "<a href='teachers.php?delete_id=" . $row['TEACHER_ID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this teacher?\");'><i class='fas fa-trash'></i> Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- add teacher modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTeacherModalLabel">Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="teachers.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="courseId" class="form-label">Course</label>
                            <select class="form-select" id="courseId" name="course_id" required>
                                <?php
                                $course_query = "SELECT * FROM course";
                                $course_stmt = oci_parse($dbconn, $course_query);
                                oci_execute($course_stmt);
                                while ($course_row = oci_fetch_assoc($course_stmt)) {
                                    echo "<option value='" . $course_row['COURSE_ID'] . "'>" . $course_row['COURSE_NAME'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- edit teacher modal -->
    <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_teacher.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="editTeacherId" name="teacher_id">
                        <div class="mb-3">
                            <label for="editFullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="editPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCourseId" class="form-label">Course</label>
                            <select class="form-select" id="editCourseId" name="course_id" required>
                                <?php
                                $course_query = "SELECT * FROM course";
                                $course_stmt = oci_parse($dbconn, $course_query);
                                oci_execute($course_stmt);
                                while ($course_row = oci_fetch_assoc($course_stmt)) {
                                    echo "<option value='" . $course_row['COURSE_ID'] . "'>" . $course_row['COURSE_NAME'] . "</option>";
                                }
                                ?>
                            </select>
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
            $('#teachersTable').DataTable({
                paging: true,
                searching: true,
                order: [
                    [0, 'asc']
                ],
            });
        });

        $(document).ready(function() {
            var table = $('#teachersTable').DataTable();

            $('#courseFilter').on('change', function() {
                table.column(5).search(this.value).draw();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editTeacherModal = document.getElementById('editTeacherModal');
            editTeacherModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const teacherId = button.getAttribute('data-id');
                const fullName = button.getAttribute('data-full_name');
                const lastName = button.getAttribute('data-last_name');
                const email = button.getAttribute('data-email');
                const password = button.getAttribute('data-password');
                const courseName = button.getAttribute('data-course_name');

                document.getElementById('editTeacherId').value = teacherId;
                document.getElementById('editFullName').value = fullName;
                document.getElementById('editLastName').value = lastName;
                document.getElementById('editEmail').value = email;
                document.getElementById('editPassword').value = password;
                document.getElementById('editCourseName').value = courseName;
            });
        });
    </script>

</body>

</html>