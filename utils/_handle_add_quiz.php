<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SESSION['user_role'] != 'educator') {
    header('location: ../index.php?error=' . urlencode('You are not authorized to access this page'));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = intval($_POST['course_id']);
    $quiz_name = htmlspecialchars($_POST['quiz_title']);
    $quiz_description = htmlspecialchars($_POST['quiz_description']);
    $educator_id = $_SESSION['user_id'];

    try {
        // Insert quiz data
        $stmt = $conn->prepare("INSERT INTO quizzes (course_id, quiz_name, quiz_description, added_on, updated_on) VALUES (?, ?, ?, NOW(), NOW())");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('iss', $course_id, $quiz_name, $quiz_description);
        $stmt->execute();
        $quiz_id = $stmt->insert_id;

        // Insert quiz questions
        foreach ($_POST['questions'] as $question) {
            $question_text = htmlspecialchars($question['question_text']);
            $option1 = htmlspecialchars($question['option1']);
            $option2 = htmlspecialchars($question['option2']);
            $option3 = htmlspecialchars($question['option3']);
            $option4 = htmlspecialchars($question['option4']);
            $correct_option = htmlspecialchars($question['correct_option']);

            $stmt = $conn->prepare("INSERT INTO quizzes_content (quiz_id, quiz_question, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            $stmt->bind_param('issssss', $quiz_id, $question_text, $option1, $option2, $option3, $option4, $correct_option);
            $stmt->execute();
        }

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Quiz added successfully"));
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: ../add_quiz.php?course_id=$course_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
