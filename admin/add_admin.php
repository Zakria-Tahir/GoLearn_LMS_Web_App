<?php

include "../utils/_db_connect.php";

$username = "admin";
$password = "admin";
$email = "golearnadmin@gmail.com";
$password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO `admin` (`admin_name`, `admin_email`, `admin_password`) VALUES ('$username', '$email','$password')";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "Admin added successfully";
} else {
    echo "Failed to add admin";
}
