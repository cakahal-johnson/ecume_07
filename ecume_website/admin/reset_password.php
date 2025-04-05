<?php
require_once "../includes/db.php"; // Ensure correct path

$newPassword = "admin123"; // Your new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password in the database
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@ecume.com'");
$stmt->execute([$hashedPassword]);

echo "✅ Password reset successfully!";
?>
