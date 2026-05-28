<?php
include 'auth_check.php';
include 'db.php';
require_once 'csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$stmt = mysqli_prepare($conn, 'UPDATE notifications SET is_read = TRUE WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);

echo json_encode(['ok' => true]);
?>
