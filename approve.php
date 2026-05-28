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
    header('Location: admin.php#requests');
    exit();
}

$id = intval($_POST['id'] ?? 0);
if ($id > 0) {
    $lookup = mysqli_prepare($conn, 'SELECT user_id, request_type, reference_number FROM requests WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($lookup, 'i', $id);
    mysqli_stmt_execute($lookup);
    $request = mysqli_fetch_assoc(mysqli_stmt_get_result($lookup));

    $stmt = mysqli_prepare($conn, "UPDATE requests SET status = 'Approved', remarks = 'Approved by barangay staff. Please wait for pickup instructions.' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    if ($request) {
        notify_user($conn, (int)$request['user_id'], 'Your ' . $request['request_type'] . ' request (' . $request['reference_number'] . ') has been approved.');
    }
}

header('Location: admin.php#requests');
exit();
?>
