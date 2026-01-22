-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: sharplync-mysql.mysql.database.azure.com
-- Generation Time: Jan 22, 2026 at 05:56 AM
-- Server version: 8.0.42-azure
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sharpfleet`
--

-- --------------------------------------------------------

--
-- Table structure for table `sf_fuel_entries`
--

CREATE TABLE `sf_fuel_entries` (
  `id` bigint UNSIGNED NOT NULL,
  `organisation_id` int UNSIGNED NOT NULL,
  `vehicle_id` int UNSIGNED NOT NULL,
  `driver_id` int UNSIGNED DEFAULT NULL,
  `trip_id` int UNSIGNED DEFAULT NULL,
  `odometer_reading` int UNSIGNED NOT NULL,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_size_bytes` int UNSIGNED DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailed_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sf_fuel_entries`
--
ALTER TABLE `sf_fuel_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sf_fuel_entries_org` (`organisation_id`),
  ADD KEY `idx_sf_fuel_entries_vehicle` (`vehicle_id`),
  ADD KEY `idx_sf_fuel_entries_driver` (`driver_id`),
  ADD KEY `idx_sf_fuel_entries_trip` (`trip_id`),
  ADD KEY `idx_sf_fuel_entries_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sf_fuel_entries`
--
ALTER TABLE `sf_fuel_entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sf_fuel_entries`
--
ALTER TABLE `sf_fuel_entries`
  ADD CONSTRAINT `fk_fuel_entries_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fuel_entries_org` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fuel_entries_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fuel_entries_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
