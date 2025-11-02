<?php
session_start();

// PREVENT BROWSER CACHING (THE FIX)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
// END OF FIX

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Include the database connection
require_once 'db_connect.php';

// Get user name for display
$name = $_SESSION['name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QPG Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* This is the styling from your original home.php for the navbar and layout */
        body {
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          background-color: #f4f7f6;
          color: #333;
          margin: 0;
          line-height: 1.6;
        }
        .container {
          max-width: 960px;
          margin: 0 auto;
          padding: 0 20px;
        }
        .navbar {
          background-color: #ffffff;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
          padding: 1rem 0;
          position: sticky;
          top: 0;
          z-index: 1000;
        }
        .navbar .container {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        .logo {
          font-size: 1.8rem;
          font-weight: bold;
          color: #4caf50;
          margin: 0;
          text-decoration: none;
        }
        .navbar nav ul {
          list-style: none;
          margin: 0;
          padding: 0;
          display: flex;
          align-items: center;
        }
        .navbar nav ul li {
          margin-left: 25px;
        }
        .navbar nav a {
          text-decoration: none;
          color: #555;
          font-weight: 500;
          transition: color 0.3s ease;
        }
        .navbar nav a:hover {
          color: #4caf50;
        }
        .logout-btn {
          background-color: #f44336;
          color: white !important;
          padding: 8px 15px;
          border-radius: 5px;
          transition: background-color 0.3s ease;
        }
        .logout-btn:hover {
          background-color: #d32f2f;
          color: white !important;
        }
        main {
          padding: 30px 20px;
        }
        .welcome-message {
          background: #e8f5e9;
          border-left: 5px solid #4caf50;
          padding: 20px;
          margin-bottom: 30px;
          border-radius: 5px;
        }
        .welcome-message h2 {
          margin-top: 0;
          color: #2e7d32;
        }
        .generator-box {
          background: #fff;
          padding: 30px;
          border-radius: 8px;
          box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .generator-box h3 {
          text-align: center;
          margin-top: 0;
          margin-bottom: 25px;
          font-size: 1.5rem;
          color: #333;
        }
        .form-group {
          margin-bottom: 20px;
        }
        .form-group label {
          display: block;
          margin-bottom: 8px;
          font-weight: bold;
          font-size: 0.9rem;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
          width: 100%;
          padding: 12px;
          border: 1px solid #ccc;
          border-radius: 6px;
          font-size: 1rem;
          box-sizing: border-box;
          background-color: #fff;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .btn-submit {
          display: block;
          width: 100%;
          padding: 15px;
          background: #4caf50;
          color: #fff;
          border: none;
          border-radius: 6px;
          cursor: pointer;
          font-size: 1.1rem;
          font-weight: bold;
          transition: background-color 0.3s ease;
        }
        .btn-submit:hover {
          background: #43a047;
        }
    </style>
</head>
<body>
<header class="navbar">
        <div class="container">
            <a href="home.php" class="logo">QPG</a>
            <nav>
                <ul>
                    <li><a href="home.php">Generate Paper</a></li>
                    <li><a href="question_bank.php">Question Bank</a></li>
                    
                    <li><a href="my_papers.php">My Papers</a></li>
                    
                    <li><a href="logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <div class="welcome-message">
            <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
            <p><?php echo $welcome_subtext ?? 'Ready to manage your questions or generate a new paper?'; ?></p>
        </div>