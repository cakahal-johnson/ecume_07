<?php
session_start();
require_once "../includes/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Ensure user is an admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["role"] !== "admin") {
    header("Location: ../members/members_list.php");
    exit();
}

$message = "";

// Handle user addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    if ($full_name && $email && $role) {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$full_name, $email, $role])) {
            $message = "✅ User added successfully.";
        } else {
            $message = "❌ Database error: Could not add user.";
        }
    } else {
        $message = "❌ Invalid input. Please check your fields.";
    }
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $message = "✅ User deleted successfully.";
    } else {
        $message = "❌ Error deleting user.";
    }
}

// Pagination settings
$limit = 8; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$searchQuery = "";
$params = [":limit" => $limit, ":offset" => $offset];

if (!empty($search)) {
    $searchQuery = " WHERE full_name LIKE :search OR email LIKE :search ";
    $params[":search"] = "%$search%";
}

// Fetch users with pagination and search
$stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users $searchQuery ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total user count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM users $searchQuery");
if (!empty($search)) {
    $totalStmt->bindValue(":search", "%$search%", PDO::PARAM_STR);
}
$totalStmt->execute();
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>

    <main class="admin-main">
        <h2>Manage Users</h2>
        <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <!-- Add User Form -->
        <form method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="role">Role:</label>
            <select name="role" required>
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <!-- Search Form -->
        <form method="GET" style="margin-top: 20px;">
            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <h3 style="margin-top: 3rem;">Existing Members</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure?');">❌ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div style="margin-top: 20px;">
            <?php if ($totalPages > 1): ?>
                <div>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">⬅️ Previous</a>
                    <?php endif; ?>

                    <span> Page <?php echo $page; ?> of <?php echo $totalPages; ?> </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next ➡️</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>