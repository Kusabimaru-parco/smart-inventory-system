-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 12:42 PM
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
(1, 2, 5, 'Late Return (1 days)', '2025-12-13 16:36:55'),
(2, 6, 5, 'Late Return (1 days) - Exponential Penalty', '2026-01-09 17:47:14'),
(3, 6, 5, 'Late Return (1 days) - Exponential Penalty', '2026-01-09 17:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `tools`
--

CREATE TABLE `tools` (
  `tool_id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `tool_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tools`
--

INSERT INTO `tools` (`tool_id`, `barcode`, `tool_name`, `category`, `status`, `created_at`) VALUES
(10, 'ME-8607', 'WATT METER - SEU,0.2A/1A,120-240V', 'Measuring', 'Available', '2026-01-27 08:25:54'),
(11, 'TO-3726', 'SCREW DRIVER', 'Tools', 'Available', '2026-01-27 08:26:19'),
(12, 'TO-3200', 'SCREW DRIVER', 'Tools', 'Available', '2026-01-27 08:26:19'),
(13, 'TO-5070', 'SCREW DRIVER', 'Tools', 'Available', '2026-01-27 08:26:19'),
(14, 'TO-1293', 'SCREW DRIVER', 'Tools', 'Available', '2026-01-27 08:26:19'),
(15, 'TO-3355', 'DIE HEAD COMPLETE', 'Tools', 'Available', '2026-01-27 08:26:31'),
(16, 'TO-4847', 'DIE HEAD COMPLETE', 'Tools', 'Available', '2026-01-27 08:26:31'),
(17, 'TO-8177', 'FLARING TOOL', 'Tools', 'Available', '2026-01-27 08:26:43'),
(18, 'TO-8732', 'FLARING TOOL', 'Tools', 'Available', '2026-01-27 08:26:43'),
(19, 'TO-7080', 'FLARING TOOL', 'Tools', 'Available', '2026-01-27 08:26:43'),
(20, 'TO-4999', 'DESOLDERING', 'Tools', 'Available', '2026-01-27 08:26:57'),
(21, 'TO-2756', 'DESOLDERING', 'Tools', 'Available', '2026-01-27 08:26:57'),
(22, 'TO-5140', 'DESOLDERING', 'Tools', 'Available', '2026-01-27 08:26:57'),
(23, 'TO-5440', 'WIRE STRIPPER', 'Tools', 'Available', '2026-01-27 08:27:14'),
(24, 'TO-7911', 'WIRE STRIPPER', 'Tools', 'Available', '2026-01-27 08:27:14'),
(25, 'TO-6037', 'WIRE STRIPPER', 'Tools', 'Available', '2026-01-27 08:27:14'),
(26, 'TO-2538', 'SOLDERING IRON', 'Tools', 'Available', '2026-01-27 08:27:27'),
(27, 'TO-7574', 'SOLDERING IRON', 'Tools', 'Available', '2026-01-27 08:27:27'),
(28, 'TO-8998', 'SOLDERING IRON', 'Tools', 'Available', '2026-01-27 08:27:27'),
(29, 'TO-9169', 'SOLDERING IRON', 'Tools', 'Available', '2026-01-27 08:27:27'),
(30, 'TO-3077', 'SOLDERING IRON', 'Tools', 'Available', '2026-01-27 08:27:27'),
(31, 'TO-1303', 'SOLDERING IRON', 'Tools', 'Available', '2026-01-27 08:27:27'),
(32, 'TO-8959', 'CUTTER', 'Tools', 'Available', '2026-01-27 08:27:41'),
(33, 'TO-2533', 'ELECTRICAL TAPE', 'Tools', 'Available', '2026-01-27 08:27:50'),
(34, 'TO-6581', 'COMBINATION PLIERS', 'Tools', 'Available', '2026-01-27 08:28:02'),
(35, 'TO-8215', 'COMBINATION PLIERS', 'Tools', 'Available', '2026-01-27 08:28:02'),
(36, 'TO-9686', 'COMBINATION PLIERS', 'Tools', 'Available', '2026-01-27 08:28:02'),
(37, 'TO-5208', 'COMBINATION PLIERS', 'Tools', 'Available', '2026-01-27 08:28:02'),
(38, 'TO-8343', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(39, 'TO-2251', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(40, 'TO-8786', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(41, 'TO-8505', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(42, 'TO-8048', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(43, 'TO-9994', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(44, 'TO-6881', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(45, 'TO-7122', 'LONG NOSE PLIERS', 'Tools', 'Available', '2026-01-27 08:28:20'),
(46, 'TO-5004', 'SIDE CUTTING PLIERS', 'Tools', 'Available', '2026-01-27 08:28:39'),
(47, 'TO-9751', 'SIDE CUTTING PLIERS', 'Tools', 'Available', '2026-01-27 08:28:39'),
(48, 'TO-6834', 'SIDE CUTTING PLIERS', 'Tools', 'Available', '2026-01-27 08:28:39'),
(49, 'TO-7052', 'SIDE CUTTING PLIERS', 'Tools', 'Available', '2026-01-27 08:28:39'),
(50, 'TO-8355', 'SIDE CUTTING PLIERS', 'Tools', 'Available', '2026-01-27 08:28:39'),
(51, 'TO-8901', 'SIDE CUTTING PLIERS', 'Tools', 'Available', '2026-01-27 08:28:39'),
(52, 'TO-5083', 'ADJUSTABLE WRENCH', 'Tools', 'Available', '2026-01-27 08:28:57'),
(53, 'TO-4274', 'ADJUSTABLE WRENCH', 'Tools', 'Available', '2026-01-27 08:28:57'),
(54, 'TO-4879', 'SLIP JOINT PLIERS', 'Tools', 'Available', '2026-01-27 08:29:12'),
(55, 'TO-1149', 'SLIP JOINT PLIERS', 'Tools', 'Available', '2026-01-27 08:29:12'),
(56, 'TO-1020', 'HACKSAW', 'Tools', 'Available', '2026-01-27 08:29:24'),
(57, 'TO-2296', 'HACKSAW', 'Tools', 'Available', '2026-01-27 08:29:24'),
(58, 'TO-8696', 'HAMMER', 'Tools', 'Available', '2026-01-27 08:29:34'),
(59, 'TO-3172', 'BALL HAMMER', 'Tools', 'Available', '2026-01-27 08:29:44'),
(60, 'TO-3182', 'BALL HAMMER', 'Tools', 'Available', '2026-01-27 08:29:44'),
(61, 'ME-4051', 'MULTITESTER - SANWA', 'Measuring', 'Available', '2026-01-27 08:30:01'),
(62, 'ME-1813', 'MULTITESTER - SANWA', 'Measuring', 'Available', '2026-01-27 08:30:01'),
(63, 'ME-3286', 'MULTITESTER - SANWA', 'Measuring', 'Available', '2026-01-27 08:30:01'),
(64, 'ME-7896', 'MULTITESTER - SANWA', 'Measuring', 'Available', '2026-01-27 08:30:01'),
(65, 'TO-9304', 'BENCH METER', 'Tools', 'Archived', '2026-01-27 08:30:13'),
(66, 'TO-4667', 'BENCH METER', 'Tools', 'Archived', '2026-01-27 08:30:13'),
(67, 'TO-2266', 'BENCH METER', 'Tools', 'Archived', '2026-01-27 08:30:13'),
(68, 'TO-4326', 'VOLTMETER', 'Tools', 'Archived', '2026-01-27 08:30:23'),
(69, 'ME-3049', 'MULTITESTER', 'Measuring', 'Available', '2026-01-27 08:30:38'),
(70, 'ME-9854', 'MULTITESTER', 'Measuring', 'Available', '2026-01-27 08:30:38'),
(71, 'ME-5240', 'MULTITESTER', 'Measuring', 'Available', '2026-01-27 08:30:38'),
(72, 'ME-4138', 'MULTITESTER', 'Measuring', 'Available', '2026-01-27 08:30:38'),
(73, 'ME-9548', 'VOLTMETER', 'Measuring', 'Available', '2026-01-27 08:31:04'),
(74, 'ME-9570', 'BENCH METER', 'Measuring', 'Available', '2026-01-27 08:31:20'),
(75, 'ME-7935', 'BENCH METER', 'Measuring', 'Available', '2026-01-27 08:31:20'),
(76, 'ME-8674', 'BENCH METER', 'Measuring', 'Available', '2026-01-27 08:31:20'),
(77, 'ME-7771', 'INSULATION RESISTANCE TESTER 1', 'Measuring', 'Available', '2026-01-27 08:31:33'),
(78, 'ME-8127', 'METER', 'Measuring', 'Available', '2026-01-27 08:31:41'),
(79, 'ME-2159', 'METER', 'Measuring', 'Available', '2026-01-27 08:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `control_no` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `borrow_date` datetime DEFAULT NULL,
  `return_date` date NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status` enum('Pending','Approved','Borrowed','Returned','Declined','Cancelled') NOT NULL,
  `processed_by` varchar(100) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `actual_borrow_date` datetime DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `control_no`, `user_id`, `tool_id`, `borrow_date`, `return_date`, `actual_return_date`, `status`, `processed_by`, `subject`, `room_no`, `date_requested`, `actual_borrow_date`, `feedback`) VALUES
(105, '2026-01-27-2', 2, 14, NULL, '2026-01-27', '2026-01-27 17:24:13', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:20:46', '2026-01-27 17:21:46', NULL),
(106, '2026-01-27-2', 2, 13, NULL, '2026-01-27', '2026-01-27 17:24:16', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:20:46', '2026-01-27 17:22:08', NULL),
(107, '2026-01-27-3', 2, 52, NULL, '2026-01-27', '2026-01-27 17:23:57', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:23:15', '2026-01-27 17:23:36', NULL),
(108, '2026-01-27-3', 2, 53, NULL, '2026-01-27', '2026-01-27 17:23:53', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:23:15', '2026-01-27 17:23:49', NULL),
(109, '2026-01-27-4', 2, 61, NULL, '2026-01-27', '2026-01-27 17:28:13', 'Cancelled', NULL, 'NETWORKING', 'Room 104', '2026-01-27 09:26:39', NULL, NULL),
(110, '2026-01-27-4', 2, 62, NULL, '2026-01-27', NULL, 'Declined', NULL, 'NETWORKING', 'Room 104', '2026-01-27 09:26:39', NULL, NULL),
(111, '2026-01-27-5', 2, 11, NULL, '2026-01-27', '2026-01-27 17:36:20', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:29:58', '2026-01-27 17:33:45', NULL),
(112, '2026-01-27-5', 2, 12, NULL, '2026-01-27', '2026-01-27 17:36:23', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:29:58', '2026-01-27 17:33:48', NULL),
(113, '2026-01-27-5', 2, 74, NULL, '2026-01-27', '2026-01-27 17:36:29', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:29:58', '2026-01-27 17:33:54', NULL),
(114, '2026-01-27-5', 2, 75, NULL, '2026-01-27', '2026-01-27 17:36:32', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:29:58', '2026-01-27 17:33:57', NULL),
(115, '2026-01-27-5', 2, 20, NULL, '2026-01-27', '2026-01-27 17:36:26', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 09:29:58', '2026-01-27 17:33:51', NULL),
(116, '2026-01-27-6', 2, 11, NULL, '2026-01-27', '2026-01-27 18:21:22', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 10:15:18', '2026-01-27 18:20:46', NULL),
(117, '2026-01-27-6', 2, 12, NULL, '2026-01-27', '2026-01-27 18:21:26', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 10:15:19', '2026-01-27 18:20:51', NULL),
(118, '2026-01-27-6', 2, 74, NULL, '2026-01-27', '2026-01-27 18:21:31', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 10:15:19', '2026-01-27 18:20:59', NULL),
(119, '2026-01-27-6', 2, 75, NULL, '2026-01-27', '2026-01-27 18:21:35', 'Returned', 'Laboratory Head', 'NETWORKING', 'Room 104', '2026-01-27 10:15:19', '2026-01-27 18:21:07', NULL),
(120, '2026-01-27-6', 6, 11, NULL, '2026-01-27', '2026-01-27 18:57:34', 'Returned', 'Juan Dela Cruz', 'Networking', 'Room 104', '2026-01-27 10:54:53', '2026-01-27 18:56:17', NULL),
(121, '2026-01-27-6', 6, 12, NULL, '2026-01-27', '2026-01-27 18:57:40', 'Returned', 'Juan Dela Cruz', 'Networking', 'Room 104', '2026-01-27 10:54:53', '2026-01-27 18:56:21', NULL),
(122, '2026-01-27-6', 6, 13, NULL, '2026-01-27', '2026-01-27 18:57:48', 'Returned', 'Juan Dela Cruz', 'Networking', 'Room 104', '2026-01-27 10:54:53', '2026-01-27 18:57:23', NULL),
(123, '2026-01-27-6', 6, 52, NULL, '2026-01-27', '2026-01-27 18:57:53', 'Returned', 'Juan Dela Cruz', 'Networking', 'Room 104', '2026-01-27 10:54:53', '2026-01-27 18:57:29', NULL),
(124, '2026-01-27-6', 6, 11, NULL, '2026-01-27', '2026-01-27 19:02:45', 'Cancelled', NULL, 'NETWORKING', 'Room 104', '2026-01-27 10:58:43', NULL, NULL),
(125, '2026-01-27-6', 6, 12, NULL, '2026-01-27', '2026-01-27 19:02:49', 'Cancelled', NULL, 'NETWORKING', 'Room 104', '2026-01-27 10:58:43', NULL, NULL),
(126, '2026-01-27-7', 6, 11, NULL, '2026-01-27', '2026-01-27 19:03:37', 'Returned', 'Juan Dela Cruz', 'NETWORKING', 'Room 104', '2026-01-27 11:03:10', '2026-01-27 19:03:24', NULL),
(127, '2026-01-27-7', 6, 12, NULL, '2026-01-27', '2026-01-27 19:03:41', 'Returned', 'Juan Dela Cruz', 'NETWORKING', 'Room 104', '2026-01-27 11:03:10', '2026-01-27 19:03:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `id_number` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `course_section` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `course` varchar(50) DEFAULT NULL,
  `year_section` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin','student_assistant') NOT NULL,
  `account_status` enum('active','restricted','deleted') NOT NULL DEFAULT 'active',
  `penalty_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ban_end_date` datetime DEFAULT NULL,
  `ban_reason` text DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `id_number`, `full_name`, `course_section`, `email`, `course`, `year_section`, `password`, `role`, `account_status`, `penalty_points`, `created_at`, `ban_end_date`, `ban_reason`, `otp_code`, `otp_expiry`) VALUES
(1, 'ADMIN-01', 'Laboratory Head', NULL, '', NULL, NULL, 'admin123', 'admin', 'active', 0, '2025-12-05 08:03:07', NULL, NULL, NULL, NULL),
(2, '2025-0001', 'Julian Eric Parco', 'DIT 3-2', 'juricparcome@gmail.com', NULL, NULL, 'student123', 'student', 'active', 5, '2025-12-05 08:03:07', NULL, NULL, NULL, NULL),
(4, 'SA-001', 'Juan Dela Cruz', NULL, '', NULL, NULL, '12345', 'student_assistant', 'active', 0, '2026-01-07 18:25:52', NULL, NULL, NULL, NULL),
(6, '2025-0002-MN-0', 'Lebron James', 'DIT 3-2', 'juricparcoyou@gmail.com', NULL, NULL, 'student123', 'student', 'active', 10, '2026-01-08 16:07:48', NULL, NULL, NULL, NULL);

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
  MODIFY `penalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tools`
--
ALTER TABLE `tools`
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
