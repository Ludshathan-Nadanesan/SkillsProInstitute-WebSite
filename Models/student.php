<?php
require_once __DIR__ . "/user.php";
require_once __DIR__ . "/course.php";

class Student {
    private $studentConn;
    private $student;
    private $studentTable = "student_details";
    private $studentRegistrationTable ="student_registrations";
    private $nonStudentQueries = "non_student_inquiry";
    private $inquiriesTable = "student_inquiries";
    private $courseModel;

    public function __construct()
    {
        $this->student = new User();
        $this->studentConn = $this->student->getUserConnection();
        $this->courseModel = new Course();
    }
    
    // Function for Create student
    public function createStudent($userId, $fullName, $nic, $dob, $gender, $streetAddr, $province, $mobile)
    {
        $sql = "INSERT INTO {$this->studentTable} (user_id, full_name, nic_number, dob, gender, street_address, province, mobile_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->bind_param('isssssss', $userId, $fullName, $nic, $dob, $gender, $streetAddr, $province, $mobile);
        return $stmt->execute();
    }

    // Function for Update Student Details
    public function updateStudent($stuId, $studentFullName, $studentNic, $studentDob, $studentGender, $studentStreetAddr, $studentProvince, $studentMobile, $studentImage)
    {
        $sql = "UPDATE {$this->studentTable} SET full_name = ?, nic_number = ?, dob = ?, gender = ?, street_address = ?, province = ?, mobile_number = ?, image_path = ? WHERE id=? LIMIT 1";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->bind_param('ssssssssi', $studentFullName, $studentNic, $studentDob, $studentGender, $studentStreetAddr, $studentProvince, $studentMobile, $studentImage, $stuId);
        return $stmt->execute();
    }

    // Function for check exists of student nic number 
    public function checkStudentNicExists($nicNumber) {
        $sql = "SELECT id FROM {$this->studentTable} WHERE nic_number = ? LIMIT 1";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->bind_param("s", $nicNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    // Function for check exists of student mobile number 
    public function checkStudentMobileExists($mobileNumber) {
        $sql = "SELECT id FROM {$this->studentTable} WHERE mobile_number = ? LIMIT 1";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->bind_param("s", $mobileNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Function for get student course registration details
    public function getStudentCourseRegistrationDetails() {
        $sql = "
                SELECT 
                    sd.full_name AS student_name,
                    u.email AS student_email,
                    sd.nic_number AS student_nic,
                    c.id AS course_id,
                    c.name AS course_name,
                    sr.id AS id,
                    sr.branch,
                    sr.registered_at,
                    sr.status
                FROM {$this->studentRegistrationTable} sr
                INNER JOIN {$this->studentTable} sd ON sr.student_id = sd.id
                INNER JOIN {$this->student->getUserTable()} u ON sd.user_id = u.id
                INNER JOIN {$this->courseModel->getCourseTable()} c ON sr.course_id = c.id
                ORDER BY sr.id DESC
            ";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // function for get all students details
    public function getAllStudents() {
        $sql = "SELECT s.*, u.email
                FROM {$this->studentTable} s
                INNER JOIN {$this->student->getUserTable()} u 
                ON s.user_id = u.id
                WHERE u.role = 'student'";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // fucntion for Add inquiry from Student Dashboard
    public function addStudentInquiry($studentId, $message) {
        $sql = "INSERT INTO {$this->inquiriesTable} (student_id, message) VALUES (?, ?)";
        $stmt = $this->studentConn->prepare($sql);
        if (!$stmt) {
            echo "Prepare failed: " . $this->studentConn->error;
            return false;
        }

        $stmt->bind_param("is", $studentId, $message);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        }
        $stmt->close();

        return $result ? true : false;
    }


    // function for Admin marks inquiry as Solved
    public function solvedStudentInquiry($inqId) {
        $sql = "UPDATE {$this->inquiriesTable} SET status = 'Solved' WHERE id = ? LIMIT 1";
        $stmt = $this->studentConn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("i", $inqId);
        $result = $stmt->execute();
        $stmt->close();

        return $result ? true : false;
    }

    // function for Admin get all inquiries
    public function getAllStudentInquiry() {
        $sql = "SELECT 
                    i.id AS id,
                    u.email AS student_email,
                    i.student_id AS student_id,
                    s.full_name AS student_name,
                    i.message AS message,
                    i.asked_at AS asked_at,
                    i.status AS status
                FROM {$this->inquiriesTable} i
                INNER JOIN {$this->studentTable} s ON i.student_id = s.id
                INNER JOIN {$this->student->getUserTable()} u ON s.user_id = u.id
                ORDER BY i.id DESC";

        $result = $this->studentConn->query($sql);
        $inquiries = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $inquiries[] = $row;
            }
        }
        return $inquiries;
    }

    // function for get all non students Queries
    public function getAllNonStudentQueries() {
        $sql = "SELECT * FROM {$this->nonStudentQueries} ORDER BY id DESC";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // function for change status of non student queries
    public function changeNonStudentQueryStatus($id,$status) {
        $sql = "UPDATE {$this->nonStudentQueries} SET status=? WHERE id=? LIMIT 1";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->bind_param('si', $status, $id);
        return $stmt->execute();
    }

    // function for add non student qauery
    public function addNonStudentQuery($name, $email, $course, $message) {
        $sql = "INSERT INTO {$this->nonStudentQueries} (full_name, email, course_name, message, asked_at, status) VALUES (?, ?, ?, ?, NOW(), 'New')";
        $stmt = $this->studentConn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $course, $message);
        return $stmt->execute();
    }

    // Function for get student details
    public function getStudent($studentEmail)
    {
        $user = $this->student->getUser($studentEmail);
        if ($user) {
            $sql = "SELECT s.*,  u.email,  u.password  FROM {$this->studentTable} AS s  JOIN {$this->student->getUserTable()} AS u  ON s.user_id = u.id  WHERE s.user_id = ?  LIMIT 1";
            $stmt = $this->studentConn->prepare($sql);
            $stmt->bind_param('s', $user["id"]);
            $stmt->execute();
            $studentUser = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $studentUser;
        } else {
            return false;
        }
    }



}
?>