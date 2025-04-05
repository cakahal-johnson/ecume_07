<?php
session_start();
require_once "../includes/inc_header.php";
require_once "../includes/db.php"; 

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: profile.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $email, $phone, $user_id])) {
        $_SESSION["success"] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $error = "Failed to update profile.";
    }
}
?>



<div class="dashboard-container">

<?php include "../includes/inc_sidebar.php"; ?>

<section class="admin-main">
    <h2>Edit Profile</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($_SESSION["success"])) {
        echo "<p class='success'>" . $_SESSION["success"] . "</p>";
        unset($_SESSION["success"]);
    } ?>

    <form action="" method="post">
        <label>Full Name:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user["full_name"]); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>" required>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user["phone"]); ?>">

        <button type="submit">Update Profile</button>
    </form>

    <a href="profile.php" class="btn">Back to Profile</a>
</section>

</div>
