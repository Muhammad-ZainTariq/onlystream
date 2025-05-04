<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$user = get_user_membership($pdo, $user_id);
if (!$user) {
    $_SESSION['error'] = "User not found. Please log in again.";
    header("Location: login.php");
    exit;
}


if ($user['membership_tier'] == 0) {
    $_SESSION['message'] = "You need a Creator Membership to upload audio files. Please upgrade!";
    header("Location: membership.php");
    exit;
}


if ($user['membership_expires_at'] && new DateTime() > new DateTime($user['membership_expires_at'])) {
    $stmt = $pdo->prepare("UPDATE users SET membership_tier = 0, video_uploads_left = 0, music_uploads_left = 0, membership_expires_at = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['message'] = "Your membership has expired. Please renew it!";
    header("Location: membership.php");
    exit;
}


if ($user['music_uploads_left'] <= 0) {
    $_SESSION['message'] = "You’ve reached your audio upload limit. Please upgrade your membership!";
    header("Location: membership.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio_file'])) {
    
    unset($_SESSION['message'], $_SESSION['error']);
    
    $result = upload_audio($pdo, $user_id, $_FILES['audio_file'], $_POST['title'], $_POST['artist'], $_POST['album'], $_POST['genre']);
    if ($result === true) {
        $_SESSION['message'] = "Audio uploaded successfully!";
    } else {
        $_SESSION['error'] = $result;
    }
    header("Location: upload_audio.php");
    exit;
}


$predefinedGenres = get_predefined_audio_genres();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Audio</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="container">
        <h1>Upload Your Audio</h1>

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
            <input type="text" name="title" placeholder="Audio Title" required>
            <input type="text" name="artist" placeholder="Artist">
            <input type="text" name="album" placeholder="Album">
            <div class="select-wrapper">
                <select name="genre" required>
                    <option value="" disabled selected>Select Genre</option>
                    <?php foreach ($predefinedGenres as $genre): ?>
                        <option value="<?php echo htmlspecialchars($genre); ?>">
                            <?php echo htmlspecialchars($genre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="file" name="audio_file" accept="audio/*" required>
            <button type="submit" class="action-button action-button--upload">Upload</button>
        </form>
    </div>
</body>
</html>