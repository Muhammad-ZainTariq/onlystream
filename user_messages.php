    <?php
    session_start();
    require_once 'functions.php';


    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_as_read'])) {
        $_SESSION['message'] = mark_message_as_read($pdo, $_POST['message_id'], $user_id);
        header("Location: user_messages.php");
        exit();
    }


    $queries = get_user_queries($pdo, $user_id);
    $messages = get_report_messages($pdo, $user_id);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Messages</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php include 'navigation.php'; ?>
        <div class="container">
            <h1>My Messages</h1>

        
            <?php if (isset($_SESSION['message'])) { ?>
                <div class="message success">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php } ?>
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php } ?>

            
            <div class="section queries-table">
                <h2>Query Responses</h2>
                <?php if (empty($queries)) { ?>
                    <p class="no-data">You have not submitted any queries yet.</p>
                <?php } else { ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Query</th>
                                <th>Response</th>
                                <th>Responded By</th>
                                <th>Response Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queries as $query) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($query['query']); ?></td>
                                    <td><?php echo $query['response'] ? htmlspecialchars($query['response']) : 'Pending response'; ?></td>
                                    <td><?php echo $query['responder_name'] ? htmlspecialchars($query['responder_name']) : 'N/A'; ?></td>
                                    <td><?php echo $query['response_date'] ? htmlspecialchars($query['response_date']) : 'N/A'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>

            
            <div class="section messages-table">
                <h2>Report Updates</h2>
                <?php if (empty($messages)) { ?>
                    <p class="no-data">No report updates available.</p>
                <?php } else { ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Message</th>
                                <th>Sent By</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['message_text']); ?></td>
                                    <td><?php echo $message['sender_name'] ? htmlspecialchars($message['sender_name']) : 'System'; ?></td>
                                    <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                                    <td>
                                        <?php if ($message['is_read']) { ?>
                                            <span class="read-status">You've read this message</span>
                                        <?php } else { ?>
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <button type="submit" name="mark_as_read" class="action-button action-button--submit">Mark as Read</button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
    </body>
    </html>