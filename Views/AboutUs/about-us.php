<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPro Institute AboutUs</title>
    <!-- Links -->
    <link rel="stylesheet" href="about-us-style.css">
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
                <a href=""data-translate="about us">About Us</a>
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
                <a href="/SkillPro/Views/InstructorHome/instructor-home.php" data-translate="instructors">Instructors</a>
                <a href="/SkillPro/Views/Event/event.php" data-translate="events">Events</a>
                <a href="/SkillPro/Views/Notice/notice.php" data-translate="notices">Notices</a>
                <a href="/SkillPro/Views/Home/index.php#inquiry" data-translate="inquiry">Inquiry</a>
                <a href="" data-translate="about us">About Us</a>
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

    <!-- Hero Section -->
    <section class="about-hero">
    <div class="hero-content">
        <h1 data-translate="skillpro">SkillPro Institute</h1>
        <p data-translate="empower">Empowering Sri Lanka through Skills, Innovation & Education</p>
    </div>
    </section>

    <!-- Who We Are -->
    <section class="about-section">
    <div class="container">
        <h2 data-translate="who-we-are">Who We Are</h2>
        <p data-translate="ab p3">
        “SkillPro Institute” is a reputed vocational training institute registered under the Tertiary and Vocational Education Commission (TVEC) of Sri Lanka. 
        With branches in Colombo, Kandy, and Matara, we provide industry-recognized training programs in IT, Engineering, Tourism, Hospitality and more.  
        Our aim is to bridge the gap between education and employability, preparing youth and adults for tomorrow’s job market.
        </p>
    </div>
    </section>

    <!-- Vision & Mission -->
    <section class="vision-mission">
    <div class="container">
        <div class="card">
        <i class="fas fa-lightbulb"></i>
        <h3 data-translate="vision">Our Vision</h3>
        <p data-translate="vision-text">To be the leading vocational training institute in Sri Lanka, inspiring a skilled workforce that drives national and global progress.</p>
        </div>
        <div class="card">
        <i class="fas fa-bullseye"></i>
        <h3 data-translate="mission">Our Mission</h3>
        <p data-translate="mission-text">To deliver innovative, affordable, and career-focused training programs that empower individuals with practical skills and lifelong learning opportunities.</p>
        </div>
    </div>
    </section>

    <!-- Core Values -->
    <section class="values">
    <div class="container">
        <h2 data-translate="core-values">Our Core Values</h2>
        <div class="values-grid">
        <div class="value"><i class="fas fa-award"></i><h4 data-translate="val-excellence">Excellence</h4><p data-translate="val-excellence-text">We maintain the highest quality standards in teaching and training.</p></div>
        <div class="value"><i class="fas fa-users"></i><h4 data-translate="val-collab">Collaboration</h4><p data-translate="val-collab-text">Working closely with industries and communities.</p></div>
        <div class="value"><i class="fas fa-graduation-cap"></i><h4 data-translate="val-innov">Innovation</h4><p data-translate="val-innov-text">Adapting to modern technologies and global skills demand.</p></div>
        <div class="value"><i class="fas fa-handshake"></i><h4 data-translate="val-integrity">Integrity</h4><p data-translate="val-integrity-text">We are transparent, ethical, and committed to our students’ success.</p></div>
        </div>
    </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose">
    <div class="container">
        <h2 data-translate="why skillpro?">Why Choose SkillPro?</h2>
        <ul>
        <li><i class="fas fa-check-circle"></i> <span data-translate="why1">Government approved TVEC registered institute</span></li>
        <li><i class="fas fa-check-circle"></i> <span data-translate="why2">Experienced instructors & industry experts</span></li>
        <li><i class="fas fa-check-circle"></i> <span data-translate="why3">Affordable training programs</span></li>
        <li><i class="fas fa-check-circle"></i> <span data-translate="why4">Internship and job placement support</span></li>
        </ul>
    </div>
    </section>

    <!-- Branches -->
    <section class="branches">
    <div class="container">
        <h2 data-translate="our branches">Our Branches</h2>
        <div class="branch-grid">
        <div class="branch">
            <img src="/SkillPro/Images/colombo-branch.jpg" alt="Colombo Branch">
            <h4 data-translate="branch-colombo">Colombo</h4>
        </div>
        <div class="branch">
            <img src="/SkillPro/Images/kandy-branch.jpg" alt="Kandy Branch">
            <h4 data-translate="branch-kandy">Kandy</h4>
        </div>
        <div class="branch">
            <img src="/SkillPro/Images/matara-branch.jpg" alt="Matara Branch">
            <h4 data-translate="branch-matara">Matara</h4>
        </div>
        </div>
    </div>
    </section>

    <!-- CTA -->
    <section class="cta">
    <h2 data-translate="cta-title">Join With SkillPro Today!</h2>
    <p data-translate="cta-text">Unlock your future with industry-ready skills. Explore our courses and start your journey.</p>
    <a href="/SkillPro/Views/Course/course.php" class="btn" data-translate="explore courses">Explore Courses</a>
    </section>

    
    <script src="about-us-script.js"></script>
</body>
</html>