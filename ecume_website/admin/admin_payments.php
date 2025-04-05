<?php
session_start();
require_once "../includes/admin_header.php";
require_once "../includes/db.php";

// Ensure admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$records_per_page = 10; // Number of records per page
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? intval($_GET["page"]) : 1;
$offset = ($page - 1) * $records_per_page;

// Search filters
$search_query = "";
$filter_values = [];

if (!empty($_GET["search"])) {
    $search_query .= " AND u.full_name LIKE ?";
    $filter_values[] = "%" . $_GET["search"] . "%";
}

// ✅ Get total records count for pagination
$total_query = "SELECT COUNT(*) FROM payments p JOIN users u ON p.user_id = u.id WHERE 1 $search_query";
$stmt_total = $pdo->prepare($total_query);
$stmt_total->execute($filter_values);
$total_records = $stmt_total->fetchColumn();
$totalPages = ceil($total_records / $records_per_page);

// ✅ Correct SQL query
$query = "SELECT p.id, u.full_name, p.amount, p.payment_date, p.category, p.status, p.receipt 
          FROM payments p 
          JOIN users u ON p.user_id = u.id 
          WHERE 1 $search_query 
          ORDER BY p.payment_date DESC 
          LIMIT $records_per_page OFFSET $offset"; // ✅ Directly inserting values

$stmt = $pdo->prepare($query);
$stmt->execute($filter_values); // ✅ Only execute once with filter values
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-container">
    <?php include "../includes/admin_sidebar.php"; ?>

    <section class="admin-main-payment">
        <h2>Member Payments</h2>

        <!-- Search Form -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by name or category" value="<?php echo htmlspecialchars($_GET["search"] ?? ""); ?>">
            <button type="submit" class="btn">Search</button>
            <a href="admin_payments.php" class="btn-reset">Reset</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payments) > 0): ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment["full_name"]); ?></td>
                            <td>₦<?php echo number_format($payment["amount"], 2); ?></td>
                            <td><?php echo htmlspecialchars($payment["category"]); ?></td>
                            <td><?php echo htmlspecialchars($payment["payment_date"]); ?></td>
                            <td class="<?php echo strtolower($payment["status"]); ?>">
                                <?php echo htmlspecialchars($payment["status"]); ?>
                            </td>
                            <td>
                                <?php if ($payment["receipt"]): ?>
                                    <a href="../uploads/receipts/<?php echo htmlspecialchars($payment["receipt"]); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    <span class="no-receipt">No Receipt</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">No payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($_GET["search"] ?? ""); ?>" class="btn">Previous</a>
            <?php endif; ?>

            <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($_GET["search"] ?? ""); ?>" class="btn">Next</a>
            <?php endif; ?>
        </div>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </section>
</main>

<style>
/* SEARCH FORM */
.search-form {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.search-form input {
    padding: 8px;
    width: 200px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.btn, .btn-reset {
    padding: 8px 12px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #0056b3;
}

.btn-reset {
    background: #dc3545;
}

.btn-reset:hover {
    background: #c82333;
}

/* TABLE STYLING */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

thead {
    background: #007bff;
    color: white;
}

thead th, tbody td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

tbody tr:hover {
    background: #f1f1f1;
}

.no-data {
    text-align: center;
    padding: 20px;
    font-weight: bold;
}

/* RECEIPT */
.no-receipt {
    color: #999;
    font-style: italic;
}

/* PAGINATION */
.pagination {
    margin-top: 20px;
    text-align: center;
}

.pagination .btn {
    display: inline-block;
    padding: 6px 12px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: 0.3s ease;
}

.pagination .btn:hover {
    background: #0056b3;
}

.pagination span {
    margin: 0 10px;
    font-size: 16px;
    font-weight: bold;
}
</style>
