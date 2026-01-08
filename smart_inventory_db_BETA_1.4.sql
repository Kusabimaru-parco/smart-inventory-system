-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 08:52 PM
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
(4, 'ME-5237', 'Measuring Tape', 'Measuring', 'Available', '2025-12-06 08:05:01'),
(5, 'HA-7127', 'Screw Driver', 'Hand Tool', 'Borrowed', '2026-01-06 16:48:03'),
(6, 'HA-5347', 'Screw Driver', 'Hand Tool', 'Borrowed', '2026-01-06 16:48:13'),
(7, 'HA-6623', 'Hammer', 'Hand Tool', 'Available', '2026-01-07 06:10:42'),
(8, 'PO-4893', 'Circular Saw', 'Power tools', 'Available', '2026-01-08 14:50:23'),
(9, 'CU-1878', 'Shaper', 'Cutting Tools', 'Available', '2026-01-08 14:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `control_no` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `tool_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
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
(1, NULL, 2, 1, '2025-12-05', '2025-12-05', '2025-12-05 22:20:22', 'Returned', NULL, NULL, NULL, '2025-12-05 13:51:31', NULL, NULL),
(2, NULL, 2, 1, '2025-12-05', '2025-12-05', NULL, 'Declined', NULL, NULL, NULL, '2025-12-05 13:51:48', NULL, NULL),
(3, NULL, 2, 1, '2025-12-06', '2025-12-06', '2025-12-06 16:00:02', 'Returned', NULL, NULL, NULL, '2025-12-06 07:57:12', NULL, NULL),
(4, NULL, 2, 1, '2025-12-06', '2025-12-06', '2025-12-06 16:08:02', 'Returned', NULL, NULL, NULL, '2025-12-06 08:05:33', NULL, NULL),
(5, NULL, 2, 2, '2025-12-06', '2025-12-06', '2025-12-06 16:08:07', 'Returned', NULL, NULL, NULL, '2025-12-06 08:05:37', NULL, NULL),
(6, NULL, 2, 3, '2025-12-06', '2025-12-06', '2025-12-06 16:09:00', 'Returned', NULL, NULL, NULL, '2025-12-06 08:05:40', NULL, NULL),
(7, NULL, 2, 3, '2025-12-06', '2025-12-06', '2025-12-06 16:08:12', 'Returned', NULL, NULL, NULL, '2025-12-06 08:05:44', NULL, NULL),
(8, NULL, 2, 4, '2025-12-06', '2025-12-06', '2025-12-06 16:08:19', 'Returned', NULL, NULL, NULL, '2025-12-06 08:05:48', NULL, NULL),
(9, NULL, 2, 1, '2025-12-07', '2025-12-07', '2025-12-07 15:19:22', 'Returned', NULL, NULL, NULL, '2025-12-07 07:18:01', NULL, NULL),
(10, NULL, 2, 1, '2025-12-07', '2025-12-07', '2025-12-07 16:04:46', 'Returned', NULL, NULL, NULL, '2025-12-07 07:25:48', NULL, NULL),
(11, NULL, 2, 2, '2025-12-07', '2025-12-07', '2025-12-07 16:04:41', 'Returned', NULL, NULL, NULL, '2025-12-07 07:25:55', NULL, NULL),
(12, NULL, 2, 1, '2025-12-08', '2025-12-08', '2025-12-08 20:40:20', 'Returned', NULL, NULL, NULL, '2025-12-08 12:39:05', NULL, NULL),
(13, NULL, 2, 2, '2025-12-08', '2025-12-08', '2025-12-08 20:40:25', 'Returned', NULL, NULL, NULL, '2025-12-08 12:39:05', NULL, NULL),
(14, NULL, 2, 3, '2025-12-08', '2025-12-08', '2025-12-08 20:40:40', 'Returned', NULL, NULL, NULL, '2025-12-08 12:39:05', NULL, NULL),
(15, NULL, 2, 4, '2025-12-08', '2025-12-08', '2025-12-08 20:40:44', 'Returned', NULL, NULL, NULL, '2025-12-08 12:39:05', NULL, NULL),
(16, NULL, 2, 2, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:17:51', NULL, NULL),
(17, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:18:28', NULL, NULL),
(18, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:22:30', NULL, NULL),
(19, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:24:19', NULL, NULL),
(20, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:27:11', NULL, NULL),
(21, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:30:10', NULL, NULL),
(22, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:39:03', NULL, NULL),
(23, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:45:02', NULL, NULL),
(24, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:52:09', NULL, NULL),
(25, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 14:54:47', NULL, NULL),
(26, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 15:01:17', NULL, NULL),
(27, NULL, 2, 1, '2025-12-08', '2025-12-08', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 15:03:22', NULL, NULL),
(28, NULL, 2, 1, '2025-12-08', '2025-12-09', '2025-12-09 00:16:43', 'Returned', NULL, NULL, NULL, '2025-12-08 16:00:25', NULL, NULL),
(29, NULL, 2, 2, '2025-12-08', '2025-12-09', NULL, 'Declined', NULL, NULL, NULL, '2025-12-08 16:06:33', NULL, NULL),
(30, NULL, 2, 1, '2025-12-08', '2025-12-09', '2025-12-09 00:18:57', 'Returned', NULL, NULL, NULL, '2025-12-08 16:18:09', NULL, NULL),
(31, NULL, 2, 1, '2025-12-09', '2025-12-09', '2025-12-09 00:31:58', 'Returned', NULL, NULL, NULL, '2025-12-08 16:31:35', NULL, NULL),
(32, NULL, 2, 1, '2025-12-10', '2025-12-10', '2025-12-10 17:19:42', 'Returned', NULL, NULL, NULL, '2025-12-10 09:18:21', NULL, NULL),
(33, NULL, 2, 1, '2025-12-13', '2025-12-13', '2025-12-14 00:36:55', 'Returned', NULL, NULL, NULL, '2025-12-13 10:37:43', NULL, NULL),
(34, NULL, 2, 1, '2026-01-06', '2026-01-06', NULL, '', NULL, NULL, NULL, '2026-01-06 08:45:26', NULL, NULL),
(35, NULL, 2, 2, '2026-01-06', '2026-01-06', NULL, '', NULL, NULL, NULL, '2026-01-06 08:45:26', NULL, NULL),
(36, NULL, 2, 3, '2026-01-06', '2026-01-06', NULL, '', NULL, NULL, NULL, '2026-01-06 08:45:26', NULL, NULL),
(37, NULL, 2, 1, '2026-01-07', '2026-01-07', '2026-01-07 00:07:24', '', NULL, NULL, NULL, '2026-01-06 16:07:04', NULL, NULL),
(38, NULL, 2, 1, '2026-01-07', '2026-01-07', '2026-01-07 00:15:14', '', NULL, NULL, NULL, '2026-01-06 16:14:53', NULL, NULL),
(39, NULL, 2, 1, '2026-01-07', '2026-01-07', '2026-01-07 00:25:20', 'Cancelled', NULL, NULL, NULL, '2026-01-06 16:25:05', NULL, NULL),
(40, NULL, 2, 1, '2026-01-07', '2026-01-07', '2026-01-07 00:47:29', 'Returned', NULL, NULL, NULL, '2026-01-06 16:40:44', '2026-01-07 00:44:48', NULL),
(41, NULL, 3, 1, '2026-01-07', '2026-01-07', '2026-01-07 01:07:47', 'Cancelled', NULL, NULL, NULL, '2026-01-06 16:53:20', NULL, NULL),
(42, NULL, 3, 5, '2026-01-07', '2026-01-07', '2026-01-07 01:07:51', 'Cancelled', NULL, NULL, NULL, '2026-01-06 16:53:20', NULL, NULL),
(43, NULL, 3, 6, '2026-01-07', '2026-01-07', '2026-01-07 01:07:54', 'Cancelled', NULL, NULL, NULL, '2026-01-06 16:53:20', NULL, NULL),
(44, NULL, 3, 1, '2026-01-07', '2026-01-07', '2026-01-07 01:18:28', 'Returned', NULL, NULL, NULL, '2026-01-06 17:08:52', '2026-01-07 01:16:19', NULL),
(45, NULL, 3, 5, '2026-01-07', '2026-01-07', '2026-01-07 01:18:34', 'Returned', NULL, NULL, NULL, '2026-01-06 17:08:52', '2026-01-07 01:16:25', NULL),
(46, NULL, 3, 6, '2026-01-07', '2026-01-07', '2026-01-07 01:18:43', 'Returned', NULL, NULL, NULL, '2026-01-06 17:08:52', '2026-01-07 01:16:31', NULL),
(47, NULL, 3, 1, '2026-01-07', '2026-01-07', NULL, 'Declined', NULL, NULL, NULL, '2026-01-07 03:37:08', NULL, NULL),
(48, NULL, 3, 5, '2026-01-07', '2026-01-07', '2026-01-07 11:39:43', 'Cancelled', NULL, NULL, NULL, '2026-01-07 03:37:08', NULL, NULL),
(49, NULL, 3, 6, '2026-01-07', '2026-01-07', '2026-01-07 11:43:29', 'Returned', NULL, NULL, NULL, '2026-01-07 03:37:08', '2026-01-07 11:40:12', NULL),
(50, NULL, 3, 1, '2026-01-07', '2026-01-07', '2026-01-07 14:23:08', 'Returned', NULL, NULL, NULL, '2026-01-07 06:07:20', '2026-01-07 14:07:58', NULL),
(51, NULL, 3, 5, '2026-01-07', '2026-01-07', '2026-01-07 14:23:18', 'Returned', NULL, NULL, NULL, '2026-01-07 06:07:20', '2026-01-07 14:08:08', NULL),
(52, NULL, 3, 6, '2026-01-07', '2026-01-07', '2026-01-07 14:08:14', 'Cancelled', NULL, NULL, NULL, '2026-01-07 06:07:20', NULL, NULL),
(53, NULL, 3, 1, '2026-01-07', '2026-01-07', '2026-01-07 17:27:56', 'Cancelled', NULL, NULL, NULL, '2026-01-07 08:38:59', NULL, NULL),
(54, '2026-01-07-18', 3, 1, '2026-01-07', '2026-01-07', '2026-01-07 23:09:38', 'Returned', NULL, NULL, NULL, '2026-01-07 15:09:02', '2026-01-07 23:09:17', NULL),
(55, '2026-01-07-19', 3, 1, '2026-01-07', '2026-01-07', '2026-01-07 23:11:05', 'Returned', NULL, NULL, NULL, '2026-01-07 15:10:05', '2026-01-07 23:10:23', NULL),
(56, '2026-01-07-19', 3, 5, '2026-01-07', '2026-01-07', '2026-01-07 23:11:12', 'Returned', NULL, NULL, NULL, '2026-01-07 15:10:05', '2026-01-07 23:10:30', NULL),
(57, '2026-01-07-19', 3, 6, '2026-01-07', '2026-01-07', '2026-01-07 23:11:20', 'Returned', NULL, NULL, NULL, '2026-01-07 15:10:05', '2026-01-07 23:10:37', NULL),
(58, '2026-01-08-1', 3, 1, '2026-01-08', '2026-01-08', '2026-01-08 01:38:35', 'Returned', NULL, 'Basic Electronics', 'Room 104', '2026-01-07 17:37:28', '2026-01-08 01:38:29', NULL),
(59, '2026-01-08-2', 3, 1, '2026-01-08', '2026-01-08', '2026-01-08 02:56:31', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-07 18:53:54', '2026-01-08 02:54:13', NULL),
(60, '2026-01-08-2', 3, 5, '2026-01-08', '2026-01-08', '2026-01-08 02:55:54', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-07 18:53:54', '2026-01-08 02:54:26', NULL),
(61, '2026-01-08-2', 3, 2, '2026-01-08', '2026-01-08', '2026-01-08 02:56:15', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-07 18:53:54', '2026-01-08 02:54:20', NULL),
(62, '2026-01-08-5', 3, 3, '2026-01-08', '2026-01-08', '2026-01-08 02:56:09', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-07 18:55:02', '2026-01-08 02:55:30', NULL),
(63, '2026-01-08-6', 3, 1, '2026-01-08', '2026-01-08', '2026-01-08 16:03:35', 'Returned', 'Juan Dela Cruz', 'Basic Electronics', 'Room 104', '2026-01-08 07:52:39', '2026-01-08 16:03:27', NULL),
(64, '2026-01-08-7', 3, 1, '2026-01-08', '2026-01-08', '2026-01-08 16:06:18', 'Returned', 'Juan Dela Cruz', 'BE', '304', '2026-01-08 08:04:44', '2026-01-08 16:05:10', NULL),
(65, '2026-01-08-8', 3, 1, '2026-01-08', '2026-01-08', '2026-01-08 16:22:05', 'Returned', 'Admin', 'BE', '304', '2026-01-08 08:07:34', '2026-01-08 16:09:43', NULL),
(66, '2026-01-08-9', 3, 3, '2026-01-08', '2026-01-08', '2026-01-08 16:22:11', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-08 08:10:32', '2026-01-08 16:11:26', NULL),
(67, '2026-01-08-10', 3, 4, '2026-01-08', '2026-01-08', '2026-01-08 16:13:52', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-08 08:12:06', '2026-01-08 16:13:16', NULL),
(68, '2026-01-08-11', 3, 4, '2026-01-08', '2026-01-08', '2026-01-08 16:22:17', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-08 08:15:17', '2026-01-08 16:21:12', NULL),
(69, '2026-01-08-12', 3, 2, '2026-01-08', '2026-01-08', '2026-01-08 16:17:49', 'Returned', 'Admin', 'Basic Electronics', 'Room 104', '2026-01-08 08:16:36', '2026-01-08 16:17:30', NULL),
(70, '2026-01-08-13', 3, 8, '2026-01-08', '2026-01-08', NULL, 'Declined', NULL, 'Industrial Technology', '100', '2026-01-08 14:53:58', NULL, NULL),
(71, '2026-01-08-13', 3, 9, '2026-01-08', '2026-01-08', '2026-01-08 23:27:30', 'Returned', 'Admin', 'Industrial Technology', '100', '2026-01-08 14:53:58', '2026-01-08 22:54:19', NULL),
(72, '2026-01-08-13', 3, 7, '2026-01-08', '2026-01-08', '2026-01-08 23:27:24', 'Returned', 'Admin', 'Industrial Technology', '100', '2026-01-08 14:53:58', '2026-01-08 22:54:25', NULL),
(73, '2026-01-08-16', 5, 1, '2026-01-08', '2026-01-08', '2026-01-08 23:43:35', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 15:42:50', '2026-01-08 23:43:16', NULL),
(74, '2026-01-08-16', 5, 5, '2026-01-08', '2026-01-08', '2026-01-08 23:43:41', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 15:42:50', '2026-01-08 23:43:22', NULL),
(75, '2026-01-08-16', 5, 6, '2026-01-08', '2026-01-08', '2026-01-08 23:43:47', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 15:42:50', '2026-01-08 23:43:27', NULL),
(76, '2026-01-09-1', 6, 1, '2026-01-09', '2026-01-09', '2026-01-09 00:09:11', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:08:38', 'Amazing tools!'),
(77, '2026-01-09-1', 6, 2, '2026-01-09', '2026-01-09', '2026-01-09 00:09:17', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:08:42', 'Amazing tools!'),
(78, '2026-01-09-1', 6, 3, '2026-01-09', '2026-01-09', '2026-01-09 00:09:23', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:08:46', 'Amazing tools!'),
(79, '2026-01-09-1', 6, 4, '2026-01-09', '2026-01-09', '2026-01-09 00:09:29', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:08:50', 'Amazing tools!'),
(80, '2026-01-09-1', 6, 8, '2026-01-09', '2026-01-09', '2026-01-09 00:09:59', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:09:04', 'Amazing tools!'),
(81, '2026-01-09-1', 6, 7, '2026-01-09', '2026-01-09', '2026-01-09 00:09:55', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:08:58', 'Amazing tools!'),
(82, '2026-01-09-1', 6, 5, '2026-01-09', '2026-01-09', '2026-01-09 00:09:34', 'Returned', 'Admin', 'Industrial Technology', '304', '2026-01-08 16:08:18', '2026-01-09 00:08:54', 'Amazing tools!'),
(83, '2026-01-09-8', 6, 5, '2026-01-09', '2026-01-09', NULL, 'Borrowed', 'Admin', 'Industrial Technology', '100', '2026-01-08 19:41:54', '2026-01-09 03:49:28', NULL),
(84, '2026-01-09-8', 6, 6, '2026-01-09', '2026-01-09', NULL, 'Borrowed', 'Admin', 'Industrial Technology', '100', '2026-01-08 19:41:54', '2026-01-09 03:49:33', NULL);

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
  `ban_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `id_number`, `full_name`, `course_section`, `email`, `course`, `year_section`, `password`, `role`, `account_status`, `penalty_points`, `created_at`, `ban_end_date`, `ban_reason`) VALUES
(1, 'ADMIN-01', 'Laboratory Head', NULL, '', NULL, NULL, 'admin123', 'admin', 'active', 0, '2025-12-05 08:03:07', NULL, NULL),
(2, '2025-0001', 'Julian Eric Parco', NULL, 'juricparcome@gmail.com', NULL, NULL, 'student123', 'student', 'active', 5, '2025-12-05 08:03:07', NULL, NULL),
(3, 'DEL_3476_1767886428', 'Lebron James', NULL, 'DEL_juricparcoyou@gmail.com', NULL, NULL, '', 'student', 'deleted', 0, '2026-01-06 16:52:13', NULL, NULL),
(4, 'SA-001', 'Juan Dela Cruz', NULL, '', NULL, NULL, '12345', 'student_assistant', 'active', 0, '2026-01-07 18:25:52', NULL, NULL),
(5, 'DEL_526_1767888299', 'Lebron James', NULL, 'DEL_1767888299_juricparcoyou@gmail.com', 'DIT', '3-2', '', 'student', 'deleted', 0, '2026-01-08 15:42:18', NULL, NULL),
(6, '2025-0002-MN-0', 'Lebron James', 'DIT 3-2', 'juricparcoyou@gmail.com', NULL, NULL, 'student123', 'student', 'active', 0, '2026-01-08 16:07:48', NULL, NULL);

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
  MODIFY `tool_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

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
