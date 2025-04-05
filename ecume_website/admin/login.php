<?php
session_start();
require_once "../includes/db.php"; // Ensure correct path

// If the user is already logged in, redirect to the appropriate dashboard
if (isset($_SESSION['role']) && !empty($_SESSION['role'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? '../admin/admin_dashboard.php' : '../members/profile.php'));
    exit();
}

$error = ""; // Initialize error variable

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        // Prepare statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Store session data securely
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            // Redirect based on role
            header("Location: " . ($user['role'] === 'admin' ? '../admin/admin_dashboard.php' : '../members/profile.php'));
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "../includes/header.php"; ?>

<section>
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form action="" method="post">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</section>

<?php include "../includes/footer.php"; ?>

</body>
</html>
