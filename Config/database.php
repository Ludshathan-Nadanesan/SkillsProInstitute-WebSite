<?php
class Database
{
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "skillprodatabase";

    public $conn;

    // function for connection with db 
    public function getConnection() {

        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname
        );

        if ($this->conn->connect_error) {
            $errorCode = $this->conn->connect_errno;
            $errorMsg  = $this->conn->connect_error;

            // Add timestamp
            $timestamp = date("Y-m-d H:i:s"); // e.g., 2025-09-05 13:45:00
            $logMessage = "[$timestamp] DB Error [$errorCode]: $errorMsg\n";

            // Log to file
            error_log($logMessage, 3, __DIR__ . "/../Logs/db_errors.log");

            // Send proper HTTP response code
            http_response_code(503);

            // Show user-friendly error page
            header("Location: /SkillPro/Views/Error/503Error.php");
            exit;
        }

        return $this->conn;
    }
}
?>
