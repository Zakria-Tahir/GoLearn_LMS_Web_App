<?php
session_start();
include '_db_connect.php';
include "./_globals.php";

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
    $material_description = htmlspecialchars($_POST['material_description']);
    $educator_id = $_SESSION['user_id'];

    // Handle material file upload
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $target_dir = '../' . COURSE_MATERIAL_PATH;
        $material = rand(1000, 100000) . "-" . $_FILES['material_file']['name'];
        $target_file = $target_dir . basename($material);
        move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file);
        $material_file = $material; // Store only the file name
    } else {
        $material_file = ''; // No material uploaded
    }

    try {
        // Insert material data
        $stmt = $conn->prepare("INSERT INTO course_materials (course_id, course_material_description, course_material_file) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $course_id, $material_description, $material_file);
        $stmt->execute();

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Material added successfully"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../add_material.php?course_id=$course_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
