<?php
// session_start();
require_once "../includes/db.php";

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user details
$stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if user not found
if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="../assets/css/member_dashboard.css">
</head>
<body>

<header class="member-header">
    <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
    <div style="float: right; padding:0 3rem 3rem 0; margin:0 3rem 3rem 0" class="user-info">
        <span> <?php echo date("F j, Y, g:i A"); ?></span>
    </div>
</header>

</body>
</html>
