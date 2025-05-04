<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['account_id']) || !isset($_SESSION['question_id']) || !isset($_SESSION['security_question'])) {
    $_SESSION['error'] = "Session expired. Please restart the password reset process.";
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['account_id'];
$question_id = $_SESSION['question_id'];
$security_question = $_SESSION['security_question'];

$password_updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_submit'])) {
    $provided_answer = trim($_POST['security_answer']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $match_result = check_password_match($new_password, $confirm_password);
    if ($match_result !== "") {
        $_SESSION['error'] = $match_result;
    } else {
        $error = "";
        if (strlen($new_password) < 8) {
            $error = $error . "Password must be at least 8 characters long. ";
        }
        if (!preg_match('/[0-9]/', $new_password)) {
            $error = $error . "Password must contain at least one digit. ";
        }
        if (strpos($new_password, '@') === false) {
            $error = $error . "Password must contain the @ symbol. ";
        }

        if ($error === "") {
            $verify_result = verify_security_answer($pdo, $staff_id, $question_id, $provided_answer, 'staff_security');
            if ($verify_result === true) {
                $update_result = update_password($pdo, $staff_id, $new_password, 'staff');
                if ($update_result === "Password updated successfully! Please log in.") {
                    $password_updated = true;
                    $_SESSION['message'] = $update_result;
                    session_unset();
                    session_destroy();
                    header("Location: staff_login.php");
                    exit();
                } else {
                    $_SESSION['error'] = $update_result;
                }
            } else {
                $_SESSION['error'] = $verify_result;
            }
        } else {
            $_SESSION['error'] = $error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Staff</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="login-container">
        <h2>Reset Password (Staff)</h2>
        
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

        <?php if (!$password_updated): ?>
            <p class="question">
                <strong>Security Question:</strong><br>
                <?php echo htmlspecialchars($security_question); ?>
            </p>
            
            <form action="reset_password_staff.php" method="POST">
                <label for="security_answer">Your Security Answer</label>
                <input type="text" name="security_answer" id="security_answer" placeholder="Answer" required>
                
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
                <p class="password-hint">Password must be at least 8 characters long, contain at least one digit, and include the @ symbol.</p>
                
                <button type="submit" name="reset_submit" class="action-button action-button--submit">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="login-link">
                <p><a href="staff_login.php">Go to Staff Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>