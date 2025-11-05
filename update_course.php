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

// Fetch course data from the database
$sql = "SELECT * FROM courses WHERE course_id = ? AND educator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $course_id, $educator_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    header('location: educator_panel.php?error=' . urlencode('Course not found or you are not authorized to update this course'));
    exit;
}
?>

<?php
include "header.php";
?>
<title>Update Course | GoLearn</title>

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
                <h1 class="text-center">Update Course</h1>

                <form action="utils/_handle_update_course.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <div class="form-group my-2">
                        <label for="course_name" class="form-label">Course Name:</label>
                        <input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
                    </div>
                    <div class="form-group my-2">
                        <label for="course_description" class="form-label">Course Description:</label>
                        <textarea name="course_description" class="form-control" required><?php echo htmlspecialchars($course['course_description']); ?></textarea>
                    </div>
                    <div class="form-group my-2">
                        <label for="course_thumbnail" class="form-label">Course Thumbnail:</label>
                        <input type="file" name="course_thumbnail" class="form-control">
                        <img src="<?php echo COURSE_IMAGE_PATH . $course['course_thumbnail']; ?>" alt="Course Thumbnail" style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px;">
                    </div>
                    <div class="form-group my-2">
                        <label for="category_id" class="form-label">Course Category:</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            $sql = "SELECT * FROM categories";
                            $result = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($row['category_id'] == $course['category_id']) ? 'selected' : '';
                                echo '<option value="' . $row['category_id'] . '" ' . $selected . '>' . $row['category_name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group my-2">
                        <label for="subcategory_id" class="form-label">Course Subcategory:</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-control" required>
                            <option value="">Select Subcategory</option>
                            <?php
                            $sql = "SELECT * FROM subcategories WHERE category_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('i', $course['category_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $selected = ($row['subcategory_id'] == $course['subcategory_id']) ? 'selected' : '';
                                echo '<option value="' . $row['subcategory_id'] . '" ' . $selected . '>' . $row['subcategory_name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Course</button>
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
        document.getElementById('category_id').addEventListener('change', function() {
            var categoryId = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'utils/_get_subcategories.php?category_id=' + categoryId, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var subcategories = JSON.parse(this.responseText);
                    var subcategorySelect = document.getElementById('subcategory_id');
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                    subcategories.forEach(function(subcategory) {
                        subcategorySelect.innerHTML += '<option value="' + subcategory.subcategory_id + '">' + subcategory.subcategory_name + '</option>';
                    });
                }
            };
            xhr.send();
        });

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