<?php
session_start();
include 'admin_utils/_db_connect.php';
include '../utils/_globals.php';
?>

<!DOCTYPE html>
<html class="no-js" lang="">

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Admin Dashboard | GoLearn</title>
    <link rel="stylesheet" href="admin_css/normalize.css">
    <link rel="stylesheet" href="admin_css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="admin_css/font-awesome.min.css"> -->
    <link rel="stylesheet" href="admin_css/themify-icons.css">
    <link rel="stylesheet" href="admin_css/pe-icon-7-filled.css">
    <link rel="stylesheet" href="admin_css/flag-icon.min.css">
    <link rel="stylesheet" href="admin_css/cs-skin-elastic.css">
    <link rel="stylesheet" href="admin_css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>

    <div id="right-panel" class="right-panel">
        <?php include './admin_header.php'; ?>

        <div class="content pb-0">
            <div class="animated fadeIn">
                <div class="row">
                    <div class="col-lg-12">
                        <h1><i class="fas fa-users m-2"></i> User Management </h1>
                        <div class="container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">User Id</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">User email</th>
                                        <th scope="col">User Image</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM users";
                                    $result = mysqli_query($conn, $sql);
                                    if ($result->num_rows > 0) {
                                        $count = 1;
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<th scope='row'>" . $count . "</th>";
                                            echo "<td>" . $row['user_id'] . "</td>";
                                            echo "<td>" . $row['username'] . "</td>";
                                            echo "<td>" . $row['user_email'] . "</td>";
                                            echo "<td>
                                                    <img src='../" . USER_IMAGE_PATH . $row['user_image'] . "' alt='user_image' style='width: 50px; height: 50px;'>
                                                </td>";
                                            echo "<td>
                                                    <a href='admin_utils/_delete_user.php?user_id=" . $row['user_id'] . "' class='btn btn-danger m-1'>Delete</a>
                                                </td>";
                                            echo "</tr>";
                                            $count++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        include 'admin_footer.php';
        ?>
    </div>

    <script src="admin_js/vendor/jquery-2.1.4.min.js" type="text/javascript"></script>
    <script src="admin_js/popper.min.js" type="text/javascript"></script>
    <script src="admin_js/plugins.js" type="text/javascript"></script>
    <script src="admin_js/main.js" type="text/javascript"></script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href.split("?")[0]);
        }
    </script>
</body>

</html>