<?php
// session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css"> 
</head>
<body>
<header class="admin-header">
    <div class="logo">ECUME Admin</div>
    <div class="user-info">
        <span>Welcome, Admin ||</span> 
        <span style="color: cyan; font-size:smaller"><?php echo date("F j, Y, g:i A"); ?></span>
        <a href="../admin/logout.php" class="logout-btn">Logout</a>
    </div>
</header>
