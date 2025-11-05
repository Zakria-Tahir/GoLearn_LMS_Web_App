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
    $quiz_id = intval($_POST['quiz_id']);
    $quiz_name = htmlspecialchars($_POST['quiz_title']);
    $quiz_description = htmlspecialchars($_POST['quiz_description']);
    $educator_id = $_SESSION['user_id'];

    // Fetch course_id associated with the quiz to ensure the educator owns the course
    $sql = "SELECT course_id FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();

    if (!$quiz) {
        header('location: ../educator_panel.php?error=' . urlencode('Quiz not found or you are not authorized to update this quiz'));
        exit;
    }

    $course_id = $quiz['course_id'];

    // Check if the educator owns the course
    $sql = "SELECT * FROM courses WHERE course_id = ? AND educator_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $educator_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if (!$course) {
        header('location: ../educator_panel.php?error=' . urlencode('Course not found or you are not authorized to view this course'));
        exit;
    }

    try {
        // Update quiz data
        $stmt = $conn->prepare("UPDATE quizzes SET quiz_name = ?, quiz_description = ?, updated_on = NOW() WHERE quiz_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('ssi', $quiz_name, $quiz_description, $quiz_id);
        $stmt->execute();

        // Delete existing quiz questions
        $stmt = $conn->prepare("DELETE FROM quizzes_content WHERE quiz_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('i', $quiz_id);
        $stmt->execute();

        // Insert updated quiz questions
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

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Quiz updated successfully"));
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: ../update_quiz.php?quiz_id=$quiz_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
