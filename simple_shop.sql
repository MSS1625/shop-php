-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 05:21 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simple_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `price`, `description`, `image_url`, `created_at`) VALUES
(3, 'آچار شبکه', 2000000.00, NULL, '1767458315_75ecbfe93449cf4575a30cd120b3b507a963795b_1686122674.webp', '2026-01-03 16:38:35'),
(4, 'مودم', 1000000.00, NULL, '1767542327_1fcc4aa773ccd3247f322f1c418e14bb633d5fca_1611595199.jpg.webp', '2026-01-04 15:58:47'),
(5, 'سوییچ', 4000000.00, NULL, '1767542356_adba94e70abb77ba0af90a3d1f1197e9f90dd016_1731931891.jpg.webp', '2026-01-04 15:59:16'),
(6, 'کابل فیبر نوری', 500000.00, NULL, '1767542384_8e4de2389964215d44dcbce66089eeb70cffc53e_1757515049.jpg.webp', '2026-01-04 15:59:44'),
(7, 'محافظ سوکت', 600000.00, NULL, '1767542422_79d14cd26cad4b7f4b9baac2fe02f1bcc8c2ace5_1706428184.webp', '2026-01-04 16:00:22'),
(8, 'کابل cat6', 200000.00, NULL, '1767542469_1934499.jpg.webp', '2026-01-04 16:01:09'),
(9, 'تستر شبکه', 700000.00, NULL, '1767542521_1c1de6a7af733af6d12cfa0efffcef0c18058ae2_1620042502.webp', '2026-01-04 16:02:01'),
(11, 'آچار شبکه', 670000.00, NULL, '1767542752_112144390.webp', '2026-01-04 16:05:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(4, 'admin', '$2y$10$sukR8AO8pvDO368iDYv5ROEtIfUv7vR2NyezmkuwngGW44A.9PaWa');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
