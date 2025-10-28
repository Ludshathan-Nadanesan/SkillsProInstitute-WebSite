<?php
session_start();
require_once __DIR__ . "/../../Controls/courseController.php";
require_once __DIR__ . "/../../Controls/studentController.php";
require_once __DIR__ . "/../../Controls/userController.php";

$control = new UserController();
$courseController = new CourseController();
$studentController = new StudentController();

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
}

// check the student account active or not
if ($control->getUserById(intval($_SESSION['user_id']))['status'] == 0) {
    echo "<script>
        alert('Enrollment Failed: Your account is still not active by admin. Please waiting for admin approve!');
        window.location.href='/SkillPro/index.php';
    </script>";
    exit;
}


// Check if course_id exists in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /SkillPro/index.php");
    exit;
}

// Get course ID from URL
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$course   = $courseController->getCourseById($courseId);

if (!$course) {
    echo "<h2>Course not found!</h2>";
    exit;
}

// Get allowed branches from DB (comma-separated -> array)
$allowedBranches = array_map('trim', explode(',', $course['branches']));

// logic for enroll confirm 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_confirm'])) {
    // Get user input branch
    $userBranch = trim($_POST['branch'] ?? '');

    // Validate
    if (!in_array($userBranch, $allowedBranches)) {
        echo "<script>
            alert('Invalid Branch');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }

    // get student_id from
    $student = $studentController->getStudentDetails($_SESSION['email']);
    if (!$student['success']) {
        echo "<script>
            alert('Student Details Not Found!');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }

    // Extract actual student info
    $studentData = $student['data'];
    $studentId   = intval($studentData['id']); // student_id from DB

    // check if the student already have pending courses
    $studentRegistration = $courseController->getStudentRegistrations($studentId, 'Pending'); 
    if ($studentRegistration['success']) {
        echo "<script>
            alert('Enrollment not allowed: You are already registered for a course. Please complete your current course before enrolling in a new one.');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }

    // check if the stduent in active or pending course batch
    $studentBatch =$courseController->studentCurrentBatchCheck($studentId);
    if ($studentBatch) {
        echo "<script>
            alert('Enrollment not allowed: You are already in a batch for a course. Please complete your current course before enrolling in a new one.');
            window.location.href='" . $_SERVER['PHP_SELF'] . "';
        </script>";
        exit;
    }

    // Build data array
    $data = [
        'student_id'   => $studentId,
        'course_id'    => $courseId,
        'branch'       => $userBranch,
    ];

    $result = $courseController->addStudentRegistration($data);
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
    <title>Course Enrollment - SkillPro Institute</title>
    <link rel="icon" href="/SkillPro/Images/logo.ico" type="image/x-icon">
</head>
<style>
    /* Import Font */
    @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Russo+One&display=swap');
    /* @import url('https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap'); */


    :root {
        --font-family: "Inter", sans-serif;
        /* --font-family: "Comfortaa", sans-serif; */
        --logo-font: "Russo One", sans-serif;
        --bg-color: #fafcff;
        --Nav-Bar-1: #124c87;
        --Nav-Bar-1-Grade: radial-gradient(circle,rgba(18, 76, 135, 1) 0%, rgba(0, 39, 74, 1) 100%);
        --Nav-Bar-2: #ffffff;
        --text-color: black;
        --text-color-secondary: hsl(0, 0%, 30%);
        --hover-color: #3fa1fe;
        --search-bg: hsl(0, 0%, 98%);
        --card-color: linear-gradient(180deg, rgba(245, 245, 245, 1) 0%, rgba(255, 255, 255, 1) 100%);
        --card-hover: white;
        --card-btn: hsl(0, 0%, 90%);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: var(--bg-color);
        font-family: var(--font-family);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .enrollment-container {
        padding: 2rem 1rem;
        border-radius: 1rem;
        background: var(--card-color);
        display: flex;
        flex-direction: column;
        row-gap: 0.5rem;
        justify-content: center;
        align-items: center;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    }

    h1, h2, p, strong, label {
        font-family: var(--font-family);
    }

    h1 {
        font-size: 1.5rem;
        font-weight: 500;
        color: var(--text-color);
        border-bottom: solid 2px var(--Nav-Bar-1);
        padding-bottom: 0.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
     h2 {
        font-size: 1.3rem;
        font-weight: 500;
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }
    p {
        width: 100%;
        text-align: center;
        color: var(--text-color);
    }
    label, select {
        font-size: 1rem;
        font-weight: 400;
        color: var(--text-color-secondary);
    }
    .enrollment-container a,
    .enrollment-container button {
        border-radius: 0.5rem;
        background-color: var(--card-btn);
        font-size: 1rem;
        color: var(--text-color);
        text-decoration: none;
        padding: 0.5rem 2rem;
        border: none;
        outline: none;
    }
    
    .enrollment-container button {
        background-color: var(--Nav-Bar-1);
        color: #fff;
    }
    .logo {
        font-family: var(--logo-font);
        color: var(--Nav-Bar-1);
        font-size: 2rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }


</style>
<body>
    <div class="enrollment-container">
        <div class="logo">
            SkillPro
        </div>
        <h1>Course Enrollment</h1>

        <h2><?= htmlspecialchars($course['name']) ?></h2>
        <p><strong>Duration:</strong> <?= $course['duration'] . ' ' . $course['duration_type'] ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($course['category']) ?></p>
        <p><strong>Fee:</strong> <?= number_format($course['fee'], 2) ?> LKR</p>

        <form method="post">
            <!-- Branch Dropdown -->
            <label for="branch">Select Branch:</label>
            <select name="branch" id="branch" required>
                <option value="">-- Select Branch --</option>
                <?php 
                if (!empty($course['branches'])) {
                    $branches = explode(',', $course['branches']); // split by comma
                    foreach ($branches as $branch) {
                        $branch = trim($branch); // remove extra spaces
                        echo "<option value='" . htmlspecialchars($branch) . "'>" . htmlspecialchars($branch) . "</option>";
                    }
                }
                ?>
            </select>

            <div style="margin-top: 1rem;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            width: 100%;
            column-gap: 0.5rem;">
                <a href="/SkillPro/Views/Home/index.php">Cancel</a>
                <button type="submit" name="enroll_confirm">Enroll</button>
            </div>
        </form>
    </div>
</body>
</html>
