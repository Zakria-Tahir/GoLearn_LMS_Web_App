<?php
include "header.php";
?>

<body>
  <div class="wrapper">
    <aside id="sidebar" class="js-sidebar">
      <!-- Content For Sidebar -->
      <div class="h-100">
        <div class="sidebar-logo mb-2">
          <a href="index.php">
            <img src="images/logo.png" alt="GoLearn" />
          </a>
        </div>
        <ul class="sidebar-nav">
          <li class="sidebar-item">
            <a href="index.php" class="sidebar-link">
              <i class="fa-solid fa-house pe-2"></i>
              Home
            </a>
          </li>
          <li class="sidebar-item"></li>
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
        </ul>
      </div>
    </aside>

    <div class="main">
      <nav class="navbar navbar-expand px-3 border-bottom">
        <button class="btn" id="sidebar-toggle" type="button">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-collapse navbar">
          <ul class="navbar-nav">
            <li class="nav-item dropdown">
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
            </li>
          </ul>
        </div>
      </nav>

      <!-- Login Modal -->
      <div
        class="modal fade"
        id="loginModal"
        tabindex="-1"
        aria-labelledby="loginModal"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="loginModalLabel">Login</h1>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="utils/_handle_login.php" method="post">
                <div class="mb-3">
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    aria-describedby="emailHelp"
                    placeholder="Email" />
                </div>
                <div class="mb-3">
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="pass"
                    placeholder="Password" />
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- SignUp Modal -->
      <div
        class="modal fade"
        id="signupModal"
        tabindex="-1"
        aria-labelledby="loginModal"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="loginModalLabel">Sign Up</h1>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="utils/_handle_registration.php" method="post">
                <div class="mb-3">
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    aria-describedby="emailHelp"
                    placeholder="Enter your name" />
                </div>
                <div class="mb-3">
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    aria-describedby="emailHelp"
                    placeholder="Enter your email" />
                </div>
                <div class="mb-3">
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="pass"
                    placeholder="Enter your password" />
                </div>
                <div class="mb-3">
                  <input
                    type="password"
                    class="form-control"
                    id="cpassword"
                    name="cpass"
                    placeholder="Confirm your password" />
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <footer class="footer">
        <div class="container-fluid">
          <div class="row text-muted">
            <div class="col-6 text-start">
              <p class="mb-0">
                <a href="#" class="text-muted">
                  <strong>GoLearn</strong>
                </a>
              </p>
            </div>
            <div class="col-6 text-end">
              <p>
                &copy; copyright @ 2024&nbsp; | &nbsp; All Rights Reserved!
              </p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>
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