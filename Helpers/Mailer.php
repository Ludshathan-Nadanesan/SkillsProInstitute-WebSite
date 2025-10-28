<?php
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = 'skillproinstitute9@gmail.com'; // 🔹 change this
            $this->mail->Password   = 'elusxqayysyxjpfh';  // 🔹 app password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = 587;

            // Default sender
            $this->mail->setFrom('skillproinstitute9@gmail.com', 'SkillPro Institute');

            // Charset (for Unicode)
            $this->mail->CharSet = 'UTF-8';

        } catch (Exception $e) {
            error_log("Mailer setup error: " . $e->getMessage());
        }
    }

    // ✅ Send email function
    public function sendMail($toEmail, $toName, $subject, $body, $altBody = '') {
        try {
            // Clear old recipients
            $this->mail->clearAddresses();

            // Add new recipient
            $this->mail->addAddress($toEmail, $toName);

            // Email content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody ?: strip_tags($body);

            // Send
            if ($this->mail->send()) {
                return ["success" => true, "message" => "Email sent to $toEmail"];
            } else {
                return ["success" => false, "message" => "Mailer Error: " . $this->mail->ErrorInfo];
            }

        } catch (Exception $e) {
            return ["success" => false, "message" => "Exception: " . $e->getMessage()];
        }
    }

    // ✅ Add Embedded Image Support
    public function addEmbeddedImage($path, $cid, $name = '') {
        return $this->mail->addEmbeddedImage($path, $cid, $name);
    }
}
