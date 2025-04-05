<?php
session_start();
require_once "../includes/inc_header.php";
require_once "../includes/db.php";

// Ensure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Fetch settings (Announcements & Projects)
$stmt = $pdo->query("SELECT announcement, projects_post FROM settings ORDER BY id DESC LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['announcement' => 'No announcements available.', 'projects_post' => 'No project updates available.'];

// Fetch user details
$stmt = $pdo->prepare("SELECT full_name, email, profile_picture, status FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if account is not approved
if ($member['status'] !== 'approved') {
    echo "<p style='color: red; text-align: center;'>Your account is pending approval.</p>";
    exit();
}

// Fetch user's gallery images
$stmt = $pdo->prepare("SELECT * FROM gallery WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-container">

<?php include "../includes/inc_sidebar.php"; ?>
    <main class="dashboard-main">
        <h2>📊 Member Dashboard</h2>

        <div style="margin-bottom: 3rem;" class="member-info">
            <img src="../uploads/profiles/<?php echo htmlspecialchars($member["profile_picture"]); ?>" alt="Profile Picture" class="profile-pic">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($member["full_name"]); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($member["email"]); ?></p>
        </div>

        <section style="margin-bottom: 3rem;" class="announcement">
            <h3>📢 Announcements</h3>
            <p><?php echo nl2br(htmlspecialchars($settings["announcement"])); ?></p>
        </section>

        <section style="margin-bottom: 3rem;" class="projects">
            <h3>📝 Project Updates</h3>
            <p><?php echo nl2br(htmlspecialchars($settings["projects_post"])); ?></p>
        </section>

        <!-- Gallery Upload Section -->
        <h3 style="margin-bottom: 3rem;">🖼️ Upload Your Gallery Images</h3>
        <form action="upload_gallery.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="gallery_images[]" multiple accept="image/*">
            <button type="submit">Upload Images</button>
        </form>

        <!-- Display Uploaded Gallery Images -->
        <h3 style="margin-bottom: 3rem;">📸 Your Gallery</h3>
        <div class="gallery">
            <?php foreach ($gallery_images as $image): ?>
                <div class="gallery-item">
                    <img src="../uploads/gallery/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery Image">
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

