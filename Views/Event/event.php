<?php 
require_once __DIR__ . "/../../Controls/courseController.php";
require_once __DIR__ . "/../../Helpers/encryption.php";
$courseObj = new CourseController();
$allEvents = $courseObj->getAllEvents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPro Institute Events</title>
    <!-- Links -->
    <link rel="stylesheet" href="event-style.css">
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
                        <a href="/SkillPro/Views/InstructorHome/instructor-home.php" data-translate="instructors">Instructors</a>
                        <a href="" data-translate="events">Events</a>
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
                <a href="/SkillPro/Views/InstructorHome/instructor-home.php" data-translate="instructors">Instructors</a>
                <a href="" data-translate="events">Events</a>
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

    <!-- Course Card List Section Start -->
    <div class="course-card-list-section">
        <div class="course-list">
            <!-- card -->
            <?php
            if (!empty($allEvents)) {
                foreach($allEvents as $e):
                    // echo "<script>console.log(" . json_encode($e) . ");</script>"; ?>
                    <div class="event-card" id="event-<?= $e['id']; ?>"
                    data-detail="<?= htmlspecialchars(json_encode($e), ENT_QUOTES, "UTF-8");?>"
                    >
                        <img src="<?= htmlspecialchars('/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($e['image_path'])); ?>" alt="Workshop" class="event-image">
                        <div class="event-content">
                            <h3 class="event-title"><?= htmlspecialchars($e['title']); ?></h3>
                            <p class="event-date">📅 <?= htmlspecialchars($e['start_date_time_formatted']); ?></p>
                            <p class="event-location">📍 SkillPro <?= htmlspecialchars($e['branch']); ?> Branch</p>
                            <p class="event-short"><?php
                                if (strlen($e['description']) > 45) {
                                    $short_description = substr($e['description'], 0, 45) . '...';
                                } else {
                                    $short_description = $e['description'];
                                }
                                ?><?= htmlspecialchars($short_description); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php } else { ?>
                <p>There is No Events Currently!</p>
            <?php } ?>

        </div>
    </div>
    <!-- Course Card List Section End -->

    <!-- separate course div Start-->
    <div id="course-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="event-details">
            <!-- Course details will be injected here -->
            </div>
        </div>
    </div>

    <!-- separate course div End-->
    
    <script src="event-script.js"></script>
</body>
</html>