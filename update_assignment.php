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

if (!isset($_GET['assignment_id'])) {
    header('location: educator_panel.php?error=' . urlencode('Assignment ID is required'));
    exit;
}

$assignment_id = intval($_GET['assignment_id']);
$educator_id = $_SESSION['user_id'];

// Fetch assignment data to ensure the educator owns the assignment
$sql = "SELECT * FROM assignments WHERE assignment_id = ? AND course_id IN (SELECT course_id FROM courses WHERE educator_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $assignment_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();

if (!$assignment) {
    header('location: educator_panel.php?error=' . urlencode('Assignment not found or you are not authorized to update this assignment'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Update Assignment | GoLearn</title>

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
                <h1 class="text-center">Update Assignment</h1>

                <form action="utils/_handle_update_assignment.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
                    <div class="form-group my-2">
                        <label for="assignment_name" class="form-label">Assignment Name:</label>
                        <input type="text" name="assignment_name" class="form-control" value="<?php echo htmlspecialchars($assignment['assignment_name']); ?>" required>
                    </div>
                    <div class="form-group my-2">
                        <label for="assignment_description" class="form-label">Assignment Description:</label>
                        <textarea name="assignment_description" class="form-control" required><?php echo htmlspecialchars($assignment['assignment_description']); ?></textarea>
                    </div>
                    <div class="form-group my-2">
                        <label for="assignment_material" class="form-label">Assignment Material:</label>
                        <input type="file" name="assignment_material" class="form-control">
                        <p>Current Material: <a href="<?php echo COURSE_MATERIAL_PATH . $assignment['assignment_material']; ?>" download><?php echo htmlspecialchars($assignment['assignment_material']); ?></a></p>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Assignment</button>
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