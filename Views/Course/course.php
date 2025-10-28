<?php 
require_once __DIR__ . "/../../Models/course.php";
require_once __DIR__ . "/../../Controls/courseController.php";
$courseObj = new CourseController();
$AllCourses = $courseObj->getAllCoursesWithInstructors();

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_now'])) {

    // 1. Check if user logged in
    if (empty($_SESSION['user_id'])) {
        header("Location: /SkillPro/Views/Login/login.php");
        exit;
    }

    // 2. Check if role is student
    if (strtolower($_SESSION['role']) !== 'student') {
        echo "<script>
            alert('Only students can enroll in courses!');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }

    // 3. pass course id to course enrollment page 
    $courseId = intval($_POST['enroll_now'] ?? 0); // button value
    if ($courseId > 0) {
        header("Location: /SkillPro/Views/Course/courseEnrollment.php?id=". $courseId);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPro Institute Courses</title>
    <!-- Links -->
    <link rel="stylesheet" href="course-style.css">
    <link rel="icon" href="/SkillPro//Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    <!-- Nav Bar Start -->
    <div class="nav-bar">
        <!-- Nav Bar 1 -->
        <div class="nav-bar-1">
            <div class="nav-bar-1-menu">
                <a href="/SkillPro/Views/Login/login.php" data-translate="login">Login</a>
                <a href="/SkillPro/Views/Register/register.php"data-translate="register">Register</a>
                <a href="/SkillPro/Views/Home/index.php#inquiry"data-translate="inquiry">Inquiry</a>
                <a href="/SkillPro/Views/Home/index.php#about-us"data-translate="about us">About Us</a>
                <a href="/SkillPro/Views/Home/index.php#faq"data-translate="faqs">FAQs</a>
            </div>
            <div class="three-lang">
                <a href="" id="English">English</a>
                <a href="" id="Tamil">தமிழ்</a>
                <a href="" id="Sinhala">සිංහල</a>
            </div>
        </div>
        <!-- Nav Bar 2 -->
        <div class="nav-bar-2">
            <div class="nav-bar-2-container">
                <div class="nav-bar-2-logo">SkillPro</div>
                <div class="nav-bar-responsive">
                    <div class="nav-bar-2-menu">
                        <a href="/SkillPro/index.php" data-translate="home">Home</a>
                        <a href="" data-translate="courses">Courses</a>
                        <a href="/SkillPro/Views/InstructorHome/instructor-home.php" data-translate="instructors">Instructors</a>
                        <a href="/SkillPro/Views/Event/event.php" data-translate="events">Events</a>
                        <a href="" data-translate="notices">Notices</a>
                        
                        <div class="nav-bar-2-search">
                            <input type="text" id="home-search" data-translate="search" placeholder="Search Here..." autocomplete="off">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <div class="autocomplete-dropdown" id="home-search-dropdown"></div>
                        </div>
                        
                    </div>
                    <i class="fa-solid fa-moon" id="theme-icon"></i>
                    <div class="hamburger-menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nav Bar menu for mobile -->
        <div class="mobile-nav-bar">
            <div class="mobile-nav-bar-menu">
                <a href="/SkillPro/index.php" data-translate="home">Home</a>
                <a href="" data-translate="courses">Courses</a>
                <a href="/SkillPro/Views/InstructorHome/instructor-home.php" data-translate="instructors">Instructors</a>
                <a href="/SkillPro/Views/Event/event.php" data-translate="events">Events</a>
                <a href="" data-translate="notices">Notices</a>
                <a href="/SkillPro/Views/Home/index.php#inquiry" data-translate="inquiry">Inquiry</a>
                <a href="/SkillPro/Views/Home/index.php#about-us" data-translate="about us">About Us</a>
                <a href="/SkillPro/Views/Home/index.php#faq" data-translate="faqs">FAQs</a>
                
                <div class="mobile-nav-bar-search">
                    <input type="text" id="mobile-home-search" data-translate="search" placeholder="Search Here . . .">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <div class="mobile-autocomplete-dropdown" id="mobile-home-search-dropdown"></div>
                </div>
                
                <div class="mobile-nav-bar-btns">
                    <button data-translate="login" onclick="window.location.href='/SkillPro/Views/Login/login.php'" type="button">Login</button>
                    <button data-translate="register" onclick="window.location.href='/SkillPro/Views/Register/register.php'" type="button">Register</button>
                </div>
            </div>
            <div class="three-lang">
                <a href="" id="English">English</a>
                <a href="" id="Tamil">தமிழ்</a>
                <a href="" id="Sinhala">සිංහල</a>
            </div>
        </div>
    </div>
    <!-- Nav Bar End -->

    <!-- Search and Filter section Start -->
    <div class="search-filter-section">
        <div class="search-filter-container">
            <div class="tit" data-translate="Search courses">Search Courses</div>
            <div class="search-filter-container-bar">
                <input type="text" id="search-text-course-name" data-translate="course name" placeholder="Course Name"/>
            </div>
            <div class="filters">
                <div class="group">
                    <div class="dropdown" data-input="course-category">
                        <div class="dropdown-selected"><span data-translate="category">Category</span><i class="fa-solid fa-caret-down"></i></div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <?php
                            $categories = $courseObj->getAllCategories();
                            if (!empty($categories)) {
                                // echo "<script>console.log(" . json_encode($course['category']) . ");</script>"; 
                                foreach($categories as $cat):?>
                                    <li data-value="<?= htmlspecialchars($cat); ?>"><?= htmlspecialchars($cat); ?></li>
                                <?php endforeach; ?>
                            <?php } ?>
                        </ul>
                    </div>
                    <input type="hidden" id="course-category" value="">
                </div>

                <div class="group">
                    <div class="dropdown" data-input="course-branch">
                        <div class="dropdown-selected"><span data-translate="location">Location</span><i class="fa-solid fa-caret-down"></i></div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <input type="hidden" id="course-branch">
                </div>

                <div class="group">
                    <div class="dropdown" data-input="course-duration">
                        <div class="dropdown-selected"><span data-translate="duration">Duration</span><i class="fa-solid fa-caret-down"></i></div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <?php
                            $durations = $courseObj->getAllDurations();
                            if (!empty($durations)) {
                                // echo "<script>console.log(" . json_encode($course['category']) . ");</script>"; 
                                foreach($durations as $dur):?>
                                    <li data-value="<?= htmlspecialchars($dur); ?>"><?= htmlspecialchars($dur); ?></li>
                                <?php endforeach; ?>
                            <?php } ?>
                        </ul>
                    </div>
                    <input type="hidden" id="course-duration" value="">
                </div>

                <div class="group">
                    <div class="dropdown" data-input="course-instructor">
                        <div class="dropdown-selected"><span data-translate="instructor">Instructor</span><i class="fa-solid fa-caret-down"></i></div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <?php
                            $instructors = $courseObj->getAllInstructors();
                            if (!empty($instructors)) {
                                // echo "<script>console.log(" . json_encode($course['category']) . ");</script>"; 
                                foreach($instructors as $ins):?>
                                    <li data-value="<?= htmlspecialchars($ins); ?>"><?= htmlspecialchars($ins); ?></li>
                                <?php endforeach; ?>
                            <?php } ?>
                        </ul>
                    </div>
                    <input type="hidden" id="course-instructor" value="">
                </div>
            </div>
        </div>
    </div>
    <!-- Search and Filter section End -->

    <!-- Course Card List Section Start -->
    <div class="course-card-list-section">
        <div class="course-list">

            <!-- card -->
            <?php
            if ($AllCourses['success']) {
                // echo "<script>console.log(" . json_encode($AllCourses['data']) . ");</script>";
                foreach($AllCourses['data'] as $course):
            ?>
                    <div class="course-card"
                    id="course-<?= $course['id']; ?>"
                    data-coursedetail='<?= json_encode($course); ?>'
                    data-name="<?= htmlspecialchars($course['name']); ?>"
                    data-category="<?= htmlspecialchars($course['category']); ?>"
                    data-branch="<?= htmlspecialchars($course['branches']); ?>"
                    data-instructor="<?= htmlspecialchars($course['instructors']); ?>"
                    data-duration="<?= htmlspecialchars($course['duration']); ?>"
                    data-durationtype="<?= htmlspecialchars($course['duration_type']); ?>"
                    >
                        <img src="/SkillPro/Views/Course/serveImage.php?image_id=<?= $course['id']; ?>" alt="course-image" class="course-image">
                        <div class="course-text">
                            <h3 class="course-name"><?= htmlspecialchars($course['name']); ?></h3>
                            <p class="instructor-name"><span data-translate="instructor">Instructors</span>: <?= htmlspecialchars($course['instructors']); ?></span>
                            <p class="location"><span data-translate="location">Location</span>: <?= htmlspecialchars($course['branches']); ?></span>
                            <p class="duration"><span data-translate="duration">Duration</span>: <?= htmlspecialchars($course['duration'] . ' ' . $course['duration_type']); ?></span>
                        </div>
                        <div class="course-btn">
                            <button class="view-more" data-translate="view-more">View More</button>
                            <form method="post">
                                <button class="enroll-now" name="enroll_now" value="<?= $course['id']; ?>" data-translate="enroll-now">Enroll Now</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
            <?php } else { ?>
                <p>There is No Course Currently!</p>
            <?php } ?>

        </div>
    </div>
    <!-- Course Card List Section End -->

    <!-- separate course div Start-->
    <div id="course-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="course-details">
            <!-- Course details will be injected here -->
                <h2>hi</h2>
            </div>
        </div>
    </div>

    <!-- separate course div End-->
    
    <script src="course-script.js"></script>
</body>
</html>