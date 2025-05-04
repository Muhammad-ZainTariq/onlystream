<?php
session_start();
require 'functions.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: staff_login.php");
    exit();
}

if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$staff_name = get_staff_name($pdo, $staff_id);

$videos = get_all_videos($pdo);
if (empty($videos) && !isset($_SESSION['error'])) {
    $_SESSION['error'] = "Failed to fetch videos.";
}

$audioFiles = get_all_audio_files($pdo);
if (empty($audioFiles) && !isset($_SESSION['error'])) {
    $_SESSION['error'] = "Failed to fetch music.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_video'])) {
    $video_id = (int)$_POST['video_id'];
    $result = delete_video($pdo, $video_id);
    $_SESSION['message'] = $result;
    header("Location: adminwork.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_audio'])) {
    $audio_id = (int)$_POST['audio_id'];
    $result = delete_audio($pdo, $audio_id);
    $_SESSION['message'] = $result;
    header("Location: adminwork.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = (int)$_POST['comment_id'];
    $content_id = (int)$_POST['content_id'];
    $content_type = $_POST['content_type'];
    $result = delete_admin_comment($pdo, $comment_id, $content_id, $content_type);
    $_SESSION['message'] = $result;
    header("Location: adminwork.php?content_id=" . $content_id . "&content_type=" . $content_type);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OnlyStream</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'admin_navigation.php'; ?>
    
    <div class="container">
        <h1>Admin Dashboard</h1>
        
        <div class="message success">
            Welcome, <?php echo $staff_name; ?>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="admin-section">
            <h2>Videos</h2>
            <?php if (empty($videos)): ?>
                <p class="no-data">No videos available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Video Title</th>
                            <th>Video</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($videos as $video): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($video['video_title']); ?></td>
                                <td>
                                    <video width="160" height="120" controls>
                                        <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                        <button type="submit" name="delete_video" class="action-button action-button--delete">Delete Video</button>
                                    </form>
                                    <form method="get" style="display:inline;">
                                        <input type="hidden" name="content_id" value="<?php echo $video['id']; ?>">
                                        <input type="hidden" name="content_type" value="video">
                                        <button type="submit" class="action-button action-button--submit">Manage Comments</button>
                                    </form>
                                </td>
                            </tr>
                            <?php if (isset($_GET['content_id']) && $_GET['content_id'] == $video['id'] && $_GET['content_type'] === 'video'): ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="comment-section">
                                            <h3>Comments for: <?php echo htmlspecialchars($video['video_title']); ?></h3>
                                            <?php
                                            $comments = get_content_comments($pdo, $video['id'], 'video');
                                            if (empty($comments)) {
                                                echo "<p class='no-data'>No comments available for this video.</p>";
                                            } else {
                                                foreach ($comments as $comment): ?>
                                                    <div class="comment <?php echo $comment['parent_comment_id'] ? 'reply' : ''; ?>">
                                                        <strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?>:</strong>
                                                        <p class="comment-text"><?php echo $comment['parent_comment_id'] ? '↳ ' : ''; ?><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                            <input type="hidden" name="content_id" value="<?php echo $video['id']; ?>">
                                                            <input type="hidden" name="content_type" value="video">
                                                            <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete Comment</button>
                                                        </form>
                                                    </div>
                                                <?php endforeach;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="admin-section">
            <h2>Audio Files</h2>
            <?php if (empty($audioFiles)): ?>
                <p class="no-data">No audio files available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Audio Title</th>
                            <th>Artist</th>
                            <th>Audio</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audioFiles as $audio): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($audio['title']); ?></td>
                                <td><?php echo htmlspecialchars($audio['artist']); ?></td>
                                <td>
                                    <audio controls controlsList="nodownload">
                                        <source src="<?php echo htmlspecialchars($audio['file_path']); ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                        <button type="submit" name="delete_audio" class="action-button action-button--delete">Delete Audio</button>
                                    </form>
                                    <form method="get" style="display:inline;">
                                        <input type="hidden" name="content_id" value="<?php echo $audio['audio_id']; ?>">
                                        <input type="hidden" name="content_type" value="audio">
                                        <button type="submit" class="action-button action-button--submit">Manage Comments</button>
                                    </form>
                                </td>
                            </tr>
                            <?php if (isset($_GET['content_id']) && $_GET['content_id'] == $audio['audio_id'] && $_GET['content_type'] === 'audio'): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="comment-section">
                                            <h3>Comments for: <?php echo htmlspecialchars($audio['title']); ?> by <?php echo htmlspecialchars($audio['artist']); ?></h3>
                                            <?php
                                            $comments = get_content_comments($pdo, $audio['audio_id'], 'audio');
                                            if (empty($comments)) {
                                                echo "<p class='no-data'>No comments available for this audio.</p>";
                                            } else {
                                                foreach ($comments as $comment): ?>
                                                    <div class="comment <?php echo $comment['parent_comment_id'] ? 'reply' : ''; ?>">
                                                        <strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?>:</strong>
                                                        <p class="comment-text"><?php echo $comment['parent_comment_id'] ? '↳ ' : ''; ?><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                            <input type="hidden" name="content_id" value="<?php echo $audio['audio_id']; ?>">
                                                            <input type="hidden" name="content_type" value="audio">
                                                            <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete Comment</button>
                                                        </form>
                                                    </div>
                                                <?php endforeach;
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>