<?php
session_start();
include '../dbconn/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['full_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['course_id']) && isset($_POST['section']) && isset($_POST['year'])) {
    $full_name = $_POST['full_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $course_id = $_POST['course_id'];
    $section = $_POST['section'];
    $year = $_POST['year'];

    $insert_query = "INSERT INTO student (student_id, full_name, last_name, email, password, course_id, section, year)
    VALUES (student_id_seq.NEXTVAL, :full_name, :last_name, :email, :password, :course_id, :section, :year)";

    $statement = oci_parse($dbconn, $insert_query);
    oci_bind_by_name($statement, ":full_name", $full_name);
    oci_bind_by_name($statement, ":last_name", $last_name);
    oci_bind_by_name($statement, ":email", $email);
    oci_bind_by_name($statement, ":password", $password);
    oci_bind_by_name($statement, ":course_id", $course_id);
    oci_bind_by_name($statement, ":section", $section);
    oci_bind_by_name($statement, ":year", $year);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Student added successfully!';
    } else {
        $_SESSION['message'] = 'Error adding student: ' . oci_error($statement)['message'];
    }
    header("Location: students.php");
    exit;
}

// deleting a student
if (isset($_GET['delete_id'])) {
    $student_id = $_GET['delete_id'];

    $delete_query = "DELETE FROM student WHERE student_id = :student_id";
    $statement = oci_parse($dbconn, $delete_query);
    oci_bind_by_name($statement, ":student_id", $student_id);

    if (oci_execute($statement)) {
        $_SESSION['message'] = 'Student deleted successfully!';
    } else {
        $_SESSION['message'] = 'Error deleting student.';
    }
    header("Location: students.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management</title>
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
        <a href="#"><i class="fas fa-user-graduate"></i> Students</a>
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
                    <h3>Students Management</h3>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                </div>
            </div>

            <div class="row g-3">
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
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Course Name</th>
                            <th>Section</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = oci_parse($dbconn, "SELECT s.student_id, s.full_name, s.last_name, s.email, s.password, c.course_name, s.section, s.year
                                                      FROM student s
                                                      JOIN course c ON s.course_id = c.course_id
                                                      ORDER BY s.student_id ASC");
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
                            echo "<td>" . htmlspecialchars($row['SECTION']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['YEAR']) . "</td>";
                            echo "<td>";
                            echo "<a href='#' class='btn btn-sm btn-warning me-2' data-bs-toggle='modal' data-bs-target='#editStudentModal' 
                            data-id='" . $row['STUDENT_ID'] . "' 
                            data-full_name='" . htmlspecialchars($row['FULL_NAME']) . "' 
                            data-last_name='" . htmlspecialchars($row['LAST_NAME']) . "' 
                            data-email='" . htmlspecialchars($row['EMAIL']) . "' 
                            data-password='" . htmlspecialchars($row['PASSWORD']) . "' 
                            data-course_name='" . htmlspecialchars($row['COURSE_NAME']) . "'
                            data-section='" . htmlspecialchars($row['SECTION']) . "'
                            data-year='" . htmlspecialchars($row['YEAR']) . "'>
                            <i class='fas fa-edit'></i> Edit
                            </a>";
                            echo "<a href='students.php?delete_id=" . $row['STUDENT_ID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this student?\");'><i class='fas fa-trash'></i> Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- add student -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="students.php" method="POST">
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
                            <label for="section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="section" name="section" required>
                        </div>
                        <div class="mb-3">
                            <label for="year" class="form-label">Year</label>
                            <input type="text" class="form-control" id="year" name="year" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_student.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="editStudentId" name="edit_student_id">
                        <div class="mb-3">
                            <label for="editFullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" name="edit_full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="edit_last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="editPassword" name="edit_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCourseName" class="form-label">Course</label>
                            <select class="form-select" id="editCourseName" name="edit_course_name" required>
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
                            <label for="editSection" class="form-label">Section</label>
                            <input type="text" class="form-control" id="editSection" name="edit_section" required>
                        </div>
                        <div class="mb-3">
                            <label for="editYear" class="form-label">Year</label>
                            <input type="text" class="form-control" id="editYear" name="edit_year" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
            $('#studentsTable').DataTable({
                paging: true,
                searching: true,
                order: [
                    [0, 'asc']
                ],
            });
        });

        $(document).ready(function() {
            var table = $('#studentsTable').DataTable();

            $('#courseFilter').on('change', function() {
                table.column(5).search(this.value).draw();
            });

            $('#sectionFilter').on('change', function() {
                table.column(6).search(this.value).draw();
            });

            $('#yearFilter').on('change', function() {
                table.column(7).search(this.value).draw();
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const editStudentModal = document.getElementById("editStudentModal");
            editStudentModal.addEventListener("show.bs.modal", (event) => {
                const button = event.relatedTarget;
                const studentId = button.getAttribute("data-id");
                const fullName = button.getAttribute("data-full_name");
                const lastName = button.getAttribute("data-last_name");
                const email = button.getAttribute("data-email");
                const password = button.getAttribute("data-password");
                const courseName = button.getAttribute("data-course_name");
                const section = button.getAttribute("data-section");
                const year = button.getAttribute("data-year");

                document.getElementById("editStudentId").value = studentId;
                document.getElementById("editFullName").value = fullName;
                document.getElementById("editLastName").value = lastName;
                document.getElementById("editEmail").value = email;
                document.getElementById("editPassword").value = password;
                document.getElementById("editSection").value = section;
                document.getElementById("editYear").value = year;

                const courseDropdown = document.getElementById("editCourseName");
                Array.from(courseDropdown.options).forEach(option => {
                    option.selected = option.text === courseName;
                });
            });
        });

        document.getElementById('addSectionBtn').addEventListener('click', function() {
            let sectionFilter = document.getElementById('sectionFilter');
            let currentSections = Array.from(sectionFilter.options).map(option => option.value);

            let nextSection = String.fromCharCode(currentSections.length + 64);

            if (!currentSections.includes(nextSection)) {
                let newOption = document.createElement('option');
                newOption.value = nextSection;
                newOption.textContent = nextSection;
                sectionFilter.appendChild(newOption);
            }
        });
    </script>
</body>

</html>