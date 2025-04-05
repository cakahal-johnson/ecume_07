<?php
session_start();
require_once "../includes/db.php"; 

// Ensure only admin can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_GET["id"];

// Prevent admin from deleting themselves
if ($user_id == $_SESSION["user_id"]) {
    $_SESSION["error"] = "You cannot delete your own account!";
    header("Location: dashboard.php");
    exit();
}

// Delete user from database
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$user_id])) {
    $_SESSION["success"] = "User deleted successfully!";
} else {
    $_SESSION["error"] = "Failed to delete user.";
}

header("Location: dashboard.php");
exit();
?>
