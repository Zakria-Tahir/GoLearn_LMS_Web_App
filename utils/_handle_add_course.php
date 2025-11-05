<?php
session_start();
include '_db_connect.php';
include '_globals.php'; // Include the globals file to define constants

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = htmlspecialchars($_POST['course_name']);
    $course_description = htmlspecialchars($_POST['course_description']);
    $category_id = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']);
    $course_educator_id = $_SESSION['user_id'];

    // Handle course thumbnail upload
    if (isset($_FILES['course_thumbnail']) && $_FILES['course_thumbnail']['error'] == 0) {
        $check = getimagesize($_FILES["course_thumbnail"]["tmp_name"]);
        if ($check !== false) {
            $target_dir = '../' . COURSE_IMAGE_PATH;
            $thumbnail = rand(1000, 100000) . "-" . $_FILES['course_thumbnail']['name'];
            $target_file = $target_dir . basename($thumbnail);
            move_uploaded_file($_FILES["course_thumbnail"]["tmp_name"], $target_file);
            $course_thumbnail = $thumbnail; // Store only the image name
        } else {
            header("Location: ../add_course.php?error=" . urlencode("File is not an image."));
            exit();
        }
    } else {
        $course_thumbnail = 'default.png'; // Default thumbnail
    }

    try {
        // Insert course data
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_description, course_thumbnail, category_id, subcategory_id, educator_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssiii', $course_name, $course_description, $course_thumbnail, $category_id, $subcategory_id, $course_educator_id);
        $stmt->execute();

        header("Location: ../educator_panel.php?success=" . urlencode("Course added successfully"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../add_course.php?error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
