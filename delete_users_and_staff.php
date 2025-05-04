<?php
session_start();
require 'functions.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}
if (!is_staff_admin($pdo, $_SESSION['staff_id'])) {
    $_SESSION['error'] = "Only admins can access this page.";
    header("Location: adminwork.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        $result = delete_user($pdo, $user_id);
        if ($result === "User deleted successfully.") {
            $_SESSION['message'] = $result;
        } else {
            $_SESSION['error'] = $result;
        }
    }

    if (isset($_POST['delete_staff'])) {
        $staff_id = (int)$_POST['staff_id'];
        $result = delete_staff($pdo, $staff_id, $_SESSION['staff_id']);
        if ($result === "Staff member deleted successfully.") {
            $_SESSION['message'] = $result;
        } else {
            $_SESSION['error'] = $result;
        }
    }

    header("Location: delete_users_and_staff.php");
    exit();
}

$userData = get_users_with_counts($pdo);
$staffData = get_staff_with_counts($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Users and Staff</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .actions-cell {
            display: block;
            text-align: center;
        }
        .warning-message {
            display: block;
            color: #ff4444;
        }
    </style>
</head>
<body>
    <?php include 'admin_navigation.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1>Delete Users and Staff Members</h1>
        </div>

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

        <h2>Normal Users</h2>
        <?php if (!empty($userData)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Videos</th>
                        <th>Audio</th>
                        <th>Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userData as $data): ?>
                        <?php $user = $data['user']; ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $data['video_count'] ?></td>
                            <td><?= $data['audio_count'] ?></td>
                            <td><?= $data['comment_count'] ?></td>
                            <td class="actions-cell">
                                <span class="warning-message">
                                    This user has <?= $data['video_count'] ?> video(s), <?= $data['audio_count'] ?> audio file(s), and <?= $data['comment_count'] ?> comment(s). These will be deleted.
                                </span>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="action-button action-button--delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No normal users found.</p>
        <?php endif; ?>

        <h2>Staff Members</h2>
        <?php if (!empty($staffData)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Messages</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffData as $data): ?>
                        <?php $staff = $data['staff']; ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['id']) ?></td>
                            <td><?= htmlspecialchars($staff['name']) ?></td>
                            <td><?= $data['message_count'] ?></td>
                            <td class="actions-cell">
                                <span class="warning-message">
                                    This staff member has <?= $data['message_count'] ?> message(s). These will be deleted.
                                </span>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                    <button type="submit" name="delete_staff" class="action-button action-button--delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No staff members found.</p>
        <?php endif; ?>
    </div>
</body>
</html>