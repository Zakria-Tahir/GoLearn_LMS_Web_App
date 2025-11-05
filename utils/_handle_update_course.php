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
    $course_id = intval($_POST['course_id']);
    $course_name = htmlspecialchars($_POST['course_name']);
    $course_description = htmlspecialchars($_POST['course_description']);
    $category_id = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']);
    $course_educator_id = $_SESSION['user_id'];

    // Fetch current course data
    $sql = "SELECT course_thumbnail FROM courses WHERE course_id = ? AND educator_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $course_id, $course_educator_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    // Handle course thumbnail upload
    if (isset($_FILES['course_thumbnail']) && $_FILES['course_thumbnail']['error'] == 0) {
        $target_dir = '../' . COURSE_IMAGE_PATH;
        $thumbnail = rand(1000, 100000) . "-" . $_FILES['course_thumbnail']['name'];
        $target_file = $target_dir . basename($thumbnail);

        // Remove the old image if it exists and is not the default image
        if ($course['course_thumbnail'] != 'default.png' && file_exists($target_dir . $course['course_thumbnail'])) {
            unlink($target_dir . $course['course_thumbnail']);
        }

        move_uploaded_file($_FILES["course_thumbnail"]["tmp_name"], $target_file);
        $course_thumbnail = $thumbnail; // Store only the image name
    } else {
        $course_thumbnail = $course['course_thumbnail']; // Keep the current thumbnail if no new thumbnail is provided
    }

    try {
        // Update course data
        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, course_description = ?, course_thumbnail = ?, category_id = ?, subcategory_id = ? WHERE course_id = ? AND educator_id = ?");
        $stmt->bind_param('sssiiii', $course_name, $course_description, $course_thumbnail, $category_id, $subcategory_id, $course_id, $course_educator_id);
        $stmt->execute();

        header("Location: ../educator_panel.php?success=" . urlencode("Course updated successfully"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../update_course.php?course_id=$course_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
