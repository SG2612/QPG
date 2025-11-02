<?php
session_start();
// DB path fixed
require_once '../includes/db_connect.php';

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    // Redirect to admin login
    header("location: index.php");
    exit;
}

try {
    $sql = "SELECT id, name, email, mobile FROM user_info ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("ERROR: Could not execute query. " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body style="display: block; height: auto; background-color: #fff;">
    <header class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="register_user.php" class="header-link">Register a user</a>
            <a href="add_admin.php" class="header-link">Add New Admin</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>
    
    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>(Admin)!</h2>
        <p>Here you can manage the users of the Question Paper Generator.</p>

        <h3>Registered Users</h3>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                            
                            <td>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                                   class="delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>