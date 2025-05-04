<?php
session_start();
require_once 'functions.php';

$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['log_view'])) {
    $video_id = (int)$_POST['video_id'];
    $type = $_POST['type'];
    $user_id = isset($_POST['user_id']) && $_POST['user_id'] ? (int)$_POST['user_id'] : null;

    if ($video_id > 0 && $type === 'video') {
        log_content_view($pdo, $video_id, $type, $user_id);
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['share'])) {
    $video_id = (int)$_POST['video_id'];
    if ($video_id) {
        $_SESSION['message'] = share_item($pdo, $video_id, 'video');
    } else {
        $_SESSION['error'] = "Invalid video ID.";
    }
    $redirect = isset($_GET['id']) ? "retreive_videos.php?id=$video_id" : "retreive_videos.php";
    if (isset($_GET['category'])) {
        $redirect .= "?category=" . urlencode($_GET['category']);
    }
    header("Location: $redirect");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['user_id'])) {
    $video_id = (int)$_POST['video_id'];
    $redirect = isset($_GET['id']) ? "retreive_videos.php?id=$video_id" : "retreive_videos.php";
    if (isset($_GET['category'])) {
        $redirect .= "?category=" . urlencode($_GET['category']);
    }

    if (isset($_POST['like'])) {
        $_SESSION['message'] = like_item($pdo, $video_id, $_SESSION['user_id'], 'video');
    } elseif (isset($_POST['follow'])) {
        $_SESSION['message'] = follow_user($pdo, $_SESSION['user_id'], $_POST['followed_id']);
    } elseif (isset($_POST['comment'])) {
        $comment_text = trim($_POST['comment_text']);
        if ($comment_text) {
            $_SESSION['message'] = add_comment($pdo, $video_id, $_SESSION['user_id'], $comment_text, 'video');
        } else {
            $_SESSION['error'] = "Comment cannot be empty.";
        }
    } elseif (isset($_POST['reply'])) {
        $reply_text = trim($_POST['reply_text']);
        if ($reply_text) {
            $_SESSION['message'] = add_reply($pdo, $video_id, $_SESSION['user_id'], $_POST['parent_comment_id'], $reply_text, 'video');
        } else {
            $_SESSION['error'] = "Reply cannot be empty.";
        }
    } elseif (isset($_POST['edit_comment'])) {
        $new_comment_text = trim($_POST['new_comment_text']);
        if ($new_comment_text) {
            $_SESSION['message'] = edit_comment($pdo, $_POST['edit_comment_id'], $_SESSION['user_id'], $new_comment_text, 'video');
        } else {
            $_SESSION['error'] = "Edited comment cannot be empty.";
        }
    } elseif (isset($_POST['delete_comment'])) {
        $_SESSION['message'] = delete_comment($pdo, $_POST['delete_comment_id'], $_SESSION['user_id'], 'video');
    } elseif (isset($_POST['report'])) {
        $reason = trim($_POST['report_reason']);
        $details = trim($_POST['report_details']);
        if ($reason) {
            $_SESSION['message'] = report_item($pdo, $video_id, $_SESSION['user_id'], $reason, $details, 'video');
        } else {
            $_SESSION['error'] = "Please select a report reason.";
        }
    }
    header("Location: $redirect");
    exit();
}

$single_video = null;
if (isset($_GET['id'])) {
    $video_id = (int)$_GET['id'];
    if ($video_id) {
        $stmt = $pdo->prepare("
            SELECT ideosv.*, users.first_name, users.last_name, users.profile_picture 
            FROM videos 
            JOIN users ON videos.user_id = users.id 
            WHERE videos.id = ?
        ");
        $stmt->execute([$video_id]);
        $single_video = $stmt->fetch();
    }
    if (!$single_video) {
        $_SESSION['error'] = "Video not found.";
        header("Location: retreive_videos.php");
        exit();
    }
}

$categories = get_video_categories($pdo);
$selected_category = isset($_GET['category']) && $_GET['category'] !== 'all' ? $_GET['category'] : null;
$videos = $single_video ? [$single_video] : get_videos($pdo, $current_user_id, $selected_category);

if (empty($videos) && !$single_video) {
    $_SESSION['error'] = "No videos available.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php if ($single_video) { echo htmlspecialchars($single_video['video_title']); } else { echo 'Video Gallery'; } ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <h1><?php if ($single_video) { echo 'Video Player'; } else { echo 'Video Gallery'; } ?></h1>

        <?php if (!$single_video): ?>
            <div class="category-filter">
                <form method="get" action="retreive_videos.php">
                    <select name="category" onchange="this.form.submit()">
                        <option value="all" <?php if (!$selected_category) { echo 'selected'; } ?>>All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php if ($selected_category === $category) { echo 'selected'; } ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($videos)): ?>
            <?php foreach ($videos as $video): ?>
                <?php if ($current_user_id && is_user_blocked($pdo, $video['user_id'], $current_user_id)) continue; ?>
                <div class="video">
                    <h2><?php echo htmlspecialchars($video['video_title']); ?></h2>
                    <video controls controlsList="nodownload" width="<?php if ($single_video) { echo '640'; } else { echo '320'; } ?>" data-video-id="<?php echo $video['id']; ?>">
                        <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="message error" style="max-width: 550px; display: inline-block;">Views: <?php echo htmlspecialchars(get_item_views($pdo, $video['id'], 'video')); ?></div>
                
                    <form id="view-form-<?php echo $video['id']; ?>" action="retreive_videos.php" method="post" target="view-iframe-<?php echo $video['id']; ?>" style="display:none;">
                        <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                        <input type="hidden" name="type" value="video">
                        <input type="hidden" name="user_id" value="<?php echo $current_user_id; ?>">
                        <input type="hidden" name="log_view" value="1">
                    </form>
                    <iframe name="view-iframe-<?php echo $video['id']; ?>" style="display:none;"></iframe>

                    <div class="uploader-info">
                        <?php if (!empty($video['profile_picture'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($video['profile_picture']); ?>" alt="Profile Picture" class="profile-pic">
                        <?php endif; ?>
                        <span class="uploader-name"><?php echo htmlspecialchars($video['first_name'] . ' ' . $video['last_name']); ?></span>
                        <span class="follower-count"><?php echo htmlspecialchars(get_follower_count($pdo, $video['user_id'])); ?> Followers</span>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $video['user_id']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="followed_id" value="<?php echo $video['user_id']; ?>">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" name="follow" class="action-button <?php if (has_user_followed($pdo, $_SESSION['user_id'], $video['user_id'])) { echo 'action-button--unfollow'; } else { echo 'action-button--follow'; } ?>">
                                    <?php if (has_user_followed($pdo, $_SESSION['user_id'], $video['user_id'])) { echo 'Unfollow'; } else { echo 'Follow'; } ?>
                                </button>
                            </form>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" class="action-button action-button--submit">Login to follow</a>
                        <?php else: ?>
                            <span>You cannot follow yourself.</span>
                        <?php endif; ?>
                    </div>

                    <div class="action-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" name="like" class="action-button action-button--like">
                                    <?php if (has_user_liked($pdo, $video['id'], $_SESSION['user_id'], 'video')) { echo '❤️ Unlike'; } else { echo '🤍 Like'; } ?>
                                </button>
                                <span class="likes-count"><?php echo htmlspecialchars(get_likes_count($pdo, $video['id'], 'video')); ?> Likes</span>
                            </form>
                            <button class="action-button action-button--report" onclick="toggleReportForm('report-<?php echo $video['id']; ?>')">Report</button>
                            <form method="post" style="display:inline;" id="share-form-<?php echo $video['id']; ?>">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" name="share" class="action-button action-button--share">Share</button>
                            </form>
                            <button class="action-button action-button--submit" onclick="toggleCommentForm('comment-<?php echo $video['id']; ?>')">Comment</button>
                        <?php else: ?>
                            <a href="login.php" class="action-button action-button--submit">Login to like</a>
                            <a href="login.php" class="action-button action-button--submit">Login to report</a>
                            <form method="post" style="display:inline;" id="share-form-<?php echo $video['id']; ?>">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" name="share" class="action-button action-button--share">Share</button>
                            </form>
                            <a href="login.php" class="action-button action-button--submit">Login to comment</a>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div id="report-<?php echo $video['id']; ?>" class="report-form">
                            <form method="post">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <select name="report_reason" required>
                                    <option value="" disabled selected>Select a reason</option>
                                    <option value="Copyright Issue">Copyright Issue</option>
                                    <option value="Racism">Racism</option>
                                    <option value="Self Harm">Self Harm</option>
                                    <option value="Terrorism">Terrorism</option>
                                    <option value="Harmful Content">Harmful Content</option>
                                </select>
                                <textarea name="report_details" placeholder="Additional details (optional)"></textarea>
                                <button type="submit" name="report" class="action-button action-button--submit">Submit Report</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div id="comment-<?php echo $video['id']; ?>" class="comment-form">
                            <form method="post">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <textarea name="comment_text" placeholder="Add a comment" required></textarea>
                                <button type="submit" name="comment" class="action-button action-button--submit">Post</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($single_video): ?>
                        <div class="video-description">
                            <h3>Description</h3>
                            <p><?php echo htmlspecialchars($video['description']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="comments-section">
                        <h3>Comments</h3>
                        <?php $comments = get_comments($pdo, $video['id'], 'video'); ?>
                        <?php if (empty($comments)): ?>
                            <p>No comments yet.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <div>
                                        <strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong>
                                        <p class="comment-text"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                        <small class="comment-meta"><?php echo $comment['created_at']; ?></small>
                                    </div>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <div class="comment-actions">
                                            <?php if ($comment['user_id'] === $_SESSION['user_id']): ?>
                                                <button class="action-button action-button--submit" onclick="toggleEditForm('edit-<?php echo $comment['id']; ?>')">Edit</button>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                                                    <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                                    <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                            <button class="action-button action-button--reply" onclick="toggleReplyForm('reply-<?php echo $comment['id']; ?>')">Reply</button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($_SESSION['user_id']) && $comment['user_id'] === $_SESSION['user_id']): ?>
                                    <div id="edit-<?php echo $comment['id']; ?>" class="edit-form">
                                        <form method="post">
                                            <input type="hidden" name="edit_comment_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <textarea name="new_comment_text" required><?php echo htmlspecialchars($comment['comment_text']); ?></textarea>
                                            <button type="submit" name="edit_comment" class="action-button action-button--submit">Save</button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div id="reply-<?php echo $comment['id']; ?>" class="reply-form">
                                        <form method="post">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <input type="hidden" name="parent_comment_id" value="<?php echo $comment['id']; ?>">
                                            <textarea name="reply_text" placeholder="Write a reply..." required></textarea>
                                            <button type="submit" name="reply" class="action-button action-button--submit">Post Reply</button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <?php $replies = get_replies($pdo, $comment['id'], 'video'); ?>
                                <?php if (!empty($replies)): ?>
                                    <h4>Replies</h4>
                                    <?php foreach ($replies as $reply): ?>
                                        <div class="reply">
                                            <div>
                                                <strong><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?></strong>
                                                <p class="comment-text">↳ <?php echo htmlspecialchars($reply['comment_text']); ?></p>
                                                <small class="comment-meta"><?php echo $reply['created_at']; ?></small>
                                            </div>
                                            <?php if (isset($_SESSION['user_id']) && $reply['user_id'] === $_SESSION['user_id']): ?>
                                                <div class="comment-actions">
                                                    <button class="action-button action-button--submit" onclick="toggleEditForm('edit-<?php echo $reply['id']; ?>')">Edit</button>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="delete_comment_id" value="<?php echo $reply['id']; ?>">
                                                        <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                                        <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (isset($_SESSION['user_id']) && $reply['user_id'] === $_SESSION['user_id']): ?>
                                            <div id="edit-<?php echo $reply['id']; ?>" class="edit-form">
                                                <form method="post">
                                                    <input type="hidden" name="edit_comment_id" value="<?php echo $reply['id']; ?>">
                                                    <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                                    <textarea name="new_comment_text" required><?php echo htmlspecialchars($reply['comment_text']); ?></textarea>
                                                    <button type="submit" name="edit_comment" class="action-button action-button--submit">Save</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No videos available<?php if ($selected_category) { echo ' in the ' . htmlspecialchars($selected_category) . ' category'; } ?>.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleEditForm(formId) { document.getElementById(formId).classList.toggle('active'); }
        function toggleReportForm(formId) { document.getElementById(formId).classList.toggle('active'); }
        function toggleReplyForm(formId) { document.getElementById(formId).classList.toggle('active'); }
        function toggleCommentForm(formId) { document.getElementById(formId).classList.toggle('active'); }

        document.querySelectorAll('form[id^="share-form-"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const videoId = this.querySelector('input[name="video_id"]').value;
        const url = `http://localhost/onlystream/retreive_videos.php?id=${videoId}`;             /*Reference: https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText */
        navigator.clipboard.writeText(url);
    });
});
        document.querySelectorAll('video').forEach(video => {
            let hasLoggedView = false;
            video.addEventListener('play', function() {
                if (!hasLoggedView) {
                    const videoId = video.getAttribute('data-video-id');
                    const form = document.getElementById(`view-form-${videoId}`);
                    if (form) {
                        form.submit();
                        hasLoggedView = true;
                    }
                }
            });
        });
    </script>
</body>
</html>