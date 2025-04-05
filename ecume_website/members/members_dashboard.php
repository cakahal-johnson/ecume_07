<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Fetch uploaded records
$stmt = $pdo->query("SELECT * FROM uploads ORDER BY uploaded_at DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "../includes/inc_header.php"; ?>
<div class="members-container">
    <?php include "../includes/inc_sidebar.php"; ?>
    <main class="members-main">
        <h2>Available Records</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Uploaded At</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['filename']); ?></td>
                        <td><?php echo $record['uploaded_at']; ?></td>
                        <td><a href="<?php echo $record['file_path']; ?>" download>⬇️ Download</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
