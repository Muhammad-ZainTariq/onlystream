<?php
session_start();
require 'functions.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_submit'])) {
    $email = trim($_POST['email']);
    $user = find_account_by_email($pdo, $email, 'users');
    if ($user) {
        $security = get_account_security_question($pdo, $user['id'], 'users');
        if ($security) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            $_SESSION['security_question'] = $security['question_text'];
            $_SESSION['question_id'] = $security['question_id'];
            header('Location: reset_password_user.php');
            exit();
        } else {
            $_SESSION['forgot_error'] = "No security question set for this account. Please contact support.";
        }
    } else {
        $_SESSION['forgot_error'] = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="login-container">
        <h2>Forgot Password (User)</h2>
        <?php
        if (isset($_SESSION['forgot_error'])) {
            echo '<div class="message error"><p>' . htmlspecialchars($_SESSION['forgot_error']) . '</p></div>';
            unset($_SESSION['forgot_error']);
        }
        ?>
        <form action="forgot_password_user.php" method="POST">
            <label for="email">Enter Your Email</label>
            <input type="email" name="email" id="email" placeholder="Email address" required>
            <button type="submit" name="forgot_submit" class="action-button action-button--submit">Submit</button>
        </form>
        <div class="login-link">
            <a href="login.php" class="action-button action-button--submit">Back to Login</a>
        </div>
    </div>
</body>
</html>