<?php
session_start();
require_once "../includes/db.php";

// Ensure user is an admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user["role"] !== "admin") {
    header("Location: ../members/members_list.php");
    exit();
}

// Handle approvals and rejections
if (isset($_POST["action"]) && isset($_POST["user_id"])) {
    if ($_POST["action"] === "approve") {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    } elseif ($_POST["action"] === "reject") {
        $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
    }
    $stmt->execute([$_POST["user_id"]]);
}

// Fetch pending users
$stmt = $pdo->query("SELECT id, full_name, email, status FROM users");

$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>

<section>
    
    <h2>Manage Approvals</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user["full_name"]); ?></td>
                    <td><?php echo htmlspecialchars($user["email"]); ?></td>
                    <td><?php echo htmlspecialchars($user["status"]); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                            <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                            <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

</div>