
<?php
session_start();
require_once 'functions.php';     

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $email = trim($_POST['signin_email']);
    $password = $_POST['signin_password'];

    $user = signin_user($pdo, $email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_logged_in'] = '1';
        $full_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'User';
        $_SESSION['message'] = "Welcome $full_name";
        header('Location: retreive_videos.php');
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password.";
    }  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="login-container">
        <h2>Login</h2>
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
        <form action="login.php" method="POST">
            <label for="signin_email">Email</label>
            <input type="text" name="signin_email" placeholder="Email" required>
            
            <label for="signin_password">Password</label>
            <input type="password" name="signin_password" placeholder="Password" required>
            
            <button type="submit" name="signin" class="action-button action-button--submit">Sign In</button>
        </form>
        <div class="signup-link">
            <p><a href="signup.php">Not a user? Sign Up</a></p>
            <p><a href="forgot_password_user.php">Forgot Password?</a></p>
        </div>
    </div>
</body>
</html>