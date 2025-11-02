<?php
session_start();
require_once '../includes/db_connect.php';

// 1. Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// 2. Check if an ID was passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No user ID specified. <a href='dashboard.php'>Go back</a>.");
}

$user_id = $_GET['id'];

try {
    // 3. Use a prepared statement to securely delete the user
    $sql = "DELETE FROM user_info WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $user_id]);

    // 4. Redirect back to the dashboard
    header("location: dashboard.php");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . " <a href='dashboard.php'>Go back</a>.");
}
?>