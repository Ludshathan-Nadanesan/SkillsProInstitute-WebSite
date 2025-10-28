<?php
require_once __DIR__ . "/../../Controls/userController.php";
require_once __DIR__ . "/../../Controls/studentController.php";
require_once __DIR__ . "/../../Controls/courseController.php";
require_once __DIR__ . "/../../Helpers/encryption.php";


$control = new UserController();
$stuCntrlr = new StudentController();
$courseController = new CourseController();

// Check if logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /SkillPro/Views/Login/login.php");
    exit;
}

// Allow only students
if ($_SESSION['role'] !== 'student') {
    // Redirect based on actual role
    header("Location: " . $control->redirectBasedOnRole());
    exit;
} else {
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($control->findUserByEmail($_SESSION['email'])['status'] == 0) {
        header("Location: " . "/SkillPro/Views/Student/waitingForApproval.php");
        exit;
    }
}

$studentEmail = $_SESSION['email'];
$student = $stuCntrlr->getStudentDetails($studentEmail);
$studentDetails = $student['data'];
$studentId = $studentDetails['id'];
$currentCourse = $courseController->getStudentCourseBatch($studentId);

$studentBranch = "";
$studentCourseId = "";
$studentCourseName = "";

foreach ($currentCourse as $curentC) {
    if ($curentC['status'] == 'Active') {
        $studentBranch = $curentC['branch'];
        $studentCourseId = $curentC['course_id'];
        $studentCourseName = $curentC['course_name'];
        break;
    }
}

// Current date
$today = new DateTime();


// logic for edit student profile details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save-changes'])) {

    // Collect input values
    $fullName = $_POST['full_name'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $streetAddress = $_POST['street_address'] ?? '';
    $province = $_POST['province'] ?? '';
    $mobileNumber = $_POST['mobile_number'] ?? '';
    $nicNumber = $_POST['nic_number'] ?? '';
    $stuImagePath = $studentDetails['image_path'] ?? null;

    // Handle profile image upload
    if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] === 0) {
        $stuImagefileTmp = $_FILES['student_image']['tmp_name'];

        // Extract file extension
        $ext = strtolower(pathinfo($_FILES['student_image']['name'], PATHINFO_EXTENSION));

        // Allowed extensions check
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            echo "<script>
                alert(" . json_encode('Invalid file type. Only JPG, JPEG, and PNG allowed.') . ");
            </script>";
            exit;
        }

        // Create student folder if not exists
        $studentDir = __DIR__ . "/../../Uploads/Students/" . $studentDetails["id"];
        if (!is_dir($studentDir)) {
            mkdir($studentDir, 0777, true); // recursive create
        }

        // File name (always same so overwrite old)
        $studentImagefileName = "profile_image." . $ext;
        $studentImageTargetPath = $studentDir . "/" . $studentImagefileName;

        // Move file
        if (move_uploaded_file($stuImagefileTmp, $studentImageTargetPath)) {
            // Store relative path in Databse table (eg: Students/id/profile_image.jpg)
            $stuImagePath = "Students/" . $studentDetails['id'] . "/" . $studentImagefileName;
        } else {
            echo "<script>
                alert(" . json_encode('Invalid file type. Only JPG, JPEG, and PNG allowed.') . ");
            </script>";
            exit;
        }
    }

    // Call StudentController register() function
    $result = $stuCntrlr->changeStudentDetails([
        "email" => $studentEmail,
        "student_id" => $studentDetails['id'],
        "full_name" => $fullName,
        "dob" => $dob,
        "gender" => $gender,
        "nic_number" => $nicNumber,
        "street_address" => $streetAddress,
        "province" => $province,
        "mobile_number" => $mobileNumber,
        "image_path" => $stuImagePath
    ]);

    if ($result['success']) {
        echo "<script>
            alert(" . json_encode($result['message']) . ");
            window.location.href = '/SkillPro/Views/Student/student_dashboard.php';
        </script>";
        exit; // prevent further execution
    } else {
        echo "<script>
            alert(" . json_encode($result['message']) . ");
            window.location.href = '/SkillPro/Views/Student/student_dashboard.php';
        </script>";
        exit;
    }

}

// Logic for update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $changePasswordResult = $stuCntrlr->changeStudentPassword([
        "email" => $studentEmail,
        "old_password" => $oldPassword,
        "new_password" => $newPassword,
        "confirm_password" => $confirmPass
    ]);

    if ($changePasswordResult['success']) {
        echo "<script>
            alert(" . json_encode($changePasswordResult['message']) . ");
            window.location.href = '/SkillPro/Views/Login/logout.php';
        </script>";
        exit; // prevent further execution
    } else {
        echo "<script>
            alert(" . json_encode($changePasswordResult['message']) . ");
            window.location.href = '/SkillPro/Views/Student/student_dashboard.php';
        </script>";
        exit;
    }
}

// logic for cancel pending course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_pending_course'])) {
    $registrationId = intval($_POST['cancel_pending_course'] ?? 0);
    if ($registrationId > 0) {
        $result = $courseController->changeStudentRegistration($registrationId, "Cancelled");
        echo "<script>
            alert(" . json_encode($result['message']) . ");
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }
}

// logic for add inquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_inquiry_submit'])) {
    $result = $stuCntrlr->addStudentInquiry(intval($studentId), $_POST["student_inquiry"]);
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
    <title>Student Dashboard - SkillPro Institute</title>
    <!-- Links -->
    <link rel="stylesheet" href="student_dashboard_style.css">
    <link rel="icon" href="/Skillpro/Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Add jsPDF and autoTable via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    
    <!-- Dashboard -->
    <div class="dashboard-container">
        <!-- Header -->
        <header class="header">
            <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
            <h1>SkillPro</h1>
            <nav class="header-nav">
                <i class="fa-solid fa-moon" id="theme-icon"></i>
                <div class="profile-mini">
                    <img src="<?php if(!empty($studentDetails['image_path'])) {
                            echo '/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($studentDetails['image_path']);
                        } else {
                            echo '/SkillPro/Images/user_image.jpg';
                        } ?>" 
                    alt="user_img">
                    <div class="profile-settings">
                        <button id="view-profile">View Profile</button>
                        <a href="/SkillPro/Views/Login/logout.php" id="logout-profile">Log out</a>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <ul>
                <li><button data-target="profile">Profile</button></li>

                <!-- My Courses with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle">My Course</button>
                    <ul class="sub">
                        <li><button data-target="current-course">Current Course</button></li>
                        <li><button data-target="pending-course">Pending Course</button></li>
                        <li><button data-target="completed-courses">Completed Courses</button></li>
                    </ul>
                </li>
                <li><button data-target="module-materials">Module Materials</button></li>

                <li><button data-target="time-table">Timetable</button></li>

                <li><button data-target="notices">Notices</button></li>
                <li><button data-target="events">Events</button></li>
                <li><button data-target="inquiries">Inquiries</button></li>
            </ul>
        </aside>

        <!-- Main Section -->
        <main class="main">
            <!-- Student Profile -->
            <div class="profile">
                <form method="post" enctype="multipart/form-data" id="student-details-edit" action="/SkillPro/Views/Student/student_dashboard.php">
                    <!-- Profile Image -->
                    <div class="student-profile-picture-container">
                        <img src="<?php if(!empty($studentDetails['image_path'])) {
                            echo '/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($studentDetails['image_path']); 
                        } else {
                            echo "/SkillPro/Images/user_image.jpg";
                        } ?>"
                        alt="student_profile_img">
                        <label>Student Profile Image</label>
                        <label class="custom-file-upload">
                            Upload Image
                            <input type="file" name="student_image" id="studentImageFileInput">
                        </label>
                    </div>

                    <!-- Basic Info -->
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input readonly type="text" name="full_name" id="full_name" 
                            value="<?php if(!empty($studentDetails['full_name'])) {
                                    echo trim($studentDetails['full_name']);
                                } else {
                                    echo "No Name";
                                } ?>">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input readonly type="email" name="email" id="email"
                            value="<?php if(!empty($studentDetails['email'])) {
                                    echo $studentDetails['email'];
                                } else {
                                    echo null;
                                } ?>">
                    </div>

                    <div class="form-group">
                        <label>Date of Birth (Y-M-D)</label>
                        <input readonly type="text" name="dob" id="dob" value="<?php echo !empty($studentDetails['dob']) ? $studentDetails['dob'] : null;?>">
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <div class="dropdown" data-input="gender">
                            <div class="dropdown-selected">
                                <span><?php echo !empty($studentDetails['gender']) ? $studentDetails['gender'] : null;?></span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Male">Male</li>
                                <li data-value="Female">Female</li>
                            </ul>
                        </div>
                        <!-- Hidden input (this is what PHP will read) -->
                        <input readonly type="text" id="gender" name="gender" value="<?php echo !empty($studentDetails['gender']) ? $studentDetails['gender'] : null;?>">
                    </div>

                    <div class="form-group">
                        <label>Street Address</label>
                        <input readonly type="text" name="street_address" value="<?php echo !empty($studentDetails['street_address']) ? $studentDetails['street_address'] : null;?>">
                    </div>

                    <div class="form-group">
                        <label>Province</label>
                        <div class="dropdown" data-input="province">
                            <div class="dropdown-selected">
                                <span><?php echo !empty($studentDetails['province']) ? $studentDetails['province'] : null;?></span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Western">Western</li>
                                <li data-value="Central">Central</li>
                                <li data-value="Southern">Southern</li>
                                <li data- data-value="North Western">North Western</li>
                                <li data-value="Sabragamuwa">Sabragamuwa</li>
                                <li data-value="Eastern">Eastern</li>
                                <li data-value="Uva">Uva</li>
                                <li data-value="North Central">North Central</li>
                                <li data-value="Northern">Northern</li>
                            </ul>
                        </div>
                        <!-- Hidden input (this is what PHP will read) -->
                        <input readonly type="text" id="province" name="province" value="<?php echo !empty($studentDetails['province']) ? $studentDetails['province'] : null;?>">
                    </div>

                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input readonly type="text" name="mobile_number" value="<?php echo !empty($studentDetails['mobile_number']) ? $studentDetails['mobile_number'] : null;?>">
                    </div>

                    <div class="form-group">
                        <label>NIC Number</label>
                        <input readonly type="text" name="nic_number" value="<?php echo !empty($studentDetails['nic_number']) ? $studentDetails['nic_number'] : null;?>">
                    </div>

                    <button id="save-changes" name="save-changes" type="submit" value="Save Changes">Save Changes</button>
                    <button type="button" id="edit-details">Edit Details</button>
                </form>


                <!-- Password Change form -->
                <form method="post" id="change-password-form" action="/SkillPro/Views/Student/student_dashboard.php">
                    <!-- Password Change -->
                    <div class="password-change">
                        <h2>Change Password</h2>
                        <div class="form-group">
                            <label>Old Password</label>
                            <input type="password" name="old_password" id="old-password">
                            <i class="fa-solid fa-eye-slash toggle-password" data-target="confirm-password"></i>
                        </div>
    
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" id="new-password">
                            <i class="fa-solid fa-eye-slash toggle-password" data-target="confirm-password"></i>
                        </div>
    
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm-password">
                            <i class="fa-solid fa-eye-slash toggle-password" data-target="confirm-password"></i>
                        </div>

                        <!-- Password Strength Indicator -->
                        <div id="strength-indicator"></div>

                        <button type="submit" name="change_password">Change Password</button>
                    </div>
                </form>

            </div>

            <!-- Pending Courses -->
            <div class="pending-course">
                <h2>Pending Course Enrollments</h2>
                <div class="table-container">
                    <table class="pending-course-table">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Branch</th>
                                <th>Registered At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch pending courses for this student
                            $pendingCourses = $courseController->getStudentRegistrations($studentDetails['id'], 'Pending');
    
                            if ($pendingCourses['success']) {
                                foreach ($pendingCourses['data'] as $course): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($course['course_name']); ?></td>
                                        <td><?= htmlspecialchars($course['branch']); ?></td>
                                        <td><?= htmlspecialchars($course['registered_at']); ?></td>
                                        <td>
                                            <span style="color: orange; font-weight: bold;">
                                                Pending
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post">
                                                <button type="submit" name="cancel_pending_course" value="<?= htmlspecialchars($course['id']) ?>" style="background:#d9534f; color:white; border:none; padding:0.5rem 1rem; border-radius:0.5rem; cursor:pointer;font-family: var(--font-family);">Cancel Enrollment</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            } else { ?>
                                <tr>
                                    <td colspan="5">No pending enrollments.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- Current Course -->
            <div class="current-course">
                <h2>Current Course</h2>
                <?php
                // echo "<script>console.log(" . json_encode($currentCourse) . ");</script>";
                if (!empty($currentCourse) && !empty($pendingCourses)) {
                    foreach ($currentCourse as $cc):
                        if ($cc['status'] == 'Active') {
                            $studentBranch = $cc['branch']?>
                            <div class="ver-tab-conta">
                                <table>
                                    <tr>
                                        <th>Course</th>
                                        <td><?= $cc['course_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Branch</th>
                                        <td><?= $cc['branch']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Batch</th>
                                        <td><?= $cc['batch_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Start Date</th>
                                        <td><?= $cc['start_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Date</th>
                                        <td><?= $cc['end_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td><?= $cc['duration_digit'].' '.$cc['duration_type'];  ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><?= $cc['status']; ?></td>
                                    </tr>
                                </table>
                            </div>
                        <?php } elseif ($cc['status'] == 'Pending') {?>
                            <p><span style="color: #d9534f; font-weight: 600;">NOTE: </span>Your enrollment request has been successfully submitted and is currently Pending Approval.The administration team will review your application and place you in the correct batch.Once approved, you will be notified via email with further instructions.</p>
                            <div class="ver-tab-conta">
                                <table>
                                    <tr>
                                        <th>Course</th>
                                        <td><?= $cc['course_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td><?= $cc['duration_digit'].' '.$cc['duration_type'];  ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><?= $cc['status']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Branch</th>
                                        <td><?= $cc['branch']; ?></td>
                                    </tr>

                                </table>
                            </div>
                    <?php } else { ?>
                        <h3>No Current Courses</h3>
                        <a href="/SkillPro/Views/Course/course.php" class="btn-enroll">Enroll Now</a>
                <?php } endforeach; } else {?>
                    <h3>No Current Courses</h3>
                    <p>Thank you for registering with SkillPro Institute. You are not enrolled in any course yet. Please enroll to start your learning journey.</p>
                    <a href="/SkillPro/Views/Course/course.php" class="btn-enroll">Enroll Now</a>
                <?php }?>
            </div>

            <!-- Completed Courses -->
            <div class="completed-courses">
                <h2>Completed Course</h2>
                <?php 
                if (!empty($currentCourse)) {
                    foreach ($currentCourse as $coc):
                        if($coc['status'] == 'Completed') {?>
                            <div class="ver-tab-conta">
                                <table>
                                    <tr>
                                        <th>Course</th>
                                        <td><?= $coc['course_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Branch</th>
                                        <td><?= $coc['branch']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Batch</th>
                                        <td><?= $coc['batch_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Start Date</th>
                                        <td><?= $coc['start_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Date</th>
                                        <td><?= $coc['end_date']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td><?= $coc['duration_digit'].' '.$cc['duration_type'];  ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><?= $cc['status']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Certificate</th>
                                        <td><?php
                                        if ($coc['certificate_issued'] == '0') {?>Not Issued<?php } else {?>Issued<?php } ?></td>
                                    </tr>
                                </table>
                            </div>
                        <?php } else { ?>
                            <p>No completed course.</p>
                        <?php }?>
                <?php endforeach; } else {?>
                    <p>No completed course.</p>
                <?php } ?>
            </div>

            <!-- Notices -->
            <div class="notices">
                <h2>Notices</h2>
                <?php
                $notices = $courseController->getAllNotices();
                // echo "<script>console.log(" . json_encode($notices) . ");</script>";
                if (!empty($notices) && (!empty($studentBranch))) {
                    foreach ($notices as $n):
                        if ($today <= new DateTime($n['end_date']) && ($studentBranch === $n['branch']) || $n['branch'] === "All") {?>
                            <div class="notice-card">
                                    <h3><?= htmlspecialchars($n['title']); ?></h3>
                                    <p><?= htmlspecialchars($n['content']); ?></p>
                                    <p>Start: <?= htmlspecialchars($n['start_date']); ?></p>
                                    <p>End: <?= htmlspecialchars($n['end_date']); ?></p>
                                    <p>Branch: <?= htmlspecialchars($n['branch']); ?></p>
                            </div>
                <?php } endforeach; } else { ?>
                    <p>Currently notices are not available.</p>
                <?php } ?>
            </div>

            <!-- Events -->
            <div class="events">
                <h2>Events</h2>
                <?php
                $events = $courseController->getAllEvents();
                if (!empty($events) && !empty($studentBranch)) {
                    foreach ($events as $e):
                        // Only show events for student's branch OR "All"
                        if (($studentBranch === $e['branch'] || $e['branch'] === "All") 
                            && $today <= new DateTime($e['end_date_time_row'])) { ?>
                            
                            <div class="event-card">
                                <?php if (!empty($e['image_path'])): ?>
                                    <div class="event-image">
                                        <img src="/SkillPro/Helpers/serveUserImage.php?file=<?= urlencode(PathEncryptor::encrypt($e['image_path'])); ?>" alt="Event Image">
                                    </div>
                                <?php endif; ?>

                                <div class="event-details">
                                    <h3><?= htmlspecialchars($e['title']); ?></h3>
                                    <p class="event-desc"><?= nl2br(htmlspecialchars($e['description'])); ?></p>

                                    <p><strong>Start:</strong> <?= htmlspecialchars($e['start_date_time_formatted']); ?></p>
                                    <p><strong>End:</strong> <?= htmlspecialchars($e['end_date_time_formatted']); ?></p>
                                    <p><strong>Branch:</strong> <?= htmlspecialchars($e['branch']); ?></p>
                                </div>
                            </div>
                <?php } endforeach; 
                } else { ?>
                    <p>No events available right now.</p>
                <?php } ?>
            </div>

            <!-- Module matarials -->
            <div class="module-materials">
                <h2>Course Module Materials</h2>
                <?php
                if (!empty($studentCourseId)) {
                    $allModules = $courseController->getModulesByCourseID(intval($studentCourseId));
                    // echo "<script>console.log(" . json_encode($allModules) . ");</script>";
                    if (!empty($allModules)) {?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan="3" style="text-align: center;"><?php echo($studentCourseName); ?></th>
                                    </tr>
                                </thead>
                                <thead>
                                    <tr>
                                        <th>Module Name</th>
                                        <th>Sessions</th>
                                        <th>Material</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allModules as $cm):?>
                                        <tr>
                                            <td><?= $cm['name']; ?></td>
                                            <td><?= $cm['duration']; ?></td>
                                            <td>
                                                <?php 
                                                $path = $cm['module_materials_path']; 
                                                if (!empty($path)) {
                                                    $encPath = PathEncryptor::encrypt($path); // now $secretKey exists
                                                    echo '<a style="color: var(--text-color);" target="_blank" 
                                                            href="/SkillPro/Helpers/serveUserImage.php?file=' . $encPath . '">
                                                            Download File
                                                        </a>';
                                                } else {
                                                    echo '<span style="color: gray;">No Material File</span>';
                                                }?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php } else { ?>
                        <p>There is no course module materials availabale at the time.</p>
                <?php } } else { ?>
                    <p>There is no course module materials availabale at the time.</p>
                <?php } ?>
            </div>

            <!-- Inquiries -->
            <div class="inquiries">
                <h2>Ask a doubt ?</h2>
                <form method="post" autocomplete="off">
                    <label>Message: </label>
                    <textarea name="student_inquiry" placeholder="Type your issue here ..."></textarea>
                    <button name="student_inquiry_submit" type="submit">Ask</button>
                </form>
            </div>

            <!-- Time Table -->
            <div class="time-table">
                <h2>Time Table</h2>
                <?php
                $timetable = $courseController->getStudentSchedule($studentId);
                // echo "<script>console.log(" . json_encode($timetable) . ");</script>";
                if (!empty($timetable)) { ?>
                    <p class="timetable-week"><span style="color: var(--Nav-Bar-1); font-weight: 600;">Week: </span><?php
                    

                    // Get the Monday of current week
                    $monday = clone $today;
                    $monday->modify('monday this week');

                    // Get the Sunday of current week
                    $sunday = clone $today;
                    $sunday->modify('sunday this week');

                    // Format
                    $weekString = $monday->format('d-m-Y') . " (Mon) - " . $sunday->format('d-m-Y') . " (Sun)";
                    echo $weekString;
                    ?></p>

                    <button id="downloadTimeTablePDF">Download PDF</button>
                    
                    <div id="student-time-table" class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Module</th>
                                    <th>Room</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Instructor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timetable as $tt):
                                $classDate = new DateTime($tt['class_date']);
                                if ($classDate >= $monday && $classDate <= $sunday) {?>
                                    <input type="hidden" id="time-table-course-name" value="<?= $tt['course_name']; ?>">
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($tt['class_date']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($tt['module_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($tt['room']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($tt['start_time']).' - '. htmlspecialchars($tt['end_time']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($tt['duration']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($tt['instructor_name']); ?>
                                        </td>
                                    </tr>
                                <?php } endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                        <p>No Timetable.</p>
                    <?php } ?>
            </div>
        </main>
        </div>


    <script src="student_dashboard_script.js"></script>
</body>
</html>