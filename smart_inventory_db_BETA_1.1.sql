-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 01:04 PM
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
-- Database: `smart_inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `penalties`
--

CREATE TABLE `penalties` (
  `penalty_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penalties`
--

INSERT INTO `penalties` (`penalty_id`, `user_id`, `points`, `reason`, `date_created`) VALUES
(1, 2, 5, 'Late Return (1 days)', '2025-12-13 16:36:55');

-- --------------------------------------------------------

--
-- Table structure for table `tools`
--

CREATE TABLE `tools` (
  `tool_id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `tool_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('Available','Borrowed','Maintenance','Lost') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tools`
--

INSERT INTO `tools` (`tool_id`, `barcode`, `tool_name`, `category`, `status`, `created_at`) VALUES
(1, 'HA-3048', 'Screw_driver', 'Hand Tool', 'Available', '2025-12-05 08:21:26'),
(2, 'NE-4511', 'RJ45 Tester', 'Network Equipment', 'Available', '2025-12-06 08:04:11'),
(3, 'NE-6777', 'RJ45 ', 'Network Equipment', 'Available', '2025-12-06 08:04:37'),
(4, 'ME-5237', 'Measuring Tape', 'Measuring', 'Available', '2025-12-06 08:05:01');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status` enum('Pending','Approved','Borrowed','Returned','Declined') DEFAULT 'Pending',
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `tool_id`, `borrow_date`, `return_date`, `actual_return_date`, `status`, `date_requested`) VALUES
(1, 2, 1, '2025-12-05', '2025-12-05', '2025-12-05 22:20:22', 'Returned', '2025-12-05 13:51:31'),
(2, 2, 1, '2025-12-05', '2025-12-05', NULL, 'Declined', '2025-12-05 13:51:48'),
(3, 2, 1, '2025-12-06', '2025-12-06', '2025-12-06 16:00:02', 'Returned', '2025-12-06 07:57:12'),
(4, 2, 1, '2025-12-06', '2025-12-06', '2025-12-06 16:08:02', 'Returned', '2025-12-06 08:05:33'),
(5, 2, 2, '2025-12-06', '2025-12-06', '2025-12-06 16:08:07', 'Returned', '2025-12-06 08:05:37'),
(6, 2, 3, '2025-12-06', '2025-12-06', '2025-12-06 16:09:00', 'Returned', '2025-12-06 08:05:40'),
(7, 2, 3, '2025-12-06', '2025-12-06', '2025-12-06 16:08:12', 'Returned', '2025-12-06 08:05:44'),
(8, 2, 4, '2025-12-06', '2025-12-06', '2025-12-06 16:08:19', 'Returned', '2025-12-06 08:05:48'),
(9, 2, 1, '2025-12-07', '2025-12-07', '2025-12-07 15:19:22', 'Returned', '2025-12-07 07:18:01'),
(10, 2, 1, '2025-12-07', '2025-12-07', '2025-12-07 16:04:46', 'Returned', '2025-12-07 07:25:48'),
(11, 2, 2, '2025-12-07', '2025-12-07', '2025-12-07 16:04:41', 'Returned', '2025-12-07 07:25:55'),
(12, 2, 1, '2025-12-08', '2025-12-08', '2025-12-08 20:40:20', 'Returned', '2025-12-08 12:39:05'),
(13, 2, 2, '2025-12-08', '2025-12-08', '2025-12-08 20:40:25', 'Returned', '2025-12-08 12:39:05'),
(14, 2, 3, '2025-12-08', '2025-12-08', '2025-12-08 20:40:40', 'Returned', '2025-12-08 12:39:05'),
(15, 2, 4, '2025-12-08', '2025-12-08', '2025-12-08 20:40:44', 'Returned', '2025-12-08 12:39:05'),
(16, 2, 2, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:17:51'),
(17, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:18:28'),
(18, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:22:30'),
(19, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:24:19'),
(20, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:27:11'),
(21, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:30:10'),
(22, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:39:03'),
(23, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:45:02'),
(24, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:52:09'),
(25, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 14:54:47'),
(26, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 15:01:17'),
(27, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', '2025-12-08 15:03:22'),
(28, 2, 1, '2025-12-08', '2025-12-09', '2025-12-09 00:16:43', 'Returned', '2025-12-08 16:00:25'),
(29, 2, 2, '2025-12-08', '2025-12-09', NULL, 'Declined', '2025-12-08 16:06:33'),
(30, 2, 1, '2025-12-08', '2025-12-09', '2025-12-09 00:18:57', 'Returned', '2025-12-08 16:18:09'),
(31, 2, 1, '2025-12-09', '2025-12-09', '2025-12-09 00:31:58', 'Returned', '2025-12-08 16:31:35'),
(32, 2, 1, '2025-12-10', '2025-12-10', '2025-12-10 17:19:42', 'Returned', '2025-12-10 09:18:21'),
(33, 2, 1, '2025-12-13', '2025-12-13', '2025-12-14 00:36:55', 'Returned', '2025-12-13 10:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `account_status` enum('active','restricted') DEFAULT 'active',
  `penalty_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ban_end_date` datetime DEFAULT NULL,
  `ban_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `id_number`, `full_name`, `email`, `password`, `role`, `account_status`, `penalty_points`, `created_at`, `ban_end_date`, `ban_reason`) VALUES
(1, 'ADMIN-01', 'Laboratory Head', '', 'admin123', 'admin', 'active', 0, '2025-12-05 08:03:07', NULL, NULL),
(2, '2025-0001', 'Julian Eric Parco', 'juricparcome@gmail.com', 'student123', 'student', 'active', 5, '2025-12-05 08:03:07', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`penalty_id`);

--
-- Indexes for table `tools`
--
ALTER TABLE `tools`
  ADD PRIMARY KEY (`tool_id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tool_id` (`tool_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `penalties`
--
ALTER TABLE `penalties`
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
