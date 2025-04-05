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

// Handle record deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Fetch file info before deleting
    $stmt = $pdo->prepare("SELECT file_path FROM records WHERE id = ?");
    $stmt->execute([$delete_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record && !empty($record['file_path'])) {
        $filePath = "../uploads/" . $record['file_path'];
        
        if (file_exists($filePath)) {
            unlink($filePath); // Delete file
        }
        
        // Delete record from database
        $stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $message = "✅ Record deleted successfully.";
        } else {
            $message = "❌ Error deleting record from database.";
        }
    } else {
        $message = "❌ Record not found.";
    }
}

// Pagination settings
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT id, file_name, uploaded_by, created_at FROM records ";
$params = [];

if (!empty($search)) {
    $sql .= "WHERE file_name LIKE :search ";
}

// Append ORDER BY and LIMIT with placeholders
$sql .= "ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total record count for pagination
$totalSql = "SELECT COUNT(*) FROM records";
if (!empty($search)) {
    $totalSql .= " WHERE file_name LIKE :search";
}

$totalStmt = $pdo->prepare($totalSql);

if (!empty($search)) {
    $totalStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$totalStmt->execute();
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>

    <main class="admin-main">
        <h2>Manage Uploaded Records</h2>
        <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <!-- Search Bar -->
        <form method="GET">
            <input type="text" name="search" placeholder="Search records..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Search</button>
        </form>

        <h3 style="margin-top: 3rem;">Uploaded Records</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Uploaded By</th>
                    <th>Date Uploaded</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['uploaded_by']); ?></td>
                        <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $record['id']; ?>" onclick="return confirm('Are you sure?');">❌ Delete</a>
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
                        <a href="?page=<?php echo $page - 1; ?>">⬅️ Previous</a>
                    <?php endif; ?>

                    <span> Page <?php echo $page; ?> of <?php echo $totalPages; ?> </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next ➡️</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
