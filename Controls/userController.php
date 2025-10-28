<?php
require_once __DIR__ . "/../Models/user.php";

class UserController{
    private $userModel;
    private $passwordPattern = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/";

    public function __construct()
    {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // function for login logic
    public function login($userEmail, $userPassword)
    {
        // get user from user model
        $user = $this->userModel->getUser($userEmail);

        // validation
        if ($user && password_verify($userPassword, $user['password']))
        {
            // if (session_status() === PHP_SESSION_NONE) {
            //     session_start();
            // }

            session_regenerate_id(true); // prevents session fixation

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            // $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }

    // Redirect based on role - put inside controller
    public function redirectBasedOnRole() {
        switch ($_SESSION['role']) {
            case 'admin':
                return "/SkillPro/Views/Admin/adminDashboard.php";
            case 'student':
                return "/SkillPro/Views/Student/student_dashboard.php";
            case 'instructor':
                return "/SkillPro/Views/Instructor/instructor_dashboard.php";
            default:
                return "/SkillPro/index.php";
        }
    }

    // function for validate and update user password
    public function registerUser($userEmail, $userPassword, $userRole, $userStatus)
    {
        // Create user
        if ($this->userModel->createUser($userEmail, $userPassword, $userRole, $userStatus)) {
            // return "User registered successfully!";
            return true;
        } else {
            // return "Error: Could not register user.";
            return false;
        }
    }

    // Function for find user by email from user model
    public function findUserByEmail($userEmail)
    {
        // Search user email
        $user = $this->userModel->getUser($userEmail);
        if ($user) {
            return $user;
        } else {
            return false;
        }
    }

    // Function for get password pattern
    public function getPasswordPattern()
    {
        return $this->passwordPattern;
    }

    // Function for get last input id from user model
    public  function getTheLastUserID()
    {
        return $this->userModel->getLastUserID();
    }

    // Change password
    public function changePassword($email, $oldPassword, $newPassword, $confirmPassword) {
        // Find user
        $user = $this->userModel->getUser($email);
        if (!$user) {
            return ["success" => false, "message" => "User not found!"];
        }

        // Validate old password
        if (!password_verify($oldPassword, $user['password'])) {
            return ["success" => false, "message" => "Old password is incorrect!"];
        }

        // Validate new password strength
        if (!preg_match($this->passwordPattern, $newPassword)) {
            return ["success" => false, "message" => "Password must be at least 8 characters, include uppercase, number, and special character"];
        }

        // Confirm new password
        if ($newPassword !== $confirmPassword) {
            return ["success" => false, "message" => "New passwords do not match!"];
        }

        // Hash new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update in DB
        if ($this->userModel->updateUserPassword($email, $hashed)) {
            return ["success" => true, "message" => "Password updated successfully! Please Re-Login."];
        } else {
            return ["success" => false, "message" => "Error updating password!"];
        }

    }

    // Get pending students
    public function getPendingStudents() {
        return $this->userModel->getNonActiveStudents();
    }

    // Function for aprove students accounts
    public function aproveStudentAccount($stuEmail)
    {
        if ($this->userModel->approveStudent($stuEmail))
        {
            return ["success"=>true, "message"=>"Student approved successfully!"];
        }
        else {
            return ["success"=>false, "message"=>"Failed to approve student."];
        }
    }

    // fucntion for delete user by id
    public function deleteUserById($userID) {
        if ($this->userModel->deletUserById($userID))
        {
            return ["success"=>true, "message"=>"Student removed successfully"];
        }
        else {
            return ["success"=>false, "message"=>"Failed to remove this student"];
        }
    } 






    // function for count students logic
    public function getStudentStats() {
        return [
            'total' => $this->userModel->getTotalStudents(),
            'active' => $this->userModel->getActiveStudents()
        ];
    }

    // function for counts students wise province 
    public function getProvinceStats() {
        $data = $this->userModel->getStudentByProvince();
        $totalStudents = $this->userModel->getTotalStudents();

        foreach ($data as &$row) {
            $row['percentage'] = $totalStudents > 0 ? round(($row['total'] / $totalStudents) * 100, 2): 0;
        }

        return $data;
    }

    // Get user by ID
    public function getUserById($userId) {
        return $this->userModel->findById($userId); // call model method
    }



    


}
?>