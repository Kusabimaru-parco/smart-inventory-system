-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 09:22 AM
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
(11, 2, 2, '2025-12-07', '2025-12-07', '2025-12-07 16:04:41', 'Returned', '2025-12-07 07:25:55');

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
  `penalty_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `id_number`, `full_name`, `email`, `password`, `role`, `penalty_points`, `created_at`) VALUES
(1, 'ADMIN-01', 'Laboratory Head', '', 'admin123', 'admin', 0, '2025-12-05 08:03:07'),
(2, '2025-0001', 'Julian Eric Parco', 'juricparcome@gmail.com', 'student123', 'student', 0, '2025-12-05 08:03:07');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
