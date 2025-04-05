<?php
session_start();
include "../includes/inc_header.php";
require_once "../includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user details
$stmt = $pdo->prepare("SELECT full_name, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_picture = $user['profile_picture'] ?: 'default.png'; // Default profile picture

?>

<?php  ?>
<div class="dashboard-container">
    <?php include "../includes/inc_sidebar.php"; ?>

    <main class="admin-main">
        <h2>Edit Profile</h2>

        <?php if (isset($_SESSION["message"])): ?>
            <div  style="margin-top: 2rem;" class="message"><?php echo $_SESSION["message"];
                                    unset($_SESSION["message"]); ?></div>
        <?php endif; ?>

        <div  style="margin-top: 1.5rem;" class="profile-picture-container">
            <img src="../uploads/profiles/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">
        </div>

        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <label for="profile_picture">Upload Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*">

            <label  style="margin-top: 1.5rem;" for="name">Full Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label  style="margin-top: 1.5rem;" for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label  style="margin-top: 1.5rem;" for="password">New Password (leave blank if not changing):</label>
            <input type="password" name="password">

            <button type="submit">Update Profile</button>
        </form>

        <form action="delete_account.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone!');">
            <button type="submit" class="delete-btn">Delete Account</button>
        </form>

        <form action="deactivate_account.php" method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account? You can reactivate it later by contacting support.');">
            <button type="submit" class="deactivate-btn">Deactivate Account</button>
        </form>

        <style>
            .deactivate-btn {
                background-color: #f39c12;
                color: white;
                border: none;
                padding: 10px 15px;
                margin-top: 10px;
                cursor: pointer;
                border-radius: 5px;
            }

            .deactivate-btn:hover {
                background-color: #e67e22;
            }
        </style>


        <style>
            .delete-btn {
                background-color: #dc3545;
                color: white;
                border: none;
                padding: 10px 15px;
                margin-top: 10px;
                cursor: pointer;
                border-radius: 5px;
            }

            .delete-btn:hover {
                background-color: #c82333;
            }
        </style>

    </main>
</div>

<style>
    .profile-picture-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #007bff;
    }
</style>