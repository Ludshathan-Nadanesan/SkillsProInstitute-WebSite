<?php 
require_once __DIR__ . "/../../Controls/userController.php";

$control = new UserController();

// Check if logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /SkillPro/Views/Login/login.php");
    exit;
}

if ($control->findUserByEmail($_SESSION['email'])['status'] == 1) {
        header("Location: " . "/SkillPro/Views/Student/student_dashboard.php");
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Account Approval Pending - SkillPro Institute</title>
    <link rel="icon" href="/Skillpro/Images/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Import Font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Russo+One&display=swap');
        /* @import url('https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap'); */

        :root {
            --font-family: "Inter", sans-serif;
            /* --font-family: "Comfortaa", sans-serif; */
            --logo-font: "Russo One", sans-serif;
            --bg-color: hsl(0, 0%, 93%);
            --Nav-Bar-1: #124c87;
            --Nav-Bar-1-Grade: radial-gradient(circle,rgba(18, 76, 135, 1) 0%, rgba(0, 39, 74, 1) 100%);
            --Nav-Bar-2: #ffffff;
            --text-color: black;
            --text-color-secondary: hsl(0, 0%, 30%);
            --hover-color: #3fa1fe;
            --focus-color-buttons: rgba(62, 161, 254, 0.5);
            --hover-color-secondary: hsl(0, 0%, 90%);
            --search-bg: hsl(0, 0%, 98%);
            --card-color: linear-gradient(180deg, rgba(245, 245, 245, 1) 0%, rgba(255, 255, 255, 1) 100%);
            --card-hover: white;
            --card-btn: hsl(0, 0%, 90%);
            --border: #ccc;
        }

        /* Dark Theme Color Settings */
        body.dark {
            --bg-color: hsl(0, 0%, 7%);
            --text-color: white;
            --text-color-secondary: hsl(0, 0%, 70%);
            --Nav-Bar-2: hsl(0, 0%, 10%);
            --card-color: linear-gradient(180deg,rgba(30, 30, 30, 1) 0%, rgba(26, 26, 26, 1) 100%);
            --card-hover: linear-gradient(180deg,rgb(40, 40, 40, 1) 0%, rgba(26, 26, 26, 1) 100%);
            --search-bg: hsl(0, 0%, 12%);
            --card-btn: hsl(0, 0%, 20%);
            --hover-color-secondary: hsl(0, 0%, 15%);
            --border: #555;
            --focus-color-buttons: rgba(62, 161, 254, 0.2);
        }

        body {
            font-family: var(--font-family);
            background: var(--bg-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            box-sizing: border-box;
        }
        .container {
            background: var(--card-color);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            border-top: 1px solid var(--border);
        }
        .container #iconImg {
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: var(--text-color-secondary);
        }

        #logo {
            font-family: var(--logo-font);
            color: var(--hover-color);
            font-size: 2rem;
            font-weight: 500;
        }

        h1 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            color: var(--text-color-secondary);
            margin-bottom: 20px;
        }
        .status {
            display: inline-block;
            padding: 10px 20px;
            border-top-left-radius: 25px;
            border-top-right-radius: 25px;
            border-bottom-left-radius: 0px;
            background: #ffcc00;
            color: #333;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info {
            font-size: 14px;
            color: var(--text-color-secondary);
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background: #2575fc;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background: #1d5edb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 id="logo">SkillPro</h1>
        <i id="iconImg" class="fa-solid fa-envelope"></i>
        <h1>Your Account is Pending for Approval</h1>
        <p>Thank you for registering with our SkillPro Institute. Your account is currently under review.</p>
        <div class="status">⏳ Waiting for Admin Approval</div>
        <p class="info">Once your account is approved, you will receive an email notification and can access the full system.</p>
        <a href="/SkillPro/Views/Login/logout.php" class="logout-btn">Logout</a>
    </div>

    <script>
        //  On page refresh or loading
        window.onload = () => {
            let savedTheme = localStorage.getItem("theme") || "light";
            setTheme(savedTheme);
        }

        // Force reload on back/forward navigation
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
        
        // Set Theme Function
        function setTheme(theme) {
            if (theme === "dark") {
                document.body.classList.add("dark");
                localStorage.setItem("theme", "dark");
            } else {
                document.body.classList.remove("dark");
                localStorage.setItem("theme", "light");
            }
        };
    </script>
</body>
</html>