-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 23, 2024 at 12:38 AM
-- Server version: 10.11.4-MariaDB-1:10.11.4+maria~ubu2204
-- PHP Version: 8.1.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fintrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `account_type` enum('cash','bank') NOT NULL DEFAULT 'cash',
  `account_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `user_id`, `balance`, `account_type`, `account_name`) VALUES
(8, 40, 5000900.00, 'cash', 'Main Cash Account'),
(9, 40, 20280.00, 'bank', 'Main Bank Account'),
(10, 41, 1000.00, 'cash', 'Main Cash Account'),
(11, 41, 0.00, 'bank', 'Main Bank Account'),
(12, 44, 4400.00, 'cash', 'Main Cash Account'),
(13, 44, 0.00, 'bank', 'Main Bank Account');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `budget_amount` decimal(10,2) NOT NULL,
  `budget_period` enum('monthly','yearly') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`budget_id`, `user_id`, `category_id`, `budget_amount`, `budget_period`) VALUES
(20, 40, 6, 360.00, 'monthly'),
(21, 40, 1, 11000.00, 'monthly'),
(22, 40, 7, 15000.00, 'monthly'),
(23, 40, 2, 1500.00, 'monthly');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_type` enum('income','expense') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_type`) VALUES
(1, 'Salary', 'income'),
(2, 'Awards', 'income'),
(3, 'Grants', 'income'),
(4, 'Rental', 'income'),
(5, 'Investments', 'income'),
(6, 'Other', 'income'),
(7, 'Food', 'expense'),
(8, 'Bills', 'expense'),
(9, 'Transportation', 'expense'),
(10, 'Shopping', 'expense'),
(11, 'Health', 'expense'),
(12, 'Other', 'expense');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `user_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `password_hash` varchar(256) NOT NULL,
  `salt` varchar(256) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `api_key` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`user_id`, `email`, `full_name`, `password_hash`, `salt`, `created_date`, `otp`, `otp_expiry`, `api_key`) VALUES
(40, 'sasithachamith@gmail.com', 'Test User', '$2y$10$.VSe/JAkbZ/90i22TCFf/eJP7gJrTFX4o5roNbrSqQw5YOhTlxBBO', '4ec3657833c7979827cb3fb75d280e2a97ba8089bdd93de9cff01e0d0901b90f', '2024-09-15 21:33:00', '494038', '2024-09-23 03:16:04', '557a677ca672a8aaec30b985cc4a69f3'),
(41, 'sasitha@yopmail.com', 'chamith dissanayake', '$2y$10$b5lBhXDDFcrR6S0RdOimyOCuC/oD39MwFwdKpC5BmzoQYbcrpARW6', '502aa7551caf891662ff28bc0adc6576d2d68014cf56154386fc2a859d3c7666', '2024-09-21 09:39:32', NULL, NULL, '02d36144c9d6c49c608c4af4cf3e9645'),
(44, 'sasithachamith97@gmail.com', 'sasitha chamith', '$2y$10$.8bkXqnM3BSu.ltapSfR3.sEUFa3tzbqZQWwZUfVNzw7fdsdzC9wG', '9f3201f2eabbc3072404bd716fc6ee854de31b68f9ecc6b146bfa061ceb74aee', '2024-09-22 21:49:49', NULL, NULL, 'c2a6accce65035adc67086f6359705b5');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `transaction_type` enum('income','expense') NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `category_id`, `account_id`, `amount`, `transaction_date`, `transaction_type`, `description`) VALUES
(22, 40, 8, 8, 500.00, '2024-09-16 00:56:31', 'income', 'Salary for September'),
(23, 40, 8, 8, 500.00, '2024-09-16 01:02:12', 'expense', 'Salary for September'),
(24, 40, 1, 8, 500.00, '2024-09-17 22:56:05', 'income', 'Salary for September'),
(25, 40, 1, 8, 500.00, '2024-09-17 23:00:13', 'income', 'Salary for September'),
(26, 40, 1, 8, 100.50, '2024-09-17 23:48:26', 'income', 'Monthly salary'),
(27, 40, 1, 8, 500.00, '2024-09-18 01:12:59', 'income', 'Salary for September'),
(28, 40, 1, 8, 500.00, '2024-09-18 01:13:01', 'income', 'Salary for September'),
(29, 40, 1, 8, 5.00, '2024-09-18 01:40:20', 'income', 'yy'),
(30, 40, 1, 8, 50000.00, '2024-09-18 01:40:42', 'income', 't'),
(31, 40, 1, 8, 5000.00, '2024-09-18 01:44:07', 'income', ''),
(32, 40, 1, 8, 22000.00, '2024-09-18 01:44:15', 'income', ''),
(33, 40, 1, 8, 50000.00, '2024-09-18 01:44:47', 'income', ''),
(34, 40, 4, 8, 500.00, '2024-09-18 01:44:57', 'income', ''),
(35, 40, 7, 8, 129605.00, '2024-09-18 01:52:41', 'expense', ''),
(36, 40, 9, 8, 0.50, '2024-09-18 01:52:54', 'expense', ''),
(37, 40, 1, 8, 100000.00, '2024-09-18 01:55:23', 'income', ''),
(38, 40, 1, 8, 200.00, '2024-09-18 01:55:31', 'income', ''),
(39, 40, 7, 8, 1000.00, '2024-09-18 01:55:39', 'expense', ''),
(40, 40, 7, 8, 25000.00, '2024-09-18 01:55:44', 'expense', ''),
(41, 40, 7, 8, 74200.00, '2024-09-18 01:55:56', 'expense', ''),
(42, 40, 1, 8, 500.00, '2024-09-18 02:00:24', 'income', ''),
(43, 40, 7, 8, 500.00, '2024-09-18 02:03:50', 'expense', ''),
(44, 40, 1, 8, 50000.00, '2024-09-18 02:05:43', 'income', ''),
(45, 40, 1, 8, 5000.00, '2024-09-18 02:05:54', 'income', ''),
(46, 40, 4, 8, 5000.00, '2024-09-18 02:07:19', 'income', 'hyrr'),
(47, 40, 1, 9, 5000.00, '2024-09-18 02:13:56', 'income', 'sasithachamith '),
(48, 40, 1, 9, 10000.00, '2024-09-18 02:14:05', 'income', ''),
(49, 40, 1, 9, 15000.00, '2024-09-18 02:14:11', 'income', ''),
(50, 40, 10, 8, 5000.00, '2024-09-18 02:14:31', 'expense', ''),
(51, 40, 1, 9, 5000000.00, '2024-09-18 02:15:06', 'income', ''),
(52, 40, 7, 9, 1000000.00, '2024-09-18 02:15:20', 'expense', ''),
(53, 40, 7, 9, 600000.00, '2024-09-18 02:15:26', 'expense', ''),
(54, 40, 7, 9, 3430000.00, '2024-09-18 02:15:51', 'expense', ''),
(55, 40, 7, 8, 50.00, '2024-09-18 02:16:06', 'expense', ''),
(56, 40, 1, 8, 500.00, '2024-09-18 07:29:33', 'income', ''),
(57, 40, 9, 8, 5000.00, '2024-09-18 07:30:10', 'expense', ''),
(58, 40, 9, 8, 50000.00, '2024-09-18 07:30:16', 'expense', ''),
(59, 40, 1, 8, 450.00, '2024-09-18 21:49:11', 'income', ''),
(60, 40, 1, 8, 450.00, '2024-09-18 21:49:22', 'income', ''),
(61, 40, 1, 8, 450.00, '2024-09-18 21:49:22', 'income', ''),
(62, 40, 1, 8, 450.00, '2024-09-18 21:49:34', 'income', ''),
(63, 40, 1, 8, 500.00, '2024-09-18 21:49:43', 'income', ''),
(64, 40, 7, 8, 450.00, '2024-09-18 21:49:56', 'expense', ''),
(65, 40, 7, 8, 450.00, '2024-09-18 21:50:01', 'expense', ''),
(66, 40, 7, 8, 1850.00, '2024-09-18 21:50:10', 'expense', ''),
(67, 40, 1, 9, 280.00, '2024-09-18 23:32:53', 'income', ''),
(68, 40, 1, 8, 500.00, '2024-09-19 00:19:26', 'income', ''),
(69, 40, 1, 8, 10000.00, '2024-09-19 00:45:24', 'income', 'sasithachamith97 '),
(70, 40, 1, 8, 1000.00, '2024-09-19 00:49:34', 'income', ''),
(71, 40, 7, 8, 10000.00, '2024-09-19 00:50:01', 'expense', ''),
(72, 40, 4, 8, 10000.00, '2024-09-19 00:50:49', 'income', ''),
(73, 40, 1, 8, 10.00, '2024-09-19 01:06:02', 'income', ''),
(74, 40, 1, 8, 30.00, '2024-09-19 09:02:28', 'income', ''),
(75, 40, 5, 9, 15000.00, '2024-09-20 09:18:47', 'income', 'share market'),
(76, 40, 10, 8, 11540.00, '2024-09-20 09:19:59', 'expense', ''),
(77, 40, 1, 8, 1000.00, '2024-09-21 09:23:36', 'income', ''),
(78, 41, 1, 10, 1000.00, '2024-09-21 09:39:55', 'income', ''),
(79, 40, 7, 8, 100.00, '2024-09-21 12:43:13', 'expense', ''),
(80, 40, 1, 8, 5000000.00, '2024-09-21 16:11:19', 'income', ''),
(81, 40, 1, 9, 5000.00, '2024-09-21 17:17:42', 'income', ''),
(82, 44, 1, 12, 100.00, '2024-09-22 21:50:00', 'income', ''),
(83, 44, 5, 12, 4500.00, '2024-09-22 21:50:08', 'income', ''),
(84, 44, 7, 12, 200.00, '2024-09-22 21:55:22', 'expense', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`budget_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `account_id` (`account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `member` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `member` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `member` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
