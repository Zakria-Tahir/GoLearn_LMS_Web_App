<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SESSION['user_role'] != 'student') {
    header('location: ../index.php?error=' . urlencode('You are not authorized to access this page'));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = intval($_POST['course_id']);
    $student_id = $_SESSION['user_id'];

    // Check if the student is already enrolled in the course
    $sql = "SELECT * FROM enrollments WHERE course_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header("Location: ../course_details.php?course_id=$course_id&error=" . urlencode("You are already enrolled in this course"));
        exit();
    }

    // Enroll the student in the course
    $sql = "INSERT INTO enrollments (course_id, student_id, enrolled_on, status) VALUES (?, ?, NOW(), 'not completed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $student_id);
    $stmt->execute();

    header("Location: ../course_details.php?course_id=$course_id&success=" . urlencode("You have been enrolled in the course successfully"));
    exit();
}
