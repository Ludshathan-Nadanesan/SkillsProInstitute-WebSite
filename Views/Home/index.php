<?php

session_start();

require_once __DIR__ . "/../../Models/course.php";
require_once __DIR__ . "/../../Helpers/encryption.php";
require_once __DIR__ . "/../../Controls/instructorController.php";
require_once __DIR__ . "/../../Controls/studentController.php";
$courseObj = new Course();
$homeCourses = $courseObj->get3courses();

$instructorObj = new InstructorController();
$homeInstructor = $instructorObj->getAllInstructors();

$studentObj = new StudentController();


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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // Sanitize inputs
    $data = [
        'name'    => trim($_POST['inquiry_name'] ?? ''),
        'email'   => trim($_POST['inquiry_email'] ?? ''),
        'course'  => trim($_POST['inquiry_course'] ?? ''),
        'message' => trim($_POST['inquiry_message'] ?? ''),
    ];

    $result = $studentObj->addNonStudentQuery($data);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPro Institute</title>
    <!-- Links -->
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/SkillPro/Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
</head>
<body>
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <!-- Nav Bar Start -->
    <div class="nav-bar">
        <!-- Nav Bar 1 -->
        <div class="nav-bar-1">
            <div class="nav-bar-1-menu">
                <a href="/SkillPro/Views/Login/login.php" data-translate="login">Login</a>
                <a href="/SkillPro/Views/Register/register.php"data-translate="register">Register</a>
                <a href="#inquiry"data-translate="inquiry">Inquiry</a>
                <a href="#about-us"data-translate="about us">About Us</a>
                <a href="#faq"data-translate="faqs">FAQs</a>
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
                <div class="nav-bar-2-logo"><a href="/SkillPro/Views/Home/index.php">SkillPro</a></div>
                <div class="nav-bar-responsive">
                    <div class="nav-bar-2-menu">
                        <a href="#home" data-translate="home">Home</a>
                        <a href="#courses" data-translate="courses">Courses</a>
                        <a href="#instructors" data-translate="instructors">Instructors</a>
                        <a href="#events-and-courses" data-translate="events">Events</a>
                        <a href="#events-and-courses" data-translate="notices">Notices</a>
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
                <a href="#home" data-translate="home">Home</a>
                <a href="#courses" data-translate="courses">Courses</a>
                <a href="#instructors" data-translate="instructors">Instructors</a>
                <a href="#events-and-courses" data-translate="events">Events</a>
                <a href="#notices" data-translate="notices">Notices</a>
                <a href="#inquiry" data-translate="inquiry">Inquiry</a>
                <a href="#about-us" data-translate="about us">About Us</a>
                <a href="#faq" data-translate="faqs">FAQs</a>
                <div class="mobile-nav-bar-search">
                    <input type="text" id="mobile-home-search" data-translate="search" placeholder="Search Here . . .">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <div class="mobile-autocomplete-dropdown" id="mobile-home-search-dropdown"></div>
                </div>
                <div class="mobile-nav-bar-btns">
                    <button onclick="window.location.href='/SkillPro/Views/Login/login.php'" data-translate="login" type="button">Login</button>
                    <button onclick="window.location.href='/SkillPro/Views/Register/register.php'" data-translate="register" type="button">Register</button>
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

    <!-- Hero Section Start -->
    <header class="hero-section" id="home">
        <div class="hero-content">
            <div class="hero-content-title">
                <h2 class="tit-1" data-translate="learn skills">Learn Skills,</h2>
                <h2 class="tit-2" data-translate="build your future">Build Your Future</h2>
            </div>
            <p class="tit-sub" data-translate="government approved courses with expert instructors">Government approved courses with expert instructors</p>
            <div class="hero-section-btns">
                <button type="button" onclick="window.location.href='/SkillPro/Views/Course/course.php'" data-translate="explore courses">Explore Courses</button>
                <button type="button" onclick="window.location.href='/SkillPro/Views/Register/register.php'" data-translate="register now">Register Now</button>
            </div>
        </div>
    </header>
    <!-- Hero Section End -->

    <!-- Chatbot start -->
    <button type="button" id="chat-bot-toggle"><i class="fa-regular fa-comments"></i></button>
    <div class="chat-container">
        <div class="chat-header">SkillPro AI Chat</div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="chat-input">
        <input type="text" id="userInput" placeholder="Type your message..." />
        <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <!-- chatbot end -->

    <!-- Highlight Section Start -->
    <div class="highlight-section">
        <div class="tit">
            <h3 data-translate="why skillpro?">Why SkillPro?</h3>
            <p data-translate="why skillpro answer">SkillPro Institute is a reputed vocational training institute, registered under the Tertiary and Vocational Education Commission (TVEC) of Sri Lanka. We focus on delivering quality training that builds both knowledge and skills for the real job market.</p>
        </div>
        <div class="highlight-section-container">
            <div class="highlight-section-card">
                <i class="fa-solid fa-graduation-cap"></i>
                <div class="highlight-section-card-text">
                    <h3 data-translate="qualified trainers">Qualified Trainers</h3>
                    <p data-translate="experienced and certified trainers to guide you">Experienced and certified trainers to guide you</p>
                </div>
            </div>
            <div class="highlight-section-card">
                <i class="fa-solid fa-building-columns"></i>
                <div class="highlight-section-card-text">
                    <h3 data-translate="government accredited">Government Accredited</h3>
                    <p data-translate="registered under the TVEC of Sri Lanka">Registered under the TVEC of Sri Lanka</p>
                </div>
            </div>
            <div class="highlight-section-card">
                <i class="fa-solid fa-map-location"></i>
                <div class="highlight-section-card-text">
                    <h3 data-translate="multiple Branches">Multiple Branches</h3>
                    <p data-translate="branches in Colombo, Kandy & Matara">Branches in Colombo, Kandy & Matara</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Highlight Section End -->

    <!-- Course Programme Start-->
    <section class="home-courses-section" id="courses">
        <div class="course-header">
            <h2 data-translate="our courses">Our Courses</h2>
            <a href="/SkillPro/Views/Course/course.php" class="view-all-btn" data-translate="view all courses">View All Courses</a>
        </div>

        <!-- Swiper Slider -->
        <div class="swiper course-list-swiper">
            <div class="swiper-wrapper">
                <!-- Slide -->
                <?php if(!empty($homeCourses)): ?>
                    <?php foreach($homeCourses as $course): ?>
                        <div class="swiper-slide">
                            <div class="course-card">
                                <img src="/SkillPro/Views/Home/serveImage.php?image_id=<?php echo $course['id']?>" alt="course-image" class="course-image">
                                <div class="course-text">
                                    <h3 class="course-name"><?php echo htmlspecialchars($course['name']); ?></h3>
                                    <p class="instructor-name"><span data-translate="instructor">Instructor</span>: Mr.Silva</p>
                                    <p class="location"><span data-translate="location">Location</span>: <?php echo $course['branches'] ?: 'Not specified'; ?></p>
                                    <p class="duration"><span data-translate="duration">Duration</span>: <?php echo $course['duration'] . ' ' . $course['duration_type']; ?></p>
                                </div>
                                <div class="course-btn">
                                    <button class="view-more" onclick="window.location.href='/SkillPro/Views/Course/course.php'" data-translate="view-more">View More</button>
                                    <form method="post">
                                        <button type="submit" name="enroll_now" class="enroll-now" data-translate="enroll-now" 
                                        value="<?php echo $course['id'] ?>">Enroll Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No courses available.</p>
                <?php endif; ?>
            </div>
                
            <!-- Arrows -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>

            <!-- Dots -->
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <!-- Course Programme End-->

    <!-- Instructor Details Section Start -->
    <section class="instructor-preview-section" id="instructors">
        <div class="instructor-preview-container">
            <div class="instruction-header">
                <h2 data-translate="our instructors">Our Instructors</h2>
                <a href="/SkillPro/Views/InstructorHome/instructor-home.php" data-translate="view all instructors">View All Instructors</a>
            </div>
            <div class="instructor-list">
                <?php 
                if (!empty($homeInstructor)) { 
                    for ($i = 0; $i < 3; $i++): 
                        if (isset($homeInstructor[$i])) { ?>
                            <!-- Instructor Card 1 -->
                            <div class="instructor-card">
                                <img src="<?= htmlspecialchars('/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($homeInstructor[$i]['image_path'])); ?>" alt="Instructor" class="instructor-image">
                                <h3 class="instructor-name"><?= htmlspecialchars($homeInstructor[$i]['full_name']); ?></h3>
                                <h3 class="instructor-mobile">Mobile: <span><?= htmlspecialchars($homeInstructor[$i]['mobile_number']); ?></span></h3>
                                <h3 class="instructor-email">Email: <span><?= htmlspecialchars($homeInstructor[$i]['email']); ?></span></h3>
                                <h3 class="instructor-branch">Branch: <span><?= htmlspecialchars($homeInstructor[$i]['branch']); ?></span></h3>
                                <p class="instructor-role"><?= htmlspecialchars($homeInstructor[$i]['specialization']); ?></p>
                                <p class="instructor-intro"><?= htmlspecialchars($homeInstructor[$i]['bio']); ?></p>
                            </div>
                <?php } endfor; } ?>
            </div>
        </div>
    </section>
    <!-- Instructor Details Section End -->


    <!-- Events & Notices Section Start -->
    <div class="events-and-notices-section" id="events-and-courses">
        <div class="events-and-notices-section-container">
            <!-- Events Section Start -->
            <section class="events-section">
                <div class="tit">
                    <h2 class="section-title" data-translate="upcomming events">Events</h2>
                    <a href="/SkillPro/Views/Event/event.php" data-translate="view all events">View All Events</a>
                </div>
        
                <!-- Swiper Slider -->
                <div class="event-list swiper">
                    <div class="swiper-wrapper">
                        <?php
                        $allEvents = $courseObj->getAllEvents();
                        if (!empty($allEvents)) {
                            for ($i = 0; $i < 4 ; $i++):?>
                                <!-- Event Card 1 -->
                                <div class="swiper-slide">
                                    <div class="event-card">
                                        <img src="<?= htmlspecialchars('/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($allEvents[$i]['image_path'])); ?>" alt="Workshop" class="event-image">
                                        <div class="event-content">
                                            <h3 class="event-title"><?= htmlspecialchars($allEvents[$i]['title']); ?></h3>
                                            <p class="event-date">📅 <?= htmlspecialchars($allEvents[$i]['start_date_time_formatted']); ?></p>
                                            <p class="event-location">📍 SkillPro <?= htmlspecialchars($allEvents[$i]['branch']); ?> Branch</p>
                                            <p class="event-short"><?php
                                                if (strlen($allEvents[$i]['description']) > 45) {
                                                    $short_description = substr($allEvents[$i]['description'], 0, 45) . '...';
                                                } else {
                                                    $short_description = $allEvents[$i]['description'];
                                                }
                                                ?><?= htmlspecialchars($short_description); ?></p>
                                        </div>
                                    </div>
                                </div>
                        <?php endfor; }?>
                    </div>
        
                    <!-- Navigation & Pagination -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
            <!-- Events Section End -->

            <!-- Notices Section Start -->
            <section class="notices-section" id="notices">
                <div class="tit">
                    <h2 class="section-title" data-translate="notices">Notices</h2>
                    <a href="/SkillPro/Views/Notice/notice.php" data-translate="view all notices">View All Notices</a>
                </div>
                
                <ul class="notice-list">
                    <?php
                    $allNotices = $courseObj->getAllNotices();
                    if (!empty($allNotices)) {
                        foreach ($allNotices as $n):
                            if ($n['audience'] == 'All') {?>  
                                <!-- Notice -->
                                <li class="notice-item">
                                    <span class="notice-date">📅 <?= htmlspecialchars($n['start_date']); ?></span>
                                    <p class="notice-text"><?= htmlspecialchars($n['title']); ?></p>
                                </li>
                    <?php } endforeach; } else {?>
                        <li class="notice-item">
                            <span class="notice-date">Empty Notices!</span>
                            <p class="notice-text">Notices are not currently available.</p>
                        </li>
                    <?php } ?>
                </ul>
            </section>
            <!-- Notices Section End -->
        </div>
    </div>
    <!-- Events & Notices Section End -->

    <!-- About Us Section Start -->
    <section class="about-us-section" id="about-us">
        <div class="about-us-section-container">
            <div class="about-us-content">
                <div class="about-text">
                    <h2 data-translate="about us">About Us</h2>
                    <p data-translate="ab p1">Education is a key pillar of sustainable development and a driving force for individual empowerment. According to UNESCO, “Education is not a way to escape poverty, it is a way of fighting it.” Sri Lanka has made considerable progress in providing access to education at all levels, maintaining high literacy rates, and investing in skills development to build a competent workforce.</p>
                    
                    <p data-translate="ab p2">To support economic growth, the Sri Lankan government encourages Technical and Vocational Education and Training (TVET) to equip youth and adults with job-oriented skills. TVET institutions offer programs in Information Technology, Engineering, Tourism, Hospitality, and more, bridging the skills gap and enhancing employability.</p>
                    
                    <p><span data-translate="ab p3">“SkillPro Institute” is a reputed vocational training institute registered under the Tertiary and Vocational Education Commission (TVEC) of Sri Lanka. It operates branches in Colombo, Kandy, and Matara. The institute now provides an interactive web-based application to manage and promote its training programs.</span><a href="/SkillPro/Views/AboutUs/about-us.php" data-translate="read more"> Read More</a></p>
                </div>
                <div class="about-image">
                    <img src="/SkillPro/Images/SkillPro.jpg" alt="SkillPro Institute">
                </div>
            </div>
        </div>
    </section>
    <!-- About Us Section End -->

    <!-- Student Success Stories Section Start -->
    <section class="student-success-stories-section">
        <div class="container">
            <div class="text">
                <h2 data-translate="stu suc stry">Student Success Stories</h2>
                <p data-translate="stu suc stry content">See how SkillPro Institute has transformed lives and helped students achieve their career goals.</p>
            </div>
            <!-- Slider main container -->
            <div class="story-container swiper">
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                    <!-- Slides -->
                    <div class="swiper-slide">
                        <div class="story-card">
                            <div class="story">
                                <h3>Sajith Rathna</h3>
                                <p>“Coming from a small town near Kandy, I wanted to build a skill that could secure my future. At SkillPro, I completed the Welding & Engineering course, where I learned industry-standard techniques. With this knowledge, I started my own small workshop, which has now grown to employ five other young people in my community. SkillPro empowered me to become self-reliant and help others.”</p>
                            </div>
                            <img src="/SkillPro/Images/student1.jpg" alt="student_pic">          
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="story-card">
                            <div class="story">
                                <h3>Kumaran Padayappa</h3>
                                <p>“When I joined SkillPro, I only had basic computer knowledge. Through the ICT program, I gained strong foundations in programming, databases, and web development. After graduating, I was selected as a Junior Software Engineer at a reputed IT company in Colombo. The practical training and supportive instructors gave me the confidence to achieve my dream career.”</p>
                            </div>
                            <img src="/SkillPro/Images/student3.jpg" alt="student_pic">          
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="story-card">
                            <div class="story">
                                <h3>Kavinthi Silva</h3>
                                <p>“I always dreamed of working in the hospitality industry but lacked professional training. SkillPro’s Hotel Management program gave me both classroom knowledge and hands-on experience in real hotel environments. Today, I work at a 5-star hotel in Colombo, where I serve international guests daily. This course completely transformed my career path.”</p>
                            </div>
                            <img src="/SkillPro/Images/student2.jpg" alt="student_pic">          
                        </div>
                    </div>
                    ...
                </div>
                <!-- If we need pagination -->
                <div class="swiper-pagination"></div>

                <!-- If we need navigation buttons -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>

            </div>
        </div>
    </section>
    <!-- Student Success Stories Section End -->


    <!-- Contact Section (map) Start -->
    <section class="branches-section">
        <div class="container">
            <h2 data-translate="obas">Our Branches Across Sri Lanka</h2>
            <p data-translate="obas content">SkillPro Institute operates in Colombo, Kandy, and Matara. Find your nearest branch below.</p>
            
            <div class="branches-grid">
                <!-- Colombo Branch -->
                <div class="branch-card">
                    <h3>Colombo Branch</h3>
                    <p><strong data-translate="addr">Address:</strong> 123 Galle Road, Colombo 03</p>
                    <p><strong data-translate="phone">Phone:</strong> +94 77 111 2222</p>
                    <p><strong data-translate="email">Email: </strong><a href="">info@skillpro.lk</a></p>
                    <div class="map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126743.58638758911!2d79.77380331745529!3d6.922001980792168!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae253d10f7a7003%3A0x320b2e4d32d3838d!2sColombo!5e0!3m2!1sen!2slk!4v1756583618263!5m2!1sen!2slk" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            
                <!-- Kandy Branch -->
                <div class="branch-card">
                    <h3>Kandy Branch</h3>
                    <p><strong data-translate="addr">Address:</strong> 45 Peradeniya Road, Kandy</p>
                    <p><strong data-translate="phone">Phone:</strong> +94 77 333 4444</p>
                    <p><strong data-translate="email">Email: </strong><a href="">kandy@skillpro.lk</a></p>
                    <div class="map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63320.418743434006!2d80.5478392589027!3d7.294623767890893!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae366266498acd3%3A0x411a3818a1e03c35!2sKandy!5e0!3m2!1sen!2slk!4v1756583679655!5m2!1sen!2slk" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                
                <!-- Matara Branch -->
                <div class="branch-card">
                    <h3>Matara Branch</h3>
                    <p><strong data-translate="addr">Address:</strong> 78 Beach Road, Matara</p>
                    <p><strong data-translate="phone">Phone:</strong> +94 77 555 6666</p>
                    <p><strong data-translate="email">Email: </strong><a href="">matara@skillpro.lk</a></p>
                    <div class="map">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63492.945970999455!2d80.5095480913615!3d5.9520760426633865!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae138d151937cd9%3A0x1d711f45897009a3!2sMatara!5e0!3m2!1sen!2slk!4v1756583706374!5m2!1sen!2slk" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->


    <!-- FAQ Section Start -->
    <section class="faq-section" id="faq">
        <div class="container">
            <h2 data-translate="faquesion">Frequently Asked Questions</h2>
            
            <div class="faq-item">
                <button class="faq-question" data-translate="faq1">What kind of courses does SkillPro Institute offer?</button>
                <div class="faq-answer">
                    <p data-translate="faq1a">SkillPro Institute offers a wide range of vocational training programs in ICT, Plumbing, Welding, Hotel Management, Tourism, Engineering, and other fields, designed to enhance employability and bridge the skills gap.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" data-translate="faq2">Can I register online for a course?</button>
                <div class="faq-answer">
                    <p data-translate="faq2a">Yes, students can register online by selecting the relevant mode of the course (Online/On-site) and submit their details directly through the web-based application.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" data-translate="faq3">Where are SkillPro Institute branches located?</button>
                <div class="faq-answer">
                    <p data-translate="faq3a">The institute operates three main branches in Colombo, Kandy, and Matara, providing easy access to students across Sri Lanka.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" data-translate="faq4">Does the website show course schedules, fees, and certifications?</button>
                <div class="faq-answer">
                    <p data-translate="faq4a">Yes, the web-based application provides detailed information about course schedules, instructor details, fees, certifications, and enrollment options.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" data-translate="faq5">Does the website display notices and events?</button>
                <div class="faq-answer">
                    <p data-translate="faq5a">Absolutely! Notices for upcoming batches, holidays, seminars, job fairs, and an event calendar for workshops, exams, and course start dates are displayed for students to stay updated.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- FAQ Section End -->

    <!-- Inquiry Form Section Start -->
    <section class="inquiry-section" id="inquiry">
        <div class="container">
            <h2 data-translate="s ur inq">Submit Your Inquiry</h2>
            <p data-translate="sur inq ans">Have questions or want to enroll in a course? Fill out the form below and we’ll get back to you soon!</p>

            <form class="inquiry-form" action="#" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="name" data-translate="full name">Full Name</label>
                    <input data-translate="enter f n" type="text" id="inquiry-name" name="inquiry_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email" data-translate="email addr">Email Address</label>
                    <input data-translate="enter email addr" type="email" id="inquiry-email" name="inquiry_email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <div class="dropdown" data-input="inquiry-course">
                        <div class="dropdown-selected">
                            <span data-translate="select course">Select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                            $AllCourses = $courseObj->getAllCourses();
                            if ($AllCourses['success']) {
                                foreach ($AllCourses['data'] as $course):    
                            ?>
                                <li data-value="<?= htmlspecialchars($course['name']); ?>"><?= htmlspecialchars($course['name']); ?></li>
                            <?php endforeach; } else { ?>
                                    <li data-value="">NO Courses At The Time</li>
                            <?php }?>
                        </ul>
                    </div>
                    <input type="hidden" id="inquiry-course" name="inquiry_course">
                </div>

                <div class="form-group">
                    <label for="message" data-translate="ur msg">Your Message</label>
                    <textarea data-translate="ur msg content" id="inquiry_message" name="inquiry_message" rows="5" placeholder="Write your message or question" required></textarea>
                </div>

                <button data-translate="submit" name="submit_inquiry" type="submit" class="submit-btn">Submit</button>
            </form>
        </div>
    </section>
    <!-- Inquiry Form Section End -->

    <!-- CTA Section Start -->
    <section class="cta-newsletter">
        <div class="cta-container">
            <h2 data-translate="suwsi">Stay Updated with SkillPro Institute</h2>
            <p data-translate="suwsip">Subscribe to our newsletter and never miss updates about new courses, events, and job opportunities.</p>
            <form class="newsletter-form">
                <input data-translate="enter email addr" type="email" placeholder="Enter your email address" required>
                <button data-translate="submit" type="submit">Subscribe</button>
            </form>
        </div>
    </section>
    <!-- CTA Section End -->

    <!-- Footer Section Start -->
    <footer class="footer">
        <!-- About -->
        <div class="footer-about">
            <h3>SkillPro Institute</h3>
            <p>Empowering Sri Lankan youth with skills for a better future. Registered under TVEC, offering high-quality vocational training in ICT, Engineering, Hospitality, and more.</p>
        </div>

        <div class="footer-container">
            <!-- Contact Info -->
            <div class="footer-contact">
                <div class="container">
                    <div class="group">
                        <h4>SkillPro Colombo</h4>
                        <p>123, Galle Road, Colombo 03 | +94 77 111 2222</p>
                        <p>Email: <a href="">info@skillpro.lk</a></p>
                    </div>
                    <div class="group">
                        <h4>SkillPro Kandy</h4>
                        <p>45, Peradeniya Road, Kandy | +94 77 333 444</p>
                        <p>Email: <a href="">kandy@skillpro.lk</a></p>
                    </div>
                    <div class="group">
                        <h4>SkillPro Matara</h4>
                        <p>78, Beach Road, Matara | +94 77 555 6666</p>
                        <p>Email: <a href="">matara@skillpro.lk</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="footer-social">
            <h4>Follow Us</h4>
            <div class="icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> SkillPro Institute. All Rights Reserved.</p>
        </div>
        
    </footer>
    <!-- Footer Section ENd -->

    <script src="script.js"></script>
</body>
</html>