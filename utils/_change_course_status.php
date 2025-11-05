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

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $educator_id = $_SESSION['user_id'];

    // Fetch the current status of the course
    $sql = "SELECT status FROM courses WHERE course_id = ? AND educator_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $educator_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if ($course) {
        // Toggle the status
        $new_status = ($course['status'] == 'active') ? 'inactive' : 'active';

        // Update the course status
        $sql = "UPDATE courses SET status = ? WHERE course_id = ? AND educator_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $new_status, $course_id, $educator_id);
        $stmt->execute();

        header("Location: ../educator_panel.php?success=" . urlencode("Course status updated successfully"));
        exit();
    } else {
        header("Location: ../educator_panel.php?error=" . urlencode("Course not found or you are not authorized to change its status"));
        exit();
    }
} else {
    header("Location: ../educator_panel.php?error=" . urlencode("Invalid course ID"));
    exit();
}
