<?php 
//addCourseWithBranch
require_once __DIR__ . "/../Models/course.php";

class CourseController{
    private $courseModel;

    public function __construct()
    {
        $this->courseModel = new Course();
    }

    public function addCourse($data) {

        // validate course name
        if (empty($data['name']) || strlen($data['name']) < 3) {
            return ['success' => false, 'message' => 'Course name must be at least 3 characters'];
        }

        // validate course category
        if (empty($data['category']) && empty($data['new_category'])) {
            return ['success' => false, 'message' => 'Please select a category or add a new one'];
        } else {
            if ($data['category'] == "No categories found") {
                return ['success' => false, 'message' => 'Please select a category or add a new one'];
            }

            // che3ck course cetegory from table
            $courseCategoryInTable = $this->courseModel->getCourseCategory();
            if ($courseCategoryInTable && $courseCategoryInTable->num_rows > 0) {
                while ($row = $courseCategoryInTable->fetch_assoc()) {
                    $courseCategory = htmlspecialchars($row['category']);
                    if ($data['new_category'] == $courseCategory) {
                        return ['success' => false, 'message' => 'Please add a new category'];
                    }
                }
            }
        }


        // validate duration
        if (empty($data['duration']) || !is_numeric($data['duration']) || $data['duration'] <= 0) {
            return ['success' => false, 'message' => 'Duration must be a positive number'];
        }

        // validate duration type
        $allowedDurationTypes = ['Month', 'Year'];
        if (empty($data['duration_type']) || !in_array($data['duration_type'], $allowedDurationTypes)) {
            return ['success' => false, 'message' => 'Please select duration type (Year/Month)'];
        }

        // validate about
        if (empty($data['about']) || strlen($data['about']) < 10) {
            return ['success' => false, 'message' => 'About section must be at least 10 characters'];
        }
        
        //  validaate fee
        if ($data['fee'] === '' || !is_numeric($data['fee']) || $data['fee'] < 0) {
            return ['success' => false, 'message' => 'Please enter a valid fee (positive number)'];
        }

        // limit to 2 decimal places
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['fee'])) {
            return ['success' => false, 'message' => 'Fee can have maximum 2 decimal places'];
        }
        
        // validate branches
        if (!isset($data['branches']) || count($data['branches']) === 0) {
            return ['success' => false, 'message' => 'Please select at least one branch'];
        }

        // validate image
        if (empty($data['image']) || $data['image']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please upload a course image'];
        }

         // check file type & size
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (!in_array($data['image']['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Only JPG and PNG images allowed'];
        }
        if ($data['image']['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Image size must be less than 2MB'];
        }

        // save image
        $uploadDir = __DIR__ . "/../Uploads/Course/"; // course image file path
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // make folder
        }
        $ext = pathinfo($data['image']['name'], PATHINFO_EXTENSION);
        $imgName = trim($data['name']) . "_img." . $ext;
        $uploadPath = $uploadDir . $imgName;

        if (!move_uploaded_file($data['image']['tmp_name'], $uploadPath)) {
            return ['success' => false, 'message' => 'Failed to save course image'];
        }

        // store only file name or relative path
        $imagePath = "Course/". $imgName;


        // sanitize
        $fee = number_format((float)$data['fee'], 2, '.', '');
        // sanitize inputs
        $name     = htmlspecialchars(trim($data['name']));
        $category = !empty($data['category']) ? htmlspecialchars(trim($data['category'])) : htmlspecialchars(trim($data['new_category']));
        $duration = (int)$data['duration'];
        $duType   = htmlspecialchars(trim($data['duration_type']));
        $about    = htmlspecialchars(trim($data['about']));

        // allowed branches for validation
        $allowedBranches = ['Colombo', 'Kandy', 'Matara'];

        foreach ($data['branches'] as $branch) {
            $branch = trim($branch); // normalize
            if (!in_array($branch, $allowedBranches)) {
                return ['success' => false, 'message' => 'Invalid branch'];                
            }
        }
        $branches = $data['branches'];



        $result = $this->courseModel->addCourseWithBranches($name, $category, $duration, $duType, $about, $branches, $imagePath, $fee);

        if ($result['success']) {
            return ['success' => true, 'message' => "Course added successfully!"];
        } else {
            return ['success' => false, 'message' => "Failed to add course!"];
        }

    }

    // function for get course category
    public function getCourseCategory()
    {
        return $this->courseModel->getCourseCategory();
    }

    // function for get all course details
    public function getAllCourse() {
        $allCourses =  $this->courseModel->getAllCourses();
        if ($allCourses) {
            return $allCourses;
        } else {
            return false;
        }
    }

    // function for get all course details
    public function getTotalCourses() {
        return $this->courseModel->getTotalCourses();
    }


    // function for get course by course id
    public function getCourseById($courseId) {
        $course = $this->courseModel->getCourseById($courseId);
        if ($course) {
            return $course;
        } else {
            return false;
        }
    }

    // function for delete course
    public function deleteCourse($courseId){
        return $this->courseModel->deleteCourseById($courseId);
    }

    // funciton for update course
    public function updateCourse($data) {
        // Sanitize inputs
        $id       = intval($data['id']);
        $name     = htmlspecialchars(trim($data['name']));
        $category = !empty($data['category']) ? htmlspecialchars(trim($data['category'])) : htmlspecialchars(trim($data['new_category']));
        $duration = intval($data['duration']);
        $durationType = htmlspecialchars(trim($data['duration_type']));
        $about    = htmlspecialchars(trim($data['about']));
        $fee      = number_format((float)$data['fee'], 2, '.', '');
        $branches = $data['branches'];

        if(empty($branches)) {
            return ['success' => false, 'message' => 'Select Branches'];
        }

        // handle image if uploaded
        $imagePath = null;
        if ($data['image'] && $data['image']['tmp_name']) {
            $uploadDir = __DIR__ . "/../Uploads/Course/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($data['image']['name'], PATHINFO_EXTENSION);
            $imgName = $name . "_img." . $ext;
            $uploadPath = $uploadDir . $imgName;
            if (move_uploaded_file($data['image']['tmp_name'], $uploadPath)) {
                $imagePath = "Course/" . $imgName;
            }
        }

        return $this->courseModel->updateCourseWithBranches($id, $name, $category, $duration, $durationType, $about, $branches, $fee, $imagePath);
    }

    // funtion for add course module
    public function addCourseModule($data, $file) {
        // Validate course
        if (empty($data['course_id'])) {
            return ['success' => false, 'message' => 'Invalid course selected'];
        }

        // Validate module name
        $moduleName = trim($data['name'] ?? '');
        if (strlen($moduleName) < 3) {
            return ['success' => false, 'message' => 'Module name must be at least 3 characters'];
        }
        
        // Validate duration
        $duration = $data['tot_sessions'] ?? '';
        if (!ctype_digit($duration) || intval($duration) <= 0) {
            return ['success' => false, 'message' => 'Duration must be a positive number'];
        }

        // Validate file
        if (!empty($file) && $file['error'] === UPLOAD_ERR_OK) {

            $fileTmpPath = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = mime_content_type($fileTmpPath);
            // Get file extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            $allowedTypes = ["application/pdf","application/zip"];

            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Allowed: PDF, ZIP'];
            }
            if ($fileSize > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File size must be less than 5MB'];
            }

            // Move file if no errors
            $uploadDir = __DIR__ . "/../Uploads/Course_Modules_Material/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = $data['course_id'] . "_module_material." . $extension;
            $destPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $destPath)) {
                return ['success' => false, 'message' => 'File upload failed'];
            }
            $filePathDB = "Course_Modules_Material/" . $newFileName;
        } else {
            return ['success' => false, 'message' => 'Please upload a valid file'];
        }

        if ($this->courseModel->addCourseModule($data['name'], (int) $data['tot_sessions'], $filePathDB, $data['course_id'])) {
            return ['success' => true, 'message' => 'Course module added successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to add course module'];

        }
    }

    // fucntion for get all course modules
    public function getAllCourseModules() {
        return $this->courseModel->getAllCourseModules();
    }

    // function for get all course with instructors therir branches
    public function getAllCoursesWithInstructors() {
        return $this->courseModel->getAllCoursesWithInstructors();
    }

    // function for get distinct categories
    public function getAllCategories() {
        return $this->courseModel->getAllCategories();
    }

    // function for get distinct instructors
    public function getAllInstructors() {
        return $this->courseModel->getAllInstructors();
    }

    // function for get distinct durations
    public function getAllDurations() {
        return $this->courseModel->getAllDurations();
    }

    // function for filter all courses
    // public function getFilteredCourses($category, $branch, $duration, $instructor) {
    //     return $this->courseModel->getFilteredCourses($category, $branch, $duration, $instructor);
    // }



    // // fucntion for get all course modules from course module table
    // public function getAllCourseModulesFromCourseModuleTable() {
    //     return $this->courseModel->getAllCourseModulesFromCourseModuleTable();
    // }

    // function for delete course module by id
    public function deleteCourseModuleById($cmID){
        $result = $this->courseModel->deleteCourseModuleById($cmID);
        if ($result) {
            return ['success' => true, 
                    'message' => 'Course module deleted successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to delete course module.'];
        } 
    }

    // function for get branches by course
    public function getBranchesByCourse($courseId) {
        $result = $this->courseModel->getBranchesByCourse($courseId);
        if (empty($result)) {
            echo json_encode([]);
            exit;
        } else {
            echo json_encode($result);
            exit;
        }
    }

    // function for get course modules by course
    public function getModulesByCourse($courseId) {
        $result = $this->courseModel->getModulesByCourse($courseId);
        if (empty($result)) {
            echo json_encode([]);
            exit;
        } else {
            echo json_encode($result);
            exit;
        }
    }

    // function for get course modules by courseid
    public function getModulesByCourseID($courseId) {
        $result = $this->courseModel->getModulesByCourse($courseId);
        if (empty($result)) {
            return [];
        } else {
            return $result;
        }
    }

    // function for get active batches by course
    public function getActiveBatchesByCourseAndBranch($courseId, $branch) {
        $result = $this->courseModel->getActiveBatchesByCourseAndBranch($courseId, $branch);
        if (empty($result)) {
            echo json_encode([]);
            exit;
        } else {
            echo json_encode($result);
            exit;
        }
    }

    // function for add details to course module instructors
    public function addDetailsToCourseModuleInstructor($data) {
        // validation
        if (empty($data['module_id']) || !is_numeric($data['module_id']) || $data[
        'module_id'] <= 0) {
            return ['success' => false, 'message' => 'Invalid module'];
        }

        if (empty($data['batch_id']) || !is_numeric($data['batch_id']) || $data[
        'batch_id'] <= 0) {
            return ['success' => false, 'message' => 'Invalid batch'];
        }

        if (empty($data['instructor_id']) || !is_numeric($data['instructor_id']) || $data[
        'instructor_id'] <= 0) {
            return ['success' => false, 'message' => 'Invalid instructor'];
        }

        if (empty($data['branch'])) {
            return ['success' => false, 'message' => 'Invalid branch'];
        }

        // allowed branches for validation
        $allowedBranches = ['Colombo', 'Kandy', 'Matara'];
        $branch = $data['branch']; // normalize
        if (!in_array($branch, $allowedBranches)) {
            return ['success' => false, 'message' => 'Invalid branch'];               
        }

        // check if the module and brach for already assign or not
        if ($this->courseModel->hasInstructorInBranch($data['module_id'],$data['branch'], $data['batch_id'])) {
            return ['success' => false, 'message' => 'The module alredy have assigned'];
        }

        $result = $this->courseModel->addDetailsToCourseModuleInstructor($data['module_id'], $data['batch_id'], $data['instructor_id'], $data['branch']);

        if ($result) {
            return ['success' => true, 'message' => "Course added successfully!"];
        } else {
            return ['success' => false, 'message' => "Failed to add course!"];
        }
    }

    // function for create batch
    public function createBatch($data) {
        // validation
        if (empty($data['courseId'])) {
            return ['success' => false, 'message' => 'Invalid Course'];
        }

        if (empty($data['branch'])) {
            return ['success' => false, 'message' => 'Invalid Branch'];
        }

        if (empty($data['batchName'])) {
            return ['success' => false, 'message' => 'Batch name is required'];
        }

        if (empty($data['startDate'])) {
            return ['success' => false, 'message' => 'Start date is required'];
        }

        if (empty($data['endDate'])) {
            return ['success' => false, 'message' => 'Invalid end date'];
        } 

        // Validate date format
        if ($data['startDate'] && !DateTime::createFromFormat('Y-m-d', $data['startDate'])) {
            return ['success' => false, 'message' => 'Invalid start date'];
        }
        if ($data['endDate'] && !DateTime::createFromFormat('Y-m-d', $data['endDate'])) {
            return ['success' => false, 'message' => 'Invalid end date'];
        }

        if ($this->courseModel->createBatch($data['batchName'], (int) $data['courseId'], $data['branch'], $data['startDate'], $data['endDate'])) {
            return ['success' => true, 'message' => 'Batch created sucessfully'];
        } else {
            return ['success' => false, 'message' => 'failed to create batch'];
        }
    }

    // function for add stduent to batch
    public function addStudentToBatch($data) {
        // validate
        if (empty($data['student_id'])){
            return ['success' => false, 'message' => 'Invalid student'];
        }

        if ($this->courseModel->studentCurrentBatchCheck($data['student_id'])) {
            return ['success' => false, 'message' => 'The student currently in a batch'];
        }

        if (empty($data['batch_id'])) {
            return ['success' => false, 'message' => 'Invalid batch'];
        }

        if (!($this->courseModel->checkBatchExist($data['batch_id']))) {
            return ['success' => false, 'message' => 'Invalid batch'];
        }

        // submit data to model
        if ($this->courseModel->addStudentToBatch($data['student_id'], $data['batch_id'])) {
            return ['success' => true, 'message' => 'Student added to the batch Successfully'];
        } else {
            return ['success' => false, 'message' => 'Unable to add student to batch'];
        }
    } 

    // function for check stduent in active batch or pending batch 
    public function studentCurrentBatchCheck($studentId) {
        $result = $this->courseModel->studentCurrentBatchCheck($studentId);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    // function for add student registration
    public function addStudentRegistration($data) {
        // check course id in table
        if (!($this->courseModel->getCourseById($data['course_id']))) {
            return ['success' => false, 'message' => 'Invalid Course'];
        }

        // add student registration
        if ($this->courseModel->addStudentRegistration($data['student_id'], $data['course_id'], $data['branch'])) {
            return ['success' => true, 'message' => 'Registration Added Successfully. Please Waiting for Admin Approval'];
        } else {
            return ['success' => false, 'message' => 'Registration Failed'];
        }
    }

    // function for get student registration
    public function getStudentRegistrations($studentId, $status) {
        $registration = $this->courseModel->getStudentRegistrations($studentId, $status);
        if ($registration) {
            return ['success' => true, 'data' => $registration];
        } else {
            return ['success' => false, 'message' => 'No Pending Courses'];
        }
    }
    
    // function for cancel student registration
    public function changeStudentRegistration($registrationId, $status) {
        $result = $this->courseModel->changeStatusStudentRegistration($registrationId, $status);
        if ($result) {
            return ['success' => true, 
                    'message' => 'Your enrollment has been cancelled successfully.',
                    'message2' => 'Student enrollment approved sucessfully.',
                    'message3' => 'Student enrollment rejected sucessfully.',
            ];
        } else {
            return ['success' => false, 
                    'message' => 'Unable to cancel enrollment. Please try again.',
                    'message2' => 'Failed to approve student enrollment.',
                    'message3' => 'failed to reject student enrollment.',
            ];
        }
    }

    // function for get all batch details with students
    public function getAllBatchesWithStudents() {
        return $this->courseModel->getAllBatchesWithStudents();
    }

    // function for udate batch details
    public function updateBatch($data) {
        // validation
        if (empty($data['batch_id'])) {
            return ['success' => false, 'message' => 'Failed to update batch details'];
        }

        if (empty($data['batch_name'])) {
            return ['success' => false, 'message' => 'Enter Valid Batch Name'];
        }

        if (empty($data['batch_status'])) {
            return ['success' => false, 'message' => 'Select Batch Status'];
        }

        $result = $this->courseModel->updateBatch($data['batch_id'], $data['batch_name'], $data['batch_status']);
        if ($result) {

            if ($data['batch_status'] == 'Active') {
                $mailerBatchStatus = 'Active';
            }

            return ['success' => true, 'mailerBatchStatus' => $mailerBatchStatus,
                    'message' => 'Update batch details successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to update batch details.'];
        }
    }

    // function for delete batch
    public function deletebatch($batchID) {
        $result = $this->courseModel->deletebatch($batchID);
        if ($result) {
            return ['success' => true, 
                    'message' => 'Batch deleted successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to delete batch.'];
        }   
    }

    // fucntion for delete student from batch
    public function deleteStudentFromBatch($data) {
        $result = $this->courseModel->deleteStudentFromBatch($data['batch_id'], $data['student_id']);
        if ($result) {
            return ['success' => true, 
                    'message' => 'Stduent deleted from batch successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to delete student from batch.'];
        }
    }

    // function for add schedule
    public function addSchedule($data) {
        // validation
        if ($data['batch_id'] <= 0) {
            return ['success' => false, 'message' => 'Invalid batch selected.'];
        }

        if ($data['module_id'] <= 0) {
            return ['success' => false, 'message' => 'Invalid module selected.'];
        }

        if ($data['instructor_id'] <= 0) {
            return ['success' => false, 'message' => 'Instructor not assigned.'];
        }
        
        if (empty($data['branch'])){
            return ['success' => false, 'message' => 'Branch is required.'];
        }
        
        if (empty($data['date'])) {
            return ['success' => false, 'message' => 'Date is required.'];
        } else {
            $today = date("Y-m-d");
            if ($data['date'] < $today) {
                return ['success' => false, 'message' => 'Date cannot be in the past.'];
            }
        }
        
        if (empty($data['start_time'])) {
            return ['success' => false, 'message' => 'Start Time is required.'];
        }

        if (empty($data['end_time'])) {
            return ['success' => false, 'message' => 'End Time is required.'];
        }
        
        if (empty($data['location'])) {
            return ['success' => false, 'message' => 'Location is required.'];
        }

        $result = $this->courseModel->addSchedule($data['batch_id'], $data['module_id'], $data['instructor_id'], $data['branch'], $data['date'], $data['start_time'], $data['end_time'], $data['location']);

        if ($result) {
            return ['success' => true, 
                    'message' => 'Scheduled added successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to add schedule.'];
        }
    }

    // function for get all time tables
    public function getAllSchedules() {
        return $this->courseModel->getAllSchedules();
    }

    // function for delete time table by id
    public function deleteSchedule($scheduleId) {
        if ($this->courseModel->deleteSchedule($scheduleId)) {
            return ['success' => true, 
                    'message' => 'Schedule deleted successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to delete this schedule.'];
        }
    }

    // function for get all notices
    public function getAllNotices() {
        return $this->courseModel->getAllNotices();
    }

    // function for delete notice
    public function deleteNotice($noticeId) {
        if ($this->courseModel->deleteNotice($noticeId)) {
            return ['success' => true, 
                    'message' => 'Notice deleted successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to delete this notice.'];
        }
    }

    // function for add notice
    public function addNotice($data) {
        // validation
        if (empty($data['title'])) {
            return ['success' => false, 'message' => 'Title is required.'];
        }

        if (empty($data['content'])) {
            return ['success' => false, 'message' => 'Content is required.'];
        }

        if (empty($data['audience'])) {
            return ['success' => false, 'message' => 'Audience is required.'];
        }
        
        if (empty($data['branch'])){
            return ['success' => false, 'message' => 'Branch is required.'];
        }
        
        if (empty($data['start_date'])) {
            return ['success' => false, 'message' => 'Start Date is required.'];
        } else {
            $today = date("Y-m-d");
            if ($data['start_date'] < $today) {
                return ['success' => false, 'message' => 'Start Date cannot be in the past.'];
            }
        }

        if (empty($data['end_date'])) {
            return ['success' => false, 'message' => 'End Date is required.'];
        } else {
            $today = date("Y-m-d");
            if ($data['end_date'] < $today) {
                return ['success' => false, 'message' => 'End Date cannot be in the past.'];
            }
        }

        $result = $this->courseModel->addNotice($data['title'], $data['content'], $data['audience'], $data['branch'], $data['start_date'], $data['end_date']);

        if ($result) {
            return ['success' => true, 
                    'message' => 'Notice added successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to add notice.'];
        }
    }

    // function for get all events
    public function getAllEvents() {
        return $this->courseModel->getAllEvents();
    }

    // function for delete 
    public function deleteEvent($eventId) {
        if ($this->courseModel->deleteEvent($eventId)) {
            return ['success' => true, 
                    'message' => 'Event deleted successfully.'];
        } else {
            return ['success' => false, 
                    'message' => 'Failed to delete this event.'];
        }
    }

    // function for add event
    public function addEvent($data) {
        // validation
        if (empty($data['title'])) {
            return ['success' => false, 'message' => 'Title is required.'];    
        }
        
        if (empty($data['description'])) {
            return ['success' => false, 'message' => 'Description is required.'];    
        }

        if (empty($data['branch'])) {
            return ['success' => false, 'message' => 'Branch is required.'];    
        }

        if (empty($data['start_date_time']) || empty($data['end_date_time'])) {
            return ['success' => false, 'message' => 'Start and End Date/Time are required.'];
        } else {
            $start = strtotime($data['start_date_time']);
            $end   = strtotime($data['end_date_time']);

            if (!$start || !$end) {
                return ['success' => false, 'message' => 'Invalid date format.'];
            } elseif ($start >= $end) {
                return ['success' => false, 'message' => 'End Date/Time must be later than Start Date/Time.'];
            }
        }

        if (!empty($data['image_path']) && $data['image_path']['error'] === 0) {
            $allowedExt = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($data['image_path']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $errors[] = "Invalid image format. Allowed: JPG, JPEG, PNG.";
                return ['success' => false, 'message' => 'Invalid image format. Allowed: JPG, JPEG, PNG.'];
            }
        }

        if ($data['image_path']['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Image size must be less than 5MB'];
        }

        $uploadDir = __DIR__ . "/../Uploads/EventsPic/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // make folder
        }
        $date_time_string = date('Y-m-d_H-i-s');
        $ext = pathinfo($data['image_path']['name'], PATHINFO_EXTENSION);
        $imgName = $date_time_string . "_img." . $ext;
        $uploadPath = $uploadDir . $imgName;

        if (!move_uploaded_file($data['image_path']['tmp_name'], $uploadPath)) {
            return ['success' => false, 'message' => 'Failed to save event image'];
        }

        $imagePath = "EventsPic/". $imgName;


        $result = $this->courseModel->addEvent(
            $data['title'], $data['description'], $data['branch'], $data['start_date_time'], $data['end_date_time'], $imagePath
        );

        if ($result) {
            return [
                "success" => true,
                "message" => "Event added successfully."
            ];
        } else {
            return [
                "success" => false,
                "errors" => ["Failed to insert event into database."]
            ];
        }
    }

    // fucntion for get student course batch by student id
    public function getStudentCourseBatch($studentId) {
        return $this->courseModel->getStudentCourseBatch($studentId);
    }

    // Function to get timetable for a specific student
    public function getStudentSchedule($studentId) {
        return $this->courseModel->getStudentSchedule($studentId);
    }

    // Function to get timetable for a specific instructor
    public function getInstructorSchedule($instructorId) {
        return $this->courseModel->getInstructorSchedule($instructorId);
    }

    // Function to get getInstructorCourseBatches
    public function getInstructorBatches($instructorId, $status) {
        return $this->courseModel->getInstructorBatches(intval($instructorId), $status);
    }

    // Function to  get instructors all batches
    public function getInstructorAllBatcheswithStudents($instructorId, $status) {
        return $this->courseModel->getInstructorAllBatcheswithStudents(intval($instructorId), $status);
    }

    // function for get students by batch id 
    public function getStudentsByBatchId($batchID) {
        return $this->courseModel->getStudentsByBatchId($batchID);
    }

}
?>