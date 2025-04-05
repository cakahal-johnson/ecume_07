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

// Update role if requested
if (isset($_POST["user_id"], $_POST["role"])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST["role"], $_POST["user_id"]]);
}

// Fetch all users
$stmt = $pdo->query("SELECT id, full_name, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>


<section>
    <h2>Manage Member Roles</h2>

    <table border="1">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Change Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user["full_name"]); ?></td>
                    <td><?php echo htmlspecialchars($user["email"]); ?></td>
                    <td><?php echo htmlspecialchars($user["role"]); ?></td>
                    <td>
                        <?php if ($user["role"] !== "admin"): ?>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                                <select name="role">
                                    <option value="member" <?php echo $user["role"] === "member" ? "selected" : ""; ?>>Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        <?php else: ?>
                            (Admin)
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
