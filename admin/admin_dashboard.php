<?php
session_start();
include 'admin_utils/_db_connect.php';

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header('location: home.php');
    exit;
}

// Fetch total number of courses
$sql = "SELECT COUNT(course_id) as total_courses FROM courses";
$result = mysqli_query($conn, $sql);
$total_courses = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['total_courses'] : 'Count Error';

// Fetch total number of students
$sql = "SELECT COUNT(user_id) as total_students FROM users WHERE user_role = 'student'";
$result = mysqli_query($conn, $sql);
$total_students = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['total_students'] : 'Count Error';

// Fetch total number of teachers
$sql = "SELECT COUNT(user_id) as total_teachers FROM users WHERE user_role = 'educator'";
$result = mysqli_query($conn, $sql);
$total_teachers = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result)['total_teachers'] : 'Count Error';

// Fetch course with most enrollments
$sql = "SELECT c.course_name, COUNT(e.enrollment_id) as total_enrollments 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        GROUP BY e.course_id 
        ORDER BY total_enrollments DESC 
        LIMIT 1";
$result = mysqli_query($conn, $sql);
$most_enrolled_course = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : ['course_name' => 'No Data', 'total_enrollments' => 0];
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
                    <div class="col-lg-12 p-5">
                        <h1>Welcome Admin</h1>
                    </div>
                    <div class="container p-3">
                        <div class="row p-5">
                            <div class="col-md-6">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Courses</h5>
                                        <p class="card-text text-dark"><?php echo $total_courses; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Students</h5>
                                        <p class="card-text  text-dark"><?php echo $total_students; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card text-white bg-warning mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Teachers</h5>
                                        <p class="card-text text-dark"><?php echo $total_teachers; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Most Enrolled Course</h5>
                                        <p class="card-text text-dark"><?php echo $most_enrolled_course['course_name']; ?> (<?php echo $most_enrolled_course['total_enrollments']; ?> enrollments)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'admin_footer.php'; ?>
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