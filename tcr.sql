-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 01:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tcr`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_status` varchar(255) DEFAULT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `driver_name` varchar(255) NOT NULL,
  `pickup_date` datetime NOT NULL,
  `dropoff_date` datetime NOT NULL,
  `license_no` varchar(100) NOT NULL,
  `valid_id` varchar(100) DEFAULT NULL,
  `destination` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_status`, `car_id`, `user_id`, `driver_name`, `pickup_date`, `dropoff_date`, `license_no`, `valid_id`, `destination`, `purpose`) VALUES
(1, 'Approved', 35, 3, 'Zake', '2024-10-16 18:14:00', '2024-10-18 18:14:00', 'L923213', '', 'davao', 'trip2 lng gud'),
(2, 'Pending', 43, 3, 'Zake', '2024-10-18 18:20:00', '2024-10-20 18:20:00', 'L06-229023-23', '', 'Tagum', 'Travel purose');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `car_status` varchar(255) NOT NULL,
  `car_brand` varchar(255) NOT NULL,
  `car_description` varchar(255) NOT NULL,
  `rent_price` varchar(50) NOT NULL,
  `body_type` varchar(255) NOT NULL,
  `transmission` varchar(255) NOT NULL,
  `fuel_type` varchar(255) NOT NULL,
  `car_image` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `car_status`, `car_brand`, `car_description`, `rent_price`, `body_type`, `transmission`, `fuel_type`, `car_image`) VALUES
(35, 'Available', 'Ford', 'Ranger 2.0L 4x4', '2000', 'Pickup', 'Automatic', 'Diesel', 0x75706c6f6164732f72616e6765722e6a7067),
(43, 'Available', 'Toyota', 'Vios 1.5L ', '1000', 'Sedan', 'Manual', 'Gasoline', 0x75706c6f6164732f76696f732e6a7067),
(44, 'Available', 'Toyota', 'Rush 1.5L 4x2', '1500', 'SUV', 'Automatic', 'Gasoline', 0x75706c6f6164732f727573682e6a7067),
(45, 'Available', 'Mitsubishi', 'Montero GT 2.4L 4x4', '2000', 'SUV', 'Automatic', 'Diesel', 0x75706c6f6164732f6d6f6e7465726f2e6a7067),
(46, 'Available', 'Suzuki', 'Swift 1.2L CVT', '900', 'Hatchback', 'Manual', 'Gasoline', 0x75706c6f6164732f73776966742e6a7067),
(47, 'Available', 'Kia', 'Forte 1.6L Turbo GT', '1400', 'Sedan', 'Automatic', 'Gasoline', 0x75706c6f6164732f666f7274652e6a7067);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` varchar(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `number` varchar(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `profile_image` longblob DEFAULT NULL,
  `license_front_image` longblob DEFAULT NULL,
  `license_back_image` longblob DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `fullname`, `email`, `number`, `password`, `position`, `birthdate`, `profile_image`, `license_front_image`, `license_back_image`, `created_at`) VALUES
(1, 'admin', 'Admin', 'admin@gmail.com', '09234567890', '$2y$10$MSUg3IZ8McbCYp4GgQxtnut1.UNIxjhLxKzmeTTBwNZlnd5/v8rLy', 'Admin', NULL, 0x70726f66696c65732f70726f66696c652e6a7067, NULL, NULL, '2024-10-15 15:33:51'),
(2, 'customer', 'john', 'john@gmail.com', '09123456712', '$2y$10$Z2NmOXovVkIq97XoF/n6T.jM85.s5us2b7n34UNztDfOTLSL3gb5y', NULL, '2003-03-15', 0x70726f66696c65732f666f7274652e6a7067, 0x6c6963656e7365732f66726f6e745f313732383938373131355f66726f6e74206c6963656e73652e6a7067, 0x6c6963656e7365732f6261636b5f313732383938373132325f6261636b206c6963656e73652e6a7067, '2024-10-15 15:35:03'),
(3, 'customer', 'Customer 2', 'customer2@gmail.com', '09987654321', '$2y$10$gBdrFurzyED2/UhYkJcyte0Rxrp3ZVYuW0DfDK4Psu0X0voV16Qc2', NULL, '2024-10-16', 0x70726f66696c65732f666f7274652e6a7067, 0x6c6963656e7365732f66726f6e745f313732383938373237355f66726f6e74206c6963656e73652e6a7067, 0x6c6963656e7365732f6261636b5f313732383938373237395f6261636b206c6963656e73652e6a7067, '2024-10-15 15:35:03'),
(4, 'customer', 'John', 'johnlloydferido@gmail.com', '09187957961', '$2y$10$hr4bQ3E7b5h99nyR32qPAOs2hjfi4eW9Z3d2JHv3l2bZXgjpXUZsW', NULL, NULL, 0x70726f66696c65732f70726f66696c652e6a7067, NULL, NULL, '2024-10-15 18:39:22'),
(5, 'customer', 'John', 'mopay55007@daypey.com', '09284360121', '$2y$10$zGXC2Hu5kVSTa.DPizJPHeYvHaW5s0PubdFBIFO3M/gZlnDT2zaH.', NULL, NULL, 0x70726f66696c65732f70726f66696c652e6a7067, NULL, NULL, '2024-10-15 18:41:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
