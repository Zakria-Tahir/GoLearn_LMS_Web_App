<?php
include 'utils/_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php');
    exit;
}

if ($_SESSION['user_role'] != 'educator') {
    header('location: index.php?error=' . urlencode('You are not authorized to access this page'));
    exit;
}

$educator_id = $_SESSION['user_id'];

$sql = "SELECT * FROM educators WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$educator = $result->fetch_assoc();

if (!$educator) {
    header('location: index.php?error=' . urlencode('Educator details not found.'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Educator Panel | GoLearn </title>

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

            <div class="container">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h3>My Courses</h3>
                    <a href="add_course.php" class="btn btn-primary">Add Course</a>
                </div>

                <?php
                $sql = "SELECT * FROM courses WHERE educator_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $educator_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '
                    <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <th>Course ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                    ';
                    while ($row = $result->fetch_assoc()) {
                        $status_button = ($row['status'] == 'active') ? 'Deactive' : 'Active';
                        echo '
                            <tr>
                                <td>' . $row['course_id'] . '</td>
                                <td>' . $row['course_name'] . '</td>
                                <td>' . $row['course_description'] . '</td>
                                <td>' . $row['category_id'] . '</td>
                                <td>' . $row['subcategory_id'] . '</td>
                                <td>' . ucfirst($row['status']) . '</td>
                                <td>
                                    <a href="update_course.php?course_id=' . $row['course_id'] . '" class=" my-1 btn btn-warning btn-sm">Update</a>
                                    <a href="utils/_change_course_status.php?course_id=' . $row['course_id'] . '" class=" my-1 btn btn-secondary btn-sm">' . $status_button . '</a>
                                    <a href="browse_course_educator.php?course_id=' . $row['course_id'] . '" class="my-1 btn btn-info btn-sm">Browse</a>
                                    <a href="utils/_delete_course.php?course_id=' . $row['course_id'] . '" class=" my-1 btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        ';
                    }
                    echo '
                        </tbody>
                    </table>
                    ';
                } else {
                    echo '
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="fa-solid fa-info-circle"></i> You have no courses. Please add a course.
                    </div>
                    ';
                }
                ?>
            </div>

            <?php
            include 'footer.php';
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script>
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.search = "";
            window.history.replaceState({
                    path: url.href,
                },
                "",
                url.href
            );
        }
    </script>
</body>

</html>