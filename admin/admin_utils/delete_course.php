<?php
session_start();
include '_db_connect.php';
include '../../utils/_globals.php';

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header('location: home.php');
    exit;
}

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);

    // Fetch the current course data
    $sql = "SELECT course_thumbnail FROM courses WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if ($course) {
        // Delete the course thumbnail if it exists and is not the default image
        if ($course['course_thumbnail'] != 'default.png' && file_exists('../' . COURSE_IMAGE_PATH . $course['course_thumbnail'])) {
            unlink('../' . COURSE_IMAGE_PATH . $course['course_thumbnail']);
        }

        // Delete submitted assignments related to the course
        $sql = "DELETE FROM submitted_assignments WHERE assignment_id IN (SELECT assignment_id FROM assignments WHERE course_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        // Fetch and delete assignment files
        $sql = "SELECT assignment_material FROM assignments WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($assignment = $result->fetch_assoc()) {
            if (file_exists('../' . COURSE_MATERIAL_PATH . $assignment['assignment_material'])) {
                unlink('../' . COURSE_MATERIAL_PATH . $assignment['assignment_material']);
            }
        }

        // Delete assignments related to the course
        $sql = "DELETE FROM assignments WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        // Delete submitted quizzes related to the course
        $sql = "DELETE FROM submitted_quizzes WHERE quiz_id IN (SELECT quiz_id FROM quizzes WHERE course_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        // Delete quizzes related to the course
        $sql = "DELETE FROM quizzes WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        // Fetch and delete course materials files
        $sql = "SELECT course_material_file FROM course_materials WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($material = $result->fetch_assoc()) {
            if (file_exists('../' . COURSE_MATERIAL_PATH . $material['course_material_file'])) {
                unlink('../' . COURSE_MATERIAL_PATH . $material['course_material_file']);
            }
        }

        // Delete course materials related to the course
        $sql = "DELETE FROM course_materials WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        // Delete enrollments related to the course
        $sql = "DELETE FROM enrollments WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        // Finally, delete the course from the database
        $sql = "DELETE FROM courses WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $course_id);
        $stmt->execute();

        header("Location: ../manage_courses.php?success=" . urlencode("Course deleted successfully"));
        exit();
    } else {
        header("Location: manage_courses.php?error=" . urlencode("Course not found"));
        exit();
    }
} else {
    header("Location: manage_courses.php?error=" . urlencode("Invalid course ID"));
    exit();
}
