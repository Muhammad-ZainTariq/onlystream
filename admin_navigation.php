<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php';


if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        
        <div class="website-name">OnlyStream</div>

        
        <div class="nav-links">
            <a href="adminwork.php">Dashboard</a>
            <a href="admin_approve_staff.php">Approve Staff</a>
            <a href="delete_users_and_staff.php">Manage Users & Staff</a>
            <a href="admin_reports.php">Manage Reports</a>
            <a href="view_queries.php">View Queries</a>
            <a href="staff_logout.php">Logout</a>
        </div>

        
        <button class="theme-toggle" id="theme-toggle-btn" onclick="toggleTheme()">Light Mode</button>
    </nav>

    <script src="/onlystream/theme.js"></script>
</body>
</html>