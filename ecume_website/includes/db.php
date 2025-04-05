<?php
$host = "localhost";
$dbname = "ecume_db";
$username = "root"; // Change if using a live server
$password = ""; // Change if using a live server

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Database connected successfully!"; // Debugging message
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
