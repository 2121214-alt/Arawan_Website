<?php
include 'auth_check.php';
include 'db.php';
require_once 'csrf.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: request.php');
    exit();
}

if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: request.php?error=' . urlencode('Invalid or expired security token. Please try again.'));
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$request_type = trim($_POST['request_type'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');
$allowed_types = ['Barangay Clearance', 'Certificate of Residency', 'Certificate of Indigency', 'Business Permit'];

if (!in_array($request_type, $allowed_types, true) || $purpose === '') {
    header('Location: request.php?error=' . urlencode('Please select a valid document type and enter your purpose.'));
    exit();
}

$purpose = substr($purpose, 0, 255);
$reference_number = 'REF-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

$stmt = mysqli_prepare($conn, 'INSERT INTO requests(user_id, request_type, purpose, reference_number) VALUES(?, ?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'isss', $user_id, $request_type, $purpose, $reference_number);

if (mysqli_stmt_execute($stmt)) {
    notify_user($conn, $user_id, "Your $request_type request was submitted. Reference: $reference_number.");
    header('Location: dashboard.php?submitted=1');
    exit();
}

header('Location: request.php?error=' . urlencode('Failed to submit request. Please try again.'));
exit();
?>
