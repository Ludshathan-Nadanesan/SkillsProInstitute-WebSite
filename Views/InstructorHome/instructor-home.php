<?php
require_once __DIR__ . "/../../Controls/instructorController.php";
require_once __DIR__ . "/../../Helpers/encryption.php";

$instructorObj = new InstructorController();
$AllInstructor = $instructorObj->getAllInstructors();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPro Institute Instructors</title>
    <!-- Links -->
    <link rel="stylesheet" href="instructor-home-style.css">
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
                        <a href="/SkillPro/Views/Course/course.php" data-translate="courses">Courses</a>
                        <a href="/SkillPro/Views/Event/event.php" data-translate="events">Events</a>
                        <a href="/SkillPro/Views/Notice/notice.php" data-translate="notices">Notices</a>
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
                <a href="/SkillPro/Views/Course/course.php" data-translate="courses">Courses</a>
                <a href="" data-translate="instructors">Instructors</a>
                <a href="/SkillPro/Views/Event/event.php" data-translate="events">Events</a>
                <a href="/SkillPro/Views/Notice/notice.php" data-translate="notices">Notices</a>
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
            <div class="tit">Search Instructor</div>
            <div class="search-filter-container-bar">
                <input type="text" id="search-text-course-name" placeholder="Instructor Name"/>
            </div>
            <div class="filters">
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
            </div>
        </div>
    </div>
    <!-- Search and Filter section End -->

    <!-- Course Card List Section Start -->
    <div class="course-card-list-section">
        <div class="course-list">

            <!-- card -->
            <?php
            if ($AllInstructor) {
                // echo "<script>console.log(" . json_encode($AllCourses['data']) . ");</script>";
                foreach($AllInstructor as $ins):
            ?>
                    <div class="course-card"
                    id="instructor-<?= $ins['id']; ?>"
                    data-name="<?= htmlspecialchars($ins['full_name']); ?>"
                    data-branch="<?= htmlspecialchars($ins['branch']); ?>"
                    >
                        <img src="<?= htmlspecialchars('/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($ins['image_path'])); ?>" alt="Instructor" class="instructor-image">
                        <h3 class="instructor-name"><?= htmlspecialchars($ins['full_name']); ?></h3>
                        <h3 class="instructor-mobile">Mobile: <span><?= htmlspecialchars($ins['mobile_number']); ?></span></h3>
                        <h3 class="instructor-email">Email: <span><?= htmlspecialchars($ins['email']); ?></span></h3>
                        <h3 class="instructor-branch">Branch: <span><?= htmlspecialchars($ins['branch']); ?></span></h3>
                        <p class="instructor-role"><?= htmlspecialchars($ins['specialization']); ?></p>
                        <p class="instructor-intro"><?= htmlspecialchars($ins['bio']); ?></p>
                    </div>
                    <?php endforeach; ?>
            <?php } else { ?>
                <p>There is No Instructor Currently!</p>
            <?php } ?>

        </div>
    </div>
    <script src="instructor-home-script.js"></script>
</body>
</html>