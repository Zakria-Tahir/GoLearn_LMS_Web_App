<?php
session_start();
$showLoginAlert = false;

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    if (isset($_GET['get_started'])) {
        $showLoginAlert = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | GoLearn </title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .hero {
            background-image: url("images/hero-1.jpg");
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 30px;
            height: 100vh;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <?php
        include 'sidebar.php';
        ?>

        <div class="main">

            <?php
            include 'navbar.php';

            if ($showLoginAlert) {
                echo '
                  <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
                    <strong>Notice!</strong> Please <a href="login.php" class="alert-link">login</a> or <a href="signup.php" class="alert-link">signup</a> to continue.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                ';
            }

            if (isset($_GET['error'])) {
                echo '
                  <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
                    <strong>Error!</strong> ' . $_GET['error'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                ';
            } elseif (isset($_GET['success'])) {
                echo '
                  <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                    <strong>Success!</strong> ' . $_GET['success'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                ';
            }
            ?>

            <!-- Hero Section   -->
            <div class="container-fluid row d-flex justify-content-center align-items-center hero">
                <div class="container col">
                    <h1 class="my-3">Empower Your Learning Journey Today!</h1>
                    <h5 class="my-3">Discover top courses, track your progress, and achieve your goals with our innovative learning platform. Whether you're a student or an educator, there's a place for you here!</h5>
                    <a href="browse_courses.php" class="btn btn-success rounded-pill my-3">Get Started</a>
                </div>
            </div>

            <div class="container p-5 text-dark">
                <h3 class="text-center">Our Growing Community of Learners and Educators</h3>
                <h6 class="text-center">Together, we're building a hub for education and innovation.</h6>
                <p class="text-center">Join a thriving community of students and educators who are transforming the way we learn and teach. With a wide variety of courses created by passionate instructors, and thousands of active learners striving for success, our platform is dedicated to empowering everyone to achieve their goals.</p>

                <div class="d-flex justify-content-center align-items-center py-3 gap-2 cards-container text-white">
                    <div class="card  home-cards text-white" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php
                                $sql = "SELECT COUNT(user_id) as total_users FROM users";
                                $result = mysqli_query($conn, $sql);
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['total_users'];
                                } else {
                                    echo 'Count Error';
                                }
                                ?>
                            </h5>
                            <p class="card-text">Total STudents.</p>
                        </div>
                    </div>
                    <div class="card home-cards  text-white" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php
                                $sql = "SELECT COUNT(educator_id) as total_educators FROM educators";
                                $result = mysqli_query($conn, $sql);
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['total_educators'];
                                } else {
                                    echo 'Count Error';
                                }
                                ?>
                            </h5>
                            <p class="card-text">Total Educators.</p>
                        </div>
                    </div>
                    <div class="card home-cards  text-white" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php
                                $sql = "SELECT COUNT(course_id) as total_courses FROM courses";
                                $result = mysqli_query($conn, $sql);
                                if ($result && mysqli_num_rows($result) > 0) {
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['total_courses'];
                                } else {
                                    echo 'Count Error';
                                }
                                ?>
                            </h5>
                            <p class="card-text">Total Courses.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center align-items-stretch py-3 gap-2  cards-container">
                    <div class="card  home-cards text-white" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">
                                Learn. Teach. Grow.
                            </h5>
                            <p class="card-text">Unlock your potential with our all-in-one learning platform.</p>
                        </div>
                    </div>
                    <div class="card home-cards  text-white" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">
                                Your Gateway to Knowledge
                            </h5>
                            <p class="card-text">Empowering students and educators with tools to succeed.</p>
                        </div>
                    </div>
                    <div class="card home-cards  text-white" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">
                                Where Education Meets Innovation
                            </h5>
                            <p class="card-text">Learn smarter, teach better, and grow together.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include 'footer.php';
            ?>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
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