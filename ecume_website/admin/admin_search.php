<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "../includes/db.php";

// Ensure the request has a valid search query
if (!isset($_GET["q"]) || empty(trim($_GET["q"]))) {
    echo json_encode(["error" => "No search term provided"]);
    exit();
}

$searchQuery = '%' . trim($_GET["q"]) . '%';

try {
    $stmt = $pdo->prepare("SELECT users.full_name, finance.type, finance.amount, finance.date 
                           FROM finance 
                           INNER JOIN users ON finance.user_id = users.id 
                           WHERE LOWER(users.full_name) LIKE LOWER(:searchQuery) 
                              OR LOWER(finance.type) LIKE LOWER(:searchQuery) 
                              OR CAST(finance.amount AS CHAR) LIKE :searchQuery
                              OR DATE(finance.date) LIKE :searchQuery
                           ORDER BY finance.date DESC");

    $stmt->bindValue(":searchQuery", $searchQuery, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        echo json_encode(["message" => "No records found"]);
    } else {
        echo json_encode($results);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
