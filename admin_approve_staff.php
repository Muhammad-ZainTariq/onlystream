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

$pendingStaff = get_pending_staff_requests($pdo);
if (empty($pendingStaff)) {
    $_SESSION['error'] = "No pending staff requests found.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $result = approve_staff_request($pdo, $request_id);
        $_SESSION['message'] = $result;
    }
    else if ($action === 'approve') {
        $result = approve_staff_request($pdo, $request_id);
        $_SESSION['message'] = $result;
    }
    else if ($action === 'reject') {
        $result = reject_staff_request($pdo, $request_id);
        $_SESSION['error'] = $result;
    }
    else {
        $_SESSION['error'] = "Invalid action.";
    }
    header("Location: admin_approve_staff.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Approval - OnlyStream</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'admin_navigation.php'; ?>
    
    <div class="container">
        <h1>Staff Approval</h1>

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

        <?php if (!empty($pendingStaff)): ?>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingStaff as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="action-button action-button--submit">Approve</button>
                                </form>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="action-button action-button--delete">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-pending">No pending staff requests.</div>
        <?php endif; ?>
    </div>
</body>
</html>