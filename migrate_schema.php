<?php
include 'db.php';

function columnExists($conn, $table, $column) {
    $stmt = mysqli_prepare($conn, "SHOW COLUMNS FROM `$table` LIKE ?");
    mysqli_stmt_bind_param($stmt, "s", $column);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result && mysqli_num_rows($result) > 0;
}

$changes = [
    "first_name VARCHAR(100) NOT NULL DEFAULT '' AFTER fullname",
    "middle_name VARCHAR(100) NULL AFTER first_name",
    "last_name VARCHAR(100) NOT NULL DEFAULT '' AFTER middle_name",
    "id_number VARCHAR(100) NOT NULL DEFAULT '' AFTER password",
    "approval_status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending' AFTER role"
];

foreach ($changes as $definition) {
    preg_match('/^([a-z_]+)/', $definition, $matches);
    if (!$matches) {
        continue;
    }
    $column = $matches[1];
    if (!columnExists($conn, 'users', $column)) {
        $sql = "ALTER TABLE users ADD COLUMN $definition";
        if (mysqli_query($conn, $sql)) {
            echo "Added column $column.\n";
        } else {
            echo "Failed to add column $column: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Column $column already exists.\n";
    }
}

echo "Migration complete.\n";
