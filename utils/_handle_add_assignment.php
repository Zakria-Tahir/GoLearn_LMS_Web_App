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
    $assignment_name = htmlspecialchars($_POST['assignment_name']);
    $assignment_description = htmlspecialchars($_POST['assignment_description']);
    $educator_id = $_SESSION['user_id'];

    // Handle assignment material upload
    if (isset($_FILES['assignment_material']) && $_FILES['assignment_material']['error'] == 0) {
        $target_dir = '../' . COURSE_MATERIAL_PATH;
        $material = rand(1000, 100000) . "-" . $_FILES['assignment_material']['name'];
        $target_file = $target_dir . basename($material);
        move_uploaded_file($_FILES["assignment_material"]["tmp_name"], $target_file);
        $assignment_material = $material; // Store only the file name
    } else {
        $assignment_material = ''; // No material uploaded
    }

    try {
        // Insert assignment data
        $stmt = $conn->prepare("INSERT INTO assignments (assignment_name, assignment_description, course_id, assignment_material) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssis', $assignment_name, $assignment_description, $course_id, $assignment_material);
        $stmt->execute();

        header("Location: ../browse_course_educator.php?course_id=$course_id&success=" . urlencode("Assignment added successfully"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../add_assignment.php?course_id=$course_id&error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
