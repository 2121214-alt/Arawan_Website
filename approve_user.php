<?php
include 'auth_check.php';
include 'db.php';
require_once 'csrf.php';
require_once 'helpers.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: admin.php#registrations');
    exit();
}

$id = intval($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET approval_status = 'Approved' WHERE id = ? AND role = 'resident'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    notify_user($conn, $id, 'Your resident account has been approved. You may now use the portal.');
}

header('Location: admin.php#registrations');
exit();
?>
