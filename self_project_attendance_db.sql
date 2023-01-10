-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2023 at 10:54 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `self_project_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_session`
--

CREATE TABLE `attendance_session` (
  `id` bigint(20) NOT NULL,
  `admin_id` bigint(20) DEFAULT NULL,
  `session_date` datetime DEFAULT NULL,
  `session_date_end` datetime DEFAULT NULL,
  `present_on_time` int(11) DEFAULT 0,
  `present_late` int(11) DEFAULT 0,
  `out_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `attendance_session`
--

INSERT INTO `attendance_session` (`id`, `admin_id`, `session_date`, `session_date_end`, `present_on_time`, `present_late`, `out_at`, `created_at`, `updated_at`) VALUES
(5, 1, '2023-01-10 14:00:00', '2023-01-10 15:25:00', 0, 0, '2023-01-10 15:28:00', '2023-01-10 08:00:11', '2023-01-10 08:26:21');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempt`
--

CREATE TABLE `login_attempt` (
  `id` bigint(20) NOT NULL,
  `device_id` text DEFAULT NULL,
  `attempt` int(11) NOT NULL DEFAULT 1,
  `update_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `session_detail`
--

CREATE TABLE `session_detail` (
  `id` bigint(20) NOT NULL,
  `attendance_session_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `present_on_time` int(11) DEFAULT 0,
  `present_late` int(11) DEFAULT 0,
  `present_out_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `session_detail`
--

INSERT INTO `session_detail` (`id`, `attendance_session_id`, `user_id`, `present_on_time`, `present_late`, `present_out_at`, `created_at`) VALUES
(12, 5, 2, 0, 0, '2023-01-10 16:44:50', '2023-01-10 08:00:11'),
(13, 5, 3, 0, 0, NULL, '2023-01-10 08:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` bigint(20) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `department` bigint(20) DEFAULT NULL,
  `device_id` text DEFAULT NULL,
  `messaging_id` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `role`, `nip`, `full_name`, `department`, `device_id`, `messaging_id`, `username`, `password`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'admin', '199209262016081020', 'jaja miharja', 1, 'GG2hXcpfrNT4TIDQvy2dl4UtTD4BZpq/WxZBi0CWvKI=', 'b2949840-9fde-4289-a021-e1183a17a385', 'jaja', '$2y$10$Z3RF3CH8a7ze9Fx4nvR7T.tEbOXi5S3T2wa4VcZ/IkRL10QQWll6K', 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2023-01-05 11:25:37', '2023-01-10 16:43:40'),
(2, 'user', '199209262017051001', 'fitriana hambali', 1, 'ZrvUSwrwlO6QjdGa9AuGEnLtPmAYW0zOwsR93w1eueY=', NULL, 'fitriana', '$2y$10$YAIi.EceFfz9cnbQrr3mEut3Nzq9lphEBRGp8ScpX2P/yKudCqMDa', 'https://images.pexels.com/photos/371160/pexels-photo-371160.jpeg?auto=compress&cs=tinysrgb&w=600', '2023-01-06 00:35:23', '2023-01-08 02:29:35'),
(3, 'user', NULL, 'adam malik', 1, '321', NULL, 'adam', '$2y$10$3YxOAe7oWaGC/KG1PfbwOODhVQp3UHqSL43X2Spdt61FMkE1osQpm', NULL, '2023-01-06 17:52:56', '2023-01-07 12:09:55'),
(4, 'admin', '199209262016081020', 'el', 1, 'ZrvUSwrwlO6QjdGa9AuGEnLtPmAYW0zOwsR93w1eueY=', 'dd2ea607-ca17-4f6c-9c0f-08301e32baab', 'el', '$2y$10$Z3RF3CH8a7ze9Fx4nvR7T.tEbOXi5S3T2wa4VcZ/IkRL10QQWll6K', 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2023-01-05 11:25:37', '2023-01-08 14:49:54');

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_attendance`
-- (See below for the actual view)
--
CREATE TABLE `user_attendance` (
`user_id` bigint(20)
,`full_name` varchar(255)
,`session_id` bigint(20)
,`session_date` datetime
,`present_on_time` decimal(22,0)
,`present_late` decimal(22,0)
,`not_present` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_attendance_by_user_id`
-- (See below for the actual view)
--
CREATE TABLE `user_attendance_by_user_id` (
`user_id` bigint(20)
,`full_name` varchar(255)
,`present_on_time` decimal(44,0)
,`present_late` decimal(44,0)
,`not_present` decimal(44,0)
);

-- --------------------------------------------------------

--
-- Structure for view `user_attendance`
--
DROP TABLE IF EXISTS `user_attendance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_attendance`  AS SELECT `user`.`id` AS `user_id`, `user`.`full_name` AS `full_name`, `attendance_session`.`id` AS `session_id`, `attendance_session`.`session_date` AS `session_date`, sum(if(`session_detail`.`present_on_time` = 1,1,0)) AS `present_on_time`, sum(if(`session_detail`.`present_late` = 1,1,0)) AS `present_late`, sum(if(`session_detail`.`present_on_time` = 0 and `session_detail`.`present_late` = 0,1,0)) AS `not_present` FROM (`attendance_session` left join (`session_detail` left join `user` on(`user`.`id` = `session_detail`.`user_id`)) on(`attendance_session`.`id` = `session_detail`.`attendance_session_id`)) WHERE `user`.`role` = 'user' GROUP BY `session_detail`.`attendance_session_id`, `user`.`id``id`  ;

-- --------------------------------------------------------

--
-- Structure for view `user_attendance_by_user_id`
--
DROP TABLE IF EXISTS `user_attendance_by_user_id`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_attendance_by_user_id`  AS SELECT `user_attendance`.`user_id` AS `user_id`, `user_attendance`.`full_name` AS `full_name`, sum(`user_attendance`.`present_on_time`) AS `present_on_time`, sum(`user_attendance`.`present_late`) AS `present_late`, sum(`user_attendance`.`not_present`) AS `not_present` FROM `user_attendance` GROUP BY `user_attendance`.`user_id``user_id`  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_session`
--
ALTER TABLE `attendance_session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempt`
--
ALTER TABLE `login_attempt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_detail`
--
ALTER TABLE `session_detail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_session`
--
ALTER TABLE `attendance_session`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempt`
--
ALTER TABLE `login_attempt`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `session_detail`
--
ALTER TABLE `session_detail`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
