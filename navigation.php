<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
       
        <div class="website-name">
            OnlyStream
        </div>

       
        <div class="nav-links">
            <a href="retreive_videos.php">Videos</a>
            <a href="display_audio.php">Music</a>
            <a href="creatorhub.php">CreatorHub</a>
            <a href="contact_us.php">Help</a>
            <a href="user_messages.php">Messages</a>
            <a href="settings.php">Settings</a>
            <a href="staff_login.php">Staff</a>
           
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="auth-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="auth-btn">Login</a>
            <?php endif; ?>
        </div>

       
        <form class="search-bar" method="GET" action="search_results.php">
            <input type="text" name="search" placeholder="Search videos or audio..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e5e5e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>

     
        <div class="profile-circle">
            <?php
            require_once 'functions.php';
            if (isset($_SESSION['user_id'])) {
                $user = get_user_profile($pdo, $_SESSION['user_id']);
                if ($user && !empty($user['profile_picture'])) {
                    $profilePicBase64 = base64_encode($user['profile_picture']);
                    $_SESSION['profile_pic_base64'] = $profilePicBase64;
                    echo "<img src='data:image/jpeg;base64,$profilePicBase64' alt='Profile Picture'>";
                } else {
                    echo "<img src='default.png' alt='Default Profile Picture'>";
                }
            } else {
                echo "<img src='default.png' alt='Default Profile Picture'>";
            }
            ?>
        </div>

        <button class="theme-toggle" id="theme-toggle-btn" onclick="toggleTheme()">Light Mode</button>
    </nav>

    <script src="/onlystream/theme.js"></script>
</body>
</html>