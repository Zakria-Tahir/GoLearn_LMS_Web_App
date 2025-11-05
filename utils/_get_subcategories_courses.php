<?php
include '_db_connect.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    $sql = "SELECT * FROM subcategories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategories = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($subcategories);
}
