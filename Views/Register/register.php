<?php
require_once __DIR__ . "/../../Controls/userController.php";
require_once __DIR__ . "/../../Controls/studentController.php";
require_once __DIR__ . "/../../Helpers/Mailer.php";

// Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

$sController = new StudentController();
$uController = new UserController();
$login_page = "/SkillPro/Views/Login/login.php";

// If already logged in, prevent going back to login page
if (!empty($_SESSION['user_id'])) {
    header("Location: " . $uController->redirectBasedOnRole());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect form data
    $fullName = trim($_POST['full-name'] ?? "");
    $dob = $_POST['dob'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $nic = trim($_POST['nic-no'] ?? "");
    $street = trim($_POST['street-addr'] ?? "");
    $province = $_POST['province'] ?? "";
    $email = trim($_POST['email-address'] ?? "");
    $mobile = trim($_POST['mobile-no'] ?? "");
    $password = $_POST['password'] ?? "";

    // Call StudentController register() function
    $result = $sController->register([
        "email" =>$email,
        "password" =>$password,
        "full_name" => $fullName,
        "dob" => $dob,
        "gender" => $gender,
        "nic_number" => $nic,
        "street_address" => $street,
        "province" => $province,
        "mobile_number" => $mobile
    ]);

    // Set action message
    if ($result["success"]) {
        $msg = "✅ Registration successful! Please Login to Continue.";

        $mailer = new Mailer();
        // Example: send email to a student
        // Local banner image
        $bannerPath = __DIR__ . "/../../Images/banner.jpg"; // full path
        $bannerCid = "institute_banner"; // unique Content-ID

        $body = "
            <div style='text-align:center; font-family: Arial, sans-serif;'>
                <img src='cid:{$bannerCid}' alt='Institute Banner' style='width:100%; max-width:600px;'><br><br>
                <h1>Welcome to Our Institute!</h1>
                <p>Hi {$fullName},</p>
                <p>Your student account has been successfully created.</p>
                <p>You can now log in to your dashboard and explore available courses.</p>
                <p>We are excited to have you onboard!</p>
                <hr>
                <p style='font-size:0.9em;'>This is an automated email. Please do not reply.</p>
            </div>
        ";
        // Embed the banner
        $mailer->addEmbeddedImage($bannerPath, $bannerCid);

        $mailer->sendMail(
            trim($email),    // to email
            trim($fullName), // to name
            "Welcome to our Institute!",  // subject
            $body// HTML body
        );

        echo "<script>
            alert('$msg'); // show error
            window.location.href = '$login_page'; // redirect after alert
        </script>";
        exit; // stop PHP execution
    } else {
        $msg = "❌ Registration Failed!";
        echo "<script>
                alert(" . json_encode($msg . ' ' . $result['message']) . ");
                window.location.href = '/SkillPro/Views/Register/register.php';
            </script>";

        exit; // stop PHP execution
    }

    exit; // stop PHP execution
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkillPro Institute</title>
    <!-- Links -->
    <link rel="stylesheet" href="register-style.css">
    <link rel="icon" href="/Skillpro/Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="register-container">
        <div class="content">
            <div class="text-container">
                <div class="logo">SkillPro</div>
                <div class="text">
                    <h2><i class="fa-solid fa-quote-left"></i></h2>
                    <h3 data-translate="ur 1st step to">“Your First Step Toward Skills & Success”</h3>
                    <p data-translate="cre acc beg ur jrny">Create an account and begin your journey</p>
                    <h2><i class="fa-solid fa-quote-right"></i></h2>
                </div>
            </div>
            <div class="three-lang">
                <a href="" id="English">English</a>
                <a href="" id="Tamil">தமிழ்</a>
                <a href="" id="Sinhala">සිංහල</a>
            </div>
        </div>
        <div class="register-form">
            <form action="" method="post" id="form" autocomplete="off">
                <!-- Full Name -->
                <input data-translate="full name" type="text" name="full-name" id="full-name" placeholder="Full Name" required>

                <div class="form-group">
                    <!-- Date Of Birth -->
                    <input data-translate="dob" min="1900-01-01" onfocus="this.type='date'; if(!this.value) this.value='YYYY-MM-DD';" onblur="if(this.value==='YYYY-MM-DD' || !this.value){ this.type='text'; this.value=''; }" type="text" name="dob" id="dob" placeholder="DOB" required>


                    <!-- Gender -->
                    <div class="dropdown" data-input="gender">
                        <div class="dropdown-selected">
                            <span data-translate="gender">Gender</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-translate="male" data-value="Male">Male</li>
                            <li data-translate="female" data-value="Female">Female</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="gender" name="gender" value="">
                </div>

                <!-- NIC No -->
                <input type="text" maxlength="12" data-translate="nic no" name="nic-no" id="nic-no" placeholder="NIC Number" oninput="this.value = this.value.toUpperCase()" required>

                <div class="form-group">
                    <!-- Street Addres -->
                    <input type="text" name="street-addr" id="street-addr" data-translate="street addr" placeholder="Street Address" required>
                    <!-- Province -->
                    <div class="dropdown" data-input="province">
                        <div class="dropdown-selected">
                            <span data-translate="province">Province</span>
                            <i class="fa-solid fa-caret-down"></i>
                        </div>
                        <ul class="dropdown-options">
                            <li data-translate="western" data-value="Western">Western</li>
                            <li data-translate="central" data-value="Central">Central</li>
                            <li data-translate="southern" data-value="Southern">Southern</li>
                            <li data-translate="north western" data-value="North Western">North Western</li>
                            <li data-translate="sabaragamuwa" data-value="Sabragamuwa">Sabragamuwa</li>
                            <li data-translate="eastern" data-value="Eastern">Eastern</li>
                            <li data-translate="uva" data-value="Uva">Uva</li>
                            <li data-translate="north central" data-value="North Central">North Central</li>
                            <li data-translate="northen" data-value="Northern">Northern</li>
                        </ul>
                    </div>
                    <!-- Hidden input (this is what PHP will read) -->
                    <input type="hidden" id="province" name="province" value="">
                </div>

                <div class="form-group">
                    <!-- Email Address -->
                    <input data-translate="email addr" type="text" name="email-address" id="email-address" placeholder="Email Address">
                    <!-- Mobile No -->
                    <input type="text" oninput="this.value = this.value.replace(/[^0-9]/g, '')" inputmode="numeric" name="mobile-no" id="mobile-no" placeholder="07XXXXXXXX" maxlength="10" required>
                </div>

                <!-- Password -->
                <div class="password">
                    <input type="password" data-translate="set pword" placeholder="Password" name="password" id="password">
                    <i class="fa-solid fa-eye-slash" id="show-password"></i>
                </div>
                
                <!-- strenght indicator -->
                <div id="strength-indicator"></div>

                <!-- Sign Up Buttons -->
                <button data-translate="signup" type="submit" id="signup">Sign up</button>
                <!-- <button type="submit" id="g-signup"> <strong data-translate="signup with" style="font-weight: 400;">Sign up with </strong><i class="fa-brands fa-google"></i></button> -->
            </form>
            <p style="text-align: center;">
                <strong data-translate="alrdy have acc">Already Have Account? </strong>
                <a data-translate="login here" href="/SkillPro/Views/Login/login.php">Login Here</a>
                <span> | </span>
                <strong data-translate="return to">Return to </strong>
                <a href="/SkillPro/Views/Home/index.php" data-translate="home">Home</a>
            </p>
        </div>
    </div>
    
    <script src="register-script.js"></script>
</body>
</html>