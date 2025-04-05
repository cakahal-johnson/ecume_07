<?php
session_start();
require_once "../includes/inc_header.php";
require_once "../includes/db.php"; 

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if member ID is provided
if (!isset($_GET["id"])) {
    header("Location: members_list.php");
    exit();
}

$member_id = $_GET["id"];

// Fetch member details
$stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if member not found
if (!$member) {
    header("Location: members_list.php");
    exit();
}

// Fetch member details including profile picture
$stmt = $pdo->prepare("SELECT full_name, email, phone, profile_picture FROM users WHERE id = ?");
$stmt->execute([$member_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_picture = $member['profile_picture'] ?: 'default.png'; // Default profile picture


?>

<div class="dashboard-container">

<?php include "../includes/inc_sidebar.php"; ?>

<section class="admin-main">
    <h2>Member Profile</h2>

    <div  style="margin-top: 2rem;" class="member-profile">
        <img src="../uploads/profiles/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic">
    </div>

    <p  style="margin-top: 1.5rem;"><strong>Full Name:</strong> <?php echo htmlspecialchars($member["full_name"]); ?></p>
    <p  style="margin-top: 1.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($member["email"]); ?></p>
    <p  style="margin-top: 1.5rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($member["phone"]); ?></p>

    <a href="members_list.php" class="btn">Back to Members List</a>
</section>

</div>

<style>
    /* SECTION CONTAINER */
.admin-main {
    flex: 1;
    max-width: 75%;
    padding: 2rem;
    background: white;
    margin: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* HEADER */
.admin-main h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #007bff;
}

/* MEMBER PROFILE */
.member-profile {
    text-align: center;
    margin-bottom: 20px;
}

.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #007bff;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
}

/* MEMBER INFO */
p {
    font-size: 16px;
    color: #333;
    margin: 10px 0;
}

/* BACK BUTTON */
.btn {
    display: inline-block;
    padding: 10px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background 0.3s ease;
    margin-top: 15px;
}

.btn:hover {
    background: #0056b3;
}

/* RESPONSIVENESS */
@media (max-width: 768px) {
    .admin-main {
        max-width: 100%;
        margin: 10px;
        padding: 15px;
    }

    .profile-pic {
        width: 100px;
        height: 100px;
    }
}

</style>