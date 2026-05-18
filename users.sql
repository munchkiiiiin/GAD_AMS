-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: May 18, 2026 at 06:23 AM
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
-- Database: `gad_submission_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `user_acronym` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `role`, `full_name`, `student_id`, `college`, `year_level`, `user_acronym`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Gender And Development', 'gad.office@bsu.edu.ph', NULL, '$2y$12$TUG4cubrDNdHbOd2q6WsdOAyxxwwCW71UuPE9AEnIIyg.BQeueFW.', 'admin', NULL, NULL, NULL, NULL, 'GAD', NULL, NULL, NULL, NULL),
(2, 'College of Agriculture', 'ca@bsu.edu.ph', NULL, '$2y$12$CNLb7UPOnZpF2yZRY0lwSeykT0VWruAa6R753JUJR3bGr2OCvUyei', 'college', NULL, NULL, NULL, NULL, 'CA', NULL, NULL, NULL, NULL),
(3, 'Registrar\'s Office BSU Buguias Campus', 'buguias.registrar@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Buguias-RO', NULL, NULL, NULL, NULL),
(4, 'Human Resources and Management Office BSU Bokod Campus', 'bokod.hrmo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Bokod-HRMO', NULL, NULL, NULL, NULL),
(5, 'International Relations Office', 'iro@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'IRO', NULL, NULL, NULL, NULL),
(6, 'Disaster Risk Reduction Management', 'drrm@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'DRRM', NULL, NULL, NULL, NULL),
(7, 'College of Social Science', 'css@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CSS', NULL, NULL, NULL, NULL),
(8, 'College of Applied Techonology BSU Bokod Campus', 'bokod.cat@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Bokod-CAT', NULL, NULL, NULL, NULL),
(9, 'University Business Affairs Office', 'ubao@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'UBAO', NULL, NULL, NULL, NULL),
(10, 'University Library and Information Service BSU Buguias Campus', 'ulis.buguias@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Buguias-ULIS', NULL, NULL, NULL, NULL),
(11, 'College of Veterinary Medicine', 'vm@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CVM', NULL, NULL, NULL, NULL),
(12, 'Compensarion, Benefits and Other Obligations', 'cboo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CBOO', NULL, NULL, NULL, NULL),
(13, 'Records Office and Archives', 'roa@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'ROA', NULL, NULL, NULL, NULL),
(14, 'Budget Office', 'bo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'BO', NULL, NULL, NULL, NULL),
(15, 'Office for Quality Assurance and Accreditation', 'oqaa@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'OQAA', NULL, NULL, NULL, NULL),
(16, 'University Health Services BSU Buguias Campus', 'buguias.uhs@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Buguias-UHS', NULL, NULL, NULL, NULL),
(17, 'College of Natural Sciences', 'cns@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CNS', NULL, NULL, NULL, NULL),
(18, 'College of Public Administration and Governance', 'cpag@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CPAG', NULL, NULL, NULL, NULL),
(19, 'Information and Communications Technolgy', 'ict@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'ICT', NULL, NULL, NULL, NULL),
(20, 'General Services Office', 'gso@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'GSO', NULL, NULL, NULL, NULL),
(21, 'College of Engineering', 'ce@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CE', NULL, NULL, NULL, NULL),
(22, 'College of Nursing', 'cn@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CN', NULL, NULL, NULL, NULL),
(23, 'BSU Office of Student Services', 'oss@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'OSS', NULL, NULL, NULL, NULL),
(24, 'University Public Affairs Office', 'upao@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'UPAO', NULL, NULL, NULL, NULL),
(25, 'Accounting Office', 'ao@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'AO', NULL, NULL, NULL, NULL),
(26, 'College of Human Kenetics', 'chk@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CHK', NULL, NULL, NULL, NULL),
(27, 'Horticulture', 'h@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Horticulture', NULL, NULL, NULL, NULL),
(28, 'Bokod Focal Person, University Health Services', 'bokod.uhs@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Bokod-FC,UHS', NULL, NULL, NULL, NULL),
(29, 'Buguias Focal Person, College of Agriculture', 'buguias.ca@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Buguias-FC,CA', NULL, NULL, NULL, NULL),
(30, 'Human Resources Development Office', 'hrdo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'HRDO', NULL, NULL, NULL, NULL),
(31, 'Budget Office Buguias Campus', 'buguias.bo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Buguias-BO', NULL, NULL, NULL, NULL),
(32, 'College of Information Sciences', 'cis@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CIS', NULL, NULL, NULL, NULL),
(33, 'Procurement Management Office BSU Bokod Campus', 'bokod.pmo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Bokod-PMO', NULL, NULL, NULL, NULL),
(34, 'Procurement Management Office', 'pmo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'PMO', NULL, NULL, NULL, NULL),
(35, 'Office of Student Services', 'oss.2@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'OSS', NULL, NULL, NULL, NULL),
(36, 'College of Arts and Humanities', 'cah@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CAH', NULL, NULL, NULL, NULL),
(37, 'College of Teacher Education', 'cte@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CTE', NULL, NULL, NULL, NULL),
(38, 'Human Resource and Management Office', 'hrmo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'HRMO', NULL, NULL, NULL, NULL),
(39, 'College of Home Economics and Technology', 'chet@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CHET', NULL, NULL, NULL, NULL),
(40, 'Supply Property Management Office', 'spmo@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'SPMO', NULL, NULL, NULL, NULL),
(41, 'University Library and Information Services', 'ulis@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'ULIS', NULL, NULL, NULL, NULL),
(42, 'College of Numeracy and Applied Sciences', 'cnas@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CNAS', NULL, NULL, NULL, NULL),
(43, 'Northern Philippines Root Crops Research  & Training Center', 'nprcrtc@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'NPRCRTC', NULL, NULL, NULL, NULL),
(44, 'Open University', 'ou@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'OU', NULL, NULL, NULL, NULL),
(45, 'College of Education BSU Bokod Campus', 'bokod.ce@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'Bokod-CE', NULL, NULL, NULL, NULL),
(46, 'College of Forestry', 'cf@bsu.edu.ph', NULL, '$2y$12$l7EFqawcRIOIN9O.LGwQ..4PpoSt5sbaRziIQVMbNsOJbF7b/3Lpq', 'college', NULL, NULL, NULL, NULL, 'CF', NULL, NULL, NULL, NULL),
(47, 'gad.staff', 'gad.staff@bsu.edu.ph', NULL, '$2y$12$fbD/jvk.znEQnBmKq4.ebOojmijHJO/zU7.P7Tzo.zV3FgvP8PzNe', 'gad_staff', 'GAD Staff User', NULL, NULL, NULL, 'GAD-STAFF', NULL, NULL, '2026-03-26 23:53:56', '2026-03-26 23:53:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
