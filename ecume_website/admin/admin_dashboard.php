<?php
session_start();
require_once "../includes/db.php"; // Database connection file

// Ensure the user is an admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user["role"] !== "admin") {
    header("Location: ../members/members_list.php");
    exit();
}

// Fetch total number of users
$totalUsersStmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
$totalUsers = $totalUsersStmt->fetch(PDO::FETCH_ASSOC)["total_users"];

// Fetch total number of approved members
$totalMembersStmt = $pdo->query("SELECT COUNT(*) AS total_members FROM users WHERE status = 'approved'");
$totalMembers = $totalMembersStmt->fetch(PDO::FETCH_ASSOC)["total_members"];

// Fetch pending approvals
$pendingApprovalsStmt = $pdo->query("SELECT COUNT(*) AS pending_approvals FROM users WHERE status = 'pending'");
$pendingApprovals = $pendingApprovalsStmt->fetch(PDO::FETCH_ASSOC)["pending_approvals"];

// Fetch rejected members
$rejectedMembersStmt = $pdo->query("SELECT COUNT(*) AS rejected_members FROM users WHERE status = 'rejected'");
$rejectedMembers = $rejectedMembersStmt->fetch(PDO::FETCH_ASSOC)["rejected_members"];

// Fetch financial records (Assuming a 'finance' table exists)
$monthlyDueStmt = $pdo->query("SELECT SUM(amount) AS total FROM finance WHERE type = 'monthly_due'");
$monthlyDue = $monthlyDueStmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

$donationStmt = $pdo->query("SELECT SUM(amount) AS total FROM finance WHERE type = 'donation'");
$donation = $donationStmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

$annualMeetingStmt = $pdo->query("SELECT SUM(amount) AS total FROM finance WHERE type = 'annual_meeting'");
$annualMeeting = $annualMeetingStmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

$activitySupportStmt = $pdo->query("SELECT SUM(amount) AS total FROM finance WHERE type = 'activity_support'");
$activitySupport = $activitySupportStmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;

$membersEmpoweredStmt = $pdo->query("SELECT SUM(amount) AS total FROM finance WHERE type = 'empowerment'");
$membersEmpowered = $membersEmpoweredStmt->fetch(PDO::FETCH_ASSOC)["total"] ?? 0;
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>
<main class="admin-main">
    <h2>Dashboard Overview</h2>
    <div class="cards">
    <div class="card">
            <h3>Rejected Members</h3>
            <p><?php echo $rejectedMembers; ?></p>
        </div>
        
        <div class="card">
            <h3>Total Members</h3>
            <p><?php echo $totalMembers; ?></p>
        </div>
        <div class="card">
            <h3>Pending Approvals</h3>
            <p><?php echo $pendingApprovals; ?></p>
        </div>
        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $totalUsers; ?></p>
        </div>
    </div>

    <h2 style="margin-top: 2rem;">Financial Records</h2>
    <div class="cards">
        <div class="card atm-card">
            <h3>Monthly Due</h3>
            <p>₦<?php echo number_format($monthlyDue, 2); ?></p>
        </div>
        <div class="card atm-card">
            <h3>Free-Will Donation</h3>
            <p>₦<?php echo number_format($donation, 2); ?></p>
        </div>
        <div class="card atm-card">
            <h3>Annual Meeting</h3>
            <p>₦<?php echo number_format($annualMeeting, 2); ?></p>
        </div>
        <div class="card atm-card">
            <h3>Activity Support</h3>
            <p>₦<?php echo number_format($activitySupport, 2); ?></p>
        </div>
        <div class="card atm-card">
            <h3>Empowerment</h3>
            <p>₦<?php echo number_format($membersEmpowered, 2); ?></p>
        </div>
    </div>
</main>

</div>
