<?php
// PHPMailer Namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load the 3 files manually (since we are not using Composer)
require_once __DIR__ . '/Exception.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';


// --- YOUR CREDENTIALS ---
// Get these from your Google Account "App Passwords"
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'generatorquestionpaper6@gmail.com'); // <-- ★★★ REPLACE THIS ★★★
define('SMTP_PASS', 'npqo qvko acle kjez'); // <-- ★★★ REPLACE THIS ★★★
define('SMTP_PORT', 587); 
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS); 
// --- END CREDENTIALS ---


// Function to send the OTP email
function send_otp_email($recipient_email, $otp) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        //Recipients
        $mail->setFrom(SMTP_USER, 'QPG Admin');
        $mail->addAddress($recipient_email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body    = "Hello,<br><br>Your One-Time Password (OTP) for resetting your password is: <h2><b>{$otp}</b></h2><br>This OTP is valid for 5 minutes.<br><br>If you did not request this, please ignore this email.";
        $mail->AltBody = 'Your OTP is: ' . $otp;

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>