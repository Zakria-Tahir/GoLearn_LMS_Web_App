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

if (isset($_GET['quiz_id'])) {
    $quiz_id = intval($_GET['quiz_id']);
    $educator_id = $_SESSION['user_id'];

    // Fetch the course_id associated with the quiz to ensure the educator owns the course
    $sql = "SELECT course_id FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();

    if (!$quiz) {
        header('location: ../educator_panel.php?error=' . urlencode('Quiz not found or you are not authorized to delete this quiz'));
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
        // Delete quiz questions
        $stmt = $conn->prepare("DELETE FROM quizzes_content WHERE quiz_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('i', $quiz_id);
        $stmt->execute();

        // Delete the quiz
        $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('i', $quiz_id);
        $stmt->execute();

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Quiz deleted successfully"));
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: ../browse_course_educator.php?course_id=$course_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
} else {
    header('location: ../educator_panel.php?error=' . urlencode('Invalid quiz ID'));
    exit;
}
