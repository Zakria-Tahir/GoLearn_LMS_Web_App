<?php
include '_db_connect.php';
include('../../utils/_globals.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $subcategory_name = htmlspecialchars($_POST['subcategory_name']);
    $category_id = intval($_POST['category_id']);
    $subcategory_description = htmlspecialchars($_POST['subcategory_description']);

    // Handle file upload
    if (isset($_FILES['subcategory_thumbnail']) && $_FILES['subcategory_thumbnail']['error'] == 0) {
        $target_dir = "../../" . SUBCATEGORY_IMAGE_PATH;
        $image = rand(1000, 100000) . "-" . basename($_FILES['subcategory_thumbnail']['name']);
        $target_file = $target_dir . $image;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['subcategory_thumbnail']['tmp_name']);
        if ($check === false) {
            header("Location: ../manage_subcategories.php?error=" . urlencode("File is not an image."));
            exit();
        }

        // Check file size (5MB max)
        if ($_FILES['subcategory_thumbnail']['size'] > 5000000) {
            header("Location: ../manage_subcategories.php?error=" . urlencode("Sorry, your file is too large."));
            exit();
        }

        // Allow certain file formats
        $allowed_formats = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowed_formats)) {
            header("Location: ../manage_subcategories.php?error=" . urlencode("Sorry, only JPG, JPEG, PNG & GIF files are allowed."));
            exit();
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            header("Location: ../manage_subcategories.php?error=" . urlencode("Sorry, file already exists."));
            exit();
        }

        // Try to upload file
        if (!move_uploaded_file($_FILES['subcategory_thumbnail']['tmp_name'], $target_file)) {
            header("Location: ../manage_subcategories.php?error=" . urlencode("Sorry, there was an error uploading your file."));
            exit();
        }
    } else {
        $target_file = null; // No file uploaded
    }

    // Insert subcategory into the database
    if ($target_file) {
        $sql = "INSERT INTO subcategories (subcategory_name, category_id, subcategory_description, subcategory_thumbnail) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siss", $subcategory_name, $category_id, $subcategory_description, $image);
    } else {
        $sql = "INSERT INTO subcategories (subcategory_name, category_id, subcategory_description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $subcategory_name, $category_id, $subcategory_description);
    }

    if ($stmt->execute()) {
        header("Location: ../manage_subcategories.php?success=" . urlencode("Subcategory added successfully."));
    } else {
        header("Location: ../manage_subcategories.php?error=" . urlencode("Error adding subcategory."));
    }

    $stmt->close();
    $conn->close();
}
