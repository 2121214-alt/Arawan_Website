<?php
include 'auth_check.php';
include 'db.php';
require_once 'csrf.php';
require_once 'helpers.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php#requests');
    exit();
}

if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token.');
}

$request_id = intval($_POST['request_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

$valid_statuses = ['Pending', 'Processing', 'Approved', 'Rejected'];
if ($request_id <= 0 || !in_array($status, $valid_statuses, true)) {
    header('Location: admin.php#requests');
    exit();
}

$lookup = mysqli_prepare($conn, 'SELECT user_id, request_type, reference_number, status FROM requests WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($lookup, 'i', $request_id);
mysqli_stmt_execute($lookup);
$request = mysqli_fetch_assoc(mysqli_stmt_get_result($lookup));

$stmt = mysqli_prepare($conn, 'UPDATE requests SET status = ?, remarks = ? WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'ssi', $status, $remarks, $request_id);
mysqli_stmt_execute($stmt);

if ($request && $request['status'] !== $status) {
    $message = 'Your ' . $request['request_type'] . ' request (' . $request['reference_number'] . ') is now ' . $status . '.';
    if ($remarks !== '') {
        $message .= ' Remarks: ' . $remarks;
    }
    notify_user($conn, (int)$request['user_id'], $message);
}

header('Location: admin.php#requests');
exit();
