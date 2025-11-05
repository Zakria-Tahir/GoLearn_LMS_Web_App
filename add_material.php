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

if (!isset($_GET['course_id'])) {
    header('location: educator_panel.php?error=' . urlencode('Course ID is required'));
    exit;
}

$course_id = intval($_GET['course_id']);
$educator_id = $_SESSION['user_id'];

// Fetch course data to ensure the educator owns the course
$sql = "SELECT * FROM courses WHERE course_id = ? AND educator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $course_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    header('location: educator_panel.php?error=' . urlencode('Course not found or you are not authorized to add materials to this course'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Add Material | GoLearn</title>

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
                <h1 class="text-center">Add Material</h1>

                <form action="utils/_handle_add_material.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <div class="form-group my-2">
                        <label for="material_description" class="form-label">Material Description:</label>
                        <textarea name="material_description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group my-2">
                        <label for="material_file" class="form-label">Upload Material:</label>
                        <input type="file" name="material_file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Material</button>
                </form>
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
            const params = new URLSearchParams(url.search);
            params.delete('error');
            params.delete('success');
            url.search = params.toString();
            window.history.replaceState({
                path: url.href
            }, '', url.href);
        }
    </script>
</body>

</html>