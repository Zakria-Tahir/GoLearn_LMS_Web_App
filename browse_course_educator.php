<?php
// THIS BROWSE COURSE IS FOR EDUCATORS
include 'utils/_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php');
    exit;
}

if ($_SESSION['user_role'] != 'educator') {
    header('location: index.php?error=' . urlencode('You are not authorized to access this page'));
    exit;
}

if (!isset($_GET['course_id'])) {
    header('location: educator_panel.php?error=' . urlencode('Course ID is required'));
    exit;
}

$course_id = intval($_GET['course_id']);
$educator_id = $_SESSION['user_id'];

// Fetch course data from the database
$sql = "SELECT * FROM courses WHERE course_id = ? AND educator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $course_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    header('location: educator_panel.php?error=' . urlencode('Course not found or you are not authorized to view this course'));
    exit;
}

// Fetch enrollment data
$sql = "SELECT COUNT(*) AS total_enrolled, 
               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS total_completed,
               SUM(CASE WHEN status = 'not completed' THEN 1 ELSE 0 END) AS total_in_progress
        FROM enrollments WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$result = $stmt->get_result();
$enrollment_data = $result->fetch_assoc();

// Fetch current assignments
$sql = "SELECT * FROM assignments WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$current_assignments = $stmt->get_result();

// Fetch current quizzes
$sql = "SELECT * FROM quizzes WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$current_quizzes = $stmt->get_result();

// Fetch submitted assignments
$sql = "SELECT sa.submission_id, a.assignment_name, sa.submission_file, sa.grade, sa.submission_text, u.username AS student_name, sa.student_id
        FROM submitted_assignments sa 
        JOIN assignments a ON sa.assignment_id = a.assignment_id 
        JOIN users u ON sa.student_id = u.user_id 
        WHERE a.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$submitted_assignments = $stmt->get_result();

// Fetch submitted quizzes
$sql = "SELECT sq.submission_id, q.quiz_name, sq.score, sq.student_id 
        FROM submitted_quizzes sq 
        JOIN quizzes q ON sq.quiz_id = q.quiz_id 
        WHERE q.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$submitted_quizzes = $stmt->get_result();
?>

<?php
include "header.php";
?>
<title>Browse Course | GoLearn</title>

<body>
    <div class="wrapper">

        <?php
        include 'sidebar.php';
        ?>

        <div class="main">

            <?php
            include 'navbar.php';

            if (isset($_GET['error'])) {
                echo '
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> ' . $_GET['error'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    ';
            } elseif (isset($_GET['success'])) {
                echo '
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> ' . $_GET['success'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    ';
            }
            ?>

            <div class="container p-5 d-flex justify-content-center align-items-start flex-column">
                <h1 class="text-center">Browse Course</h1>
                <div class="d-flex justify-content-around align-items-start pt-3">
                    <div class="me-4">
                        <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                        <p><?php echo htmlspecialchars($course['course_description']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($course['category_id']); ?></p>
                        <p>Subcategory: <?php echo htmlspecialchars($course['subcategory_id']); ?></p>
                        <img src="<?php echo COURSE_IMAGE_PATH . $course['course_thumbnail']; ?>" alt="Course Thumbnail" style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px;">
                    </div>

                    <div class="ms-4">
                        <h4>Enrollment Statistics</h4>
                        <p>Total Enrolled: <?php echo $enrollment_data['total_enrolled']; ?></p>
                        <p>Completed: <?php echo $enrollment_data['total_completed']; ?></p>
                        <p>In Progress: <?php echo $enrollment_data['total_in_progress']; ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="add_material.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Add Material</a>
                    <a href="add_assignment.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Add Assignment</a>
                    <a href="add_quiz.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Add Quiz</a>
                </div>
            </div>

            <div class="container mt-5">
                <h4>Current Assignments</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Assignment ID</th>
                            <th>Assignment Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $current_assignments->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['assignment_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['assignment_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['assignment_description']); ?></td>
                                <td>
                                    <a href="update_assignment.php?assignment_id=<?php echo $row['assignment_id']; ?>" class="btn btn-warning btn-sm m-1">Update</a>
                                    <a href="utils/_delete_assignment.php?assignment_id=<?php echo $row['assignment_id']; ?>" class="btn btn-danger btn-sm m-1">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="container mt-5">
                <h4>Current Quizzes</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Quiz ID</th>
                            <th>Quiz Title</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $current_quizzes->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['quiz_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['quiz_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quiz_description']); ?></td>
                                <td>
                                    <a href="update_quiz.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-warning btn-sm m-1">Update</a>
                                    <a href="utils/_delete_quiz.php?quiz_id=<?php echo $row['quiz_id']; ?>" class="btn btn-danger btn-sm m-1">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="container mt-5">
                <h4>Submitted Assignments</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Assignment ID</th>
                            <th>Assignment Name</th>
                            <th>Student Name</th>
                            <th>Submission Text</th>
                            <th>Download</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $submitted_assignments->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['submission_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['assignment_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['submission_text']); ?></td>
                                <td><a href="<?php echo SUBMITTED_ASSIGNMENT_PATH . $row['submission_file']; ?>" class="btn btn-info btn-sm" download>Download</a></td>
                                <td>
                                    <form action="utils/_grade_assignment.php" method="post" class="d-inline">
                                        <input type="hidden" name="assignment_id" value="<?php echo $row['submission_id']; ?>">
                                        <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                        <input type="number" name="grade" class="form-control d-inline" style="width: 80px;" value="<?php echo $row['grade']; ?>" required>
                                        <button type="submit" class="btn btn-success btn-sm">Grade</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="container mt-5">
                <h4>Attempted Quizzes</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Quiz ID</th>
                            <th>Quiz Title</th>
                            <th>Student Name</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $submitted_quizzes->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['submission_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['quiz_name']); ?></td>
                                <td><?php
                                    $sql2 = "SELECT username FROM users WHERE user_id = ?";
                                    $stmt2 = $conn->prepare($sql2);
                                    $stmt2->bind_param('i', $row['student_id']);
                                    $stmt2->execute();
                                    $result2 = $stmt2->get_result();
                                    $student = $result2->fetch_assoc();
                                    echo htmlspecialchars($student['username']);
                                    ?></td>
                                <td><?php echo $row['score']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php
            include 'footer.php';
            ?>
        </div>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script>
        if (window.history.replaceState) {
            const url = new URL(window.location);
            const params = new URLSearchParams(url.search);
            params.delete('error');
            params.delete('success');
            url.search = params.toString();
            window.history.replaceState({
                path: url.href
            }, '', url.href);
        }
    </script>
</body>

</html>