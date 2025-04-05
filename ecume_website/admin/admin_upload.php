<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check if the user is an admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["role"] !== "admin") {
    header("Location: ../members/members_list.php");
    exit();
}

$message = "";

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["record"])) {
    $file_name = $_FILES["record"]["name"];
    $file_tmp = $_FILES["record"]["tmp_name"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ["pdf", "jpg", "png", "docx"];

    if (in_array($file_ext, $allowed_ext)) {
        $new_filename = time() . "_" . $file_name;
        $upload_path = "../uploads/" . $new_filename;

        if (move_uploaded_file($file_tmp, $upload_path)) {
            $stmt = $pdo->prepare("INSERT INTO uploads (filename, file_path) VALUES (?, ?)");
            if ($stmt->execute([$file_name, $upload_path])) {
                $message = "✅ Record uploaded successfully.";
            } else {
                $message = "❌ Database error.";
            }
        } else {
            $message = "❌ Error moving file.";
        }
    } else {
        $message = "❌ Invalid file type.";
    }
}
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>
    <main class="admin-main">
        <h2>Upload Record</h2>
        <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="record">Select File:</label>
            <input type="file" name="record" required>
            <button type="submit">Upload</button>
        </form>
    </main>
</div>
