-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 05, 2026 at 02:36 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `seid_ac_line-monitor`
--

-- --------------------------------------------------------

--
-- Table structure for table `machine_downtime_log`
--

CREATE TABLE `machine_downtime_log` (
  `id` int(11) NOT NULL,
  `machine_id` varchar(50) NOT NULL,
  `area` varchar(50) NOT NULL,
  `stop_time` datetime NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machine_downtime_log`
--

INSERT INTO `machine_downtime_log` (`id`, `machine_id`, `area`, `stop_time`, `start_time`, `duration_seconds`, `created_at`) VALUES
(1, 'IDU-Helium', 'IDU LINE', '2026-08-22 08:28:46', '2026-08-22 08:28:51', 20, '2026-08-22 01:28:46'),
(2, 'IDU-Helium', 'IDU LINE', '2026-08-22 08:30:59', '2026-08-22 08:31:08', 5, '2026-08-22 01:30:59'),
(3, 'ODU-Aging', 'ODU LINE', '2026-09-03 01:33:18', '2026-09-03 01:33:19', 1, '2026-09-02 18:33:18'),
(4, 'ODU-Aging', 'ODU LINE', '2026-09-03 04:24:14', '2026-09-03 04:24:38', 11, '2026-09-02 21:24:14'),
(5, 'ODU-Aging', 'ODU LINE', '2026-09-03 04:59:55', '2026-09-03 04:59:59', 4, '2026-09-02 21:59:55'),
(6, 'ODU-Aging', 'ODU LINE', '2026-09-03 05:32:58', '2026-09-03 06:47:26', 4471, '2026-09-02 22:32:58'),
(7, 'ODU-Aging', 'ODU LINE', '2026-09-04 01:35:20', '2026-09-04 01:39:46', 265, '2026-09-03 18:35:20'),
(8, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:18:10', '2026-09-05 14:18:11', 1, '2026-09-05 07:18:10'),
(9, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:18:11', '2026-09-05 14:18:18', 6, '2026-09-05 07:18:11'),
(10, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:18:23', '2026-09-05 14:18:25', 2, '2026-09-05 07:18:23'),
(11, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:18:27', '2026-09-05 14:18:29', 1, '2026-09-05 07:18:27'),
(12, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:18:29', '2026-09-05 14:18:30', 1, '2026-09-05 07:18:29'),
(13, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:27:08', '2026-09-05 14:27:09', 1, '2026-09-05 07:27:08'),
(14, 'ODU-Aging', 'ODU LINE', '2026-09-05 14:27:11', '2026-09-05 14:27:21', 10, '2026-09-05 07:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `machine_monthly`
--

CREATE TABLE `machine_monthly` (
  `id` int(11) NOT NULL,
  `machine_id` varchar(255) NOT NULL,
  `periode` varchar(50) NOT NULL,
  `total_qty` int(11) NOT NULL DEFAULT 0,
  `last_signal` timestamp NULL DEFAULT NULL,
  `total_stop_seconds` int(11) NOT NULL DEFAULT 0,
  `status` varchar(10) NOT NULL DEFAULT 'GREEN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machine_monthly`
--

INSERT INTO `machine_monthly` (`id`, `machine_id`, `periode`, `total_qty`, `last_signal`, `total_stop_seconds`, `status`) VALUES
(1, 'B3', '2026-08', 0, '2026-08-28 22:18:52', 0, 'GREEN'),
(2, 'A1', '2026-08', 0, '2026-08-31 21:59:11', 0, 'GREEN'),
(3, 'A3', '2026-08', 0, '2026-08-31 21:59:05', 0, 'GREEN'),
(4, 'A4', '2026-08', 0, '2026-08-31 21:59:49', 0, 'GREEN'),
(5, 'B1', '2026-08', 0, '2026-08-29 00:07:09', 0, 'GREEN'),
(6, 'A2', '2026-08', 0, '2026-08-31 21:59:26', 0, 'GREEN'),
(7, 'B2', '2026-08', 0, '2026-08-31 20:05:54', 0, 'GREEN'),
(8, 'B4', '2026-08', 0, '2026-08-25 21:50:21', 0, 'GREEN'),
(9, 'IDU-Helium', '2026-08', 0, NULL, 0, 'GREEN'),
(10, 'ODU-Charging', '2026-08', 0, NULL, 0, 'GREEN'),
(11, 'ODU-Aging', '2026-08', 0, NULL, 0, 'GREEN'),
(12, 'ODU-Vacuum', '2026-08', 0, NULL, 0, 'GREEN'),
(297946, 'A3', '2026-09', 0, '2026-09-05 12:35:52', 0, 'GREEN'),
(297947, 'A1', '2026-09', 0, '2026-09-03 01:40:04', 0, 'GREEN'),
(297948, 'A2', '2026-09', 0, '2026-09-05 12:35:54', 0, 'GREEN'),
(297949, 'A4', '2026-09', 0, '2026-09-05 12:36:00', 0, 'GREEN'),
(298844, 'B2', '2026-09', 0, '2026-09-05 12:35:51', 0, 'GREEN'),
(300415, 'B3', '2026-09', 0, '2026-09-05 08:00:42', 0, 'GREEN'),
(316686, 'B1', '2026-09', 0, '2026-09-05 06:47:30', 0, 'GREEN'),
(329660, 'ODU-Aging', '2026-09', 0, '2026-09-05 07:27:21', 4782, 'GREEN');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `machine_downtime_log`
--
ALTER TABLE `machine_downtime_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_machine_area` (`area`,`machine_id`),
  ADD KEY `idx_time_range` (`stop_time`,`start_time`);

--
-- Indexes for table `machine_monthly`
--
ALTER TABLE `machine_monthly`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `machine_month` (`machine_id`,`periode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `machine_downtime_log`
--
ALTER TABLE `machine_downtime_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `machine_monthly`
--
ALTER TABLE `machine_monthly`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375097;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
