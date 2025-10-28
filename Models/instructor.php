<?php
require_once __DIR__ . "/user.php";

class Instructor {
    private $userConn;
    private $user;
    private $instructorTable = "instructors_details";
    private $courseModuleInstructorTable = "course_module_instructors";

    public function __construct()
    {
        $this->user = new User();
        $this->userConn = $this->user->getUserConnection();
    }

    // function for create instructor
    public function addInstructor($userId, $fullName, $mobile, $bio, $imagePath, $specialization, $address, $branch, $gender) {
        $sql = "INSERT INTO {$this->instructorTable} 
            (user_id, full_name, mobile_number, bio, image_path, specialization, address, branch, gender) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->userConn->prepare($sql);

        // "i" = integer, "s" = string
        $stmt->bind_param(
            "issssssss", 
            $userId,        // i
            $fullName,      // s
            $mobile,        // s
            $bio,           // s
            $imagePath,     // s
            $specialization,// s
            $address,       // s
            $branch,        // s
            $gender         // s
        );

        return $stmt->execute();
    }

    // Function for check exists of student mobile number 
    public function checkInstructorMobileExists($mobileNumber) {
        $sql = "SELECT id FROM {$this->instructorTable} WHERE mobile_number = ? LIMIT 1";
        $stmt = $this->userConn->prepare($sql);
        $stmt->bind_param("s", $mobileNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();  // return actual row
        } else {
            return false;
        }
    }

    // function for get total number of courses
    public function getTotalInstructors() {
        $sql = "SELECT COUNT(*) as total FROM {$this->instructorTable}";
        $result = $this->userConn->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return intval($row['total']);
        }
        return 0;
    }


    // function for getting all Instructors
    public function getAllInstructors() {
        // join with users table to fetch email
        
        $userTable = $this->user->getUserTable();

        $sql = "SELECT i.full_name,
                    i.user_id,
                    i.id,
                    u.email, 
                    i.mobile_number, 
                    i.bio, 
                    i.specialization, 
                    i.address, 
                    i.branch, 
                    i.gender, 
                    i.image_path
                FROM {$this->instructorTable} i
                INNER JOIN {$userTable} u ON i.user_id = u.id";

        $result = $this->userConn->query($sql);

        $instructors = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $instructors[] = $row;
            }
        }
        return $instructors;
    }

    // fucntion for delete instructor
    public function deleteInstructorById($instructorId) {
        // Step 1: Fetch the instructor_details to get image_path
        $stmt = $this->userConn->prepare("SELECT image_path FROM {$this->instructorTable} WHERE user_id = ?");
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $instructor = $result->fetch_assoc();
        $stmt->close();

        // Step 2: Delete the image file if it exists
        if ($instructor && !empty($instructor['image_path'])) {
            $filePath = __DIR__ . "/../Uploads/Instructors/" . $instructorId; 
            if (file_exists($filePath)) {
                unlink($filePath); // delete file
            }
        }

        // Step 3: Delete the user from DB
        $result = $this->user->deletUserById($instructorId);
        return $result;
    }

    // function for update instructor details
    public function updateInstructor($name, $mobile, $bio, $imagePath, $specialization, $address, $branch, $gender, $id) {
        $sql = "UPDATE {$this->instructorTable} SET full_name = ?, mobile_number = ?, bio = ?, image_path = ?, specialization = ?, address = ?, branch = ?, gender = ? WHERE id = ? LIMIT 1";
        $stmt = $this->userConn->prepare($sql);
        $stmt->bind_param("ssssssssi", $name, $mobile, $bio, $imagePath, $specialization, $address, $branch, $gender, $id);
        return $stmt->execute();
    }

    // function for get instructor by userid
    public function getInstructorByUserId($userId) {
        $sql = "SELECT i.*, u.email
            FROM {$this->instructorTable} i
            INNER JOIN {$this->user->getUserTable()} u ON u.id = i.user_id
            WHERE i.user_id = ? 
            LIMIT 1";
        $stmt = $this->userConn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $instructor = $result->fetch_assoc();
        $stmt->close();

        return $instructor ?: null;
    }

    // public function for get instructors by branch
    public function getInstructorsByBranch($branch) {
        $sql = "SELECT id, full_name FROM {$this->instructorTable} WHERE branch = ?";
        $stmt = $this->userConn->prepare($sql);
        $stmt->bind_param("s", $branch);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return $result;
    }

    // public function for get instructors by module, batch and branch
    public function getInstructorsByModuleBatchBranch($moduleId, $batchId, $branch) {
        $sql = "
            SELECT i.full_name AS instructor_name, 
                cmi.instructor_id AS instructor_id
            FROM {$this->courseModuleInstructorTable} cmi
            INNER JOIN {$this->instructorTable} i ON cmi.instructor_id = i.id
            WHERE cmi.module_id = ? 
            AND cmi.batch_id = ? 
            AND cmi.branch = ?
        ";

        $stmt = $this->userConn->prepare($sql);

        // 🔒 check before bind_param
        if (!$stmt) {
            error_log("SQL Error: " . $this->userConn->error); // log it
            return []; // return empty array instead of crashing
        }

        $stmt->bind_param("iis", $moduleId, $batchId, $branch);
        $stmt->execute();

        $result = $stmt->get_result();
        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }



}

?>