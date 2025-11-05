<?php
include('admin_utils/_db_connect.php');
include('../utils/_globals.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header('location: home.php');
    exit;
}
?>

<!DOCTYPE html>
<html class="no-js" lang="">

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Manage Subcategories | GoLearn</title>
    <link rel="stylesheet" href="admin_css/normalize.css">
    <link rel="stylesheet" href="admin_css/bootstrap.min.css">
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

        <div class="container">
            <h1 class="text-center px-5">Subcategories</h1>
        </div>

        <div class="container mb-5">
            <?php
            if (isset($_GET['error'])) {
                echo '
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> ' . $_GET['error'] . '
                    </div>
                ';
            } elseif (isset($_GET['success'])) {
                echo '
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> ' . $_GET['success'] . '
                    </div>
                ';
            }
            ?>
            <h3>Add Subcategory</h3>
            <form action="admin_utils/_add_subcategory.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="subcategory_name" class="form-label">Subcategory Name</label>
                    <input type="text" class="form-control" id="subcategory_name" name="subcategory_name" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <?php
                        $sql = "SELECT * FROM categories";
                        $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . $row['category_id'] . '">' . $row['category_name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subcategory_description" class="form-label">Subcategory Description</label>
                    <textarea class="form-control" id="subcategory_description" name="subcategory_description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="subcategory_thumbnail" class="form-label">Subcategory Thumbnail</label>
                    <input class="form-control p-1" type="file" id="subcategory_thumbnail" name="subcategory_thumbnail">
                </div>
                <button type="submit" class="btn btn-primary">Add Subcategory</button>
            </form>
        </div>

        <div class="container-fluid">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Subcategory Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Subcategory Description</th>
                        <th scope="col">Subcategory Thumbnail</th>
                        <th scope="col">Added on</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT subcategories.*, categories.category_name FROM subcategories JOIN categories ON subcategories.category_id = categories.category_id";
                    $result = mysqli_query($conn, $sql);
                    $i = 1;

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '
                            <tr>
                                <th scope="row">' . $i++ . '</th>
                                <td>' . htmlspecialchars($row['subcategory_name']) . '</td>
                                <td>' . $row['category_name'] . '</td>
                                <td class="description">' . $row['subcategory_description'] . '</td>
                                <td><img src="../' . SUBCATEGORY_IMAGE_PATH . htmlspecialchars($row['subcategory_thumbnail']) . '" alt="Thumbnail" style="width: 50px; height: auto;"></td>
                                <td>' . htmlspecialchars($row['added_on']) . '</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_subcategory.php?id=' . htmlspecialchars($row['subcategory_id']) . '" class="btn btn-outline-primary">Edit</a>
                                        <a href="admin_utils/_delete_subcategory.php?id=' . htmlspecialchars($row['subcategory_id']) . '" class="btn btn-outline-danger">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        ';
                    }
                    ?>
                </tbody>
            </table>
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