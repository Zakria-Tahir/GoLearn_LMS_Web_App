<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: ../index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $category_id = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']);

    try {
        // Insert educator data
        $stmt = $conn->prepare("INSERT INTO educators (user_id, category_id, subcategory_id) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $user_id, $category_id, $subcategory_id);
        $stmt->execute();

        // Update user role to educator
        $stmt = $conn->prepare("UPDATE users SET user_role = 'educator' WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        // Update session variable
        $_SESSION['user_role'] = 'educator';

        header("Location: ../become_educator.php?success=" . urlencode("You have successfully become an educator"));
        exit();
    } catch (mysqli_sql_exception $e) {
        header("Location: ../become_educator.php?error=" . urlencode("Connection failed: " . $e->getMessage()));
        exit();
    }
}
