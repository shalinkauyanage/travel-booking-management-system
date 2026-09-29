-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 18, 2026 at 06:46 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `globetrek`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `custom_date` date DEFAULT NULL,
  `custom_duration` int DEFAULT NULL,
  `custom_travelers` int DEFAULT NULL,
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_email`, `package_name`, `date`, `user_id`, `package_id`, `price`, `status`, `custom_date`, `custom_duration`, `custom_travelers`, `special_requests`) VALUES
(5, NULL, 'Ella Scenic Escape – Train & Mountains', '2026-06-17', 5, NULL, 128700.00, 'Confirmed', NULL, NULL, NULL, NULL),
(6, NULL, 'Yala National Park Safari Adventure', '2026-05-06', 5, 18, 31500.00, 'Cancelled', NULL, NULL, NULL, NULL),
(19, NULL, 'Polonnaruwa Ruins & Cycling Heritage Tour', '2026-05-30', 5, 23, 53800.00, 'Pending', '2026-05-30', NULL, 2, ''),
(15, NULL, 'Ella Scenic Escape – Train & Mountains', '2026-05-15', 6, 14, 85800.00, 'Confirmed', '2026-05-15', NULL, 2, ''),
(14, NULL, 'Kandy Heritage & Temple of the Tooth Tour', '2026-05-08', 5, 16, 26500.00, 'Pending', '2026-05-08', NULL, 1, ''),
(13, NULL, 'Sigiriya Rock Fortress & Ancient Cultural Tour', '2026-05-07', 5, 24, 106500.00, 'Confirmed', '2026-05-29', NULL, 4, ''),
(17, NULL, 'Yala National Park Safari Adventure', '2026-05-26', 6, 18, 94500.00, 'Confirmed', '2026-05-26', NULL, 3, ''),
(18, NULL, 'Galle Fort & Southern Coast Experience', '2026-09-02', 6, 19, 59800.00, 'Confirmed', '2026-09-02', NULL, 4, '');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
CREATE TABLE IF NOT EXISTS `packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_days` int DEFAULT NULL,
  `availability` int DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `price`, `image`, `destination`, `duration_days`, `availability`, `description`) VALUES
(14, 'Ella Scenic Escape – Train & Mountains', 42900.00, 'img/Ella tp.jpg', NULL, NULL, 8, NULL),
(15, 'Mirissa Beach Getaway & Whale Watching', 28750.00, 'img/Mirissa tp.jpg', NULL, NULL, 10, NULL),
(16, 'Kandy Heritage & Temple of the Tooth Tour', 26500.00, 'img/Kandy tp.jpg', NULL, NULL, 9, NULL),
(17, 'Nuwara Eliya Tea Country & Cool Climate Escape', 38900.00, 'img/Nuwara Eliya tp.jpg', NULL, NULL, 10, NULL),
(18, 'Yala National Park Safari Adventure', 31500.00, 'img/Yala tp.jpg', NULL, NULL, 6, NULL),
(19, 'Galle Fort & Southern Coast Experience', 29900.00, 'img/Galle.jpg', NULL, NULL, 8, NULL),
(21, 'Arugam Bay Surfing & Beach Adventure', 37800.00, 'img/Arugam Bay.jpg', NULL, NULL, 10, NULL),
(22, 'Anuradhapura Ancient City & Sacred Sites', 27900.00, 'img/Anuradhapura.jpg', NULL, NULL, 10, NULL),
(23, 'Polonnaruwa Ruins & Cycling Heritage Tour', 26900.00, 'img/Ancient Vatadage at Polonnaruwa.jpg', NULL, NULL, 8, NULL),
(24, 'Sigiriya Rock Fortress & Ancient Cultural Tour', 35500.00, 'img/Sigiriya Rock.jpg', 'Sigiriya', 4, 2, ''),
(26, 'Arugam Bay Surf & Safari Adventure', 35800.00, 'img/Arugam Bay.jpg', 'N/A', 4, 12, '');

-- --------------------------------------------------------

--
-- Table structure for table `queries`
--

DROP TABLE IF EXISTS `queries`;
CREATE TABLE IF NOT EXISTS `queries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Randyn Shalinka', 'admin@gmail.com', '123456', 'admin'),
(2, 'Devindu Sathpium', 'staff@gmail.com', '654321', 'staff'),
(5, 'Shemine Kodikara', 'customer@gmail.com', '123456', 'customer'),
(6, 'John David', 'johnD@gmail.com', '111111', 'customer');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
