<?php
session_start();
require 'db_connection.php';
require_once 'functions.php';

$user_id = require_login();

$user_membership = get_user_membership($pdo, $user_id);
if ($user_membership === null) {
    $_SESSION['error'] = "User not found. Please log in again.";
    header("Location: login.php");
    exit();
}

if (isset($user_membership['membership_tier'])) {
    $tier = $user_membership['membership_tier'];
} else {
    $tier = 0;
}
if (isset($user_membership['membership_expires_at'])) {
    $expires_at = $user_membership['membership_expires_at'];
} else {
    $expires_at = null;
}

check_membership_expiration($pdo, $user_id, $tier, $expires_at);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tier = (int)$_POST['tier'];
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $card_number = preg_replace('/[^0-9]/', '', $_POST['card_number']);
    $cvv = preg_replace('/[^0-9]/', '', $_POST['cvv']);
    $phone_number = trim($_POST['phone_number']);
    $postcode = trim($_POST['postcode']);

    if ($tier !== 1 && $tier !== 2) {
        $_SESSION['error'] = "Please select a valid membership tier.";
    } elseif (strlen($card_number) !== 16) {
        $_SESSION['error'] = "Card number must be 16 digits.";
    } elseif (strlen($cvv) !== 3) {
        $_SESSION['error'] = "CVV must be 3 digits.";
    } elseif (empty($name) || empty($address) || empty($phone_number) || empty($postcode)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        $hashed_card_number = password_hash($card_number, PASSWORD_DEFAULT);
        $expires_at = (new DateTime())->modify('+30 days')->format('Y-m-d H:i:s');

        if ($tier == 1) {
            $stmt = $pdo->prepare("UPDATE users SET membership_tier = 1, video_uploads_left = 2, music_uploads_left = 2, membership_expires_at = ? WHERE id = ?");
            $stmt->execute([$expires_at, $user_id]);
            $_SESSION['message'] = "Tier 1 (Free) activated! Expires on $expires_at.";
        } elseif ($tier == 2) {
            $stmt = $pdo->prepare("UPDATE users SET membership_tier = 2, video_uploads_left = 7, music_uploads_left = 7, membership_expires_at = ? WHERE id = ?");
            $stmt->execute([$expires_at, $user_id]);
            $_SESSION['message'] = "Tier 2 (£5/month) activated! Expires on $expires_at.";
        }

        $stmt = $pdo->prepare("INSERT INTO user_payments (user_id, tier, name_on_card, address, card_number, cvv, phone_number, postcode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $tier, $name, $address, $hashed_card_number, $cvv, $phone_number, $postcode]);
    }
    header("Location: membership.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Membership - OnlyStream</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="container membership-container">
        <h1>Become a Creator</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="tiers">
            <div class="tier">
                <h2>Tier 1 - Free (£0/month)</h2>
                <p>Upload 2 videos and 2 audios. Card details required for verification.</p>
            </div>
            <div class="tier">
                <h2>Tier 2 - £5/month</h2>
                <p>Upload 7 videos and 7 audios.</p>
            </div>
        </div>
        <form method="POST">
            <label for="tier">Membership Tier</label>
            <select name="tier" id="tier" required>
                <option value="">Select a Tier</option>
                <option value="1">Tier 1 - Free (£0/month)</option>
                <option value="2">Tier 2 - £5/month</option>
            </select>
            <label for="name">Name on Card</label>
            <input type="text" name="name" id="name" placeholder="John Doe" required>
            <label for="address">Billing Address</label>
            <input type="text" name="address" id="address" placeholder="123 Main St" required>
            <label for="card_number">Card Number (16 digits)</label>
            <input type="text" name="card_number" id="card_number" placeholder="1234567890123456" maxlength="16" pattern="[0-9]{16}" required>
            <label for="cvv">CVV (3 digits)</label>
            <input type="text" name="cvv" id="cvv" placeholder="123" maxlength="3" pattern="[0-9]{3}" required>
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" placeholder="+447774068884" required>
            <label for="postcode">Postcode</label>
            <input type="text" name="postcode" id="postcode" placeholder="CV11 8TY" required>
            <button type="submit" class="action-button action-button--submit">Purchase Membership</button>
        </form>
    </div>
</body>
</html>