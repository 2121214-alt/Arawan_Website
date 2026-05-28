<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function notify_user($conn, $user_id, $message) {
    $stmt = mysqli_prepare($conn, 'INSERT INTO notifications (user_id, message) VALUES (?, ?)');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $message);
        mysqli_stmt_execute($stmt);
    }
}

function redirect_back($fallback = 'index.php') {
    $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
    header('Location: ' . $target);
    exit();
}
?>
