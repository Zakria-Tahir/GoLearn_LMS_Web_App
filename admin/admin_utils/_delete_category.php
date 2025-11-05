<?php
include '_db_connect.php';
include '../../utils/_globals.php';

if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);

    // Fetch category details to get the image path
    $sql = "SELECT category_thumbnail FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();

    if ($category) {
        // Delete the image file from the server
        $image_path = "../../" . CATEGORY_IMAGE_PATH . $category['category_thumbnail'];
        if (file_exists($image_path) && $category['category_thumbnail'] != 'default-category.png') {
            unlink($image_path);
        }

        // Fetch and delete related subcategories and their courses
        $sql = "SELECT subcategory_id FROM subcategories WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($subcategory = $result->fetch_assoc()) {
            $subcategory_id = $subcategory['subcategory_id'];

            // Fetch and delete related courses
            $sql = "SELECT course_id FROM courses WHERE subcategory_id = ?";
            $stmt_sub = $conn->prepare($sql);
            $stmt_sub->bind_param("i", $subcategory_id);
            $stmt_sub->execute();
            $result_sub = $stmt_sub->get_result();

            while ($course = $result_sub->fetch_assoc()) {
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

            // Delete the subcategory
            $sql = "DELETE FROM subcategories WHERE subcategory_id = ?";
            $stmt_delete = $conn->prepare($sql);
            $stmt_delete->bind_param("i", $subcategory_id);
            $stmt_delete->execute();
        }

        // Delete the category record from the database
        $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);

        if ($stmt->execute()) {
            header("Location: ../manage_categories.php?success=" . urlencode("Category and related subcategories and courses deleted successfully."));
        } else {
            header("Location: ../manage_categories.php?error=" . urlencode("Error deleting category."));
        }
    } else {
        header("Location: ../manage_categories.php?error=" . urlencode("Category not found."));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../manage_categories.php?error=" . urlencode("Invalid request."));
}
