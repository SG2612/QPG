<?php
session_start();
require_once 'includes/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            // UPDATED to index.php
            echo "Email and password fields cannot be blank. <a href='index.php'>Please try again.</a>";
            exit();
        }

        $sql = "SELECT id, email, name, password FROM user_info WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashed_password = $user['password'];

            if (password_verify($password, $hashed_password)) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];

                header("location: home.php");
                exit;
            } else {
                // UPDATED to index.php
                echo "The email or password you entered is incorrect. <a href='index.php'>Please try again.</a>";
            }
        } else {
            // UPDATED to index.php
            echo "The email or password you entered is incorrect. <a href='index.php'>Please try again.</a>";
        }
    } else {
        // UPDATED to index.php
        echo "Form data is incomplete. <a href='index.php'>Go back</a>";
        exit();
    }
}
?>