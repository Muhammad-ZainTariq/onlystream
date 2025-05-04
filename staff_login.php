leaving this login for you to login and test the website admin login: admin@gmail.com password = Admin123@
    
<?php           
session_start();
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $result = signin_staff($pdo, $email, $password);
    if ($result === "Logged in successfully!") {
        $_SESSION['message'] = $result;
        header("Location: adminwork.php");
        exit();
    } else {
        $_SESSION['error'] = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Login - OnlyStream</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="login-container">
        <h1>Staff Login</h1>
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
        <form action="staff_login.php" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="action-button action-button--submit">Login</button>
        </form>
        <div class="signup-link">
            <p>Wanna be a staff member? <a href="staff_signup.php">Sign up here</a></p>
            <p><a href="forgot_password_staff.php">Forgot Password?</a></p>
        </div>
    </div>
</body>
</html>