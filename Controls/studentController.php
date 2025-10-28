<?php
require_once __DIR__ . "/../Controls/userController.php";
require_once __DIR__ . "/../Models/student.php";

class StudentController {
    private $studentModel;
    private $userController;
    private $hashedPassword;

    public function __construct()
    {
        $this->userController = new UserController();
        $this->studentModel = new Student();
    }

    // Function for register student
    public function register($data)
    {
        // validate student full name
        if ($data['full_name'] === "" || !preg_match("/^[A-Za-z\s]+$/", $data['full_name'])) {
            return ["success" => false, "message" => "Full name is invalid!"];
        }

        // validate dob
        if (empty($data['dob'])) {
            return ["success" => false, "message" => "Date of Birth required!"];
        } else {
            $dob = $data['dob'];
            $dobDate = DateTime::createFromFormat('Y-m-d', $dob); // yyyy-mm-dd
            
            // Check parsing success & exact format
            if (!$dobDate || $dobDate->format('Y-m-d') !== $dob) {
                return ["success" => false, "message" => "Invalid Date of Birth format!"];
            }

            $today = new DateTime();

            // Future date check
            if ($dobDate > $today) {
                return ["success" => false, "message" => "Date of Birth cannot be in the future!"];
            }

            $age = $today->diff($dobDate)->y; // calculate age

            if ($age < 18) {
                return ["success" => false, "message" => "You must be at least 18 years old!"];
            }
        }


        // validate gender
        if (empty($data['gender'])) {
            return ["success" => false, "message" => "Please select a gender!"];
        } else {
            $gender = $data['gender'];
            $validGenders = ["Male", "Female"];

            if (!in_array($gender, $validGenders)) {
                return ["success" => false, "message" => "Please select a valid gender option!"];
            }
        }

        // validate Nic
        if (!preg_match("/^\d{9}[Vv]$|^\d{12}$/", $data['nic_number'])) {
            return ["success" => false, "message" => "Invalid NIC format!"];
        }

        // validate street
        if (empty($data['street_address'])) {
            return ["success" => false, "message" => "Street address required!"];
        }

        // validate province
        if (empty($data['province'])) {
            return ["success" => false, "message" => "Please select a province!"];
        } else {
            $province = $data['province'];
            $validProvince = ['Northern', 'Western', 'Southern', 'Central', 'North Western', 'Sabragamuwa', 'Eastern', 'Uva', 'North Central'];

            if (!in_array($province, $validProvince)) {
                return ["success" => false, "message" => "Please select a valid province option!"];
            }
        }

        // validate mobile number
        if (!preg_match('/^07\d{8}$/', $data['mobile_number'])) {
            return ["success" => false, "message" => "Mobile must be 10 digits!"];
        }
            


        // validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Invalid email format!"];
        }

        // check if user already exists
        $existUser = $this->userController->findUserByEmail($data['email']);
        if ($existUser) {
            return ["success" => false, "message" => "Email already registered!"];
        }

        // check if nic alredy exists
        if ($this->studentModel->checkStudentNicExists($data['nic_number'])) {
            return ["success" => false, "message" => "NIC number already taken!"];

        }

        // check if mobile number already exists
        if ($this->studentModel->checkStudentMobileExists($data['mobile_number'])) {
            return ["success" => false, "message" => "Mobile number already taken!"];

        }

        // password validation (at least 8 chars, 1 uppercase, 1 number, 1 special char)
        if (!preg_match($this->userController->getPasswordPattern(), $data['password'])) {
            return ["success" => false, "message" => "Password must be at least 8 characters, include 1 uppercase, 1 number, and 1 special character."];
        }

        // password hashing
        $this->hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // create user
        if ($this->userController->registerUser($data['email'], $this->hashedPassword, $role="student", 0)) {
            // get last created user id
            $userId = $this->userController->getTheLastUserID();

            // insert student details
            $success = $this->studentModel->createStudent(
                $userId,
                $data['full_name'],
                $data['nic_number'],
                $data['dob'],
                $data['gender'],
                $data['street_address'],
                $data['province'],
                $data['mobile_number']
            );

            if ($success) {
                return ["success" => true, "message" => "Registration successful!"];
            } else {
                return ["success" => false, "message" => "Registration Failed!"];
            }

        } else {
            return ["success" => false, "message" => "Registration Failed!"];
        }
    }

    // Function for change student details
    public function changeStudentDetails($data)
    {
        // validate student full name
        if ($data['full_name'] === "" || !preg_match("/^[A-Za-z\s]+$/", $data['full_name'])) {
            return ["success" => false, "message" => "Full name is invalid!"];
        }

        // validate dob
        if (empty($data['dob'])) {
            return ["success" => false, "message" => "Date of Birth required!"];
        } else {
            $dob = $data['dob'];
            $dobDate = DateTime::createFromFormat('Y-m-d', $dob); // yyyy-mm-dd
            
            // Check parsing success & exact format
            if (!$dobDate || $dobDate->format('Y-m-d') !== $dob) {
                return ["success" => false, "message" => "Invalid Date of Birth format!"];
            }

            $today = new DateTime();

            // Future date check
            if ($dobDate > $today) {
                return ["success" => false, "message" => "Date of Birth cannot be in the future!"];
            }

            $age = $today->diff($dobDate)->y; // calculate age

            if ($age < 18) {
                return ["success" => false, "message" => "You must be at least 18 years old!"];
            }
        }


        // validate gender
        if (empty($data['gender'])) {
            return ["success" => false, "message" => "Please select a gender!"];
        } else {
            $gender = $data['gender'];
            $validGenders = ["Male", "Female"];

            if (!in_array($gender, $validGenders)) {
                return ["success" => false, "message" => "Please select a valid gender option!"];
            }
        }

        // validate street
        if (empty($data['street_address'])) {
            return ["success" => false, "message" => "Street address required!"];
        }

        // validate province
        if (empty($data['province'])) {
            return ["success" => false, "message" => "Please select a province!"];
        } else {
            $province = $data['province'];
            $validProvince = ['Northern', 'Western', 'Southern', 'Central', 'North Western', 'Sabragamuwa', 'Eastern', 'Uva', 'North Central'];

            if (!in_array($province, $validProvince)) {
                return ["success" => false, "message" => "Please select a valid province option!"];
            }
        }

        // validate Nic
        if (!preg_match("/^\d{9}[Vv]$|^\d{12}$/", $data['nic_number'])) {
            return ["success" => false, "message" => "Invalid NIC format!"];
        }

        // validate mobile number
        if (!preg_match('/^07\d{8}$/', $data['mobile_number'])) {
            return ["success" => false, "message" => "Mobile must be 10 digits!"];
        }
            
        // getting stuent details for verify
        $studentDetails = $this->studentModel->getStudent($data['email']);

        // check if nic number is not same to student old nic number
        if (!($studentDetails['nic_number'] === $data['nic_number'])) {
            // check if nic alredy exists
            if ($this->studentModel->checkStudentNicExists($data['nic_number'])) {
                return ["success" => false, "message" => "NIC number already taken!"];
            }
        } 

        // check if mobile number is not same to student old mobile number
        if (!($studentDetails['mobile_number'] === $data['mobile_number'])) {
            // check if the mobile number exits or not
            if ($this->studentModel->checkStudentMobileExists($data['mobile_number'])) {
                return ["success" => false, "message" => "Mobile number already taken!"];
            }
        }

        $stuId = $data['student_id'];
        $stuFullName = $data['full_name'];
        $stuNic = $data['nic_number'];
        $stuDOB = $data['dob'];
        $stuGender = $data['gender'];
        $stuStreetAddr = $data['street_address'];
        $stuProvince = $data['province'];
        $stuMobile = $data['mobile_number'];
        $stuImagePath = $data['image_path'];


        if ($this->studentModel->updateStudent($stuId, $stuFullName, $stuNic, $stuDOB, $stuGender, $stuStreetAddr, $stuProvince, $stuMobile, $stuImagePath))
        {
            return ["success" => true, "message" => "Update Details Successfully!"];
        } else {
            return ["success" => false, "message" => "Update Detaails Failed!"];
        }
    }

    // Function for change stuent account password
    public function changeStudentPassword($data)
    {
        return $this->userController->changePassword($data['email'], $data['old_password'], $data['new_password'], $data['confirm_password']);
    }

    // Function for get student details
    public function getStudentDetails($stuEmail)
    {
        $stdnt = $this->studentModel->getStudent($stuEmail);
        if($stdnt) {
            return ["success" => true, "data" => $stdnt];
        } else {
            return ["success" => false, "message" => "Student Details Not Load!"];
        }
    } 

    // function for get student course registration details
    public function getStudentCourseRegistrationDetails() {
        $details = $this->studentModel->getStudentCourseRegistrationDetails();
        if ($details) {
            return $details;
        } else {
            return null;
        }
    }

    // function for get all student details
    public function getAllStudents() {
        $details = $this->studentModel->getAllStudents();
        if ($details) {
            return $details;
        } else {
            return null;
        }
    }

    // function for delete Student
    public function deleteStudentByUserID($userID) {
        if (empty($userID)) {
            return ['sucess' => false, 'message' => 'Failed to remove this student.'];
        }

        $result = $this->userController->deleteUserById($userID);
        if ($result['success']) {
            return ['sucess' => true, 'message' => 'Student removed succesfully'];
        } else {
            return ['sucess' => false, 'message' => 'Failed to remove this student'];
        }

    }

    
    // function for get all non students Queries
    public function getAllNonStudentQueries() {
        return $this->studentModel->getAllNonStudentQueries();
    }

    // fucntion foer change non stduent query status
    public function changeNonStudentQueryStatus($id, $status) {
        $result = $this->studentModel->changeNonStudentQueryStatus($id, $status);
        if ($result) {
            return ['sucess' => true, 'message' => 'Query solved successfully'];
        } else {
            return ['sucess' => false, 'message' => 'Failed to solved query'];
        } 
    }

    // fucntion for add non student query
    public function addNonStudentQuery($data) {
        // Validate name
        if (strlen($data['name']) < 3) {
            return ['sucess' => false, 'message' => "Full name must be at least 3 characters."];
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['sucess' => false, 'message' => "Invalid email address."];
        }

        // Validate course
        if (empty($data['course'])) {
            return ['sucess' => false, 'message' => "Please select a course."];
        }

        // Validate message
        if (strlen($data['message']) < 10) {
            return ['sucess' => false, 'message' => "Message must be at least 10 characters."];
        }

        if ($this->studentModel->addNonStudentQuery($data['name'], $data['email'], $data['course'], $data['message'])) {
            return ['sucess' => true, 'message' => "Query Send Successfully."];
        } else {
            return ['sucess' => false, 'message' => "Failed to Send Query!"];
        }
    }

    // fucntion for add student query
    public function addStudentInquiry($studentId, $message) {
        // Validate message
        if (strlen($message) < 10) {
            return ['sucess' => false, 'message' => "Message must be at least 10 characters."];
        }

        if ($this->studentModel->addStudentInquiry($studentId, $message)) {
            return ['sucess' => true, 'message' => "Query Send Successfully."];
        } else {
            return ['sucess' => false, 'message' => "Failed to Send Query!"];
        }
    }

    // fucntion foer change stduent query status
    public function solvedStudentInquiry($inqId) {
        $result = $this->studentModel->solvedStudentInquiry($inqId);
        if ($result) {
            return ['sucess' => true, 'message' => 'Query solved successfully'];
        } else {
            return ['sucess' => false, 'message' => 'Failed to solved query'];
        } 
    }

    // fucntion for get all student query
    public function getAllStudentInquiry() {
        return $this->studentModel->getAllStudentInquiry();
    }
}
?>