<?php
require_once __DIR__ . "/../Controls/userController.php";
require_once __DIR__ . "/../Models/instructor.php";

class InstructorController {
    private $instrutorModel;
    private $userController;

    public function __construct()
    {
        $this->userController = new UserController();
        $this->instrutorModel = new Instructor();
    }

    // function for add instructor
    public function addInstructor($data, $file) {
        // Validation rules
        if (!preg_match("/^[a-zA-Z\s]{3,50}$/", $data['name'])) {
            return ["success" => false,  "message" => "Invalid name"];
        }

        if (!preg_match("/^[0-9]{10}$/", $data['mobile'])) {
            return ["success" => false,  "message" => "Invalid mobile number"];
        }

        if (strlen($data['bio']) < 10) {
            return ["success" => false,  "message" => "Bio must be at least 10 characters"];
        }

        if (empty($data['specialization'])) {
            return ["success" => false,  "message" => "Specialization required"];
        }

        if (empty($data['address'])) {
            return ["success" => false,  "message" => "Address required"];
        }

        if (empty($data['branch'])) {
            return ["success" => false,  "message" => "Branch required"];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ["success" => false,  "message" => "Invalid email"];
        }

        if (!preg_match($this->userController->getPasswordPattern(), $data['password'])) {
            return ["success" => false,  "message" => "Weak password"];
        }

        if (empty($data['gender'])) {
            return ["success" => false,  "message" => "Gender required"];
        }

        // ✅ Validate image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ["success" => false,  "message" => "Invalid image format (only JPG, JPEG, PNG allowed)"];
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2MB
            return ["success" => false,  "message" => "Image size too large (max 2MB)"];
        }

        // check if user already exists
        $existUser = $this->userController->findUserByEmail($data['email']);
        if ($existUser) {
            return ["success" => false, "message" => "Email already registered!"];
        }

        // check if mobile number already exists
        if ($this->instrutorModel->checkInstructorMobileExists($data['mobile'])) {
            return ["success" => false, "message" => "Mobile number already taken!"];
        }

        // password hashing
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // ✅ Create user first
        $userId = $this->userController->registerUser(
            $data['email'],
            $hashedPassword,
            'instructor',
            1,
        );

        if (!$userId) {
            return ["success" => false, "message" => "Failed to create user"];
        }

        $userId = $this->userController->getTheLastUserID();
        
        // ✅ Create instructor upload folder
        $instructorDir = __DIR__ . "/../Uploads/Instructors/" . $userId;
        if (!is_dir($instructorDir)) {
            mkdir($instructorDir, 0777, true);
        }

        // ✅ Handle file upload
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $instructorImageFileName = "profile_image." . $ext;
        $instructorImageTargetPath = $instructorDir . "/" . $instructorImageFileName;

        if (!move_uploaded_file($file['tmp_name'], $instructorImageTargetPath)) {
            return ["success" => false,  "message" => "Image upload failed"];
        }

        // ✅ Save relative path to DB
        $imagePathDB = "Instructors/" . $userId . "/" . $instructorImageFileName;

        // ✅ Insert instructor details
        $result = $this->instrutorModel->addInstructor(
            $userId,
            $data['name'],
            $data['mobile'],
            $data['bio'],
            $imagePathDB,
            $data['specialization'],
            $data['address'],
            $data['branch'],
            $data['gender']
        );

        if ($result) {
            return ["success" => true, "message" => "Instructor added successfully"];
        } else {
            return ["success" => false, "message" => "Failed to save instructor details"];
        }
    }

    // function for get all instructor details
    public function getAllInstructors() {
        return $this->instrutorModel->getAllInstructors();
    }

    // function for get total of all instructor
    public function getTotalInstructors() {
        return $this->instrutorModel->getTotalInstructors();
    }

    //  function for delete instructor
    public function deleteInstructor($instructorUserId) {
        $result = $this->instrutorModel->deleteInstructorById($instructorUserId);
        if ($result) {
            return ["success" => true, "message" => "Instructor deleted successfully"];
        } else {
            return ["success" => false, "message" => "Failed to delete instructor"];
        }
    }

    public function getInstructorByUserId($userID) {
        return $this->instrutorModel->getInstructorByUserId($userID);
    }

    // function for change instructor account password
    public function changeInstructorPassword($data)
    {
        return $this->userController->changePassword($data['email'], $data['old_password'], $data['new_password'], $data['confirm_password']);
    }

    // function for update instructor
    public function updateInstructorDetails($data, $file) {

        // Validation rules
        if (empty($data['instId']) || empty($data['userId'])) {
            return ["success" => false,  "message" => "Unable to change instructor details"];
        }

        if (!preg_match("/^[a-zA-Z\s]{3,50}$/", $data['name'])) {
            return ["success" => false,  "message" => "Invalid name"];
        }

        if (!preg_match("/^[0-9]{10}$/", $data['mobile'])) {
            return ["success" => false,  "message" => "Invalid mobile number"];
        }

        if (strlen($data['bio']) < 10) {
            return ["success" => false,  "message" => "Bio must be at least 10 characters"];
        }

        if (empty($data['spec'])) {
            return ["success" => false,  "message" => "Specialization required"];
        }

        if (empty($data['address'])) {
            return ["success" => false,  "message" => "Address required"];
        }

        if (empty($data['branch'])) {
            return ["success" => false,  "message" => "Branch required"];
        }

        if (empty($data['gender'])) {
            return ["success" => false,  "message" => "Gender required"];
        }

        // check if mobile number already exists
        $mobileResult = $this->instrutorModel->checkInstructorMobileExists($data['mobile']);
        if ($mobileResult && $mobileResult['id'] != $data['instId']) {
            // mobile exists and belongs to another instructor
            return ["success" => false, "message" => "Mobile number already taken!"];
        }


        // ✅ Validate image
        $imagePathDB = null;
        if (empty($file) || empty($file['name'])) {
            // Keep existing image if already present in DB
            $currentInstructor = $this->instrutorModel->getInstructorByUserId($data['userId']);
            $imagePathDB = $currentInstructor['image_path'] ?? null;
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file['type'], $allowedTypes)) {
                return ["success" => false,  "message" => "Invalid image format (only JPG, JPEG, PNG allowed)"];
            }
    
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB
                return ["success" => false,  "message" => "Image size too large (max 2MB)"];
            }

            
            // ✅ Handle file upload
            $instructorDir = __DIR__ . "/../Uploads/Instructors/" . $data['userId'];
            if (!is_dir($instructorDir)) {
                mkdir($instructorDir, 0777, true);
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $instructorImageFileName = "profile_image." . $ext;
            $instructorImageTargetPath = $instructorDir . "/" . $instructorImageFileName;
    
            if (!move_uploaded_file($file['tmp_name'], $instructorImageTargetPath)) {
                return ["success" => false,  "message" => "Image change failed. Failed to update instructor details"];
            }
    
            // ✅ Save relative path to DB
            $imagePathDB = "Instructors/" . $data['userId'] . "/" . $instructorImageFileName;
        }

        $result = $this->instrutorModel->updateInstructor($data['name'], $data['mobile'], $data['bio'], $imagePathDB, $data['spec'], $data['address'], $data['branch'], $data['gender'], $data['instId']);
        if($result) {
            return ["success" => true, "message" => "Instructor details updated successfully"];
        } else {
            return ["success" => false, "message" => "Failed to update instructor details"];
        }
    }

    // function for get instructors by branch
    public function getInstructorsByBranch($branch) {
        $result = $this->instrutorModel->getInstructorsByBranch($branch);
        if (empty($result)) {
            echo json_encode([]);
            exit;
        } else {
            echo json_encode($result);
            exit;
        }
    }

    // function for get instructors by module, batch and branch
    public function getInstructorsByModuleBatchBranch($moduleId, $batchId ,$branch) {
        $result = $this->instrutorModel->getInstructorsByModuleBatchBranch($moduleId, $batchId ,$branch);
        
        if (empty($result)) {
            echo json_encode([]);
            exit;
        } else {
            echo json_encode($result);
            exit;
        }
    }

}