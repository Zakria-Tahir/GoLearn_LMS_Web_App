<?php
include '_db_connect.php';
include '../../utils/_globals.php';

if (isset($_GET['id'])) {
    $subcategory_id = intval($_GET['id']);

    // Fetch subcategory details to get the image path
    $sql = "SELECT subcategory_thumbnail FROM subcategories WHERE subcategory_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subcategory_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategory = $result->fetch_assoc();

    if ($subcategory) {
        // Delete the image file from the server
        $image_path = "../../" . SUBCATEGORY_IMAGE_PATH . $subcategory['subcategory_thumbnail'];
        if (file_exists($image_path) && $subcategory['subcategory_thumbnail'] != 'default-subcategory.png') {
            unlink($image_path);
        }

        // Fetch and delete related courses
        $sql = "SELECT course_id FROM courses WHERE subcategory_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($course = $result->fetch_assoc()) {
            $course_id = $course['course_id'];

            // Delete related course materials
            $sql = "DELETE FROM course_materials WHERE course_id = ?";
            $stmt_delete = $conn->prepare($sql);
            $stmt_delete->bind_param("i", $course_id);
            $stmt_delete->execute();

            // Delete related assignments
            $sql = "DELETE FROM assignments WHERE course_id = ?";
            $stmt_delete = $conn->prepare($sql);
            $stmt_delete->bind_param("i", $course_id);
            $stmt_delete->execute();

            // Delete related quizzes
            $sql = "DELETE FROM quizzes WHERE course_id = ?";
            $stmt_delete = $conn->prepare($sql);
            $stmt_delete->bind_param("i", $course_id);
            $stmt_delete->execute();

            // Delete the course
            $sql = "DELETE FROM courses WHERE course_id = ?";
            $stmt_delete = $conn->prepare($sql);
            $stmt_delete->bind_param("i", $course_id);
            $stmt_delete->execute();
        }

        // Delete the subcategory record from the database
        $sql = "DELETE FROM subcategories WHERE subcategory_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $subcategory_id);

        if ($stmt->execute()) {
            header("Location: ../manage_subcategories.php?success=" . urlencode("Subcategory and related courses deleted successfully."));
        } else {
            header("Location: ../manage_subcategories.php?error=" . urlencode("Error deleting subcategory."));
        }
    } else {
        header("Location: ../manage_subcategories.php?error=" . urlencode("Subcategory not found."));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../manage_subcategories.php?error=" . urlencode("Invalid request."));
}
