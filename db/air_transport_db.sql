-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2026 at 07:33 AM
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
-- Database: `air_transport_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$VUkrHQzBqDWztCjbq05zq.9Tx9iduvf.alsQEaKMaXhzw398UP1VW', 'admin admin', 1, '2026-10-29 19:56:09', '2026-10-29 07:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `air_patients`
--

CREATE TABLE `air_patients` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL COMMENT '1. วันที่',
  `hn` varchar(20) NOT NULL COMMENT '2. HN',
  `an` varchar(20) DEFAULT NULL COMMENT '2.1. AN',
  `rank_code` varchar(50) DEFAULT NULL COMMENT '3. ยศ',
  `first_name` varchar(50) NOT NULL COMMENT '4. ชื่อ',
  `last_name` varchar(50) NOT NULL COMMENT '4. สกุล',
  `unit` varchar(100) DEFAULT NULL COMMENT '5. หน่วย',
  `mission_type` tinyint(3) UNSIGNED NOT NULL COMMENT '6. ภารกิจ: 1=ฝึกใน ทบ, 2=ฝึกร่วมหลายเหล่าทัพ, 3=ฝึกร่วมต่างประเทศ',
  `mission_name` varchar(100) DEFAULT NULL COMMENT '7. ชื่อภารกิจ',
  `origin` varchar(100) NOT NULL COMMENT '8. ต้นทาง',
  `destination` varchar(100) NOT NULL COMMENT '9. ปลายทาง',
  `diagnosis` varchar(255) NOT NULL COMMENT '10. วินิจฉัยเบื้องต้น',
  `aircraft_type` tinyint(3) UNSIGNED NOT NULL COMMENT '11. อากาศยาน: 1=ฮ.ท.145 (UH 145), 2=ฮ.ท.212 (Bell 212), 3=ฮ.ท.72 (Lekofar), 4=ฮ.ท.60 (UH-60 Black Hawk)',
  `remark_type` tinyint(3) UNSIGNED NOT NULL COMMENT '12. หมายเหตุ: 1=เจ็บป่วย, 2=เป็นการฝึก rehearsal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `air_patients`
--

INSERT INTO `air_patients` (`id`, `date`, `hn`, `an`, `rank_code`, `first_name`, `last_name`, `unit`, `mission_type`, `mission_name`, `origin`, `destination`, `diagnosis`, `aircraft_type`, `remark_type`, `created_at`) VALUES
(1, '2026-10-29', '11179/69', NULL, 'พลตรี', 'เอกชัย', 'โชครักษา', 'ทหารบก', 1, 'เรียนรู้เกี่ยวกับอาวุธปืน', 'โรงพยาบาลอานันทมหหิดล', 'กองบิน 2', 'ฝึกฝนกาใช้ปืน', 4, 2, '2026-10-29 04:21:43'),
(2, '2026-10-06', '14098/67', '2101/69', 'พลฯ', 'ปฏิภาณ', 'อิฐารัตน์', 'ป.1 พัน.31 รอ.', 1, 'ฝึกทบทวนประจำปี', 'ค่ายสุรสีห์', 'โรงพยาบาลพระมงกุฎเกล้า', 'Rhabdomyolysis (CPK > 1,000)', 4, 1, '2026-10-29 06:44:32'),
(3, '2026-09-24', '6636/69', '1985/69', 'พลฯ', 'กฤษณะ', 'ข้อม่วง', 'ร.1 พัน.2 รอ.', 1, 'ฝึกทบทวนประจำปี', 'ค่ายอดิศร', 'โรงพยาบาลอานันทมหิดล', 'Heat Exhaustion', 3, 1, '2026-10-29 06:44:32'),
(4, '2026-10-12', '7321/68', NULL, 'ส.อ.', 'ธนากร', 'ศรีสุวรรณ', 'ป.3 พัน.1', 2, 'ฝึกร่วมเหล่าทัพ 2569', 'สนามบินลพบุรี', 'โรงพยาบาลพระมงกุฎเกล้า', 'Fracture femur', 4, 1, '2026-10-29 06:44:32'),
(5, '2026-10-15', '9054/66', '2230/69', 'จ.ส.อ.', 'วิชัย', 'มั่นคง', 'ม.พัน.4', 3, 'ฝึกร่วมไทย-ต่างประเทศ', 'ค่ายธนะรัชต์', 'โรงพยาบาลค่ายธนะรัชต์', 'Acute appendicitis', 4, 1, '2026-10-29 06:44:32'),
(6, '2026-10-20', '3318/65', NULL, 'ร.ท.', 'สุรชัย', 'พงษ์ไพร', 'พัน.สร.1', 1, 'ฝึกซ้อมลำเลียงทางอากาศ', 'สนามบินดอนเมือง', 'โรงพยาบาลพระมงกุฎเกล้า', 'ซ้อมลำเลียง (ไม่มีผู้ป่วยจริง)', 1, 2, '2026-10-29 06:44:32'),
(7, '2026-10-29', '11145/62', '1', 'กไฟกฟไ', 'ดไดฟ', 'ดไฟด', 'ดไฟด', 2, 'ดไฟด', 'กไฟก', 'กไฟก', 'ดไฟดฟ', 3, 1, '2026-10-29 06:57:13'),
(8, '2026-10-30', '11185/69', '1', 'พลเอก', 'ประหยัด', 'จันทร์อังคารพุธ', 'ทหารบก', 3, 'ศึกรบเรือดำน้ำ', 'ประเทศไทย', 'ประเทศอเมริกา', '-', 2, 2, '2026-10-30 01:34:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`username`);

--
-- Indexes for table `air_patients`
--
ALTER TABLE `air_patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hn` (`hn`),
  ADD KEY `idx_evac_date` (`date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `air_patients`
--
ALTER TABLE `air_patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
