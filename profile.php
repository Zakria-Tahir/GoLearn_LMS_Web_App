<?php
include "utils/_db_connect.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php');
    exit;
}
?>

<?php
include "header.php";
?>
<title><?php echo $_SESSION['username'] ?>&nbsp;| GoLearn</title>

<body>
    <div class="wrapper">

        <?php
        include 'sidebar.php';
        ?>

        <div class="main">

            <?php
            include 'navbar.php';

            $sql = "SELECT * FROM `users` WHERE `user_id` = '" . $_SESSION['user_id'] . "'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            ?>

            <div class="container p-5">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td>User ID</td>
                            <td><?php echo $row['user_id'] ?></td>
                        </tr>
                        <tr>
                            <td>Username</td>
                            <td><?php echo $_SESSION['username'] ?></td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td><?php echo $row['user_email'] ?></td>
                        </tr>
                        <tr>
                            <td>Bio</td>
                            <td><?php echo $row['user_bio'] ?></td>
                        </tr>
                        <tr>
                            <td>Role</td>
                            <td><?php echo $row['user_role'] ?></td>
                        </tr>
                        <tr>
                            <td>Gender</td>
                            <td><?php echo $row['gender'] ?></td>
                        </tr>
                        <tr>
                            <td>Profile Picture</td>
                            <td><img src="<?php echo USER_IMAGE_PATH . $row['user_image'] ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;"></td>
                        </tr>
                        <tr>
                            <td>Created At</td>
                            <td><?php echo $row['added_on'] ?></td>
                        </tr>
                    </tbody>
                </table>
                <div class="m-2 container">
                    <a href="update_profile.php" class="btn btn-primary">Update Data</a>
                </div>
            </div>

            <?php
            include 'footer.php';
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
</body>

</html>