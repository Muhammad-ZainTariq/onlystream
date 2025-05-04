<?php
session_start();
require_once 'functions.php';

$security_questions = get_security_questions($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $security_question = $_POST['security_question'];
    $security_answer = trim($_POST['security_answer']);

    $password_errors = validate_password($password, $confirm_password);
    if (empty($password_errors)) {
        $_SESSION['staff_signup_success'] = signup_staff($pdo, $first_name, $last_name, $email, $password, $security_question, $security_answer);
        header("Location: staff_signup.php");
        exit();
    } else {
        $_SESSION['staff_signup_error'] = implode(" ", $password_errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Signup</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="signup-container">
        <h1>Staff Signup</h1>
        <?php if (isset($_SESSION['staff_signup_success'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_SESSION['staff_signup_success']); ?>
            </div>
            <?php unset($_SESSION['staff_signup_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['staff_signup_error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_SESSION['staff_signup_error']); ?>
            </div>
            <?php unset($_SESSION['staff_signup_error']); ?>
        <?php endif; ?>
        <form action="staff_signup.php" method="post">
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

            <label for="security_question">Security Question</label>
            <select name="security_question" id="security_question" required>
                <option value="" disabled selected>Select a Security Question</option>
                <?php foreach ($security_questions as $question): ?>
                    <option value="<?php echo $question['question_id']; ?>">
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="security_answer">Your Answer</label>
            <input type="text" name="security_answer" placeholder="Your Security Answer" required>

            <button type="submit" name="signup" class="action-button action-button--submit">Submit Request</button>
        </form>
        <div class="login-link">
            <p>Already a staff member? <a href="staff_login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>