<?php
// This file was 'register.php' (the processing script)
require_once "includes/db_connect.php";

$username          = $_POST['username'] ?? null;
$email             = $_POST['email'] ?? null;
$mobile            = $_POST['mobile'] ?? null;
$security_question = $_POST['security_question'] ?? null;
$answer            = $_POST['answer'] ?? null;
$password          = $_POST['password'] ?? null;

if ($username && $email && $mobile && $security_question && $answer && $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

    try {
        $sql = "INSERT INTO user_info (name, email, mobile, security_question, answer, password) 
                VALUES (:username, :email, :mobile, :security_question, :answer, :password)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':mobile' => $mobile,
            ':security_question' => $security_question,
            ':answer' => $answer,
            ':password' => $hashed_password
        ]);

        // Redirect to login page on success
        header("location: index.php?status=success");
        exit;

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            // UPDATED to index.php
            echo "Error: An account with this email already exists. <a href='index.php'>Please login.</a>";
        } else {
            echo "Database error: " . $e->getMessage();
        }
    }
} else {
    // UPDATED to register.php
    echo "Missing required fields! <a href='register.php'>Go back.</a>";
}
?>