<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch profile picture file name
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Delete profile picture if it exists
if (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) {
    unlink("../uploads/profiles/" . $user['profile_picture']);
}

// Delete user record
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// Destroy session and redirect
session_destroy();
header("Location: ../index.php");
exit();
?>
