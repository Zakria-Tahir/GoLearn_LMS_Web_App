<?php
include '_db_connect.php';
include "../../utils/_globals.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_GET['user_id'])) {
        header('location: ../manage_users.php?error=' . urlencode('User ID is required'));
        exit;
    }

    $user_id = intval($_GET['user_id']);

    // Fetch user details to delete media files
    $sql = "SELECT user_image, user_role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Delete user image file
        $user_image_path = '../' . USER_IMAGE_PATH . $user['user_image'];
        if (file_exists($user_image_path)) {
            unlink($user_image_path);
        }

        // Delete related records if the user is an educator
        if ($user['role'] == 'educator') {
            // Fetch courses created by the educator
            $sql = "SELECT course_id FROM courses WHERE educator_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($course = $result->fetch_assoc()) {
                $course_id = $course['course_id'];

                // Delete related course materials
                $sql = "DELETE FROM course_materials WHERE course_id = ?";
                $stmt_delete = $conn->prepare($sql);
                $stmt_delete->bind_param('i', $course_id);
                $stmt_delete->execute();

                // Delete related assignments
                $sql = "DELETE FROM assignments WHERE course_id = ?";
                $stmt_delete = $conn->prepare($sql);
                $stmt_delete->bind_param('i', $course_id);
                $stmt_delete->execute();

                // Delete related quizzes
                $sql = "DELETE FROM quizzes WHERE course_id = ?";
                $stmt_delete = $conn->prepare($sql);
                $stmt_delete->bind_param('i', $course_id);
                $stmt_delete->execute();

                // Delete the course
                $sql = "DELETE FROM courses WHERE course_id = ?";
                $stmt_delete = $conn->prepare($sql);
                $stmt_delete->bind_param('i', $course_id);
                $stmt_delete->execute();
            }
        }

        // Delete related records from submitted_assignments
        $sql = "DELETE FROM submitted_assignments WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        // Delete related records from submitted_quizzes
        $sql = "DELETE FROM submitted_quizzes WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        // Delete related records from enrollments
        $sql = "DELETE FROM enrollments WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        // Delete the user from the users table
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            error_log('No rows affected: ' . $stmt->error);
            header('location: ../manage_users.php?error=' . urlencode('No rows affected'));
            exit;
        }

        header('location: ../manage_users.php?success=' . urlencode('User deleted successfully'));
        exit;
    } else {
        header('location: ../manage_users.php?error=' . urlencode('User not found'));
        exit;
    }
} else {
    header('location: ../manage_users.php?error=' . urlencode('Invalid request method'));
    exit;
}
