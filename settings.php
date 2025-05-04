<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = get_user_profile($pdo, $user_id);
if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif ((!empty($new_password) || !empty($confirm_password)) && $new_password !== $confirm_password) {
        $_SESSION['error'] = "New password and confirm password do not match.";
    } else {
        $pictureBlob = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $pictureBlob = file_get_contents($_FILES['profile_picture']['tmp_name']);
        }

        if (empty($_SESSION['error'])) {
            $result = update_user_profile($pdo, $user_id, $first_name, $last_name, $email, $new_password, $pictureBlob);
            if ($result === "Profile updated successfully.") {
                $_SESSION['message'] = $result;
                $user = get_user_profile($pdo, $user_id);
            } else {
                $_SESSION['error'] = $result;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="settings-container">
        <h2>Profile Settings</h2>
        
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

        <?php if (!empty($user['profile_picture'])): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_picture']); ?>" 
                 alt="Profile Picture" class="profile-picture">
        <?php else: ?>
            <img src="default.png" alt="Default Profile Picture" class="profile-picture">
        <?php endif; ?>

        <form action="settings.php" method="post" enctype="multipart/form-data">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" placeholder="New Password (optional)">

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password">

            <label for="profile_picture">Profile Picture</label>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*">

            <button type="submit" class="action-button action-button--submit">Update Profile</button>
        </form>
    </div>
</body>
</html>