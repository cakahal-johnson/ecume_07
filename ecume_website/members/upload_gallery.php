<?php
session_start();
require_once "../includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["gallery_images"])) {
    $upload_dir = "../uploads/gallery/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES["gallery_images"]["tmp_name"] as $key => $tmp_name) {
        $file_name = basename($_FILES["gallery_images"]["name"][$key]);
        $target_file = $upload_dir . $file_name;

        // Validate file type
        $allowed_types = ["image/jpeg", "image/png", "image/gif"];
        $file_type = mime_content_type($tmp_name);

        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($tmp_name, $target_file)) {
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO gallery (user_id, image_path) VALUES (?, ?)");
                $stmt->execute([$user_id, $file_name]);
            }
        }
    }
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>
