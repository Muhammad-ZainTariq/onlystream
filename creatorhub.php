<?php
session_start();
require 'db_connection.php';
require_once 'functions.php';

$user_id = require_login("Please log in to access the Creator Hub.");

$user_membership = get_user_membership($pdo, $user_id);
if ($user_membership === null) {
    $_SESSION['error'] = "User not found. Please log in again.";
    header("Location: login.php");
    exit();
}

$tier = $user_membership['membership_tier'];
$expires_at = $user_membership['membership_expires_at'];
$videos_left = $user_membership['video_uploads_left'];
$audios_left = $user_membership['music_uploads_left'];

if ($tier == 0) {
    $_SESSION['message'] = "You need a Creator Membership to access the Creator Hub. Please upgrade!";
    header("Location: membership.php");
    exit();
}

check_membership_expiration($pdo, $user_id, $tier, $expires_at, true);

if (isset($_GET['email_receipt'])) {
    $python_script = escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'send_receipt.py');
    $user_id_escaped = escapeshellarg($user_id);
    $command = "python $python_script $user_id_escaped 2>&1";
    exec($command, $output, $return_var);
    $output_message = !empty($output) ? $output[0] : "Unknown error occurred.";
    if ($return_var === 0) {
        $_SESSION['message'] = "Receipt sent to your email!";
    } else {
        $_SESSION['error'] = "Failed to send receipt: " . htmlspecialchars($output_message);
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_content'])) {
    $content_id = (int)$_POST['content_id'];
    $content_type = $_POST['content_type'];
    $new_title = trim($_POST['new_title']);
    if ($content_id && $new_title) {
        edit_content($pdo, $user_id, $content_id, $content_type, $new_title);
        $_SESSION['message'] = "Title updated successfully!";
    } else {
        $_SESSION['error'] = "Invalid content or title.";
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_content'])) {
    $content_id = (int)$_POST['content_id'];
    $content_type = $_POST['content_type'];
    if ($content_id) {
        delete_content($pdo, $user_id, $content_id, $content_type);
        $_SESSION['message'] = "Content deleted successfully!";
    } else {
        $_SESSION['error'] = "Invalid content selected.";
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_comment'])) {
    $comment_id = (int)$_POST['comment_id'];
    $reply_text = trim($_POST['reply_text']);
    $result = add_reply_to_comment($pdo, $user_id, $comment_id, $reply_text);
    if ($result === "Reply added successfully.") {
        $_SESSION['message'] = $result;
    } else {
        $_SESSION['error'] = $result;
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = (int)$_POST['comment_id'];
    $result = delete_user_content_comment($pdo, $user_id, $comment_id);
    if ($result === "Comment deleted successfully.") {
        $_SESSION['message'] = $result;
    } else {
        $_SESSION['error'] = $result;
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_user'])) {
    $blocked_id = (int)$_POST['blocked_id'];
    if ($blocked_id && $blocked_id != $user_id) {
        block_user($pdo, $user_id, $blocked_id);
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$blocked_id, $user_id]);
        $_SESSION['message'] = "User blocked successfully.";
    } else {
        $_SESSION['error'] = "Invalid user to block or cannot block yourself.";
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblock_user'])) {
    $blocked_id = (int)$_POST['blocked_id'];
    if ($blocked_id) {
        unblock_user($pdo, $user_id, $blocked_id);
        $_SESSION['message'] = "User unblocked successfully.";
    } else {
        $_SESSION['error'] = "Invalid user to unblock.";
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_follower'])) {
    $follower_id = (int)$_POST['follower_id'];
    if ($follower_id && $follower_id != $user_id) {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$follower_id, $user_id]);
        $_SESSION['message'] = "Follower removed successfully.";
    } else {
        $_SESSION['error'] = "Invalid follower to remove.";
    }
    header("Location: creatorhub.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_membership'])) {
    $stmt = $pdo->prepare("UPDATE users SET membership_tier = 0, video_uploads_left = 0, music_uploads_left = 0, membership_expires_at = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['message'] = "Membership canceled.";
    header("Location: membership.php");
    exit();
}

$content_list = get_user_content($pdo, $user_id);
$video_likes = get_video_likes($pdo, $user_id);
$audio_likes = get_audio_likes($pdo, $user_id);
$comments = get_comments_on_user_content($pdo, $user_id);
$blocked_users = get_blocked_users($pdo, $user_id);

$stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name FROM follows f JOIN users u ON f.follower_id = u.id WHERE f.followed_id = ?");
$stmt->execute([$user_id]);
$followers = $stmt->fetchAll();

$analytics = get_creator_analytics($pdo, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Hub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'navigation.php'; ?>
    <div class="container">
        <h1>Creator Hub</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="dashboard">
            <div class="card">
                <h2>Upload Content</h2>
                <p>Videos left: <?php echo htmlspecialchars($videos_left) ?>, Audios left: <?php echo htmlspecialchars($audios_left) ?></p>
                <a href="upload_videos.php" class="action-button action-button--upload">Upload Videos</a>
                <a href="upload_audio.php" class="action-button action-button--upload">Upload Audio</a>
            </div>
            <div class="card">
                <h2>Your Analytics</h2>
                <p>Video Views: <?php echo htmlspecialchars($analytics['views'][0]['views'] ?? 0); ?></p>
                <p>Audio Views: <?php echo htmlspecialchars($analytics['views'][1]['views'] ?? 0); ?></p>
                <p>Video Likes: <?php echo htmlspecialchars($analytics['video_likes']) ?></p>
                <p>Audio Likes: <?php echo htmlspecialchars($analytics['audio_likes']) ?></p>
                <p>Comments: <?php echo htmlspecialchars($analytics['comments']) ?></p>
                <p>Followers: <?php echo htmlspecialchars($analytics['followers']) ?></p>
            </div>
            <div class="card">
                <h2>Membership</h2>
                <p>Tier: <?php if ($tier == 1) { echo 'Tier 1 (Free)'; } else { echo 'Tier 2 (£5/month)'; } ?></p>
                <p>Expires: <?php if ($expires_at) { echo htmlspecialchars($expires_at); } else { echo 'N/A'; } ?></p>
                <button class="action-button action-button--receipt" onclick="window.location.href='creatorhub.php?email_receipt=1'">Email Receipt</button>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="cancel_membership" class="action-button action-button--delete">Cancel Membership</button>
                </form>
            </div>
            <div class="card">
                <h2>Your Followers</h2>
                <?php if (empty($followers)): ?>
                    <p>No followers yet.</p>
                <?php else: ?>
                    <?php foreach ($followers as $follower): ?>
                        <div class="follower">
                            <p><strong><?php echo htmlspecialchars($follower['first_name'] . ' ' . $follower['last_name']) ?></strong></p>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="follower_id" value="<?php echo htmlspecialchars($follower['id']) ?>">
                                <button type="submit" name="remove_follower" class="action-button action-button--delete">Remove Follower</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="blocked_id" value="<?php echo htmlspecialchars($follower['id']) ?>">
                                <button type="submit" name="block_user" class="action-button action-button--block">Block</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="content-section">
            <h2>Your Content</h2>
            <?php if (empty($content_list)): ?>
                <p>No content yet. Start uploading!</p>
            <?php else: ?>
                <table class="content-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Media</th>
                            <th>Views</th>
                            <th>Likes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($content_list as $item): ?>
                            <?php
                            $item_id = isset($item['id']) ? (int)$item['id'] : 0;
                            $item_type = isset($item['type']) && in_array($item['type'], ['video', 'audio']) ? $item['type'] : '';
                            if (!$item_id || !$item_type) {
                                continue;
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title'] ?? 'Untitled'); ?> (<?php echo htmlspecialchars($item_type); ?>)</td>
                                <td>
                                    <?php if ($item_type === 'video'): ?>
                                        <video width="160" height="120" controls controlsList="nodownload">
                                            <source src="<?php echo htmlspecialchars($item['media_path'] ?? ''); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php else: ?>
                                        <audio controls controlsList="nodownload">
                                            <source src="<?php echo htmlspecialchars($item['media_path'] ?? ''); ?>" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(get_item_views($pdo, $item_id, $item_type)); ?></td>
                                <td><?php echo htmlspecialchars(get_likes_count($pdo, $item_id, $item_type)); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="text" name="new_title" value="<?php echo htmlspecialchars($item['title'] ?? 'Untitled'); ?>" required>
                                        <input type="hidden" name="content_id" value="<?php echo htmlspecialchars($item_id); ?>">
                                        <input type="hidden" name="content_type" value="<?php echo htmlspecialchars($item_type); ?>">
                                        <button type="submit" name="edit_content" class="action-button action-button--submit">Save Title</button>
                                        <button type="submit" name="delete_content" class="action-button action-button--delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="comments-section">
            <h2>Comments on Your Content</h2>
            <?php if (empty($comments)): ?>
                <p>No comments yet.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p><strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?></strong> on 
                            <?php if ($comment['content_type'] == 'video') { echo htmlspecialchars($comment['video_title']); } else { echo htmlspecialchars($comment['audio_title']); } ?>:</p>
                        <p class="comment-text"><?php echo htmlspecialchars($comment['comment_text']) ?></p>
                        <p><small><?php echo $comment['created_at'] ?></small></p>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id'] ?>">
                            <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete Comment</button>
                        </form>
                        <?php if (!$comment['parent_comment_id']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id'] ?>">
                                <textarea name="reply_text" placeholder="Write a reply..." required></textarea>
                                <button type="submit" name="reply_comment" class="action-button action-button--submit">Reply</button>
                            </form>
                        <?php endif; ?>
                        <?php $replies = get_replies_on_user_content($pdo, $user_id, $comment['id']); ?>
                        <?php if (!empty($replies)): ?>
                            <h4>Replies</h4>
                            <?php foreach ($replies as $reply): ?>
                                <div class="reply">
                                    <p><strong><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']) ?></strong></p>
                                    <p class="comment-text">↳ <?php echo htmlspecialchars($reply['comment_text']) ?></p>
                                    <p><small><?php echo $reply['created_at'] ?></small></p>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo $reply['id'] ?>">
                                        <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete Reply</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="blocked-section">
            <h2>Blocked Users</h2>
            <?php if (empty($blocked_users)): ?>
                <p>No users blocked yet.</p>
            <?php else: ?>
                <?php foreach ($blocked_users as $blocked): ?>
                    <div class="blocked-user">
                        <p><strong><?php echo htmlspecialchars($blocked['first_name'] . ' ' . $blocked['last_name']) ?></strong></p>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="blocked_id" value="<?php echo htmlspecialchars($blocked['id']) ?>">
                            <button type="submit" name="unblock_user" class="action-button action-button--follow">Unblock</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
