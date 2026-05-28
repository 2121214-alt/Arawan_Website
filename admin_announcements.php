<?php
include 'auth_check.php';
include 'db.php';
require_once 'csrf.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit();
}

if (!validate_csrf($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token.');
}

$action = $_POST['action'] ?? '';
$allowed_categories = ['Notice', 'Advisory', 'Program', 'Health'];

function clean_category($value, $allowed_categories) {
    $value = trim($value ?? '');
    return in_array($value, $allowed_categories, true) ? $value : 'Notice';
}

if ($action === 'create') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = clean_category($_POST['category'] ?? 'Notice', $allowed_categories);

    if ($title !== '' && $content !== '') {
        $stmt = mysqli_prepare($conn, 'INSERT INTO announcements (title, content, category) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $title, $content, $category);
        mysqli_stmt_execute($stmt);
    }

} elseif ($action === 'update') {
    $id = intval($_POST['announcement_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = clean_category($_POST['category'] ?? 'Notice', $allowed_categories);

    if ($id > 0 && $title !== '' && $content !== '') {
        $stmt = mysqli_prepare($conn, 'UPDATE announcements SET title = ?, content = ?, category = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'sssi', $title, $content, $category, $id);
        mysqli_stmt_execute($stmt);
    }

} elseif ($action === 'delete') {
    $id = intval($_POST['announcement_id'] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, 'DELETE FROM announcements WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
    }
}

header('Location: admin.php#announcements');
exit();