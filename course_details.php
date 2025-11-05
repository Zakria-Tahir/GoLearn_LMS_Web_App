<?php
include 'utils/_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['course_id'])) {
    header('location: browse_courses.php?error=' . urlencode('Course ID is required'));
    exit;
}

$course_id = intval($_GET['course_id']);

// Fetch course details
$sql = "SELECT * FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    header('location: browse_courses.php?error=' . urlencode('Course not found'));
    exit;
}

// Handle missing educator, category, or subcategory names
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course['educator_id']);
$stmt->execute();
$course['educator_name'] = $stmt->get_result()->fetch_assoc()['username'];

$sql = "SELECT category_name FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course['category_id']);
$stmt->execute();
$course['category_name'] = $stmt->get_result()->fetch_assoc()['category_name'];

$sql = "SELECT subcategory_name FROM subcategories WHERE subcategory_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course['subcategory_id']);
$stmt->execute();
$course['subcategory_name'] = $stmt->get_result()->fetch_assoc()['subcategory_name'];

// Check if the user is enrolled in the course
$sql = "SELECT * FROM enrollments WHERE course_id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $course_id, $user_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();
$is_enrolled = $enrollment ? true : false;

// Fetch total assignments and quizzes
$sql = "SELECT COUNT(*) as total_assignments FROM assignments WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$total_assignments = $stmt->get_result()->fetch_assoc()['total_assignments'];

$sql = "SELECT COUNT(*) as total_quizzes FROM quizzes WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $course_id);
$stmt->execute();
$total_quizzes = $stmt->get_result()->fetch_assoc()['total_quizzes'];

if ($is_enrolled) {
    // Fetch completed assignments
    $sql = "SELECT COUNT(*) as completed_assignments FROM submitted_assignments WHERE student_id = ? AND assignment_id IN (SELECT assignment_id FROM assignments WHERE course_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $course_id);
    $stmt->execute();
    $completed_assignments = $stmt->get_result()->fetch_assoc()['completed_assignments'];

    // Fetch completed quizzes and their scores
    $sql = "SELECT sq.quiz_id, sq.score, sq.submitted_on,
               (SELECT COUNT(*) FROM quizzes_content qc WHERE qc.quiz_id = sq.quiz_id) AS total_questions
            FROM submitted_quizzes sq
            INNER JOIN quizzes q ON sq.quiz_id = q.quiz_id
            WHERE sq.student_id = ? AND q.course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $course_id);
    $stmt->execute();
    $submitted_quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate completed quizzes
    $completed_quizzes = count(array_unique(array_column($submitted_quizzes, 'quiz_id')));
}
?>

<?php
include "header.php";
?>
<title><?php echo htmlspecialchars($course['course_name']); ?> | GoLearn</title>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php';

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


            <div class="container p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
                    <?php if ($is_enrolled) { ?>
                        <form action="utils/_unenroll_course.php" method="post" onsubmit="return confirm('Are you sure you want to unenroll from this course?');">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" class="btn btn-danger">Unenroll from Course</button>
                        </form>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <img src="<?php echo COURSE_IMAGE_PATH . $course['course_thumbnail']; ?>" class="img-fluid mb-2" alt="Course Thumbnail">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($course['course_description'])); ?></p>
                        <h3>Educator</h3>
                        <p><?php echo htmlspecialchars($course['educator_name']); ?></p>
                        <h3>Category</h3>
                        <p><?php echo htmlspecialchars($course['category_name']); ?></p>
                        <h3>Subcategory</h3>
                        <p><?php echo htmlspecialchars($course['subcategory_name']); ?></p>
                    </div>
                    <div class="col-md-6">

                        <?php if ($is_enrolled) { ?>
                            <div class="alert alert-success" role="alert">
                                You are enrolled in this course.
                            </div>
                            <p>
                                Assignments Completed: <?php echo $completed_assignments; ?>/<?php echo $total_assignments; ?><br>
                                Quizzes Completed: <?php echo $completed_quizzes; ?>/<?php echo $total_quizzes; ?>
                            </p>
                            <?php if ($completed_assignments == $total_assignments && $completed_quizzes == $total_quizzes && $total_assignments > 0 && $total_quizzes > 0) { ?>
                                <div class="alert alert-info">
                                    Congratulations! You have completed this course.
                                </div>
                            <?php } ?>
                            <h3>Course Materials</h3>
                            <?php
                            // Fetch course materials
                            $sql = "SELECT * FROM course_materials WHERE course_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('i', $course_id);
                            $stmt->execute();
                            $materials = $stmt->get_result();
                            if ($materials->num_rows > 0) {
                                while ($material = $materials->fetch_assoc()) {
                            ?>
                                    <a href="<?php echo COURSE_MATERIAL_PATH . $material['course_material_file']; ?>" class="btn btn-primary mb-2" download><?php echo htmlspecialchars($material['course_material_description']); ?></a><br>
                            <?php
                                }
                            } else {
                                echo "<p>No course materials available.</p>";
                            }
                            ?>
                            <h3 class="mt-4">Assignments</h3>
                            <ul class="list-group mt-2">
                                <?php
                                // Fetch assignments
                                $sql = "SELECT * FROM assignments WHERE course_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $course_id);
                                $stmt->execute();
                                $assignments = $stmt->get_result();

                                while ($assignment = $assignments->fetch_assoc()) {
                                    // Check if assignment is submitted
                                    $sql = "SELECT * FROM submitted_assignments WHERE assignment_id = ? AND student_id = ?";
                                    $stmt_check = $conn->prepare($sql);
                                    $stmt_check->bind_param('ii', $assignment['assignment_id'], $user_id);
                                    $stmt_check->execute();
                                    $submitted_assignment = $stmt_check->get_result()->fetch_assoc();
                                ?>
                                    <li class="list-group-item">
                                        <div>
                                            <strong><?php echo htmlspecialchars($assignment['assignment_name']); ?></strong>
                                            <div class="mt-2">
                                                <a href="<?php echo COURSE_MATERIAL_PATH . $assignment['assignment_material']; ?>" class="btn btn-secondary btn-sm me-2" download>Download</a>
                                                <?php if ($submitted_assignment) { ?>
                                                    <span class="badge bg-success">Submitted</span>
                                                    <?php if (isset($submitted_assignment['grade'])) { ?>
                                                        <span class="badge bg-info">Grade: <?php echo htmlspecialchars($submitted_assignment['grade']); ?></span>
                                                    <?php } ?>
                                                    <form action="utils/_unsubmit_assignment.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to unsubmit this assignment?');">
                                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                                                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Unsubmit</button>
                                                    </form>
                                                <?php } else { ?>
                                                    <a href="submit_assignment.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-primary btn-sm">Submit</a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                            <h3 class="mt-4">Quizzes</h3>
                            <ul class="list-group mt-2">
                                <?php
                                // Fetch quizzes
                                $sql = "SELECT * FROM quizzes WHERE course_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $course_id);
                                $stmt->execute();
                                $quizzes = $stmt->get_result();

                                while ($quiz = $quizzes->fetch_assoc()) {
                                    // Check if quiz is attempted
                                    $sql = "SELECT sq.score, sq.submitted_on, (
                                            SELECT COUNT(*) FROM quizzes_content qc WHERE qc.quiz_id = sq.quiz_id
                                        ) AS total_questions
                                        FROM submitted_quizzes sq
                                        WHERE sq.quiz_id = ? AND sq.student_id = ?";
                                    $stmt_check = $conn->prepare($sql);
                                    $stmt_check->bind_param('ii', $quiz['quiz_id'], $user_id);
                                    $stmt_check->execute();
                                    $submitted_quiz = $stmt_check->get_result()->fetch_assoc();
                                ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($quiz['quiz_name']); ?></span>
                                            <a href="attempt_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-secondary">Attempt Quiz</a>
                                        </div>
                                        <?php
                                        // Fetch all attempts for the current quiz
                                        $sql = "SELECT sq.score, sq.submitted_on, (
                                                    SELECT COUNT(*) FROM quizzes_content qc WHERE qc.quiz_id = sq.quiz_id
                                                ) AS total_questions
                                                FROM submitted_quizzes sq
                                                WHERE sq.quiz_id = ? AND sq.student_id = ?
                                                ORDER BY sq.submitted_on DESC";
                                        $stmt_check = $conn->prepare($sql);
                                        $stmt_check->bind_param('ii', $quiz['quiz_id'], $user_id);
                                        $stmt_check->execute();
                                        $attempts = $stmt_check->get_result()->fetch_all(MYSQLI_ASSOC);

                                        foreach ($attempts as $attempt) {
                                            $score_percentage = ($attempt['score'] / $attempt['total_questions']) * 100;
                                        ?>
                                            <div>
                                                <span class="badge bg-<?php echo ($score_percentage >= 50) ? 'success' : 'danger'; ?>">
                                                    <?php echo ($score_percentage >= 50) ? 'Passed' : 'Failed'; ?> - <?php echo round($score_percentage, 2); ?>%
                                                </span>
                                                <span class="text-muted">Submitted on: <?php echo $attempt['submitted_on']; ?></span>
                                            </div>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <form action="utils/_enroll_course.php" method="post">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <button type="submit" class="btn btn-primary">Enroll in Course</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <!-- Include Bootstrap JS and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script>
        if (window.history.replaceState) {
            const url = new URL(window.location);
            const params = new URLSearchParams(url.search);
            if (params.has('error')) {
                params.delete('error');
                url.search = params.toString();
                window.history.replaceState({
                    path: url.href
                }, "", url.href);
            }
            if (params.has('success')) {
                params.delete('success');
                url.search = params.toString();
                window.history.replaceState({
                    path: url.href
                }, "", url.href);
            }
        }
    </script>
</body>

</html>