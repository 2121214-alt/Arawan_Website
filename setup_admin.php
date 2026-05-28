<?php
include 'db.php';

$email = 'admin@arawan.local';
$password_hash = '$2y$10$SYVyacxSOtViWDj/5y9GF.4PK8EWZZdQRWhoY0RLJ2U8CkHvkoTum';

$check = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
mysqli_stmt_bind_param($check, 's', $email);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {
    echo "Admin account already exists ($email).\n";
    exit();
}

$stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, first_name, middle_name, last_name, email, password, id_number, role, approval_status) VALUES ('Barangay Admin', 'Barangay', NULL, 'Admin', ?, ?, 'ADMIN-001', 'admin', 'Approved')");
mysqli_stmt_bind_param($stmt, 'ss', $email, $password_hash);
if (mysqli_stmt_execute($stmt)) {
    echo "Admin created.\nEmail: $email\nPassword: Admin@1234\n";
} else {
    echo 'Failed: ' . mysqli_error($conn) . "\n";
}
