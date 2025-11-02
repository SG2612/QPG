<?php
session_start();
require_once '../includes/db_connect.php';

// First, check if the user is logged in as an admin. If not, redirect to the admin login page.
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    // UPDATED to index.php
    header("location: index.php");
    exit;
}

$username = $password = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $message = "Please enter a username.";
    } else {
        $sql = "SELECT id FROM admin_info WHERE username = :username";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":username", trim($_POST["username"]), PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $message = "This username is already taken.";
            } else {
                $username = trim($_POST["username"]);
            }
            unset($stmt);
        }
    }

    if (empty(trim($_POST["password"]))) {
        $message = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $message = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($message)) {
        $sql = "INSERT INTO admin_info (username, password) VALUES (:username, :password)";
        if ($stmt = $conn->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $message = "New admin registered successfully!";
            } else {
                $message = "Something went wrong.";
            }
            unset($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Admin</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body style="display: block; height: auto; background-color: #f0f2f5;">
    <header class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="dashboard.php" class="header-link">View Users</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>
    
    <div class="form-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
            <h2>Register New Admin</h2>
            <p>Create a new administrator account.</p>
            
            <?php if(!empty($message)): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label for="username">New Admin Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">New Admin Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Create Admin</button>
        </form>
    </div>
</body>
</html>