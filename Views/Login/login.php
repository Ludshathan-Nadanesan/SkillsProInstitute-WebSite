<?php
require_once __DIR__ . "/../../Controls/userController.php";

// 🚫 Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

$userCtrl = new UserController();
$error = '';

// If already logged in  prevent going back to login page
if (!empty($_SESSION['user_id'])) {
    // redirect them to home (or role dashboard if you want)
    header("Location: " . $userCtrl->redirectBasedOnRole());
    exit;
}

// session time out for dashboards
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    echo "<script>alert('Your session expired, please login again!');</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_var(trim($_POST['username'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['userpwod'] ?? '');

    if (!$email) {
        $error = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $error = "Password cannot be empty.";
    } elseif ($userCtrl->login($email, $password)) {
        // PRG pattern: Redirect based on role
        header("Location: " . $userCtrl->redirectBasedOnRole());
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkillPro Institute</title>
    <!-- Links -->
    <link rel="stylesheet" href="login-style.css">
    <link rel="icon" href="/Skillpro/Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="login-container">
        <div class="content">
            <div class="text-container">
                <div class="logo">SkillPro</div>
                <div class="text">
                    <h2><i class="fa-solid fa-quote-left"></i></h2>
                    <h3 data-translate="wtoSIP">Welcome to SkillPro Institute</h3>
                    <p data-translate="access ur acc to con">Access your account to continue</p>
                    <h2><i class="fa-solid fa-quote-right"></i></h2>
                </div>
            </div>
            <div class="three-lang">
                <a href="" id="English">English</a>
                <a href="" id="Tamil">தமிழ்</a>
                <a href="" id="Sinhala">සිංහල</a>
            </div>
        </div>
        <div class="login-form">
            <form action="" method="post" id="form" autocomplete="off">
                <input data-translate="enter email addr" type="email" name="username" id="username" placeholder="Enter Email Address" required>
                <div class="password">
                    <input data-translate="enter pwprd" type="password" name="userpwod" id="userpwod" placeholder="Enter Password" required>
                    <i class="fa-solid fa-eye-slash" id="show-password"></i>
                </div>

                <?php if($error): ?>
                    <p style="color:red; font-size: 0.9rem; font-family: var(--font-family)"><?= htmlspecialchars($error)?></p>
                <?php endif; ?>
                
                <button data-translate="log in" type="submit" name="login" value="1" id="login">Log in</button>
                <!-- <button type="submit" id="g-login"> <strong data-translate="log in with" style="font-weight: 400;">Log in with </strong><i class="fa-brands fa-google"></i></button> -->
                <!-- <a data-translate="forgotten password" href="">Forgotten password?</a> -->
            </form>
            <p>
                <strong data-translate="new student">New Student? </strong>
                <a data-translate="register here" href="/SkillPro/Views/Register/register.php">
                    Register Here
                </a>
                <span> | </span>
                <strong data-translate="return to">Return to </strong>
                <a href="/SkillPro/Views/Home/index.php" data-translate="home">Home</a>
            </p>
        </div>
    </div>
    <script src="login-script.js"></script>
</body>
</html>