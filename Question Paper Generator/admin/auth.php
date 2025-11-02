<?php
session_start();
require_once '../includes/db_connect.php'; 

if (isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

$username = $password = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        $error_msg = "Please enter both username and password.";
    } else {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
    }

    if (empty($error_msg)) {
        $sql = "SELECT id, username, password FROM admin_info WHERE username = :username";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $hashed_password = $row["password"];
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION["admin_loggedin"] = true;
                            $_SESSION["admin_id"] = $row["id"];
                            $_SESSION["admin_username"] = $row["username"];
                            
                            // Redirect to dashboard.php
                            header("location: dashboard.php");
                            exit;
                        } else {
                            $error_msg = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $error_msg = "No account found with that username.";
                }
            } else {
                $error_msg = "Oops! Something went wrong.";
            }
            unset($stmt);
        }
    }
    
if (!empty($error_msg)) {
        // UPDATED to index.php
        echo "<script>alert('" . addslashes($error_msg) . "'); window.location.href='index.php';</script>";
        exit;
    }
}
?>