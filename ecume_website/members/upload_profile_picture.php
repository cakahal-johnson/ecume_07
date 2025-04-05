<?php
session_start();
require_once "../includes/db.php"; 

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$upload_dir = "../uploads/";

// Ensure the upload directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $file = $_FILES["profile_picture"];
    $allowed_types = ["image/jpeg", "image/png", "image/jpg"];

    if (in_array($file["type"], $allowed_types) && $file["size"] <= 5000000) {
        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $user_id . "." . $extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file["tmp_name"], $upload_path)) {
            // Update database with new profile picture path
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$new_filename, $user_id]);

            $_SESSION["success"] = "Profile picture updated successfully.";
        } else {
            $_SESSION["error"] = "Error uploading file.";
        }
    } else {
        $_SESSION["error"] = "Invalid file type or size.";
    }
}

header("Location: profile.php");
exit();
?>
