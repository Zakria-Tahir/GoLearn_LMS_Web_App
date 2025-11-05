<?php
include 'utils/_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['assignment_id'])) {
    header('location: course_details.php?error=' . urlencode('Assignment ID is required'));
    exit;
}

$assignment_id = intval($_GET['assignment_id']);

// Fetch assignment details
$sql = "SELECT * FROM assignments WHERE assignment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $assignment_id);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();

if (!$assignment) {
    header('location: course_details.php?error=' . urlencode('Assignment not found'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Submit Assignment | GoLearn</title>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <div class="container p-5">
                <h1 class="text-center">Submit Assignment</h1>
                <?php if (isset($_GET['error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php } ?>
                <form action="utils/_handle_assignment_submission.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $assignment['course_id']; ?>">
                    <div class="mb-3">
                        <label for="submission_text" class="form-label">Submission Text</label>
                        <textarea class="form-control" id="submission_text" name="submission_text" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="submission_file" class="form-label">Upload File</label>
                        <input class="form-control" type="file" id="submission_file" name="submission_file" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Assignment</button>
                </form>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <!-- Include Bootstrap JS and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
</body>

</html>