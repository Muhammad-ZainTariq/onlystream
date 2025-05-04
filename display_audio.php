<?php
session_start();
require_once 'functions.php';

$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['log_view'])) {
    $audio_id = (int)$_POST['audio_id'];
    $type = $_POST['type'];
    $user_id = isset($_POST['user_id']) && $_POST['user_id'] ? (int)$_POST['user_id'] : null;

    if ($audio_id > 0 && $type === 'audio') {
        log_content_view($pdo, $audio_id, $type, $user_id);
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $audio_id = (int)$_POST['audio_id'];
    $redirect = isset($_GET['id']) ? "display_audio.php?id=$audio_id" : "display_audio.php" . (isset($_GET['genre']) ? "?genre=" . urlencode($_GET['genre']) : "");

    if (isset($_POST['share'])) {
        if ($audio_id) {
            $_SESSION['message'] = share_item($pdo, $audio_id, 'audio');
        } else {
            $_SESSION['error'] = "Invalid audio ID.";
        }
    } elseif (isset($_SESSION['user_id'])) {
        if (isset($_POST['like'])) {
            $_SESSION['message'] = like_item($pdo, $audio_id, $_SESSION['user_id'], 'audio');
        } elseif (isset($_POST['follow'])) {
            $_SESSION['message'] = follow_user($pdo, $_SESSION['user_id'], $_POST['followed_id']);
        } elseif (isset($_POST['comment'])) {
            $comment_text = trim($_POST['comment_text']);
            if ($comment_text) {
                $_SESSION['message'] = add_comment($pdo, $audio_id, $_SESSION['user_id'], $comment_text, 'audio');
            } else {
                $_SESSION['error'] = "Comment cannot be empty.";
            }
        } elseif (isset($_POST['reply'])) {
            $reply_text = trim($_POST['reply_text']);
            if ($reply_text) {
                $_SESSION['message'] = add_reply($pdo, $audio_id, $_SESSION['user_id'], $_POST['parent_comment_id'], $reply_text, 'audio');
            } else {
                $_SESSION['error'] = "Reply cannot be empty.";
            }
        } elseif (isset($_POST['edit_comment'])) {
            $new_comment_text = trim($_POST['new_comment_text']);
            if ($new_comment_text) {
                $_SESSION['message'] = edit_comment($pdo, $_POST['edit_comment_id'], $_SESSION['user_id'], $new_comment_text, 'audio');
            } else {
                $_SESSION['error'] = "Edited comment cannot be empty.";
            }
        } elseif (isset($_POST['delete_comment'])) {
            $_SESSION['message'] = delete_comment($pdo, $_POST['delete_comment_id'], $_SESSION['user_id'], 'audio');
        } elseif (isset($_POST['report'])) {
            $reason = trim($_POST['report_reason']);
            $details = trim($_POST['report_details']);
            if ($reason) {
                $_SESSION['message'] = report_item($pdo, $audio_id, $_SESSION['user_id'], $reason, $details, 'music');
            } else {
                $_SESSION['error'] = "Please select a report reason.";
            }
        }
    }
    header("Location: $redirect");
    exit();
}

$single_audio = null;
if (isset($_GET['id'])) {
    $audio_id = (int)$_GET['id'];
    if ($audio_id) {
        $stmt = $pdo->prepare("
            SELECT audio_files.*, users.first_name, users.last_name, users.profile_picture 
            FROM audio_files 
            JOIN users ON audio_files.user_id = users.id 
            WHERE audio_files.audio_id = ?
        ");
        $stmt->execute([$audio_id]);
        $single_audio = $stmt->fetch();
    }
    if (!$single_audio) {
        $_SESSION['error'] = "Audio not found.";
        header("Location: display_audio.php");
        exit();
    }
}

$genres = get_audio_genres($pdo);
$selected_genre = isset($_GET['genre']) && $_GET['genre'] !== 'all' ? $_GET['genre'] : null;
$audioFiles = $single_audio ? [$single_audio] : get_audio_files($pdo, $current_user_id, $selected_genre);

if (empty($audioFiles) && !$single_audio) {
    $_SESSION['error'] = "No song available.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $single_audio ? htmlspecialchars($single_audio['title']) . ' by ' . htmlspecialchars($single_audio['artist']) : 'Audio Gallery'; ?></title>
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

        <h1><?php echo $single_audio ? 'Audio Player' : 'Audio Gallery'; ?></h1>

        <?php if (!$single_audio): ?>
            <div class="genre-filter">
                <form method="get" action="display_audio.php">
                    <select name="genre" onchange="this.form.submit()">
                        <option value="all" <?php echo !$selected_genre ? 'selected' : ''; ?>>All Genres</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo htmlspecialchars($genre); ?>" <?php echo $selected_genre === $genre ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($audioFiles)): ?>
            <?php foreach ($audioFiles as $audio): ?>
                <?php if ($current_user_id && is_user_blocked($pdo, $audio['user_id'], $current_user_id)) continue; ?>
                <div class="audio">
                    <h2><?php echo htmlspecialchars($audio['title']); ?> by <?php echo htmlspecialchars($audio['artist']); ?></h2>
                    <audio controls controlsList="nodownload" data-audio-id="<?php echo $audio['audio_id']; ?>">
                        <source src="<?php echo htmlspecialchars($audio['file_path']); ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <div class="message error" style="max-width: 550px; display: inline-block;">Views: <?php echo htmlspecialchars(get_item_views($pdo, $audio['audio_id'], 'audio')); ?></div>
                
                    <form id="view-form-<?php echo $audio['audio_id']; ?>" action="display_audio.php" method="post" target="view-iframe-<?php echo $audio['audio_id']; ?>" style="display:none;">
                        <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                        <input type="hidden" name="type" value="audio">
                        <input type="hidden" name="user_id" value="<?php echo $current_user_id; ?>">
                        <input type="hidden" name="log_view" value="1">
                    </form>
                    <iframe name="view-iframe-<?php echo $audio['audio_id']; ?>" style="display:none;"></iframe>

                    <div class="uploader-info">
                        <?php if (!empty($audio['profile_picture'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($audio['profile_picture']); ?>" alt="Profile Picture" class="profile-pic">
                        <?php endif; ?>
                        <span class="uploader-name"><?php echo htmlspecialchars($audio['first_name'] . ' ' . $audio['last_name']); ?></span>
                        <span class="follower-count"><?php echo get_follower_count($pdo, $audio['user_id']); ?> Followers</span>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== $audio['user_id']): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="followed_id" value="<?php echo $audio['user_id']; ?>">
                                <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                <button type="submit" name="follow" class="action-button <?php echo has_user_followed($pdo, $_SESSION['user_id'], $audio['user_id']) ? 'action-button--unfollow' : 'action-button--follow'; ?>">
                                    <?php echo has_user_followed($pdo, $_SESSION['user_id'], $audio['user_id']) ? 'Unfollow' : 'Follow'; ?>
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
                                <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                <button type="submit" name="like" class="action-button action-button--like">
                                    <?php echo has_user_liked($pdo, $audio['audio_id'], $_SESSION['user_id'], 'audio') ? '❤️ Unlike' : '🤍 Like'; ?>
                                </button>
                                <span class="likes-count"><?php echo get_likes_count($pdo, $audio['audio_id'], 'audio'); ?> Likes</span>
                            </form>
                            <button class="action-button action-button--report" onclick="toggleReportForm('report-<?php echo $audio['audio_id']; ?>')">Report</button>
                            <form method="post" style="display:inline;" id="share-form-<?php echo $audio['audio_id']; ?>">
                                <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                <button type="submit" name="share" class="action-button action-button--share">Share</button>
                            </form>
                            <button class="action-button action-button--submit" onclick="toggleCommentForm('comment-<?php echo $audio['audio_id']; ?>')">Comment</button>
                        <?php else: ?>
                            <a href="login.php" class="action-button action-button--submit">Login to like</a>
                            <a href="login.php" class="action-button action-button--submit">Login to report</a>
                            <form method="post" style="display:inline;" id="share-form-<?php echo $audio['audio_id']; ?>">
                                <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                <button type="submit" name="share" class="action-button action-button--share">Share</button>
                            </form>
                            <a href="login.php" class="action-button action-button--submit">Login to comment</a>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div id="report-<?php echo $audio['audio_id']; ?>" class="report-form">
                            <form method="post">
                                <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
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
                        <div id="comment-<?php echo $audio['audio_id']; ?>" class="comment-form">
                            <form method="post">
                                <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                <textarea name="comment_text" placeholder="Add a comment" required></textarea>
                                <button type="submit" name="comment" class="action-button action-button--submit">Post</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="comments-section">
                        <h3>Comments</h3>
                        <?php $comments = get_comments($pdo, $audio['audio_id'], 'audio'); ?>
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
                                                    <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
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
                                            <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                            <textarea name="new_comment_text" required><?php echo htmlspecialchars($comment['comment_text']); ?></textarea>
                                            <button type="submit" name="edit_comment" class="action-button action-button--submit">Save</button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div id="reply-<?php echo $comment['id']; ?>" class="reply-form">
                                        <form method="post">
                                            <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                            <input type="hidden" name="parent_comment_id" value="<?php echo $comment['id']; ?>">
                                            <textarea name="reply_text" placeholder="Write a reply..." required></textarea>
                                            <button type="submit" name="reply" class="action-button action-button--submit">Post Reply</button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <?php $replies = get_replies($pdo, $comment['id'], 'audio'); ?>
                                <?php if (empty($replies)): ?>
                                    <p>No replies yet.</p>
                                <?php else: ?>
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
                                                        <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
                                                        <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (isset($_SESSION['user_id']) && $reply['user_id'] === $_SESSION['user_id']): ?>
                                            <div id="edit-<?php echo $reply['id']; ?>" class="edit-form">
                                                <form method="post">
                                                    <input type="hidden" name="edit_comment_id" value="<?php echo $reply['id']; ?>">
                                                    <input type="hidden" name="audio_id" value="<?php echo $audio['audio_id']; ?>">
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
            <p>No audio files available<?php echo $selected_genre ? ' in the ' . htmlspecialchars($selected_genre) . ' genre' : ''; ?>.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleEditForm(formId) { document.getElementById(formId).classList.toggle('active'); }
        function toggleReportForm(formId) { document.getElementById(formId).classList.toggle('active'); }
        function toggleReplyForm(formId) { document.getElementById(formId).classList.toggle('active'); }
        function toggleCommentForm(formId) { document.getElementById(formId).classList.toggle('active'); }

        document.querySelectorAll('form[id^="share-form-"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const audioId = this.querySelector('input[name="audio_id"]').value;
        const url = `http://localhost/onlystream/display_audio.php?id=${audioId}`;        /*Reference: https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText */
        navigator.clipboard.writeText(url);
    });
});
    
        document.querySelectorAll('audio').forEach(audio => {
            let hasLoggedView = false;
            audio.addEventListener('play', function() {
                if (!hasLoggedView) {
                    const audioId = audio.getAttribute('data-audio-id');
                    const form = document.getElementById(`view-form-${audioId}`);
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