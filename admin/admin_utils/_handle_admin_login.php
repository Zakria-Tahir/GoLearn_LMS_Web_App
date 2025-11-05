<?php
include "_db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user inputs
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL Injection
    $sql = "SELECT * FROM `admin` WHERE `admin_name` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $num = $result->num_rows;

    if ($num == 1) {
        $row = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $row['admin_password'])) {
            session_start();
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_username'] = $username;
            header("location: ../admin_dashboard.php");
            exit();
        } else {
            header("location: ../home.php?error=" . urlencode("Invalid Password"));
            exit();
        }
    } else {
        header("location: ../home.php?error=" . urlencode("Invalid Username"));
        exit();
    }
}
