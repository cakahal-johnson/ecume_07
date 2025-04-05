<?php
session_start();
require_once "../includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $profile_picture = null;

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["message"] = "❌ Invalid email format!";
        header("Location: profile.php");
        exit();
    }

    // Handle profile picture upload
    if (!empty($_FILES["profile_picture"]["name"])) {
        $upload_dir = "../uploads/profiles/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allow only image files
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION["message"] = "❌ Only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: profile.php");
            exit();
        }

        // Limit file size (2MB max)
        if ($_FILES["profile_picture"]["size"] > 2 * 1024 * 1024) {
            $_SESSION["message"] = "❌ File size must be less than 2MB.";
            header("Location: profile.php");
            exit();
        }

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $file_name;
        } else {
            $_SESSION["message"] = "❌ Error uploading profile picture.";
            header("Location: profile.php");
            exit();
        }
    }

    // Prepare update query
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$name, $email, $hashed_password, $profile_picture, $user_id]);
    } else {
        if ($profile_picture) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, profile_picture = ? WHERE id = ?");
            $stmt->execute([$name, $email, $profile_picture, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user_id]);
        }
    }

    $_SESSION["message"] = "✅ Profile updated successfully!";
    header("Location: profile.php");
    exit();
}
?>
