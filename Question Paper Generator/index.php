<?php
session_start();

// PREVENT BROWSER CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// "Gatekeeper"
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Auto-logout user if they land here
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/style.css" rel="stylesheet">
    <title>Question Paper Generator - Login</title>
</head>
<body> 
    <div class="bg-image"></div>
    <div class="overlay"></div>
        <div class="login-box">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <input type="text" name="email" placeholder="Email" required>
                
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
                </div>
                <input type="submit" value="Login">
            </form>
          <p id="p">Don't have an account? <a href="register.php">Register</a></p>

          <p id="p-forgot"><a href="forgot_password.php">Forgot Password?</a></p>
        </div>   

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
</body>
</html>