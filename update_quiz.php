<?php
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

if (!isset($_GET['quiz_id'])) {
    header('location: educator_panel.php?error=' . urlencode('Quiz ID is required'));
    exit;
}

$quiz_id = intval($_GET['quiz_id']);
$educator_id = $_SESSION['user_id'];

// Fetch quiz data to ensure the educator owns the quiz
$sql = "SELECT * FROM quizzes WHERE quiz_id = ? AND course_id IN (SELECT course_id FROM courses WHERE educator_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $quiz_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();

if (!$quiz) {
    header('location: educator_panel.php?error=' . urlencode('Quiz not found or you are not authorized to update this quiz'));
    exit;
}

// Fetch quiz questions
$sql = "SELECT * FROM quizzes_content WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$quiz_questions = $stmt->get_result();
?>

<?php
include "header.php";
?>
<title>Update Quiz | GoLearn</title>

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

            <div class="container p-5">
                <h1 class="text-center">Update Quiz</h1>

                <form action="utils/_handle_update_quiz.php" method="post">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <div class="form-group my-2">
                        <label for="quiz_title" class="form-label">Quiz Title:</label>
                        <input type="text" name="quiz_title" class="form-control" value="<?php echo htmlspecialchars($quiz['quiz_name']); ?>" required>
                    </div>
                    <div class="form-group my-2">
                        <label for="quiz_description" class="form-label">Quiz Description:</label>
                        <textarea name="quiz_description" class="form-control" required><?php echo htmlspecialchars($quiz['quiz_description']); ?></textarea>
                    </div>
                    <div id="questions-container" class="mt-4">
                        <h4>Questions</h4>
                        <?php
                        $question_count = 1;
                        while ($question = $quiz_questions->fetch_assoc()) {
                        ?>
                            <div class="question-item mb-3">
                                <label for="question_text_<?php echo $question_count; ?>" class="form-label">Question <?php echo $question_count; ?>:</label>
                                <textarea name="questions[<?php echo $question_count; ?>][question_text]" class="form-control" required><?php echo htmlspecialchars($question['quiz_question']); ?></textarea>
                                <label for="option1_<?php echo $question_count; ?>" class="form-label mt-2">Option 1:</label>
                                <input type="text" name="questions[<?php echo $question_count; ?>][option1]" class="form-control" value="<?php echo htmlspecialchars($question['option1']); ?>" required>
                                <label for="option2_<?php echo $question_count; ?>" class="form-label mt-2">Option 2:</label>
                                <input type="text" name="questions[<?php echo $question_count; ?>][option2]" class="form-control" value="<?php echo htmlspecialchars($question['option2']); ?>" required>
                                <label for="option3_<?php echo $question_count; ?>" class="form-label mt-2">Option 3:</label>
                                <input type="text" name="questions[<?php echo $question_count; ?>][option3]" class="form-control" value="<?php echo htmlspecialchars($question['option3']); ?>" required>
                                <label for="option4_<?php echo $question_count; ?>" class="form-label mt-2">Option 4:</label>
                                <input type="text" name="questions[<?php echo $question_count; ?>][option4]" class="form-control" value="<?php echo htmlspecialchars($question['option4']); ?>" required>
                                <label for="correct_option_<?php echo $question_count; ?>" class="form-label mt-2">Correct Option:</label>
                                <select name="questions[<?php echo $question_count; ?>][correct_option]" class="form-control" required>
                                    <option value="option1" <?php echo ($question['correct_option'] == 'option1') ? 'selected' : ''; ?>>Option 1</option>
                                    <option value="option2" <?php echo ($question['correct_option'] == 'option2') ? 'selected' : ''; ?>>Option 2</option>
                                    <option value="option3" <?php echo ($question['correct_option'] == 'option3') ? 'selected' : ''; ?>>Option 3</option>
                                    <option value="option4" <?php echo ($question['correct_option'] == 'option4') ? 'selected' : ''; ?>>Option 4</option>
                                </select>
                            </div>
                        <?php
                            $question_count++;
                        }
                        ?>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="add-question-btn">Add Another Question</button>
                    <button type="submit" class="btn btn-primary mt-3">Update Quiz</button>
                </form>
            </div>

            <?php
            include 'footer.php';
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script>
        document.getElementById('add-question-btn').addEventListener('click', function() {
            const questionsContainer = document.getElementById('questions-container');
            const questionCount = questionsContainer.getElementsByClassName('question-item').length + 1;
            const questionItem = document.createElement('div');
            questionItem.className = 'question-item mb-3';
            questionItem.innerHTML = `
                <label for="question_text_${questionCount}" class="form-label">Question ${questionCount}:</label>
                <textarea name="questions[${questionCount}][question_text]" class="form-control" required></textarea>
                <label for="option1_${questionCount}" class="form-label mt-2">Option 1:</label>
                <input type="text" name="questions[${questionCount}][option1]" class="form-control" required>
                <label for="option2_${questionCount}" class="form-label mt-2">Option 2:</label>
                <input type="text" name="questions[${questionCount}][option2]" class="form-control" required>
                <label for="option3_${questionCount}" class="form-label mt-2">Option 3:</label>
                <input type="text" name="questions[${questionCount}][option3]" class="form-control" required>
                <label for="option4_${questionCount}" class="form-label mt-2">Option 4:</label>
                <input type="text" name="questions[${questionCount}][option4]" class="form-control" required>
                <label for="correct_option_${questionCount}" class="form-label mt-2">Correct Option:</label>
                <select name="questions[${questionCount}][correct_option]" class="form-control" required>
                    <option value="option1">Option 1</option>
                    <option value="option2">Option 2</option>
                    <option value="option3">Option 3</option>
                    <option value="option4">Option 4</option>
                </select>
            `;
            questionsContainer.appendChild(questionItem);
        });

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