<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define the base URL dynamically
$base_url = "http://" . $_SERVER['HTTP_HOST'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecume 2007 Set</title>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/responsive.css">
</head>
<body>

<header>
    <h1>Welcome to Ecume 2007 Set</h1>
</header>

<nav>
    <button class="menu-toggle">☰</button>
    <ul>
        <li><a href="<?= $base_url ?>/index.php">Home</a></li>
        <li><a href="<?= $base_url ?>/members/members_list.php">Members</a></li>
        <li><a href="<?= $base_url ?>/auth/register.php">Register</a></li>
        <li><a href="<?= $base_url ?>/admin/admin_dashboard.php">Admin</a></li>
        <li><a href="<?= $base_url ?>/auth/login.php">Login</a></li>
    </ul>
</nav>

<script src="<?= $base_url ?>/assets/js/navbar.js"></script>
