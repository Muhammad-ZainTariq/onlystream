    <?php
    session_start();       /* admin login: admin@gmail.com
                             password = Siolfiol7924@
    
                                              user: user@gmail.com
                                                    password:User123@ */


    require 'functions.php';

    if (!isset($_SESSION['staff_id'])) {
        header("Location: staff_login.php");
        exit();        
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_item'])) {
        $item_id = $_POST['item_id'];
        $item_type = $_POST['item_type'];
        $result = delete_reported_item($pdo, $item_id, $item_type);
        if ($result === "Item deleted successfully.") {
            $_SESSION['message'] = $result;
        } else {
            $_SESSION['error'] = $result;
        }
        header("Location: admin_reports.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_comment'])) {
        $comment_id = $_POST['comment_id'];
        $item_id = $_POST['item_id'];
        $item_type = $_POST['item_type'];
        $content_type = '';
        if ($item_type === 'video') {
            $content_type = 'video';
        } else {
            $content_type = 'audio';
        }
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND item_id = ? AND content_type = ?");
        $stmt->execute([$comment_id, $item_id, $content_type]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Comment deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete comment.";
        }
        header("Location: admin_reports.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_message'])) {
        $sender_id = $_SESSION['staff_id'];
        $receiver_id = $_POST['receiver_id'];
        $message_text = $_POST['message_text'];
        $result = send_admin_message($pdo, $sender_id, $receiver_id, $message_text);
        if ($result === "Message sent successfully.") {
            $_SESSION['message'] = $result;
        } else {
            $_SESSION['error'] = $result;
        }
        header("Location: admin_reports.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {
        $report_id = $_POST['report_id'];
        $new_status = $_POST['new_status'];
        $result = update_report_status($pdo, $report_id, $new_status);
        if ($result === "Report status updated successfully.") {
            $_SESSION['message'] = $result;
        } else {
            $_SESSION['error'] = $result;
        }
        header("Location: admin_reports.php");
        exit();
    }

    $reported_items = get_reported_items($pdo);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Staff - Manage Reports</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include 'admin_navigation.php'; ?>
        
        <div class="container">
            <div class="header">
                <h1>Manage Reports (Videos & Music)</h1>
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

            <?php if (!empty($reported_items)): ?>
                <?php 
                $current_item_id = null;
                foreach ($reported_items as $report): 
                    if ($current_item_id !== $report['item_id']): 
                        if ($current_item_id !== null): ?>
                            </div>
                            </div>
                        <?php endif; 
                        $current_item_id = $report['item_id']; ?>
                        <div class="report">
                            <div class="media-section">
                                <h2><?php echo htmlspecialchars($report['title']); ?> (<?php if ($report['item_type'] === 'video') { echo 'Video'; } else { echo 'Music'; } ?>)</h2>
                                <?php if ($report['item_type'] === 'video'): ?>
                                    <div class="media-player-card">
                                        <video width="320" height="180" controls controlsList="nodownload">
                                            <source src="<?php echo htmlspecialchars($report['url']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php else: ?>
                                    <div class="media-player-card">
                                        <audio controls controlsList="nodownload">
                                            <source src="<?php echo htmlspecialchars($report['url']); ?>" type="audio/mpeg">
                                            Your browser does not support the audio tag.
                                        </audio>
                                    </div>
                                <?php endif; ?>
                                <p>Uploaded by: <?php echo htmlspecialchars($report['uploader_first_name'] . ' ' . $report['uploader_last_name']); ?></p>
                            </div>
                            <div class="comments-section">
                                <h3>Comments</h3>
                                <?php 
                                $content_type = '';
                                if ($report['item_type'] === 'video') {
                                    $content_type = 'video';
                                } else {
                                    $content_type = 'audio';
                                }
                                $comments = get_comments($pdo, $report['item_id'], $content_type);
                                if (empty($comments)): ?>
                                    <p>No comments available.</p>
                                <?php else: 
                                    foreach ($comments as $comment): ?>
                                        <div class="comment">
                                            <strong><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></strong>
                                            <p class="comment-text"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                            <small class="comment-meta"><?php echo $comment['created_at']; ?></small>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo $report['item_id']; ?>">
                                                <input type="hidden" name="item_type" value="<?php echo $report['item_type']; ?>">
                                                <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete Comment</button>
                                            </form>
                                        </div>
                                        <?php $replies = get_replies($pdo, $comment['id'], $content_type); ?>
                                        <?php if (!empty($replies)): ?>
                                            <h4>Replies</h4>
                                            <?php foreach ($replies as $reply): ?>
                                                <div class="reply">
                                                    <strong><?php echo htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']); ?></strong>
                                                    <p class="comment-text">↳ <?php echo htmlspecialchars($reply['comment_text']); ?></p>
                                                    <small class="comment-meta"><?php echo $reply['created_at']; ?></small>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="comment_id" value="<?php echo $reply['id']; ?>">
                                                        <input type="hidden" name="item_id" value="<?php echo $report['item_id']; ?>">
                                                        <input type="hidden" name="item_type" value="<?php echo $report['item_type']; ?>">
                                                        <button type="submit" name="delete_comment" class="action-button action-button--delete">Delete Reply</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; 
                                endif; ?>
                            </div>
                            <div class="report-section">
                                <h3>Reports</h3>
                    <?php endif; ?>
                                <div class="report-details">
                                    <p><strong>Reported by:</strong> <?php echo htmlspecialchars($report['reporter_first_name'] . ' ' . $report['reporter_last_name']); ?></p>
                                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($report['reason']); ?></p>
                                    <p><strong>Reported at:</strong> <?php echo $report['report_created_at']; ?></p>
                                    <p><strong>Status:</strong> <?php echo htmlspecialchars($report['report_status']); ?></p>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <input type="hidden" name="new_status" value="resolved">
                                        <button type="submit" name="update_status" class="action-button action-button--submit">Resolve</button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <input type="hidden" name="new_status" value="dismissed">
                                        <button type="submit" name="update_status" class="action-button action-button--delete">Dismiss</button>
                                    </form>
                                    <button class="action-button action-button--submit" onclick="toggleMessageForm('message-uploader-<?php echo $report['report_id']; ?>')">Message Uploader</button>
                                    <button class="action-button action-button--submit" onclick="toggleMessageForm('message-reporter-<?php echo $report['report_id']; ?>')">Message Reporter</button>
                                    
                                    <div id="message-uploader-<?php echo $report['report_id']; ?>" class="message-form">
                                        <form method="post">
                                            <input type="hidden" name="receiver_id" value="<?php echo $report['uploader_id']; ?>">
                                            <textarea name="message_text" placeholder="Message to uploader" required></textarea>
                                            <button type="submit" name="send_message" class="action-button action-button--submit">Send</button>
                                        </form>
                                    </div>

                                    <div id="message-reporter-<?php echo $report['report_id']; ?>" class="message-form">
                                        <form method="post">
                                            <input type="hidden" name="receiver_id" value="<?php echo $report['reporter_id']; ?>">
                                            <textarea name="message_text" placeholder="Message to reporter" required></textarea>
                                            <button type="submit" name="send_message" class="action-button action-button--submit">Send</button>
                                        </form>
                                    </div>
                                </div>
                <?php endforeach; ?>
                            </div>
                            <div class="controls-section">
                                <h3>Controls</h3>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $current_item_id; ?>">
                                    <input type="hidden" name="item_type" value="<?php echo $report['item_type']; ?>">
                                    <button type="submit" name="delete_item" class="action-button action-button--delete">Delete <?php if ($report['item_type'] === 'video') { echo 'Video'; } else { echo 'Music'; } ?></button>
                                </form>
                            </div>
                        </div>
            <?php else: ?>
                <p>No pending reports available.</p>
            <?php endif; ?>
        </div>

        <script>
            function toggleMessageForm(formId) {
                const form = document.getElementById(formId);
                if (form.style.display === 'block') {
                    form.style.display = 'none';
                } else {
                    form.style.display = 'block';
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const messageForms = document.querySelectorAll('.message-form');
                messageForms.forEach(form => {
                    form.style.display = 'none';
                });
            });
        </script>
    </body>
    </html>