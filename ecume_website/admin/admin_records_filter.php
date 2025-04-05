<?php
session_start();
require_once "../includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "Unauthorized access."]);
    exit();
}

// Only process search if a query is provided
if (empty($_GET['q'])) {
    echo json_encode([]); // Return an empty array instead of an error
    exit();
}

$q = "%" . trim($_GET['q']) . "%";

$stmt = $pdo->prepare("SELECT users.full_name, finance.type, finance.amount, finance.date 
                      FROM finance 
                      INNER JOIN users ON finance.user_id = users.id 
                      WHERE users.full_name LIKE ? 
                      OR finance.type LIKE ? 
                      OR finance.date LIKE ?
                      ORDER BY finance.date DESC");
$stmt->execute([$q, $q, $q]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
exit();
?>
