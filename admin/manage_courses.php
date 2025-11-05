<?php
session_start();
include 'admin_utils/_db_connect.php';

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header('location: home.php');
    exit;
}

// Fetch all courses
$sql = "SELECT * FROM courses";
$result = mysqli_query($conn, $sql);
$courses = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | GoLearn</title>
    <link rel="stylesheet" href="admin_css/bootstrap.min.css">
    <link rel="stylesheet" href="admin_css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>

    <div id="right-panel" class="right-panel">
        <?php include 'admin_header.php'; ?>

        <div class="content pb-0">
            <div class="animated fadeIn">
                <div class="row">
                    <div class="col-lg-12 p-5">
                        <h1 class="text-center mb-2">Manage Courses</h1>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Course ID</th>
                                    <th>Course Name</th>
                                    <th>Category</th>
                                    <th>Subcategory</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courses)) { ?>
                                    <?php foreach ($courses as $course) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['category_id']); ?></td>
                                            <td><?php echo htmlspecialchars($course['subcategory_id']); ?></td>
                                            <td>
                                                <a href="admin_utils/delete_course.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No courses found</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'admin_footer.php'; ?>
    </div>

    <script src="admin_js/vendor/jquery-2.1.4.min.js"></script>
    <script src="admin_js/popper.min.js"></script>
    <script src="admin_js/plugins.js"></script>
    <script src="admin_js/main.js"></script>
</body>

</html>