<?php
session_start();
include '_db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['pass'];

    try {
        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            header("Location: ../index.php?error=" . urlencode("Email does not exist"));
            exit();
        }

        $user = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $user['user_password'])) {
            header("Location: ../index.php?error=" . urlencode("Incorrect password"));
            exit();
        }

        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['user_role'] = $user['user_role'];

        header("Location: ../index.php?success=" . urlencode("Login successful"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../index.php?error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
