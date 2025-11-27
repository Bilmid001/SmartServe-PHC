-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 12:32 PM
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
-- Database: `phchms`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_recommendations`
--

CREATE TABLE `ai_recommendations` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `recommendation_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `confidence_level` decimal(5,2) DEFAULT NULL,
  `status` enum('pending','reviewed','implemented') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eha_reports`
--

CREATE TABLE `eha_reports` (
  `id` int(11) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `inspector_id` int(11) DEFAULT NULL,
  `inspection_date` date DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `risk_level` enum('low','medium','high') DEFAULT NULL,
  `status` enum('open','in-progress','resolved') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_tests`
--

CREATE TABLE `lab_tests` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `test_type` varchar(100) DEFAULT NULL,
  `test_name` varchar(100) DEFAULT NULL,
  `ordered_by` int(11) DEFAULT NULL,
  `status` enum('pending','in-progress','completed') DEFAULT 'pending',
  `results` text DEFAULT NULL,
  `normal_range` text DEFAULT NULL,
  `flag` enum('normal','abnormal') DEFAULT NULL,
  `uploaded_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `visit_date` datetime DEFAULT current_timestamp(),
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_id`, `full_name`, `date_of_birth`, `gender`, `address`, `phone`, `email`, `emergency_contact`, `blood_type`, `allergies`, `medical_history`, `created_at`) VALUES
(1, 'PAT20251124233', 'Ahmad Abdulhamid', '2000-05-15', 'Male', 'Angwan Tofa', '08069715695', 'bilmid@gmail.com', '08069715695', 'B+', 'none', 'malaria', '2025-11-24 20:09:45'),
(2, 'PAT20251124162', 'Ahmad Abdulhamid', '2000-05-15', 'Male', 'Angwan Tofa', '08069715695', 'bilmid@gmail.com', '08069715695', 'B+', 'none', 'malaria', '2025-11-24 20:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_inventory`
--

CREATE TABLE `pharmacy_inventory` (
  `id` int(11) NOT NULL,
  `drug_name` varchar(100) NOT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','records','doctor','pharmacy','lab','eha','patient') NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `department`, `full_name`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$AMEHPipZr0ofbd8ARhY/2OxyOmct8hZcIt0JhFAtJSmOOyVq2Wlsu', 'admin@phchms.com', 'admin', 'Administration', 'System Administrator', 'active', '2025-11-22 12:49:36'),
(2, 'records1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'records@phchms.com', 'records', 'Records', 'Records Officer', 'active', '2025-11-22 12:49:36'),
(3, 'dr_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr.smith@phchms.com', 'doctor', 'Cardiology', 'Dr. John Smith', 'active', '2025-11-22 12:49:36'),
(4, 'pharmacy1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacy@phchms.com', 'pharmacy', 'Pharmacy', 'Pharmacy Manager', 'active', '2025-11-22 12:49:36'),
(5, 'doctor', '$2y$10$l7TOwuUF.hyVjYlZicFtB.QnotbkR4luQIiWE01gZAvALRDyGUEbW', 'doctor@gmail.com', 'doctor', 'Doctor', 'Ahmad Abdulhamid', 'active', '2025-11-24 17:41:47'),
(6, 'environmental', '$2y$10$00NEcELarO1YoV53AAdCvOHt4SQnrPLMe4uuu9y0BKtUbBlHW9jTi', 'environmental@gmail.com', 'eha', '', 'Ahmad Adam', 'active', '2025-11-24 19:28:31'),
(7, 'laboratory', '$2y$10$eaYYBiIoyzuz6Kh7.PMX/ew1eDp9LbjTq8srs0LGumpfLKK0WBvdi', 'laboratory@gmail.com', 'lab', 'laboratory', 'Ahmad Abdulhamid', 'active', '2025-11-24 19:39:10'),
(8, 'patient', '$2y$10$M5iCUWwMlFpLvPQCRfI/9O.tLnsVcvGBuR4zlYqzlHlQi2byrTfTu', 'patient@gmail.com', 'patient', 'Patient', 'Ahmad Abdulhamid', 'active', '2025-11-24 19:49:43'),
(9, 'pharmacy', '$2y$10$qhaC3AEgHceSt2pM20buiOMXhabHdOo7YVtLSzQ0rUe0saVQNdZlS', 'pharmacy@gmail.com', 'pharmacy', 'pharmacy', 'Ahmad Abdulhamid', 'active', '2025-11-24 20:00:11'),
(10, 'record', '$2y$10$Of8o8i8VySPTHKNwZb9sguWnMUwMiwSHro/4b/7jA14f/pmfZOxfG', 'record@gmail.com', 'records', 'record', 'Ahmad Abdulhamid', 'active', '2025-11-24 20:06:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eha_reports`
--
ALTER TABLE `eha_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspector_id` (`inspector_id`);

--
-- Indexes for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `ordered_by` (`ordered_by`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`);

--
-- Indexes for table `pharmacy_inventory`
--
ALTER TABLE `pharmacy_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eha_reports`
--
ALTER TABLE `eha_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_tests`
--
ALTER TABLE `lab_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pharmacy_inventory`
--
ALTER TABLE `pharmacy_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `eha_reports`
--
ALTER TABLE `eha_reports`
  ADD CONSTRAINT `eha_reports_ibfk_1` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD CONSTRAINT `lab_tests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `lab_tests_ibfk_2` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
