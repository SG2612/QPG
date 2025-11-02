<?php
session_start();

// PREVENT BROWSER CACHING
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// "Gatekeeper"
if (isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true) {
    // Auto-logout admin if they land here
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body class="admin-login-body">
    <div class="bg-image"></div>
    <div class="login-container">
        <form action="auth.php" method="POST" class="login-form">
            <h2>Admin Panel Login</h2>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="admin-password-container">
                    <input type="password" id="password" name="password" required>
                    <span class="admin-toggle-password" onclick="togglePassword('password', this)">👁️</span>
                </div>
            </div>
            <button type="submit">Login</button>
        </form>
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