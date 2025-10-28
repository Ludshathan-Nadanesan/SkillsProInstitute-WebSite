<?php
require_once __DIR__ . "/../Config/database.php";

class Course {
    private $db;
    private $conn;
    private $course_table = "courses";
    private $course_location_table = "course_location";
    private $course_module_table = "course_modules";
    private $course_module_instructor_table = "course_module_instructors";
    private $batches_table = "batches"; 
    private $studentBatchTable = "student_batches";
    private $studentRegistrationTable = "student_registrations";
    private $timetableTable = "timetables";
    private $studentTable = "student_details";
    private $instructorTable = "instructors_details";
    private $userTable = "users";
    private $noticesTable = "notices";
    private $eventsTable = "events";

    public function __construct()
    {
        $this->db = new Database(); // object for Databse class from database config
        $this->conn = $this->db->getConnection();
    }

    // function for get name of course table
    public function getCourseTable()
    {
        return $this->course_table;
    }

    // fucntion for get name of course location table
    public function getCourseLocationTable()
    {
        return $this->course_location_table;
    }

    // function for add course
    public function addCourseWithBranches($name, $category, $duration, $durationType, $about, $branches, $imagePath, $fee) 
    {
        // 1. Insert into courses (only once)
        $sql = "INSERT INTO {$this->course_table} 
                (name, category, duration, duration_type, about, image_path, fee) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssisssd", $name, $category, $duration, $durationType, $about, $imagePath, $fee);
        $result = $stmt->execute();

        if (!$result) {
            $stmt->close();
            return ['success' => false, 'message' => 'Course insert failed'];
        }

        // Get course ID
        $courseId = $this->conn->insert_id; // Correct way
        $stmt->close();


        // 2. Insert multiple branches into course_location
        $sqlBranch = "INSERT INTO {$this->course_location_table} (course_id, branch) VALUES (?, ?)";
        $stmtBranch = $this->conn->prepare($sqlBranch);

        foreach ($branches as $branch) {
            $stmtBranch->bind_param("is", $courseId, $branch);
            $branchResult = $stmtBranch->execute();
            if (!$branchResult) {
                $stmtBranch->close();
                return ['success' => false, 'message' => 'Course branch insert failed'];
            }
        }
        $stmtBranch->close();

        return ['success' => true, 'message' => 'Course added successfully'];
    }

    //  function for get course categories
    public function getCourseCategory() {
        $sql = "SELECT DISTINCT category FROM {$this->course_table}";
        $result = $this->conn->query($sql);
        return $result;
    }

    // function for get all courses with their branches
    public function getAllCoursesWithInstructors() {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.category,
                c.duration,
                c.duration_type,
                c.about,
                c.image_path,
                c.fee,
                GROUP_CONCAT(DISTINCT cl.branch ORDER BY cl.branch SEPARATOR ', ') AS branches,
                GROUP_CONCAT(DISTINCT CONCAT(i.full_name, ' (', cmi.branch, ')') SEPARATOR ', ') AS instructors,
                GROUP_CONCAT(DISTINCT cm.name ORDER BY cm.id SEPARATOR ', ') AS modules
            FROM {$this->course_table} c
            LEFT JOIN {$this->course_location_table} cl ON c.id = cl.course_id
            LEFT JOIN {$this->course_module_table} cm ON cm.course_id = c.id
            LEFT JOIN {$this->course_module_instructor_table} cmi ON cmi.module_id = cm.id
            LEFT JOIN {$this->instructorTable} i ON i.id = cmi.instructor_id
            GROUP BY c.id
            ORDER BY c.id DESC;
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to fetch courses'];
        }

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = [
                'id'            => $row['id'],
                'name'          => htmlspecialchars($row['name']),
                'category'      => htmlspecialchars($row['category']),
                'duration'      => $row['duration'],
                'duration_type' => $row['duration_type'],
                'about'         => htmlspecialchars($row['about']),
                'image_path'    => $row['image_path'],
                'fee'           => $row['fee'],
                'branches'      => $row['branches'] ?? 'Not Set',
                'instructors'   => $row['instructors'] ?? 'Not Set',
                'modules'   => $row['modules'] ?? false
            ];
        }

        return ['success' => true, 'data' => $courses];
    }


    // function for get all courses with instructors with therir branhces
    public function getAllCourses() {
        // Join courses + course_location
        $sql = "SELECT c.id, c.name, c.category, c.duration, c.duration_type, 
                    c.about, c.image_path, c.fee, 
                    GROUP_CONCAT(cl.branch ORDER BY cl.branch SEPARATOR ', ') as branches
                FROM {$this->course_table} c
                LEFT JOIN {$this->course_location_table} cl 
                ON c.id = cl.course_id
                GROUP BY c.id
                ORDER BY c.id DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to fetch courses'];
        }

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = [
                'id'            => $row['id'],
                'name'          => htmlspecialchars($row['name']),
                'category'      => htmlspecialchars($row['category']),
                'duration'      => $row['duration'],
                'duration_type' => $row['duration_type'],
                'about'         => htmlspecialchars($row['about']),
                'image_path'    => $row['image_path'],
                'fee'           => $row['fee'],
                'branches'      => $row['branches']
            ];
        }

        return ['success' => true, 'data' => $courses];
    }

    // function for get total number of courses
    public function getTotalCourses() {
        $sql = "SELECT COUNT(*) as total FROM {$this->course_table}";
        $result = $this->conn->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return intval($row['total']);
        }
        return 0;
    }


    // function for get course by id
    public function getCourseById($id) {
        // Single course with branches
        $sql = "SELECT c.id, c.name, c.category, c.duration, c.duration_type, 
                    c.about, c.image_path, c.fee, 
                    GROUP_CONCAT(cl.branch ORDER BY cl.branch SEPARATOR ', ') as branches
                FROM {$this->course_table} c
                LEFT JOIN {$this->course_location_table} cl 
                ON c.id = cl.course_id
                WHERE c.id = ?
                GROUP BY c.id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id); // bind int
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return [
                'id'            => $row['id'],
                'name'          => htmlspecialchars($row['name']),
                'category'      => htmlspecialchars($row['category']),
                'duration'      => $row['duration'],
                'duration_type' => $row['duration_type'],
                'about'         => htmlspecialchars($row['about']),
                'image_path'    => $row['image_path'],
                'fee'           => $row['fee'],
                'branches'      => $row['branches']
            ];
        }

        return null;
    }

    // fucntion for delete course
    public function deleteCourseById($courseId) {
        // Step 1: Fetch the course to get image_path
        $stmt = $this->conn->prepare("SELECT image_path FROM {$this->course_table} WHERE id = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        $stmt->close();

        // Step 2: Delete the image file if it exists
        if ($course && !empty($course['image_path'])) {
            $filePath = __DIR__ . "/../Uploads/" . $course['image_path']; 
            if (file_exists($filePath)) {
                unlink($filePath); // delete file
            }
        }

        // Step 3: Delete the course row from DB
        $stmt = $this->conn->prepare("DELETE FROM {$this->course_table} WHERE id = ?");
        $stmt->bind_param("i", $courseId);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? true : false;
    }

    // function for update course
    public function updateCourseWithBranches($id, $name, $category, $duration, $durationType, $about, $branches, $fee, $image = null) {
        // 1. Update main course info
        $sql = "UPDATE {$this->course_table} SET 
                    name=?, category=?, duration=?, duration_type=?, about=?, fee=?" .
            ($image ? ", image_path=?" : "") . 
            " WHERE id=? LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);

        if ($image) {
            $stmt->bind_param("ssissdsi", $name, $category, $duration, $durationType, $about, $fee, $image, $id);
        } else {
            $stmt->bind_param("ssissdi", $name, $category, $duration, $durationType, $about, $fee, $id);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return ['success' => false, 'message' => 'Course update failed'];
        }
        $stmt->close();

        // 2. Delete old branches
        $delSql = "DELETE FROM {$this->course_location_table} WHERE course_id=?";
        $stmtDel = $this->conn->prepare($delSql);
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();

        // 3. Insert new branches
        $sqlBranch = "INSERT INTO {$this->course_location_table} (course_id, branch) VALUES (?, ?)";
        $stmtBranch = $this->conn->prepare($sqlBranch);
        foreach ($branches as $branch) {
            $stmtBranch->bind_param("is", $id, $branch);
            $stmtBranch->execute();
        }
        $stmtBranch->close();

        return ['success' => true, 'message' => 'Course updated successfully!'];
    }

    // Function to get 3 courses for home page slider
    public function get3courses() {
        $sql = "SELECT c.id, c.name, c.category, c.duration, c.duration_type, c.about, c.image_path, c.fee,
                       GROUP_CONCAT(cl.branch SEPARATOR ', ') AS branches
                FROM {$this->course_table} c
                LEFT JOIN {$this->course_location_table} cl ON c.id = cl.course_id
                GROUP BY c.id
                LIMIT 4";

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            return $courses;
        } else {
            return []; // return empty array if no courses found
        }
    }

    // fucntion for add course module
    public function addCourseModule($name, $totSessions, $filePath, $courseId) {
        // course_id	name	duration	module_materials_path
        $sql = "INSERT INTO {$this->course_module_table} 
                (name, course_id, duration, module_materials_path) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siis", $name, $courseId, $totSessions, $filePath);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }

    // function for delete course module
    public function deleteCourseModuleById($moduleId) {
        // course_id	name	duration	module_materials_path
        // Step 1: Fetch the course module table to get module_materials_path
        $stmt = $this->conn->prepare("SELECT module_materials_path FROM {$this->course_module_table} WHERE id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        $courseModule = $result->fetch_assoc();
        $stmt->close();

        // Step 2: Delete the module materials file if it exists
        if ($courseModule && !empty($courseModule['module_materials_path'])) {
            $filePath = __DIR__ . "/../Uploads/" . $courseModule['module_materials_path']; 
            if (file_exists($filePath)) {
                unlink($filePath); // delete file
            }
        }

        // Step 3: Delete the course module row from DB
        $stmt = $this->conn->prepare("DELETE FROM {$this->course_module_table} WHERE id = ?");
        $stmt->bind_param("i", $moduleId);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? true : false;
    }

    // function for get all course modules
    public function getAllCourseModules() {
        $sql = "
        SELECT 
            cm.name AS module_name,
            c.name AS course_name,
            COALESCE(cmi.branch, 'Not Set') AS branch,
            cm.duration AS total_sessions,
            COALESCE(cm.module_materials_path, 'Not Set') AS material,
            COALESCE(b.name, 'Not Set') AS batch_name,
            COALESCE(i.full_name, 'Not Set') AS instructor_name,
            COALESCE(i.id, 0) AS instructor_id,
            cm.id AS course_module_id,
            COALESCE(cmi.id, 0) AS course_module_instructor_id
        FROM {$this->course_module_table} cm
        LEFT JOIN {$this->course_module_instructor_table} cmi ON cmi.module_id = cm.id
        LEFT JOIN {$this->instructorTable} i ON cmi.instructor_id = i.id
        LEFT JOIN {$this->course_table} c ON cm.course_id = c.id
        LEFT JOIN {$this->batches_table} b ON cmi.batch_id = b.id
        ";
        
        $result = $this->conn->query($sql);
        $courses = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
        }
        return $courses;
    }

    // function for get distinct categories
    public function getAllCategories() {
        $sql = "SELECT DISTINCT category FROM {$this->course_table} WHERE category IS NOT NULL";
        $result = $this->conn->query($sql);

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        return $categories;
    }

    // function for get distinct instructors
    public function getAllInstructors() {
        $sql = "SELECT DISTINCT full_name FROM {$this->instructorTable}";
        $result = $this->conn->query($sql);

        $instructors = [];
        while ($row = $result->fetch_assoc()) {
            $instructors[] = $row['full_name'];
        }
        return $instructors;
    }

    // function for get distinct durations
    public function getAllDurations() {
        $sql = "SELECT DISTINCT duration, duration_type 
                FROM {$this->course_table} 
                WHERE duration IS NOT NULL";
        $result = $this->conn->query($sql);

        $durations = [];
        while ($row = $result->fetch_assoc()) {
            $durations[] = $row['duration'] . ' ' . $row['duration_type'];
        }
        return $durations;
    }

    // // function for get all course modules
    // public function getFilteredCourses($category = "All", $branch = "All", $duration = "All", $instructor = "All") {
    //     // Base SQL with joins (adjust table names to yours)
    //     $sql = "
    //         SELECT 
    //             c.id,
    //             c.name,
    //             c.category,
    //             c.duration,
    //             c.duration_type,
    //             c.about,
    //             c.image_path,
    //             c.fee,
    //             GROUP_CONCAT(DISTINCT cl.branch ORDER BY cl.branch SEPARATOR ', ') AS branches,
    //             GROUP_CONCAT(DISTINCT i.full_name ORDER BY i.full_name SEPARATOR ', ') AS instructors
    //         FROM {$this->course_table} c
    //         LEFT JOIN {$this->course_location_table} cl ON c.id = cl.course_id
    //         LEFT JOIN {$this->course_module_table} cm ON c.id = cm.course_id
    //         LEFT JOIN {$this->course_module_instructor_table} cmi ON cm.id = cmi.module_id
    //         LEFT JOIN {$this->instructorTable} i ON cmi.instructor_id = i.id
    //         WHERE 1=1
    //     ";

    //     // Apply filters dynamically
    //     if ($category !== "All" && $category !== "") {
    //         $category = $this->conn->real_escape_string($category);
    //         $sql .= " AND c.category = '$category'";
    //     }

    //     if ($branch !== "All" && $branch !== "") {
    //         $branch = $this->conn->real_escape_string($branch);
    //         $sql .= " AND cl.branch = '$branch'";
    //     }

    //     if ($duration !== "All" && $duration !== "") {
    //         $duration = $this->conn->real_escape_string($duration);
    //         $sql .= " AND CONCAT(c.duration, ' ', c.duration_type) = '$duration'";
    //     }

    //     if ($instructor !== "All" && $instructor !== "") {
    //         $instructor = $this->conn->real_escape_string($instructor);
    //         $sql .= " AND i.full_name = '$instructor'";
    //     }

    //     // Grouping (because of GROUP_CONCAT)
    //     $sql .= " GROUP BY c.id ORDER BY c.id DESC";

    //     // Execute query
    //     $result = $this->conn->query($sql);

    //     if (!$result) {
    //         return ['success' => false, 'message' => 'Query Failed: ' . $this->conn->error];
    //     }

    //     $courses = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $courses[] = [
    //             'id'            => $row['id'],
    //             'name'          => htmlspecialchars($row['name']),
    //             'category'      => htmlspecialchars($row['category']),
    //             'duration'      => $row['duration'],
    //             'duration_type' => $row['duration_type'],
    //             'about'         => htmlspecialchars($row['about']),
    //             'image_path'    => $row['image_path'],
    //             'fee'           => $row['fee'],
    //             'branches'      => $row['branches'] ?? 'Not Set',
    //             'instructors'   => $row['instructors'] ?? 'Not Set',
    //         ];
    //     }

    //     return ['success' => true, 'data' => $courses];
    // }


    // // fucntion for get all course modules from course_module table only 
    // public function getAllCourseModulesFromCourseModuleTable(){
    //     // id module_id batch_id instructor_id branch
    //     $sql = "
    //     SELECT 
    //         cm.name AS module_name,  
    //         c.name AS course_name,
    //         cm.duration AS total_sessions,
    //         cm.module_materials_path AS material,  
    //         cm.id AS course_module_id 
    //     FROM {$this->course_module_table} cm 
    //     LEFT JOIN {$this->course_table} c ON cm.course_id = c.id
    //     ";
    //     $result = $this->conn->query($sql);
    //     if ($result && $result->num_rows > 0) {
    //         $courses = [];
    //         while ($row = $result->fetch_assoc()) {
    //             $courses[] = $row;
    //         }
    //         return $courses;
    //     } else {
    //         return []; // return empty array if no courses found
    //     }
    // }

    // // fucntion for get all course modules
    // public function getAllCourseModules(){
    //     // id module_id batch_id instructor_id branch
    //     $sql = "
    //     SELECT 
    //         cm.name AS module_name,  
    //         c.name AS course_name,  
    //         cmi.branch AS branch,
    //         cm.duration AS total_sessions,
    //         cm.module_materials_path AS material,  
    //         b.name AS batch_name,
    //         i.full_name AS instructor_name,
    //         i.id AS instructor_id,
    //         cm.id AS course_module_id,
    //         cmi.id AS course_module_instructor_id
    //     FROM {$this->course_module_instructor_table} cmi 
    //     INNER JOIN {$this->instructorTable} i ON cmi.instructor_id = i.id 
    //     INNER JOIN {$this->course_module_table} cm ON cmi.module_id = cm.id 
    //     LEFT JOIN {$this->course_table} c ON cm.course_id = c.id
    //     INNER JOIN {$this->batches_table} b ON cmi.batch_id = b.id
    //     ";
    //     $result = $this->conn->query($sql);
    //     if ($result && $result->num_rows > 0) {
    //         $courses = [];
    //         while ($row = $result->fetch_assoc()) {
    //             $courses[] = $row;
    //         }
    //         return $courses;
    //     } else {
    //         return [];
    //     }
    // }

    // function for update course module
    public function updateCourseModule($moduleId) {
        //
    }

    // function for get branches by course
    public function getBranchesByCourse($courseId) {
        $sql = "SELECT branch FROM {$this->course_location_table} WHERE course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    // function for get course modules by course
    public function getModulesByCourse($courseId) {
        $sql = "SELECT * FROM {$this->course_module_table} WHERE course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    // function for get pending batches by course and branch
    public function getActiveBatchesByCourseAndBranch($courseId, $branch) {
        $sql = "SELECT id, name, start_date, end_date FROM {$this->batches_table} WHERE course_id = ? AND branch = ? AND status IN ('Pending', 'Active')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $courseId, $branch);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    // Add details to course module instructor table
    public function addDetailsToCourseModuleInstructor($moduleId, $batchId, $instructorId, $branch) {
        // module_id	batch_id	instructor_id branch
        $sql = "INSERT INTO {$this->course_module_instructor_table} 
                (module_id, batch_id, instructor_id, branch) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $moduleId, $batchId, $instructorId, $branch);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }

    }

    // function for check module has alredy instructor in per branches
    public function hasInstructorInBranch($moduleId, $branch, $batch) {
        $sql = "SELECT id 
                FROM course_module_instructors 
                WHERE module_id = ? AND branch = ? AND batch_id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $moduleId, $branch, $batch);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // function for create batch
    public function createBatch($batchName, $courseId, $branch, $startDate, $endDate) {
        // id	name	course_id	branch	start_date	end_date	status	
        $sql = "INSERT INTO {$this->batches_table} (name, course_id, branch, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisss", $batchName, $courseId, $branch, $startDate, $endDate);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }

    // Function to update batch and mark enrolled students as Completed
    public function updateBatch($batchId, $batchName, $batchStatus) {
        // 1. Update batch table
        $sql = "UPDATE {$this->batches_table} SET name=?, status=? WHERE id=? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ssi", $batchName, $batchStatus, $batchId);
        $result = $stmt->execute();
        $stmt->close();

        if (!$result) return false;

        // 2. If batch status is 'Completed', update student_batches
        if (strtolower($batchStatus) === 'completed' || strtolower($batchStatus) === 'active' || strtolower($batchStatus) === 'pending') {
            $sql2 = "UPDATE {$this->studentBatchTable} 
                    SET status=? 
                    WHERE batch_id=? AND status<>'Dropped'";
            $stmt2 = $this->conn->prepare($sql2);
            if (!$stmt2) return false;

            $stmt2->bind_param("si", $batchStatus, $batchId);
            $result2 = $stmt2->execute();
            $stmt2->close();

            return $result2 ? true : false;
        }

        return true; // batch updated but not completed
    }


    // function for delete batch
    public function deletebatch($batchId) {
        $sql = "DELETE FROM {$this->batches_table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$batchId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }

    // function for delete student from batch
    public function deleteStudentFromBatch($batchId, $studentId) {
        $sql = "UPDATE {$this->studentBatchTable} SET status = ? WHERE batch_id = ? AND student_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $status = "Dropped";
        $stmt->bind_param("sii", $status,$batchId, $studentId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    } 

    // function for check batch if it exists or not
    public function checkBatchExist($batchId) {
        $sql = "SELECT b.id, b.status 
                FROM {$this->batches_table} b
                WHERE b.id = ? 
                AND b.status = 'Pending'
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $batchId);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? $row : false;
    }


    // function for add student to batches
    public function addStudentToBatch($studentId, $batchId) {
        // 	id	student_id	batch_id	assigned_at	
        $sql = "INSERT INTO {$this->studentBatchTable} (student_id, batch_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $studentId, $batchId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;
        }

    }

    // Function to get all batches with course name, student count, and student details
    public function getAllBatchesWithStudents() {
        $sql = "SELECT 
                    b.id AS batch_id,
                    b.name AS batch_name,
                    b.branch AS branch,
                    b.start_date AS start_date,
                    b.end_date AS end_date,
                    b.status AS status,
                    c.name AS course_name,
                    COUNT(sb.id) AS total_students
                FROM {$this->batches_table} b
                INNER JOIN {$this->course_table} c ON b.course_id = c.id
                LEFT JOIN {$this->studentBatchTable} sb ON b.id = sb.batch_id
                GROUP BY b.id
                ORDER BY b.id DESC";

        $result = $this->conn->query($sql);

        if (!$result || $result->num_rows === 0) {
            return [];
        }

        $batches = [];
        // while ($row = $result->fetch_assoc()) {
        //     $batches[] = $row;
        // }

        while ($row = $result->fetch_assoc()) {
            $row['students'] = $this->getStudentsByBatchId($row['batch_id']);
            $batches[] = $row;
        }

        return $batches;
    }

    // Helper: get all students in a batch
    public function getStudentsByBatchId($batchId) {
        $sql = "SELECT s.id AS student_id, s.full_name, s.mobile_number, s.user_id, u.email
                FROM {$this->studentBatchTable} sb
                INNER JOIN {$this->studentTable} s ON sb.student_id = s.id
                INNER JOIN {$this->userTable} u ON s.user_id = u.id
                WHERE sb.batch_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $batchId);
        $stmt->execute();
        $result = $stmt->get_result();

        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt->close();

        return $students;
    }


    // function for check student in batches
    public function studentCurrentBatchCheck($studentId) {
        $sql = "SELECT sb.id, sb.batch_id, b.status 
                FROM {$this->studentBatchTable} sb
                INNER JOIN {$this->batches_table} b ON sb.batch_id = b.id
                WHERE sb.student_id = ? 
                AND (b.status = 'Active' OR b.status = 'Pending')
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ? $row : false;
    }


    // function for add student registration
    public function addStudentRegistration($studentId, $courseId, $branch) {
        // id	student_id	course_id	branch	status	registered_at	
        $sql = "INSERT INTO {$this->studentRegistrationTable} (student_id, course_id, branch, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $studentId, $courseId, $branch);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }

    // function for get student registration
    public function getStudentRegistrations($studentId, $status = null) {
        $sql = "SELECT sr.id, c.name AS course_name, sr.branch, sr.registered_at, sr.status
                FROM {$this->studentRegistrationTable} sr
                INNER JOIN courses c ON sr.course_id = c.id
                WHERE sr.student_id = ?";
        
        if ($status) {
            $sql .= " AND sr.status = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $studentId, $status);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $studentId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // function for cancel student registration
    public function changeStatusStudentRegistration($registrationId, $status = null) {
        $sql = "UPDATE {$this->studentRegistrationTable} 
                SET status = ? 
                WHERE id = ? AND status = 'Pending'";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $status, $registrationId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // fucntion for add schedule
    public function addSchedule($batchId, $moduleId, $instructorId, $branch, $date, $sTime, $eTime, $room) {
        $sql = "INSERT INTO {$this->timetableTable} 
            (batch_id, course_module_id, instructor_id, branch, class_date, start_time, end_time, room, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $status = "Scheduled";  // assign to variable
        $stmt->bind_param("iiissssss", 
            $batchId, $moduleId, $instructorId, $branch, $date, $sTime, $eTime, $room, $status
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // function for get all time tables
    public function getAllSchedules() {
        $sql = "SELECT 
            c.name AS course_name,
            s.branch AS branch,
            s.id AS id,
            b.name AS batch_name,
            m.name AS module_name,
            i.full_name AS instructor_name,
            s.class_date,
            CONCAT(
                s.start_time, '\n', '-', '\n', s.end_time, 
                ' \n(',
                LPAD(HOUR(TIMEDIFF(s.end_time, s.start_time)), 2, '0'), 'h:',
                LPAD(MINUTE(TIMEDIFF(s.end_time, s.start_time)), 2, '0'), 'm',
                ')'
            ) AS duration,
            s.room AS room,
            CASE
                WHEN NOW() < CONCAT(s.class_date, ' ', s.start_time) THEN 'Scheduled'
                WHEN NOW() BETWEEN CONCAT(s.class_date, ' ', s.start_time) AND CONCAT(s.class_date, ' ', s.end_time) THEN 'Ongoing'
                WHEN NOW() > CONCAT(s.class_date, ' ', s.end_time) THEN 'Completed'
            END AS status
        FROM {$this->timetableTable} s
        INNER JOIN {$this->batches_table} b ON s.batch_id = b.id
        INNER JOIN {$this->course_table} c ON b.course_id = c.id
        INNER JOIN {$this->course_module_table} m ON s.course_module_id = m.id
        INNER JOIN {$this->instructorTable} i ON s.instructor_id = i.id
        ORDER BY s.class_date DESC
        ";

        $result = $this->conn->query($sql);
        $schedules = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }
        }
        return $schedules;
    }

    // function for delete schedule
    public function deleteSchedule($scheduleId) {
        $sql = "DELETE FROM {$this->timetableTable} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$scheduleId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }

    // function for add notice
    public function addNotice($tit, $con, $aud, $br, $sd, $ed) {
        // id	title	content	audience	branch	start_date	end_date	
        $sql = "INSERT INTO {$this->noticesTable} 
            (title, content, audience, branch, start_date, end_date) 
            VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssssss", 
            $tit, $con, $aud, $br, $sd, $ed
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
        
    }

    // function for delete notice by id
    public function deleteNotice($noticeId) {
        $sql = "DELETE FROM {$this->noticesTable} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$noticeId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }

    // funcrtin for get all notices
    public function getAllNotices() {
        // id	title	content	audience	branch	start_date	end_date	
        $sql = "SELECT 
            n.id AS id,
            n.title as title,
            n.content as content,
            n.audience as audience,
            n.branch as branch,
            n.start_date AS start_date,
            n.end_date AS end_date,
            DATEDIFF(n.end_date, n.start_date) AS total_days,
            CASE
                WHEN n.start_date IS NULL OR n.end_date IS NULL THEN 'Unknown'
                WHEN CURDATE() < n.start_date THEN 'Scheduled'
                WHEN CURDATE() BETWEEN n.start_date AND n.end_date THEN 'Ongoing'
                WHEN CURDATE() > n.end_date THEN 'Completed'
            END AS status
        FROM {$this->noticesTable} n
        ORDER BY n.id DESC
        ";

        $result = $this->conn->query($sql);
        $notices = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notices[] = $row;
            }
        }
        return $notices;
    }


    // fucntion for add event
    public function addEvent($tit, $des, $br, $sdt, $edt, $ip) {
        // 	id	title	description	branch	start_date_time	end_date_time	image_path	
        $sql = "INSERT INTO {$this->eventsTable} 
            (title, description, branch, start_date_time, end_date_time, image_path) 
            VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssssss", 
            $tit, $des, $br, $sdt, $edt, $ip
        );

        $result = $stmt->execute();
        $stmt->close();
        return $result;
        
    }

    // function for delete event by id
    public function deleteEvent($eventId) {
        $sql = "DELETE FROM {$this->eventsTable} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$eventId);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return true;
        } else {
            return false;  
        }
    }


    // funcrtin for get all events
    public function getAllEvents() {
        // 	id	title	description	branch	start_date_time	end_date_time	image_path	
        $sql = "SELECT 
            e.id AS id,
            e.title AS title,
            e.description AS description,
            e.branch AS branch,
            e.image_path AS image_path,
            
            -- Original
            e.start_date_time AS start_date_time_row,
            e.end_date_time AS end_date_time_row,
            
            -- Formatted
            CONCAT(DATE(e.start_date_time), ' (', DATE_FORMAT(e.start_date_time, '%H : %i'), ')') AS start_date_time,
            CONCAT(DATE(e.end_date_time), ' (', DATE_FORMAT(e.end_date_time, '%H : %i'), ')') AS end_date_time,

            -- Formatted with AM/PM
            CONCAT(DATE(e.start_date_time), ' (', DATE_FORMAT(e.start_date_time, '%h:%i %p'), ')') AS start_date_time_formatted,
            CONCAT(DATE(e.end_date_time), ' (', DATE_FORMAT(e.end_date_time, '%h:%i %p'), ')') AS end_date_time_formatted,
            
            -- Duration breakdown
            TIMESTAMPDIFF(DAY, e.start_date_time, e.end_date_time) AS total_days,
            TIMESTAMPDIFF(HOUR, e.start_date_time, e.end_date_time) AS total_hours,
            TIMESTAMPDIFF(MINUTE, e.start_date_time, e.end_date_time) % 60 AS total_minutes,
            
            -- Status
            CASE
                WHEN e.start_date_time IS NULL OR e.end_date_time IS NULL THEN 'Unknown'
                WHEN NOW() < e.start_date_time THEN 'Scheduled'
                WHEN NOW() BETWEEN e.start_date_time AND e.end_date_time THEN 'Ongoing'
                WHEN NOW() > e.end_date_time THEN 'Completed'
            END AS status
        FROM {$this->eventsTable} e
        ORDER BY e.id DESC";


        $result = $this->conn->query($sql);
        $notices = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notices[] = $row;
            }
        }
        return $notices;
    }

    // Function for getting student + batch + course details
    public function getStudentCourseBatch($studentId) {
        $sql = "SELECT 
                    sb.student_id AS student_id,
                    sb.status AS status,
                    sb.certificate_issued AS certificate_issued,
                    b.name AS batch_name,
                    b.branch AS branch,
                    b.start_date AS start_date,
                    b.end_date AS end_date,
                    b.status AS batch_status,
                    b.id AS batch_id,
                    c.id AS course_id,
                    c.name AS course_name,
                    c.duration AS duration_digit,
                    c.duration_type AS duration_type
                FROM {$this->studentBatchTable} sb
                INNER JOIN {$this->batches_table} b ON b.id = sb.batch_id
                INNER JOIN {$this->course_table} c ON c.id = b.course_id
                WHERE sb.student_id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $studentId);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data; // returns an array of rows, empty if none
    }

    // Function to get timetable for a specific student
    public function getStudentSchedule($studentId) {
        $sql = "
            SELECT 
                c.name AS course_name,
                s.branch AS branch,
                s.id AS id,
                b.name AS batch_name,
                m.name AS module_name,
                i.full_name AS instructor_name,
                s.class_date,
                DATE_FORMAT(s.start_time, '%h:%i %p') AS start_time,
                DATE_FORMAT(s.end_time, '%h:%i %p') AS end_time,
                CONCAT(
                    LPAD(HOUR(TIMEDIFF(s.end_time, s.start_time)), 2, '0'), ' H : ',
                    LPAD(MINUTE(TIMEDIFF(s.end_time, s.start_time)), 2, '0'), ' M'
                ) AS duration,
                s.room AS room,
                CASE
                    WHEN NOW() < CONCAT(s.class_date, ' ', s.start_time) THEN 'Scheduled'
                    WHEN NOW() BETWEEN CONCAT(s.class_date, ' ', s.start_time) AND CONCAT(s.class_date, ' ', s.end_time) THEN 'Ongoing'
                    WHEN NOW() > CONCAT(s.class_date, ' ', s.end_time) THEN 'Completed'
                END AS status
            FROM {$this->timetableTable} s
            INNER JOIN {$this->batches_table} b ON s.batch_id = b.id
            INNER JOIN {$this->course_table} c ON b.course_id = c.id
            INNER JOIN {$this->course_module_table} m ON s.course_module_id = m.id
            INNER JOIN {$this->instructorTable} i ON s.instructor_id = i.id
            INNER JOIN {$this->studentBatchTable} sb ON sb.batch_id = b.id
            WHERE sb.student_id = ? 
            AND b.status = 'Active'            -- only active batches
            AND sb.status <> 'Dropped'         -- only students who are not dropped
            ORDER BY s.class_date ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // optional: log $this->conn->error for debugging
            return [];
        }

        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $schedules = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        $stmt->close();
        return $schedules;
    }

    // Function to get timetable for a specific instructor
    public function getInstructorSchedule($instructorId) {
        $sql = "
            SELECT 
                c.name AS course_name,
                s.branch AS branch,
                s.id AS id,
                b.name AS batch_name,
                m.name AS module_name,
                i.full_name AS instructor_name,
                s.class_date,
                DATE_FORMAT(s.start_time, '%h:%i %p') AS start_time,
                DATE_FORMAT(s.end_time, '%h:%i %p') AS end_time,
                CONCAT(
                    LPAD(HOUR(TIMEDIFF(s.end_time, s.start_time)), 2, '0'), ' H : ',
                    LPAD(MINUTE(TIMEDIFF(s.end_time, s.start_time)), 2, '0'), ' M'
                ) AS duration,
                s.room AS room,
                CASE
                    WHEN NOW() < CONCAT(s.class_date, ' ', s.start_time) THEN 'Scheduled'
                    WHEN NOW() BETWEEN CONCAT(s.class_date, ' ', s.start_time) AND CONCAT(s.class_date, ' ', s.end_time) THEN 'Ongoing'
                    WHEN NOW() > CONCAT(s.class_date, ' ', s.end_time) THEN 'Completed'
                END AS status
            FROM {$this->timetableTable} s
            INNER JOIN {$this->batches_table} b ON s.batch_id = b.id
            INNER JOIN {$this->course_table} c ON b.course_id = c.id
            INNER JOIN {$this->course_module_table} m ON s.course_module_id = m.id
            INNER JOIN {$this->instructorTable} i ON s.instructor_id = i.id
            WHERE s.instructor_id = ?
            AND b.status = 'Active'            -- only active batches
            ORDER BY s.class_date ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // optional: log error
            return [];
        }

        $stmt->bind_param("i", $instructorId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $schedules = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        $stmt->close();
        return $schedules;
    }


    // public function getInstructorCourseBatches($instructorId) {
    //     return $this->getInstructorModulesAndActiveBatches($instructorId);
    // }

    public function debugInstructorModules($instructorId) {
        $rows = [];

        $sql = "SELECT * FROM {$this->course_module_instructor_table} WHERE instructor_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    public function getInstructorBatches($instructorId, $status) {
        $batches = [];

       $sql = "SELECT 
            cmi.branch AS branch,
            cm.name AS module_name,
            cm.module_materials_path AS material_path,
            cm.duration AS module_session,
            c.name AS course_name,
            c.duration AS duration,
            c.duration_type AS duration_type,
            b.name AS batch_name,
            b.start_date AS batch_start_date,
            b.end_date AS batch_end_date,
            cmi.module_id
        FROM {$this->course_module_instructor_table} cmi
        INNER JOIN {$this->course_module_table} cm ON cm.id = cmi.module_id
        INNER JOIN {$this->course_table} c ON c.id = cm.course_id
        INNER JOIN {$this->batches_table} b ON b.id = cmi.batch_id
        WHERE cmi.instructor_id = ? AND  b.status = ?
        ORDER BY cmi.batch_id ASC, cmi.id DESC";


        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $instructorId, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $batches[] = $row;
        }

        $stmt->close();
        return $batches;
    }

    public function getInstructorAllBatcheswithStudents($instructorId, $status) {
        $batches = [];

        $sql = "SELECT
                    cmi.batch_id AS batch_id,
                    b.name AS batch_name,
                    b.start_date AS batch_start_date,
                    b.end_date AS batch_end_date,
                    c.name AS course_name,
                    COUNT(DISTINCT sb.student_id) AS total_students
                FROM {$this->course_module_instructor_table} cmi
                INNER JOIN {$this->batches_table} b ON b.id = cmi.batch_id
                INNER JOIN {$this->studentBatchTable} sb ON sb.batch_id = cmi.batch_id
                INNER JOIN {$this->course_table} c ON c.id = b.course_id
                WHERE cmi.instructor_id = ? AND b.status = ?
                GROUP BY cmi.batch_id, b.name, b.start_date, b.end_date, c.name
                ORDER BY cmi.batch_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $instructorId, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Add students for this batch
            $row['students'] = $this->getStudentsByBatchId($row['batch_id']);
            $batches[] = $row;
        }

        $stmt->close();
        return $batches;
    }


// getStudentsByBatchId







}




?>