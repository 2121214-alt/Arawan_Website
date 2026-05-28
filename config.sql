CREATE DATABASE IF NOT EXISTS arawan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arawan_db;

CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL DEFAULT '',
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL DEFAULT '',
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_number VARCHAR(100) NOT NULL DEFAULT '',
    role ENUM('resident','admin') DEFAULT 'resident',
    approval_status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS requests(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    purpose VARCHAR(255),
    reference_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('Pending','Processing','Approved','Rejected') DEFAULT 'Pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS announcements(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS schedules(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO announcements(title, content) VALUES
('Barangay Document Requests Now Available', 'Residents may now submit document requests online and track their status using their reference number.')
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO users (fullname, first_name, middle_name, last_name, email, password, id_number, role, approval_status)
SELECT 'Barangay Admin', 'Barangay', NULL, 'Admin', 'admin@arawan.local', '$2y$10$SYVyacxSOtViWDj/5y9GF.4PK8EWZZdQRWhoY0RLJ2U8CkHvkoTum', 'ADMIN-001', 'admin', 'Approved'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@arawan.local'
);