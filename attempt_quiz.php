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

if (!isset($_GET['quiz_id'])) {
    header('location: course_details.php?error=' . urlencode('Quiz ID is required'));
    exit;
}

$quiz_id = intval($_GET['quiz_id']);

// Fetch quiz details
$sql = "SELECT * FROM quizzes WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();

if (!$quiz) {
    header('location: course_details.php?error=' . urlencode('Quiz not found'));
    exit;
}

// Fetch quiz questions
$sql = "SELECT * FROM quizzes_content WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = 0;
    $total_questions = count($questions);

    foreach ($questions as $question) {
        $question_id = $question['quiz_content_id'];
        $correct_option = $question['correct_option'];
        $selected_option = $_POST['question_' . $question_id];

        if ($selected_option == $correct_option) {
            $score++;
        }
    }

    // Save the quiz result
    $sql = "INSERT INTO submitted_quizzes (quiz_id, student_id, score) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $quiz_id, $user_id, $score);
    $stmt->execute();

    header('location: course_details.php?course_id=' . $quiz['course_id'] . '&success=' . urlencode('Quiz submitted successfully'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Attempt Quiz | GoLearn</title>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <div class="container p-5">
                <h1 class="text-center"><?php echo htmlspecialchars($quiz['quiz_name']); ?></h1>
                <form action="attempt_quiz.php?quiz_id=<?php echo $quiz_id; ?>" method="post">
                    <?php foreach ($questions as $question) { ?>
                        <div class="mb-4">
                            <h5><?php echo htmlspecialchars($question['quiz_question']); ?></h5>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="question_<?php echo $question['quiz_content_id']; ?>" value="option1" required>
                                <label class="form-check-label">
                                    <?php echo htmlspecialchars($question['option1']); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="question_<?php echo $question['quiz_content_id']; ?>" value="option2" required>
                                <label class="form-check-label">
                                    <?php echo htmlspecialchars($question['option2']); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="question_<?php echo $question['quiz_content_id']; ?>" value="option3" required>
                                <label class="form-check-label">
                                    <?php echo htmlspecialchars($question['option3']); ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="question_<?php echo $question['quiz_content_id']; ?>" value="option4" required>
                                <label class="form-check-label">
                                    <?php echo htmlspecialchars($question['option4']); ?>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                    <button type="submit" class="btn btn-primary">Submit Quiz</button>
                </form>
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
        }
    </script>
</body>

</html>