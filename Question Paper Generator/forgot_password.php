<?php
session_start();
require_once 'includes/db_connect.php';
// 1. Include our new mail config file
require_once 'includes/mail_config.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Auto-logout user if they land here
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

$step = $_SESSION['forgot_step'] ?? 1;
$error = $message = "";
$email = $_SESSION['forgot_email'] ?? '';

// --- Handle Form Submissions (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($step == 1 && isset($_POST['email'])) {
        // --- STEP 1: User submitted their email ---
        $email = trim($_POST['email']);
        $sql = "SELECT id FROM user_info WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 1) {
            $otp = rand(100000, 999999);
            $expiry = time() + 300; // 5 minutes

            // 2. --- SEND THE REAL EMAIL ---
            $send_result = send_otp_email($email, $otp);
            
            if ($send_result === true) {
                // Email sent, save details to session
                $_SESSION['forgot_email'] = $email;
                $_SESSION['forgot_otp'] = $otp;
                $_SESSION['forgot_expiry'] = $expiry;
                $_SESSION['forgot_step'] = 2;
                $step = 2;
                $message = "An OTP has been sent to your email address.";
            } else {
                // Email failed to send
                $error = "Could not send email. " . $send_result;
            }
        } else {
            $error = "No account found with that email address.";
        }

    } elseif ($step == 2 && isset($_POST['otp'])) {
        // --- STEP 2: User submitted OTP ---
        if (time() > $_SESSION['forgot_expiry']) {
            $error = "Your OTP has expired. Please request a new one.";
            $_SESSION['forgot_step'] = 1;
            $step = 1;
        } else {
            $otp = trim($_POST['otp']);
            if ($otp == $_SESSION['forgot_otp']) {
                $_SESSION['forgot_step'] = 3;
                $step = 3;
            } else {
                $error = "Invalid OTP. Please try again.";
            }
        }

    } elseif ($step == 3 && isset($_POST['password'])) {
        // --- STEP 3: User submitted new password ---
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirm_password)) {
            $error = "Please fill out both password fields.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "The new passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $email = $_SESSION['forgot_email'];
            
            $sql = "UPDATE user_info SET password = :password WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':password' => $hashed_password, ':email' => $email]);
            
            session_unset();
            session_destroy();
            
            $message = "Your password has been reset successfully! <a href='index.php'>Click here to login.</a>";
            $step = 4;
        }
    }
}

// --- Handle Messages for GET request ---
if ($step == 2) {
    if(isset($_SESSION['otp_resent'])) {
        $message = "A new OTP has been sent to your email.";
        unset($_SESSION['otp_resent']);
    } elseif (empty($message)) {
        $message = "Enter the OTP sent to your email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="bg-image"></div>
  <div class="overlay"> </div>
  <div class="login-box" id="forgot_form">
    
    <?php if ($step == 1): ?>
        <h2>Forgot Password</h2>
        <p style="text-align: center; margin-bottom: 15px;">Enter your email to get started.</p>
        <form action="forgot_password.php" method="POST">
            <?php if ($error): ?><p style="color: red; text-align: center;"><?php echo $error; ?></p><?php endif; ?>
            <input type="email" name="email" placeholder="Your Email Address" value="<?php echo htmlspecialchars($email); ?>" required>
            <input type="submit" value="Send OTP">
        </form>
        <p id="p">Remember your password? <a href="index.php">Login</a></p>

    <?php elseif ($step == 2): ?>
        <h2>Enter OTP</h2>
        <p style="text-align: center; margin-bottom: 15px;"><?php echo $message; ?></p>
        
        <form action="forgot_password.php" method="POST">
            <?php if ($error): ?><p style="color: red; text-align: center;"><?php echo $error; ?></p><?php endif; ?>
            <input type="text" name="otp" placeholder="6-Digit OTP" required>
            <input type="submit" value="Verify OTP">
        </form>
        <p id="p">
            <a href="resend_otp.php" style="float: right;">Resend OTP</a>
            <a href="index.php">Cancel</a>
        </p>

    <?php elseif ($step == 3): ?>
        <h2>Reset Password</h2>
        <p style="text-align: center; margin-bottom: 15px;">Enter your new password.</p>
        <form action="forgot_password.php" method="POST">
            <?php if ($error): ?><p style="color: red; text-align: center;"><?php echo $error; ?></p><?php endif; ?>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="New Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
            </div>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
            </div>
            <input type="submit" value="Reset Password">
        </form>

    <?php elseif ($step == 4): ?>
        <h2>Success!</h2>
        <p style="color: green; text-align: center; line-height: 1.5;"><?php echo $message; ?></p>
    
    <?php endif; ?>

  </div>
  
  <?php if ($step == 3): ?>
  <script>
  function togglePassword(inputId, el) {
      const input = document.getElementById(inputId);
      if (input.type === "password") {
          input.type = "text";
          el.innerHTML = "🙈"; // Hide icon
      } else {
          input.type = "password";
          el.innerHTML = "👁️"; // Show icon
      }
  }
  </script>
  <?php endif; ?>
  
</body>
</html>