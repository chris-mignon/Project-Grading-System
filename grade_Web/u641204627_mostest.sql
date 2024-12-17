-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 17, 2024 at 03:48 PM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u641204627_mostest`
--

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `user_id`, `project_id`, `score`, `created_at`) VALUES
(1, 2, 2, 42.00, '2024-12-16 14:53:09'),
(2, 8, 2, 48.00, '2024-12-16 14:55:07'),
(3, 7, 2, 43.00, '2024-12-16 14:55:20'),
(4, 9, 1, 40.00, '2024-12-16 14:58:46'),
(5, 9, 2, 43.00, '2024-12-16 14:59:39'),
(6, 8, 1, 27.00, '2024-12-16 15:09:01'),
(7, 2, 1, 29.00, '2024-12-16 15:09:06'),
(8, 7, 1, 37.00, '2024-12-16 15:09:33'),
(9, 7, 3, 48.00, '2024-12-16 15:17:47'),
(10, 2, 3, 47.00, '2024-12-16 15:17:48'),
(11, 8, 3, 47.00, '2024-12-16 15:18:05'),
(12, 9, 3, 47.00, '2024-12-16 15:18:54'),
(13, 2, 4, 45.00, '2024-12-16 15:28:32'),
(14, 7, 4, 41.00, '2024-12-16 15:30:04'),
(15, 8, 4, 46.00, '2024-12-16 15:30:49'),
(16, 9, 4, 48.00, '2024-12-16 15:31:10'),
(17, 2, 5, 31.00, '2024-12-16 15:38:21'),
(18, 7, 5, 42.00, '2024-12-16 15:38:47'),
(19, 8, 5, 36.00, '2024-12-16 15:39:32'),
(20, 9, 5, 43.00, '2024-12-16 15:40:01'),
(21, 7, 8, 47.00, '2024-12-16 15:52:50'),
(22, 8, 8, 50.00, '2024-12-16 15:53:04'),
(23, 2, 8, 48.00, '2024-12-16 15:53:41'),
(24, 9, 8, 50.00, '2024-12-16 15:54:39'),
(25, 7, 9, 39.00, '2024-12-16 16:06:19'),
(26, 8, 9, 33.00, '2024-12-16 16:06:58'),
(27, 2, 9, 32.00, '2024-12-16 16:07:41'),
(28, 9, 9, 46.00, '2024-12-16 16:07:47'),
(29, 2, 11, 37.00, '2024-12-16 16:12:21'),
(30, 7, 11, 47.00, '2024-12-16 16:14:34'),
(31, 8, 11, 44.00, '2024-12-16 16:14:50'),
(32, 9, 11, 45.00, '2024-12-16 16:15:55'),
(33, 7, 10, 35.00, '2024-12-16 16:24:10'),
(34, 8, 10, 41.00, '2024-12-16 16:24:14'),
(35, 2, 10, 34.00, '2024-12-16 16:24:41'),
(36, 9, 10, 45.00, '2024-12-16 16:24:59'),
(37, 7, 13, 47.00, '2024-12-16 16:32:23'),
(38, 2, 13, 37.00, '2024-12-16 16:32:57'),
(39, 8, 13, 35.00, '2024-12-16 16:33:00'),
(40, 9, 13, 45.00, '2024-12-16 16:33:22'),
(41, 8, 15, 43.00, '2024-12-16 16:43:52'),
(42, 2, 15, 40.00, '2024-12-16 16:44:14'),
(43, 7, 15, 48.00, '2024-12-16 16:46:10'),
(44, 9, 15, 47.00, '2024-12-16 16:47:01'),
(45, 8, 16, 46.00, '2024-12-16 16:53:42'),
(46, 7, 16, 44.00, '2024-12-16 16:54:40'),
(47, 9, 16, 50.00, '2024-12-16 16:55:55'),
(48, 2, 16, 44.00, '2024-12-16 16:56:03'),
(49, 2, 14, 31.00, '2024-12-16 20:54:12'),
(50, 9, 14, 44.00, '2024-12-16 20:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `created_at`) VALUES
(1, 'Task Management app', 'Trevon Friday and Jaffar Shade', '2024-12-14 23:37:33'),
(2, 'Study Group Finder ', 'Kwamique John, Giovanni Jones', '2024-12-14 23:38:43'),
(3, 'Expensive Item Tracker', 'Ulric Aird and Kieran Francis', '2024-12-14 23:39:51'),
(4, 'Phone Directory Application', 'Anaya Edwards	Micah Sylvester', '2024-12-14 23:40:58'),
(5, 'green haven carts', ' Nailah Greene and	Hughren Epiphane', '2024-12-15 00:43:36'),
(6, 'Intelligent Study Companion Platform', ' Renaud Alexander	Jamal Millette ', '2024-12-15 00:46:04'),
(7, 'Messaging App ', 'Londel Pope	Tyler Modeste', '2024-12-15 00:47:17'),
(8, 'Grenadian-Style Hangman Game ', 'Summer Castle	Jenna Edwards', '2024-12-15 00:50:32'),
(9, '	FitFree', 'Kazim Griffith,	Jevante Coomansingh', '2024-12-15 00:51:35'),
(10, 'Student Grading System', 'Nasim Ramdeen,	Samuel Peters', '2024-12-15 00:53:48'),
(11, 'Smart Planner', 'Zahi Joseph,	Brandon Calliste', '2024-12-15 00:54:42'),
(12, 'To-Do List', 'Rogel Paul', '2024-12-15 00:56:36'),
(13, 'The Two Sides ', 'Jordan St. Juste, Lennon Joseph', '2024-12-15 00:57:27'),
(14, 'AI Study Assistant', ' Ethan Williams', '2024-12-15 00:58:24'),
(15, 'Word pyramid', 'Cherika Hall, Kibose Hamilton', '2024-12-16 15:48:45'),
(16, 'ProgressPal ', 'Jemil Thomas \r\nDonato Christopher', '2024-12-16 15:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `rubric`
--

CREATE TABLE `rubric` (
  `id` int(11) NOT NULL,
  `criterion` varchar(255) NOT NULL,
  `max_score` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `email`, `subscribed_at`) VALUES
(1, 'mignon121@live.com', '2024-12-15 07:24:43'),
(2, 'mignon12@live.com', '2024-12-15 07:29:13'),
(3, 'christopherm@tamcc.edu.gd', '2024-12-15 07:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','lecturer') NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `role`, `email`, `password`) VALUES
(1, 'Admin', 'admin', 'mignon121@live.com', '$2y$10$7koVWqBecbUr8RiZvJzNtedhiu0nD1123nbUbxEVfpyQNT87e/Y2O'),
(2, 'christopher', 'lecturer', 'christopherm@tamcc.edu.gd', '$2y$10$DcJEAvO9uRdHv2IvkN8u8u8d.bvvgcQSZJuzSCLLlkerxpnErjqji'),
(3, 'collins charles', 'lecturer', 'collinsc@tamcc.edu.gd', '$2y$10$tzzGZV1iaINWxN1TiEAXmOjSOPtWhOe9g153gU3TuHaeh7NqA6oSe'),
(4, 'Mαкєdα Gιbbon', 'lecturer', 'makedag@tamcc.edu.gd', '$2y$10$Mvt9.vY4M9k/u7o59uNj..gYZHc7w9e6TRYVcwKyUdnK750I/cXC2'),
(5, 'Carlos Gittens', 'lecturer', 'carlosg@tamcc.edu.gd', '$2y$10$isM4fJsQb3WzPttsF0yFouwtBCXeIRnuGD.JECm6sLineDSapakge'),
(6, 'Chrislyn Charles-Williams', 'lecturer', 'chrislync@tamcc.edu.gd', '$2y$10$B4l5mjs0dO/NzA5QMOdvqedlvpbFnsNu21hzkmzES3.p0ydEULkN2'),
(7, 'Kelly Charles', 'lecturer', 'kellyc@tamcc.edu.gd', '$2y$10$kPmaOt9LoDCf/ikj74QYqeZ3J7dsMpqQPuvEn3OEzeRETNYHywS.m'),
(8, 'Colin Phillip', 'lecturer', 'colin@tamcc.edu.gd', '$2y$10$hvQM6JWEt.Zpegy2sPTw8uyx68aFmAFJgu/FkW91kiDaccBaINBpC'),
(9, 'Edward Heylige', 'lecturer', 'edwardh@tamcc.edu.gd', '$2y$10$dxRvLQbUMRLtkeRKmOHrCesNNpzjigijOMouoktZfCtLpohCVdsY2'),
(10, 'Aaron Gay', 'lecturer', 'aarong@tamcc.edu.gd', '$2y$10$IKp/0UUMG0RKOtklyQwFXuuDvlfn6Kgmeb2Qbxj1kfW9jq3eeh1c6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rubric`
--
ALTER TABLE `rubric`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `rubric`
--
ALTER TABLE `rubric`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
