<?php
require_once __DIR__ . "/../../Controls/courseController.php";

session_start();

$courseController = new CourseController();

// Check if logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /SkillPro/Views/Login/login.php");
    exit;
}

if (isset($_GET['image_id'])) {
    $courseId = intval($_GET['image_id']);
    $course   = $courseController->getCourseById($courseId);

    $imagePath = $course['image_path'] ?? null;
    if (!$imagePath) {
        http_response_code(404);
        exit("Image not found");
    }

    $fullPath = __DIR__ . "/../../Uploads/" . $imagePath;
    if (!file_exists($fullPath)) {
        http_response_code(404);
        exit("File not found");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fullPath);
    finfo_close($finfo);

    header("Content-Type: " . $mime);
    header("Content-Length: " . filesize($fullPath));
    readfile($fullPath);
    exit;
}
