<aside id="sidebar" class="js-sidebar">
    <!-- Content For Sidebar -->
    <div class="h-100">
        <div class="sidebar-logo  mb-2">
            <a href="index.php">
                <img src="images/logo.png" alt="GoLearn">
            </a>
        </div>
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="index.php" class="sidebar-link">
                    <i class="fa-solid fa-house pe-2"></i>
                    Home
                </a>
            </li>
            <li class="sidebar-item">
                <?php
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'educator') {
                    echo '
                        <a href="educator_panel.php" class="sidebar-link">
                            <i class="fa-solid fa-tachometer-alt pe-2"></i>
                            Dashboard
                        </a>
                    ';
                } else {
                    echo '
                        <a href="dashboard.php" class="sidebar-link">
                            <i class="fa-solid fa-tachometer-alt pe-2"></i>
                            Dashboard
                        </a>
                    ';
                }
                ?>
            </li>
            <li class="sidebar-item">
                <a href="browse_courses.php" class="sidebar-link">
                    <i class="fa-solid fa-book pe-2"></i>
                    Courses
                </a>
            </li>
            <li class="sidebar-item">
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fa-solid fa-chart-line pe-2"></i>
                    My Progress
                </a>
            </li>
            <?php
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'educator') {
                echo '
                    <li class="sidebar-item">
                        <a href="educator_panel.php" class="sidebar-link">
                            <i class="fa-solid fa-chalkboard-teacher pe-2"></i>
                            Educator Panel
                        </a>
                    </li>
                ';
            } else {
                echo '
                    <li class="sidebar-item">
                        <a href="become_educator.php" class="sidebar-link">
                            <i class="fa-solid fa-user-graduate pe-2"></i>
                            Become an Educator
                        </a>
                    </li>
                ';
            }
            ?>
        </ul>
    </div>
</aside>