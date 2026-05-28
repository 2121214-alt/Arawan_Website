<?php
include 'auth_check.php';
include 'db.php';
require_once 'csrf.php';

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
    $stmt = mysqli_prepare($conn, "UPDATE users SET approval_status = 'Rejected' WHERE id = ? AND role = 'resident'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

header('Location: admin.php#registrations');
exit();
?>
