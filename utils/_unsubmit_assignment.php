<?php
include '_db_connect.php';
include '_globals.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['assignment_id'])) {
        header('location: ../course_details.php?error=' . urlencode('Assignment ID is required'));
        exit;
    }

    $assignment_id = intval($_POST['assignment_id']);
    $course_id = intval($_POST['course_id']);

    // Fetch the file path of the submitted assignment
    $sql = "SELECT submission_file FROM submitted_assignments WHERE assignment_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $assignment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submitted_assignment = $result->fetch_assoc();

    if ($submitted_assignment) {
        $file_path = '../' . SUBMITTED_ASSIGNMENT_PATH . $submitted_assignment['submission_file'];

        // Delete the assignment submission record
        $sql = "DELETE FROM submitted_assignments WHERE assignment_id = ? AND student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $assignment_id, $user_id);
        $stmt->execute();

        // Delete the file from the server
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        header('location: ../course_details.php?course_id=' . $course_id . '&success=' . urlencode('Assignment unsubmitted successfully'));
        exit;
    } else {
        header('location: ../course_details.php?course_id=' . $course_id . '&error=' . urlencode('Assignment submission not found'));
        exit;
    }
}
