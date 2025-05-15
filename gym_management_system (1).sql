-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 08:49 AM
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
-- Database: `gym_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `slot_time` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_name`, `slot_id`, `status`, `created_at`, `slot_time`) VALUES
(4, 'manoj', 7, 'approved', '2025-05-10 03:46:36', '02:00 PM'),
(5, 'manoj', 8, 'rejected', '2025-05-10 03:51:16', '03:00 PM'),
(6, 'manoj', 9, 'approved', '2025-05-10 04:10:20', '04:00 PM'),
(7, 'manoj', 10, 'rejected', '2025-05-10 04:50:34', '05:00 PM'),
(8, 'kumar', 11, 'approved', '2025-05-12 06:51:35', '06:00 PM'),
(9, 'manoj', 12, 'rejected', '2025-05-12 14:05:35', '07:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` char(60) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `password`, `phone`, `created_at`, `status`) VALUES
(1, 'John Doe', 'johndoe@example.com', 'password123', '1234567890', '2025-05-13 02:51:42', 'inactive'),
(4, 'Manoj', 'manoj@gmail.com', '', '9829894888', '2025-05-13 04:04:27', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `customer_reports`
--

CREATE TABLE `customer_reports` (
  `id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `weight` float NOT NULL,
  `height` float NOT NULL,
  `activity_level` enum('low','moderate','high') NOT NULL,
  `goal` enum('weight_loss','muscle_gain','maintenance') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_reports`
--

INSERT INTO `customer_reports` (`id`, `customer_id`, `weight`, `height`, `activity_level`, `goal`, `created_at`) VALUES
(1, 1, 70, 175, 'moderate', 'weight_loss', '2025-05-13 03:55:11'),
(4, 4, 70, 175, 'moderate', 'weight_loss', '2025-05-13 04:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `diet_plans`
--

CREATE TABLE `diet_plans` (
  `id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `diet_plan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `feedback` text NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `customer_id`, `feedback`, `rating`, `created_at`) VALUES
(1, 4, 'good', 5, '2025-05-13 06:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `customer_id`, `plan_id`, `amount_paid`, `payment_date`, `payment_method`, `transaction_id`) VALUES
(1, 4, 1, 500.00, '2025-05-13 05:41:41', '', ''),
(2, 4, 1, 500.00, '2025-05-13 05:44:51', 'debit_card', '123'),
(3, 4, 1, 500.00, '2025-05-13 05:49:36', 'debit_card', '123'),
(4, 4, 1, 500.00, '2025-05-13 05:52:13', 'debit_card', '123'),
(5, 4, 1, 500.00, '2025-05-13 05:56:25', 'credit_card', '12');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structure`
--

CREATE TABLE `fee_structure` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_structure`
--

INSERT INTO `fee_structure` (`id`, `plan_name`, `fee_amount`, `description`, `created_at`) VALUES
(1, 'Monthly Membership', 500.00, 'Access to gym facilities for one month', '2025-05-13 05:38:11'),
(2, 'Yearly Membership', 5000.00, 'Access to gym facilities for one year', '2025-05-13 05:38:11');

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `body_fat` decimal(5,2) DEFAULT NULL,
  `muscle_mass` decimal(5,2) DEFAULT NULL,
  `waist` decimal(5,2) DEFAULT NULL,
  `hip` decimal(5,2) DEFAULT NULL,
  `chest` decimal(5,2) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `workout_freq` int(11) DEFAULT NULL,
  `energy` int(11) DEFAULT NULL,
  `sleep` decimal(4,2) DEFAULT NULL,
  `water` decimal(4,2) DEFAULT NULL,
  `mood` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `customer_id`, `weight`, `body_fat`, `muscle_mass`, `waist`, `hip`, `chest`, `heart_rate`, `workout_freq`, `energy`, `sleep`, `water`, `mood`, `note`, `created_at`) VALUES
(1, 4, 50.00, 25.00, 75.00, 28.00, 28.00, 150.00, 120, 3, 7, 5.00, 2.00, 6, 'I want to increase my weight . ', '2025-05-12 20:40:09'),
(2, 4, 53.00, 35.00, 65.00, 30.00, 30.00, 155.00, 120, 5, 5, 5.00, 3.00, 6, 'I want to increase weight', '2025-05-12 20:52:02'),
(3, 8, 58.00, 50.00, 50.00, 30.00, 30.00, 155.00, 120, 8, 8, 8.00, 3.00, 5, 'i want to reduce wait', '2025-05-12 21:14:49'),
(4, 8, 55.00, 25.00, 75.00, 29.00, 29.00, 150.00, 110, 5, 8, 6.00, 2.00, 5, 'Good now', '2025-05-12 21:16:51'),
(5, 4, 55.00, 75.00, 25.00, 32.00, 32.00, 155.00, 110, 5, 6, 6.00, 3.00, 5, 'increased my weight', '2025-05-13 09:21:05'),
(6, 4, 55.00, 80.00, 20.00, 32.00, 32.00, 157.00, 120, 3, 5, 7.00, 3.00, 5, 'weight gained', '2025-05-13 09:39:28'),
(7, 4, 45.00, 25.00, 75.00, 28.00, 28.00, 145.00, 90, 7, 8, 8.00, 4.00, 7, 'reduced wight', '2025-05-13 09:42:19'),
(8, 4, 30.00, 10.00, 90.00, 25.00, 25.00, 130.00, 95, 7, 5, 5.00, 2.00, 2, 'weight lossed', '2025-05-13 09:48:01'),
(9, 4, 80.00, 80.00, 20.00, 36.00, 36.00, 160.00, 130, 2, 3, 4.00, 2.00, 1, 'gained weight\r\n', '2025-05-13 09:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `progress_tracker`
--

CREATE TABLE `progress_tracker` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `weight_kg` float DEFAULT NULL,
  `body_fat_percentage` float DEFAULT NULL,
  `workout_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slots`
--

CREATE TABLE `slots` (
  `id` int(11) NOT NULL,
  `slot_time` varchar(50) NOT NULL,
  `status` enum('available','booked') DEFAULT 'available',
  `trainer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slots`
--

INSERT INTO `slots` (`id`, `slot_time`, `status`, `trainer_id`, `user_id`) VALUES
(1, '08:00 AM', 'booked', 3, 4),
(2, '09:00 AM', 'booked', 3, 4),
(3, '10:00 AM', 'booked', 3, 4),
(4, '11:00 AM', 'booked', 3, 4),
(5, '12:00 PM', 'booked', 3, 4),
(6, '01:00 PM', 'booked', 3, 4),
(7, '02:00 PM', 'booked', 3, 4),
(8, '03:00 PM', 'booked', 3, 4),
(9, '04:00 PM', 'booked', 3, 4),
(10, '05:00 PM', 'booked', 3, 4),
(11, '06:00 PM', 'booked', 3, 8),
(12, '07:00 PM', 'booked', 3, 4),
(13, '08:00 PM', 'available', 3, NULL),
(14, '09:00 PM', 'available', 3, NULL),
(15, '10:00 PM', 'available', 3, NULL),
(16, '11:00 PM', 'available', 3, NULL),
(17, '12:00 AM', 'available', 3, NULL),
(18, '01:00 AM', 'available', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','trainer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'madhan', '927623BCS054@mkce.ac.in', '$2y$10$P2nrkXIB3WDtWDtB2OteyuMKcmKtzp2HXnLJxTkWau7idthqEZz1a', 'trainer', '2025-05-07 12:30:20'),
(4, 'manoj', 'manoj@gmail.com', '$2y$10$HymkiUwhKHmBJoHX7s2K6ucFy.yiIDnM70uBcqYDCzpR3WpvOI1j.', 'customer', '2025-05-07 13:38:47'),
(5, 'arun', 'arun@gmail.com', '$2y$10$j5H6qqai.l4HnXv68IFTiO6Nnrj20.Gc8enXrj8M6tVyL7dVlbgGm', 'customer', '2025-05-07 13:45:27'),
(8, 'kumar', 'kumar@gmail.com', '$2y$10$5QO7dx4PCpyfFHgm3Ct9YOawpQ0sKDZOPCprXmL3MXmfeZPDXhnaG', 'customer', '2025-05-12 06:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plans`
--

CREATE TABLE `workout_plans` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `monday` text DEFAULT NULL,
  `tuesday` text DEFAULT NULL,
  `wednesday` text DEFAULT NULL,
  `thursday` text DEFAULT NULL,
  `friday` text DEFAULT NULL,
  `saturday` text DEFAULT NULL,
  `sunday` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_plans`
--

INSERT INTO `workout_plans` (`id`, `customer_id`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`) VALUES
(3, 4, 'Cardio and Stretching', 'Upper Body Strength Training', 'Yoga and Core', 'Lower Body Workout', 'Full Body HIIT', 'Swimming or Cycling', 'Rest Day');

-- --------------------------------------------------------

--
-- Table structure for table `workout_progress`
--

CREATE TABLE `workout_progress` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `weight_lifted` varchar(50) NOT NULL,
  `body_measurements` text NOT NULL,
  `workout_frequency` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workout_progress`
--

INSERT INTO `workout_progress` (`id`, `customer_id`, `weight_lifted`, `body_measurements`, `workout_frequency`, `created_at`) VALUES
(1, 4, '45', 'Chest: 143, Arms: 2, Legs: 9', '2', '2025-05-11 16:52:09'),
(2, 4, '45', 'Chest: 143, Arms: 2, Legs: 9', '2', '2025-05-11 16:52:19'),
(3, 4, '58', 'Chest: 152, Arms: 5, Legs: 12', '7', '2025-05-12 05:05:18'),
(4, 4, '60', 'Chest: 150, Arms: 2, Legs: 6', '3', '2025-05-12 05:07:05'),
(5, 4, '50', 'Chest: 150, Arms: 1, Legs: 5', '2', '2025-05-12 06:08:33'),
(6, 8, '58', 'Chest: 150, Arms: 4, Legs: 8', '3', '2025-05-12 06:51:56'),
(7, 8, '60', 'Chest: 155, Arms: 5, Legs: 8', '5', '2025-05-12 07:16:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customer_reports`
--
ALTER TABLE `customer_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`),
  ADD UNIQUE KEY `customer_id_2` (`customer_id`);

--
-- Indexes for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `progress_tracker`
--
ALTER TABLE `progress_tracker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `slots`
--
ALTER TABLE `slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `workout_progress`
--
ALTER TABLE `workout_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_reports`
--
ALTER TABLE `customer_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `diet_plans`
--
ALTER TABLE `diet_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fee_structure`
--
ALTER TABLE `fee_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `progress_tracker`
--
ALTER TABLE `progress_tracker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slots`
--
ALTER TABLE `slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `workout_progress`
--
ALTER TABLE `workout_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_reports`
--
ALTER TABLE `customer_reports`
  ADD CONSTRAINT `customer_reports_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `diet_plans`
--
ALTER TABLE `diet_plans`
  ADD CONSTRAINT `diet_plans_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `fee_structure` (`id`);

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progress_tracker`
--
ALTER TABLE `progress_tracker`
  ADD CONSTRAINT `progress_tracker_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `slots`
--
ALTER TABLE `slots`
  ADD CONSTRAINT `slots_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `slots_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `workout_progress`
--
ALTER TABLE `workout_progress`
  ADD CONSTRAINT `workout_progress_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
