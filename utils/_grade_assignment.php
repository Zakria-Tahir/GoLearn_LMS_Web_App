<?php
include '_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SESSION['user_role'] != 'educator') {
    header('location: ../index.php?error=' . urlencode('You are not authorized to access this page'));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['assignment_id']) || !isset($_POST['student_id']) || !isset($_POST['grade']) || !isset($_POST['course_id'])) {
        header('location: ../browse_course_educator.php?error=' . urlencode('All fields are required'));
        exit;
    }

    $assignment_id = intval($_POST['assignment_id']);
    $student_id = intval($_POST['student_id']);
    $grade = intval($_POST['grade']);
    $course_id = intval($_POST['course_id']);

    echo $assignment_id;
    echo $student_id;
    echo $grade;
    echo $course_id;

    // Update the grade for the submitted assignment
    $sql  = "UPDATE `submitted_assignments` SET `grade` = ? WHERE `submitted_assignments`.`submission_id` = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('Failed to prepare statement: ' . $conn->error);
        header('location: ../browse_course_educator.php?course_id=' . $course_id . '&error=' . urlencode('Failed to prepare statement'));
        exit;
    }
    $stmt->bind_param('ii', $grade, $assignment_id);
    if ($stmt->execute() === false) {
        error_log('Failed to execute statement: ' . $stmt->error);
        header('location: ../browse_course_educator.php?course_id=' . $course_id . '&error=' . urlencode('Failed to execute statement'));
        exit;
    }

    if ($stmt->affected_rows === 0) {
        error_log('No rows affected: ' . $stmt->error);
        header('location: ../browse_course_educator.php?course_id=' . $course_id . '&error=' . urlencode('No rows affected'));
        exit;
    }

    header('location: ../browse_course_educator.php?course_id=' . $course_id . '&success=' . urlencode('Assignment graded successfully'));
    exit;
} else {
    header('location: ../course_details.php?error=' . urlencode('Invalid request method'));
    exit;
}
