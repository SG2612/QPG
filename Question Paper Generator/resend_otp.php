<?php
session_start();
// 1. Include our new mail config file
require_once 'includes/mail_config.php';

// Check if user is on the correct step
if (!isset($_SESSION['forgot_step']) || $_SESSION['forgot_step'] != 2 || !isset($_SESSION['forgot_email'])) {
    header('Location: forgot_password.php');
    exit;
}

$otp = rand(100000, 999999);
$expiry = time() + 300; // 5 minutes

// 2. --- SEND THE REAL EMAIL ---
$email = $_SESSION['forgot_email'];
$send_result = send_otp_email($email, $otp);

if ($send_result === true) {
    // Email sent, update session
    $_SESSION['forgot_otp'] = $otp;
    $_SESSION['forgot_expiry'] = $expiry;
    $_SESSION['otp_resent'] = true;
} else {
    // Email failed
    error_log("Resend OTP failed: " . $send_result);
    // We can't show an error here easily, so we just log it
}

// Redirect back to the OTP entry page
header('Location: forgot_password.php');
exit;
?>