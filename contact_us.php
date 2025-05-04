<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $query = $_POST['query'];

    $message = submit_contact_query($pdo, $user_id, $name, $email, $contact_number, $query);
    $success_message = $message;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - OnlyStream</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="contact-container">
        <h1>Contact Us</h1>

        <?php if (isset($success_message)): ?>
            <p class="message <?php echo strpos($success_message, 'submitted') !== false ? 'success' : 'error'; ?>"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="text" name="contact_number" placeholder="Your Contact Number (Optional)">
            <textarea name="query" placeholder="Your Query/Problem" rows="5" required></textarea>
            <button type="submit" class="action-button action-button--submit">Submit</button>
        </form>
    </div>
</body>
</html>