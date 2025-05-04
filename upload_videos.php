<?php
session_start();
require_once 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = get_user_membership($pdo, $user_id);
if (!$user || $user['membership_tier'] == 0) {
    $_SESSION['message'] = "You need a Creator Membership to upload videos. Please upgrade!";
    header("Location: membership.php");
    exit();
}


if ($user['video_uploads_left'] <= 0) {
    $_SESSION['message'] = "You’ve reached your video upload limit. Please upgrade your membership!";
    header("Location: membership.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {
    $result = upload_video($pdo, $user_id, $_FILES['video_file'], $_POST['video_title'], $_POST['description'], $_POST['category']);
    if (strpos($result, "successfully") !== false) {
        $_SESSION['message'] = $result;
    } else {
        $_SESSION['error'] = $result;
    }
    header("Location: upload_videos.php");
    exit();
}


$categories = get_upload_categories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="container">
        <h1>Upload Your Video</h1>

        
        <div>
            <a href="creatorhub.php" class="action-button action-button--submit">Back to Dashboard</a>
        </div>

        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="video_title" placeholder="Video Title" required>
            <textarea name="description" placeholder="Video Description" required></textarea>
            <div class="select-wrapper">
                <select name="category" required>
                    <option value="" disabled selected>Select a Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>">
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="file" name="video_file" accept="video/*" required>
            <button type="submit" class="action-button action-button--upload">Upload</button>
        </form>
    </div>
</body>
</html>