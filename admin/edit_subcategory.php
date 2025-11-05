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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: manage_subcategories.php?error=' . urlencode('Invalid subcategory ID.'));
    exit;
}

$subcategory_id = intval($_GET['id']);

// Fetch existing subcategory details
$sql = "SELECT * FROM subcategories WHERE subcategory_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$result = $stmt->get_result();
$subcategory = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html class="no-js" lang="">

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Edit Subcategory | GoLearn</title>
    <link rel="stylesheet" href="admin_css/normalize.css">
    <link rel="stylesheet" href="admin_css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="admin_css/font-awesome.min.css"> -->
    <link rel="stylesheet" href="admin_css/themify-icons.css">
    <link rel="stylesheet" href="admin_css/pe-icon-7-filled.css">
    <link rel="stylesheet" href="admin_css/flag-icon.min.css">
    <link rel="stylesheet" href="admin_css/cs-skin-elastic.css">
    <link rel="stylesheet" href="admin_css/style.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>

    <div id="right-panel" class="right-panel">
        <?php include './admin_header.php'; ?>

        <div class="container">
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
        </div>

        <div class="content" id="content">
            <div class="toggler-container">
                <h1 class="text-center px-5">Edit Subcategory: <?php echo htmlspecialchars($subcategory['subcategory_name']); ?></h1>
            </div>

            <div class="container mb-5">
                <form action="admin_utils/_handle_edit_subcategory.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3 from-group">
                        <label for="subcategory_id" class="form-label">Subcategory Id</label>
                        <input type="hidden" name="subcategory_id" value="<?php echo $subcategory_id; ?>">
                    </div>
                    <div class="mb-3 from-group">
                        <label for="subcategory_name" class="form-label">Subcategory Name</label>
                        <input type="text" class="form-control" id="subcategory_name" name="subcategory_name" value="<?php echo htmlspecialchars($subcategory['subcategory_name']); ?>" required>
                    </div>
                    <div class="mb-3 from-group">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <?php
                            $sql = "SELECT * FROM categories";
                            $result = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['category_id'] == $subcategory['category_id']) ? 'selected' : '';
                                echo '<option value="' . $row['category_id'] . '" ' . $selected . '>' . htmlspecialchars($row['category_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3 from-group">
                        <label for="subcategory_description" class="form-label">Subcategory Description</label>
                        <textarea class="form-control" id="subcategory_description" name="subcategory_description" rows="3" required><?php echo htmlspecialchars($subcategory['subcategory_description']); ?></textarea>
                    </div>
                    <div class="mb-3 from-group">
                        <label for="subcategory_thumbnail" class="form-label">Subcategory Thumbnail</label>
                        <input class="form-control p-1" type="file" id="subcategory_thumbnail" name="subcategory_thumbnail">
                        <img src="../<?php echo SUBCATEGORY_IMAGE_PATH . htmlspecialchars($subcategory['subcategory_thumbnail']); ?>" alt="Thumbnail" style="width: 100px; height: auto; margin-top: 10px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Subcategory</button>
                </form>
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