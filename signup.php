<?php
session_start();
require_once 'functions.php';

$security_questions = get_security_questions($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $security_question = $_POST['security_question'];
    $security_answer = trim($_POST['security_answer']);

    $password_errors = validate_password($password, $confirm_password);
    if (empty($password_errors)) {
        $result = signup_user($pdo, $first_name, $last_name, $email, $phone, $password, $security_question, $security_answer);
        if (strpos($result, "successful") !== false) {
            $_SESSION['message'] = $result;
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['error'] = $result;
        }
    } else {
        $_SESSION['error'] = $password_errors; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php
                if (is_array($_SESSION['error'])) {
                    
                    echo '<ul>';
                    foreach ($_SESSION['error'] as $error) {
                        echo '<li>' . htmlspecialchars($error) . '</li>';
                    }
                    echo '</ul>';
                } else {
                    
                    echo htmlspecialchars($_SESSION['error']);
                }
                ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="signup.php" method="POST">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" placeholder="First Name" required>
            
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" placeholder="Last Name" required>
            
            <label for="email">Email</label>
            <input type="email" name="email" placeholder="Email" required>
            
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Password" required>
            
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <p class="password-hint">Password must be at least 8 characters long, contain at least one digit, and include the @ symbol.</p>
            
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone" placeholder="Phone Number" required>
            
            <label for="security_question">Security Question</label>
            <select name="security_question" required>
                <option value="" disabled selected>Select a question</option>
                <?php foreach ($security_questions as $question): ?>
                    <option value="<?php echo $question['question_id']; ?>">
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="security_answer">Your Answer</label>
            <input type="text" name="security_answer" placeholder="Your Answer" required>
            
            <button type="submit" name="signup" class="action-button action-button--submit">Sign Up</button>
        </form>
        <div class="login-link">
            <p><a href="login.php">Already a user? Login</a></p>
        </div>
    </div>
</body>
</html>