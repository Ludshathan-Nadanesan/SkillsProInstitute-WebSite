<?php
require_once __DIR__ . "/../Config/database.php";

class User{
    private $db;
    private $conn;

    // tables
    private $table = "users";
    private $studentTable = "student_details";


    public function __construct()
    {
        $this->db = new Database(); // object for Database class from database config
        $this->conn = $this->db->getConnection(); // connection with database using getconnection function from database config
    }

    // Function for Getting User Connection 
    public function getUserConnection()
    {
        return $this->conn;
    }

    // Function for Get User By Email
    public function getUser($userEmail)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->bind_param('s',$userEmail);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Fucntion for Set User Password
    public function updateUserPassword($userEmail, $newPassword)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = ? WHERE email = ? LIMIT 1");
        $stmt->bind_param('ss', $newPassword, $userEmail);
        return $stmt->execute();   
    }

    // Function for Create User Account
    public function createUser($userEmail, $userPassword, $userRole, $userStatus)
    {
        // insert into users table 
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (email, password, role, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $userEmail, $userPassword, $userRole, $userStatus);
        return $stmt->execute();
    }

    // Function for Find Last User ID
    public function getLastUserID()
    {
        return $this->conn->insert_id;
    }

    // Get non-active students
    public function getNonActiveStudents() {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.email, s.*
            FROM {$this->table} u
            INNER JOIN {$this->studentTable} s ON s.user_id = u.id
            WHERE u.role='student' AND u.status=0
            ORDER BY u.id DESC"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        while($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        return $students;
    }

     // Function for Approve student (set status = 1)
    public function approveStudent($stuEmail) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status=1 WHERE email=?");
        $stmt->bind_param("s", $stuEmail);
        return $stmt->execute();
    }

    // Function for getting user table name
    public function getUserTable()
    {
        return $this->table;
    }

    // Function for deletting user
    public function deletUserById($userId)
    {
        // Delete user
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result; // true if success
    }









    // Add student details after creating user
    public function createStudentDetails($userId, $fullName, $nic, $dob, $gender, $streetAddr, $province, $mobile)
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->studentTable} (user_id, full_name, nic_number, dob, gender, street_address, province, mobile_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssssi', $userId, $fullName, $nic, $dob, $gender, $streetAddr, $province, $mobile);
        return $stmt->execute();
    }

    // Get total students
    public function getTotalStudents() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->table} WHERE role='student'");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    // Get active students
    public function getActiveStudents() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS active FROM {$this->table} WHERE role='student' AND status=1");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['active'];
    }

   

    // get student count by province
    public function getStudentByProvince() {
        $sql = "SELECT s.province, COUNT(*) AS total
                FROM student_details s
                INNER JOIN {$this->table} u ON u.id = s.user_id
                WHERE u.role = 'student'
                GROUP BY s.province";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];   // ensure array
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;   // push row into array
        }
        return $data; // always array
    }


    // Find user by ID
    public function findById($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user;
    }


    // Update password by email
    public function updatePasswordByEmail($email, $newPassword) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $newPassword, $email);
        return $stmt->execute();
    } 










}

?>