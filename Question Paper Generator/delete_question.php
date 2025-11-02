<?php
session_start();
require_once 'includes/db_connect.php';

// 1. Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die("Access denied.");
}

// 2. Get user ID and question ID
$user_id = $_SESSION['user_id'];
$question_id = $_GET['id'] ?? null;

if (!$question_id) {
    die("No question ID provided.");
}

// 3. Delete the question *only* if it belongs to this user (for security)
try {
    $sql = "DELETE FROM question_bank WHERE question_id = :qid AND user_id = :uid";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':qid' => $question_id, ':uid' => $user_id]);
    
    // 4. Redirect back to the question bank with a success message
    header("Location: question_bank.php?status=deleted");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>