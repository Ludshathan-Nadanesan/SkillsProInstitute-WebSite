<?php 
require_once __DIR__ . "/../../Config/database.php";
$db = new Database();
if ($db->getConnection()) {
    header("Location: " . "/SkillPro/index.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Unavailable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f8f9fa;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            box-sizing: border-box;
        }
        .error-box {
            background: #fff;
            border-radius: 12px;
            padding: 2rem 5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: inline-block;
            width: auto;
            height: auto;
        }
        img {
            width: 350px;
            height: auto;
            margin: 0;
            margin-bottom: -70px;
        }

        p {
            font-size: 1.25rem;
            color: #555;
        }

    </style>
</head>
<body>
    <div class="error-box">
        <img src="/SkillPro/Images/503error.jpg" alt="error-503">
        <p>Sorry, our servers are temporarily down.<br>Please try again later.</p>
    </div>
</body>
</html>
