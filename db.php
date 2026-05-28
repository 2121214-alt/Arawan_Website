<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'arawan_db';

$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
mysqli_select_db($conn, $dbname);
mysqli_set_charset($conn, 'utf8mb4');

function columnExists($conn, $table, $column) {
    global $dbname;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ?
         AND TABLE_NAME = ?
         AND COLUMN_NAME = ?"
    );

    mysqli_stmt_bind_param($stmt, 'sss', $dbname, $table, $column);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return (int)$row['total'] > 0;
}

function addColumnIfMissing($conn, $table, $column, $definition) {
    if (!columnExists($conn, $table, $column)) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        mysqli_query($conn, $sql) or die("Migration failed: " . mysqli_error($conn));
    }
}

/* USERS TABLE */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('resident','admin') DEFAULT 'resident',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

addColumnIfMissing($conn, 'users', 'first_name', "VARCHAR(100) NOT NULL DEFAULT ''");
addColumnIfMissing($conn, 'users', 'middle_name', "VARCHAR(100) NULL");
addColumnIfMissing($conn, 'users', 'last_name', "VARCHAR(100) NOT NULL DEFAULT ''");
addColumnIfMissing($conn, 'users', 'id_number', "VARCHAR(100) NOT NULL DEFAULT ''");
addColumnIfMissing($conn, 'users', 'approval_status', "ENUM('Pending','Approved','Rejected') DEFAULT 'Pending'");
addColumnIfMissing($conn, 'announcements', 'category', "ENUM('Notice','Advisory','Program','Health') NOT NULL DEFAULT 'Notice'");

/* REQUESTS TABLE */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS requests(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    purpose VARCHAR(255),
    reference_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('Pending','Processing','Approved','Rejected') DEFAULT 'Pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

/* ANNOUNCEMENTS TABLE */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS announcements(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

/* SCHEDULES TABLE */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS schedules(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    description TEXT
)");

/* NOTIFICATIONS TABLE */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>