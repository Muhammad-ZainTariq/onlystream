<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query_id'], $_POST['response'])) {
    $query_id = filter_input(INPUT_POST, 'query_id', FILTER_VALIDATE_INT);
    $response = $_POST['response'];
    $_SESSION['message'] = respond_to_query($pdo, $query_id, $_SESSION['staff_id'], $response);
    header("Location: view_queries.php");
    exit();
}

$queries = get_all_queries($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View and Respond to Queries</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'admin_navigation.php'; ?>
    
    <div class="container">
        <h1>View and Respond to Queries</h1>

        <?php if (isset($_SESSION['message'])) { ?>
            <div class="message success">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php } ?>

        <div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact Number</th>
                        <th>Query</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($queries)) { ?>
                        <tr>
                            <td colspan="5" class="message">No queries available.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($queries as $query) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($query['name']); ?></td>
                                <td><?php echo htmlspecialchars($query['email']); ?></td>
                                <td><?php echo htmlspecialchars($query['contact_number'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($query['query']); ?></td>
                                <td>
                                    <?php if ($query['response']) { ?>
                                        <div class="response-details">
                                            <p><strong>Responded by:</strong> <?php echo htmlspecialchars($query['responder_name'] ?: 'Unknown'); ?></p>
                                            <p><?php echo htmlspecialchars($query['response']); ?></p>
                                            <p><small><strong>Date:</strong> <?php echo htmlspecialchars($query['response_date']); ?></small></p>
                                        </div>
                                    <?php } else { ?>
                                        <form method="post" class="response-form">
                                            <textarea name="response" placeholder="Write your response here..." required></textarea>
                                            <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                            <button type="submit" class="action-button action-button--submit">Submit Response</button>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>