<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Prevent cache (so back button won’t show dashboard again)
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0");

// Redirect to home or login page
header("Location: /SkillPro/Views/Login/login.php");
exit;
?>
