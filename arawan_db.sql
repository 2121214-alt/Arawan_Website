-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 09:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `arawan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` enum('Notice','Advisory','Program','Health') NOT NULL DEFAULT 'Notice'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`, `category`) VALUES
(1, 'Barangay Document Requests Now Available', 'Residents may now submit document requests online and track their status using their reference number.', '2026-05-19 06:13:02', 'Notice'),
(7, 'Voting Registration is now OPEN!', 'Residents may now submit document requests online and track their status using their reference number.', '2026-05-19 07:44:40', 'Notice'),
(8, 'Tuli Program is NOW OPEN', 'Residents may proceed to the Barangay Health Center', '2026-05-19 07:45:18', 'Program');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Your resident account has been approved. You may now use the portal.', 1, '2026-05-19 06:25:33'),
(2, 2, 'Your Certificate of Residency request was submitted. Reference: REF-20260519-290EC1.', 1, '2026-05-19 06:27:08'),
(3, 2, 'Your Certificate of Residency request was submitted. Reference: REF-20260519-D2B75D.', 0, '2026-05-19 06:38:46'),
(4, 2, 'Your Barangay Clearance request was submitted. Reference: REF-20260519-9B0169.', 0, '2026-05-19 06:48:29'),
(5, 2, 'Your Certificate of Residency request was submitted. Reference: REF-20260519-25FFD4.', 0, '2026-05-19 06:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_type` varchar(100) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `reference_number` varchar(50) NOT NULL,
  `status` enum('Pending','Processing','Approved','Rejected') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `request_type`, `purpose`, `reference_number`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 2, 'Certificate of Residency', 'tiet', 'REF-20260519-290EC1', 'Pending', NULL, '2026-05-19 06:27:08', '2026-05-19 06:27:08'),
(2, 2, 'Certificate of Residency', 'fsf', 'REF-20260519-D2B75D', 'Pending', NULL, '2026-05-19 06:38:46', '2026-05-19 06:38:46'),
(3, 2, 'Barangay Clearance', 'cdas', 'REF-20260519-9B0169', 'Pending', NULL, '2026-05-19 06:48:29', '2026-05-19 06:48:29'),
(4, 2, 'Certificate of Residency', 'erg', 'REF-20260519-25FFD4', 'Pending', NULL, '2026-05-19 06:55:14', '2026-05-19 06:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('resident','admin') DEFAULT 'resident',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `id_number` varchar(100) NOT NULL DEFAULT '',
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `created_at`, `first_name`, `middle_name`, `last_name`, `id_number`, `approval_status`) VALUES
(1, 'Barangay Admin', 'admin@arawan.local', '$2y$10$SYVyacxSOtViWDj/5y9GF.4PK8EWZZdQRWhoY0RLJ2U8CkHvkoTum', 'admin', '2026-05-19 06:21:28', 'Barangay', NULL, 'Admin', 'ADMIN-001', 'Approved'),
(2, 'Anthony Katigbak', 'katigbak@gmail.com', '$2y$10$jGh0pCfpkxkit3iCuNd2y.TnI.siHvPy2xCTqXb7/NokeHnQGog7W', 'resident', '2026-05-19 06:25:00', 'Anthony', '', 'Katigbak', '123456', 'Approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
