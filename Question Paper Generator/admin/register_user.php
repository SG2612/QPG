<?php
session_start();
require_once '../includes/db_connect.php';

// First, check if the user is logged in as an admin.
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: index.php");
    exit;
}

$name = $email = $mobile = $password = "";
$message = "";

// Process form data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate form data
    if (empty(trim($_POST["name"])) || empty(trim($_POST["email"])) || empty(trim($_POST["password"]))) {
        $message = "Name, Email, and Password fields are required.";
    } else {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $mobile = trim($_POST["mobile"]); // Mobile is optional
        $password = trim($_POST["password"]);
    }

    // Check if email already exists
    if (empty($message)) {
        $sql_check = "SELECT id FROM user_info WHERE email = :email";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt_check->execute();
            if ($stmt_check->rowCount() == 1) {
                $message = "An account with this email already exists.";
            }
            unset($stmt_check);
        }
    }

    // If there are no errors, insert the new user into the database
    if (empty($message)) {
        
        $sql = "INSERT INTO user_info (name, email, mobile, password, security_question, answer) 
                VALUES (:name, :email, :mobile, :password, :sq, :ans)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Set default blank values for security Q/A as admin is creating it
            $default_sq = "n/a"; 
            $default_ans = "n/a";

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":mobile", $mobile, PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(":sq", $default_sq, PDO::PARAM_STR);
            $stmt->bindParam(":ans", $default_ans, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $message = "New user registered successfully!";
            } else {
                $message = "Something went wrong. Please try again later.";
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
    <title>Register New User</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body style="display: block; height: auto; background-color: #f0f2f5;">
    <header class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="dashboard.php" class="header-link">View Users</a>
            <a href="add_admin.php" class="header-link">Add New Admin</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>
    
    <div class="form-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
            <h2>Register New User</h2>
            <p>Create a new user account. Security questions will be blank.</p>
            
            <?php if(!empty($message)): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label for="name">User's Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="input-group">
                <label for="email">User's Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="mobile">User's Mobile</label>
                <input type="text" id="mobile" name="mobile">
            </div>
            <div class="input-group">
                <label for="password">Temporary Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Create User Account</button>
        </form>
    </div>
</body>
</html>