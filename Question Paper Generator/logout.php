<?php
session_start();

// Send headers to prevent caching of the logout page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
 
// Unset all of the session variables
$_SESSION = array();
 
// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();
 
// UPDATED to index.php
header("location: index.php");
exit;
?>