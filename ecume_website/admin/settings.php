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

// Handle site settings update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $site_name = $_POST["site_name"] ?? "";
    $admin_email = $_POST["admin_email"] ?? "";
    $announcement = $_POST["announcement"] ?? "";
    $projects_post = $_POST["projects_post"] ?? "";

    $stmt = $pdo->prepare("UPDATE settings SET site_name = ?, admin_email = ?, announcement = ?, projects_post = ? WHERE id = 1");
    if ($stmt->execute([$site_name, $admin_email, $announcement, $projects_post])) {
        $message = "<div class='message success'>✅ Settings updated successfully.</div>";
    } else {
        $message = "<div class='message error'>❌ Error updating settings.</div>";
    }
}

// Handle Gallery Upload
if (isset($_FILES["gallery_images"])) {
    $upload_dir = "../uploads/gallery/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES["gallery_images"]["tmp_name"] as $key => $tmp_name) {
        $file_name = basename($_FILES["gallery_images"]["name"][$key]);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO gallery (image_path) VALUES (?)");
            $stmt->execute([$file_name]);
        }
    }
}

// Fetch settings
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch gallery images
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY id DESC");
$gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "../includes/admin_header.php"; ?>
<div class="admin-container">
    <?php include "../includes/admin_sidebar.php"; ?>

    <main class="admin-main">
        <h2>Site Settings</h2>
        <?php echo $message; ?>

        <form method="POST" enctype="multipart/form-data" class="two-column-form">
            <div class="form-group">
                <label for="site_name">Site Name:</label>
                <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="admin_email">Admin Email:</label>
                <input type="email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" required>
            </div>

            <div class="form-group full-width">
                <label for="announcement">Announcement Message:</label>
                <textarea name="announcement" rows="3"><?php echo htmlspecialchars($settings['announcement'] ?? ''); ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="projects_post">Projects Post:</label>
                <textarea name="projects_post" rows="5"><?php echo htmlspecialchars($settings['projects_post'] ?? ''); ?></textarea>
            </div>

            <div class="form-group full-width">
                <button type="submit">Update Settings</button>
            </div>
        </form>

        <!-- Gallery Upload Section -->
        <h2 style="margin-top: 3rem;">Upload Gallery Images</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="gallery_images[]" multiple accept="image/*">
            <button type="submit">Upload Images</button>
        </form>

        <!-- Display Uploaded Gallery Images -->
        <h2>Gallery</h2>
        <div class="gallery">
            <?php foreach ($gallery_images as $image): ?>
                <div class="gallery-item">
                    <img src="../uploads/gallery/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery Image">
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>


<style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .admin-container {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .admin-sidebar {
        width: 250px;
        background: #333;
        color: white;
        padding: 20px;
        min-height: 100vh;
    }

    .admin-sidebar a {
        color: white;
        display: block;
        padding: 10px;
        text-decoration: none;
    }

    .admin-sidebar a:hover {
        background: #575757;
        border-radius: 5px;
    }

    /* Main Content */
    .admin-main {
        flex: 1;
        padding: 20px;
        background: white;
        margin: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Form Styles */
    form {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .two-column-form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .form-group {
        flex: 1;
        min-width: 300px;
    }
    .full-width {
        flex: 100%;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    button {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px;
        cursor: pointer;
        border-radius: 5px;
        width: 100%;
        font-size: 16px;
    }
    button:hover {
        background: #0056b3;
    }

    /* Gallery Styling */
    .gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 20px;
    }

    .gallery-item {
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .gallery img {
        width: 100%;
        height: auto;
        display: block;
    }


    /* Message Styles */
    .message {
        margin: 15px 0;
        padding: 10px;
        border-radius: 5px;
        font-weight: bold;
    }

    .success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>