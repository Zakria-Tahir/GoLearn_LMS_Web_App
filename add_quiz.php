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

if (!isset($_GET['course_id'])) {
    header('location: educator_panel.php?error=' . urlencode('Course ID is required'));
    exit;
}

$course_id = intval($_GET['course_id']);
$educator_id = $_SESSION['user_id'];

// Fetch course data to ensure the educator owns the course
$sql = "SELECT * FROM courses WHERE course_id = ? AND educator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $course_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    header('location: educator_panel.php?error=' . urlencode('Course not found or you are not authorized to add quizzes to this course'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Add Quiz | GoLearn</title>

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
                <h1 class="text-center">Add Quiz</h1>

                <form action="utils/_handle_add_quiz.php" method="post">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <div class="form-group my-2">
                        <label for="quiz_title" class="form-label">Quiz Title:</label>
                        <input type="text" name="quiz_title" class="form-control" required>
                    </div>
                    <div class="form-group my-2">
                        <label for="quiz_description" class="form-label">Quiz Description:</label>
                        <textarea name="quiz_description" class="form-control" required></textarea>
                    </div>
                    <div id="questions-container" class="mt-4">
                        <h4>Questions</h4>
                        <div class="question-item mb-3">
                            <label for="question_text_1" class="form-label">Question 1:</label>
                            <textarea name="questions[1][question_text]" class="form-control" required></textarea>
                            <label for="option1_1" class="form-label mt-2">Option 1:</label>
                            <input type="text" name="questions[1][option1]" class="form-control" required>
                            <label for="option2_1" class="form-label mt-2">Option 2:</label>
                            <input type="text" name="questions[1][option2]" class="form-control" required>
                            <label for="option3_1" class="form-label mt-2">Option 3:</label>
                            <input type="text" name="questions[1][option3]" class="form-control" required>
                            <label for="option4_1" class="form-label mt-2">Option 4:</label>
                            <input type="text" name="questions[1][option4]" class="form-control" required>
                            <label for="correct_option_1" class="form-label mt-2">Correct Option:</label>
                            <select name="questions[1][correct_option]" class="form-control" required>
                                <option value="option1">Option 1</option>
                                <option value="option2">Option 2</option>
                                <option value="option3">Option 3</option>
                                <option value="option4">Option 4</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="add-question-btn">Add Another Question</button>
                    <button type="submit" class="btn btn-primary mt-3">Add Quiz</button>
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