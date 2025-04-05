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

// Fetch total transaction count
$total_stmt = $pdo->query("SELECT COUNT(*) FROM finance");
$total_rows = $total_stmt->fetchColumn();
$rows_per_page = 6;
$total_pages = ceil($total_rows / $rows_per_page);

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

// Fetch transactions with pagination
$stmt = $pdo->prepare("SELECT users.full_name, finance.type, finance.amount, finance.date 
                     FROM finance 
                     INNER JOIN users ON finance.user_id = users.id 
                     ORDER BY finance.date DESC 
                     LIMIT :offset, :rows_per_page");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':rows_per_page', $rows_per_page, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>

    <main class="admin-main">
        <h2>Financial Records</h2>

        <!-- Search Bar -->
        <input style="padding: 8px;" type="text" id="searchInput" placeholder="Search records..." onkeyup="searchRecords()" />

        <!-- Transaction Table -->
        <h3 style="margin-top: 2rem;">Transaction Details</h3>
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

        <!-- Pagination Controls -->
        <div style="margin-top: 3rem;" id="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">← Prev</a>
            <?php endif; ?>

            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next →</a>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Search Function
function searchRecords() {
    let query = document.getElementById("searchInput").value.trim();

    if (query === "") {
        document.getElementById("recordsTableBody").innerHTML = "<tr><td colspan='4'>Please enter a search term</td></tr>";
        return;
    }

    fetch(`admin_records_filter.php?q=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then(data => {
            console.log("Fetched Data:", data);  // Debugging log
            if (!data || data.length === 0) {
                document.getElementById("recordsTableBody").innerHTML = "<tr><td colspan='4'>No records found.</td></tr>";
            } else {
                displayResults(data);
            }
        })
        .catch(error => console.error("Error fetching records:", error));
}

function displayResults(data) {
    let tableBody = document.getElementById("recordsTableBody");
    tableBody.innerHTML = "";

    data.forEach(record => {
        let row = document.createElement("tr");

        row.innerHTML = `
            <td>${record.full_name}</td>
            <td>${record.type.replace('_', ' ')}</td>
            <td>₦${parseFloat(record.amount).toFixed(2)}</td>
            <td>${record.date}</td>
        `;

        tableBody.appendChild(row);
    });
}


</script>
