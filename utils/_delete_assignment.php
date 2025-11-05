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

if (isset($_GET['assignment_id'])) {
    $assignment_id = intval($_GET['assignment_id']);
    $educator_id = $_SESSION['user_id'];

    // Fetch the course_id associated with the assignment to ensure the educator owns the course
    $sql = "SELECT course_id FROM assignments WHERE assignment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $assignment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignment = $result->fetch_assoc();

    if (!$assignment) {
        header('location: ../educator_panel.php?error=' . urlencode('Assignment not found or you are not authorized to delete this assignment'));
        exit;
    }

    $course_id = $assignment['course_id'];

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
        // Delete submitted assignments
        $stmt = $conn->prepare("DELETE FROM submitted_assignments WHERE assignment_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('i', $assignment_id);
        $stmt->execute();

        // Delete the assignment
        $stmt = $conn->prepare("DELETE FROM assignments WHERE assignment_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('i', $assignment_id);
        $stmt->execute();

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Assignment deleted successfully"));
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: ../browse_course_educator.php?course_id=$course_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
} else {
    header('location: ../educator_panel.php?error=' . urlencode('Invalid assignment ID'));
    exit;
}
