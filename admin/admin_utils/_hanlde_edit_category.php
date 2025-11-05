<?php
include('../admin_utils/_db_connect.php');
include('../../utils/_globals.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header('location: ../home.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $category_name = htmlspecialchars($_POST['category_name']);
    $category_description = htmlspecialchars($_POST['category_description']);
    $category_id = intval($_POST['category_id']);

    // Fetch existing category details
    $sql = "SELECT category_thumbnail FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();

    // Handle file upload
    if (isset($_FILES['category_thumbnail']) && $_FILES['category_thumbnail']['error'] == 0) {
        $target_dir = "../../" . CATEGORY_IMAGE_PATH;
        $image = rand(1000, 100000) . "-" . basename($_FILES['category_thumbnail']['name']);
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['category_thumbnail']['tmp_name']);
        if ($check === false) {
            header("Location: ../edit_category.php?id=$category_id&error=" . urlencode("File is not an image."));
            exit();
        }

        // Check file size (5MB max)
        if ($_FILES['category_thumbnail']['size'] > 5000000) {
            header("Location: ../edit_category.php?id=$category_id&error=" . urlencode("Sorry, your file is too large."));
            exit();
        }

        // Allow certain file formats
        $allowed_formats = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowed_formats)) {
            header("Location: ../edit_category.php?id=$category_id&error=" . urlencode("Sorry, only JPG, JPEG, PNG & GIF files are allowed."));
            exit();
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            header("Location: ../edit_category.php?id=$category_id&error=" . urlencode("Sorry, file already exists."));
            exit();
        }

        // Try to upload file
        if (!move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], $target_file)) {
            header("Location: ../edit_category.php?id=$category_id&error=" . urlencode("Sorry, there was an error uploading your file."));
            exit();
        }

        // Delete the old image if it's not the default image
        if ($category['category_thumbnail'] != 'default-category.png') {
            $old_image_path = "../../" . CATEGORY_IMAGE_PATH . $category['category_thumbnail'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }

        // Update category with new image
        $sql = "UPDATE categories SET category_name = ?, category_description = ?, category_thumbnail = ? WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $category_name, $category_description, $image, $category_id);
    } else {
        // Update category without changing the image
        $sql = "UPDATE categories SET category_name = ?, category_description = ? WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $category_name, $category_description, $category_id);
    }

    if ($stmt->execute()) {
        header("Location: ../manage_categories.php?success=" . urlencode("Category updated successfully."));
    } else {
        header("Location: ../edit_category.php?id=$category_id&error=" . urlencode("Error updating category."));
    }

    $stmt->close();
    $conn->close();
}
