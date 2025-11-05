<?php
include "utils/_db_connect.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php');
    exit;
}

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM `users` WHERE `user_id` = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
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

            if (isset($_GET['error'])) {
                echo '
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> ' . $_GET['error'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    ';
            } elseif (isset($_GET['success'])) {
                echo '
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> ' . $_GET['success'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    ';
            }
            ?>

            <div class="container p-5">
                <h1 class="text-center">Update Profile</h1>

                <form action="utils/_handle_update_profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-group my-2">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                    <div class="form-group my-2">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['user_email']); ?>" disabled>
                    </div>
                    <div class="form-group my-2">
                        <label for="bio" class="form-label">Bio:</label>
                        <textarea name="bio" class="form-control"><?php echo $user['user_bio']; ?></textarea>
                    </div>
                    <div class="form-group my-2">
                        <label for="role" class="form-label">Role:</label>
                        <input type="text" name="role" class="form-control" value="<?php echo htmlspecialchars($user['user_role']); ?>" disabled>
                        <a href="become_educator.php" class="btn btn-success mt-1">Become an Educator Now</a>
                    </div>
                    <div class="form-group my-2">
                        <label for="gender" class="form-group">Gender:</label>
                        <div class="form-check">
                            <div>
                                <input type="radio" id="male" name="gender" value="male" class="form-check-input" <?php echo ($user['gender'] == 'male') ? 'checked' : ''; ?>>
                                <label for="male" class="form-check-label">Male</label>
                            </div>
                            <div>
                                <input type="radio" id="female" name="gender" value="female" class="form-check-input" <?php echo ($user['gender'] == 'female') ? 'checked' : ''; ?>>
                                <label for="female" class="form-check-label">Female</label>
                            </div>
                            <div>
                                <input type="radio" id="other" name="gender" value="other" class="form-check-input" <?php echo ($user['gender'] == 'other') ? 'checked' : ''; ?>>
                                <label for="other" class="form-check-label">Other</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group my-2">
                        <label for="profile_picture" class="form-label">Profile Picture:</label>
                        <input type="file" name="profile_picture" class="form-control">
                        <img src="<?php echo USER_IMAGE_PATH . $user['user_image']; ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-top: 10px;">
                    </div>
                    <div class="form-group my-2">
                        <label for="pass" class="form-label my-1">Password:</label>
                        <input type="password" name="pass" class="form-control">

                        <label for="newpass" class="form-label my-1">New Password:</label>
                        <input type="password" name="newpass" class="form-control">
                        <label for="cnewpass" class="form-label my-1">Confirm Password:</label>
                        <input type="password" name="cnewpass" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
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