<?php
include "_db_connect.php";
include '_globals.php';

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$sql = "SELECT * FROM `users` WHERE `user_id` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $bio = htmlspecialchars($_POST['bio']);
    $gender = htmlspecialchars($_POST['gender']);
    $current_password = $_POST['pass'];
    $new_password = $_POST['newpass'];
    $confirm_new_password = $_POST['cnewpass'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = '../' . USER_IMAGE_PATH;
        $image = rand(1000, 100000) . "-" . $_FILES['profile_picture']['name'];
        $target_file = $target_dir . basename($image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an actual image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            header("Location: ../update_profile.php?error=" . urlencode("File is not an image."));
            exit();
        }

        // Check file size (limit to 5MB)
        if ($_FILES["profile_picture"]["size"] > 5000000) {
            header("Location: ../update_profile.php?error=" . urlencode("File is too large."));
            exit();
        }

        // Allow certain file formats
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowed_types)) {
            header("Location: ../update_profile.php?error=" . urlencode("Only JPG, JPEG, PNG & GIF files are allowed."));
            exit();
        }

        // Remove the old image if it exists and is not the default image
        if ($user['user_image'] != 'default.png' && file_exists($target_dir . $user['user_image'])) {
            unlink($target_dir . $user['user_image']);
        }

        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $image; // Store only the image name
    } else {
        $profile_picture = $user['user_image'];
    }

    // Verify current password
    if (!empty($current_password) && password_verify($current_password, $user['user_password'])) {
        // Check if new password is at least 8 characters long
        if (!empty($new_password) && strlen($new_password) >= 8) {
            // Check if new password and confirm password match
            if ($new_password === $confirm_new_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                // Handle password mismatch error
                header("Location: ../update_profile.php?error=" . urlencode("New passwords do not match"));
                exit();
            }
        } else {
            // Handle password length error
            header("Location: ../update_profile.php?error=" . urlencode("New password must be at least 8 characters long"));
            exit();
        }
    } elseif (!empty($current_password)) {
        // Handle incorrect current password error
        header("Location: ../update_profile.php?error=" . urlencode("Incorrect current password"));
        exit();
    } else {
        $hashed_new_password = $user['user_password']; // Keep the current password if no new password is provided
    }

    // Update user data in the database
    $sql = "UPDATE `users` SET `username` = ?, `user_bio` = ?, `gender` = ?, `user_image` = ?, `user_password` = ? WHERE `user_id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $username, $bio, $gender, $profile_picture, $hashed_new_password, $user_id);
    $stmt->execute();

    // Refresh the page to show updated data
    header("Location: ../update_profile.php?success=" . urlencode("Profile updated successfully"));
    exit();
}
