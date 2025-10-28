<?php
require_once __DIR__ . "/../../Controls/courseController.php"; // include your controller class
require_once __DIR__ . "/../../Controls/instructorController.php"; // include your controller class

$courseController = new CourseController();
$instructorController = new InstructorController();

// Get the action from query string
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'getBranchesByCourse':
        $courseId = intval($_GET['courseId'] ?? 0);
        $courseController->getBranchesByCourse($courseId);
        break;

    case 'getModulesByCourse':
        $courseId = intval($_GET['courseId'] ?? 0);
        $courseController->getModulesByCourse($courseId);
        break;

    case 'getActiveBatchesByCourseAndBranch':
        $courseId = intval($_GET['courseId'] ?? 0);
        $branch = trim($_GET['branch']) ?? ""; // remove intval
        $courseController->getActiveBatchesByCourseAndBranch($courseId, $branch);
        break;

    case 'getInstructorsByBranch':
        $branch = $_GET['branch'] ?? ""; // remove intval
        $instructorController->getInstructorsByBranch($branch);
        break;

    case 'getInstructorsByModuleBatchBranch':
        $branch = trim($_GET['branch']) ?? "";
        $moduleId = intval($_GET['moduleId'] ?? 0);
        $batchId = intval($_GET['batchId'] ?? 0);
        $instructorController->getInstructorsByModuleBatchBranch($moduleId, $batchId ,$branch);
        break;

    case 'getCourseById':
        $courseId = intval($_GET['courseId'] ?? 0);
        echo json_encode($courseController->getCourseById($courseId));
        break;

    case 'deleteStduentFromBatch':
        $data = [
            'batch_id' => intval($_GET['batchId'] ?? 0),
            'student_id' => intval($_GET['studentId'] ?? 0)
        ];
        echo json_encode($courseController->deleteStudentFromBatch($data));
        break;
    
    

    default:
        echo json_encode([]);
        exit;
}

?>