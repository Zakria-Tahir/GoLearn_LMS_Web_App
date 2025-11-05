<?php
// THIS BROWSE COURSES PAGE IS FOR STUDENTS TO ENROLL IN COURSES
include 'utils/_db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header('location: index.php?error' . urlencode('You need to login first'));
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch all categories
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php
include "header.php";
?>
<title>Browse Courses | GoLearn</title>

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
                <h1 class="text-center">
                    <i class="fa-solid fa-book pe-2"></i>
                    Browse Courses
                </h1>

                <div class="mt-4">
                    <h3>Categories</h3>
                    <div class="row">
                        <?php foreach ($categories as $category) { ?>
                            <div class="col-md-4">
                                <div class="card mb-4 col-2" style="width: 18rem;">
                                    <img src="<?php echo CATEGORY_IMAGE_PATH . $category['category_thumbnail']; ?>" class="card-img-top" alt="Category Thumbnail" style="width: 100%; height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $category['category_name']; ?></h5>
                                        <p class="card-text"><?php echo $category['category_description']; ?></p>
                                        <a href="javascript:void(0);" class="btn btn-primary category-item" data-category-id="<?php echo $category['category_id']; ?>">View Subcategories</a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="mt-4" id="subcategories-container" style="display: none;">
                    <h3>Subcategories</h3>
                    <div class="row" id="subcategories-list">
                        <!-- Subcategories will be loaded here via AJAX -->
                    </div>
                </div>

                <div class="mt-4" id="courses-container" style="display: none;">
                    <h3>Courses</h3>
                    <div class="row" id="courses-list">
                        <!-- Courses will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

            <?php
            include 'footer.php';
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const SUBCATEGORY_IMAGE_PATH = '<?php echo SUBCATEGORY_IMAGE_PATH; ?>';
            const COURSE_IMAGE_PATH = '<?php echo COURSE_IMAGE_PATH; ?>';

            document.querySelectorAll('.category-item').forEach(item => {
                item.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category-id');
                    console.log('Category ID:', categoryId); // Debugging
                    fetchSubcategories(categoryId);
                });
            });

            function fetchSubcategories(categoryId) {
                fetch(`utils/_get_subcategories.php?category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Subcategories:', data); // Debugging
                        const subcategoriesContainer = document.getElementById('subcategories-container');
                        const subcategoriesList = document.getElementById('subcategories-list');
                        subcategoriesList.innerHTML = '';

                        data.forEach(subcategory => {
                            const subcategoryCard = document.createElement('div');
                            subcategoryCard.className = 'col-md-4';
                            subcategoryCard.innerHTML = `
                            <div class="card mb-4" style="width: 18rem;">
                                <img src="${SUBCATEGORY_IMAGE_PATH + subcategory.subcategory_thumbnail}" class="card-img-top" alt="Subcategory Thumbnail" style="width: 100%; height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${subcategory.subcategory_name}</h5>
                                    <p class="card-text">${subcategory.subcategory_description}</p>
                                    <a href="javascript:void(0);" class="btn btn-primary subcategory-item" data-subcategory-id="${subcategory.subcategory_id}">View Courses</a>
                                </div>
                            </div>
                        `;
                            subcategoryCard.querySelector('.subcategory-item').addEventListener('click', function() {
                                const subcategoryId = this.getAttribute('data-subcategory-id');
                                console.log('Subcategory ID:', subcategoryId); // Debugging
                                fetchCourses(subcategoryId);
                            });
                            subcategoriesList.appendChild(subcategoryCard);
                        });

                        subcategoriesContainer.style.display = 'block';
                    })
                    .catch(error => console.error('Error fetching subcategories:', error)); // Debugging
            }

            function fetchCourses(subcategoryId) {
                fetch(`utils/_get_courses.php?subcategory_id=${subcategoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Courses:', data); // Debugging
                        const coursesContainer = document.getElementById('courses-container');
                        const coursesList = document.getElementById('courses-list');
                        coursesList.innerHTML = '';

                        data.forEach(course => {
                            const courseCard = document.createElement('div');
                            courseCard.className = 'col-md-4';
                            courseCard.innerHTML = `
                            <div class="card mb-4" style="width: 18rem;">
                                <img src="${COURSE_IMAGE_PATH + course.course_thumbnail}" class="card-img-top" alt="Course Thumbnail" style="width: 100%; height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${course.course_name}</h5>
                                    <p class="card-text">${course.course_description}</p>
                                    <a href="course_details.php?course_id=${course.course_id}" class="btn btn-primary">View Course</a>
                                </div>
                            </div>
                        `;
                            coursesList.appendChild(courseCard);
                        });

                        coursesContainer.style.display = 'block';
                    })
                    .catch(error => console.error('Error fetching courses:', error)); // Debugging
            }
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