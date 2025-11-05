<?php
session_start();
include '_db_connect.php'; // Include your database connection file
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['pass'];
    $confirm_password = $_POST['cpass']; // Ensure this matches the form field name

    // Check if password is at least 8 characters long
    if (strlen($password) < 8) {
        $errorMessage = "Password must be at least 8 characters long";
        header("Location: ../index.php?error=" . urlencode($errorMessage));
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match";
        header("Location: ../index.php?error=" . urlencode($errorMessage));
        exit();
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = "Email already exists";
            header("Location: ../index.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, user_email, user_password) VALUES (?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
        $stmt->bind_param('sss', $name, $email, $hashed_password);
        $stmt->execute();

        header("Location: ../index.php?success=" . urlencode("Registration successful"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../index.php?error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
