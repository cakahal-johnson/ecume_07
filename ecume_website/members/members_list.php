<?php
session_start();
require_once "../includes/inc_header.php";
require_once "../includes/db.php"; 

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Pagination settings
$limit = 10; // Number of members per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of members
$totalStmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch members with LIMIT for pagination
$stmt = $pdo->prepare("SELECT id, full_name, email, phone FROM users ORDER BY full_name ASC LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-container">
    <?php include "../includes/inc_sidebar.php"; ?>
    <section class="admin-main">
        <h2>All Members</h2>

        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Profile</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member["full_name"]); ?></td>
                        <td><?php echo htmlspecialchars($member["email"]); ?></td>
                        <td><?php echo htmlspecialchars($member["phone"]); ?></td>
                        <td><a href="view_member.php?id=<?php echo $member["id"]; ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>" class="prev">← Previous</a>
            <?php endif; ?>

            <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="next">Next →</a>
            <?php endif; ?>
        </div>

    </section>
</main>

<style>
/* PAGINATION STYLING */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    align-items: center;
}

.pagination a {
    padding: 10px 15px;
    margin: 0 5px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background 0.3s ease;
}

.pagination a:hover {
    background: #0056b3;
}

.pagination span {
    margin: 0 10px;
    font-size: 16px;
    color: #333;
}

/* Disable Previous & Next if at first/last page */
.pagination a.prev[disabled],
.pagination a.next[disabled] {
    pointer-events: none;
    background: #ccc;
}

  /* TABLE CONTAINER */
  .admin-main {
    flex: 1;
    max-width: 75%;
    padding: 2rem;
    background: white;
    margin: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* HEADER */
.admin-main h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #007bff;
    text-align: center;
}

/* TABLE STYLING */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

/* TABLE HEADERS */
thead {
    background-color: #007bff;
    color: white;
}

thead th {
    padding: 12px;
    font-size: 16px;
    text-align: left;
}

/* TABLE ROWS */
tbody tr {
    border-bottom: 1px solid #ddd;
    transition: background 0.3s ease;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

/* TABLE CELLS */
td {
    padding: 12px;
    font-size: 14px;
    color: #333;
}

/* VIEW PROFILE LINK */
td a {
    display: inline-block;
    padding: 6px 12px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: 0.3s ease;
}

td a:hover {
    background: #0056b3;
}

/* RESPONSIVENESS */
@media (max-width: 768px) {
    .admin-main {
        max-width: 100%;
        margin: 10px;
        padding: 15px;
    }

    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    td,
    th {
        padding: 10px;
        font-size: 14px;
    }
}


</style>
