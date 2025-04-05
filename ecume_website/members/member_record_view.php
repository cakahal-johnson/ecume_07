<?php
session_start();
require_once "../includes/inc_header.php";
require_once "../includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$records_per_page = 5; // Number of payments per page

// Get the current page number
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? intval($_GET["page"]) : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch member details
$stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    header("Location: dashboard.php");
    exit();
}

// Fetch total number of payments
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$totalPayments = $totalStmt->fetchColumn();
$totalPages = ceil($totalPayments / $records_per_page);

// Fetch paginated payment records
$paymentStmt = $pdo->prepare("SELECT id, amount, payment_date, status, category, receipt FROM payments WHERE user_id = :user_id ORDER BY payment_date DESC LIMIT :offset, :records_per_page");
$paymentStmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
$paymentStmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$paymentStmt->bindValue(":records_per_page", $records_per_page, PDO::PARAM_INT);
$paymentStmt->execute();
$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize messages
$message = $error = "";

// Handle receipt upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["upload_receipt"])) {
    $payment_id = $_POST["payment_id"] ?? 0;

    if (!empty($_FILES["receipt"]["name"])) {
        $targetDir = "../uploads/receipts/";

        // Ensure directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["receipt"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allowed file types
        $allowedTypes = ["jpg", "jpeg", "png", "pdf"];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $targetFilePath)) {
                // Update payment record with receipt file path
                $updateReceipt = $pdo->prepare("UPDATE payments SET receipt = ? WHERE id = ? AND user_id = ?");
                if ($updateReceipt->execute([$fileName, $payment_id, $user_id])) {
                    $message = "Receipt uploaded successfully.";
                } else {
                    $error = "Failed to update payment record.";
                }
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Invalid file format. Only JPG, PNG, and PDF allowed.";
        }
    } else {
        $error = "No file selected.";
    }
}

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_payment"])) {
    $category = $_POST["category"] ?? "";
    $amount = $_POST["amount"] ?? 0;

    if ($category && $amount > 0) {
        $insertPayment = $pdo->prepare("INSERT INTO payments (user_id, amount, category, status, payment_date) VALUES (?, ?, ?, 'Pending', NOW())");
        if ($insertPayment->execute([$user_id, $amount, $category])) {
            $message = "Payment submitted successfully.";
        } else {
            $error = "Failed to submit payment.";
        }
    } else {
        $error = "Please enter a valid amount and category.";
    }
}
?>

<main class="dashboard-container">
    <?php include "../includes/inc_sidebar.php"; ?>

    <section class="admin-main">
        <h2>Member Profile</h2>

        <div style="margin-top:3rem" class="profile-section">
            <img src="../uploads/profile_pics/<?php echo htmlspecialchars($user_id); ?>.jpg" alt="Profile Picture" class="profile-pic">
            <p style="margin-top:1.5rem"><strong>Full Name:</strong> <?php echo htmlspecialchars($member["full_name"]); ?></p>
            <p style="margin-top:1.5rem"><strong>Email:</strong> <?php echo htmlspecialchars($member["email"]); ?></p>
            <p style="margin-top:1.5rem"><strong>Phone:</strong> <?php echo htmlspecialchars($member["phone"]); ?></p>
        </div>

        <h2 style="margin-top:3rem">Payment History</h2>

        <div>
            <p>Account Details: </p>
            <strong>Payment Instruction: </strong>
        </div>

        <table>
            <thead>
                <tr>
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
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment["id"]); ?>">
                                        <input type="file" name="receipt" required>
                                        <button type="submit" name="upload_receipt" class="btn-upload">Upload</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2 style="margin-top:3rem">Make a Payment</h2>

        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="category">Select Payment Type:</label>
            <select name="category" id="category" required>
                <option value="Monthly Dues">Monthly Dues</option>
                <option value="Wedding">Wedding</option>
                <option value="Donation">Donation</option>
                <option value="Burial">Burial</option>
                <option value="Other">Other</option>
            </select>

            <label style="margin-top:1.5rem" for="amount">Amount (₦):</label>
            <input type="number" name="amount" id="amount" required>

            <button style="margin-top:1.5rem" type="submit" name="submit_payment" class="btn">Submit Payment</button>
        </form>

        

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </section>
</main>


<style>
/* UPLOAD BUTTON */
.btn-upload {
    padding: 6px 12px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 5px;
}

.btn-upload:hover {
    background: #218838;
}

/* RECEIPT VIEW */
table td a {
    display: inline-block;
    padding: 6px 12px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: 0.3s ease;
}

table td a:hover {
    background: #0056b3;
}

/* PROFILE SECTION */
.profile-section {
    text-align: center;
    margin-bottom: 20px;
}

.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #007bff;
}

/* TABLE STYLES */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

thead {
    background-color: #007bff;
    color: white;
}

thead th, td {
    padding: 12px;
    font-size: 16px;
    text-align: left;
}

tbody tr {
    border-bottom: 1px solid #ddd;
}

/* STATUS COLORS */
.completed {
    color: green;
    font-weight: bold;
}

.pending {
    color: orange;
    font-weight: bold;
}

.failed {
    color: red;
    font-weight: bold;
}

/* FORM STYLES */
form {
    background: #f8f8f8;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

label {
    font-size: 16px;
    margin-top: 10px;
    display: block;
}

input, select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.btn {
    display: inline-block;
    padding: 10px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background 0.3s ease;
    margin-top: 10px;
}

.btn:hover {
    background: #0056b3;
}

.success {
    color: green;
    font-weight: bold;
}

.error {
    color: red;
    font-weight: bold;
}

.no-data {
    text-align: center;
    padding: 15px;
    color: #777;
}
</style>
