<?php
include 'utils/_globals.php';
include 'utils/_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$loggedin = false;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $loggedin = true;
} else {
    $loggedin = false;
}
?>

<nav class="navbar navbar-expand px-3 border-bottom">
    <button class="btn" id="sidebar-toggle" type="button">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse navbar">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <?php
                if ($loggedin) {
                    $sql = "SELECT * FROM users WHERE user_id = " . $_SESSION['user_id'];
                    $result = mysqli_query($conn, $sql);
                    if ($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        echo '
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                                <img src="' . USER_IMAGE_PATH . $row['user_image'] . '" class="avatar img-fluid rounded" alt="">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="profile.php" class="dropdown-item">Profile</a>
                                <a href="logout.php" class="dropdown-item">Logout</a>
                            </div>
                            ';
                    } else {
                        echo 'Error fetching user details.';
                    }
                } else {
                    echo '
                            <div class="d-flex gap-1">
                                <button
                                    type="button"
                                    class="btn btn-outline-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                    Login
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#signupModal">
                                    SignUp
                                </button>
                            </div>
                        ';
                }
                ?>
            </li>
        </ul>
    </div>
</nav>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="loginModalLabel">Login</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="utils/_handle_login.php" method="post">
                    <div class="mb-3">
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Email" />
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="password" name="pass" placeholder="Password" />
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SignUp Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="loginModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="loginModalLabel">Sign Up</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="utils/_handle_registration.php" method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="name" name="name" aria-describedby="emailHelp" placeholder="Enter your name" />
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter your email" />
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="password" name="pass" placeholder="Enter your password" />
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="cpassword" name="cpass" placeholder="Confirm your password" />
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>