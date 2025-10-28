<?php
require_once __DIR__ . "/../../Controls/userController.php";
require_once __DIR__ . "/../../Controls/studentController.php";
require_once __DIR__ . "/../../Controls/instructorController.php";
require_once __DIR__ . "/../../Controls/courseController.php";
require_once __DIR__ . "/../../Helpers/encryption.php";

$control = new UserController();
$stuCntrlr = new StudentController();
$instCntrlr = new InstructorController();
$courseCntrlr = new CourseController();

// Check if logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /SkillPro/Views/Login/login.php");
    exit;
}

// Allow only students
if ($_SESSION['role'] !== 'instructor') {
    // Redirect based on actual role
    header("Location: " . $control->redirectBasedOnRole());
    exit;
}

// Current date
$today = new DateTime();

$instEmail = $_SESSION['email'];
$instUserId = $_SESSION['user_id'];
$instDetails = $instCntrlr->getInstructorByUserId(intval($instUserId));
$instructorId = $instDetails['id'];
$instructorBranch = $instDetails['branch'];
$instructorName = $instDetails['full_name'];

// echo "<script>console.log(" . json_encode($instDetails) . ");</script>";


// logic for update instructor details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save-changes'])) {
    // Collect form data
    $data = [
        'instId'  => intval($instDetails['id']) ?? null,
        'userId'  => intval($instUserId) ?? null,
        'name'    => trim($_POST['full_name'] ?? ''),
        'gender'  => trim($_POST['gender'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'mobile'  => trim($_POST['mobile_number'] ?? ''),
        'bio'     => trim($_POST['bio'] ?? ''),
        'branch'  => trim($_POST['branch'] ?? ''),
        'spec'    => trim($_POST['specialization'] ?? '')
    ];

    $file = $_FILES['instructor_image'] ?? null;

    // Call update function in controller
    $result = $instCntrlr->updateInstructorDetails($data, $file);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}


// Logic for update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $changePasswordResult = $instCntrlr->changeInstructorPassword([
        "email" => $instEmail,
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
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - SkillPro Institute</title>
    <!-- Links -->
    <link rel="stylesheet" href="instructor_dashboard_style.css">
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
                    <img src="<?php if(!empty($instDetails['image_path'])) {
                            echo '/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($instDetails['image_path']);
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
                <li><button data-target="profile"><i class="fa-regular fa-user"></i><span>Profile</span></button></li>
                <li><button data-target="courses-and-batches"><i class="fa-solid fa-book"></i><span>Courses</span></button></li>
                <li><button data-target="notices"><i class="fa-regular fa-envelope"></i><span>Notices</span></button></li>
                <li><button data-target="events"><i class="fa-regular fa-calendar"></i><span>Events</span></button></li>
                <li><button data-target="time-table"><i class="fa-solid fa-table"></i><span>Schedule</span></button></li>
                <li><button data-target="view-batches"><i class="fa-solid fa-user-graduate"></i><span>View Batches</span></button></li>
                
            </ul>
        </aside>

        <!-- Main Section -->
        <main class="main">

            <!-- Instructor Profile -->
            <div class="profile">
                <form method="post" enctype="multipart/form-data" id="instructor-details-edit">
                    <!-- Profile Image -->
                    <div class="instructor-profile-picture-container">
                        <img src="<?php if(!empty($instDetails['image_path'])) {
                            echo '/SkillPro/Helpers/serveUserImage.php?file=' . PathEncryptor::encrypt($instDetails['image_path']); 
                        } else {
                            echo "/SkillPro/Images/user_image.jpg";
                        } ?>"
                        alt="instructor_profile_img">
                        <label>Instructor Profile Image</label>
                        <label class="custom-file-upload">
                            Upload Image
                            <input type="file" name="instructor_image" id="instructorImagefileInput">
                        </label>
                    </div>

                    <!-- name -->
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input readonly type="text" name="full_name" id="full_name"
                            value="<?php if(!empty($instDetails['full_name'])) {
                                    echo trim($instDetails['full_name']);
                                } else {
                                    echo "No Name";
                                } ?>">
                    </div>

                    <!-- email -->
                    <div class="form-group">
                        <label>Email</label>
                        <input readonly type="email" name="email"
                            value="<?php if(!empty($instDetails['email'])) {
                                    echo $instDetails['email'];
                                } else {
                                    echo "No Email";
                                } ?>">
                    </div>

                    <!-- gender -->
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="dropdown" data-input="gender">
                            <div class="dropdown-selected">
                                <span>Gender</span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Male">Male</li>
                                <li data-value="Female">Female</li>
                            </ul>
                        </div>
                        <!-- Hidden input (this is what PHP will read) -->
                        <input readonly type="text" id="gender" name="gender" value="<?php echo !empty($instDetails['gender']) ? $instDetails['gender'] : 'No Date Of Birth';?>">
                    </div>

                    <!-- address -->
                    <div class="form-group">
                        <label>Address</label>
                        <input readonly type="text" name="address" value="<?php echo !empty($instDetails['address']) ? $instDetails['address'] : 'No Date Of Birth';?>">
                    </div>

                    <!-- mobile number -->
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input readonly type="text" name="mobile_number" value="<?php echo !empty($instDetails['mobile_number']) ? $instDetails['mobile_number'] : 'No Date Of Birth';?>">
                    </div>

                    <!-- bio -->
                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" placeholder="Enter about you ..." readonly><?php echo !empty($instDetails['bio']) ? $instDetails['bio'] : 'No Bio Yet'; ?></textarea>
                    </div>

                    <!-- branch -->
                    <div class="form-group">
                        <label>Branch</label>
                        <div class="dropdown" data-input="branch">
                            <div class="dropdown-selected">
                                <span>Branch</span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Colombo">Colombo</li>
                                <li data-value="Kandy">Kandy</li>
                                <li data-value="Matara">Matara</li>
                            </ul>
                        </div>
                        <!-- Hidden input (this is what PHP will read) -->
                        <input readonly type="text" id="branch" name="branch" value="<?php echo !empty($instDetails['branch']) ? $instDetails['branch'] : 'No Branch Yet';?>">
                    </div>

                    <!-- specialization -->
                    <div class="form-group">
                        <label>specialization</label>
                        <input readonly type="text" name="specialization" value="<?php echo !empty($instDetails['specialization']) ? $instDetails['specialization'] : 'No Specialization Yet';?>">
                    </div>
                    
                    <button id="save-changes" name="save-changes" type="submit">Save Changes</button>
                    <button type="button" id="edit-details">Edit Details</button>
                </form>

                <form method="post" id="change-password-form">
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

            <!-- Courses & Bathces -->
            <div class="courses-and-batches">
                <h2>Courses & Batch</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Module Name</th>
                                <th>Batch Name</th>
                                <th>Branch</th>
                                <th>Module Sessions</th>
                                <th>Module Material</th>
                                <th>Course Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cbi = $courseCntrlr->getInstructorBatches($instructorId, "Active");
                            // echo "<script>console.log(" . json_encode($cbi) . ");</script>";
                            if (!empty($cbi)) {
                                foreach ($cbi as $c):?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['course_name']); ?></td>
                                        <td><?= htmlspecialchars($c['module_name']); ?></td>
                                        <td><?= htmlspecialchars($c['batch_name']); ?></td>
                                        <td><?= htmlspecialchars($c['branch']); ?></td>
                                        <td><?= htmlspecialchars($c['module_session']); ?></td>
                                        <td>
                                            <?php 
                                                $path = $c['material_path']; 
                                                if (!empty($path)) {
                                                    $encPath = PathEncryptor::encrypt($path); // now $secretKey exists
                                                    echo '<a style="color: var(--text-color);" target="_blank" 
                                                            href="/SkillPro/Helpers/serveUserImage.php?file=' . $encPath . '">
                                                            Material File
                                                        </a>';
                                                } else {
                                                    echo '<span style="color: gray;">No Material File</span>';
                                                }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($c['duration']) . ' ' .htmlspecialchars($c['duration_type']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } else {?>
                                <tr style="text-align: center;">
                                    <td colspan="7">Currentlty no courses and batches.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View Batches -->
            <div class="view-batches">
                <h2>Batches</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Batch Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Course Name</th>
                                <th>Total Students</th>
                                <th>View Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cbs = $courseCntrlr->getInstructorAllBatcheswithStudents($instructorId,"Active");
                            // echo "<script>console.log(" . json_encode($cbs) . ");</script>";
                            if (!empty($cbs)) { 
                                foreach ($cbs as $cb): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cb['batch_name']); ?></td>
                                        <td><?= htmlspecialchars($cb['batch_start_date']); ?></td>
                                        <td><?= htmlspecialchars($cb['batch_end_date']); ?></td>
                                        <td><?= htmlspecialchars($cb['course_name']); ?></td>
                                        <td><?= htmlspecialchars($cb['total_students']); ?></td>
                                        <td>
                                            <p style="text-decoration: underline; cursor: pointer;" 
                                                data-batchname="<?= htmlspecialchars($cb['batch_name']); ?>" 
                                                data-students='<?= htmlspecialchars(json_encode($cb['students']), ENT_QUOTES, "UTF-8"); ?>' 
                                                class="view-students">
                                                view students
                                            </p>
                                        </td>

                                    </tr>
                            <?php endforeach; } else {?>
                                <tr style="text-align: center;">
                                    <td colspan="6">No Batches available.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- View Students Panel -->
                <div id="view-students-panel" style="display:none;">
                    <div class="panel-header">
                        <h2>Students in Batch</h2>
                        <button id="close-students-panel">X</button>
                    </div>
                    <div class="panel-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                    </tr>
                                </thead>
                                <tbody id="students-list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Time Tables -->
            <div class="time-table">
                <h2>Schedule</h2>
                <?php
                $timetable = $courseCntrlr->getInstructorSchedule($instructorId);
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

                    <button id="downloadTimeTablePDF">Download Timetable PDF</button>

                    <div class="table-container" id="instructor-time-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Batch Name</th>
                                    <th>Module Name</th>
                                    <th>Course Name</th>
                                    <th>Start time</th>
                                    <th>End time</th>
                                    <th>Duration</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timetable as $tt):
                                $classDate = new DateTime($tt['class_date']);
                                if ($classDate >= $monday && $classDate <= $sunday) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tt['class_date']); ?></td>
                                        <td><?= htmlspecialchars($tt['batch_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['module_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['course_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['start_time']); ?></td>
                                        <td><?= htmlspecialchars($tt['end_time']); ?></td>
                                        <td><?= htmlspecialchars($tt['duration']); ?></td>
                                        <td><?= htmlspecialchars($tt['room']); ?></td>
                                    </tr>
                                    <?php } endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                        <p>No Timetable yet.</p>
                <?php } ?>
                <input type="hidden" id="instructor-tt-name" value="<?php echo $instructorName;?>">
            </div>

            <!-- Notices -->
            <div class="notices">
                <h2>Notices</h2>
                <?php
                $notices = $courseCntrlr->getAllNotices();
                // echo "<script>console.log(" . json_encode($notices) . ");</script>";
                if (!empty($notices) && (!empty($instructorBranch))) {
                    foreach ($notices as $n):
                        if ($today <= new DateTime($n['end_date']) && ($instructorBranch === $n['branch'] || $n['branch'] === "All") && $n['audience'] === 'All' || $n['audience'] === '') {?>
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
                <h2>Events & Updates</h2>
                <?php
                $events = $courseCntrlr->getAllEvents();
                // echo "<script>console.log(" . json_encode($events) . ");</script>";
                if (!empty($events) && !empty($instructorBranch)) {
                    foreach ($events as $e):
                        // Only show events for student's branch OR "All"
                        if (($instructorBranch === $e['branch'] || $e['branch'] === "All") 
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
        </main>
        </div>


    <script src="instructor_dashboard_script.js"></script>
</body>
</html>