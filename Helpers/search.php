<?php
require_once __DIR__ . "/../Controls/instructorController.php";
require_once __DIR__ . "/../Controls/courseController.php";

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$query = strtolower(trim($query));
$results = [];

// Instructors
$instructorObj = new InstructorController();
$allInstructors = $instructorObj->getAllInstructors();
foreach($allInstructors as $ins){
    if(str_contains(strtolower($ins['full_name']), $query) || str_contains(strtolower($ins['branch']), $query)){
        $results[] = [
            'text' => $ins['full_name'] . " - " . $ins['branch'],
            'url' => "/SkillPro/Views/InstructorHome/instructor-home.php#instructor-" . $ins['id']
        ];
    }
}

// Courses
$courseObj = new CourseController();
$allCourses = $courseObj->getAllCoursesWithInstructors();
if($allCourses['success']){
    foreach($allCourses['data'] as $course){
        if(str_contains(strtolower($course['name']), $query) || str_contains(strtolower($course['category']), $query)){
            $results[] = [
                'text' => $course['name'] . " - " . $course['instructors'],
                'url' => "/SkillPro/Views/Course/course.php#course-" . $course['id']
            ];
        }
    }
}

// Events
$allEvents = $courseObj->getAllEvents();
foreach($allEvents as $event){
    if(str_contains(strtolower($event['title']), $query)){
        $results[] = [
            'text' => $event['title'],
            'url' => "/SkillPro/Views/Event/event.php#event-" . $event['id']
        ];
    }
}

echo json_encode($results);
