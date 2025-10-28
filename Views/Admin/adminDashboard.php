<?php
require_once __DIR__ . "/../../Controls/userController.php";
require_once __DIR__ . "/../../Controls/courseController.php";
require_once __DIR__ . "/../../Controls/instructorController.php";
require_once __DIR__ . "/../../Controls/studentController.php";
require_once __DIR__ . "/../../Helpers/encryption.php";
require_once __DIR__ . "/../../Helpers/Mailer.php";


$ucontrol = new UserController();
$courseController = new CourseController();
$instructorController = new InstructorController();
$studentController = new StudentController();

// Mailer
$mailer = new Mailer();
// Example: send email to a student
// Local banner image
$bannerPath = __DIR__ . "/../../Images/banner.jpg"; // full path
$bannerCid = "institute_banner"; // unique Content-ID

$stats = $ucontrol->getStudentStats();
$provinceStats = $ucontrol->getProvinceStats();
$msg = '';

// =========================== logic for count instructors for dashboard graph
$allInstructorsWithBranch = $instructorController->getAllInstructors();
        // Group by branch
$branchStats = [];

foreach ($allInstructorsWithBranch as $inst) {
    $branch = $inst['branch'] ?? 'Unknown';
    if (!isset($branchStats[$branch])) {
        $branchStats[$branch] = 0;
    }
    $branchStats[$branch]++;
}

        // Prepare arrays for chart.js
$branchLabels = array_keys($branchStats);
$branchCounts = array_values($branchStats);

        // Calculate percentages
$totalInstructors = array_sum($branchCounts);
$branchPercentages = [];
foreach ($branchCounts as $count) {
    $branchPercentages[] = round(($count / $totalInstructors) * 100, 1);
}


// Check if logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /SkillPro/Views/Login/login.php");
    exit;
}

// Allow only admin
if ($_SESSION['role'] !== 'admin') {
    // Redirect based on actual role
    header("Location: " . $ucontrol->redirectBasedOnRole());
    exit;
}

// Session timeout (30 min)
// if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
//     session_unset();
//     session_destroy();
//     header("Location: /SkillPro/Views/Login/login.php?timeout=1");
//     exit;
// }

// logic for change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save-password'])) {
    $email = $_SESSION['email']; // from session
    $oldPassword = $_POST['old-password'];
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];

    $result = $ucontrol->changePassword($email, $oldPassword, $newPassword, $confirmPassword);

    if ($result['success']) {
        echo "<script>
            alert(" . json_encode($result['message']) . ");
            window.location.href = '/SkillPro/Views/Login/logout.php';
        </script>";
        exit; // prevent further execution
    } else {
        echo "<script>
            alert(" . json_encode($result['message']) . ");
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }

}

// logic for aprove students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_student'])) {
    $studentEmail = $_POST['approve_email'];
    $studentName = trim($_POST['approve_student']) ?? "";
    $result = $ucontrol->aproveStudentAccount($studentEmail);

    if ($result['success']) {
        

        $body = "
            <html>
                <body style='font-family: Arial, sans-serif; background-color:#f9f9f9; padding:20px;'>
                    <div style='max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>

                    <!-- Banner -->
                    <img src='cid:{$bannerCid}' alt='Institute Banner' style='width:100%; display:block;' />

                    <!-- Content -->
                    <div style='padding:20px;'>
                        <h2 style='color:#333;'>Hello {$studentName},</h2>
                        <p style='font-size:16px; color:#555; line-height:1.6;'>
                        We are excited to inform you that your <strong>student account has been approved</strong>! 🎉
                        </p>
                        <p style='font-size:16px; color:#555; line-height:1.6;'>
                        You can now log in to your account and start enrolling in any of our available courses.
                        </p>

                        <p style='margin:20px 0; text-align:center;'>
                        <a href='http://localhost/SkillPro/Views/Login/login.php' 
                            style='background:#007BFF; color:#fff; padding:12px 24px; text-decoration:none; border-radius:5px; font-size:16px;'>
                            Enroll in Courses
                        </a>
                        </p>

                        <p style='font-size:14px; color:#777;'>
                        If you have any questions, feel free to reply to this email or contact our support team.
                        </p>
                    </div>

                    <!-- Footer -->
                    <div style='background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#666;'>
                        &copy; " . date('Y') . " SkillPro Institute. All rights reserved.
                    </div>

                    </div>
                </body>
                </html>
        ";
        // Embed the banner
        $mailer->addEmbeddedImage($bannerPath, $bannerCid);

        $mailer->sendMail(
            trim($studentEmail),    // to email
            trim($studentName), // to name
            "Your Student Account Has Been Approved – Start Enrolling Today!!",  // subject
            $body// HTML body
        );
    }

    // Use session alert or JS alert
    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for add courses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $data = [
        'name'          => $_POST['name'] ?? '',
        'category'      => $_POST['course_category'] ?? '',
        'new_category'  => $_POST['new_category'] ?? '',
        'duration'      => $_POST['duration'] ?? '',
        'duration_type' => $_POST['duration_type'] ?? '',
        'about'         => $_POST['about'] ?? '',
        'branches'      => $_POST['branches'] ?? [],
        'image'         => $_FILES['course_img'] ?? null,
        'fee'           => $_POST['fee'] ?? ''
    ];

    $result = $courseController->addCourse($data);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    $courseId = $_POST['course_id'];
    $deleteResult = $courseController->deleteCourse($courseId);

    if ($deleteResult) {
        echo "<script>
            alert('Course deleted successfully!');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
            </script>";
        exit;
    } else {
        echo "<script>
            alert('Failed to delete course!');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
            </script>";
        exit;
    }
}

// logic for update course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {

    $category = $_POST['edit_course_category'] ?? '';
    $newCategory = trim($_POST['new_category'] ?? '');

    // Use new category if provided
    if (!empty($newCategory)) {
        $category = $newCategory;
    }

    $data = [
        'id'            => $_POST['course_id'],
        'name'          => $_POST['edit_name'],
        'category'      => $category,  // <-- updated
        'new_category'  => $newCategory,
        'duration'      => $_POST['edit_duration'],
        'duration_type' => $_POST['edit_duration_type'],
        'fee'           => $_POST['fee'],
        'branches'      => $_POST['edit_branches'] ?? [],
        'about'         => $_POST['about'],
        'image'         => $_FILES['course_img'] ?? null
    ];

    $result = $courseController->updateCourse($data);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

//  logic for add instructor 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_instructor'])) {
    $data = [
        'name' => $_POST['instructor_name'],
        'mobile' => $_POST['instructor_mobile_number'],
        'bio' => $_POST['instructor_bio'],
        'specialization' => $_POST['instructor_specialization'],
        'address' => $_POST['instructor_address'],
        'branch' => $_POST['instructor_branch'],
        'email' => $_POST['instructor_email'],
        'password' => $_POST['instructor_password'],
        'gender'   => $_POST['instructor_gender'],
    ];

    $instructorName = trim($data['name']) ?? "";
    $instructorEmail = $data['email'] ?? "";
    $instructorPassword = trim($data['password']) ?? "";

    $file = $_FILES['instructor_image_path'];
    $result = $instructorController->addInstructor($data, $file);

    if ($result['success']) {
        $body = "
            <html>
                <body style='font-family: Arial, sans-serif; background-color:#f9f9f9; padding:20px;'>
                    <div style='max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>

                    <!-- Banner -->
                    <img src='cid:{$bannerCid}' alt='Institute Banner' style='width:100%; display:block;' />

                    <p>Dear {$instructorName},</p>

                    <p>We are pleased to inform you that your <b>Instructor Account</b> has been successfully created on the <b>SkillPro Institute</b> portal.</p>

                    <p><b>Login Credentials:</b></p>
                    <ul>
                    <li>Email: <b>{$instructorEmail}</b></li>
                    <li>Temporary Password: <b>{$instructorPassword}</b></li>
                    </ul>

                    <p>For security reasons, please log in using the above credentials and <b>change your password immediately</b>.</p>

                    <p>You can log in here: <a href='http://localhost/SkillPro/Views/Login/login.php'>Instructor Portal</a></p>

                    <p>If you experience any issues, please contact the administration team at <a href='skillproinstitute9@gmail.com'>skillproinstitute9@gmail.com</a>.</p>

                    <br>
                    <p>Best regards,<br>
                    SkillPro Institute Admin Team</p>

                    <!-- Footer -->
                    <div style='background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#666;'>
                        &copy; " . date('Y') . " SkillPro Institute. All rights reserved.
                    </div>

                    </div>
                </body>
                </html>
        ";
        // Embed the banner
        $mailer->addEmbeddedImage($bannerPath, $bannerCid);

        $mailer->sendMail(
            trim($instructorEmail),    // to email
            trim($instructorName), // to name
            "Welcome to SkillPro Institute – Your Instructor Account Has Been Created",  // subject
            $body// HTML body
        );
    }

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete instructor 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_instructor'])) {
    $instructorUserId = $_POST['instructor_user_id'];
    $deleteResult = $instructorController->deleteInstructor($instructorUserId);

    if ($deleteResult["success"]) {
        echo "<script>
            alert(" . json_encode($deleteResult['message']) . ");
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
            </script>";
        exit;
    } else {
        echo "<script>
            alert(" . json_encode($deleteResult['message']) . ");
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
            </script>";
        exit;
    }
}

// logic for update instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_instructor'])) {

    $insImage = null;
    // If new image uploaded
    if (empty($_FILES['edit_instructor_image']['name'])) {
        $insImage = null;
    } else {
        $insImage = $_FILES['edit_instructor_image'];
    }

    $data = [
        'instId' => $_POST['instructor_table_id'],
        'userId' => $_POST['instructor_user_id'],
        'name'   => $_POST['edit_instructor_name'],
        'mobile' => $_POST['edit_instructor_mobile'],
        'bio'    => $_POST['edit_instructor_bio'],
        'spec'   => $_POST['edit_instructor_specialization'],
        'address'=> $_POST['edit_instructor_address'],
        'gender' => $_POST['edit_instructor_gender'],
        'branch' => $_POST['edit_instructor_branch'],
    ];

    $result = $instructorController->updateInstructorDetails($data, $insImage);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for add course module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add-module'])) {
    $data = [
        'name' => $_POST['add_course_module_name'],
        'course_id' => $_POST['module_course_id'],
        'tot_sessions' => $_POST['add_course_module_duration'],
    ];

    $file = $_FILES['add_course_materials'];
    $result = $courseController->addCourseModule($data, $file);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for assign instructor to course module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_module_instructor'])) {
    $courseId = $_POST['assign_module_course_id'];
    $branch = $_POST['assign_course_module_branch'];
    $moduleId = $_POST['assign_module_course_moduleId'];
    $batchId = $_POST['assign_module_course_batchId'];
    $instructorId = $_POST['assign_module_course_instructorId'];

    $data = [
        'module_id' => (int) $moduleId,
        'batch_id' => (int) $batchId,
        'instructor_id' => (int) $instructorId,
        'branch' => trim($branch),
    ];

    $result = $courseController->addDetailsToCourseModuleInstructor($data);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for create batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_batch_btn'])) {
    $data = [
        'courseId'   => trim($_POST['create_batches_course_id'] ?? ''),
        'branch'     => trim($_POST['create_batches_course_branch'] ?? ''),
        'batchName'  => trim($_POST['create_batch_name'] ?? ''),
        'startDate'  => trim($_POST['create_batch_start_date'] ?? ''),
        'endDate'    => trim($_POST['add_batch_end_date'] ?? ''),
    ];

    $result = $courseController->createBatch($data);

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for approve student enrollments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_registration_approve'])) {
    $data= ['id' => intval($_POST['student_registration_id']) ?? 0,
            'status'     => 'Approved',
    ];

    $result = $courseController->changeStudentRegistration($data['id'], $data['status']);

    echo "<script>
        alert(" . json_encode($result['message2']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;

}

// logic for reject student enrollments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_registration_reject'])) {
    $data= ['student_id' => intval($_POST['student_registration_id']) ?? 0,
            'status'     => 'Rejected',
    ];

    $result = $courseController->changeStudentRegistration($data['student_id'], $data['status']);

    if ($result['success']) {
        $studentEmail = trim($_POST['student_registration_email']) ?? "";
        $studentName = trim($_POST['student_registration_name']) ?? "";
        $body = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color:#f9f9f9; padding:20px;'>
                <div style='max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>

                <!-- Banner -->
                <img src='cid:{$bannerCid}' alt='Institute Banner' style='width:100%; display:block;' />

                <!-- Content -->
                <div style='padding:20px;'>
                    <h2 style='color:#333;'>Dear {$studentName},</h2>
                    <p style='font-size:16px; color:#555; line-height:1.6;'>
                    Thank you for applying to enroll in our course. After reviewing your application, 
                    we regret to inform you that your enrollment request has not been approved at this time.
                    </p>
                    <p style='font-size:16px; color:#555; line-height:1.6;'>
                    Please note that this decision may be due to course prerequisites, limited seats, 
                    or other eligibility requirements. You are welcome to explore and apply for our other 
                    available courses that may better suit your qualifications and interests.
                    </p>

                    <p style='margin:20px 0; text-align:center;'>
                    <a href='http://localhost/SkillPro/Views/Course/course.php' 
                        style='background:#007BFF; color:#fff; padding:12px 24px; text-decoration:none; border-radius:5px; font-size:16px;'>
                        Browse Other Courses
                    </a>
                    </p>

                    <p style='font-size:14px; color:#777;'>
                    If you have any questions or would like further clarification, 
                    please don’t hesitate to contact our support team.
                    </p>
                </div>

                <!-- Footer -->
                <div style='background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#666;'>
                    &copy; " . date('Y') . " SkillPro Institute. All rights reserved.
                </div>

                </div>
            </body>
            </html>
            ";
        // Embed the banner
        $mailer->addEmbeddedImage($bannerPath, $bannerCid);

        $mailer->sendMail(
            trim($studentEmail),    // to email
            trim($studentName), // to name
            "Update on Your Course Enrollment Application",  // subject
            $body// HTML body
        );
    }

    echo "<script>
        alert(" . json_encode($result['message3']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;

}

// logic for add student to batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_to_batch_btn'])) { 
    // get student details
    $studentDtls = $studentController->getStudentDetails($_POST['add_student_to_batch_student_email']);
    
    // validate student
    if (!$studentDtls['success']) {
        echo "<script>
            alert('Invalid Student');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    } else {
        $studentName = trim($studentDtls['data']['full_name']) ?? "";
    }

    $data = [
        'student_id' => $studentDtls['data']['id'],
        'batch_id'   => intval($_POST['add_student_to_batch_course_batch_id']) ?? 0,
    ];

    $result = $courseController->addStudentToBatch($data);

    if ($result['success']) {
        $studentEmail = trim($_POST['add_student_to_batch_student_email']) ?? "";
        $body = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color:#f9f9f9; padding:20px;'>
                <div style='max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>

                <!-- Banner -->
                <img src='cid:{$bannerCid}' alt='Institute Banner' style='width:100%; display:block;' />

                <!-- Content -->
                <div style='padding:20px;'>
                    <h2 style='color:#333;'>Dear {$studentName},</h2>
                    <p style='font-size:16px; color:#555; line-height:1.6;'>
                    Thank you for submitting your enrollment request at <strong>SkillPro Institute</strong>.
                    </p>

                    <p style='font-size:16px; color:#555; line-height:1.6;'>
                    Your enrollment request has been <strong>successfully submitted</strong> and is currently <strong>pending approval</strong>.
                    </p>

                    <p style='font-size:16px; color:#555; line-height:1.6;'>
                    Our administration team will carefully review your application and assign you to the appropriate batch. 
                    Once approved, you will receive a confirmation email with your course and batch details, along with further instructions.
                    </p>

                    <p style='font-size:16px; color:#555; line-height:1.6;'>
                    In the meantime, you can log in to your <a href='http://localhost/SkillPro/Views/Login/login.php' style='color:#007bff; text-decoration:none;'>Student Dashboard</a> 
                    to review your profile and stay updated.
                    </p>
                </div>

                <!-- Footer -->
                <div style='background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#666;'>
                    &copy; " . date('Y') . " SkillPro Institute. All rights reserved.
                </div>

                </div>
            </body>
            </html>
            ";
        // Embed the banner
        $mailer->addEmbeddedImage($bannerPath, $bannerCid);

        $mailer->sendMail(
            trim($studentEmail),    // to email
            trim($studentName), // to name
            "Your Enrollment Request is Pending Approval",  // subject
            $body// HTML body
        );
    }

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for update batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit_batch'])) {
    $data = [
        'batch_id' => intval($_POST['save_edit_batch']) ?? 0,
        'batch_name' => trim($_POST['edit_batch_name']) ?? "",
        'batch_status' => trim($_POST['edit_batch_status']) ?? "",
    ];

    $result = $courseController->updateBatch($data);

    if ($result['success']) {
        if ($result['mailerBatchStatus'] == 'Active') {
            $batchResult = $courseController->getStudentsByBatchId(intval($_POST['save_edit_batch']) ?? 0);
            if (!empty($batchResult)) {
                foreach ($batchResult as $bts):
                    $studentName = $bts['full_name'] ?? "";
                    $studentEmail = $bts['email'] ?? "";
                    $body = "
                        <html>
                        <body style='font-family: Arial, sans-serif; background-color:#f9f9f9; padding:20px;'>
                            <div style='max-width:600px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.1);'>

                            <!-- Banner -->
                            <img src='cid:{$bannerCid}' alt='Institute Banner' style='width:100%; display:block;' />

                            <!-- Content -->
                            <div style='padding:20px;'>
                                <h2 style='color:#333;'>Dear {$studentName},</h2>
                                <p style='font-size:16px; color:#555; line-height:1.6;'>
                                We are delighted to inform you that your <strong>course enrollment has been approved</strong>.
                                </p>

                                <p style='font-size:16px; color:#555; line-height:1.6;'>
                                You can now access your Student Dashboard to view course materials, schedules, and updates from your instructors.
                                </p>

                                <p style='font-size:16px; color:#555; line-height:1.6;'>
                                Please ensure you check your dashboard regularly and stay prepared for upcoming sessions.
                                </p>
                                
                                <p style='font-size:16px; color:#555; line-height:1.6;'>
                                In the meantime, you can log in to your <a href='http://localhost/SkillPro/Views/Login/login.php' style='color:#007bff; text-decoration:none;'>Student Dashboard</a> 
                                to review your profile and stay updated.
                                </p>
                            </div>

                            <!-- Footer -->
                            <div style='background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#666;'>
                                &copy; " . date('Y') . " SkillPro Institute. All rights reserved.
                            </div>

                            </div>
                        </body>
                        </html>
                        ";
                    // Embed the banner
                    $mailer->addEmbeddedImage($bannerPath, $bannerCid);

                    $mailer->sendMail(
                        trim($studentEmail),    // to email
                        trim($studentName), // to name
                        "Your Course Enrollment Has Been Approved",  // subject
                        $body// HTML body
                    );
                endforeach;
            }
        }
    }

    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_batch_btn'])) {
    $result = $courseController->deletebatch($_POST['delete_batch_btn']);
    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete student account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_btn'])) {
    $result = $studentController->deleteStudentByUserID($_POST['delete_student_btn']);
    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete course module 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course_module'])) {
    $result = $courseController->deleteCourseModuleById(intval($_POST['delete_course_module']));
    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for chnage student queries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solve_non_student_query_btn'])) {
    $result = $studentController->changeNonStudentQueryStatus(
        intval($_POST['solve_non_student_query_btn']),
        "Solved"    
    );
    echo "<script>
    alert(" . json_encode($result['message']) . ");
    window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for solved student queries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solve_student_query_btn'])) {
    $result = $studentController->solvedStudentInquiry(intval($_POST['solve_student_query_btn']));
    echo "<script>
    alert(" . json_encode($result['message']) . ");
    window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for add schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule_save_btn'])) {
    $data =[
        'batch_id' => intval($_POST['new_schedule_course_batch'] ?? 0),
        'module_id'=> intval($_POST['new_schedule_course_module_id'] ?? 0),
        'instructor_id' => intval($_POST['new_schedule_course_module_instructor_id'] ?? 0),
        'branch' => trim($_POST['new_schedule_course_branch']),
        'date' => $_POST['new_schedule_course_class_date'],
        'start_time' => $_POST['new_schedule_course_class_start_time'],
        'end_time' => $_POST['new_schedule_course_class_end_time'],
        'location' => $_POST['new_schedule_course_class_location'],
    ];

    $result = $courseController->addSchedule($data);

    echo "<script>
    alert(" . json_encode($result['message']) . ");
    window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete shedule 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_schedule'])) {
    $result = $courseController->deleteSchedule(intval($_POST['delete_schedule']));
    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for add notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice_btn'])) {
    $data =[
        'title' => trim($_POST['add_notice_title']) ?? "",
        'content'=> trim($_POST['add_notice_content']) ?? "",
        'audience' => trim($_POST['add_notice_audience']) ?? "",
        'branch' => trim($_POST['add_notice_branch']) ?? "",
        'start_date' => trim($_POST['add_notice_start_date']) ?? "",
        'end_date' => trim($_POST['add_notice_end_date']) ?? "",
    ];

    $result = $courseController->addNotice($data);

    echo "<script>
    alert(" . json_encode($result['message']) . ");
    window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete event 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $result = $courseController->deleteEvent(intval($_POST['delete_event']));
    echo "<script>
        alert(" . json_encode($result['message']) . ");
        window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for add event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event_btn'])) {
    $data =[
        'title' => trim($_POST['add_event_title']) ?? "",
        'description'=> trim($_POST['add_event_description']) ?? "",
        'branch' => trim($_POST['add_event_branch']) ?? "",
        'start_date_time' => trim($_POST['add_event_start_date_time']) ?? "",
        'end_date_time' => trim($_POST['add_event_end_date_time']) ?? "",
        'image_path' => $_FILES['add_event_image'],
    ];

    $result = $courseController->addEvent($data);

    echo "<script>
    alert(" . json_encode($result['message']) . ");
    window.location.href='" . $_SERVER['PHP_SELF'] . "';
    </script>";
    exit;
}

// logic for delete notice 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice'])) {
    $result = $courseController->deleteNotice(intval($_POST['delete_notice']));
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
    <title>Admin - SkillPro Institute</title>
    <!-- Links -->
    <link rel="stylesheet" href="admin-dasshboard-style.css">
    <link rel="icon" href="/Skillpro/Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Header -->
        <header class="header">
            <button class="menu-toggle" id="menu-toggle"><i class="fa-solid fa-bars"></i></button>
            <h1>SkillPro</h1>
            <nav class="header-nav">
                <i class="fa-solid fa-moon" id="theme-icon"></i>
                <div class="profile-mini">
                    <img src="/SkillPro/Images/user_image.jpg" alt="user_img">
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
                <li><button data-target="dashboard"><span>Dashboard</span><i class="fa-solid fa-grip"></i></button></li>
                <li><button data-target="profile"><span>Profile</span><i class="fa-solid fa-user"></i></button></li>

                <!-- Students with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Students</span><i class="fa-solid fa-graduation-cap"></i></button>
                    <ul class="sub">
                        <li><button data-target="approve-students">Approve Students</button></li>
                        <li><button data-target="manage-students">Manage Students</button></li>
                        <li><button data-target="student-enrollments">Students Entrollments</button></li>
                    </ul>
                </li>

                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Student Batches</span><i class="fa-solid fa-people-group"></i></button>
                    <ul class="sub">
                        <li><button data-target="create-batches">Create Batches</button></li>
                        <li><button data-target="add-student-to-batch">Add Student to Batch</button></li>
                        <li><button data-target="manage-batches">Manage Batches</button></li>
                    </ul>
                </li>

                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Courses</span><i class="fa-solid fa-book"></i></button>
                    <ul class="sub">
                        <li><button data-target="add-course">Add Course</button></li>
                        <li><button data-target="manage-courses">Manage Course</button></li>
                    </ul>
                </li>
                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Course Modules</span><i class="fa-solid fa-book-open"></i></button>
                    <ul class="sub">
                        <li><button data-target="add-modules">Add Modules</button></li>
                        <li><button data-target="assign-instructor-to-modules">Assign Instructors to Modules</button></li>
                        <li><button data-target="manage-modules">Manage Modules</button></li>
                    </ul>
                </li>
                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Instructors</span><i class="fa-solid fa-person-chalkboard"></i></button>
                    <ul class="sub">
                        <li><button data-target="add-instructor">Add Instructor</button></li>
                        <li><button data-target="manage-instructor">Manage Instructor</button></li>
                    </ul>
                </li>
                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Timetable</span><i class="fa-solid fa-clock"></i></button>
                    <ul class="sub">
                        <li><button data-target="add-new-schedule">Add New Schedule</button></li>
                        <li><button data-target="manage-timetable">Manage Timetable</button></li>
                    </ul>
                </li>

                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Queries</span><i class="fa-solid fa-clipboard-question"></i></button>
                    <ul class="sub">
                        <li><button data-target="students-queries">Student Queries</button></li>
                        <li><button data-target="non-students-queries">Non Student Queries</button></li>
                    </ul>
                </li>

                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Notices</span><i class="fa-solid fa-envelope"></i></button>
                    <ul class="sub">
                        <li><button data-target="add-notice">Add Notice</button></li>
                        <li><button data-target="manage-notices">Manage Notices</button></li>
                    </ul>
                </li>

                <!-- Queries with sub menu -->
                <li class="has-submenu">
                    <button class="submenu-toggle"><span>Events</span><i class="fa-solid fa-calendar-days"></i></button>
                    <ul class="sub">
                        <li><button data-target="add-event">Add Event</button></li>
                        <li><button data-target="manage-events">Manage Events</button></li>
                    </ul>
                </li>
            </ul>
        </aside>




        <!-- Main Section -->
        <main class="main">

            <!-- Dashboard -->
            <div class="dashboard">
                <!-- province student card -->
                <div class="dash-row">
                    <div class="card" id="province-chart-card">
                        <p>Students by Province</p>
                        <canvas id="provinceChart"></canvas>
                    </div>

                    <!-- instrcuctor branch card -->
                    <div class="card" id="instructor-chart-card">
                        <p>Instructors by Branch</p>
                        <canvas id="instructorBranchChart"></canvas>
                    </div>
                </div>

                <div class="dash-row">
                    <!-- Student Total Card -->
                    <div class="card" id="student-card">
                        <p>Total Students</p>
                        <strong><?= $stats['total'] ?></strong>
                        <p>Account Active: <span><?= $stats['active'] ?></span></p>
                    </div>

                    <!-- Instructor total card -->
                    <div class="card" id="instructor-card">
                        <p>Total Instructors</p>
                        <strong><?= htmlspecialchars($instructorController->getTotalInstructors()); ?></strong>
                    </div>
                    
                    <!-- course total card -->
                    <div class="card" id="course-card">
                        <p>Total courses</p>
                        <strong><?= htmlspecialchars($courseController->getTotalCourses()); ?></strong>
                    </div>

                </div>

                <!-- Include Chart.js -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Data from PHP
                    const provinceLabels = <?= json_encode(array_column($provinceStats, 'province')); ?>;
                    const provinceCounts = <?= json_encode(array_column($provinceStats, 'total')); ?>;
                    const provincePercentages = <?= json_encode(array_column($provinceStats, 'percentage')); ?>;

                    const ctx = document.getElementById('provinceChart').getContext('2d');

                    new Chart(ctx, {
                        type: 'bar', // or 'pie' if you prefer
                        data: {
                            labels: provinceLabels,
                            datasets: [{
                                label: 'Number of Students',
                                data: provinceCounts,
                                backgroundColor: [
                                    '#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0', '#00BCD4'
                                ],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false,
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            const count = context.raw;
                                            const percent = provincePercentages[index];
                                            return `${context.label}: ${count} students (${percent}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                });
                
                document.addEventListener("DOMContentLoaded", function() {
                    const branchLabels = <?= json_encode($branchLabels); ?>;
                    const branchCounts = <?= json_encode($branchCounts); ?>;
                    const branchPercentages = <?= json_encode($branchPercentages); ?>;

                    const ctx = document.getElementById('instructorBranchChart').getContext('2d');

                    new Chart(ctx, {
                        type: 'bar', // try 'pie' also if you want
                        data: {
                            labels: branchLabels,
                            datasets: [{
                                label: 'Number of Instructors',
                                data: branchCounts,
                                backgroundColor: [
                                    '#4CAF50', '#2196F3', '#FFC107',
                                    '#FF5722', '#9C27B0', '#00BCD4'
                                ],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            const count = context.raw;
                                            const percent = branchPercentages[index];
                                            return `${context.label}: ${count} instructors (${percent}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });
                });
                </script>
            </div>

            <!-- Profile -->
            <div class="profile">
                <h2>Admin Profile</h2>
                <!-- Email Display -->
                <div class="profile-item">
                    <label>Email:</label>
                    <p id="admin-email"><?= htmlspecialchars($_SESSION['email']); ?></p>
                </div>

                <!-- Change Password Form -->
                <form id="change-password-form" method="post" autocomplete="off">
                
                    <!-- Old Password -->
                    <div class="profile-item">
                        <label for="old-password">Old Password</label>
                        <input type="password" id="old-password" name="old-password" placeholder="Enter old password" required>
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="old-password"></i>
                    </div>

                    <!-- New Password -->
                    <div class="profile-item">
                        <label for="new-password">New Password</label>
                        <input type="password" id="new-password" name="new-password" placeholder="Enter new password" required>
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="new-password"></i>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="profile-item">
                        <label for="confirm-password">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm new password" required>
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="confirm-password"></i>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div id="strength-indicator"></div>

                    <button type="submit" value="1" name="save-password" id="save-password">Save Changes</button>
                    <button type="button" onclick="window.location.href='/SkillPro/Views/Login/logout.php'" id="logout">Logout</button>
                </form>
            </div>

            <!-- Approve Students -->
            <div class="approve-students">
                <h2>Pending Student Approvals</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>NIC</th>
                                <th>DOB</th>
                                <th>Gender</th>
                                <th>Street Address</th>
                                <th>Province</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch pending students
                            $pendingStudents = $ucontrol->getPendingStudents(); // function in user controller
                            if (empty($pendingStudents)) { ?>
                                <tr>
                                    <td style="text-align: center;" colspan="9">No pending students to approve.</td>
                                </tr>
                            <?php } else { 
                                foreach($pendingStudents as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td><?= htmlspecialchars($student['nic_number']) ?></td>
                                    <td><?= htmlspecialchars($student['dob']) ?></td>
                                    <td><?= htmlspecialchars($student['gender']) ?></td>
                                    <td><?= htmlspecialchars($student['street_address']) ?></td>
                                    <td><?= htmlspecialchars($student['province']) ?></td>
                                    <td><?= htmlspecialchars($student['mobile_number']) ?></td>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="approve_email" value="<?= $student['email'] ?>">
                                            <button type="submit" name="approve_student" value="<?= $student['full_name'] ?>">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; }?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Student Registrations -->
            <div class="student-enrollments">
                <h2>Student Enrollments</h2>
                <div class="filters">
                    <!-- Search Student Name -->
                    <input type="text" name="enroll_student_name_nic_email" id="enroll-student-name-nic-email" placeholder="Search Student Name/NIC/Email">

                    <!-- Select Course -->
                    <div class="dropdown" data-input="student-enrolment-filter-course">
                        <div class="dropdown-selected">
                            <span>Select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <?php
                            $coursesList = $courseController->getAllCourse();
                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['name']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="student-enrolment-filter-course" name="student_enrolment_filter_course" value="">

                    <!-- Select Branch -->
                    <div class="dropdown" data-input="student-enrolment-filter-branch">
                        <div class="dropdown-selected">
                            <span>Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="student-enrolment-filter-branch" name="student_enrolment_filter_branch" value="">

                    <!-- Select Status -->
                    <div class="dropdown" data-input="student-enrolment-filter-status">
                        <div class="dropdown-selected">
                            <span>Status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <li data-value="Pending">Pending</li>
                            <li data-value="Approved">Approved</li>
                            <li data-value="Rejected">Rejected</li>
                            <li data-value="Cancelled">Cancelled</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="student-enrolment-filter-status" name="student_enrolment_filter_status" value="">
                </div>

                <div class="table-container">
                    <table class="student-enrollment-table" id="student-enrollment-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student Email</th>
                                <th>Student NIC</th>
                                <th>Course</th>
                                <th>Branch</th>
                                <th>Registered At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="student-enrollment-list">
                        <?php
                        $studentRegistrationDetails = $studentController->getStudentCourseRegistrationDetails();
                        if (!empty($studentRegistrationDetails)) {
                            foreach ($studentRegistrationDetails as $srDetail):
                        ?>
                                <tr data-studentRegistrationId="<?= $srDetail['id'] ?>"
                                data-studentRegistrationStatus="<?= $srDetail['status'] ?>">
                                    <td>
                                        <?= htmlspecialchars($srDetail['student_name']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($srDetail['student_email']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($srDetail['student_nic']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($srDetail['course_name']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($srDetail['branch']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($srDetail['registered_at']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = htmlspecialchars($srDetail['status']);
                                            $color = ($status == "Pending") ? "orange" : (($status == "Approved") ? "green" : (($status == "Rejected") ? "red" : "gray"));
                                        ?>
                                        <span style="padding: 0.3rem 0.5rem; border-radius: 30px; color: white; background-color: <?= $color ?>;">
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(htmlspecialchars($srDetail['status']) == "Pending") { ?>
                                            <form method="post">
                                                <input type="hidden" name="student_registration_id" value="<?= $srDetail['id'] ?>">
                                                <input type="hidden" name="student_registration_email" value="<?= $srDetail['student_email'] ?>">
                                                <input type="hidden" name="student_registration_name" value="<?= $srDetail['student_name'] ?>">
                                                
                                                <!-- Approve Button -->
                                                <button name="student_registration_approve" type="submit"
                                                    onclick="return confirm('Are you sure you want to approve this registration?')"
                                                    style="color: white; background-color: green; padding: 0.3rem; border-radius: 50%; border: none; outline: none; cursor: pointer; transition: transform 0.2s ease;"
                                                    onmouseover="this.style.transform = 'translateY(-3px)';"
                                                    onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                </button>
        
                                                <!-- Reject Button -->
                                                <button name="student_registration_reject" type="submit"
                                                    onclick="return confirm('Are you sure you want to reject this registration?')"
                                                    style="color: white; background-color: red; padding: 0.3rem; border-radius: 50%; border: none; outline: none; cursor: pointer; transition: transform 0.2s ease;"
                                                    onmouseover="this.style.transform = 'translateY(-3px)';"
                                                    onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa-solid fa-circle-xmark"></i>
                                                </button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Empty Student Registration</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Manage Students -->
            <div class="manage-students">
                <h2>Manage Students</h2>
                <div class="filters">
                    <!-- Search name/nic/email/mobile -->
                    <input type="text" id="manage-students-search-input" placeholder="Search Student Name/NIC/Email/Mobile">

                    <!-- Gender -->
                    <div class="dropdown" data-input="manage-stduents-gender">
                        <div class="dropdown-selected">
                            <span>Select Gender</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <li data-value="all">Male</li>
                            <li data-value="all">Female</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-stduents-gender" value="">

                    <!-- Province -->
                    <div class="dropdown" data-input="manage-stduents-province">
                        <div class="dropdown-selected">
                            <span>Select Province</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <li data-value="Western">Western</li>
                            <li data-value="Central">Central</li>
                            <li data-value="Southern">Southern</li>
                            <li data-value="North Western">North Western</li>
                            <li data-value="Sabragamuwa">Sabragamuwa</li>
                            <li data-value="Eastern">Eastern</li>
                            <li data-value="Uva">Uva</li>
                            <li data-value="North Central">North Central</li>
                            <li data-value="Northern">Northern</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-stduents-province" value="">
                </div>

                <div class="table-container">
                    <table class="manage-students-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>NIC</th>
                                <th>DOB</th>
                                <th>Gender</th>
                                <th>Street Address</th>
                                <th>Province</th>
                                <th>Mobile</th>
                                <th>Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="manage-students-list">
                            <?php
                            $students = $studentController->getAllStudents();
                            if (!empty($students)) {
                                foreach ($students as $student):
                            ?>
                                    <tr data-userid="<?= $student['user_id']; ?>" data-studentid="<?= $student['id']; ?>">
                                        <td><?= htmlspecialchars($student['full_name']); ?></td>
                                        <td><?= htmlspecialchars($student['email']); ?></td>
                                        <td><?= htmlspecialchars($student['nic_number']); ?></td>
                                        <td><?= htmlspecialchars($student['dob']); ?></td>
                                        <td><?= htmlspecialchars($student['gender']); ?></td>
                                        <td><?= htmlspecialchars($student['street_address']); ?></td>
                                        <td><?= htmlspecialchars($student['province']); ?></td>
                                        <td><?= htmlspecialchars($student['mobile_number']); ?></td>
                                        <td>
                                            <?php
                                            $studentImagePath = $student['image_path'];
                                            if (!empty($studentImagePath)) {
                                                $encPath = PathEncryptor::encrypt($studentImagePath);
                                                echo '<a style="color: var(--text-color);" target="_blank" 
                                                        href="/SkillPro/Helpers/serveUserImage.php?file=' . $encPath . '">
                                                        Student Image
                                                    </a>';
                                            } else {
                                                echo '<span style="color: gray;">No Image</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <!-- delete button -->
                                            <form method="post" class="delete-student-btn-form" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                <button 
                                                name="delete_student_btn"
                                                value="<?= intval($student['user_id']); ?>" 
                                                class="delete-student-btn" 
                                                type="submit"
                                                style="color: white; 
                                                background-color: red;
                                                padding: 0.3rem;
                                                border-radius: 50%;
                                                border: none;
                                                outline: none;
                                                cursor: pointer;
                                                transition: transform 0.2s ease;"
                                                onmouseover="this.style.transform = 'translateY(-3px)';"
                                                onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">Empty Students</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
            </div>

            <!-- Add Batches -->
            <div class="create-batches">
                <h2>Create Batch</h2>
                <!-- Create Batch Form -->
                <form method="post" id="create-batch-form">
                    <!-- Select Course -->
                    <label>Select Course</label>
                    <div class="dropdown" data-input="create-batches-course-id">
                        <div class="dropdown-selected">
                            <span>select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                            $coursesList = $courseController->getAllCourse();
    
                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="create-batches-course-id" name="create_batches_course_id" value="">
    
                    <!-- Select Branch -->
                    <label>Select Branch</label>
                    <div class="dropdown" data-input="create-batches-course-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="create-batches-course-branch" name="create_batches_course_branch" value="">
    
                    <!-- Batch Name -->
                    <label>Batch Name</label>
                    <input type="text" placeholder="Enter Batch Name" name="create_batch_name" id="create-batch-name" required>
    
                    <!-- Course Start Date -->
                    <label>Start Date</label>
                    <input type="date" id="create-batch-start-date" name="create_batch_start_date" required>
    
                    <!-- Course End Date -->
                    <label>End Date</label>
                    <input type="date" id="add-batch-end-date" name="add_batch_end_date" readonly required>
    
                    <!-- Create Batch Button -->
                    <button type="submit" id="create-batch-btn" name="create_batch_btn">Create Batch</button>
                </form>

                
                
            </div>

            <!-- Add Student to Batch -->
            <div class="add-student-to-batch">
                <h2>Add Student to Batch</h2>
                <form autocomplete="off" method="post" id="add-student-to-batch-form">
                    <!-- Select Course -->
                    <label>Select Course</label>
                    <div class="dropdown" data-input="add-student-to-batch-course-id">
                        <div class="dropdown-selected">
                            <span>select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                            $coursesList = $courseController->getAllCourse();
    
                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="add-student-to-batch-course-id" name="add_student_to_batch_course_id" value="">
                    
                    <!-- Select Branch -->
                    <label>Select Branch</label>
                    <div class="dropdown" data-input="add-student-to-batch-course-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="add-student-to-batch-course-branch" name="add_student_to_batch_course_branch" value="">

                    <!-- Select Batch -->
                    <label>Select Batch</label>
                    <div class="dropdown" data-input="add-student-to-batch-course-batch-id">
                        <div class="dropdown-selected">
                            <span>Select Batch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course & Branch</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="add-student-to-batch-course-batch-id" name="add_student_to_batch_course_batch_id" value="">

                    <!-- Student Email -->
                    <label>Student Email</label>
                    <input type="email" name="add_student_to_batch_student_email" id="add-student-to-batch-student-email" placeholder="Enter Student Email" required>    

                    <button type="submit" name="add_student_to_batch_btn">Add Student to Batch</button>
                </form>
            </div>

            <!-- Manage Batches -->
            <div class="manage-batches">
                <h2>Manage Batches</h2>
                <div class="filters">
                    <!-- Search Batch Name -->
                    <input type="text" id="manage-batches-search-batch-name" placeholder="Search Batch\Student Name or Email">

                    <!-- Select Course -->
                    <div class="dropdown" data-input="manage-batches-filter-course-id">
                        <div class="dropdown-selected">
                            <span>Select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <?php
                            $coursesList = $courseController->getAllCourse();

                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-batches-filter-course-id" name="manage_batches_filter_course_id" value="">
                    

                    <!-- Select Branch -->
                    <div class="dropdown" data-input="manage-batches-filter-branch">
                        <div class="dropdown-selected">
                            <span>Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value='all'>All</li>
                            <li data-value='Colombo'>Colombo</li>
                            <li data-value='Kandy'>Kandy</li>
                            <li data-value='Matara'>Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input for (this is what PHP will read) -->
                    <input type="hidden" id="manage-batches-filter-branch" name="manage_batches_filter_branch" value="">              
                    
                    <!-- Select Status -->
                    <div class="dropdown" data-input="manage-batches-filter-status">
                        <div class="dropdown-selected">
                            <span>Status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value='all'>All</li>
                            <li data-value='Pending'>Pending</li>
                            <li data-value='Active'>Active</li>
                            <li data-value='Completed'>Completed</li>
                        </ul>
                    </div>
                    <!-- Hidden input for (this is what PHP will read) -->
                    <input type="hidden" id="manage-batches-filter-status" name="manage_batches_filter_status" value=""> 
                </div>

                <!-- Batch Details Table -->
                <div class="table-container">
                    <table id="batch-details-table" class="batch-details-table">
                        <thead>
                            <tr>
                                <th>Batch Name</th>
                                <th>Course Name</th>
                                <th>Branch</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Total Student</th>
                                <th>Student Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="batch-details-list">
                            <?php 
                            $batches = $courseController->getAllBatchesWithStudents();
                            // echo "<script>console.log(" . json_encode($batches) . ");</script>";                           
                            if (!empty($batches)) {
                                foreach ($batches as $batch): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($batch['batch_name']); ?></td>
                                        <td><?= htmlspecialchars($batch['course_name']); ?></td>
                                        <td><?= htmlspecialchars($batch['branch']); ?></td>
                                        <td>
                                            <p><?= htmlspecialchars("Start: ".$batch['start_date']);?></p>
                                            <p><?= htmlspecialchars("End: ".$batch['end_date']); ?></p>
                                        </td>
                                        <td>
                                            <?php if($batch['status'] == "Pending") { ?>
                                                <p style="background-color: yellow;
                                                color: black;
                                                padding: 0.3rem 0.5rem;
                                                border-radius: 0.5rem;">
                                                    <?= htmlspecialchars($batch['status']); ?>
                                                </p>
                                            <?php } elseif($batch['status'] == "Active") { ?>
                                                <p style="background: var(--Nav-Bar-1);
                                                color: white;
                                                padding: 0.3rem 0.5rem;
                                                border-radius: 0.5rem;">
                                                    <?= htmlspecialchars($batch['status']); ?>
                                                </p>
                                            <?php } elseif($batch['status'] == "Completed") { ?>
                                                <p style="background-color: green;
                                                color: black;
                                                padding: 0.3rem 0.5rem;
                                                border-radius: 0.5rem;">
                                                    <?= htmlspecialchars($batch['status']); ?>
                                                </p>
                                            <?php }?>
                                        </td>
                                        <td>
                                            <?= $batch['total_students'] ?>
                                        </td>
                                        <td>
                                            <?php if ($batch['total_students'] 
                                            != 0) { ?>
                                                <p style="text-decoration: underline;
                                                cursor: pointer;"
                                                data-batchid="<?= $batch['batch_id']; ?>"
                                                data-batchname="<?= htmlspecialchars($batch['batch_name']); ?>"
                                                data-students="<?= htmlspecialchars(json_encode($batch["students"]), ENT_QUOTES, "UTF-8") ?>" 
                                                class="view-students">View Students</p>
                                            <?php } else { ?>
                                                <p>No Students</p>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <button
                                            class="edit-batch-btn"
                                            data-batchid="<?= $batch['batch_id']; ?>"
                                            style="background-color: var(--Nav-Bar-1);
                                            color: white;
                                            margin-bottom: 0.25rem;">Edit</button>
                                            <form method="post">
                                                <button
                                                name="delete_batch_btn"
                                                onclick="return confirm('Are you sure you want to delete this batch?')"
                                                value="<?= $batch['batch_id']; ?>"
                                                style="background-color: red;
                                                color: white;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Empty Batch Found</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- View Students Panel -->
                <div class="view-students-batches" id="view-students-panel" style="display:none;">
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
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="students-list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Edit Batch Name/Status -->
                <div class="edit-batch-name-status" style="display:none;">
                    <div class="panel-header">
                        <h2>Edit Batch Details</h2>
                        <button type="button" id="close-edit-batch-panel">X</button>
                    </div>
                    <form method="post" id="edit-batch-form">
                        <!-- batch name -->
                        <label>Batch Name</label>
                        <input type="text" id="edit-batch-name" name="edit_batch_name" required placeholder="Type Batch Name">
                    
                        <!-- batch Status -->
                        <label>Batch Status</label>
                        <div class="dropdown" data-input="edit-batch-status">
                            <div class="dropdown-selected">
                                <span>Select Status</span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value='Pending'>Pending</li>
                                <li data-value='Active'>Active</li>
                                <li data-value='Completed'>Completed</li>
                            </ul>
                        </div>
                        <!-- Hidden input for (this is what PHP will read) -->
                        <input type="hidden" id="edit-batch-status" name="edit_batch_status" value=""> 

                        <button type="submit" id="save-edit-batch" name="save_edit_batch" value="">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Add Instructors -->
            <div class="add-instructor">
                <h2>Add New Instructor</h2>
                <form method="POST" enctype="multipart/form-data" autocomplete="off">

                    <!-- Instructor Name -->
                    <div>
                        <label for="instructor_name">Instructor Name</label>
                        <input type="text" id="instructor-name" name="instructor_name" placeholder="Enter Instructor Name" required>
                    </div>

                    <!-- Gender -->
                    <div>
                        <div class="dropdown" data-input="instructor-gender">
                            <div class="dropdown-selected">
                                <span>Select Gender</span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Male">Male</li>
                                <li data-value="Female">Female</li>
                            </ul>
                        </div>
                        <!-- Hidden input (this is what PHP will read) -->
                        <input type="hidden" id="instructor-gender" name="instructor_gender" value="">
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label for="instructor_mobile_number">Mobile Number</label>
                        <input type="tel" id="instructor_mobile_number" name="instructor_mobile_number" placeholder="Enter Mobile Number" required>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="instructor_bio">Bio</label><br>
                        <textarea id="instructor-bio" name="instructor_bio" placeholder="Enter Bio..." required></textarea>
                    </div>

                    <!-- Profile Picture -->
                    <div>
                        <label for="instructor_image_path">Profile Picture</label>
                        <input type="file" id="instructor-image_path" name="instructor_image_path" placeholder="Upload Instructor Image" accept="image/*" required>
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label for="instructor_specialization">Specialization</label>
                        <input type="text" id="instructor-specialization" name="instructor_specialization" placeholder="Enter Instructor Specialization" required>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="instructor_address">Address</label>
                        <input type="text" id="instructor-address" name="instructor_address" placeholder="Enter Address" required>
                    </div>

                    <!-- Brach -->
                    <div>
                        <div class="dropdown" data-input="instructor-branch">
                            <div class="dropdown-selected">
                                <span>Select Branch</span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Colombo">Colombo</li>
                                <li data-value="Kandy">Kandy</li>
                                <li data-value="Matara">Matara</li>
                            </ul>
                        </div>
                        <!-- Hidden input (this is what PHP will read) -->
                        <input type="hidden" id="instructor-branch" name="instructor_branch" value="">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="instructor_email">Email</label>
                        <input type="email" name="instructor_email" id="instructor-email" placeholder="Enter Email Address" required>
                    </div>

                    <!-- Passsword -->
                    <div class="instructor-password-group">
                        <label for="instructor_password">Password</label>
                        <input type="text" name="instructor_password" id="instructor-password" placeholder="Enter Password" required>
                        <i id="toggle-password" class="fa-solid fa-eye"></i>
                    </div>

                    <!-- password strength indiactor -->
                    <p id="instructor-password-indicator"></p>
                    
                    <!-- Submit Button -->
                    <div>
                        <button name="add_instructor" type="submit">Add Instructor</button>
                    </div>
                </form>
            </div>
            
            <!-- Manage Instructors -->
            <div class="manage-instructor">
                <h2>Manage Instructors</h2>

                <!-- search & filter -->
                <div class="filters">
                    <input type="text" id="search-instructors" placeholder="Search by Instructor Name">
    
                    <!-- Instructor branch -->
                    <div class="dropdown" data-input="filter-instructor-branch">
                        <div class="dropdown-selected">
                            <span>Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value='All'>All</li>
                            <li data-value='Colombo'>Colombo</li>
                            <li data-value='Kandy'>Kandy</li>
                            <li data-value='Matara'>Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input for (this is what PHP will read) -->
                    <input type="hidden" id="filter-instructor-branch" name="filter_instructor_branch" value="">
                </div>

                <!-- Instructor Table -->
                <div class="table-container"> 
                    <table class="instrutors-table" id="instrutors-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Mobile Number</th>
                                <th>Bio</th>
                                <th>Specialization</th>
                                <th>Address</th>
                                <th>Branch</th>
                                <th>Gender</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="instructors-list">
                            <?php 
                            $instructors = $instructorController->getAllInstructors();
                            foreach ($instructors as $instructor): ?>
                                <tr data-instructorUserId="<?= $instructor['user_id']?>" 
                                data-instructorId="<?= $instructor['id']?>">
                                    <td><?= htmlspecialchars($instructor['full_name']); ?></td>
                                    <td><?= htmlspecialchars($instructor['email']); ?></td>
                                    <td><?= htmlspecialchars($instructor['mobile_number']); ?></td>
                                    <td><?= htmlspecialchars($instructor['bio']); ?></td>
                                    <td><?= htmlspecialchars($instructor['specialization']); ?></td>
                                    <td><?= htmlspecialchars($instructor['address']); ?></td>
                                    <td><?= htmlspecialchars($instructor['branch']); ?></td>
                                    <td><?= htmlspecialchars($instructor['gender']); ?></td>
                                    <td>
                                        <?php 
                                            $path = $instructor['image_path']; 
                                            if (!empty($path)) {
                                                $encPath = PathEncryptor::encrypt($path); // now $secretKey exists
                                                echo '<a style="color: var(--text-color);" target="_blank" 
                                                        href="/SkillPro/Helpers/serveUserImage.php?file=' . $encPath . '">
                                                        Instructor Image
                                                    </a>';
                                            } else {
                                                echo '<span style="color: gray;">No Image</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- Edit / Delete Buttons -->
                                        <!-- edit button -->
                                        <button name="edit_instructor" class="edit-instructor-btn"
                                        style="color: white; 
                                        background: var(--Nav-Bar-1);
                                        padding: 0.35rem;
                                        border-radius: 50%;
                                        border: none;
                                        outline: none;
                                        cursor: pointer;
                                        transition: transform 0.2s ease;"
                                        onmouseover="this.style.transform = 'translateY(-3px)';"
                                        onmouseleave="this.style.transform = 'translateY(0px)';">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <!-- delete button -->
                                        <form method="post" class="delete-instructor-btn-form" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this instructor?');">
                                            <!-- hidden value for php -->
                                            <input type="hidden" name="instructor_user_id" value="<?= $instructor['user_id']; ?>">
                                            <button name="delete_instructor" class="delete-instructor-btn" type="submit"
                                            style="color: white; 
                                            background-color: red;
                                            padding: 0.3rem;
                                            border-radius: 50%;
                                            border: none;
                                            outline: none;
                                            cursor: pointer;
                                            transition: transform 0.2s ease;"
                                            onmouseover="this.style.transform = 'translateY(-3px)';"
                                            onmouseleave="this.style.transform = 'translateY(0px)';">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Edit Instructor -->
                <div class="edit-instructor-div">
                    <div class="head-group">
                        <h3>Edit Instructor</h3>
                        <span class="close-btn">&times;</span>
                    </div>

                    <!-- Edit Instructor Form -->
                    <form id="edit-instructor-form" method="POST" enctype="multipart/form-data">
                        <!-- Hidden Input For Instructor User ID -->
                        <input type="hidden" name="instructor_user_id" id="edit-instructor-user-id">

                        <!-- Hidden Input For Instructor table ID -->
                        <input type="hidden" name="instructor_table_id" id="edit-instructor-table-id">

                        <label>Full Name</label>
                        <input type="text" name="edit_instructor_name" id="edit-instructor-name">

                        <label>Email</label>
                        <input type="text" id="edit-instructor-email" readonly>

                        <label>Mobile Number</label>
                        <input type="text" name="edit_instructor_mobile" id="edit-instructor-mobile">

                        <label>Bio</label>
                        <textarea name="edit_instructor_bio" id="edit-instructor-bio"></textarea>

                        <label>Specialization</label>
                        <input type="text" name="edit_instructor_specialization" id="edit-instructor-specialization">

                        <label>Address</label>
                        <input type="text" name="edit_instructor_address" id="edit-instructor-address">

                        <label style="cursor: pointer; color: var(--Nav-Bar-1);" id="change-instructor-image">Change Profile Picture</label>
                        <input style="visibility: hidden;" type="file" id="edit-instructor-image" name="edit_instructor_image" accept="image/*">
                        
                        <div class="form-group">
                            <div class="form-group-2">
                                <!-- Gender -->
                                <label>Gender</label>
                                <div class="dropdown" data-input="edit-instructor-gender">
                                    <div class="dropdown-selected">
                                        <span id="edit-instructor-gender-span">Gender</span>
                                        <i class="fa-solid fa-caret-down"></i>
                                    </div>
                                    <ul class="dropdown-options">
                                        <li data-value="Male">Male</li>
                                        <li data-value="Female">Female</li>
                                    </ul>
                                </div>
                                <!-- Hidden input (this is what PHP will read) -->
                                <input type="hidden" id="edit-instructor-gender" name="edit_instructor_gender" value="">                                
                            </div>
                            <div class="form-group-2">
                                <!-- Branch -->
                                <label>Branch</label>
                                <div class="dropdown" data-input="edit-instructor-branch">
                                    <div class="dropdown-selected">
                                        <span id="edit-instructor-branch-span">Branch</span>
                                        <i class="fa-solid fa-caret-down"></i>
                                    </div>
                                    <ul class="dropdown-options">
                                        <li data-value="Colombo">Colombo</li>
                                        <li data-value="Kandy">Kandy</li>
                                        <li data-value="Matara">Matara</li>
                                    </ul>
                                </div>
                                <!-- Hidden input (this is what PHP will read) -->
                                <input type="hidden" id="edit-instructor-branch" name="edit_instructor_branch" value="">
                            </div>
                        </div>

                        <button name="update_instructor" type="submit">Update Details</button>
                    </form>
                </div>
            </div>

            <!-- Add Course Modules -->
            <div class="add-modules">
                <h2>Add Course Modules</h2>
                <form method="post" id="add-course-modules" enctype="multipart/form-data">
                    <!-- course selection -->
                    <label>Select Course</label>
                    <div class="dropdown" data-input="module-course-id">
                        <div class="dropdown-selected">
                            <span>Select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                            $coursesList = $courseController->getAllCourse();

                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="module-course-id" name="module_course_id" value="">

                    <!-- Course Module Name -->
                    <label>Module Name</label>
                    <input type="text" id="add-course-module-name" name="add_course_module_name" placeholder="Ex:- Module 1" required>

                    <!-- Course Module Duration -->
                    <label>Module Total Sessions</label>
                    <input type="text" name="add_course_module_duration" id="add-course-module-duration" placeholder="Ex:- 10" required>

                    <!-- Course Module Materials -->
                    <label>Module Materials</label>
                    <input type="file" name="add_course_materials" id="add-course-materials" required>

                    <!-- Add Module Button -->
                    <button type="submit" name="add-module">Add Module</button>
                </form>
            </div>

            <!-- Assign Instructor to Modules -->
            <div class="assign-instructor-to-modules">
                <h2>Assign Instructrs to Modules</h2>
                <form method="post" id="assign-instructor-to-module-form">
                    <!-- course selection -->
                    <label>Select Course</label>
                    <div class="dropdown" data-input="assign-module-course-id">
                        <div class="dropdown-selected">
                            <span>select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                            $coursesList = $courseController->getAllCourse();

                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="assign-module-course-id" name="assign_module_course_id" value="">

                    <!-- Branch Selection -->
                    <label>Select Branch</label>
                    <div class="dropdown" data-input="assign-course-module-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="assign-course-module-branch" name="assign_course_module_branch" value="">

                    <!-- Module Selection -->
                    <label>Select Course Module</label>
                    <div class="dropdown" data-input="assign-module-course-moduleId">
                        <div class="dropdown-selected">
                            <span>select Course Module</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="assign-module-course-moduleId" name="assign_module_course_moduleId" value="">

                    <!-- Batch Selection -->
                    <label>Select Course Batch</label>
                    <div class="dropdown" data-input="assign-module-course-batchId">
                        <div class="dropdown-selected">
                            <span>Select Batch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course & branch</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="assign-module-course-batchId" name="assign_module_course_batchId" value="">

                    <!-- Select Instructors -->
                    <label>Select Course Module Instructor</label>
                    <div class="dropdown" data-input="assign-module-course-instructorId">
                        <div class="dropdown-selected">
                            <span>select Instructor</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course & branch</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="assign-module-course-instructorId" name="assign_module_course_instructorId" value="">

                    <button type="submit" name="assign_module_instructor">Assign Instructor</button>
                </form>
            </div>

            <!-- Manage Course Modules -->
            <div class="manage-modules">
                <h2>Manage Course Modules</h2>
                <div class="filters">
                    <!-- Search Module Name/Instructor Name/Course Name -->
                    <input type="text" id="manage-modules-search-input" placeholder="Search Module/Instructor/Course Name">

                    <!-- course selection -->
                    <div class="dropdown" data-input="manage-search-module-course">
                        <div class="dropdown-selected">
                            <span>select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="all">All</li>
                            <?php
                            $coursesList = $courseController->getAllCourse();

                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['name']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-search-module-course" name="manage_search_module_course" value="">

                    <!-- Branch Selection -->
                    <div class="dropdown" data-input="manage-search-course-module-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-search-course-module-branch" name="manage_search_course_module_branch" value="">     
                </div>

                <div class="table-container">
                    <table class="course-module-table" id="course-module-table">
                        <thead>
                            <tr>
                                <th>Module Name</th>
                                <th>Course Name</th>
                                <th>Branch</th>
                                <th>Total Sessions</th>
                                <th>Material</th>
                                <th>Batch Name</th>
                                <th>Instructor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="course-modules-list">
                            <?php
                            $courseModules = $courseController->getAllCourseModules();
                            // echo "<script>console.log(" . json_encode($courseModules) . ");</script>";
                            if (!empty($courseModules)) {
                                foreach ($courseModules as $cm):
                                ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($cm['module_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cm['course_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cm['branch']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cm['total_sessions']); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $path = $cm['material']; 
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
                                        <td>
                                            <?= htmlspecialchars($cm['batch_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cm['instructor_name']); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <!-- delete button -->
                                            <form method="post" class="delete-course-module-btn-form" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this course module?');">
                                                <button
                                                value="<?= $cm['course_module_id']; ?>" 
                                                name="delete_course_module" class="delete-course-module" type="submit"
                                                style="color: white; 
                                                background-color: red;
                                                padding: 0.3rem;
                                                border-radius: 50%;
                                                border: none;
                                                outline: none;
                                                cursor: pointer;
                                                transition: transform 0.2s ease;"
                                                onmouseover="this.style.transform = 'translateY(-3px)';"
                                                onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <tr>
                                    <td style="text-align: center;" colspan="8">Empty Course Modules</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Student Queries -->
            <div class="students-queries">
                <h2>Student Queries</h2>
                <div class="filters">
                    <!-- Status Selection -->
                    <div class="dropdown" data-input="students-queries-status">
                        <div class="dropdown-selected">
                            <span>Select Status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="New">New Queries</li>
                            <li data-value="Solved">Solved Queires</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="students-queries-status" value="">     
                </div>

                <div class="table-container">
                    <table class="students-queries-table" id="students-queries-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Asked At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $studentQueries = $studentController->getAllStudentInquiry();
                            // echo "<script>console.log(" . json_encode($studentQueries) . ");</script>";
                            if (!empty($studentQueries)) {
                                foreach ($studentQueries as $nq):
                                ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($nq['student_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['student_email']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['message']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['asked_at']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['status']); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($nq['status'] == 'New') { ?>
                                                <!-- Solved button -->
                                                <form method="post" style="display: inline-block;">
                                                    <button
                                                    data-email="<?= htmlspecialchars($nq['student_email']); ?>"
                                                    data-name="<?= htmlspecialchars($nq['student_name']); ?>"
                                                    value="<?= $nq['id']; ?>"
                                                    name="solve_student_query_btn" 
                                                    class="open-gmail-btn-squery"
                                                    type="submit"
                                                    style="color: white; 
                                                    background-color: green;
                                                    padding: 0.3rem;
                                                    border-radius: 50%;
                                                    border: none;
                                                    outline: none;
                                                    cursor: pointer;
                                                    transition: transform 0.2s ease;"
                                                    onmouseover="this.style.transform = 'translateY(-3px)';"
                                                    onmouseleave="this.style.transform = 'translateY(0px)';">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <tr>
                                    <td style="text-align: center;" colspan="8">Empty Student Queries</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Non Student Queries -->
            <div class="non-students-queries">
                <h2>Non Student Queries</h2>
                <div class="filters">
                    <!-- Status Selection -->
                    <div class="dropdown" data-input="non-students-queries-status">
                        <div class="dropdown-selected">
                            <span>Select Status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="New">New Queries</li>
                            <li data-value="Solved">Solved Queires</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="non-students-queries-status" value="">     
                </div>

                <div class="table-container">
                    <table class="non-students-queries-table" id="non-students-queries-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Course Name</th>
                                <th>Message</th>
                                <th>Asked At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="course-modules-list">
                            <?php
                            $nonStudentQueries = $studentController->getAllNonStudentQueries();
                            // echo "<script>console.log(" . json_encode($nonStudentQueries) . ");</script>";
                            if (!empty($nonStudentQueries)) {
                                foreach ($nonStudentQueries as $nq):
                                ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($nq['full_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['email']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['course_name']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['message']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['asked_at']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($nq['status']); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($nq['status'] == 'New') { ?>
                                                <!-- Solved button -->
                                                <form method="post" style="display: inline-block;">
                                                    <button
                                                    data-email="<?= htmlspecialchars($nq['email']); ?>"
                                                    data-name="<?= htmlspecialchars($nq['full_name']); ?>"
                                                    data-course="<?= htmlspecialchars($nq['course_name']); ?>"
                                                    value="<?= $nq['id']; ?>" 
                                                    name="solve_non_student_query_btn" 
                                                    class="open-gmail-btn"
                                                    type="submit"
                                                    style="color: white; 
                                                    background-color: green;
                                                    padding: 0.3rem;
                                                    border-radius: 50%;
                                                    border: none;
                                                    outline: none;
                                                    cursor: pointer;
                                                    transition: transform 0.2s ease;"
                                                    onmouseover="this.style.transform = 'translateY(-3px)';"
                                                    onmouseleave="this.style.transform = 'translateY(0px)';">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php } else { ?>
                                <tr>
                                    <td style="text-align: center;" colspan="8">Empty Queries</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
            </div>

            <!-- Add notice -->
            <div class="add-notice">
                <h2>Add Notice</h2>
                <form method="post" autocomplete="off">
                    <!-- Title -->
                    <label>Title</label>
                    <input type="text" name="add_notice_title" id="add-notice-title" placeholder="Enter Notice Title" required>

                    <!-- content -->
                    <label>Content</label>
                    <textarea name="add_notice_content" id="add-notice-content" placeholder="Type here ..." required></textarea>

                    <!-- audience -->
                    <label>Audience</label>
                    <div class="dropdown" data-input="add-notice-audience">
                        <div class="dropdown-selected">
                            <span>Select Audience</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Students">Students</li>
                            <li data-value="Instructors">Instructor</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="add-notice-audience" name="add_notice_audience" value="">

                    <!-- branch -->
                    <label>Branch</label>
                    <div class="dropdown" data-input="add-notice-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="add-notice-branch" name="add_notice_branch" value="">

                    <!-- Start Date -->
                    <label>Start Date</label>
                    <input type="date" required name="add_notice_start_date" id="add-notice-start-date">
                    
                    <!-- End Date -->
                    <label>End Date</label>
                    <input type="date" required name="add_notice_end_date" id="add-notice-end-date">

                    <!-- Add Button -->
                    <button type="submit" name="add_notice_btn">Add Notice</button>
                </form>
            </div>

            <!-- Notice Management -->
            <div class="manage-notices">
                <h2>Manage Notices</h2>
                
                <!-- filters -->
                <div class="filters">
                    <!-- Search Input -->
                    <input type="text" id="manage-notices-search-name" placeholder="Search Notice Name">

                    <!-- Branch -->
                    <div class="dropdown" data-input="manage-notices-filter-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-notices-filter-branch" value="">

                    <!-- date -->
                    <input type="date" id="manage-notices-filter-date">

                    <!-- Status -->
                    <div class="dropdown" data-input="manage-notices-filter-status">
                        <div class="dropdown-selected">
                            <span>Select Status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Scheduled">Scheduled</li>
                            <li data-value="Ongoing">Ongoing</li>
                            <li data-value="Completed">Completed</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-notices-filter-status" value="">
                </div>

                <!-- table container -->
                <div class="table-container"> 
                    <table class="notices-table" id="notices-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Audience</th>
                                <th>Branch</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Total Days</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $allNotices = $courseController->getAllNotices();
                            // echo "<script>console.log(" . json_encode($allNotices) . ");</script>";
                            if (!(empty($allNotices))) { 
                                foreach ($allNotices as $n):?>
                                    <tr>
                                        <td><?= htmlspecialchars($n['title'])?></td>
                                        <td><?= htmlspecialchars($n['content'])?></td>
                                        <td><?= htmlspecialchars($n['audience'])?></td>
                                        <td><?= htmlspecialchars($n['branch'])?></td>
                                        <td><?= htmlspecialchars($n['start_date'])?></td>
                                        <td><?= htmlspecialchars($n['end_date'])?></td>
                                        <td><?= htmlspecialchars($n['total_days'])?></td>
                                        <td><?= htmlspecialchars($n['status'])?></td>
                                        <td style="text-align: center;">
                                            <!-- Delete Buttons -->
                                            <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this notice?');">
                                                <button 
                                                name="delete_notice"
                                                value="<?= intval($n['id']); ?>"
                                                type="submit"
                                                style="color: white; 
                                                background-color: red;
                                                padding: 0.3rem;
                                                border-radius: 50%;
                                                border: none;
                                                outline: none;
                                                cursor: pointer;
                                                transition: transform 0.2s ease;"
                                                onmouseover="this.style.transform = 'translateY(-3px)';"
                                                onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php endforeach; } else { ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">Empty Notices!</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Event -->
            <div class="add-event">
                <h2>Add Event</h2>
                <form method="post" autocomplete="off" enctype="multipart/form-data">
                    <!-- Title -->
                    <label>Title</label>
                    <input type="text" name="add_event_title" id="add-event-title" placeholder="Enter Event Title" required>

                    <!-- description -->
                    <label>Description</label>
                    <textarea name="add_event_description" id="add-event-description" placeholder="Type here ..." required></textarea>

                    <!-- branch -->
                    <label>Branch</label>
                    <div class="dropdown" data-input="add-event-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="add-event-branch" name="add_event_branch" value="">

                    <!-- Start Date Time -->
                    <label>Start Date & Time</label>
                    <input type="datetime-local" required name="add_event_start_date_time" id="add-event-start-date-time">
                    
                    <!-- End Date Time -->
                    <label>End Date & Time</label>
                    <input type="datetime-local" required name="add_event_end_date_time" id="add-event-end-date-time">

                    <!-- image input -->
                    <label>Image</label>
                    <input type="file" id="add-event-image" name="add_event_image" accept="image/*" required>

                    <!-- Add Button -->
                    <button type="submit" name="add_event_btn">Add Events</button>
                </form>
            </div>

            <!-- Event Management -->
            <div class="manage-events">
                <h2>Manage Events</h2>
                
                <!-- filters -->
                <div class="filters">
                    <!-- Search Input -->
                    <input type="text" id="manage-events-search-name" placeholder="Search Notice Name">

                    <!-- Branch -->
                    <div class="dropdown" data-input="manage-events-filter-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-events-filter-branch" value="">

                    <!-- date -->
                    <input type="date" id="manage-events-filter-date">

                    <!-- Status -->
                    <div class="dropdown" data-input="manage-events-filter-status">
                        <div class="dropdown-selected">
                            <span>Select Status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Scheduled">Scheduled</li>
                            <li data-value="Ongoing">Ongoing</li>
                            <li data-value="Completed">Completed</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-events-filter-status" value="">
                </div>

                <!-- timetable container -->
                <div class="table-container"> 
                    <table class="events-table" id="events-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Image</th>
                                <th>Branch</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Total Days</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $allEvents = $courseController->getAllEvents();
                            // echo "<script>console.log(" . json_encode($allEvents) . ");</script>";
                            if (!(empty($allEvents))) { 
                                foreach ($allEvents as $e):?>
                                    <tr>
                                        <td><?= htmlspecialchars($e['title'])?></td>
                                        <td><?= htmlspecialchars($e['description'])?></td>
                                        <td>
                                            <?php
                                            $path = $e['image_path']; 
                                            if (!empty($path)) {
                                                $encPath = PathEncryptor::encrypt($path); // now $secretKey exists
                                                echo '<a style="color: var(--text-color);" target="_blank" 
                                                        href="/SkillPro/Helpers/serveUserImage.php?file=' . $encPath . '">
                                                        Notice Image
                                                    </a>';
                                            } else {
                                                echo '<span style="color: gray;">No Image</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($e['branch'])?></td>
                                        <td><?= htmlspecialchars($e['start_date_time'])?></td>
                                        <td><?= htmlspecialchars($e['end_date_time'])?></td>
                                        <td><?= htmlspecialchars($e['total_days'])?></td>
                                        <td><?= htmlspecialchars($e['status'])?></td>
                                        <td style="text-align: center;">
                                            <!-- Delete Buttons -->
                                            <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                <button 
                                                name="delete_event"
                                                value="<?= intval($e['id']); ?>"
                                                type="submit"
                                                style="color: white; 
                                                background-color: red;
                                                padding: 0.3rem;
                                                border-radius: 50%;
                                                border: none;
                                                outline: none;
                                                cursor: pointer;
                                                transition: transform 0.2s ease;"
                                                onmouseover="this.style.transform = 'translateY(-3px)';"
                                                onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php endforeach; } else { ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">Empty Notices!</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Add Courses -->
            <div class="add-course">
                <!-- Add Course Form -->
                <form method="post" id="add-course-form" enctype="multipart/form-data" autocomplete="off">
                    <h2>Add a New Course</h2>

                    <!-- course name -->
                    <label for="name">Course name</label>
                    <input type="text" name="name" id="name" placeholder="Enter Course Name" style="text-transform: capitalize;">

                    <!-- course category -->
                    <div class="dropdown" data-input="course-category">
                        <div class="dropdown-selected">
                            <span>Category</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                                $courseCategory = $courseController->getCourseCategory(); 
                                if ($courseCategory && $courseCategory->num_rows > 0) {
                                    while ($row = $courseCategory->fetch_assoc()) {
                                        $category = htmlspecialchars($row['category']); // secure output
                                        echo "<li data-value='{$category}'>{$category}</li>";
                                    }
                                } else {
                                    echo "<li>No categories found</li>";
                                }
                            ?>
                        </ul>
                    </div>
                    <!-- New course category  -->
                    <p id="add-course-category">Add a new category</p>
                    <input type="text" id="new-course-category" name="new_category" placeholder="Enter Category Name" style="text-transform: capitalize;">
                    
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="course-category" name="course_category" value="">
                    
                    <!-- Duration & Duration Type -->
                    <div class="form-group">
                        <div class="form-group-2">
                            <label for="duration">Duration</label>
                            <input type="text" name="duration" id="duration" placeholder="Ex: 2">
                        </div>
                        <div class="dropdown" data-input="duration-type">
                            <div class="dropdown-selected">
                                <span>Type</span>
                                <i class="fa-solid fa-caret-down"></i>
                            </div>
                            <ul class="dropdown-options">
                                <li data-value="Year">Year</li>
                                <li data-value="Month">Month</li>
                            </ul>
                        </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="duration-type" name="duration_type" value="">
                    </div>

                    <!-- Fee -->
                    <label for="fee" style="margin-top: 0.5rem;">Course Fee</label>
                    <input type="text" name="fee" id="course-fee" placeholder="Ex: 10000">

                    <!-- About -->
                    <textarea name="about" id="about-course" placeholder="Enter about course ...."></textarea>

                    <!-- Branches -->
                    <h3>Select Branches</h3>
                    <div class="form-select-branch-group">
                        <label><input type="checkbox" name="branches[]" value="Colombo"> Colombo</label>
                        <label><input type="checkbox" name="branches[]" value="Kandy"> Kandy</label>
                        <label><input type="checkbox" name="branches[]" value="Matara"> Matara</label>
                    </div>

                    <!-- Image -->
                    <div class="course-img-container">
                        <label id="course-img-upload">
                            Upload Course Image
                            <input style="visibility: hidden;" type="file" name="course_img" id="course-img">
                        </label>
                        <img id="course-img-preview" src="" alt="course_img">
                    </div>
                    <button type="submit" name="add_course">Add Course</button>
                </form>
            </div>
            
            <!-- Manage Courses -->
            <div class="manage-courses">
                <h2>Manage Courses</h2>

                <!-- Search & Filter -->
                <div class="filters">

                    <!-- Search Course Name -->
                    <input type="text" id="search-course" placeholder="Search by course name">
                    
                    <!-- Search Course Category -->
                    <div class="dropdown" data-input="filter-category">
                        <div class="dropdown-selected">
                            <span>Category</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                                $filterCategory = $courseController->getCourseCategory(); 
                                if ($filterCategory && $filterCategory->num_rows > 0) {
                                    while ($row = $filterCategory->fetch_assoc()) {
                                        $category = htmlspecialchars($row['category']); // secure output
                                        echo "<li data-value='{$category}'>{$category}</li>";
                                    }
                                } else {
                                    echo "<li>No categories found</li>";
                                }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input for course category (this is what PHP will read) -->
                    <input type="hidden" id="filter-category" name="filter_category" value="">

                    <!-- Seacrch course branch -->
                    <div class="dropdown" data-input="filter-branch">
                        <div class="dropdown-selected">
                            <span>Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value='All'>All</li>
                            <li data-value='Colombo'>Colombo</li>
                            <li data-value='Kandy'>Kandy</li>
                            <li data-value='Matara'>Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input for course category (this is what PHP will read) -->
                    <input type="hidden" id="filter-branch" name="filter_branch" value="">
                </div>

                <!-- Courses Table -->
                <div class="table-container"> 
                    <table class="courses-table" id="courses-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Type</th>
                                <th>Fee</th>
                                <th>Branches</th>
                                <th>About</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="course-list">
                            <?php 
                            $courses = $courseController->getAllCourse();
    
                            if ($courses['success'] && !empty($courses['data'])): 
                                foreach($courses['data'] as $course): ?>
                                    <tr data-courseId="<?=$course['id']?>" data-branch="<?= htmlspecialchars($course['branches']); ?>" 
                                        data-category="<?= htmlspecialchars($course['category']); ?>">
                                        <td><?= htmlspecialchars($course['name']); ?></td>
                                        <td><?= htmlspecialchars($course['category']); ?></td>
                                        <td><?= $course['duration']; ?></td>
                                        <td><?= $course['duration_type']; ?></td>
                                        <td><?= number_format($course['fee'],2); ?></td>
                                        <td><?= htmlspecialchars($course['branches']); ?></td>
                                        <td><?= htmlspecialchars($course['about']); ?></td>
                                        <td>
                                            <a style="color: var(--text-color);" target="_blank" href="/SkillPro/Views/Admin/serveImage.php?image_id=<?= $course['id']; ?>">Course Image</a>
                                        </td>
                                        <td>
                                            <!-- Edit / Delete Buttons -->
                                            <!-- edit button -->
                                            <button name="edit_course" class="edit-course-btn"
                                            style="color: white; 
                                            background: var(--Nav-Bar-1);
                                            padding: 0.35rem;
                                            border-radius: 50%;
                                            border: none;
                                            outline: none;
                                            cursor: pointer;
                                            transition: transform 0.2s ease;"
                                            onmouseover="this.style.transform = 'translateY(-3px)';"
                                            onmouseleave="this.style.transform = 'translateY(0px)';" 
                                            class="edit-btn">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <form method="post" class="edit-delete-course-btns" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                <!-- hidden value for php -->
                                                <input type="hidden" name="course_id" value="<?= $course['id']; ?>">
                                                <button name="delete_course" class="delete-course-btn" type="submit"
                                                style="color: white; 
                                                background-color: red;
                                                padding: 0.3rem;
                                                border-radius: 50%;
                                                border: none;
                                                outline: none;
                                                cursor: pointer;
                                                transition: transform 0.2s ease;"
                                                onmouseover="this.style.transform = 'translateY(-3px)';"
                                                onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php endforeach; 
                            else: ?>
                                <tr>
                                    <td colspan="9">No courses found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Hidden Edit Form Popup -->
                <div id="edit-course-div" class="edit-popup">
                    <div class="edit-popup-content">
                        <div class="head-group">
                            <h3>Edit Course</h3>
                            <span class="close-btn">&times;</span>
                        </div>
                        <form id="edit-course-form" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="course_id" id="edit-course-id">
    
                            <label>Name</label>
                            <input type="text" name="edit_name" id="edit-name">
    
                            <label>Category</label>
                            <div class="dropdown" data-input="edit-course-category">
                                <div class="dropdown-selected">
                                    <span id="category">Category</span>
                                    <i class="fa-solid fa-caret-down"></i>
                                </div>
                                <ul class="dropdown-options">
                                    <?php
                                        $courseCategory = $courseController->getCourseCategory(); 
                                        if ($courseCategory && $courseCategory->num_rows > 0) {
                                            while ($row = $courseCategory->fetch_assoc()) {
                                                $category = htmlspecialchars($row['category']); // secure output
                                                echo "<li data-value='{$category}'>{$category}</li>";
                                            }
                                        } else {
                                            echo "<li>No categories found</li>";
                                        }
                                    ?>
                                </ul>
                            </div>
                            <!-- New course category  -->
                            <p id="add-category">Add a new category</p>
                            <input type="text" id="edit-new-category" name="new_category" placeholder="Enter Category Name" style="text-transform: capitalize;">
                    
                            <!-- Hidden input (this is what PHP will read) -->
                            <input type="hidden" id="edit-course-category" name="edit_course_category" value="">

                            <!-- Duration & Duration Type -->
                            <div class="form-group">
                                <div class="form-group-2">
                                    <label for="duration">Duration</label>
                                    <input type="text" name="edit_duration" id="edit-duration" placeholder="Ex: 2">
                                </div>
                                <div class="dropdown" data-input="edit-duration-type">
                                    <div class="dropdown-selected">
                                        <span id="type">Type</span>
                                        <i class="fa-solid fa-caret-down"></i>
                                    </div>
                                    <ul class="dropdown-options">
                                        <li data-value="Year">Year</li>
                                        <li data-value="Month">Month</li>
                                    </ul>
                                </div>
                            <!-- Hidden input (this is what PHP will read) -->
                            <input type="hidden" id="edit-duration-type" name="edit_duration_type" value="">
                            </div>
                            
                            <label>Fee</label>
                            <input type="text" name="fee" placeholder="ex: 75000" id="edit-fee">
    
                            <!-- Branches -->
                            <h3>Select Branches</h3>
                            <div class="form-select-branch-group">
                                <label><input type="checkbox" name="edit_branches[]" value="Colombo"> Colombo</label>
                                <label><input type="checkbox" name="edit_branches[]" value="Kandy"> Kandy</label>
                                <label><input type="checkbox" name="edit_branches[]" value="Matara"> Matara</label>
                            </div>
    
                            <label>About</label>
                            <textarea placeholder="Enter about course ..." name="about" id="edit-about"></textarea>
    
                            <div class="course-img-container">
                                <label id="course-img-upload">
                                    Upload Course Image
                                    <input style="visibility: hidden;" type="file" name="course_img" id="edit-course-img">
                                </label>
                                <img id="course-img-preview" src="" alt="course_img">
                            </div>
                            <button name="update_course" type="submit">Update Course</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Manage Time Table -->
            <div class="manage-timetable">
                <h2>Manage Timetable</h2>
                
                <!-- filters -->
                <div class="filters">
                    <!-- Search Input -->
                    <input type="text" id="manage-timetable-search-input" placeholder="Search Batch/Instructor/Module Name">

                    <!-- Course -->
                    <div class="dropdown" data-input="manage-timetable-filter-course">
                        <div class="dropdown-selected">
                            <span>Select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <?php
                            $coursesList = $courseController->getAllCourse();

                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['name']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-timetable-filter-course" value="">

                    <!-- Branch -->
                    <div class="dropdown" data-input="manage-timetable-filter-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Colombo">Colombo</li>
                            <li data-value="Kandy">Kandy</li>
                            <li data-value="Matara">Matara</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-timetable-filter-branch" value="">

                    <!-- date -->
                    <input type="date" id="manage-timetable-filter-date">

                    <!-- Branch -->
                    <div class="dropdown" data-input="manage-timetable-filter-status">
                        <div class="dropdown-selected">
                            <span>Select status</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-value="All">All</li>
                            <li data-value="Scheduled">Scheduled</li>
                            <li data-value="Ongoing">Ongoing</li>
                            <li data-value="Completed">Completed</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="manage-timetable-filter-status" value="">
                </div>

                <!-- timetable container -->
                <div class="table-container"> 
                    <table class="time-schedule-table" id="time-schedule-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Branch</th>
                                <th>Batch</th>
                                <th>Module</th>
                                <th>Instructor</th>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $timeTables = $courseController->getAllSchedules();
                            // echo "<script>console.log(" . json_encode($timeTables) . ");</script>";
                            if (!empty($timeTables)) {
                                foreach ($timeTables as $tt):?>
                                    <tr>
                                        <td><?= htmlspecialchars($tt['course_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['branch']); ?></td>
                                        <td><?= htmlspecialchars($tt['batch_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['module_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['instructor_name']); ?></td>
                                        <td><?= htmlspecialchars($tt['class_date']); ?></td>
                                        <td style="text-align: center;"><?= htmlspecialchars($tt['duration']); ?></td>
                                        <td><?= htmlspecialchars($tt['room']); ?></td>
                                        <td><?= htmlspecialchars($tt['status']); ?></td>
                                        <td style="text-align: center;">
                                            <!-- Delete Buttons -->
                                            <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this schedue?');">
                                                <button 
                                                name="delete_schedule"
                                                value="<?= intval($tt['id']); ?>"
                                                type="submit"
                                                style="color: white; 
                                                background-color: red;
                                                padding: 0.3rem;
                                                border-radius: 50%;
                                                border: none;
                                                outline: none;
                                                cursor: pointer;
                                                transition: transform 0.2s ease;"
                                                onmouseover="this.style.transform = 'translateY(-3px)';"
                                                onmouseleave="this.style.transform = 'translateY(0px)';">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; } else { ?>
                                    <tr>
                                        <td colspan="10" style="text-align: center;">Empty Time Tables!</td>
                                    </tr>
                                <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Add New Schedule -->
            <div class="add-new-schedule">
                <h2>Add New Class Schedule</h2>
                <form method="post" autocomplete="off">
                    <!-- course selection -->
                    <label>Select Course</label>
                    <div class="dropdown" data-input="new-schedule-course-id">
                        <div class="dropdown-selected">
                            <span>Select Course</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <?php
                            $coursesList = $courseController->getAllCourse();

                            if ($coursesList['success'] && !empty($coursesList['data'])) {
                                foreach ($coursesList['data'] as $course) {
                                    ?>
                                    <li data-value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </li>
                                    <?php
                                }
                            } else {
                                ?>
                                <li>No courses found</li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="new-schedule-course-id" name="new_schedule_course_id" value="">

                    <!-- Branch selection -->
                    <label>Select Branch</label>
                    <div class="dropdown" data-input="new-schedule-course-branch">
                        <div class="dropdown-selected">
                            <span>Select Branch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="new-schedule-course-branch" name="new_schedule_course_branch" value="">

                    <!-- Batch selection -->
                    <label>Select Batch</label>
                    <div class="dropdown" data-input="new-schedule-course-batch">
                        <div class="dropdown-selected">
                            <span>Select Batch</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course & Branch</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="new-schedule-course-batch" name="new_schedule_course_batch" value="">

                    <!-- Course Module selection -->
                    <label>Select Course Module</label>
                    <div class="dropdown" data-input="new-schedule-course-module-id">
                        <div class="dropdown-selected">
                            <span>Select Course Module</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li>Please Select Course</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="new-schedule-course-module-id" name="new_schedule_course_module_id" value="">

                    <!-- Instructor Name -->
                    <label>Instructor Name</label>
                    <input type="text" readonly required id="new-schedule-course-module-instructor-name" name="new_schedule_course_module_instructor_name" placeholder="Please Select Course, module and branch">
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="new-schedule-course-module-instructor-id" name="new_schedule_course_module_instructor_id" value="">

                    <!-- Set Location -->
                    <label>Set Location</label>
                    <input type="text" placeholder="Eg:- Hall 01 or Room 01 or lab 01" required name="new_schedule_course_class_location" id="new-schedule-course-class-location">
                    
                    <!-- Set Date -->
                    <label>Set Date</label>
                    <input type="date" required name="new_schedule_course_class_date" id="new-schedule-course-class-date">
                    
                    <!-- Set Start Time -->
                    <label>Set Start Time</label>
                    <input type="time" required name="new_schedule_course_class_start_time" id="new-schedule-course-class-start-time">
                    
                    <!-- Set End Time -->
                    <label>Set End Time</label>
                    <input type="time" required name="new_schedule_course_class_end_time" id="new-schedule-course-class-end-time">

                    <!-- Save Btn -->
                    <button type="submit" id="add-schedule-save-btn" name="add_schedule_save_btn">Add Schedule</button>
                </form>
            </div>
        </main>
    </div>


    <script src="admin-dashboard-script.js"></script>
</body>
</html>