<?php
session_start();
include '_db_connect.php';
include '_globals.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SESSION['user_role'] != 'educator') {
    header('location: ../index.php?error=' . urlencode('You are not authorized to access this page'));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = intval($_POST['assignment_id']);
    $assignment_name = htmlspecialchars($_POST['assignment_name']);
    $assignment_description = htmlspecialchars($_POST['assignment_description']);
    $educator_id = $_SESSION['user_id'];

    // Fetch course_id associated with the assignment to ensure the educator owns the course
    $sql = "SELECT course_id FROM assignments WHERE assignment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $assignment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignment = $result->fetch_assoc();

    if (!$assignment) {
        header('location: ../educator_panel.php?error=' . urlencode('Assignment not found or you are not authorized to update this assignment'));
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

    // Handle assignment material upload
    if (isset($_FILES['assignment_material']) && $_FILES['assignment_material']['error'] == 0) {
        $target_dir = '../' . COURSE_MATERIAL_PATH;
        $material = rand(1000, 100000) . "-" . $_FILES['assignment_material']['name'];
        $target_file = $target_dir . basename($material);

        // Remove the old material if it exists
        if ($assignment['assignment_material'] && file_exists($target_dir . $assignment['assignment_material'])) {
            unlink($target_dir . $assignment['assignment_material']);
        }

        move_uploaded_file($_FILES["assignment_material"]["tmp_name"], $target_file);
        $assignment_material = $material; // Store only the file name
    } else {
        $assignment_material = $assignment['assignment_material']; // Keep the current material if no new material is provided
    }

    try {
        // Update assignment data
        $stmt = $conn->prepare("UPDATE assignments SET assignment_name = ?, assignment_description = ?, assignment_material = ?, updated_on = NOW() WHERE assignment_id = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('sssi', $assignment_name, $assignment_description, $assignment_material, $assignment_id);
        $stmt->execute();

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Assignment updated successfully"));
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: ../update_assignment.php?assignment_id=$assignment_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
