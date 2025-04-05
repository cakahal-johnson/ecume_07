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

// Handle financial record submission with CSRF protection
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        die("❌ CSRF validation failed!");
    }

    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($user_id && $type && $amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO finance (user_id, type, amount, date) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$user_id, $type, $amount])) {
            $message = "✅ Financial record added successfully.";
        } else {
            $message = "❌ Database error: Could not add record.";
        }
    } else {
        $message = "❌ Invalid input. Please check your fields.";
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Fetch financial summary
$stmt = $pdo->query("SELECT type, SUM(amount) as total FROM finance GROUP BY type");
$financial_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch transactions
$stmt = $pdo->query("SELECT users.full_name, finance.type, finance.amount, finance.date 
                     FROM finance 
                     INNER JOIN users ON finance.user_id = users.id 
                     ORDER BY finance.date DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users for dropdown
$stmt = $pdo->query("SELECT id, full_name FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination settings
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch transactions with pagination
$stmt = $pdo->prepare("SELECT users.full_name, finance.type, finance.amount, finance.date 
                       FROM finance 
                       INNER JOIN users ON finance.user_id = users.id 
                       ORDER BY finance.date DESC 
                       LIMIT :limit OFFSET :offset");

$stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
$stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records count for pagination
$totalStmt = $pdo->query("SELECT COUNT(*) FROM finance");
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);


?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>

    <main class="admin-main">
        <h2>Financial Records</h2>

        <?php if (!empty($message)) echo "<p style='color: green;'>$message</p>"; ?>

        <!-- Form to add financial records -->
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="user_id">Select Member:</label>
            <select name="user_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="type">Type:</label>
            <select name="type" required>
                <option value="monthly_due">Monthly Due</option>
                <option value="donation">Donation</option>
                <option value="annual_meeting">Annual Meeting</option>
                <option value="activity_support">Activity Support</option>
                <option value="empowerment">Empowerment</option>
            </select>
            <br> <br>
            <label for="amount">Amount (₦):</label>
            <input type="number" name="amount" step="0.01" min="1" required>

            <button type="submit">Add Record</button>
        </form>

        <!-- Financial Summary Cards -->
        <div style="margin-top: 3rem;" class="cards">
            <?php foreach ($financial_summary as $record): ?>
                <div class="atm-card">
                    <h3><?php echo ucfirst(str_replace('_', ' ', $record['type'])); ?></h3>
                    <p>₦<?php echo number_format($record['total'], 2); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 style="margin-top: 3rem;">Transaction Details</h3>

        <table border="1">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Type</th>
                    <th>Amount (₦)</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="recordsTableBody">
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['full_name']); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $transaction['type'])); ?></td>
                        <td>₦<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td><?php echo $transaction['date']; ?></td>
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