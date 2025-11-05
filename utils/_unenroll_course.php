<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['course_id'])) {
        header('location: ../course_details.php?error=' . urlencode('Course ID is required'));
        exit;
    }

    $course_id = intval($_POST['course_id']);

    // Check if the user is enrolled in the course
    $sql = "SELECT * FROM enrollments WHERE course_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $user_id);
    $stmt->execute();
    $enrollment = $stmt->get_result()->fetch_assoc();

    if (!$enrollment) {
        header('location: ../course_details.php?course_id=' . $course_id . '&error=' . urlencode('You are not enrolled in this course'));
        exit;
    }

    // Unenroll the user from the course
    $sql = "DELETE FROM enrollments WHERE course_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $user_id);
    $stmt->execute();

    // Remove the user's submitted assignments for the course
    $sql = "DELETE FROM submitted_assignments WHERE assignment_id IN (SELECT assignment_id FROM assignments WHERE course_id = ?) AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $user_id);
    $stmt->execute();

    // Remove the user's submitted quizzes for the course
    $sql = "DELETE FROM submitted_quizzes WHERE quiz_id IN (SELECT quiz_id FROM quizzes WHERE course_id = ?) AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $user_id);
    $stmt->execute();

    header('location: ../course_details.php?course_id=' . $course_id . '&success=' . urlencode('You have been unenrolled from the course successfully'));
    exit;
}
