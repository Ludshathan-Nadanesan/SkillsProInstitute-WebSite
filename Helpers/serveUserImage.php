<?php
require_once __DIR__ . "/encryption.php";

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit("Missing User File Parameter");
}

// Decrypt the file path
$path = PathEncryptor::decrypt($_GET['file']);

if (!$path) {
    http_response_code(400);
    exit("Invalid request");
}

$fullPath = __DIR__ . "/../Uploads/" . $path;

// Security check: must be inside Uploads/Instructors
$realBase = realpath(__DIR__ . "/../Uploads");
$realPath = realpath($fullPath);

if (strpos($realPath, $realBase) !== 0 || !file_exists($realPath)) {
    http_response_code(404);
    exit("File not found");
}

// Send file
header("Content-Type: " . mime_content_type($realPath));
header("Content-Length: " . filesize($realPath));
readfile($realPath);
exit;
?>