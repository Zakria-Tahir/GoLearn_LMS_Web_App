<?php
include '_db_connect.php';

if (isset($_GET['subcategory_id'])) {
    $subcategory_id = intval($_GET['subcategory_id']);
    $sql = "SELECT * FROM courses WHERE subcategory_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subcategory_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($courses);
}
