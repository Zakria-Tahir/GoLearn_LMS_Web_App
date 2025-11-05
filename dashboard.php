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
$username = $_SESSION['username'];

// Fetch enrolled courses
$sql = "SELECT c.course_id, c.course_name, c.course_description, c.course_thumbnail 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        WHERE e.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

// Fetch missing assignments and quizzes
$missing_items = [];

// Reset the result pointer to the beginning for enrolled courses
$enrolled_courses->data_seek(0);
while ($course = $enrolled_courses->fetch_assoc()) {
    $course_id = $course['course_id'];

    // Fetch missing assignments
    $sql = "SELECT a.assignment_id, a.assignment_name, ? as course_name
            FROM assignments a 
            LEFT JOIN submitted_assignments sa ON a.assignment_id = sa.assignment_id AND sa.student_id = ? 
            WHERE a.course_id = ? AND sa.assignment_id IS NULL";
    $assignment_stmt = $conn->prepare($sql);
    $assignment_stmt->bind_param('sii', $course['course_name'], $user_id, $course_id);
    $assignment_stmt->execute();
    $result = $assignment_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'Assignment';
        $missing_items[] = $row;
    }

    // Fetch missing quizzes
    $sql = "SELECT q.quiz_id, q.quiz_name, ? as course_name
            FROM quizzes q 
            LEFT JOIN submitted_quizzes sq ON q.quiz_id = sq.quiz_id AND sq.student_id = ? 
            WHERE q.course_id = ? AND sq.quiz_id IS NULL";
    $quiz_stmt = $conn->prepare($sql);
    $quiz_stmt->bind_param('sii', $course['course_name'], $user_id, $course_id);
    $quiz_stmt->execute();
    $result = $quiz_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'Quiz';
        $missing_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<?php
include "header.php";
?>
<title>Home | GoLearn </title>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main">
            <?php include 'navbar.php'; ?>
            <div class="container p-5">
                <h1 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

                <?php if (!empty($missing_items)) { ?>
                    <div class="mt-5">
                        <h3>Missing Assignments and Quizzes</h3>
                        <ul class="list-group mt-3">
                            <?php foreach ($missing_items as $item) { ?>
                                <li class="list-group-item">
                                    <i class="fa-solid fa-exclamation-circle text-warning"></i>
                                    Missing <?php echo $item['type']; ?>: <?php echo htmlspecialchars($item[$item['type'] == 'Assignment' ? 'assignment_name' : 'quiz_name']); ?> - Course: <?php echo htmlspecialchars($item['course_name']); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <div class="d-flex justify-content-between align-items-center mt-5">
                    <h3>Your Enrolled Courses</h3>
                    <a href="browse_courses.php" class="btn btn-primary">
                        <i class="fa-solid fa-book pe-2"></i>
                        Browse Courses
                    </a>
                </div>

                <?php if ($enrolled_courses->num_rows > 0) { ?>
                    <div class="mt-4">
                        <?php
                        $enrolled_courses->data_seek(0); // Reset the result pointer
                        while ($course = $enrolled_courses->fetch_assoc()) {
                            // Fetch total and completed assignments
                            $sql = "SELECT COUNT(*) as total_assignments FROM assignments WHERE course_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('i', $course['course_id']);
                            $stmt->execute();
                            $total_assignments = $stmt->get_result()->fetch_assoc()['total_assignments'];

                            $sql = "SELECT COUNT(*) as completed_assignments FROM submitted_assignments WHERE student_id = ? AND assignment_id IN (SELECT assignment_id FROM assignments WHERE course_id = ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('ii', $user_id, $course['course_id']);
                            $stmt->execute();
                            $completed_assignments = $stmt->get_result()->fetch_assoc()['completed_assignments'];

                            // Fetch total quizzes
                            $sql = "SELECT COUNT(*) as total_quizzes FROM quizzes WHERE course_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('i', $course['course_id']);
                            $stmt->execute();
                            $total_quizzes = $stmt->get_result()->fetch_assoc()['total_quizzes'];

                            // Fetch completed quizzes (only passed quizzes, count each quiz only once)
                            $sql = "SELECT sq.quiz_id, MAX(sq.score) as max_score, (
                                        SELECT COUNT(*) FROM quizzes_content qc WHERE qc.quiz_id = sq.quiz_id
                                    ) AS total_questions
                                    FROM submitted_quizzes sq
                                    WHERE sq.student_id = ? AND sq.quiz_id IN (SELECT quiz_id FROM quizzes WHERE course_id = ?)
                                    GROUP BY sq.quiz_id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('ii', $user_id, $course['course_id']);
                            $stmt->execute();
                            $submitted_quizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                            $completed_quizzes = 0;
                            foreach ($submitted_quizzes as $quiz) {
                                $score_percentage = ($quiz['max_score'] / $quiz['total_questions']) * 100;
                                if ($score_percentage >= 50) {
                                    $completed_quizzes++;
                                }
                            }
                        ?>
                            <div class="card mb-4 p-2">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="<?php echo COURSE_IMAGE_PATH . $course['course_thumbnail']; ?>" class="img-fluid rounded-start" alt="Course Thumbnail" style="height: 100%; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($course['course_description']); ?></p>
                                            <p class="card-text">
                                                Assignments Completed: <?php echo $completed_assignments; ?>/<?php echo $total_assignments; ?><br>
                                                Quizzes Completed: <?php echo $completed_quizzes; ?>/<?php echo $total_quizzes; ?>
                                            </p>
                                            <a href="course_details.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary">View Course Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info mt-4" role="alert">
                        <i class="fa-solid fa-info-circle"></i> You are not enrolled in any courses. Please browse and enroll in courses.
                    </div>
                <?php } ?>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script>
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.search = "";
            window.history.replaceState({
                    path: url.href,
                },
                "",
                url.href
            );
        }
    </script>
</body>

</html>