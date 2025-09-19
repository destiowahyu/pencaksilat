-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 10, 2025 at 01:16 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pencak_silat_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `age_categories`
--

CREATE TABLE `age_categories` (
  `id` int NOT NULL,
  `competition_id` int DEFAULT NULL,
  `nama_kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usia_min` int NOT NULL,
  `usia_max` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `age_categories`
--

INSERT INTO `age_categories` (`id`, `competition_id`, `nama_kategori`, `usia_min`, `usia_max`, `created_at`) VALUES
(1, 5, 'USIA DINI 1', 1, 7, '2025-06-25 08:56:00'),
(2, 5, 'USIA DINI 2', 8, 10, '2025-06-25 08:56:00'),
(3, 5, 'PRA REMAJA', 11, 14, '2025-06-25 08:56:00'),
(4, 5, 'REMAJA', 15, 19, '2025-06-25 08:56:00'),
(5, 5, 'DEWASA/UMUM', 20, 45, '2025-06-25 08:56:00'),
(6, 6, 'USIA DINI 1', 7, 10, '2025-07-12 17:14:59'),
(7, 6, 'USIA DINI 2', 11, 15, '2025-07-12 17:15:13'),
(8, 6, 'PRA REMAJA', 16, 18, '2025-07-12 17:15:45'),
(9, 6, 'REMAJA', 19, 24, '2025-07-12 17:16:00'),
(10, 6, 'DEWASA/UMUM', 20, 45, '2025-07-12 17:16:16'),
(11, 8, 'USIA DINI 1', 7, 10, '2025-09-02 15:21:00'),
(12, 8, 'USIA DINI 2', 10, 15, '2025-09-02 15:21:17'),
(13, 8, 'PRA REMAJA', 15, 18, '2025-09-02 15:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `athletes`
--

CREATE TABLE `athletes` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `kontingen_id` int DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nik` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `tempat_lahir` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_sekolah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `athletes`
--

INSERT INTO `athletes` (`id`, `user_id`, `kontingen_id`, `foto`, `nama`, `nik`, `jenis_kelamin`, `tanggal_lahir`, `tempat_lahir`, `nama_sekolah`, `berat_badan`, `tinggi_badan`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 'Ahmad Rizki', '3171234567890001', 'L', '2005-03-15', 'Jakarta', 'SMA Negeri 1 Jakarta', '65.50', '170.00', '2025-06-21 10:11:35', '2025-06-21 10:11:35'),
(2, 3, 1, NULL, 'Siti Aminah', '3171234567890002', 'P', '2006-07-20', 'Jakarta', 'SMA Negeri 2 Jakarta', '55.00', '160.00', '2025-06-21 10:11:35', '2025-06-21 10:11:35'),
(3, 3, 2, NULL, 'Budi Santoso', '3273234567890001', 'L', '2004-11-10', 'Bandung', 'SMA Negeri 1 Bandung', '70.00', '175.00', '2025-06-21 10:11:35', '2025-06-21 10:11:35'),
(4, 5, 4, '1752418482_6873c8b2aee2d.jpg', 'zidan', '1111237738849979', 'L', '2010-02-01', 'semarang', 'SDN 01 KOTA SEMARANG', '40.00', '145.00', '2025-07-13 14:54:42', '2025-07-13 15:09:48'),
(5, 5, 5, '1752423874_6873ddc2424e4.jpg', 'ariq', '1111237738849979', 'L', '2015-02-03', 'semarang', 'SDN 01 KOTA SEMARANG', '45.00', '160.00', '2025-07-13 16:24:34', '2025-08-28 16:29:08'),
(10, 5, 5, NULL, 'Ahmad Rizki', '1234567891011120', 'L', '2005-03-15', 'Jakarta', 'SMA Negeri 1 Jakarta', '65.50', '170.00', '2025-08-28 16:39:57', '2025-08-28 16:39:57'),
(11, 5, 5, NULL, 'Siti Aminah', '1234567891022120', 'P', '2006-07-20', 'Jakarta', 'SMA Negeri 2 Jakarta', '55.00', '160.00', '2025-08-28 16:39:57', '2025-08-28 16:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `bracket_results`
--

CREATE TABLE `bracket_results` (
  `id` int NOT NULL,
  `draw_id` int NOT NULL,
  `round` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `match_id` int NOT NULL,
  `winner_player_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `kategori_umur` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kompetisi` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kategori_tanding` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bracket_results`
--

INSERT INTO `bracket_results` (`id`, `draw_id`, `round`, `match_id`, `winner_player_id`, `created_at`, `updated_at`, `kategori_umur`, `jenis_kelamin`, `jenis_kompetisi`, `kategori_tanding`) VALUES
(282, 4, '1', 2, 2, '2025-07-25 10:44:30', '2025-07-25 10:44:30', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(283, 4, '1', 3, 4, '2025-07-25 10:44:30', '2025-07-25 10:44:30', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(284, 4, '1', 4, 6, '2025-07-25 10:59:24', '2025-07-25 10:59:24', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(285, 4, '1', 5, 9, '2025-07-25 10:59:24', '2025-07-25 10:59:24', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(286, 4, '1', 6, 11, '2025-07-25 10:59:24', '2025-07-25 10:59:24', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(287, 4, '1', 7, 13, '2025-07-25 10:59:24', '2025-07-25 10:59:24', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(288, 4, '1', 8, 14, '2025-07-25 10:59:24', '2025-07-25 10:59:24', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(289, 4, '2', 9, 2, '2025-07-25 10:59:33', '2025-07-25 10:59:33', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(290, 4, '2', 10, 6, '2025-07-25 10:59:33', '2025-07-25 10:59:33', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(291, 4, '2', 11, 11, '2025-07-25 10:59:33', '2025-07-25 10:59:33', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(292, 4, '2', 12, 13, '2025-07-25 10:59:33', '2025-07-25 10:59:33', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(293, 4, '3', 13, 2, '2025-07-25 10:59:40', '2025-07-25 10:59:40', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(294, 4, '3', 14, 13, '2025-07-25 10:59:40', '2025-07-25 10:59:40', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(295, 4, '4', 15, 2, '2025-07-25 10:59:43', '2025-07-25 10:59:43', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(296, 5, '1', 8, 10, '2025-07-27 22:00:24', '2025-07-27 22:00:24', 'PRA REMAJA', 'L', 'TANDING', '\"TANDING KELAS J\"'),
(333, 24, '1', 1, 1, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(334, 24, '1', 2, 2, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(335, 24, '1', 3, 3, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(336, 24, '1', 4, 4, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(337, 24, '1', 5, 6, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(338, 24, '1', 6, 8, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(339, 24, '1', 7, 9, '2025-09-02 22:34:47', '2025-09-02 22:34:47', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(340, 24, '1', 8, 11, '2025-09-02 22:34:53', '2025-09-02 22:34:53', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(341, 24, '2', 1, 1, '2025-09-02 22:34:58', '2025-09-02 22:34:58', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(342, 24, '2', 2, 3, '2025-09-02 22:34:58', '2025-09-02 22:34:58', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(343, 24, '2', 3, 6, '2025-09-02 22:35:03', '2025-09-02 22:35:03', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(344, 24, '2', 4, 11, '2025-09-02 22:35:03', '2025-09-02 22:35:53', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(345, 24, '3', 1, 1, '2025-09-02 22:36:15', '2025-09-02 22:36:15', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(346, 24, '3', 2, 11, '2025-09-02 22:36:15', '2025-09-02 22:36:15', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(347, 24, '4', 1, 1, '2025-09-02 22:36:25', '2025-09-02 22:36:25', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(355, 25, '1', 1, 1, '2025-09-06 07:56:55', '2025-09-06 07:56:55', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(356, 25, '1', 2, 3, '2025-09-06 07:56:55', '2025-09-06 07:56:55', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(357, 25, '1', 3, 4, '2025-09-06 07:56:55', '2025-09-06 07:56:55', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(358, 25, '1', 4, 6, '2025-09-06 07:57:03', '2025-09-06 07:57:03', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(359, 25, '2', 1, 3, '2025-09-06 07:57:08', '2025-09-06 08:03:24', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(360, 25, '2', 2, 6, '2025-09-06 07:57:22', '2025-09-06 07:57:22', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(361, 25, '3', 1, 6, '2025-09-06 07:57:31', '2025-09-06 07:57:31', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C');

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int NOT NULL,
  `nama_perlombaan` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `lokasi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `maps_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `whatsapp_group` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `kontak_panitia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('coming_soon','open_regist','close_regist','active','finished','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'coming_soon',
  `tanggal_open_regist` date DEFAULT NULL,
  `tanggal_close_regist` date DEFAULT NULL,
  `tanggal_pelaksanaan` date DEFAULT NULL,
  `poster` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `registration_status` enum('auto','coming_soon','open_regist','close_regist') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'auto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `nama_perlombaan`, `deskripsi`, `lokasi`, `maps_link`, `whatsapp_group`, `kontak_panitia`, `status`, `tanggal_open_regist`, `tanggal_close_regist`, `tanggal_pelaksanaan`, `poster`, `created_at`, `updated_at`, `registration_status`) VALUES
(2, 'Piala Gubernur Jawa Barat 2024', 'Kompetisi pencak silat antar kontingen se-Jawa Barat dengan kategori tanding, tunggal, dan beregu.', 'GOR Pajajaran, Bandung', NULL, NULL, NULL, 'active', NULL, NULL, '2024-04-20', NULL, '2024-01-19 20:30:00', '2024-01-19 20:30:00', 'auto'),
(3, 'Festival Pencak Silat Pelajar 2024', 'Festival pencak silat khusus untuk pelajar SMP dan SMA se-Indonesia dengan fokus pada pembinaan atlet muda.', 'GOR Gelora Bung Karno, Jakarta', NULL, NULL, NULL, 'active', NULL, NULL, '2024-05-10', NULL, '2024-02-01 00:15:00', '2024-02-01 00:15:00', 'auto'),
(4, 'Kejuaraan Pencak Silat Veteran 2024', 'Kompetisi khusus untuk atlet veteran pencak silat dengan kategori umur 35+ dan 45+.', 'GOR Satria, Purwokerto', NULL, NULL, NULL, 'active', NULL, NULL, '2024-06-05', NULL, '2024-02-09 19:45:00', '2025-07-12 16:36:18', 'auto'),
(5, 'Open Tournament Pencak Silat 2024', 'Turnamen terbuka untuk semua kalangan dengan sistem eliminasi langsung dan babak penyisihan.', 'GOR Kertajaya, Surabaya', 'https://chat.whatsapp.com/BY0QQoNvjcs72bwYq9PEQV', 'https://chat.whatsapp.com/BY0QQoNvjcs72bwYq9PEQV', '089878888', 'active', '2025-06-23', '2025-06-28', '2025-06-25', '1751435820_6864ca2c390a1.jpg', '2024-02-15 02:20:00', '2025-07-12 16:36:20', 'open_regist'),
(6, 'KENDAL CHAMPIONSHIP', 'ddd', 'Gor bahurekso', 'https://maps.app.goo.gl/NFfzfSyfXrAwZCPs5', 'https://chat.whatsapp.com/IC11ZcShREtHxJ2H1K2fjX', NULL, 'active', '2025-07-31', '2025-08-01', '2025-08-16', '1752338877_687291bd5392c.png', '2025-07-12 16:40:55', '2025-09-02 15:50:04', 'open_regist'),
(7, 'UDINUS PENCAK SILAT CHAMPIONSHIP', 'DGGHHJSJK', 'GOR HASANUDIN SEMARANG', '', '', NULL, 'active', '2025-08-28', '2025-10-31', '2025-11-29', NULL, '2025-08-28 14:26:22', '2025-08-28 14:55:35', 'open_regist'),
(8, 'TTMC', 'CCCDDD', 'GOR HASANUDIN SEMARANG', 'https://maps.app.goo.gl/NFfzfSyfXrAwZCPs5', 'https://chat.whatsapp.com/IC11ZcShREtHxJ2H1K2fjX?mode=r_t', NULL, 'active', '2025-09-02', '2025-10-30', '2025-12-28', '1756826389_68b70b15f1356.jpeg', '2025-08-28 14:35:24', '2025-09-02 15:19:50', 'open_regist');

-- --------------------------------------------------------

--
-- Table structure for table `competition_admins`
--

CREATE TABLE `competition_admins` (
  `id` int NOT NULL,
  `competition_id` int DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_admins`
--

INSERT INTO `competition_admins` (`id`, `competition_id`, `admin_id`, `assigned_at`) VALUES
(3, 5, 2, '2025-06-22 22:18:25'),
(5, 6, 2, '2025-08-28 14:43:40'),
(6, 7, 2, '2025-08-28 14:44:00'),
(8, 8, 2, '2025-09-02 15:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `competition_categories`
--

CREATE TABLE `competition_categories` (
  `id` int NOT NULL,
  `competition_id` int DEFAULT NULL,
  `nama_kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `berat_min` decimal(5,2) DEFAULT NULL,
  `berat_max` decimal(5,2) DEFAULT NULL,
  `age_category_id` int DEFAULT NULL,
  `jenis_kelamin` enum('L','P','Campuran') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Campuran',
  `competition_type_id` int DEFAULT NULL,
  `tipe_kelas` enum('Berat','Tinggi','Lengan','Umur','Campuran') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Berat',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_categories`
--

INSERT INTO `competition_categories` (`id`, `competition_id`, `nama_kategori`, `deskripsi`, `berat_min`, `berat_max`, `age_category_id`, `jenis_kelamin`, `competition_type_id`, `tipe_kelas`, `created_at`) VALUES
(1, 5, 'TANDING KELAS A', '', '30.00', '40.00', 1, 'Campuran', NULL, 'Berat', '2025-06-25 08:56:00'),
(2, 5, 'TANDING KELAS B', '', '40.00', '50.00', 1, 'Campuran', NULL, 'Berat', '2025-06-25 08:56:00'),
(3, 5, 'TANDING KELAS C', '', '50.00', '60.00', 2, 'Campuran', NULL, 'Berat', '2025-06-25 08:56:00'),
(4, 6, 'KELAS A', '', '20.00', '30.00', 6, 'L', NULL, 'Berat', '2025-07-12 17:44:25'),
(5, 6, 'KELAS A', '', '40.00', '45.00', 7, 'L', NULL, 'Berat', '2025-07-13 15:11:18'),
(6, 8, 'KELAS A', '', '30.00', '45.00', 11, 'L', NULL, 'Berat', '2025-09-02 15:22:54'),
(7, 8, 'KELAS A', '', '35.00', '40.00', 13, 'L', NULL, 'Berat', '2025-09-02 15:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `competition_contacts`
--

CREATE TABLE `competition_contacts` (
  `id` int NOT NULL,
  `competition_id` int NOT NULL,
  `nama_kontak` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_whatsapp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_contacts`
--

INSERT INTO `competition_contacts` (`id`, `competition_id`, `nama_kontak`, `nomor_whatsapp`, `jabatan`, `created_at`) VALUES
(1, 5, 'Panitia 1', '081234567890', 'Ketua Panitia', '2025-07-12 17:11:18'),
(2, 5, 'Panitia 2', '081298765432', 'Sekretaris', '2025-07-12 17:11:18'),
(4, 2, 'Panitia Jawa Barat', '08144555666', 'Koordinator', '2025-07-12 17:11:18'),
(5, 6, 'NAUFAL', '089123456789', 'ADMIN', '2025-07-12 17:11:39'),
(6, 6, 'ANISA', '089123456780', 'ADMIN', '2025-07-12 17:12:07'),
(7, 8, 'acong', '0891234567', 'PANITIA PELAKSANA', '2025-09-02 15:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `competition_documents`
--

CREATE TABLE `competition_documents` (
  `id` int NOT NULL,
  `competition_id` int NOT NULL,
  `nama_dokumen` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_documents`
--

INSERT INTO `competition_documents` (`id`, `competition_id`, `nama_dokumen`, `file_path`, `created_at`) VALUES
(1, 6, 'PRPOSAL', '1752340446_687297de14b27.pdf', '2025-07-12 17:14:06'),
(2, 8, 'PROPOSAL TTMC', '1756826414_68b70b2e33038.pdf', '2025-09-02 15:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `competition_payment_methods`
--

CREATE TABLE `competition_payment_methods` (
  `id` int NOT NULL,
  `competition_id` int NOT NULL,
  `payment_method_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_payment_methods`
--

INSERT INTO `competition_payment_methods` (`id`, `competition_id`, `payment_method_id`, `created_at`) VALUES
(1, 5, 1, '2025-07-12 16:54:20'),
(2, 5, 2, '2025-07-12 16:54:20'),
(4, 2, 4, '2025-07-12 16:54:20'),
(5, 3, 1, '2025-07-12 16:54:20'),
(6, 4, 2, '2025-07-12 16:54:20'),
(10, 6, 3, '2025-07-12 17:00:58'),
(14, 4, 5, '2025-09-06 00:44:11'),
(15, 6, 5, '2025-09-06 00:44:11');

-- --------------------------------------------------------

--
-- Table structure for table `competition_types`
--

CREATE TABLE `competition_types` (
  `id` int NOT NULL,
  `competition_id` int NOT NULL,
  `nama_kompetisi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P','Campuran') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Campuran',
  `biaya_pendaftaran` decimal(10,2) DEFAULT '0.00',
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_types`
--

INSERT INTO `competition_types` (`id`, `competition_id`, `nama_kompetisi`, `jenis_kelamin`, `biaya_pendaftaran`, `deskripsi`, `created_at`) VALUES
(1, 6, 'SENI TUNGGAL TANGAN KOSONG', 'Campuran', '300000.00', '', '2025-07-12 17:18:35'),
(2, 6, 'SENI TUNGGAL SENJATA', 'Campuran', '300000.00', '', '2025-07-12 17:18:57'),
(3, 6, 'SENI TUNGGAL IPSI', 'Campuran', '300000.00', '', '2025-07-12 17:19:09'),
(4, 6, 'SENI GANDA', 'Campuran', '600000.00', '', '2025-07-12 17:19:22'),
(5, 6, 'SENI SOLO KREATIF', 'Campuran', '300000.00', '', '2025-07-12 17:19:42'),
(8, 5, 'Tanding Kelas A Putra', 'L', '150000.00', 'Kompetisi tanding untuk kelas berat 50-60 kg kategori Putra', '2025-07-12 17:23:53'),
(9, 5, 'Tanding Kelas A Putri', 'P', '150000.00', 'Kompetisi tanding untuk kelas berat 50-60 kg kategori Putri', '2025-07-12 17:23:53'),
(15, 6, 'BEREGU', 'Campuran', '900000.00', '', '2025-07-12 17:27:53'),
(17, 6, 'TANDING', 'Campuran', '300000.00', '', '2025-07-12 17:36:26'),
(24, 8, 'TUNGGAL', 'L', '300000.00', '', '2025-09-02 15:25:02'),
(25, 8, 'TANDING', 'L', '300000.00', '', '2025-09-02 15:25:16');

-- --------------------------------------------------------

--
-- Table structure for table `daftar_peserta`
--

CREATE TABLE `daftar_peserta` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `tempat_lahir` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_sekolah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `berat_badan` float DEFAULT NULL,
  `tinggi_badan` float DEFAULT NULL,
  `kontingen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kategori_umur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kompetisi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kategori_tanding` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `imported_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_peserta`
--

INSERT INTO `daftar_peserta` (`id`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `tempat_lahir`, `nama_sekolah`, `berat_badan`, `tinggi_badan`, `kontingen`, `kategori_umur`, `jenis_kompetisi`, `kategori_tanding`, `imported_at`) VALUES
(2701, 'Patricia Nababan', 'L', '2011-11-16', 'Yogyakarta', 'PT Marpaung Marbun Tbk', 46.3, 157, 'PT Marpaung Marbun Tbk - Yogyakarta', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2702, 'Ifa Lailasari, M.TI.', 'P', '2020-03-11', 'Ternate', 'Perum Situmorang Sudiati Tbk', 47.5, 122, 'Perum Situmorang Sudiati Tbk - Ternate', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2703, 'Ika Pangestu, S.Kom', 'P', '2019-09-08', 'Bandung', 'UD Tamba (Persero) Tbk', 35.9, 148, 'UD Tamba (Persero) Tbk - Bandung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2704, 'Hendri Dongoran', 'L', '2015-11-28', 'Bima', 'Perum Prasetyo Tbk', 30.5, 152, 'Perum Prasetyo Tbk - Bima', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2705, 'Icha Handayani', 'L', '2017-09-18', 'Banjarmasin', 'CV Wulandari', 78.7, 130, 'CV Wulandari - Banjarmasin', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2706, 'Darmanto Saptono', 'P', '2019-09-25', 'Bima', 'CV Firgantoro Mayasari', 50.5, 160, 'CV Firgantoro Mayasari - Bima', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2707, 'Saiful Lailasari', 'P', '1996-08-30', 'Banjar', 'PT Prasasta', 37.1, 129, 'PT Prasasta - Banjar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2708, 'drg. Uli Wahyuni', 'P', '2016-02-14', 'Samarinda', 'PT Najmudin', 32, 141, 'PT Najmudin - Samarinda', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2709, 'Elisa Hardiansyah', 'P', '1999-08-10', 'Samarinda', 'CV Megantara (Persero) Tbk', 34.2, 135, 'CV Megantara (Persero) Tbk - Samarinda', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2710, 'Ir. Pranawa Siregar', 'L', '2020-04-29', 'Gorontalo', 'PD Wahyudin', 72.7, 138, 'PD Wahyudin - Gorontalo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2711, 'Hardana Simbolon', 'P', '2009-06-14', 'Bengkulu', 'PT Hutasoit Handayani', 88.3, 171, 'PT Hutasoit Handayani - Bengkulu', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2712, 'Nilam Lazuardi', 'P', '2009-08-03', 'Batu', 'PT Nashiruddin Tbk', 52.9, 140, 'PT Nashiruddin Tbk - Batu', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2713, 'dr. Harsaya Hidayanto, S.H.', 'L', '2019-11-16', 'Pontianak', 'CV Situmorang (Persero) Tbk', 31.1, 171, 'CV Situmorang (Persero) Tbk - Pontianak', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2714, 'Hj. Jessica Damanik, M.TI.', 'P', '2018-01-12', 'Tasikmalaya', 'UD Nasyiah Haryanto Tbk', 65.5, 152, 'UD Nasyiah Haryanto Tbk - Tasikmalaya', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2715, 'Kala Kusumo', 'P', '2010-07-19', 'Ternate', 'PT Lestari Susanti Tbk', 35.7, 127, 'PT Lestari Susanti Tbk - Ternate', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2716, 'Lidya Firgantoro', 'P', '2019-07-28', 'Batu', 'Perum Nababan (Persero) Tbk', 31.1, 160, 'Perum Nababan (Persero) Tbk - Batu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2717, 'Dono Pangestu', 'P', '2020-01-31', 'Parepare', 'UD Mayasari Saragih (Persero) Tbk', 37.6, 136, 'UD Mayasari Saragih (Persero) Tbk - Parepare', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2718, 'Aurora Siregar', 'L', '2019-10-14', 'Blitar', 'PT Suwarno', 44.3, 146, 'PT Suwarno - Blitar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2719, 'Ika Mahendra', 'P', '2003-06-27', 'Tegal', 'UD Laksmiwati', 36.6, 125, 'UD Laksmiwati - Tegal', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2720, 'Dr. Martani Marbun', 'L', '2017-11-09', 'Kota Administrasi Jakarta Barat', 'PT Samosir Usada Tbk', 58.6, 145, 'PT Samosir Usada Tbk - Kota Administrasi Jakarta Barat', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2721, 'Wani Prakasa', 'L', '2018-06-02', 'Balikpapan', 'PD Safitri Napitupulu (Persero) Tbk', 39.7, 150, 'PD Safitri Napitupulu (Persero) Tbk - Balikpapan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2722, 'Rini Wijaya, M.Kom.', 'P', '2017-12-18', 'Tasikmalaya', 'UD Sihombing (Persero) Tbk', 87.8, 152, 'UD Sihombing (Persero) Tbk - Tasikmalaya', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2723, 'Fitria Mahendra', 'L', '2008-11-21', 'Kotamobagu', 'PT Andriani (Persero) Tbk', 36.1, 184, 'PT Andriani (Persero) Tbk - Kotamobagu', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2724, 'R. Michelle Sitompul, M.Pd', 'P', '2004-04-25', 'Madiun', 'UD Sinaga Riyanti Tbk', 81.5, 181, 'UD Sinaga Riyanti Tbk - Madiun', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2725, 'Mahdi Mansur', 'P', '2017-07-26', 'Pasuruan', 'Perum Saputra', 58, 165, 'Perum Saputra - Pasuruan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2726, 'Putri Widodo, S.Psi', 'P', '2020-06-19', 'Banjar', 'PT Wasita Tamba Tbk', 48.9, 183, 'PT Wasita Tamba Tbk - Banjar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2727, 'R. Gasti Widodo', 'P', '2015-12-27', 'Bandung', 'UD Kuswoyo', 57.7, 146, 'UD Kuswoyo - Bandung', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2728, 'Jail Prasetya', 'L', '2013-07-12', 'Cilegon', 'CV Zulkarnain (Persero) Tbk', 67.2, 158, 'CV Zulkarnain (Persero) Tbk - Cilegon', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2729, 'Dt. Caturangga Lailasari, S.Psi', 'P', '2020-07-04', 'Sorong', 'CV Widodo Damanik', 73.7, 134, 'CV Widodo Damanik - Sorong', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2730, 'Jaga Habibi', 'L', '2019-09-05', 'Probolinggo', 'PT Sudiati', 31.4, 146, 'PT Sudiati - Probolinggo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2731, 'dr. Gaiman Simbolon, M.Ak', 'P', '2018-05-17', 'Bau-Bau', 'PT Maulana', 75.5, 177, 'PT Maulana - Bau-Bau', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2732, 'drg. Nabila Nasyiah, S.Pt', 'L', '2014-04-22', 'Metro', 'Perum Wijaya Hasanah Tbk', 45.5, 177, 'Perum Wijaya Hasanah Tbk - Metro', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2733, 'Bella Siregar', 'P', '2017-11-16', 'Bontang', 'UD Nababan Tarihoran (Persero) Tbk', 55.4, 142, 'UD Nababan Tarihoran (Persero) Tbk - Bontang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2734, 'Kasiran Latupono', 'L', '2009-11-21', 'Pangkalpinang', 'CV Napitupulu Maulana Tbk', 71.1, 183, 'CV Napitupulu Maulana Tbk - Pangkalpinang', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2735, 'Arsipatra Samosir', 'L', '2017-08-17', 'Kediri', 'PD Prakasa Prasetyo (Persero) Tbk', 62.1, 178, 'PD Prakasa Prasetyo (Persero) Tbk - Kediri', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2736, 'Kamila Nurdiyanti', 'P', '2011-01-09', 'Tegal', 'PT Wacana', 32.3, 153, 'PT Wacana - Tegal', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2737, 'Nadine Firgantoro', 'P', '2012-06-27', 'Dumai', 'UD Tamba', 77.8, 149, 'UD Tamba - Dumai', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2738, 'Kawaca Wastuti', 'L', '2017-10-03', 'Tangerang', 'PT Zulkarnain', 64.9, 127, 'PT Zulkarnain - Tangerang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2739, 'Ika Mustofa', 'L', '2016-03-01', 'Denpasar', 'UD Narpati Dabukke', 89, 151, 'UD Narpati Dabukke - Denpasar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2740, 'Hj. Rahmi Laksmiwati', 'L', '2018-05-20', 'Sibolga', 'PD Palastri Tbk', 50.6, 176, 'PD Palastri Tbk - Sibolga', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2741, 'Cecep Habibi, M.TI.', 'L', '1995-09-02', 'Batu', 'PD Novitasari (Persero) Tbk', 56.2, 138, 'PD Novitasari (Persero) Tbk - Batu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2742, 'Safina Rahayu', 'L', '2013-09-14', 'Pontianak', 'PD Latupono Tbk', 30.4, 184, 'PD Latupono Tbk - Pontianak', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2743, 'Qori Hartati', 'L', '2011-02-17', 'Pontianak', 'PT Uyainah Hardiansyah (Persero) Tbk', 30.4, 165, 'PT Uyainah Hardiansyah (Persero) Tbk - Pontianak', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2744, 'Dr. Mala Halimah', 'L', '2009-10-14', 'Sungai Penuh', 'CV Waluyo Tbk', 42.5, 133, 'CV Waluyo Tbk - Sungai Penuh', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2745, 'Nadia Prayoga, M.TI.', 'P', '2012-01-30', 'Pangkalpinang', 'PT Saragih Simbolon', 54.1, 168, 'PT Saragih Simbolon - Pangkalpinang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2746, 'Wulan Waluyo', 'L', '2016-05-23', 'Bandung', 'PT Riyanti', 80.8, 142, 'PT Riyanti - Bandung', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2747, 'Hafshah Yuniar', 'P', '2018-01-07', 'Makassar', 'Perum Sihotang', 41.5, 147, 'Perum Sihotang - Makassar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2748, 'R.A. Tira Najmudin', 'L', '2001-10-30', 'Mataram', 'CV Prasetyo Tamba', 64.2, 122, 'CV Prasetyo Tamba - Mataram', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2749, 'R.A. Tantri Lazuardi, M.Pd', 'L', '2005-07-24', 'Palu', 'Perum Mardhiyah', 83.1, 127, 'Perum Mardhiyah - Palu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2750, 'Daruna Wibisono', 'P', '2017-12-05', 'Parepare', 'PD Wijaya Hassanah', 73.1, 146, 'PD Wijaya Hassanah - Parepare', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2751, 'Ikin Sihotang', 'L', '2016-04-29', 'Payakumbuh', 'PD Wibisono', 68.7, 133, 'PD Wibisono - Payakumbuh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2752, 'Drs. Emas Januar, S.Pt', 'L', '2004-12-06', 'Mojokerto', 'Perum Latupono Hartati', 71.6, 166, 'Perum Latupono Hartati - Mojokerto', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2753, 'Kasiyah Rajata', 'L', '2018-04-10', 'Sukabumi', 'PT Yulianti', 40, 127, 'PT Yulianti - Sukabumi', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2754, 'Banara Zulaika', 'L', '2020-02-10', 'Medan', 'PD Lazuardi Manullang', 79, 178, 'PD Lazuardi Manullang - Medan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2755, 'Jaswadi Rahimah', 'L', '2002-10-22', 'Kediri', 'PT Laksmiwati', 85.8, 172, 'PT Laksmiwati - Kediri', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2756, 'Rahayu Rajasa', 'P', '2006-11-05', 'Purwokerto', 'UD Maryati Andriani (Persero) Tbk', 39.6, 171, 'UD Maryati Andriani (Persero) Tbk - Purwokerto', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2757, 'Drs. Pia Suryatmi', 'L', '2017-12-17', 'Bandung', 'Perum Suryatmi Damanik Tbk', 68.3, 189, 'Perum Suryatmi Damanik Tbk - Bandung', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2758, 'Balijan Tampubolon', 'P', '2001-03-21', 'Samarinda', 'PT Gunarto', 47.5, 124, 'PT Gunarto - Samarinda', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2759, 'Karimah Suartini', 'P', '2013-11-03', 'Langsa', 'CV Lailasari', 61.6, 159, 'CV Lailasari - Langsa', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2760, 'Drs. Asirwada Lazuardi, S.H.', 'L', '2019-12-28', 'Mataram', 'UD Hardiansyah', 84, 125, 'UD Hardiansyah - Mataram', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2761, 'Hasan Hassanah', 'L', '2014-02-01', 'Dumai', 'UD Wacana Puspasari', 62.3, 158, 'UD Wacana Puspasari - Dumai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2762, 'Bambang Rahayu', 'L', '2016-05-17', 'Ambon', 'PD Zulkarnain Suartini', 43, 162, 'PD Zulkarnain Suartini - Ambon', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2763, 'Ganda Dongoran', 'L', '2018-06-02', 'Banda Aceh', 'Perum Wijayanti Wijaya Tbk', 83.9, 134, 'Perum Wijayanti Wijaya Tbk - Banda Aceh', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2764, 'Anita Ardianto, S.Gz', 'P', '2015-03-20', 'Sawahlunto', 'Perum Nugroho (Persero) Tbk', 78.8, 127, 'Perum Nugroho (Persero) Tbk - Sawahlunto', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2765, 'Tgk. Slamet Mardhiyah, S.Pd', 'L', '2019-12-28', 'Sabang', 'Perum Handayani Hardiansyah', 54.3, 140, 'Perum Handayani Hardiansyah - Sabang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2766, 'Kayla Tampubolon', 'L', '2020-06-18', 'Sukabumi', 'PD Marpaung Suryatmi (Persero) Tbk', 89, 169, 'PD Marpaung Suryatmi (Persero) Tbk - Sukabumi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2767, 'Cawuk Permata', 'L', '2009-08-29', 'Banda Aceh', 'CV Mustofa (Persero) Tbk', 82, 182, 'CV Mustofa (Persero) Tbk - Banda Aceh', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2768, 'Iriana Nugroho', 'L', '2018-07-05', 'Pariaman', 'Perum Dongoran Wibowo Tbk', 51.7, 145, 'Perum Dongoran Wibowo Tbk - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2769, 'Dr. Vanesa Kusumo, S.Pt', 'L', '2018-06-05', 'Blitar', 'Perum Budiman Wahyuni Tbk', 30.4, 144, 'Perum Budiman Wahyuni Tbk - Blitar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2770, 'R.M. Olga Wastuti', 'L', '1996-08-20', 'Pekalongan', 'PT Oktaviani', 33.3, 176, 'PT Oktaviani - Pekalongan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2771, 'Edi Mardhiyah', 'L', '2012-02-12', 'Magelang', 'CV Puspita Palastri', 64.8, 189, 'CV Puspita Palastri - Magelang', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2772, 'Azalea Hasanah, S.I.Kom', 'P', '2009-07-25', 'Tasikmalaya', 'Perum Purnawati Prasetya Tbk', 33.1, 125, 'Perum Purnawati Prasetya Tbk - Tasikmalaya', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2773, 'Daliman Jailani', 'L', '1999-02-16', 'Bengkulu', 'Perum Dongoran Kurniawan (Persero) Tbk', 63.9, 140, 'Perum Dongoran Kurniawan (Persero) Tbk - Bengkulu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2774, 'Karsa Samosir', 'L', '2020-02-23', 'Depok', 'UD Namaga Prasasta Tbk', 69.7, 121, 'UD Namaga Prasasta Tbk - Depok', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2775, 'Endra Pertiwi', 'P', '1999-07-27', 'Kotamobagu', 'PD Kuswandari Nasyidah (Persero) Tbk', 65.7, 132, 'PD Kuswandari Nasyidah (Persero) Tbk - Kotamobagu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2776, 'R. Laras Hutagalung', 'P', '2018-02-23', 'Solok', 'UD Narpati (Persero) Tbk', 69.4, 156, 'UD Narpati (Persero) Tbk - Solok', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2777, 'Galih Kurniawan', 'L', '2018-01-13', 'Gorontalo', 'Perum Nainggolan', 81.3, 154, 'Perum Nainggolan - Gorontalo', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2778, 'Drs. Kartika Rajata, M.Farm', 'P', '2014-03-22', 'Pangkalpinang', 'Perum Nugroho Waskita', 48.9, 189, 'Perum Nugroho Waskita - Pangkalpinang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2779, 'Kemba Usada, M.M.', 'L', '2016-02-26', 'Tanjungpinang', 'PT Kuswoyo Nugroho (Persero) Tbk', 49.8, 129, 'PT Kuswoyo Nugroho (Persero) Tbk - Tanjungpinang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2780, 'Oni Hutasoit, M.TI.', 'L', '2000-05-06', 'Banda Aceh', 'CV Haryanto', 66.6, 151, 'CV Haryanto - Banda Aceh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2781, 'Viktor Sirait', 'P', '2010-09-12', 'Jayapura', 'UD Hardiansyah', 89.3, 165, 'UD Hardiansyah - Jayapura', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2782, 'Among Mustofa', 'L', '1999-11-16', 'Bau-Bau', 'Perum Andriani Firmansyah (Persero) Tbk', 84, 188, 'Perum Andriani Firmansyah (Persero) Tbk - Bau-Bau', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2783, 'Cici Napitupulu', 'L', '2015-03-13', 'Tanjungpinang', 'PT Usada Ramadan Tbk', 82.8, 140, 'PT Usada Ramadan Tbk - Tanjungpinang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2784, 'Surya Pratama', 'P', '2004-12-14', 'Payakumbuh', 'PT Sirait Tbk', 58.5, 137, 'PT Sirait Tbk - Payakumbuh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2785, 'Ir. Bahuraksa Hastuti', 'L', '2015-11-04', 'Yogyakarta', 'UD Utama Andriani (Persero) Tbk', 44.9, 139, 'UD Utama Andriani (Persero) Tbk - Yogyakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2786, 'Aris Usamah', 'L', '2019-12-08', 'Batu', 'PD Winarsih Situmorang Tbk', 76.7, 124, 'PD Winarsih Situmorang Tbk - Batu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2787, 'Tami Laksita', 'P', '2020-04-05', 'Metro', 'Perum Yolanda Susanti Tbk', 79.8, 127, 'Perum Yolanda Susanti Tbk - Metro', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2788, 'Dt. Kariman Wastuti', 'P', '2019-12-01', 'Langsa', 'CV Ramadan Halim (Persero) Tbk', 71.1, 140, 'CV Ramadan Halim (Persero) Tbk - Langsa', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2789, 'Zaenab Anggriawan', 'L', '2017-10-23', 'Semarang', 'CV Gunawan Tbk', 49.6, 184, 'CV Gunawan Tbk - Semarang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2790, 'Nrima Hartati', 'L', '2010-06-22', 'Bontang', 'UD Prasetya', 76.2, 144, 'UD Prasetya - Bontang', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2791, 'Sutan Aditya Tarihoran', 'L', '2013-12-05', 'Padangpanjang', 'PT Purnawati Hidayat', 76.9, 141, 'PT Purnawati Hidayat - Padangpanjang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2792, 'Jamil Saptono', 'P', '2017-08-13', 'Pariaman', 'PT Safitri', 37.3, 137, 'PT Safitri - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2793, 'Drs. Prabawa Hakim', 'P', '1999-12-02', 'Balikpapan', 'PD Siregar Kusmawati', 49, 163, 'PD Siregar Kusmawati - Balikpapan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2794, 'R. Irfan Hariyah, S.Psi', 'P', '2015-02-03', 'Parepare', 'UD Winarno', 88.2, 168, 'UD Winarno - Parepare', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2795, 'Drs. Juli Thamrin', 'P', '2020-04-29', 'Pekalongan', 'Perum Halimah', 56.2, 163, 'Perum Halimah - Pekalongan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2796, 'Yoga Nasyidah', 'L', '2020-03-01', 'Palopo', 'PT Iswahyudi', 31.1, 170, 'PT Iswahyudi - Palopo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2797, 'Queen Dabukke', 'L', '2010-07-22', 'Magelang', 'PT Sirait Tbk', 36.9, 133, 'PT Sirait Tbk - Magelang', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2798, 'Prabawa Prasetyo', 'P', '2018-05-18', 'Sorong', 'UD Sihotang', 78.6, 167, 'UD Sihotang - Sorong', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2799, 'drg. Karen Padmasari', 'P', '2002-03-25', 'Malang', 'CV Widodo Tbk', 62.3, 172, 'CV Widodo Tbk - Malang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2800, 'Nurul Wulandari', 'L', '2014-08-17', 'Surakarta', 'CV Mandasari Sitompul', 37.8, 124, 'CV Mandasari Sitompul - Surakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2801, 'Leo Rahimah', 'L', '2002-05-13', 'Langsa', 'PT Usada Nurdiyanti Tbk', 51.3, 128, 'PT Usada Nurdiyanti Tbk - Langsa', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2802, 'Ir. Adika Puspasari, S.I.Kom', 'L', '2014-10-02', 'Bau-Bau', 'Perum Melani Andriani', 86, 135, 'Perum Melani Andriani - Bau-Bau', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2803, 'Dr. Latika Susanti, S.E.', 'P', '2018-01-16', 'Probolinggo', 'UD Uwais Tbk', 34.2, 174, 'UD Uwais Tbk - Probolinggo', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2804, 'Unggul Ramadan, M.Kom.', 'P', '2020-04-05', 'Tidore Kepulauan', 'PT Melani Pratiwi Tbk', 88.5, 176, 'PT Melani Pratiwi Tbk - Tidore Kepulauan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2805, 'Tina Sudiati', 'L', '2011-01-24', 'Prabumulih', 'CV Hariyah Habibi', 70.2, 183, 'CV Hariyah Habibi - Prabumulih', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2806, 'Calista Dabukke', 'P', '2017-10-11', 'Kota Administrasi Jakarta Timur', 'PT Sihombing Mayasari Tbk', 56.8, 181, 'PT Sihombing Mayasari Tbk - Kota Administrasi Jakarta Timur', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2807, 'Argono Winarsih, S.T.', 'L', '1999-07-20', 'Kendari', 'UD Hidayat', 51.8, 180, 'UD Hidayat - Kendari', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2808, 'Drs. Irma Wibisono, S.T.', 'L', '2011-06-07', 'Denpasar', 'UD Pudjiastuti Dabukke (Persero) Tbk', 77.8, 148, 'UD Pudjiastuti Dabukke (Persero) Tbk - Denpasar', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2809, 'Kairav Saptono', 'L', '2009-06-05', 'Kota Administrasi Jakarta Selatan', 'CV Narpati Pradipta', 39.3, 170, 'CV Narpati Pradipta - Kota Administrasi Jakarta Selatan', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2810, 'Bakianto Zulkarnain', 'L', '2015-03-08', 'Salatiga', 'CV Pradipta Tbk', 33.3, 135, 'CV Pradipta Tbk - Salatiga', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2811, 'Rika Sirait, S.IP', 'P', '2015-05-10', 'Ambon', 'Perum Mustofa Putra Tbk', 57.4, 143, 'Perum Mustofa Putra Tbk - Ambon', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2812, 'Yunita Kuswoyo', 'P', '2011-10-09', 'Kota Administrasi Jakarta Timur', 'CV Mandasari Lestari', 73.3, 135, 'CV Mandasari Lestari - Kota Administrasi Jakarta Timur', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2813, 'Lasmanto Anggriawan', 'L', '2017-08-29', 'Sabang', 'CV Sirait Hardiansyah (Persero) Tbk', 44.2, 123, 'CV Sirait Hardiansyah (Persero) Tbk - Sabang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2814, 'Balangga Setiawan', 'L', '2012-03-28', 'Pangkalpinang', 'CV Suryatmi', 67, 147, 'CV Suryatmi - Pangkalpinang', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2815, 'Bahuwarna Suryatmi', 'P', '2005-03-31', 'Meulaboh', 'Perum Palastri Puspita Tbk', 36.2, 136, 'Perum Palastri Puspita Tbk - Meulaboh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2816, 'Naradi Marbun', 'P', '2014-09-24', 'Tangerang Selatan', 'Perum Habibi Firmansyah', 69.2, 171, 'Perum Habibi Firmansyah - Tangerang Selatan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2817, 'Salimah Hutasoit', 'P', '2019-07-28', 'Bau-Bau', 'PD Hidayat (Persero) Tbk', 78.3, 180, 'PD Hidayat (Persero) Tbk - Bau-Bau', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2818, 'Marsudi Puspita', 'P', '2020-02-01', 'Jayapura', 'PD Winarno Mahendra', 79.3, 147, 'PD Winarno Mahendra - Jayapura', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2819, 'Dt. Purwadi Prasasta, M.TI.', 'P', '2006-06-29', 'Kendari', 'Perum Hutagalung Mayasari', 77.5, 134, 'Perum Hutagalung Mayasari - Kendari', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2820, 'Citra Setiawan', 'P', '2017-08-26', 'Bau-Bau', 'Perum Permata', 61, 148, 'Perum Permata - Bau-Bau', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2821, 'R.M. Cagak Yuliarti, M.Pd', 'P', '2019-09-12', 'Banjarbaru', 'PT Usamah Namaga (Persero) Tbk', 84.8, 164, 'PT Usamah Namaga (Persero) Tbk - Banjarbaru', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2822, 'Raina Melani', 'L', '2020-03-10', 'Yogyakarta', 'PT Astuti Firmansyah', 60.7, 172, 'PT Astuti Firmansyah - Yogyakarta', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2823, 'Ir. Maras Suartini', 'L', '2007-01-04', 'Bekasi', 'Perum Lazuardi Sitompul (Persero) Tbk', 84.2, 149, 'Perum Lazuardi Sitompul (Persero) Tbk - Bekasi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2824, 'Jasmani Sihotang', 'P', '2011-11-05', 'Yogyakarta', 'UD Firgantoro (Persero) Tbk', 65.9, 152, 'UD Firgantoro (Persero) Tbk - Yogyakarta', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2825, 'Ir. Bajragin Hutagalung, S.T.', 'P', '2019-07-15', 'Sibolga', 'PT Suwarno (Persero) Tbk', 83.8, 165, 'PT Suwarno (Persero) Tbk - Sibolga', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2826, 'Ida Lazuardi', 'L', '2016-06-05', 'Prabumulih', 'PD Mustofa', 84.4, 158, 'PD Mustofa - Prabumulih', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2827, 'Cut Michelle Pangestu', 'P', '2017-07-26', 'Sorong', 'UD Marbun Hardiansyah (Persero) Tbk', 83, 148, 'UD Marbun Hardiansyah (Persero) Tbk - Sorong', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2828, 'Dewi Maulana', 'P', '1997-05-22', 'Mataram', 'PT Kuswandari Pertiwi (Persero) Tbk', 60.7, 124, 'PT Kuswandari Pertiwi (Persero) Tbk - Mataram', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2829, 'Calista Safitri', 'L', '2020-05-16', 'Madiun', 'PD Maryati', 72.8, 182, 'PD Maryati - Madiun', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2830, 'Tgk. Okta Utami, M.TI.', 'L', '2015-01-04', 'Banjar', 'PD Nasyiah Prasasta', 65.8, 143, 'PD Nasyiah Prasasta - Banjar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2831, 'Ana Usamah', 'L', '2015-02-04', 'Kupang', 'PT Napitupulu Hutasoit', 71.5, 125, 'PT Napitupulu Hutasoit - Kupang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2832, 'Cinta Pangestu', 'P', '2019-08-01', 'Mojokerto', 'UD Hastuti Maulana (Persero) Tbk', 86.6, 126, 'UD Hastuti Maulana (Persero) Tbk - Mojokerto', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2833, 'Malika Nasyidah, M.Pd', 'P', '2018-02-19', 'Lhokseumawe', 'CV Aryani', 78.5, 165, 'CV Aryani - Lhokseumawe', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2834, 'Queen Siregar', 'L', '2017-12-03', 'Gorontalo', 'UD Hariyah', 66.3, 137, 'UD Hariyah - Gorontalo', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2835, 'Dewi Hartati', 'L', '2020-03-27', 'Salatiga', 'PD Dabukke (Persero) Tbk', 46.4, 138, 'PD Dabukke (Persero) Tbk - Salatiga', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2836, 'Kusuma Utama', 'L', '2009-04-30', 'Prabumulih', 'UD Dabukke Nasyidah', 64.5, 154, 'UD Dabukke Nasyidah - Prabumulih', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2837, 'Dt. Anggabaya Maheswara', 'P', '2010-01-05', 'Binjai', 'PT Zulkarnain Tbk', 63.6, 155, 'PT Zulkarnain Tbk - Binjai', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2838, 'H. Eka Tarihoran', 'L', '2017-11-08', 'Metro', 'PD Sitompul Pratama Tbk', 39.4, 184, 'PD Sitompul Pratama Tbk - Metro', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2839, 'Jindra Haryanto', 'P', '2018-04-05', 'Tidore Kepulauan', 'CV Winarno Melani', 54.4, 148, 'CV Winarno Melani - Tidore Kepulauan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2840, 'Ifa Purnawati', 'P', '2007-03-20', 'Sukabumi', 'PT Usamah', 41, 137, 'PT Usamah - Sukabumi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2841, 'Eka Novitasari', 'P', '2020-04-06', 'Ambon', 'PT Laksmiwati Tbk', 83, 174, 'PT Laksmiwati Tbk - Ambon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2842, 'Daliono Santoso', 'P', '2017-12-04', 'Balikpapan', 'CV Santoso Novitasari (Persero) Tbk', 58.9, 139, 'CV Santoso Novitasari (Persero) Tbk - Balikpapan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2843, 'dr. Dono Prakasa, S.Psi', 'P', '2020-05-25', 'Lubuklinggau', 'PT Tamba', 76, 136, 'PT Tamba - Lubuklinggau', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2844, 'Puti Nadine Setiawan, S.I.Kom', 'L', '2009-09-05', 'Bima', 'PT Ramadan Hardiansyah (Persero) Tbk', 61.5, 187, 'PT Ramadan Hardiansyah (Persero) Tbk - Bima', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2845, 'Lalita Fujiati', 'L', '2015-08-27', 'Bengkulu', 'PT Permadi', 70.3, 128, 'PT Permadi - Bengkulu', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2846, 'Bakidin Simbolon', 'L', '2009-04-21', 'Yogyakarta', 'PD Puspasari Mulyani Tbk', 56.4, 176, 'PD Puspasari Mulyani Tbk - Yogyakarta', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2847, 'Yusuf Mandasari', 'P', '2010-06-21', 'Kota Administrasi Jakarta Utara', 'PT Saputra Waluyo Tbk', 67.5, 144, 'PT Saputra Waluyo Tbk - Kota Administrasi Jakarta Utara', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2848, 'Titin Prabowo', 'L', '2018-03-29', 'Purwokerto', 'PD Waskita Riyanti (Persero) Tbk', 89.6, 120, 'PD Waskita Riyanti (Persero) Tbk - Purwokerto', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2849, 'Wisnu Mulyani', 'L', '2020-05-03', 'Palembang', 'UD Anggraini (Persero) Tbk', 34.9, 185, 'UD Anggraini (Persero) Tbk - Palembang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2850, 'Ir. Puspa Hariyah', 'P', '2010-10-05', 'Samarinda', 'CV Melani Tbk', 59.7, 154, 'CV Melani Tbk - Samarinda', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2851, 'Utama Pratiwi', 'L', '2017-09-12', 'Tangerang Selatan', 'UD Riyanti Widodo Tbk', 50.2, 120, 'UD Riyanti Widodo Tbk - Tangerang Selatan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2852, 'Usyi Tampubolon', 'P', '2018-05-26', 'Singkawang', 'CV Mustofa Kusmawati', 88.5, 127, 'CV Mustofa Kusmawati - Singkawang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2853, 'Galih Nashiruddin', 'L', '2009-03-19', 'Lubuklinggau', 'UD Sihotang Wahyudin (Persero) Tbk', 63.4, 178, 'UD Sihotang Wahyudin (Persero) Tbk - Lubuklinggau', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2854, 'Dr. Shakila Usada, S.IP', 'P', '2016-01-07', 'Palembang', 'CV Waskita Handayani', 60.6, 149, 'CV Waskita Handayani - Palembang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2855, 'Budi Laksita, S.Ked', 'L', '2020-03-13', 'Banjarmasin', 'CV Sihotang', 31.6, 190, 'CV Sihotang - Banjarmasin', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2856, 'Puji Sihombing', 'P', '2010-12-12', 'Kota Administrasi Jakarta Pusat', 'PD Rahmawati Adriansyah', 37.4, 189, 'PD Rahmawati Adriansyah - Kota Administrasi Jakarta Pusat', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2857, 'Yulia Simbolon', 'P', '2018-01-04', 'Mataram', 'UD Haryanti', 55.3, 187, 'UD Haryanti - Mataram', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2858, 'Ana Pradipta', 'L', '2019-08-17', 'Tangerang', 'Perum Puspita', 31.7, 167, 'Perum Puspita - Tangerang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2859, 'Hj. Hasna Lestari, S.E.I', 'L', '2020-05-31', 'Ternate', 'CV Melani Tbk', 77.3, 134, 'CV Melani Tbk - Ternate', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2860, 'Samiah Pangestu', 'P', '2019-08-13', 'Pekanbaru', 'CV Nasyiah Handayani', 52, 141, 'CV Nasyiah Handayani - Pekanbaru', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2861, 'Janet Wacana', 'P', '2014-11-06', 'Bontang', 'UD Sitompul Melani Tbk', 64.6, 184, 'UD Sitompul Melani Tbk - Bontang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2862, 'Farhunnisa Agustina, S.IP', 'P', '2002-03-05', 'Prabumulih', 'PT Winarno', 47.3, 190, 'PT Winarno - Prabumulih', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2863, 'Dt. Laswi Adriansyah', 'P', '2019-12-05', 'Metro', 'Perum Wibisono', 73.6, 125, 'Perum Wibisono - Metro', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2864, 'Kalim Ramadan, M.Farm', 'L', '2020-04-24', 'Batu', 'PT Namaga', 31.4, 163, 'PT Namaga - Batu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2865, 'Umi Mayasari, M.Pd', 'L', '2005-12-19', 'Madiun', 'PT Aryani Tbk', 61.3, 157, 'PT Aryani Tbk - Madiun', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2866, 'drg. Victoria Wacana, M.Pd', 'P', '2019-11-08', 'Tual', 'UD Namaga', 44, 178, 'UD Namaga - Tual', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2867, 'Sutan Gandi Mandasari', 'P', '2016-01-11', 'Denpasar', 'PD Kuswoyo (Persero) Tbk', 75, 155, 'PD Kuswoyo (Persero) Tbk - Denpasar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2868, 'Joko Mulyani', 'L', '2006-09-24', 'Kota Administrasi Jakarta Timur', 'PD Puspasari Tampubolon', 60.3, 130, 'PD Puspasari Tampubolon - Kota Administrasi Jakarta Timur', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2869, 'Laswi Tarihoran', 'P', '2016-06-27', 'Banjarbaru', 'PD Suryono Thamrin', 34.8, 185, 'PD Suryono Thamrin - Banjarbaru', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2870, 'dr. Cahyo Wibowo, S.E.', 'P', '2004-02-11', 'Parepare', 'UD Haryanti', 52.6, 130, 'UD Haryanti - Parepare', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2871, 'Puti Patricia Sirait', 'P', '1998-09-29', 'Banjar', 'Perum Maulana Dabukke (Persero) Tbk', 50.7, 120, 'Perum Maulana Dabukke (Persero) Tbk - Banjar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2872, 'Tri Mulyani', 'P', '2011-12-31', 'Prabumulih', 'PT Anggriawan', 50.2, 126, 'PT Anggriawan - Prabumulih', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2873, 'Prabu Uwais', 'L', '2015-08-28', 'Tasikmalaya', 'PD Waluyo Prastuti (Persero) Tbk', 32.2, 125, 'PD Waluyo Prastuti (Persero) Tbk - Tasikmalaya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2874, 'Humaira Pratama', 'L', '2017-07-25', 'Tidore Kepulauan', 'PD Irawan (Persero) Tbk', 78.6, 183, 'PD Irawan (Persero) Tbk - Tidore Kepulauan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2875, 'Bakijan Maryati', 'L', '2019-11-13', 'Surakarta', 'CV Gunarto (Persero) Tbk', 75.1, 158, 'CV Gunarto (Persero) Tbk - Surakarta', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2876, 'Elon Simanjuntak, S.Sos', 'P', '2020-05-05', 'Pekanbaru', 'CV Nababan Tbk', 31.4, 161, 'CV Nababan Tbk - Pekanbaru', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2877, 'Aurora Suryatmi', 'P', '2019-12-14', 'Payakumbuh', 'Perum Wibowo', 78.3, 168, 'Perum Wibowo - Payakumbuh', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2878, 'Hasta Prastuti', 'L', '2016-02-06', 'Banda Aceh', 'PD Wahyuni Nuraini', 73.6, 178, 'PD Wahyuni Nuraini - Banda Aceh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2879, 'Unggul Wacana', 'P', '2015-12-19', 'Padang Sidempuan', 'Perum Pangestu Tbk', 40.3, 189, 'Perum Pangestu Tbk - Padang Sidempuan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2880, 'Winda Widiastuti', 'P', '2017-09-29', 'Lubuklinggau', 'Perum Waluyo Permadi (Persero) Tbk', 64.9, 148, 'Perum Waluyo Permadi (Persero) Tbk - Lubuklinggau', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2881, 'Harsaya Suryatmi', 'P', '2009-06-19', 'Palembang', 'PD Laksmiwati Tbk', 36.7, 124, 'PD Laksmiwati Tbk - Palembang', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2882, 'Syahrini Hutapea', 'L', '1999-08-20', 'Malang', 'Perum Astuti Nugroho (Persero) Tbk', 33.3, 190, 'Perum Astuti Nugroho (Persero) Tbk - Malang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2883, 'Gandi Halim, S.Ked', 'P', '2008-08-28', 'Meulaboh', 'PD Nashiruddin Puspasari', 68.9, 172, 'PD Nashiruddin Puspasari - Meulaboh', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2884, 'drg. Kamaria Winarsih', 'L', '1997-10-15', 'Kota Administrasi Jakarta Utara', 'CV Narpati Permata', 60.4, 177, 'CV Narpati Permata - Kota Administrasi Jakarta Utara', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2885, 'Dr. Yoga Pertiwi, S.I.Kom', 'L', '2018-01-24', 'Kupang', 'CV Latupono Tbk', 85.1, 186, 'CV Latupono Tbk - Kupang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2886, 'Puti Nurul Prabowo', 'P', '2010-05-04', 'Kota Administrasi Jakarta Barat', 'CV Padmasari', 43.7, 123, 'CV Padmasari - Kota Administrasi Jakarta Barat', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2887, 'Bakiman Winarsih', 'P', '1996-11-03', 'Padang', 'CV Manullang', 73.9, 189, 'CV Manullang - Padang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2888, 'Halima Astuti', 'L', '2018-01-23', 'Kota Administrasi Jakarta Barat', 'Perum Pradana Prasetya Tbk', 73.7, 189, 'Perum Pradana Prasetya Tbk - Kota Administrasi Jakarta Barat', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2889, 'dr. Patricia Tamba, S.E.I', 'L', '2017-09-24', 'Batam', 'UD Yuniar (Persero) Tbk', 56.2, 151, 'UD Yuniar (Persero) Tbk - Batam', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2890, 'Mulya Mandasari', 'P', '2018-02-07', 'Medan', 'Perum Samosir Prastuti', 52.9, 130, 'Perum Samosir Prastuti - Medan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2891, 'Melinda Astuti, S.Pd', 'L', '2019-10-20', 'Sungai Penuh', 'PT Suartini', 71.6, 138, 'PT Suartini - Sungai Penuh', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2892, 'R.A. Iriana Hidayat, S.Kom', 'P', '1999-04-06', 'Tegal', 'PT Nasyiah Tbk', 81.7, 176, 'PT Nasyiah Tbk - Tegal', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2893, 'T. Paiman Tamba', 'P', '2020-06-18', 'Palembang', 'Perum Prakasa Megantara (Persero) Tbk', 45.4, 169, 'Perum Prakasa Megantara (Persero) Tbk - Palembang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2894, 'Sari Irawan', 'P', '2010-07-03', 'Tangerang', 'CV Situmorang Rahimah', 74.5, 162, 'CV Situmorang Rahimah - Tangerang', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2895, 'Kezia Haryanto', 'L', '2015-09-29', 'Langsa', 'CV Thamrin Haryanti Tbk', 79.2, 168, 'CV Thamrin Haryanti Tbk - Langsa', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2896, 'Amalia Pradipta', 'P', '2017-08-14', 'Pariaman', 'PT Andriani Tarihoran Tbk', 33, 151, 'PT Andriani Tarihoran Tbk - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2897, 'Dt. Gaman Yuniar, M.Pd', 'P', '2019-10-26', 'Pangkalpinang', 'PT Wibisono', 34.6, 129, 'PT Wibisono - Pangkalpinang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2898, 'Fitria Halimah', 'P', '2018-06-16', 'Binjai', 'PD Irawan Marpaung (Persero) Tbk', 51.4, 142, 'PD Irawan Marpaung (Persero) Tbk - Binjai', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2899, 'Lembah Padmasari', 'P', '2009-11-24', 'Kota Administrasi Jakarta Pusat', 'Perum Padmasari', 75.7, 147, 'Perum Padmasari - Kota Administrasi Jakarta Pusat', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2900, 'Nyoman Narpati', 'P', '2018-04-27', 'Malang', 'PT Permadi', 49.2, 131, 'PT Permadi - Malang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2901, 'Natalia Melani', 'P', '2009-06-26', 'Bukittinggi', 'Perum Pranowo Wulandari', 33.5, 184, 'Perum Pranowo Wulandari - Bukittinggi', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2902, 'Galak Yuniar', 'L', '2018-02-01', 'Banjar', 'PT Hasanah Maryati Tbk', 31.1, 178, 'PT Hasanah Maryati Tbk - Banjar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2903, 'Ina Mangunsong', 'P', '2018-05-28', 'Tangerang', 'UD Nurdiyanti', 85, 142, 'UD Nurdiyanti - Tangerang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2904, 'Kamila Irawan, S.Pt', 'P', '1998-05-01', 'Jayapura', 'PT Siregar Zulkarnain Tbk', 67.1, 131, 'PT Siregar Zulkarnain Tbk - Jayapura', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2905, 'Hj. Ratih Saputra', 'L', '2011-06-29', 'Jambi', 'Perum Winarsih Wahyudin', 62.3, 156, 'Perum Winarsih Wahyudin - Jambi', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2906, 'Edward Haryanti', 'L', '2015-11-05', 'Tegal', 'PT Situmorang Permadi Tbk', 78.8, 151, 'PT Situmorang Permadi Tbk - Tegal', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2907, 'Estiono Marbun', 'P', '2000-07-21', 'Tanjungbalai', 'CV Yuniar Sirait (Persero) Tbk', 51, 164, 'CV Yuniar Sirait (Persero) Tbk - Tanjungbalai', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2908, 'Cager Pratama', 'P', '2020-03-16', 'Pematangsiantar', 'PT Purnawati Tbk', 72.2, 135, 'PT Purnawati Tbk - Pematangsiantar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2909, 'R. Jamalia Sitompul', 'P', '2013-07-19', 'Subulussalam', 'PT Suryono Pudjiastuti Tbk', 82.6, 173, 'PT Suryono Pudjiastuti Tbk - Subulussalam', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2910, 'R.A. Kamila Hutagalung, S.Farm', 'P', '2014-12-09', 'Bima', 'PT Pertiwi Wahyuni', 53.9, 136, 'PT Pertiwi Wahyuni - Bima', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2911, 'Asmuni Maheswara', 'P', '2019-07-25', 'Jambi', 'PD Widodo', 40.4, 126, 'PD Widodo - Jambi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2912, 'Melinda Nababan', 'P', '2014-01-14', 'Kendari', 'PD Ramadan Tbk', 56.4, 145, 'PD Ramadan Tbk - Kendari', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2913, 'Hj. Ifa Widiastuti, S.E.', 'L', '2011-03-05', 'Cilegon', 'CV Halimah Tamba', 43.4, 120, 'CV Halimah Tamba - Cilegon', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2914, 'Gamblang Pudjiastuti', 'P', '2019-07-25', 'Magelang', 'UD Prasetya Tbk', 64.5, 149, 'UD Prasetya Tbk - Magelang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2915, 'Ega Sinaga', 'L', '2012-02-11', 'Cimahi', 'CV Yuniar', 85.3, 160, 'CV Yuniar - Cimahi', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2916, 'Irma Salahudin', 'L', '2017-10-29', 'Sukabumi', 'Perum Tarihoran Winarsih Tbk', 73.5, 133, 'Perum Tarihoran Winarsih Tbk - Sukabumi', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2917, 'Mujur Susanti, M.Farm', 'P', '2019-11-16', 'Palembang', 'CV Maulana Iswahyudi Tbk', 64.9, 177, 'CV Maulana Iswahyudi Tbk - Palembang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2918, 'Juli Astuti', 'P', '2020-02-17', 'Tebingtinggi', 'UD Mangunsong Tbk', 52, 126, 'UD Mangunsong Tbk - Tebingtinggi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2919, 'Drs. Bambang Kuswoyo', 'L', '2009-02-13', 'Pematangsiantar', 'CV Sihombing (Persero) Tbk', 47.7, 189, 'CV Sihombing (Persero) Tbk - Pematangsiantar', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2920, 'R. Ganep Saefullah', 'P', '2015-02-01', 'Ambon', 'PT Yolanda Anggraini', 79.9, 145, 'PT Yolanda Anggraini - Ambon', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2921, 'Bagus Lestari', 'L', '2016-05-06', 'Tual', 'PT Mulyani Tbk', 52.7, 158, 'PT Mulyani Tbk - Tual', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2922, 'Mila Santoso, S.I.Kom', 'L', '2018-05-24', 'Prabumulih', 'PT Anggriawan (Persero) Tbk', 38.2, 124, 'PT Anggriawan (Persero) Tbk - Prabumulih', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2923, 'Drs. Gandi Budiman, S.H.', 'P', '2019-12-10', 'Kotamobagu', 'Perum Saragih Mandasari', 44.8, 149, 'Perum Saragih Mandasari - Kotamobagu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2924, 'Ayu Uwais', 'P', '2011-08-18', 'Pangkalpinang', 'Perum Hidayanto', 54.2, 163, 'Perum Hidayanto - Pangkalpinang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2925, 'Cici Haryanti', 'L', '2011-11-24', 'Bima', 'Perum Wibisono (Persero) Tbk', 70.8, 188, 'Perum Wibisono (Persero) Tbk - Bima', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2926, 'Hamima Lailasari', 'L', '2010-01-10', 'Banjarmasin', 'PD Riyanti Samosir', 53.5, 136, 'PD Riyanti Samosir - Banjarmasin', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2927, 'Gilda Irawan', 'L', '2009-07-05', 'Pematangsiantar', 'CV Namaga', 74.7, 166, 'CV Namaga - Pematangsiantar', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2928, 'Mulyono Utama', 'P', '2019-11-26', 'Tasikmalaya', 'CV Andriani', 82.3, 131, 'CV Andriani - Tasikmalaya', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2929, 'Dr. Wahyu Agustina', 'L', '2015-12-21', 'Metro', 'CV Hardiansyah', 68, 125, 'CV Hardiansyah - Metro', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2930, 'Tedi Hutapea', 'P', '2019-12-17', 'Bontang', 'UD Mulyani Halimah', 56.2, 136, 'UD Mulyani Halimah - Bontang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2931, 'Tiara Andriani', 'L', '2019-10-08', 'Gorontalo', 'PT Mandasari', 36.3, 157, 'PT Mandasari - Gorontalo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2932, 'Anastasia Hartati', 'P', '2015-06-07', 'Purwokerto', 'PD Namaga (Persero) Tbk', 30.6, 124, 'PD Namaga (Persero) Tbk - Purwokerto', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2933, 'Wawan Setiawan', 'L', '2017-08-11', 'Lhokseumawe', 'PT Saptono', 79.3, 126, 'PT Saptono - Lhokseumawe', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2934, 'Gabriella Pranowo', 'L', '2010-08-30', 'Sawahlunto', 'PT Uyainah Putra (Persero) Tbk', 73.1, 180, 'PT Uyainah Putra (Persero) Tbk - Sawahlunto', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2935, 'Zizi Farida, S.E.', 'P', '2013-09-18', 'Bandung', 'PT Astuti (Persero) Tbk', 43.4, 167, 'PT Astuti (Persero) Tbk - Bandung', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2936, 'Keisha Kuswandari', 'P', '2013-12-03', 'Bontang', 'PT Wahyudin Hariyah', 54.5, 123, 'PT Wahyudin Hariyah - Bontang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2937, 'Ilsa Rahmawati', 'P', '2014-09-20', 'Tual', 'CV Winarsih Gunarto (Persero) Tbk', 44.9, 122, 'CV Winarsih Gunarto (Persero) Tbk - Tual', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2938, 'Bakiadi Hidayanto', 'P', '2020-05-16', 'Surakarta', 'PD Hardiansyah', 63.9, 168, 'PD Hardiansyah - Surakarta', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2939, 'Cut Wani Oktaviani', 'P', '2018-01-31', 'Banjarmasin', 'PT Hakim', 64.2, 154, 'PT Hakim - Banjarmasin', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2940, 'R. Asmadi Wacana, S.H.', 'L', '2011-01-04', 'Solok', 'CV Usamah Tbk', 66.6, 173, 'CV Usamah Tbk - Solok', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2941, 'Elma Nababan, S.Kom', 'L', '1999-08-19', 'Lubuklinggau', 'PT Novitasari', 64, 169, 'PT Novitasari - Lubuklinggau', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2942, 'Cakrawala Mayasari', 'P', '2020-02-11', 'Bandung', 'UD Ramadan Sudiati', 50.5, 167, 'UD Ramadan Sudiati - Bandung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2943, 'drg. Hesti Kusmawati, M.Pd', 'P', '2011-05-16', 'Bengkulu', 'Perum Padmasari (Persero) Tbk', 84.1, 146, 'Perum Padmasari (Persero) Tbk - Bengkulu', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2944, 'Icha Rahmawati, S.Pt', 'L', '2009-11-23', 'Banjarmasin', 'UD Budiyanto Agustina', 87.3, 135, 'UD Budiyanto Agustina - Banjarmasin', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2945, 'Lintang Namaga', 'P', '2015-03-27', 'Kota Administrasi Jakarta Pusat', 'CV Laksmiwati Novitasari', 62.2, 190, 'CV Laksmiwati Novitasari - Kota Administrasi Jakarta Pusat', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2946, 'Dodo Damanik', 'L', '2007-02-19', 'Tebingtinggi', 'PT Maulana Tbk', 79.7, 164, 'PT Maulana Tbk - Tebingtinggi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2947, 'Aurora Prasetyo', 'P', '2016-06-04', 'Tidore Kepulauan', 'PD Pertiwi', 42.2, 127, 'PD Pertiwi - Tidore Kepulauan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2948, 'Wulan Nasyidah', 'P', '2009-04-26', 'Mataram', 'CV Prastuti Budiyanto Tbk', 38.5, 178, 'CV Prastuti Budiyanto Tbk - Mataram', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2949, 'Tgk. Gaduh Kurniawan, S.Sos', 'L', '2010-04-18', 'Makassar', 'UD Wastuti (Persero) Tbk', 75.7, 135, 'UD Wastuti (Persero) Tbk - Makassar', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2950, 'Anom Purwanti', 'P', '2018-03-26', 'Batam', 'CV Wacana Mayasari Tbk', 49.2, 178, 'CV Wacana Mayasari Tbk - Batam', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2951, 'H. Murti Gunawan, M.Kom.', 'P', '2010-12-12', 'Sorong', 'PT Prasetyo Utama Tbk', 79.5, 165, 'PT Prasetyo Utama Tbk - Sorong', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2952, 'Vinsen Mandala', 'P', '2013-10-07', 'Pekalongan', 'PT Rajata Kuswandari Tbk', 41.5, 122, 'PT Rajata Kuswandari Tbk - Pekalongan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35');
INSERT INTO `daftar_peserta` (`id`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `tempat_lahir`, `nama_sekolah`, `berat_badan`, `tinggi_badan`, `kontingen`, `kategori_umur`, `jenis_kompetisi`, `kategori_tanding`, `imported_at`) VALUES
(2953, 'R. Capa Wibisono, S.T.', 'L', '2018-03-07', 'Parepare', 'CV Pertiwi Puspasari Tbk', 36.8, 175, 'CV Pertiwi Puspasari Tbk - Parepare', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2954, 'Jaiman Narpati', 'P', '2020-03-31', 'Surabaya', 'CV Wijaya', 49, 141, 'CV Wijaya - Surabaya', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2955, 'Cawisono Pradana', 'P', '1998-04-17', 'Denpasar', 'PT Anggriawan (Persero) Tbk', 59.7, 148, 'PT Anggriawan (Persero) Tbk - Denpasar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2956, 'Titi Uyainah', 'L', '2002-08-28', 'Kupang', 'Perum Napitupulu Kusmawati (Persero) Tbk', 75.9, 173, 'Perum Napitupulu Kusmawati (Persero) Tbk - Kupang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2957, 'Ganep Hasanah', 'P', '2008-08-01', 'Serang', 'UD Winarno', 67.3, 163, 'UD Winarno - Serang', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2958, 'Jasmani Nurdiyanti, S.Pt', 'L', '2020-06-28', 'Binjai', 'PT Uwais Tbk', 67.4, 181, 'PT Uwais Tbk - Binjai', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2959, 'Salsabila Yuniar', 'L', '2019-12-15', 'Serang', 'UD Susanti Agustina', 45.3, 136, 'UD Susanti Agustina - Serang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2960, 'Mala Wibisono, S.IP', 'P', '2019-10-16', 'Tegal', 'CV Wahyuni', 54.5, 120, 'CV Wahyuni - Tegal', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2961, 'R. Najwa Farida, S.E.I', 'L', '2015-03-24', 'Samarinda', 'Perum Winarno Saptono', 64.4, 147, 'Perum Winarno Saptono - Samarinda', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2962, 'Dr. Adinata Jailani, S.T.', 'L', '2000-03-24', 'Palu', 'Perum Ardianto (Persero) Tbk', 64, 132, 'Perum Ardianto (Persero) Tbk - Palu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2963, 'Dadi Pertiwi', 'P', '2011-03-08', 'Tomohon', 'PD Nashiruddin Mangunsong Tbk', 79.6, 134, 'PD Nashiruddin Mangunsong Tbk - Tomohon', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2964, 'Yuliana Fujiati, S.Pd', 'L', '2009-11-18', 'Pangkalpinang', 'PT Utami Laksita', 62.4, 186, 'PT Utami Laksita - Pangkalpinang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2965, 'R. Zamira Rajasa, S.Sos', 'P', '2019-09-29', 'Bau-Bau', 'Perum Winarsih Januar', 82.4, 134, 'Perum Winarsih Januar - Bau-Bau', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2966, 'Wardi Suryatmi', 'P', '2009-08-16', 'Singkawang', 'CV Fujiati Simanjuntak', 49, 122, 'CV Fujiati Simanjuntak - Singkawang', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2967, 'Mahdi Hidayat', 'P', '2004-06-07', 'Tual', 'PT Ardianto Haryanto (Persero) Tbk', 33.7, 121, 'PT Ardianto Haryanto (Persero) Tbk - Tual', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2968, 'Kayla Halim', 'L', '2020-01-03', 'Kendari', 'PD Sihombing', 65.8, 182, 'PD Sihombing - Kendari', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2969, 'Drs. Calista Pertiwi', 'P', '2019-08-24', 'Blitar', 'PT Kusumo Tbk', 75, 142, 'PT Kusumo Tbk - Blitar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2970, 'R.M. Mulyanto Samosir, S.E.I', 'L', '2020-06-11', 'Ternate', 'CV Maheswara', 71.1, 152, 'CV Maheswara - Ternate', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(2971, 'Ana Jailani', 'P', '2020-06-11', 'Bandar Lampung', 'UD Utami', 50.8, 164, 'UD Utami - Bandar Lampung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2972, 'Padma Sihombing', 'P', '2015-02-01', 'Manado', 'PD Hariyah Wijaya', 87.2, 188, 'PD Hariyah Wijaya - Manado', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2973, 'Tgk. Upik Saefullah', 'P', '2015-10-27', 'Batam', 'CV Oktaviani Setiawan', 66.8, 135, 'CV Oktaviani Setiawan - Batam', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2974, 'Belinda Damanik, S.Ked', 'L', '2015-02-17', 'Bau-Bau', 'CV Puspita Farida (Persero) Tbk', 81.7, 127, 'CV Puspita Farida (Persero) Tbk - Bau-Bau', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2975, 'R.M. Nugraha Sirait', 'L', '2008-07-10', 'Surabaya', 'CV Susanti', 60.8, 148, 'CV Susanti - Surabaya', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2976, 'Tiara Rajata', 'L', '2011-03-04', 'Payakumbuh', 'Perum Maheswara Farida Tbk', 82.4, 190, 'Perum Maheswara Farida Tbk - Payakumbuh', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2977, 'Dt. Wisnu Anggraini, M.Kom.', 'L', '2016-01-04', 'Sungai Penuh', 'CV Lazuardi (Persero) Tbk', 65.4, 166, 'CV Lazuardi (Persero) Tbk - Sungai Penuh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2978, 'Dr. Karja Hasanah, S.Gz', 'P', '2018-03-02', 'Tidore Kepulauan', 'PD Saputra (Persero) Tbk', 30.6, 162, 'PD Saputra (Persero) Tbk - Tidore Kepulauan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2979, 'Gamani Santoso, S.Pd', 'P', '2019-07-24', 'Balikpapan', 'Perum Narpati', 47.8, 142, 'Perum Narpati - Balikpapan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2980, 'Argono Wijayanti', 'L', '2017-10-09', 'Palembang', 'UD Mulyani Waskita', 31.9, 123, 'UD Mulyani Waskita - Palembang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2981, 'drg. Tantri Hasanah, S.Kom', 'L', '2011-08-17', 'Cimahi', 'CV Sihombing Napitupulu Tbk', 46.9, 166, 'CV Sihombing Napitupulu Tbk - Cimahi', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2982, 'Lili Jailani', 'L', '2009-08-07', 'Batam', 'PT Mardhiyah', 80.6, 167, 'PT Mardhiyah - Batam', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2983, 'Laila Saefullah', 'L', '2019-12-02', 'Lhokseumawe', 'CV Prayoga', 62.8, 145, 'CV Prayoga - Lhokseumawe', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2984, 'Lintang Hassanah, S.H.', 'P', '2008-07-25', 'Magelang', 'PT Lestari Mustofa', 73.4, 150, 'PT Lestari Mustofa - Magelang', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(2985, 'Drs. Faizah Prasasta, M.Ak', 'P', '2018-02-07', 'Kota Administrasi Jakarta Selatan', 'PT Maulana (Persero) Tbk', 35.2, 185, 'PT Maulana (Persero) Tbk - Kota Administrasi Jakarta Selatan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2986, 'Salman Agustina, S.E.I', 'L', '2018-04-30', 'Prabumulih', 'CV Setiawan Wibisono (Persero) Tbk', 76.6, 156, 'CV Setiawan Wibisono (Persero) Tbk - Prabumulih', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(2987, 'Drs. Hafshah Wahyuni, S.Sos', 'P', '2008-08-29', 'Tanjungpinang', 'Perum Tamba Kuswandari (Persero) Tbk', 34.4, 171, 'Perum Tamba Kuswandari (Persero) Tbk - Tanjungpinang', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(2988, 'Ega Purnawati', 'L', '2014-10-25', 'Binjai', 'PT Sudiati (Persero) Tbk', 64.3, 185, 'PT Sudiati (Persero) Tbk - Binjai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2989, 'Zizi Wibowo', 'P', '1995-11-14', 'Bitung', 'Perum Astuti (Persero) Tbk', 53.6, 161, 'Perum Astuti (Persero) Tbk - Bitung', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2990, 'Pia Dabukke', 'P', '1999-12-29', 'Prabumulih', 'PT Pertiwi (Persero) Tbk', 48.6, 171, 'PT Pertiwi (Persero) Tbk - Prabumulih', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(2991, 'Ir. Baktianto Agustina', 'P', '2009-03-14', 'Batam', 'UD Padmasari', 34.4, 182, 'UD Padmasari - Batam', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2992, 'Yuni Pranowo', 'P', '2011-10-12', 'Yogyakarta', 'PT Adriansyah Hidayat (Persero) Tbk', 79.7, 167, 'PT Adriansyah Hidayat (Persero) Tbk - Yogyakarta', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2993, 'Drs. Ganjaran Nurdiyanti, M.Ak', 'L', '2015-06-25', 'Tebingtinggi', 'PT Kusumo', 62.4, 133, 'PT Kusumo - Tebingtinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(2994, 'drg. Galur Pratiwi', 'L', '2020-02-05', 'Banda Aceh', 'CV Purwanti Ardianto', 60.3, 152, 'CV Purwanti Ardianto - Banda Aceh', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(2995, 'Paramita Mahendra', 'P', '2011-10-26', 'Binjai', 'Perum Wijayanti Zulkarnain', 58.4, 134, 'Perum Wijayanti Zulkarnain - Binjai', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(2996, 'Padmi Salahudin, S.Gz', 'P', '2017-07-13', 'Kupang', 'PT Nugroho', 60.6, 165, 'PT Nugroho - Kupang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(2997, 'Labuh Nashiruddin', 'L', '1995-12-22', 'Kota Administrasi Jakarta Utara', 'PD Pranowo', 43.8, 179, 'PD Pranowo - Kota Administrasi Jakarta Utara', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2998, 'Ir. Amalia Aryani', 'L', '2017-09-06', 'Pariaman', 'CV Lailasari Waskita Tbk', 45.9, 162, 'CV Lailasari Waskita Tbk - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(2999, 'Martaka Tarihoran', 'P', '2017-08-04', 'Bau-Bau', 'CV Wacana Nasyidah (Persero) Tbk', 44.4, 148, 'CV Wacana Nasyidah (Persero) Tbk - Bau-Bau', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3000, 'Zulaikha Setiawan', 'L', '2009-02-19', 'Pariaman', 'PT Nashiruddin Pudjiastuti (Persero) Tbk', 44.7, 134, 'PT Nashiruddin Pudjiastuti (Persero) Tbk - Pariaman', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3001, 'KH. Umaya Nababan', 'L', '2009-02-10', 'Probolinggo', 'UD Wulandari', 41, 154, 'UD Wulandari - Probolinggo', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3002, 'Cornelia Najmudin', 'L', '2003-09-05', 'Batu', 'Perum Hakim Latupono', 54.6, 151, 'Perum Hakim Latupono - Batu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3003, 'T. Bagus Jailani, M.Farm', 'L', '2015-06-09', 'Gorontalo', 'Perum Hidayanto Tbk', 62.3, 132, 'Perum Hidayanto Tbk - Gorontalo', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3004, 'Queen Puspita', 'P', '2020-06-01', 'Palu', 'CV Kuswoyo Tbk', 77.8, 186, 'CV Kuswoyo Tbk - Palu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3005, 'Galih Mardhiyah', 'P', '2009-11-20', 'Padang Sidempuan', 'UD Santoso Tbk', 66, 168, 'UD Santoso Tbk - Padang Sidempuan', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3006, 'Hilda Puspita, S.Farm', 'P', '2003-03-01', 'Surakarta', 'PT Napitupulu', 38.7, 168, 'PT Napitupulu - Surakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3007, 'Karsa Thamrin, S.T.', 'P', '1996-09-05', 'Malang', 'Perum Widodo Tbk', 58.9, 137, 'Perum Widodo Tbk - Malang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3008, 'Maimunah Maheswara', 'L', '2019-09-19', 'Tangerang Selatan', 'Perum Wacana Tbk', 86.2, 160, 'Perum Wacana Tbk - Tangerang Selatan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3009, 'Dian Sirait', 'L', '2019-07-24', 'Bima', 'CV Marpaung (Persero) Tbk', 32, 160, 'CV Marpaung (Persero) Tbk - Bima', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3010, 'Tgk. Jamalia Winarsih, M.Pd', 'P', '1999-08-24', 'Gorontalo', 'UD Tarihoran Mandasari', 70.3, 185, 'UD Tarihoran Mandasari - Gorontalo', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3011, 'Wahyu Mayasari, S.IP', 'P', '2019-11-10', 'Metro', 'UD Mayasari Nashiruddin', 50.1, 185, 'UD Mayasari Nashiruddin - Metro', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3012, 'Jamal Sihombing', 'L', '2012-01-05', 'Banjarbaru', 'PD Wastuti Kusumo', 36.1, 166, 'PD Wastuti Kusumo - Banjarbaru', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3013, 'Rizki Riyanti', 'P', '2020-03-19', 'Kupang', 'Perum Marpaung Wulandari Tbk', 49.2, 151, 'Perum Marpaung Wulandari Tbk - Kupang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3014, 'Puti Laksmiwati', 'L', '2014-07-19', 'Banda Aceh', 'Perum Namaga', 70.6, 165, 'Perum Namaga - Banda Aceh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3015, 'H. Karja Simbolon, S.T.', 'L', '2013-11-09', 'Cimahi', 'Perum Hariyah', 49, 163, 'Perum Hariyah - Cimahi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3016, 'Catur Riyanti', 'L', '2020-05-30', 'Cimahi', 'UD Rajata', 75.3, 165, 'UD Rajata - Cimahi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3017, 'Drs. Maida Suryatmi, M.Pd', 'P', '2018-03-16', 'Tebingtinggi', 'UD Hutapea (Persero) Tbk', 58, 159, 'UD Hutapea (Persero) Tbk - Tebingtinggi', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3018, 'Putri Anggraini', 'L', '2009-05-21', 'Kotamobagu', 'PT Winarno Kuswandari', 63.9, 187, 'PT Winarno Kuswandari - Kotamobagu', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3019, 'Ayu Lestari', 'L', '2018-02-11', 'Pematangsiantar', 'PT Nasyidah Wacana Tbk', 75.7, 120, 'PT Nasyidah Wacana Tbk - Pematangsiantar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3020, 'Cawisono Nashiruddin', 'L', '2016-02-23', 'Serang', 'PD Saragih (Persero) Tbk', 69.7, 135, 'PD Saragih (Persero) Tbk - Serang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3021, 'H. Cahyanto Padmasari, S.IP', 'L', '2012-06-07', 'Denpasar', 'Perum Thamrin', 78.5, 187, 'Perum Thamrin - Denpasar', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3022, 'Dt. Cahyadi Sitompul', 'L', '2007-05-15', 'Surakarta', 'CV Waskita Prayoga Tbk', 51.4, 147, 'CV Waskita Prayoga Tbk - Surakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3023, 'Karta Iswahyudi', 'P', '2018-01-05', 'Ambon', 'CV Sihotang', 34.9, 136, 'CV Sihotang - Ambon', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3024, 'Latika Usada', 'P', '2002-09-19', 'Medan', 'Perum Rahayu Wibisono Tbk', 87.9, 150, 'Perum Rahayu Wibisono Tbk - Medan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3025, 'Teddy Laksmiwati', 'L', '1996-08-08', 'Balikpapan', 'UD Laksmiwati', 35.3, 162, 'UD Laksmiwati - Balikpapan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3026, 'dr. Gabriella Rahmawati', 'P', '2008-10-02', 'Bogor', 'Perum Zulaika', 74, 124, 'Perum Zulaika - Bogor', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3027, 'Okta Mangunsong', 'P', '2019-07-20', 'Cirebon', 'CV Sihombing Laksita', 65.7, 155, 'CV Sihombing Laksita - Cirebon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3028, 'Jelita Waskita', 'L', '2006-04-23', 'Binjai', 'UD Laksmiwati Pangestu', 60.1, 150, 'UD Laksmiwati Pangestu - Binjai', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3029, 'Opung Pudjiastuti', 'P', '2016-06-12', 'Banda Aceh', 'PD Laksmiwati Prakasa', 60.4, 173, 'PD Laksmiwati Prakasa - Banda Aceh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3030, 'Prayoga Siregar', 'L', '1996-03-11', 'Kota Administrasi Jakarta Utara', 'PD Zulaika Natsir', 69.9, 161, 'PD Zulaika Natsir - Kota Administrasi Jakarta Utara', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3031, 'Raisa Haryanto', 'L', '2013-12-15', 'Pagaralam', 'PD Pangestu Simanjuntak', 46, 139, 'PD Pangestu Simanjuntak - Pagaralam', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3032, 'Kiandra Andriani', 'L', '2017-12-30', 'Depok', 'CV Puspasari Melani', 71.3, 133, 'CV Puspasari Melani - Depok', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3033, 'Drs. Anggabaya Rahmawati, S.Pd', 'L', '2015-05-25', 'Yogyakarta', 'Perum Najmudin Salahudin Tbk', 38.5, 147, 'Perum Najmudin Salahudin Tbk - Yogyakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3034, 'KH. Kenzie Thamrin', 'L', '2014-10-24', 'Bogor', 'UD Andriani Tbk', 73.2, 159, 'UD Andriani Tbk - Bogor', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3035, 'T. Rosman Purwanti, S.H.', 'P', '2013-09-14', 'Kota Administrasi Jakarta Selatan', 'CV Usada Tbk', 48.6, 150, 'CV Usada Tbk - Kota Administrasi Jakarta Selatan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3036, 'Nurul Wastuti, S.Kom', 'L', '2010-06-22', 'Tegal', 'CV Mayasari Anggriawan Tbk', 87, 184, 'CV Mayasari Anggriawan Tbk - Tegal', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3037, 'Opan Mandasari', 'P', '2016-06-21', 'Tebingtinggi', 'UD Safitri Widodo', 80.3, 133, 'UD Safitri Widodo - Tebingtinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3038, 'Warji Nashiruddin', 'L', '2003-08-10', 'Bitung', 'UD Farida Tbk', 87.3, 171, 'UD Farida Tbk - Bitung', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3039, 'drg. Vera Sihombing, S.Pt', 'L', '2017-09-22', 'Surabaya', 'PT Prasetya Tbk', 52.5, 152, 'PT Prasetya Tbk - Surabaya', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3040, 'Dt. Ikhsan Pudjiastuti, S.Kom', 'P', '2020-03-22', 'Cirebon', 'UD Rahayu (Persero) Tbk', 83.1, 165, 'UD Rahayu (Persero) Tbk - Cirebon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3041, 'Dalima Latupono', 'L', '2019-08-01', 'Madiun', 'UD Suryatmi Tbk', 63.8, 170, 'UD Suryatmi Tbk - Madiun', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3042, 'Mulya Permadi', 'L', '2014-05-28', 'Payakumbuh', 'PD Hutagalung', 74.8, 139, 'PD Hutagalung - Payakumbuh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3043, 'Warji Usada', 'L', '2020-01-21', 'Madiun', 'CV Padmasari Suryatmi Tbk', 45.5, 149, 'CV Padmasari Suryatmi Tbk - Madiun', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3044, 'Dt. Limar Usamah, S.E.', 'P', '2014-03-16', 'Mojokerto', 'UD Sitompul Marpaung Tbk', 81.4, 133, 'UD Sitompul Marpaung Tbk - Mojokerto', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3045, 'H. Pangestu Wibowo', 'L', '2014-08-04', 'Solok', 'CV Widiastuti Laksmiwati', 61, 125, 'CV Widiastuti Laksmiwati - Solok', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3046, 'Bakiman Haryanti, S.Farm', 'P', '2017-12-20', 'Kota Administrasi Jakarta Selatan', 'CV Riyanti Ramadan', 63.5, 163, 'CV Riyanti Ramadan - Kota Administrasi Jakarta Selatan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3047, 'R.A. Hani Iswahyudi, S.E.I', 'L', '2014-05-28', 'Palangkaraya', 'Perum Suryatmi Tbk', 64.2, 182, 'Perum Suryatmi Tbk - Palangkaraya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3048, 'Wahyu Gunawan', 'P', '2018-01-14', 'Kota Administrasi Jakarta Barat', 'CV Rahayu (Persero) Tbk', 89.5, 134, 'CV Rahayu (Persero) Tbk - Kota Administrasi Jakarta Barat', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3049, 'Dr. Kania Yuniar, S.H.', 'L', '2000-12-17', 'Kotamobagu', 'PD Hidayat (Persero) Tbk', 35.7, 159, 'PD Hidayat (Persero) Tbk - Kotamobagu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3050, 'Kacung Sitompul', 'P', '2014-02-08', 'Yogyakarta', 'PD Hutapea (Persero) Tbk', 82.4, 143, 'PD Hutapea (Persero) Tbk - Yogyakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3051, 'Puti Kamila Wahyuni', 'P', '2017-09-05', 'Tegal', 'UD Zulaika Tarihoran Tbk', 45.8, 164, 'UD Zulaika Tarihoran Tbk - Tegal', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3052, 'Jatmiko Wahyuni', 'P', '2009-02-07', 'Malang', 'PT Nuraini', 36.7, 164, 'PT Nuraini - Malang', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3053, 'Pia Wahyudin', 'P', '1996-06-23', 'Tomohon', 'PT Rahimah Melani', 73.1, 169, 'PT Rahimah Melani - Tomohon', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3054, 'Nurul Prasetyo', 'P', '2017-12-23', 'Kupang', 'PD Hastuti Wibisono Tbk', 86.6, 142, 'PD Hastuti Wibisono Tbk - Kupang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3055, 'Cengkal Fujiati', 'L', '2018-02-12', 'Pariaman', 'CV Rajasa Sudiati', 51, 126, 'CV Rajasa Sudiati - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3056, 'Jefri Sirait', 'P', '2004-10-25', 'Kota Administrasi Jakarta Pusat', 'Perum Hartati (Persero) Tbk', 40.7, 120, 'Perum Hartati (Persero) Tbk - Kota Administrasi Jakarta Pusat', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3057, 'Dewi Ramadan', 'L', '2019-08-24', 'Manado', 'Perum Hassanah Tbk', 46.8, 159, 'Perum Hassanah Tbk - Manado', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3058, 'Widya Fujiati', 'P', '2018-01-11', 'Pangkalpinang', 'Perum Rajata Tamba (Persero) Tbk', 56.3, 160, 'Perum Rajata Tamba (Persero) Tbk - Pangkalpinang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3059, 'Ir. Prasetya Hassanah', 'P', '2019-12-12', 'Makassar', 'Perum Yuliarti Dabukke', 81.9, 128, 'Perum Yuliarti Dabukke - Makassar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3060, 'Dr. Kani Wijayanti', 'L', '2010-12-30', 'Pekanbaru', 'Perum Wahyudin Tbk', 62.5, 128, 'Perum Wahyudin Tbk - Pekanbaru', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3061, 'Jarwa Sirait, M.Pd', 'P', '2017-12-07', 'Solok', 'PT Yulianti Nasyiah Tbk', 75.5, 149, 'PT Yulianti Nasyiah Tbk - Solok', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3062, 'Kasiran Iswahyudi', 'P', '2010-09-05', 'Bau-Bau', 'CV Nugroho', 47.9, 151, 'CV Nugroho - Bau-Bau', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3063, 'Simon Mayasari', 'P', '2018-06-03', 'Parepare', 'PT Nashiruddin (Persero) Tbk', 59.8, 135, 'PT Nashiruddin (Persero) Tbk - Parepare', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3064, 'Marwata Andriani', 'P', '2018-03-05', 'Kotamobagu', 'UD Nugroho Laksita Tbk', 47.5, 142, 'UD Nugroho Laksita Tbk - Kotamobagu', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3065, 'Yance Hutagalung', 'P', '2011-01-10', 'Solok', 'Perum Yuniar', 32.5, 160, 'Perum Yuniar - Solok', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3066, 'Murti Usada', 'L', '2011-04-03', 'Batam', 'UD Aryani', 62.9, 171, 'UD Aryani - Batam', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3067, 'Drs. Sakti Sihombing', 'L', '2011-05-24', 'Padang', 'PT Firmansyah Siregar', 60.2, 177, 'PT Firmansyah Siregar - Padang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3068, 'Ghaliyati Budiyanto', 'L', '2005-09-07', 'Meulaboh', 'UD Megantara Rahayu Tbk', 87.2, 131, 'UD Megantara Rahayu Tbk - Meulaboh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3069, 'Asman Saefullah', 'L', '2015-07-28', 'Parepare', 'CV Hutasoit Tbk', 46.8, 137, 'CV Hutasoit Tbk - Parepare', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3070, 'Mahdi Hassanah', 'P', '2019-12-28', 'Sorong', 'Perum Utama', 62.4, 174, 'Perum Utama - Sorong', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3071, 'Kiandra Puspasari', 'P', '2009-05-11', 'Meulaboh', 'CV Pangestu Pratiwi (Persero) Tbk', 88, 169, 'CV Pangestu Pratiwi (Persero) Tbk - Meulaboh', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3072, 'Ulya Wastuti, M.M.', 'P', '2015-12-16', 'Manado', 'Perum Widiastuti Zulaika Tbk', 76.5, 164, 'Perum Widiastuti Zulaika Tbk - Manado', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3073, 'Silvia Yolanda', 'L', '2012-04-25', 'Tanjungpinang', 'PD Maheswara', 40.2, 148, 'PD Maheswara - Tanjungpinang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3074, 'Bahuraksa Halimah', 'P', '1997-02-06', 'Pekalongan', 'CV Agustina (Persero) Tbk', 68.9, 180, 'CV Agustina (Persero) Tbk - Pekalongan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3075, 'Vanesa Lestari', 'L', '2015-09-08', 'Serang', 'PT Iswahyudi (Persero) Tbk', 50.5, 164, 'PT Iswahyudi (Persero) Tbk - Serang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3076, 'Kiandra Suryono', 'P', '2009-06-30', 'Sukabumi', 'CV Kuswoyo Marbun (Persero) Tbk', 88, 126, 'CV Kuswoyo Marbun (Persero) Tbk - Sukabumi', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3077, 'Ella Adriansyah', 'L', '2006-05-27', 'Palangkaraya', 'PT Suartini Januar', 41.5, 136, 'PT Suartini Januar - Palangkaraya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3078, 'R. Tiara Damanik, S.Farm', 'P', '2002-07-26', 'Batam', 'PD Hassanah Tbk', 52.6, 123, 'PD Hassanah Tbk - Batam', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3079, 'Drs. Arta Lailasari, S.Pd', 'P', '2010-07-07', 'Pariaman', 'CV Usamah', 74.5, 142, 'CV Usamah - Pariaman', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3080, 'Reza Puspita', 'L', '2020-04-24', 'Palembang', 'CV Suryatmi Halim', 81.2, 149, 'CV Suryatmi Halim - Palembang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3081, 'Luwar Narpati', 'L', '1999-11-16', 'Tegal', 'UD Mayasari Mustofa (Persero) Tbk', 63.6, 135, 'UD Mayasari Mustofa (Persero) Tbk - Tegal', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3082, 'Karma Mulyani', 'L', '2013-12-31', 'Bukittinggi', 'CV Situmorang', 60.8, 129, 'CV Situmorang - Bukittinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3083, 'Yance Adriansyah', 'L', '2013-09-10', 'Subulussalam', 'CV Pradipta Kurniawan Tbk', 73.6, 143, 'CV Pradipta Kurniawan Tbk - Subulussalam', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3084, 'Daru Hutagalung, M.Farm', 'L', '2004-05-08', 'Palangkaraya', 'CV Nugroho (Persero) Tbk', 36.6, 168, 'CV Nugroho (Persero) Tbk - Palangkaraya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3085, 'Kani Utama', 'P', '2020-01-05', 'Padang Sidempuan', 'CV Suartini Aryani Tbk', 37.5, 154, 'CV Suartini Aryani Tbk - Padang Sidempuan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3086, 'Ivan Adriansyah', 'P', '2012-01-21', 'Sawahlunto', 'PD Sihombing Nurdiyanti', 88.7, 168, 'PD Sihombing Nurdiyanti - Sawahlunto', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3087, 'Dt. Harjasa Prayoga', 'L', '2017-07-20', 'Malang', 'PD Andriani Waluyo (Persero) Tbk', 55.7, 125, 'PD Andriani Waluyo (Persero) Tbk - Malang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3088, 'Paramita Laksita', 'L', '1999-04-01', 'Singkawang', 'UD Hutasoit Nugroho Tbk', 75.2, 169, 'UD Hutasoit Nugroho Tbk - Singkawang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3089, 'Malika Rajasa', 'L', '2017-09-13', 'Batu', 'UD Riyanti Jailani', 38.9, 144, 'UD Riyanti Jailani - Batu', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3090, 'Gasti Utama', 'L', '2017-12-23', 'Batam', 'CV Suryatmi Mahendra', 53.5, 142, 'CV Suryatmi Mahendra - Batam', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3091, 'Drs. Slamet Andriani, S.Pt', 'L', '2001-12-10', 'Surakarta', 'CV Sitompul (Persero) Tbk', 47.8, 185, 'CV Sitompul (Persero) Tbk - Surakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3092, 'R. Mala Hakim', 'P', '2018-01-26', 'Langsa', 'UD Santoso Prasasta', 64.8, 154, 'UD Santoso Prasasta - Langsa', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3093, 'Kunthara Hastuti', 'P', '2014-05-05', 'Meulaboh', 'PD Gunawan Halimah (Persero) Tbk', 69.3, 166, 'PD Gunawan Halimah (Persero) Tbk - Meulaboh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3094, 'Drs. Darmanto Sinaga, S.IP', 'P', '2016-03-28', 'Tanjungbalai', 'CV Lailasari Yulianti', 83.2, 125, 'CV Lailasari Yulianti - Tanjungbalai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3095, 'KH. Simon Gunawan', 'L', '1995-07-25', 'Tomohon', 'CV Hakim', 84.9, 132, 'CV Hakim - Tomohon', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3096, 'Yuni Budiyanto', 'P', '2014-02-25', 'Dumai', 'PT Fujiati', 34, 189, 'PT Fujiati - Dumai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3097, 'R. Hasta Mustofa', 'P', '2019-11-10', 'Tanjungbalai', 'CV Hakim Pratama', 46.7, 150, 'CV Hakim Pratama - Tanjungbalai', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3098, 'Tgk. Sarah Mandala', 'P', '2011-05-15', 'Probolinggo', 'UD Hutapea Prakasa (Persero) Tbk', 72.1, 131, 'UD Hutapea Prakasa (Persero) Tbk - Probolinggo', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3099, 'Gada Rahimah', 'L', '2008-12-13', 'Blitar', 'PD Palastri Nurdiyanti (Persero) Tbk', 58.8, 189, 'PD Palastri Nurdiyanti (Persero) Tbk - Blitar', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3100, 'Rina Mansur', 'P', '2005-08-24', 'Madiun', 'CV Hidayanto Dongoran (Persero) Tbk', 86.2, 136, 'CV Hidayanto Dongoran (Persero) Tbk - Madiun', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3101, 'dr. Eka Maryati, S.E.', 'P', '2015-01-12', 'Kendari', 'UD Saptono Tbk', 34.3, 138, 'UD Saptono Tbk - Kendari', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3102, 'Niyaga Nainggolan', 'L', '2018-05-21', 'Salatiga', 'PT Firmansyah Rajata', 60.1, 150, 'PT Firmansyah Rajata - Salatiga', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3103, 'dr. Mila Halim, M.Ak', 'P', '2018-04-23', 'Tual', 'UD Andriani (Persero) Tbk', 56.3, 155, 'UD Andriani (Persero) Tbk - Tual', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3104, 'Atma Widodo', 'L', '2015-04-27', 'Bima', 'PT Pudjiastuti', 89.2, 179, 'PT Pudjiastuti - Bima', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3105, 'Irfan Padmasari', 'L', '2010-11-25', 'Magelang', 'PD Wahyudin (Persero) Tbk', 39.8, 124, 'PD Wahyudin (Persero) Tbk - Magelang', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3106, 'Candrakanta Puspita', 'L', '2019-08-11', 'Makassar', 'CV Hariyah Mulyani Tbk', 82, 139, 'CV Hariyah Mulyani Tbk - Makassar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3107, 'Dr. Silvia Hidayat, S.IP', 'P', '2019-07-10', 'Palopo', 'PD Lailasari (Persero) Tbk', 86.9, 138, 'PD Lailasari (Persero) Tbk - Palopo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3108, 'Karen Hasanah, S.Kom', 'L', '2005-05-08', 'Semarang', 'CV Fujiati', 89, 141, 'CV Fujiati - Semarang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3109, 'Lili Siregar', 'P', '2014-08-12', 'Pontianak', 'UD Suryatmi', 55.9, 156, 'UD Suryatmi - Pontianak', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3110, 'Sari Nasyiah, M.Kom.', 'P', '1997-03-14', 'Tebingtinggi', 'CV Ardianto Dabukke', 58.3, 152, 'CV Ardianto Dabukke - Tebingtinggi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3111, 'Suci Setiawan', 'P', '2011-12-25', 'Blitar', 'UD Lazuardi (Persero) Tbk', 69.9, 141, 'UD Lazuardi (Persero) Tbk - Blitar', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3112, 'Edi Susanti', 'P', '2011-04-07', 'Tanjungpinang', 'PD Manullang Sitompul', 87.9, 138, 'PD Manullang Sitompul - Tanjungpinang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3113, 'Maria Jailani, S.Pd', 'P', '2014-08-22', 'Bukittinggi', 'CV Najmudin Anggraini (Persero) Tbk', 88.1, 161, 'CV Najmudin Anggraini (Persero) Tbk - Bukittinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3114, 'Kala Saefullah', 'P', '2015-09-01', 'Padang Sidempuan', 'PT Riyanti', 72, 135, 'PT Riyanti - Padang Sidempuan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3115, 'Kamidin Hidayat', 'L', '2009-01-03', 'Surakarta', 'UD Purwanti', 60, 174, 'UD Purwanti - Surakarta', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3116, 'Victoria Pradana, S.H.', 'L', '2020-06-17', 'Kota Administrasi Jakarta Pusat', 'Perum Nasyiah Haryanto (Persero) Tbk', 63.5, 183, 'Perum Nasyiah Haryanto (Persero) Tbk - Kota Administrasi Jakarta Pusat', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3117, 'Ifa Oktaviani', 'L', '2020-06-28', 'Palopo', 'UD Permadi Hutasoit Tbk', 46.2, 163, 'UD Permadi Hutasoit Tbk - Palopo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3118, 'Artawan Novitasari', 'P', '2017-08-20', 'Sabang', 'UD Wahyuni Zulkarnain Tbk', 54.1, 176, 'UD Wahyuni Zulkarnain Tbk - Sabang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3119, 'Lukita Santoso', 'P', '2018-02-21', 'Langsa', 'CV Nuraini Melani (Persero) Tbk', 72, 176, 'CV Nuraini Melani (Persero) Tbk - Langsa', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3120, 'Jumari Mandasari', 'L', '2009-08-26', 'Palangkaraya', 'Perum Kusmawati Wibisono (Persero) Tbk', 58.8, 122, 'Perum Kusmawati Wibisono (Persero) Tbk - Palangkaraya', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3121, 'R.M. Galih Kuswandari, S.Pd', 'L', '2015-11-16', 'Tebingtinggi', 'Perum Susanti Marpaung (Persero) Tbk', 41, 153, 'Perum Susanti Marpaung (Persero) Tbk - Tebingtinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3122, 'Ir. Rika Gunawan, M.M.', 'L', '2013-11-14', 'Pangkalpinang', 'Perum Yuniar Napitupulu Tbk', 71.8, 189, 'Perum Yuniar Napitupulu Tbk - Pangkalpinang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3123, 'Harsaya Kusmawati, S.IP', 'P', '2010-04-23', 'Tanjungpinang', 'CV Prabowo Hutagalung Tbk', 32.5, 126, 'CV Prabowo Hutagalung Tbk - Tanjungpinang', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3124, 'R. Rahmi Nasyidah, M.Kom.', 'L', '2010-09-02', 'Jambi', 'UD Najmudin', 80.3, 154, 'UD Najmudin - Jambi', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3125, 'KH. Narji Prasasta, S.IP', 'P', '2019-09-29', 'Solok', 'PT Budiyanto Nuraini (Persero) Tbk', 38.2, 134, 'PT Budiyanto Nuraini (Persero) Tbk - Solok', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3126, 'Kenes Latupono', 'L', '2005-04-17', 'Sibolga', 'PT Narpati', 61.1, 141, 'PT Narpati - Sibolga', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3127, 'Zelda Wijayanti', 'L', '2014-05-01', 'Sukabumi', 'Perum Habibi Megantara', 58.2, 147, 'Perum Habibi Megantara - Sukabumi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3128, 'Ir. Humaira Nababan', 'P', '2015-07-26', 'Bima', 'Perum Nababan Wibisono', 39.7, 171, 'Perum Nababan Wibisono - Bima', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3129, 'Marsito Siregar, M.TI.', 'L', '2001-09-02', 'Langsa', 'PD Yolanda Tbk', 81.7, 143, 'PD Yolanda Tbk - Langsa', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3130, 'Dodo Waskita', 'L', '2015-09-10', 'Padangpanjang', 'PD Zulaika Tbk', 74.4, 152, 'PD Zulaika Tbk - Padangpanjang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3131, 'Maya Anggriawan, S.T.', 'L', '2005-08-02', 'Pematangsiantar', 'CV Mandasari Maryadi', 41.7, 178, 'CV Mandasari Maryadi - Pematangsiantar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3132, 'T. Rafid Halimah, M.M.', 'L', '2020-01-21', 'Cilegon', 'PD Mandala Hastuti (Persero) Tbk', 34, 134, 'PD Mandala Hastuti (Persero) Tbk - Cilegon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3133, 'Rafid Saputra, S.Psi', 'L', '2012-01-18', 'Pariaman', 'Perum Ramadan', 72.4, 165, 'Perum Ramadan - Pariaman', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3134, 'Kunthara Laksita, S.Gz', 'L', '2018-04-30', 'Mataram', 'CV Sitorus', 63.9, 141, 'CV Sitorus - Mataram', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3135, 'Bakiadi Tampubolon, S.T.', 'L', '2011-06-11', 'Surabaya', 'PT Pratama (Persero) Tbk', 63.5, 132, 'PT Pratama (Persero) Tbk - Surabaya', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3136, 'Cahya Pradipta', 'P', '2019-10-11', 'Kota Administrasi Jakarta Utara', 'CV Padmasari (Persero) Tbk', 69.5, 152, 'CV Padmasari (Persero) Tbk - Kota Administrasi Jakarta Utara', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3137, 'Galak Uwais, M.Ak', 'P', '2015-07-09', 'Pasuruan', 'UD Wasita Tbk', 50.4, 169, 'UD Wasita Tbk - Pasuruan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3138, 'drg. Cinta Mustofa', 'L', '1999-07-04', 'Probolinggo', 'UD Kuswoyo', 33.2, 180, 'UD Kuswoyo - Probolinggo', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3139, 'Sutan Nyoman Uwais, S.T.', 'L', '2015-12-31', 'Balikpapan', 'CV Utami Wibowo', 43, 171, 'CV Utami Wibowo - Balikpapan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3140, 'Harjaya Nurdiyanti', 'L', '2019-11-14', 'Pematangsiantar', 'CV Mandala', 82, 140, 'CV Mandala - Pematangsiantar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3141, 'Mahfud Simbolon', 'P', '2017-12-11', 'Pangkalpinang', 'CV Megantara Melani (Persero) Tbk', 46, 160, 'CV Megantara Melani (Persero) Tbk - Pangkalpinang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3142, 'Bajragin Zulkarnain, S.E.I', 'L', '2011-08-26', 'Tidore Kepulauan', 'Perum Rajasa', 67.6, 131, 'Perum Rajasa - Tidore Kepulauan', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3143, 'Mumpuni Ardianto', 'P', '2018-03-01', 'Kota Administrasi Jakarta Utara', 'PD Hakim Salahudin', 61.3, 162, 'PD Hakim Salahudin - Kota Administrasi Jakarta Utara', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3144, 'Jono Sitorus, S.Pd', 'P', '2013-09-02', 'Jayapura', 'UD Nasyiah Pranowo (Persero) Tbk', 34.1, 128, 'UD Nasyiah Pranowo (Persero) Tbk - Jayapura', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3145, 'Ratih Yulianti', 'L', '2005-10-30', 'Bima', 'Perum Riyanti Nurdiyanti', 36.6, 122, 'Perum Riyanti Nurdiyanti - Bima', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3146, 'Panca Irawan, S.Pd', 'P', '2009-07-26', 'Bima', 'PD Yuniar', 65.7, 165, 'PD Yuniar - Bima', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3147, 'Paiman Januar', 'L', '2010-03-19', 'Ambon', 'PT Sinaga Setiawan (Persero) Tbk', 82.8, 133, 'PT Sinaga Setiawan (Persero) Tbk - Ambon', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3148, 'Dwi Hassanah', 'P', '2010-07-09', 'Kediri', 'PT Kuswandari (Persero) Tbk', 54.4, 171, 'PT Kuswandari (Persero) Tbk - Kediri', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3149, 'Putri Tampubolon', 'L', '2002-04-23', 'Manado', 'Perum Latupono Oktaviani', 89.2, 130, 'Perum Latupono Oktaviani - Manado', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3150, 'Najwa Pratama, S.IP', 'P', '2020-02-26', 'Palu', 'UD Situmorang Halim Tbk', 60.7, 157, 'UD Situmorang Halim Tbk - Palu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3151, 'Drajat Megantara', 'P', '2002-04-22', 'Kupang', 'Perum Sudiati Saefullah Tbk', 55.6, 135, 'Perum Sudiati Saefullah Tbk - Kupang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3152, 'Jono Lazuardi, M.Farm', 'P', '2015-12-24', 'Tangerang Selatan', 'PT Uyainah (Persero) Tbk', 82.6, 154, 'PT Uyainah (Persero) Tbk - Tangerang Selatan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3153, 'Sabri Widiastuti', 'P', '2018-02-15', 'Prabumulih', 'Perum Riyanti', 87.9, 127, 'Perum Riyanti - Prabumulih', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3154, 'Radit Samosir, S.Kom', 'P', '2020-05-14', 'Lubuklinggau', 'CV Nashiruddin Marpaung', 88.5, 144, 'CV Nashiruddin Marpaung - Lubuklinggau', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3155, 'Suci Wahyudin', 'L', '2008-09-03', 'Padang', 'PT Sihotang', 79.2, 128, 'PT Sihotang - Padang', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3156, 'Ghaliyati Melani', 'L', '2011-01-28', 'Padang', 'UD Purwanti', 60.2, 132, 'UD Purwanti - Padang', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3157, 'Tina Uyainah', 'L', '2002-01-29', 'Binjai', 'UD Mayasari Maulana Tbk', 53, 149, 'UD Mayasari Maulana Tbk - Binjai', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3158, 'H. Gadang Hidayat, S.E.', 'L', '2014-10-27', 'Denpasar', 'PT Iswahyudi Setiawan', 85.4, 189, 'PT Iswahyudi Setiawan - Denpasar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3159, 'Sutan Elon Yuniar', 'P', '2009-12-14', 'Depok', 'Perum Andriani Susanti', 31.4, 155, 'Perum Andriani Susanti - Depok', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3160, 'Dt. Kanda Adriansyah, M.Kom.', 'L', '2017-12-03', 'Pekalongan', 'PD Maryadi Januar Tbk', 84.9, 174, 'PD Maryadi Januar Tbk - Pekalongan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3161, 'Titi Palastri', 'L', '2018-01-30', 'Pekalongan', 'PT Hardiansyah Januar', 63.4, 158, 'PT Hardiansyah Januar - Pekalongan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3162, 'drg. Rahmi Saptono, M.M.', 'P', '2010-11-03', 'Pematangsiantar', 'PD Suartini Ardianto (Persero) Tbk', 51, 168, 'PD Suartini Ardianto (Persero) Tbk - Pematangsiantar', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3163, 'Dadap Mangunsong, M.Ak', 'L', '1999-11-06', 'Bukittinggi', 'PD Prayoga Prakasa', 41.9, 157, 'PD Prayoga Prakasa - Bukittinggi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3164, 'Ir. Gamanto Winarno', 'L', '2014-03-28', 'Palangkaraya', 'PD Hariyah Kuswandari', 34.8, 151, 'PD Hariyah Kuswandari - Palangkaraya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3165, 'Kasiran Budiman', 'P', '2013-10-30', 'Tanjungbalai', 'CV Saptono (Persero) Tbk', 71.9, 121, 'CV Saptono (Persero) Tbk - Tanjungbalai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3166, 'Rama Permata', 'L', '2000-07-04', 'Manado', 'PT Megantara (Persero) Tbk', 64.7, 158, 'PT Megantara (Persero) Tbk - Manado', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3167, 'drg. Wulan Situmorang', 'L', '2015-10-24', 'Serang', 'CV Padmasari Santoso (Persero) Tbk', 36.2, 131, 'CV Padmasari Santoso (Persero) Tbk - Serang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3168, 'Maida Nuraini, S.IP', 'L', '2008-07-20', 'Tangerang Selatan', 'UD Salahudin Pratiwi', 86.3, 169, 'UD Salahudin Pratiwi - Tangerang Selatan', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3169, 'Eman Prasetyo, S.Sos', 'L', '2019-10-25', 'Pasuruan', 'CV Pratiwi Pangestu', 52.1, 140, 'CV Pratiwi Pangestu - Pasuruan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3170, 'Puti Zahra Nashiruddin, S.Kom', 'L', '2003-05-01', 'Tual', 'Perum Hutasoit Astuti', 35.7, 163, 'Perum Hutasoit Astuti - Tual', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3171, 'Drs. Pranawa Wacana, S.Kom', 'P', '2010-12-17', 'Banjarbaru', 'Perum Lailasari Hidayanto', 58.8, 179, 'Perum Lailasari Hidayanto - Banjarbaru', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3172, 'Lili Yuniar', 'L', '2014-03-06', 'Tebingtinggi', 'Perum Padmasari Wahyudin', 58.2, 125, 'Perum Padmasari Wahyudin - Tebingtinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3173, 'Cut Sari Sihombing', 'P', '2011-05-25', 'Bontang', 'PT Pratiwi (Persero) Tbk', 72.5, 127, 'PT Pratiwi (Persero) Tbk - Bontang', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3174, 'Kadir Halim, S.Kom', 'L', '2017-12-02', 'Padang', 'PT Irawan', 89.1, 137, 'PT Irawan - Padang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3175, 'Hasna Kuswandari', 'P', '2010-06-21', 'Pasuruan', 'CV Wasita Gunawan (Persero) Tbk', 59.9, 150, 'CV Wasita Gunawan (Persero) Tbk - Pasuruan', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3176, 'Dr. Uli Hastuti, S.Gz', 'P', '2019-11-15', 'Kediri', 'CV Suartini Latupono Tbk', 52.6, 171, 'CV Suartini Latupono Tbk - Kediri', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3177, 'Hasim Pradana', 'P', '2020-05-02', 'Kota Administrasi Jakarta Timur', 'CV Waskita Tbk', 51.4, 120, 'CV Waskita Tbk - Kota Administrasi Jakarta Timur', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3178, 'Wardaya Prastuti', 'P', '2019-07-27', 'Sawahlunto', 'CV Suartini Permata (Persero) Tbk', 78.6, 146, 'CV Suartini Permata (Persero) Tbk - Sawahlunto', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3179, 'Gawati Waluyo', 'P', '2020-05-01', 'Parepare', 'PD Suryono', 58.4, 183, 'PD Suryono - Parepare', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3180, 'R. Candrakanta Haryanto, S.I.Kom', 'L', '1998-06-14', 'Kota Administrasi Jakarta Timur', 'CV Pradana Prasetyo', 87.4, 172, 'CV Pradana Prasetyo - Kota Administrasi Jakarta Timur', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3181, 'Kasiran Hastuti, S.E.I', 'L', '2002-06-13', 'Bandung', 'Perum Damanik', 32, 170, 'Perum Damanik - Bandung', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3182, 'Suci Ramadan', 'P', '2006-04-07', 'Solok', 'Perum Nashiruddin Laksmiwati', 40.1, 174, 'Perum Nashiruddin Laksmiwati - Solok', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3183, 'Salimah Mandasari', 'P', '2004-02-08', 'Manado', 'UD Nugroho', 31.3, 182, 'UD Nugroho - Manado', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3184, 'Ratih Anggraini', 'L', '2018-02-01', 'Padang Sidempuan', 'UD Megantara', 50.5, 182, 'UD Megantara - Padang Sidempuan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3185, 'Gading Nugroho, S.Gz', 'P', '2020-01-01', 'Makassar', 'CV Sitompul', 61.9, 159, 'CV Sitompul - Makassar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3186, 'Ulya Hasanah', 'P', '2017-10-16', 'Madiun', 'PD Hutasoit Utama', 68.4, 145, 'PD Hutasoit Utama - Madiun', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3187, 'Ida Uyainah', 'L', '2019-12-19', 'Mataram', 'UD Wahyudin Namaga (Persero) Tbk', 33.1, 169, 'UD Wahyudin Namaga (Persero) Tbk - Mataram', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3188, 'T. Leo Hartati, M.Farm', 'P', '2020-05-10', 'Surabaya', 'Perum Yuniar Wahyudin', 68.7, 134, 'Perum Yuniar Wahyudin - Surabaya', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3189, 'Calista Sitorus', 'L', '2004-03-23', 'Prabumulih', 'PD Saragih (Persero) Tbk', 44.7, 162, 'PD Saragih (Persero) Tbk - Prabumulih', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3190, 'R.M. Cahyo Haryanti, S.Ked', 'P', '2014-11-03', 'Kota Administrasi Jakarta Barat', 'CV Tampubolon (Persero) Tbk', 72.1, 169, 'CV Tampubolon (Persero) Tbk - Kota Administrasi Jakarta Barat', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3191, 'Farah Wasita, S.Ked', 'L', '2018-06-29', 'Pekalongan', 'UD Firmansyah Winarsih Tbk', 38.6, 128, 'UD Firmansyah Winarsih Tbk - Pekalongan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3192, 'Martani Handayani', 'P', '2010-12-15', 'Surakarta', 'PD Hastuti', 64.7, 188, 'PD Hastuti - Surakarta', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3193, 'H. Lasmanto Habibi, M.Kom.', 'L', '2017-08-31', 'Singkawang', 'CV Yuliarti', 66.5, 133, 'CV Yuliarti - Singkawang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3194, 'Zulaikha Suryono', 'L', '2015-01-10', 'Tanjungbalai', 'CV Sudiati (Persero) Tbk', 77.3, 172, 'CV Sudiati (Persero) Tbk - Tanjungbalai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3195, 'Melinda Jailani', 'L', '2016-05-01', 'Binjai', 'PD Siregar Latupono', 45.7, 178, 'PD Siregar Latupono - Binjai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3196, 'Ibrahim Farida, M.M.', 'L', '1996-06-30', 'Madiun', 'Perum Mayasari Nababan Tbk', 70.5, 174, 'Perum Mayasari Nababan Tbk - Madiun', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3197, 'Drs. Ilyas Nugroho, S.Pd', 'L', '2019-08-09', 'Kota Administrasi Jakarta Barat', 'UD Tarihoran', 78.3, 159, 'UD Tarihoran - Kota Administrasi Jakarta Barat', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3198, 'Legawa Hutapea', 'P', '1999-06-12', 'Denpasar', 'PD Maheswara (Persero) Tbk', 31.4, 183, 'PD Maheswara (Persero) Tbk - Denpasar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3199, 'Gangsa Hariyah', 'L', '2011-07-08', 'Solok', 'Perum Sihombing Marbun (Persero) Tbk', 36.9, 166, 'Perum Sihombing Marbun (Persero) Tbk - Solok', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3200, 'Sutan Cawuk Sinaga', 'P', '1999-05-06', 'Mataram', 'PD Puspita Tbk', 42.4, 146, 'PD Puspita Tbk - Mataram', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3201, 'Titi Nababan', 'P', '2013-07-09', 'Lhokseumawe', 'Perum Fujiati (Persero) Tbk', 62.9, 145, 'Perum Fujiati (Persero) Tbk - Lhokseumawe', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35');
INSERT INTO `daftar_peserta` (`id`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `tempat_lahir`, `nama_sekolah`, `berat_badan`, `tinggi_badan`, `kontingen`, `kategori_umur`, `jenis_kompetisi`, `kategori_tanding`, `imported_at`) VALUES
(3202, 'Purwa Pradana, S.Farm', 'L', '2018-05-18', 'Tegal', 'Perum Wacana', 65.5, 127, 'Perum Wacana - Tegal', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3203, 'Farah Purwanti', 'P', '2020-04-09', 'Pematangsiantar', 'Perum Suryono', 76.6, 179, 'Perum Suryono - Pematangsiantar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3204, 'Rina Wulandari, S.Gz', 'L', '2019-12-09', 'Blitar', 'PD Wastuti Mayasari', 72.8, 148, 'PD Wastuti Mayasari - Blitar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3205, 'Ayu Handayani', 'L', '2014-09-07', 'Tual', 'Perum Lestari Andriani', 83.3, 166, 'Perum Lestari Andriani - Tual', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3206, 'Maimunah Nasyiah', 'L', '2014-04-05', 'Pariaman', 'Perum Kusumo', 81.3, 183, 'Perum Kusumo - Pariaman', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3207, 'Lili Widodo', 'P', '2009-05-19', 'Jambi', 'UD Pratama', 55.6, 179, 'UD Pratama - Jambi', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3208, 'Kanda Samosir', 'L', '2018-04-29', 'Bogor', 'Perum Rajata (Persero) Tbk', 73.9, 135, 'Perum Rajata (Persero) Tbk - Bogor', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3209, 'Irfan Kuswoyo, S.T.', 'P', '2011-09-09', 'Pekalongan', 'PT Pradana Tbk', 37.4, 181, 'PT Pradana Tbk - Pekalongan', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3210, 'Ikhsan Setiawan', 'L', '2009-05-27', 'Probolinggo', 'PD Wahyuni Maheswara', 60, 155, 'PD Wahyuni Maheswara - Probolinggo', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3211, 'Ganda Lestari', 'P', '1998-07-03', 'Tarakan', 'UD Suryono Tbk', 87.7, 164, 'UD Suryono Tbk - Tarakan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3212, 'drg. Shania Firmansyah', 'P', '2018-05-11', 'Depok', 'PD Widiastuti (Persero) Tbk', 41.9, 146, 'PD Widiastuti (Persero) Tbk - Depok', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3213, 'Salwa Simanjuntak', 'L', '2009-08-02', 'Palangkaraya', 'PD Prasetya', 40, 140, 'PD Prasetya - Palangkaraya', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3214, 'Tgk. Emil Safitri', 'L', '2015-02-09', 'Padang', 'PT Yulianti Tbk', 40.9, 171, 'PT Yulianti Tbk - Padang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3215, 'dr. Saiful Hastuti', 'L', '2015-05-29', 'Pekalongan', 'Perum Manullang Halimah Tbk', 75.9, 155, 'Perum Manullang Halimah Tbk - Pekalongan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3216, 'Yunita Suryono', 'L', '1997-12-26', 'Batam', 'PD Salahudin (Persero) Tbk', 60.6, 126, 'PD Salahudin (Persero) Tbk - Batam', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3217, 'Drs. Ina Yuliarti', 'L', '2003-11-08', 'Serang', 'Perum Simbolon Suwarno (Persero) Tbk', 40, 145, 'Perum Simbolon Suwarno (Persero) Tbk - Serang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3218, 'Cindy Wibisono', 'L', '2018-02-12', 'Makassar', 'PD Waskita Utami', 35.2, 137, 'PD Waskita Utami - Makassar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3219, 'H. Gandewa Usada, M.M.', 'P', '2019-11-07', 'Purwokerto', 'PT Riyanti', 67.3, 158, 'PT Riyanti - Purwokerto', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3220, 'Siska Saptono', 'P', '2020-04-02', 'Magelang', 'PD Padmasari (Persero) Tbk', 70.1, 133, 'PD Padmasari (Persero) Tbk - Magelang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3221, 'Mursita Kurniawan, S.I.Kom', 'P', '2004-08-16', 'Malang', 'PD Prabowo Handayani Tbk', 36.9, 132, 'PD Prabowo Handayani Tbk - Malang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3222, 'drg. Kajen Hutagalung', 'L', '2006-04-09', 'Tual', 'Perum Farida Nuraini Tbk', 53.6, 150, 'Perum Farida Nuraini Tbk - Tual', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3223, 'R.A. Queen Uwais', 'P', '1998-08-16', 'Depok', 'PT Damanik Tbk', 35.9, 158, 'PT Damanik Tbk - Depok', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3224, 'Paris Lailasari, S.T.', 'L', '2017-07-09', 'Sabang', 'Perum Nasyiah (Persero) Tbk', 73.2, 170, 'Perum Nasyiah (Persero) Tbk - Sabang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3225, 'drg. Nilam Adriansyah, S.Kom', 'P', '2012-04-25', 'Banjar', 'PT Gunarto Fujiati', 87.8, 144, 'PT Gunarto Fujiati - Banjar', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3226, 'R. Galang Pertiwi', 'L', '2019-09-25', 'Cimahi', 'Perum Hidayat Winarno (Persero) Tbk', 65, 187, 'Perum Hidayat Winarno (Persero) Tbk - Cimahi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3227, 'Rahmat Winarno', 'P', '2020-05-17', 'Sorong', 'CV Riyanti Anggriawan (Persero) Tbk', 34.3, 186, 'CV Riyanti Anggriawan (Persero) Tbk - Sorong', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3228, 'Almira Mandala', 'P', '2001-03-14', 'Palopo', 'UD Permata Saptono Tbk', 33.2, 178, 'UD Permata Saptono Tbk - Palopo', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3229, 'Novi Padmasari', 'L', '2002-10-15', 'Malang', 'Perum Usamah Hasanah Tbk', 39.8, 139, 'Perum Usamah Hasanah Tbk - Malang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3230, 'Karsana Salahudin, M.Pd', 'P', '1998-05-18', 'Prabumulih', 'PT Yuliarti', 55, 184, 'PT Yuliarti - Prabumulih', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3231, 'Bakianto Wastuti, S.Pt', 'P', '2002-05-03', 'Lubuklinggau', 'UD Andriani Sihombing Tbk', 78.3, 167, 'UD Andriani Sihombing Tbk - Lubuklinggau', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3232, 'Maria Purnawati', 'P', '2009-01-23', 'Pematangsiantar', 'Perum Agustina Utami', 43, 159, 'Perum Agustina Utami - Pematangsiantar', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3233, 'Ajiono Sirait', 'L', '2013-07-08', 'Tasikmalaya', 'PD Santoso (Persero) Tbk', 45.4, 183, 'PD Santoso (Persero) Tbk - Tasikmalaya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3234, 'Putri Wasita', 'P', '1996-03-08', 'Sungai Penuh', 'UD Saputra Prasetyo Tbk', 52.5, 171, 'UD Saputra Prasetyo Tbk - Sungai Penuh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3235, 'dr. Garang Haryanti, S.T.', 'L', '2002-06-15', 'Kupang', 'CV Maryadi', 70.7, 149, 'CV Maryadi - Kupang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3236, 'Kariman Setiawan', 'P', '1996-10-25', 'Kota Administrasi Jakarta Selatan', 'PD Saputra (Persero) Tbk', 52, 133, 'PD Saputra (Persero) Tbk - Kota Administrasi Jakarta Selatan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3237, 'H. Wawan Widodo, S.IP', 'L', '1996-06-04', 'Metro', 'Perum Sitorus', 46.3, 129, 'Perum Sitorus - Metro', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3238, 'Drs. Shakila Hutapea, S.Farm', 'L', '2020-04-20', 'Tomohon', 'CV Pradana Hassanah', 85.6, 181, 'CV Pradana Hassanah - Tomohon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3239, 'Endah Santoso', 'P', '2009-12-24', 'Probolinggo', 'PT Winarno (Persero) Tbk', 41.9, 134, 'PT Winarno (Persero) Tbk - Probolinggo', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3240, 'Fitria Hardiansyah', 'L', '2019-09-23', 'Tanjungbalai', 'PD Hidayanto (Persero) Tbk', 71.1, 122, 'PD Hidayanto (Persero) Tbk - Tanjungbalai', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3241, 'Galih Hartati', 'P', '2017-07-21', 'Bukittinggi', 'PT Hassanah Mustofa', 60.7, 149, 'PT Hassanah Mustofa - Bukittinggi', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3242, 'Ella Wijaya', 'L', '2003-06-17', 'Kota Administrasi Jakarta Timur', 'PT Iswahyudi', 82, 149, 'PT Iswahyudi - Kota Administrasi Jakarta Timur', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3243, 'Sadina Saefullah', 'L', '2003-06-26', 'Padangpanjang', 'PT Halimah (Persero) Tbk', 67.2, 155, 'PT Halimah (Persero) Tbk - Padangpanjang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3244, 'Gandewa Tamba, S.Farm', 'L', '2017-07-23', 'Kota Administrasi Jakarta Pusat', 'PD Kuswandari Hutapea (Persero) Tbk', 86.7, 157, 'PD Kuswandari Hutapea (Persero) Tbk - Kota Administrasi Jakarta Pusat', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3245, 'Calista Wastuti, S.E.', 'L', '2020-04-10', 'Samarinda', 'PT Hartati Tamba', 52.1, 140, 'PT Hartati Tamba - Samarinda', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3246, 'Ratih Mandasari', 'L', '2009-09-22', 'Padang Sidempuan', 'UD Siregar', 64.6, 167, 'UD Siregar - Padang Sidempuan', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3247, 'Dr. Nilam Pradana', 'L', '2013-12-24', 'Pasuruan', 'Perum Wahyuni', 59.8, 153, 'Perum Wahyuni - Pasuruan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3248, 'Gamani Waskita', 'P', '2016-04-18', 'Samarinda', 'CV Waluyo Tbk', 47.5, 180, 'CV Waluyo Tbk - Samarinda', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3249, 'Saka Permadi', 'L', '2010-01-04', 'Kota Administrasi Jakarta Selatan', 'PT Andriani', 62.3, 184, 'PT Andriani - Kota Administrasi Jakarta Selatan', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3250, 'Hj. Safina Usada', 'L', '2019-11-09', 'Cimahi', 'CV Hutagalung Sitorus', 33, 134, 'CV Hutagalung Sitorus - Cimahi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3251, 'Ega Dabukke', 'P', '2011-11-21', 'Probolinggo', 'Perum Gunawan', 46.5, 141, 'Perum Gunawan - Probolinggo', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3252, 'Galiono Maheswara', 'P', '2020-06-28', 'Manado', 'PD Maulana Utama (Persero) Tbk', 88.3, 187, 'PD Maulana Utama (Persero) Tbk - Manado', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3253, 'dr. Salimah Lailasari, M.Farm', 'L', '2006-11-10', 'Kotamobagu', 'UD Rahimah Kurniawan Tbk', 45, 187, 'UD Rahimah Kurniawan Tbk - Kotamobagu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3254, 'Panca Hastuti', 'P', '2015-01-18', 'Sabang', 'PT Usamah', 44.4, 187, 'PT Usamah - Sabang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3255, 'Argono Utami', 'P', '1999-02-17', 'Bengkulu', 'CV Mahendra Tbk', 50.6, 158, 'CV Mahendra Tbk - Bengkulu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3256, 'Sutan Virman Pratiwi', 'P', '1996-07-17', 'Palu', 'PD Dabukke Uyainah (Persero) Tbk', 30.4, 169, 'PD Dabukke Uyainah (Persero) Tbk - Palu', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3257, 'Lili Narpati, S.I.Kom', 'P', '1995-09-13', 'Pariaman', 'Perum Aryani Siregar', 78.3, 142, 'Perum Aryani Siregar - Pariaman', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3258, 'Unjani Utama', 'P', '2004-03-28', 'Madiun', 'UD Nugroho Mahendra (Persero) Tbk', 80.9, 182, 'UD Nugroho Mahendra (Persero) Tbk - Madiun', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3259, 'Gandewa Manullang', 'P', '2009-02-05', 'Padang Sidempuan', 'Perum Riyanti (Persero) Tbk', 61.5, 163, 'Perum Riyanti (Persero) Tbk - Padang Sidempuan', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3260, 'Puti Ellis Oktaviani, S.Sos', 'P', '2017-12-11', 'Solok', 'UD Mustofa', 87.8, 150, 'UD Mustofa - Solok', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3261, 'Bakti Iswahyudi', 'P', '2018-06-29', 'Tual', 'PT Utami', 75.4, 155, 'PT Utami - Tual', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3262, 'Talia Nainggolan, S.Sos', 'P', '2015-07-05', 'Sabang', 'UD Hastuti Nasyiah Tbk', 39.4, 159, 'UD Hastuti Nasyiah Tbk - Sabang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3263, 'Waluyo Hariyah', 'L', '2020-02-03', 'Samarinda', 'Perum Mahendra Dabukke (Persero) Tbk', 40.7, 138, 'Perum Mahendra Dabukke (Persero) Tbk - Samarinda', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3264, 'Jagapati Wibisono', 'L', '2014-12-25', 'Tual', 'Perum Hutagalung (Persero) Tbk', 72.5, 141, 'Perum Hutagalung (Persero) Tbk - Tual', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3265, 'Tgk. Septi Andriani', 'L', '2007-05-08', 'Kota Administrasi Jakarta Timur', 'PT Latupono', 32.4, 158, 'PT Latupono - Kota Administrasi Jakarta Timur', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3266, 'R. Puti Mustofa, M.Ak', 'L', '2015-01-23', 'Bandar Lampung', 'Perum Najmudin Habibi', 83.8, 143, 'Perum Najmudin Habibi - Bandar Lampung', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3267, 'Dartono Yolanda', 'P', '2018-06-01', 'Sawahlunto', 'Perum Lestari (Persero) Tbk', 66.9, 181, 'Perum Lestari (Persero) Tbk - Sawahlunto', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3268, 'Jessica Wijayanti', 'P', '2019-09-22', 'Mataram', 'CV Winarno', 81.5, 169, 'CV Winarno - Mataram', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3269, 'Suci Oktaviani', 'P', '2008-07-11', 'Sawahlunto', 'PD Mulyani', 52.9, 186, 'PD Mulyani - Sawahlunto', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3270, 'Shakila Zulkarnain', 'L', '2019-10-26', 'Bandar Lampung', 'Perum Prakasa', 50.2, 155, 'Perum Prakasa - Bandar Lampung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3271, 'Rusman Pradana', 'P', '2018-05-01', 'Pariaman', 'PT Wijayanti', 89.1, 157, 'PT Wijayanti - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3272, 'Winda Wulandari, M.Farm', 'L', '2008-09-21', 'Tangerang', 'PD Sihombing', 87, 141, 'PD Sihombing - Tangerang', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3273, 'Cut Tari Mustofa, S.I.Kom', 'P', '2008-08-25', 'Manado', 'CV Sirait Hidayanto', 34.5, 123, 'CV Sirait Hidayanto - Manado', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3274, 'Raisa Usada', 'L', '2017-11-11', 'Padangpanjang', 'UD Pudjiastuti', 36.3, 168, 'UD Pudjiastuti - Padangpanjang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3275, 'Agnes Utama, M.Farm', 'P', '2015-02-19', 'Sibolga', 'UD Natsir Tbk', 71.6, 147, 'UD Natsir Tbk - Sibolga', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3276, 'Jayadi Hasanah', 'L', '2006-11-17', 'Tangerang', 'PT Usamah', 60.9, 144, 'PT Usamah - Tangerang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3277, 'Uli Melani, M.M.', 'P', '2019-12-19', 'Manado', 'PD Utami', 75.5, 162, 'PD Utami - Manado', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3278, 'Setya Nuraini, M.TI.', 'L', '2013-10-13', 'Kota Administrasi Jakarta Utara', 'Perum Suartini Kusmawati Tbk', 67.7, 161, 'Perum Suartini Kusmawati Tbk - Kota Administrasi Jakarta Utara', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3279, 'Yance Wulandari', 'P', '2010-06-09', 'Ambon', 'PT Lazuardi (Persero) Tbk', 68.5, 171, 'PT Lazuardi (Persero) Tbk - Ambon', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3280, 'dr. Warsita Thamrin, S.Sos', 'P', '2020-05-13', 'Bitung', 'Perum Irawan Sihotang Tbk', 78.6, 155, 'Perum Irawan Sihotang Tbk - Bitung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3281, 'R.A. Safina Nasyidah, S.IP', 'P', '2019-07-14', 'Banjarmasin', 'PD Wasita Tbk', 61.2, 120, 'PD Wasita Tbk - Banjarmasin', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3282, 'Dinda Purnawati', 'L', '2012-06-16', 'Banjarbaru', 'PD Dongoran Maryadi', 50.5, 136, 'PD Dongoran Maryadi - Banjarbaru', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3283, 'Praba Maryati', 'P', '2010-05-28', 'Pekanbaru', 'PD Adriansyah (Persero) Tbk', 56.5, 120, 'PD Adriansyah (Persero) Tbk - Pekanbaru', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3284, 'Kartika Maryadi', 'P', '2019-12-02', 'Prabumulih', 'PT Pertiwi', 58.9, 190, 'PT Pertiwi - Prabumulih', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3285, 'Hadi Mardhiyah', 'L', '2012-03-12', 'Tual', 'UD Suryono Tbk', 69.7, 132, 'UD Suryono Tbk - Tual', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3286, 'Oskar Sihombing', 'P', '2018-01-03', 'Blitar', 'UD Narpati Wacana', 56.1, 152, 'UD Narpati Wacana - Blitar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3287, 'Ika Tampubolon', 'P', '2015-05-30', 'Tidore Kepulauan', 'Perum Wasita', 38.5, 156, 'Perum Wasita - Tidore Kepulauan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3288, 'Cakrajiya Winarno', 'L', '2017-07-07', 'Pontianak', 'PD Yolanda Tbk', 85.5, 177, 'PD Yolanda Tbk - Pontianak', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3289, 'Sabri Mayasari', 'L', '2011-09-05', 'Tomohon', 'PD Simbolon Tbk', 89.5, 178, 'PD Simbolon Tbk - Tomohon', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3290, 'T. Dagel Mandala, S.Pt', 'L', '2017-12-16', 'Bandar Lampung', 'PD Lestari Wijaya', 76.7, 145, 'PD Lestari Wijaya - Bandar Lampung', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3291, 'Elvin Kusumo, S.Farm', 'L', '2004-01-26', 'Gorontalo', 'CV Anggraini', 51.6, 158, 'CV Anggraini - Gorontalo', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3292, 'Gasti Setiawan, S.Sos', 'L', '2019-12-15', 'Bandar Lampung', 'CV Saefullah Tbk', 52.6, 175, 'CV Saefullah Tbk - Bandar Lampung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3293, 'T. Gamblang Habibi, S.E.', 'P', '2018-03-11', 'Batam', 'CV Sitompul Sihombing', 48.6, 131, 'CV Sitompul Sihombing - Batam', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3294, 'R.M. Ajimat Widodo', 'P', '2018-04-07', 'Malang', 'Perum Lestari Tbk', 43, 164, 'Perum Lestari Tbk - Malang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3295, 'Kambali Nuraini', 'L', '2020-06-30', 'Denpasar', 'PT Yuniar Pradipta Tbk', 43.6, 185, 'PT Yuniar Pradipta Tbk - Denpasar', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3296, 'Ibrahim Nasyidah', 'L', '2009-03-06', 'Kendari', 'PD Pangestu Suryatmi Tbk', 52.2, 173, 'PD Pangestu Suryatmi Tbk - Kendari', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3297, 'Salimah Halimah, S.Sos', 'L', '2013-11-04', 'Cimahi', 'CV Hutapea Handayani', 50.9, 125, 'CV Hutapea Handayani - Cimahi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3298, 'Nadia Manullang', 'L', '2014-11-16', 'Subulussalam', 'Perum Nuraini Haryanti', 57.3, 164, 'Perum Nuraini Haryanti - Subulussalam', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3299, 'Edward Marpaung', 'L', '2019-07-18', 'Dumai', 'PD Prabowo Tbk', 33.9, 157, 'PD Prabowo Tbk - Dumai', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3300, 'Talia Hartati', 'L', '2019-10-16', 'Metro', 'CV Gunarto', 76.5, 160, 'CV Gunarto - Metro', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3301, 'Argono Saptono, M.M.', 'L', '2016-06-16', 'Prabumulih', 'UD Aryani (Persero) Tbk', 42.6, 159, 'UD Aryani (Persero) Tbk - Prabumulih', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3302, 'H. Hadi Anggriawan, S.T.', 'P', '2020-02-21', 'Singkawang', 'UD Rahimah Megantara', 73, 162, 'UD Rahimah Megantara - Singkawang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3303, 'R.M. Yoga Wahyuni, S.H.', 'L', '2017-11-02', 'Cimahi', 'Perum Sudiati Yolanda Tbk', 36.1, 159, 'Perum Sudiati Yolanda Tbk - Cimahi', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3304, 'Dinda Sitorus, S.E.I', 'P', '1998-01-23', 'Palembang', 'PT Nababan Narpati', 46.5, 151, 'PT Nababan Narpati - Palembang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3305, 'Ir. Ade Nasyiah', 'L', '2015-08-12', 'Payakumbuh', 'PD Novitasari Pradana', 42.9, 135, 'PD Novitasari Pradana - Payakumbuh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3306, 'Kania Irawan', 'P', '2015-01-17', 'Pontianak', 'PD Samosir Zulaika', 48.1, 186, 'PD Samosir Zulaika - Pontianak', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3307, 'Drs. Umar Waskita, S.Farm', 'P', '2019-08-03', 'Tebingtinggi', 'UD Jailani (Persero) Tbk', 82.4, 122, 'UD Jailani (Persero) Tbk - Tebingtinggi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3308, 'Ismail Widiastuti', 'L', '2004-08-02', 'Palembang', 'PT Winarsih Kurniawan', 53.2, 157, 'PT Winarsih Kurniawan - Palembang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3309, 'Jasmani Palastri, S.Sos', 'P', '2018-02-08', 'Purwokerto', 'PD Novitasari (Persero) Tbk', 89, 122, 'PD Novitasari (Persero) Tbk - Purwokerto', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3310, 'dr. Citra Hasanah', 'P', '1996-10-20', 'Tual', 'PT Nurdiyanti Maryati', 88.9, 168, 'PT Nurdiyanti Maryati - Tual', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3311, 'Kardi Thamrin', 'L', '2020-03-11', 'Salatiga', 'Perum Mandasari Wijayanti (Persero) Tbk', 54.9, 131, 'Perum Mandasari Wijayanti (Persero) Tbk - Salatiga', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3312, 'Jamalia Maulana', 'L', '2015-05-07', 'Batu', 'PT Maryadi Nashiruddin Tbk', 81.1, 121, 'PT Maryadi Nashiruddin Tbk - Batu', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3313, 'Laila Halimah', 'P', '2012-03-31', 'Kotamobagu', 'PD Farida Mustofa', 62, 176, 'PD Farida Mustofa - Kotamobagu', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3314, 'R. Faizah Mahendra, M.TI.', 'L', '2011-12-04', 'Manado', 'UD Mulyani Palastri', 87.7, 131, 'UD Mulyani Palastri - Manado', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3315, 'Tgk. Rina Megantara, S.E.I', 'P', '2018-05-13', 'Ternate', 'CV Andriani Yuniar Tbk', 35.7, 184, 'CV Andriani Yuniar Tbk - Ternate', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3316, 'Syahrini Mandasari, S.Pd', 'L', '2001-11-15', 'Prabumulih', 'PT Sirait Laksita', 82.5, 178, 'PT Sirait Laksita - Prabumulih', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3317, 'Tomi Widodo', 'L', '2014-04-11', 'Samarinda', 'UD Maheswara Adriansyah', 44.7, 172, 'UD Maheswara Adriansyah - Samarinda', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3318, 'Tgk. Vanya Wacana, S.IP', 'P', '2003-06-06', 'Padang Sidempuan', 'Perum Pradipta Utami', 79, 137, 'Perum Pradipta Utami - Padang Sidempuan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3319, 'Cawisono Santoso', 'P', '2019-08-19', 'Pagaralam', 'Perum Puspita Nasyiah', 35.7, 130, 'Perum Puspita Nasyiah - Pagaralam', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3320, 'Cut Rahmi Haryanti, S.IP', 'P', '2009-08-20', 'Magelang', 'PD Prasetyo Januar', 36.7, 121, 'PD Prasetyo Januar - Magelang', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3321, 'T. Mujur Siregar, S.E.I', 'L', '2020-02-07', 'Padang', 'Perum Uyainah Usada', 69.1, 154, 'Perum Uyainah Usada - Padang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3322, 'Pangeran Kusmawati', 'P', '2017-10-14', 'Surakarta', 'PD Utami', 36, 143, 'PD Utami - Surakarta', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3323, 'Nabila Uyainah', 'L', '2015-07-18', 'Tidore Kepulauan', 'Perum Natsir Narpati Tbk', 66.2, 160, 'Perum Natsir Narpati Tbk - Tidore Kepulauan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3324, 'Rahman Waskita', 'P', '2019-11-26', 'Cilegon', 'PD Lazuardi Dongoran', 49.9, 120, 'PD Lazuardi Dongoran - Cilegon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3325, 'Kemal Mandasari', 'P', '2018-03-19', 'Parepare', 'CV Kuswandari (Persero) Tbk', 34.6, 172, 'CV Kuswandari (Persero) Tbk - Parepare', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3326, 'Dt. Karma Haryanti', 'P', '2020-06-25', 'Kediri', 'Perum Budiman Kusmawati (Persero) Tbk', 60.5, 160, 'Perum Budiman Kusmawati (Persero) Tbk - Kediri', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3327, 'Devi Hutapea', 'P', '2011-11-15', 'Tarakan', 'PD Setiawan Haryanti', 78.2, 125, 'PD Setiawan Haryanti - Tarakan', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3328, 'Sutan Imam Manullang, S.E.', 'P', '2019-08-22', 'Surabaya', 'Perum Manullang Tbk', 78.9, 155, 'Perum Manullang Tbk - Surabaya', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3329, 'Gaiman Simbolon', 'L', '2017-12-06', 'Bandung', 'PT Prabowo Handayani (Persero) Tbk', 86.6, 181, 'PT Prabowo Handayani (Persero) Tbk - Bandung', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3330, 'Gasti Mayasari', 'P', '2009-01-19', 'Ambon', 'PD Usamah Nasyidah Tbk', 35.8, 184, 'PD Usamah Nasyidah Tbk - Ambon', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3331, 'Lulut Hastuti', 'P', '2020-01-22', 'Bontang', 'PT Zulkarnain Novitasari Tbk', 72.6, 159, 'PT Zulkarnain Novitasari Tbk - Bontang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3332, 'Cindy Halim', 'P', '2002-09-29', 'Dumai', 'Perum Adriansyah', 89.5, 121, 'Perum Adriansyah - Dumai', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3333, 'Silvia Sihotang', 'L', '2009-01-08', 'Solok', 'PT Wasita Mandala Tbk', 35.4, 189, 'PT Wasita Mandala Tbk - Solok', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3334, 'Wisnu Laksita, M.Kom.', 'P', '2018-03-20', 'Samarinda', 'UD Dongoran Maryadi Tbk', 85.2, 186, 'UD Dongoran Maryadi Tbk - Samarinda', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3335, 'Aisyah Uwais', 'L', '2019-12-18', 'Palembang', 'PD Wibowo Permata (Persero) Tbk', 74.2, 146, 'PD Wibowo Permata (Persero) Tbk - Palembang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3336, 'Irsad Firgantoro, S.Pd', 'P', '2015-02-09', 'Banjarbaru', 'Perum Rahayu Tbk', 74.5, 146, 'Perum Rahayu Tbk - Banjarbaru', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3337, 'Restu Sihotang', 'P', '2009-11-07', 'Metro', 'CV Sihombing (Persero) Tbk', 72.8, 174, 'CV Sihombing (Persero) Tbk - Metro', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3338, 'Gandi Samosir, S.Gz', 'P', '2018-06-08', 'Pontianak', 'PD Hutapea Jailani', 66.8, 139, 'PD Hutapea Jailani - Pontianak', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3339, 'Jamalia Wibowo', 'P', '2014-06-08', 'Ternate', 'Perum Sihombing (Persero) Tbk', 33.8, 126, 'Perum Sihombing (Persero) Tbk - Ternate', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3340, 'Gamanto Marbun, M.TI.', 'L', '1996-07-21', 'Kota Administrasi Jakarta Timur', 'CV Prasasta Pranowo', 81.6, 186, 'CV Prasasta Pranowo - Kota Administrasi Jakarta Timur', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3341, 'Unggul Mansur, S.E.', 'P', '2014-02-13', 'Ternate', 'PD Putra (Persero) Tbk', 64.1, 143, 'PD Putra (Persero) Tbk - Ternate', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3342, 'Drs. Gilang Pertiwi, M.Pd', 'P', '2011-09-24', 'Cirebon', 'Perum Tarihoran (Persero) Tbk', 55.8, 163, 'Perum Tarihoran (Persero) Tbk - Cirebon', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3343, 'Tgk. Artawan Ardianto', 'L', '2004-05-08', 'Tebingtinggi', 'PT Namaga Tbk', 66.8, 174, 'PT Namaga Tbk - Tebingtinggi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3344, 'Vicky Ramadan', 'L', '2003-04-23', 'Parepare', 'Perum Pradipta Mahendra', 45.7, 165, 'Perum Pradipta Mahendra - Parepare', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3345, 'Ana Astuti', 'L', '2011-06-08', 'Depok', 'Perum Yulianti Yuniar', 31.7, 140, 'Perum Yulianti Yuniar - Depok', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3346, 'R.A. Zahra Agustina, S.Ked', 'P', '2007-03-09', 'Bima', 'PD Iswahyudi Hasanah', 62.5, 184, 'PD Iswahyudi Hasanah - Bima', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3347, 'Ifa Melani', 'L', '1997-10-11', 'Yogyakarta', 'UD Iswahyudi', 81.4, 188, 'UD Iswahyudi - Yogyakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3348, 'T. Irsad Wasita, M.TI.', 'P', '1995-10-22', 'Padang', 'Perum Hassanah', 90, 178, 'Perum Hassanah - Padang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3349, 'Indah Haryanti, S.H.', 'P', '2004-05-11', 'Balikpapan', 'PD Saputra', 88, 140, 'PD Saputra - Balikpapan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3350, 'Rusman Wibisono', 'L', '2005-05-22', 'Bandung', 'Perum Budiman Purnawati (Persero) Tbk', 65.8, 157, 'Perum Budiman Purnawati (Persero) Tbk - Bandung', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3351, 'H. Laswi Yolanda, M.Pd', 'P', '2019-12-19', 'Palu', 'CV Wahyuni (Persero) Tbk', 52.4, 163, 'CV Wahyuni (Persero) Tbk - Palu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3352, 'Drs. Tiara Utama, S.H.', 'P', '2018-02-04', 'Ambon', 'Perum Mulyani', 65.4, 165, 'Perum Mulyani - Ambon', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3353, 'Hari Yuliarti', 'L', '2015-01-01', 'Pangkalpinang', 'UD Prasetyo (Persero) Tbk', 75.1, 163, 'UD Prasetyo (Persero) Tbk - Pangkalpinang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3354, 'Darmana Sihombing, S.H.', 'P', '1999-04-20', 'Cilegon', 'CV Yuniar Hakim Tbk', 44, 190, 'CV Yuniar Hakim Tbk - Cilegon', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3355, 'Puti Julia Manullang, M.Farm', 'P', '2015-03-30', 'Kendari', 'CV Putra Suryono', 38.2, 161, 'CV Putra Suryono - Kendari', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3356, 'Yunita Simbolon', 'P', '2005-08-26', 'Surabaya', 'PT Waskita', 58.3, 183, 'PT Waskita - Surabaya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3357, 'Sadina Budiman', 'L', '2018-02-27', 'Surakarta', 'Perum Permadi Sitorus', 30.3, 180, 'Perum Permadi Sitorus - Surakarta', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3358, 'Aswani Suartini', 'P', '2018-05-29', 'Tegal', 'PT Prakasa Jailani', 38.2, 164, 'PT Prakasa Jailani - Tegal', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3359, 'Wira Wijayanti', 'L', '2010-08-04', 'Semarang', 'UD Wahyuni Tbk', 76.9, 181, 'UD Wahyuni Tbk - Semarang', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3360, 'Ina Januar', 'L', '2001-05-14', 'Bontang', 'CV Melani Sitompul Tbk', 56.2, 137, 'CV Melani Sitompul Tbk - Bontang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3361, 'Gina Nurdiyanti, M.M.', 'P', '2019-10-24', 'Mataram', 'Perum Mandasari', 35.7, 186, 'Perum Mandasari - Mataram', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3362, 'Septi Hutasoit', 'P', '2013-08-02', 'Parepare', 'CV Lailasari Tbk', 65.1, 132, 'CV Lailasari Tbk - Parepare', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3363, 'Ilsa Manullang', 'P', '2002-12-28', 'Probolinggo', 'PD Hutasoit (Persero) Tbk', 87.9, 180, 'PD Hutasoit (Persero) Tbk - Probolinggo', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3364, 'Victoria Permata', 'P', '2012-03-23', 'Metro', 'UD Gunawan Aryani', 69.7, 148, 'UD Gunawan Aryani - Metro', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3365, 'Galur Astuti', 'L', '2020-06-19', 'Solok', 'UD Pranowo Tbk', 82.5, 122, 'UD Pranowo Tbk - Solok', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3366, 'Diana Pangestu, S.Gz', 'L', '2017-11-05', 'Madiun', 'PT Widodo', 58, 120, 'PT Widodo - Madiun', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3367, 'Hj. Yani Hakim, M.Kom.', 'L', '2014-05-10', 'Jayapura', 'UD Lestari Mayasari Tbk', 62.9, 142, 'UD Lestari Mayasari Tbk - Jayapura', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3368, 'Endra Prasetyo', 'P', '2014-10-31', 'Kota Administrasi Jakarta Barat', 'PT Haryanti Puspita Tbk', 52.2, 125, 'PT Haryanti Puspita Tbk - Kota Administrasi Jakarta Barat', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3369, 'Dt. Umay Najmudin, S.Farm', 'P', '2014-09-22', 'Sibolga', 'PD Permata Usada Tbk', 81.3, 183, 'PD Permata Usada Tbk - Sibolga', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3370, 'T. Jaswadi Januar', 'L', '2015-06-05', 'Palembang', 'PD Sihombing Mansur Tbk', 50.5, 150, 'PD Sihombing Mansur Tbk - Palembang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3371, 'drg. Ibrani Prabowo, S.Pd', 'L', '2008-10-26', 'Bandung', 'CV Usamah Tbk', 49.9, 137, 'CV Usamah Tbk - Bandung', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3372, 'Ade Megantara', 'P', '2009-06-22', 'Purwokerto', 'Perum Maryadi Permata Tbk', 41.7, 166, 'Perum Maryadi Permata Tbk - Purwokerto', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3373, 'Cut Sari Winarno, S.H.', 'L', '2019-08-12', 'Tebingtinggi', 'CV Wacana Puspita', 43.6, 148, 'CV Wacana Puspita - Tebingtinggi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3374, 'Nilam Mangunsong, S.Ked', 'L', '2018-02-10', 'Payakumbuh', 'Perum Anggriawan Wahyudin', 60.2, 135, 'Perum Anggriawan Wahyudin - Payakumbuh', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3375, 'R.A. Puput Putra, S.Sos', 'P', '2010-09-18', 'Tangerang', 'Perum Hidayanto Hidayanto', 55, 160, 'Perum Hidayanto Hidayanto - Tangerang', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3376, 'Kambali Rahayu', 'L', '2016-03-19', 'Palopo', 'CV Latupono Hutasoit', 31.4, 158, 'CV Latupono Hutasoit - Palopo', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3377, 'Drs. Edi Prasasta, M.TI.', 'P', '2017-07-26', 'Pasuruan', 'UD Andriani Maryati', 58, 170, 'UD Andriani Maryati - Pasuruan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3378, 'R.M. Kariman Prayoga', 'P', '2018-02-14', 'Padang', 'CV Nasyiah', 62, 156, 'CV Nasyiah - Padang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3379, 'Digdaya Mansur', 'P', '2016-06-30', 'Padang Sidempuan', 'PT Prakasa (Persero) Tbk', 73, 140, 'PT Prakasa (Persero) Tbk - Padang Sidempuan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3380, 'Ella Rahmawati', 'P', '2020-05-27', 'Palangkaraya', 'PT Halimah', 36.1, 176, 'PT Halimah - Palangkaraya', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3381, 'Cahyanto Pratama', 'P', '2008-12-10', 'Bekasi', 'CV Pradana Thamrin', 48.2, 150, 'CV Pradana Thamrin - Bekasi', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3382, 'Kezia Hasanah', 'P', '2020-05-05', 'Depok', 'CV Purwanti Tbk', 41.8, 147, 'CV Purwanti Tbk - Depok', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3383, 'R. Clara Laksita', 'P', '1997-07-25', 'Tasikmalaya', 'CV Wijaya Safitri', 79.1, 120, 'CV Wijaya Safitri - Tasikmalaya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3384, 'Sabrina Setiawan', 'L', '2008-10-24', 'Serang', 'Perum Jailani Nasyiah Tbk', 82.5, 131, 'Perum Jailani Nasyiah Tbk - Serang', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3385, 'Hasim Lestari', 'L', '2015-02-26', 'Pariaman', 'PT Hidayanto Utami', 76.4, 145, 'PT Hidayanto Utami - Pariaman', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3386, 'Warji Yolanda', 'P', '2010-03-15', 'Ambon', 'CV Napitupulu (Persero) Tbk', 72.6, 186, 'CV Napitupulu (Persero) Tbk - Ambon', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3387, 'Novi Pratama', 'L', '2020-03-08', 'Magelang', 'CV Halimah (Persero) Tbk', 40.5, 160, 'CV Halimah (Persero) Tbk - Magelang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3388, 'Unjani Winarno', 'P', '2020-05-11', 'Kota Administrasi Jakarta Utara', 'PT Hutagalung Andriani', 44.8, 164, 'PT Hutagalung Andriani - Kota Administrasi Jakarta Utara', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3389, 'Jagapati Kuswoyo', 'L', '2008-12-16', 'Salatiga', 'Perum Prabowo Pranowo', 86.2, 189, 'Perum Prabowo Pranowo - Salatiga', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3390, 'Shania Thamrin, S.Gz', 'L', '2003-08-12', 'Salatiga', 'UD Dongoran Wasita', 82.2, 144, 'UD Dongoran Wasita - Salatiga', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3391, 'Maida Budiyanto', 'P', '1998-02-25', 'Kota Administrasi Jakarta Barat', 'CV Astuti Uwais', 32.6, 128, 'CV Astuti Uwais - Kota Administrasi Jakarta Barat', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3392, 'R. Hana Hastuti, S.Pt', 'L', '2014-07-28', 'Pagaralam', 'PT Mahendra Namaga (Persero) Tbk', 57.3, 137, 'PT Mahendra Namaga (Persero) Tbk - Pagaralam', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3393, 'Tantri Prasasta', 'P', '2017-10-02', 'Pontianak', 'UD Usada', 48.2, 125, 'UD Usada - Pontianak', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3394, 'Fitriani Siregar', 'P', '1996-11-11', 'Yogyakarta', 'PD Hastuti (Persero) Tbk', 83, 185, 'PD Hastuti (Persero) Tbk - Yogyakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3395, 'Margana Wibowo', 'L', '2019-09-21', 'Kotamobagu', 'PD Winarno Natsir', 32, 186, 'PD Winarno Natsir - Kotamobagu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3396, 'Ulya Andriani', 'L', '2000-07-01', 'Semarang', 'Perum Simbolon Tbk', 89, 175, 'Perum Simbolon Tbk - Semarang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3397, 'Faizah Suartini, M.Ak', 'P', '2017-08-30', 'Yogyakarta', 'PT Natsir Ardianto', 50, 153, 'PT Natsir Ardianto - Yogyakarta', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3398, 'Kusuma Rahayu', 'L', '2017-10-10', 'Banjarbaru', 'UD Puspita (Persero) Tbk', 80.8, 161, 'UD Puspita (Persero) Tbk - Banjarbaru', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3399, 'R.A. Hasna Maheswara', 'P', '2014-04-03', 'Palopo', 'Perum Hidayat Puspita', 64.6, 141, 'Perum Hidayat Puspita - Palopo', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3400, 'Kasiran Saragih', 'L', '2008-11-28', 'Tanjungpinang', 'PT Narpati', 89.5, 147, 'PT Narpati - Tanjungpinang', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3401, 'Cawisono Yulianti, M.Pd', 'L', '2018-07-06', 'Lhokseumawe', 'UD Wulandari Tampubolon Tbk', 79.6, 150, 'UD Wulandari Tampubolon Tbk - Lhokseumawe', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3402, 'Anggabaya Handayani', 'L', '2011-08-24', 'Surakarta', 'UD Nurdiyanti Dabukke', 32.3, 121, 'UD Nurdiyanti Dabukke - Surakarta', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3403, 'Eka Dongoran', 'P', '2012-04-11', 'Balikpapan', 'CV Sirait Narpati Tbk', 45.3, 122, 'CV Sirait Narpati Tbk - Balikpapan', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3404, 'Ade Sinaga', 'P', '2009-07-05', 'Sorong', 'PD Zulkarnain Haryanto', 55.2, 156, 'PD Zulkarnain Haryanto - Sorong', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3405, 'Sarah Zulkarnain', 'P', '2009-03-06', 'Prabumulih', 'PD Permadi Zulkarnain Tbk', 71, 184, 'PD Permadi Zulkarnain Tbk - Prabumulih', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3406, 'Eluh Sinaga', 'P', '2020-06-09', 'Cimahi', 'CV Hutapea Waluyo (Persero) Tbk', 60.6, 129, 'CV Hutapea Waluyo (Persero) Tbk - Cimahi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3407, 'Bagus Puspasari', 'L', '2020-02-11', 'Tangerang Selatan', 'UD Puspita Samosir', 44.7, 122, 'UD Puspita Samosir - Tangerang Selatan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3408, 'Sarah Uyainah', 'P', '2020-05-12', 'Pekalongan', 'PT Saptono Handayani', 61.9, 143, 'PT Saptono Handayani - Pekalongan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3409, 'Cut Zizi Salahudin, S.Kom', 'L', '2017-12-10', 'Samarinda', 'CV Halimah', 61.1, 164, 'CV Halimah - Samarinda', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3410, 'Titin Saragih', 'L', '2015-05-02', 'Yogyakarta', 'UD Simanjuntak', 72.1, 127, 'UD Simanjuntak - Yogyakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3411, 'Sutan Ilyas Suartini', 'P', '2013-11-12', 'Tasikmalaya', 'Perum Yuliarti Permata (Persero) Tbk', 44.3, 134, 'Perum Yuliarti Permata (Persero) Tbk - Tasikmalaya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3412, 'R.A. Calista Hutagalung, M.Pd', 'P', '2013-12-09', 'Pontianak', 'UD Januar Farida', 38.7, 150, 'UD Januar Farida - Pontianak', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3413, 'Drs. Dian Kusumo', 'P', '2015-06-12', 'Jambi', 'PT Suryono Oktaviani', 68.3, 123, 'PT Suryono Oktaviani - Jambi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3414, 'Winda Pranowo, M.Farm', 'P', '2018-03-28', 'Blitar', 'Perum Rahimah Puspita', 48.8, 169, 'Perum Rahimah Puspita - Blitar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3415, 'Kasusra Hasanah', 'P', '2006-02-17', 'Ternate', 'Perum Napitupulu Saptono (Persero) Tbk', 53.9, 137, 'Perum Napitupulu Saptono (Persero) Tbk - Ternate', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3416, 'Hj. Puput Maulana, M.Farm', 'P', '2016-05-21', 'Batu', 'PD Iswahyudi Tbk', 58.1, 121, 'PD Iswahyudi Tbk - Batu', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3417, 'Queen Purwanti', 'L', '2004-08-25', 'Magelang', 'Perum Saputra Tbk', 62.9, 168, 'Perum Saputra Tbk - Magelang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3418, 'Ella Anggriawan', 'P', '1996-05-17', 'Sibolga', 'CV Tarihoran Jailani', 33.2, 155, 'CV Tarihoran Jailani - Sibolga', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3419, 'Citra Hakim', 'L', '2017-07-26', 'Kota Administrasi Jakarta Pusat', 'PT Rahmawati Januar', 56, 172, 'PT Rahmawati Januar - Kota Administrasi Jakarta Pusat', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3420, 'Hj. Agnes Budiman, M.M.', 'L', '2014-08-09', 'Kota Administrasi Jakarta Pusat', 'CV Yulianti', 65.4, 165, 'CV Yulianti - Kota Administrasi Jakarta Pusat', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3421, 'Muni Nugroho, S.IP', 'P', '2017-10-10', 'Medan', 'UD Sirait Waskita', 51.9, 155, 'UD Sirait Waskita - Medan', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3422, 'dr. Jasmin Zulaika', 'P', '2009-02-14', 'Tegal', 'Perum Lailasari Hariyah (Persero) Tbk', 70.1, 167, 'Perum Lailasari Hariyah (Persero) Tbk - Tegal', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3423, 'Febi Budiman', 'L', '2017-09-07', 'Ambon', 'CV Haryanto Prasetyo Tbk', 52.5, 148, 'CV Haryanto Prasetyo Tbk - Ambon', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3424, 'Vanya Usamah', 'P', '2020-01-29', 'Kupang', 'UD Gunarto (Persero) Tbk', 89.3, 189, 'UD Gunarto (Persero) Tbk - Kupang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3425, 'Lidya Wacana', 'P', '2005-02-24', 'Padang', 'CV Andriani (Persero) Tbk', 58.7, 123, 'CV Andriani (Persero) Tbk - Padang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3426, 'Pranata Waskita', 'L', '2019-07-08', 'Pagaralam', 'Perum Tarihoran Tbk', 33, 144, 'Perum Tarihoran Tbk - Pagaralam', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3427, 'Garan Andriani, S.H.', 'L', '2016-04-17', 'Yogyakarta', 'Perum Kuswandari Siregar', 70.9, 171, 'Perum Kuswandari Siregar - Yogyakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3428, 'Ira Latupono, S.Kom', 'P', '2020-05-15', 'Tangerang', 'PD Anggriawan Zulkarnain Tbk', 43, 159, 'PD Anggriawan Zulkarnain Tbk - Tangerang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3429, 'Vanesa Sihombing', 'L', '2019-08-14', 'Balikpapan', 'PD Suryono Hakim Tbk', 80.1, 144, 'PD Suryono Hakim Tbk - Balikpapan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3430, 'Eli Ardianto', 'L', '2019-10-17', 'Pekalongan', 'CV Yuliarti Manullang Tbk', 68.7, 169, 'CV Yuliarti Manullang Tbk - Pekalongan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3431, 'Puji Namaga', 'L', '2014-12-09', 'Tasikmalaya', 'Perum Lailasari Marbun', 89.2, 157, 'Perum Lailasari Marbun - Tasikmalaya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3432, 'Tasnim Puspita', 'P', '2019-09-15', 'Palopo', 'CV Halim', 82.1, 147, 'CV Halim - Palopo', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3433, 'drg. Waluyo Nainggolan, M.M.', 'P', '2019-12-31', 'Cirebon', 'PD Setiawan Nababan', 60.2, 146, 'PD Setiawan Nababan - Cirebon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3434, 'Panji Najmudin', 'L', '2010-09-01', 'Bogor', 'PD Hasanah', 81, 134, 'PD Hasanah - Bogor', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3435, 'Nrima Nugroho', 'L', '2013-11-22', 'Bima', 'PD Sudiati', 84.3, 155, 'PD Sudiati - Bima', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3436, 'H. Nardi Napitupulu', 'L', '2018-01-17', 'Padangpanjang', 'CV Dongoran Tbk', 41.4, 125, 'CV Dongoran Tbk - Padangpanjang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3437, 'Laila Pertiwi, S.E.I', 'P', '2010-05-25', 'Tual', 'CV Mayasari', 45.6, 157, 'CV Mayasari - Tual', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3438, 'Naradi Wacana', 'P', '2014-10-16', 'Palembang', 'Perum Nasyidah Tbk', 84.6, 190, 'Perum Nasyidah Tbk - Palembang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3439, 'Jumadi Putra', 'P', '2010-03-01', 'Banda Aceh', 'PT Handayani Yuniar', 53.7, 181, 'PT Handayani Yuniar - Banda Aceh', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3440, 'Johan Sitorus', 'P', '2020-02-27', 'Semarang', 'PD Gunarto Tbk', 55.8, 137, 'PD Gunarto Tbk - Semarang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3441, 'Bakianto Agustina', 'L', '2001-07-02', 'Jambi', 'PD Astuti Thamrin (Persero) Tbk', 77.8, 184, 'PD Astuti Thamrin (Persero) Tbk - Jambi', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3442, 'Salimah Wahyudin', 'L', '1998-05-06', 'Yogyakarta', 'UD Puspita Tbk', 45.7, 142, 'UD Puspita Tbk - Yogyakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3443, 'Maria Hutasoit, M.M.', 'L', '2018-01-04', 'Mataram', 'Perum Utama (Persero) Tbk', 68.8, 122, 'Perum Utama (Persero) Tbk - Mataram', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3444, 'Maida Anggraini', 'L', '2014-04-11', 'Tidore Kepulauan', 'PT Januar (Persero) Tbk', 32.1, 160, 'PT Januar (Persero) Tbk - Tidore Kepulauan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3445, 'Ivan Pranowo, M.M.', 'L', '2020-02-25', 'Tanjungpinang', 'UD Melani Tbk', 37.3, 186, 'UD Melani Tbk - Tanjungpinang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3446, 'Hj. Zaenab Lazuardi, M.Pd', 'P', '2020-02-28', 'Mataram', 'CV Setiawan Firmansyah', 68.2, 128, 'CV Setiawan Firmansyah - Mataram', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3447, 'Nilam Rahmawati', 'L', '2019-10-14', 'Sabang', 'UD Sihombing Nainggolan (Persero) Tbk', 45.2, 184, 'UD Sihombing Nainggolan (Persero) Tbk - Sabang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3448, 'Ulya Yuniar, M.TI.', 'P', '2014-06-02', 'Palangkaraya', 'Perum Rahayu Prasasta', 68.2, 134, 'Perum Rahayu Prasasta - Palangkaraya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3449, 'Yuni Laksita', 'L', '2019-07-07', 'Sibolga', 'PT Nugroho Tbk', 45.1, 167, 'PT Nugroho Tbk - Sibolga', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3450, 'Ajimat Farida, S.Farm', 'P', '2019-09-05', 'Bandar Lampung', 'PT Prastuti Tbk', 44.6, 154, 'PT Prastuti Tbk - Bandar Lampung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3451, 'Vinsen Pudjiastuti, M.Kom.', 'P', '2019-12-21', 'Salatiga', 'CV Budiman Mardhiyah', 80.2, 131, 'CV Budiman Mardhiyah - Salatiga', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3452, 'Oliva Kurniawan', 'P', '2014-11-08', 'Tasikmalaya', 'PD Mulyani Tbk', 82.9, 122, 'PD Mulyani Tbk - Tasikmalaya', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3453, 'Gandi Kusumo', 'L', '2019-07-12', 'Ambon', 'Perum Sihombing Lazuardi (Persero) Tbk', 32.3, 138, 'Perum Sihombing Lazuardi (Persero) Tbk - Ambon', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35');
INSERT INTO `daftar_peserta` (`id`, `nama`, `jenis_kelamin`, `tanggal_lahir`, `tempat_lahir`, `nama_sekolah`, `berat_badan`, `tinggi_badan`, `kontingen`, `kategori_umur`, `jenis_kompetisi`, `kategori_tanding`, `imported_at`) VALUES
(3454, 'Puti Fathonah Fujiati, S.Gz', 'L', '2005-03-31', 'Sorong', 'PT Salahudin Hutapea Tbk', 56.8, 148, 'PT Salahudin Hutapea Tbk - Sorong', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3455, 'Puti Dian Gunarto, S.E.', 'P', '2013-09-30', 'Tidore Kepulauan', 'CV Mahendra', 50.4, 127, 'CV Mahendra - Tidore Kepulauan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3456, 'Puti Zelda Pertiwi, M.Pd', 'L', '2019-12-12', 'Metro', 'UD Halimah Wulandari', 83.8, 127, 'UD Halimah Wulandari - Metro', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3457, 'Cagak Suryono', 'P', '2015-10-20', 'Makassar', 'CV Pratama Tbk', 75.1, 149, 'CV Pratama Tbk - Makassar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3458, 'Ir. Kemal Januar', 'L', '2017-07-31', 'Palangkaraya', 'Perum Novitasari Tarihoran', 67.4, 152, 'Perum Novitasari Tarihoran - Palangkaraya', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3459, 'Damu Agustina, S.Pt', 'P', '2018-02-03', 'Pagaralam', 'UD Maheswara Prabowo', 46.7, 174, 'UD Maheswara Prabowo - Pagaralam', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3460, 'R.A. Tari Prayoga', 'L', '2019-09-10', 'Palu', 'Perum Kurniawan Situmorang (Persero) Tbk', 36.5, 185, 'Perum Kurniawan Situmorang (Persero) Tbk - Palu', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3461, 'Salman Manullang', 'P', '2017-09-01', 'Bima', 'PT Pranowo Tbk', 51.9, 171, 'PT Pranowo Tbk - Bima', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3462, 'Drs. Ade Tampubolon', 'L', '2011-01-27', 'Kota Administrasi Jakarta Selatan', 'PD Siregar Utama', 52.7, 125, 'PD Siregar Utama - Kota Administrasi Jakarta Selatan', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3463, 'dr. Padmi Astuti, S.I.Kom', 'P', '2018-01-04', 'Denpasar', 'Perum Prasetyo', 77.5, 178, 'Perum Prasetyo - Denpasar', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3464, 'Pranata Maulana', 'P', '1999-07-15', 'Lubuklinggau', 'UD Wibowo Tamba Tbk', 80.8, 132, 'UD Wibowo Tamba Tbk - Lubuklinggau', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3465, 'Winda Dongoran', 'L', '2019-10-28', 'Pangkalpinang', 'Perum Halimah (Persero) Tbk', 84.3, 185, 'Perum Halimah (Persero) Tbk - Pangkalpinang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3466, 'Genta Pratiwi', 'P', '2001-12-07', 'Payakumbuh', 'Perum Nashiruddin (Persero) Tbk', 65.2, 158, 'Perum Nashiruddin (Persero) Tbk - Payakumbuh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3467, 'Putri Mansur', 'P', '2017-08-12', 'Solok', 'PT Prabowo Tbk', 34.8, 120, 'PT Prabowo Tbk - Solok', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3468, 'Dr. Nardi Pradana, S.Farm', 'L', '2018-04-23', 'Jayapura', 'UD Zulaika Uwais Tbk', 78.4, 177, 'UD Zulaika Uwais Tbk - Jayapura', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3469, 'Harimurti Prasasta, S.IP', 'L', '2012-01-04', 'Serang', 'Perum Haryanto Sihotang', 65, 168, 'Perum Haryanto Sihotang - Serang', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3470, 'Ajeng Zulkarnain', 'P', '2009-10-20', 'Jambi', 'Perum Winarno', 77.3, 129, 'Perum Winarno - Jambi', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3471, 'Tiara Hakim', 'P', '2014-02-20', 'Jayapura', 'Perum Widiastuti Santoso (Persero) Tbk', 36.3, 159, 'Perum Widiastuti Santoso (Persero) Tbk - Jayapura', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3472, 'R.A. Pia Mayasari, S.Farm', 'L', '2018-07-05', 'Parepare', 'UD Januar Haryanto', 55.8, 136, 'UD Januar Haryanto - Parepare', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3473, 'Cut Padmi Firgantoro, M.Kom.', 'L', '2014-01-05', 'Tomohon', 'UD Sitompul', 76.6, 143, 'UD Sitompul - Tomohon', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3474, 'Bella Zulaika', 'P', '2019-08-31', 'Banjarmasin', 'UD Prasasta', 32.9, 126, 'UD Prasasta - Banjarmasin', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3475, 'Gatot Hariyah', 'L', '2017-09-15', 'Dumai', 'PD Lestari Sudiati', 62.7, 123, 'PD Lestari Sudiati - Dumai', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3476, 'Edison Sitorus', 'P', '2012-02-05', 'Mataram', 'Perum Manullang Pertiwi', 51.7, 151, 'Perum Manullang Pertiwi - Mataram', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3477, 'Tgk. Alambana Hassanah, M.Kom.', 'P', '2018-01-18', 'Tomohon', 'Perum Firmansyah', 32.2, 128, 'Perum Firmansyah - Tomohon', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3478, 'Akarsana Sirait', 'L', '2020-03-22', 'Bima', 'UD Susanti Andriani Tbk', 63.1, 127, 'UD Susanti Andriani Tbk - Bima', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3479, 'Kanda Rahayu, M.M.', 'P', '2020-01-28', 'Tegal', 'UD Fujiati Laksmiwati Tbk', 82.9, 179, 'UD Fujiati Laksmiwati Tbk - Tegal', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3480, 'Puti Siska Budiman, S.Farm', 'L', '2019-12-15', 'Bekasi', 'UD Sitorus', 77.7, 157, 'UD Sitorus - Bekasi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3481, 'Cinta Zulaika', 'L', '2010-05-25', 'Magelang', 'PT Pranowo', 55.2, 166, 'PT Pranowo - Magelang', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3482, 'Latika Suartini', 'P', '2004-06-27', 'Palembang', 'CV Uwais Tbk', 49.7, 135, 'CV Uwais Tbk - Palembang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3483, 'Nurul Sihotang', 'L', '2015-10-28', 'Sabang', 'CV Jailani', 49.1, 177, 'CV Jailani - Sabang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3484, 'Ade Hutagalung', 'P', '2019-12-20', 'Tangerang', 'PD Siregar (Persero) Tbk', 43.3, 153, 'PD Siregar (Persero) Tbk - Tangerang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3485, 'Garang Uyainah', 'P', '1998-07-23', 'Cilegon', 'PT Zulkarnain Iswahyudi', 63.1, 161, 'PT Zulkarnain Iswahyudi - Cilegon', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3486, 'Wulan Rajasa', 'L', '2015-05-31', 'Binjai', 'Perum Suryatmi Saputra', 70.9, 173, 'Perum Suryatmi Saputra - Binjai', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3487, 'Cut Putri Budiyanto, S.Kom', 'P', '2020-04-29', 'Tangerang Selatan', 'PD Narpati Tbk', 46.1, 135, 'PD Narpati Tbk - Tangerang Selatan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3488, 'Hesti Yuniar', 'P', '2015-03-24', 'Yogyakarta', 'PD Rajata Namaga Tbk', 40.4, 124, 'PD Rajata Namaga Tbk - Yogyakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3489, 'Dr. Nugraha Zulaika, S.Gz', 'P', '1997-09-12', 'Langsa', 'CV Astuti Tbk', 86.8, 169, 'CV Astuti Tbk - Langsa', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3490, 'Cut Ira Gunawan', 'L', '2001-07-17', 'Kota Administrasi Jakarta Utara', 'PD Haryanti Tbk', 65.3, 189, 'PD Haryanti Tbk - Kota Administrasi Jakarta Utara', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3491, 'Anita Maryati', 'P', '2009-12-01', 'Tangerang', 'Perum Tampubolon (Persero) Tbk', 62.2, 175, 'Perum Tampubolon (Persero) Tbk - Tangerang', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3492, 'Utama Widiastuti', 'P', '2020-05-21', 'Jambi', 'PD Siregar Tbk', 61.1, 132, 'PD Siregar Tbk - Jambi', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3493, 'Dr. Syahrini Saputra', 'P', '2019-09-27', 'Bandung', 'UD Sihombing', 63, 159, 'UD Sihombing - Bandung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3494, 'Sabrina Puspasari', 'L', '2000-01-18', 'Meulaboh', 'UD Agustina Fujiati', 53.5, 152, 'UD Agustina Fujiati - Meulaboh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3495, 'Rini Saputra', 'L', '1999-12-03', 'Meulaboh', 'CV Haryanti', 75.6, 180, 'CV Haryanti - Meulaboh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3496, 'Tiara Widodo', 'L', '1996-10-19', 'Tarakan', 'UD Setiawan Tbk', 58.3, 129, 'UD Setiawan Tbk - Tarakan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3497, 'dr. Caturangga Kurniawan', 'L', '2000-06-23', 'Banjarbaru', 'UD Laksmiwati Rajata', 67.8, 162, 'UD Laksmiwati Rajata - Banjarbaru', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3498, 'Nilam Sitompul, S.Pd', 'P', '2000-06-02', 'Bontang', 'UD Mandasari', 89.4, 124, 'UD Mandasari - Bontang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3499, 'Eman Budiman', 'P', '2017-12-12', 'Payakumbuh', 'Perum Hardiansyah Mayasari (Persero) Tbk', 52, 169, 'Perum Hardiansyah Mayasari (Persero) Tbk - Payakumbuh', 'USIA DINI 2', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3500, 'Iriana Maulana', 'L', '2017-07-22', 'Serang', 'PD Pratama Astuti', 38.8, 183, 'PD Pratama Astuti - Serang', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3501, 'Mustika Damanik', 'P', '2020-01-16', 'Metro', 'PT Jailani Hassanah', 67.7, 170, 'PT Jailani Hassanah - Metro', 'USIA DINI 1', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3502, 'Raden Saptono', 'L', '1998-01-01', 'Banda Aceh', 'CV Putra', 75.8, 125, 'CV Putra - Banda Aceh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3503, 'Bajragin Widodo', 'L', '1997-04-23', 'Tual', 'UD Narpati Tbk', 58.4, 126, 'UD Narpati Tbk - Tual', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3504, 'Mila Padmasari', 'P', '2014-01-16', 'Batu', 'UD Wulandari Tbk', 75.1, 150, 'UD Wulandari Tbk - Batu', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3505, 'Tgk. Yuni Riyanti', 'P', '1995-11-13', 'Subulussalam', 'PD Hassanah Nainggolan', 61.1, 131, 'PD Hassanah Nainggolan - Subulussalam', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3506, 'drg. Kuncara Lestari', 'L', '2008-10-18', 'Pariaman', 'UD Pratiwi', 58.5, 174, 'UD Pratiwi - Pariaman', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3507, 'Talia Nababan', 'P', '2010-07-20', 'Lubuklinggau', 'UD Nugroho (Persero) Tbk', 70.2, 151, 'UD Nugroho (Persero) Tbk - Lubuklinggau', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3508, 'Viktor Wijaya', 'L', '2016-03-23', 'Bandung', 'PD Utami', 76, 177, 'PD Utami - Bandung', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3509, 'Kawaca Susanti', 'P', '2017-12-26', 'Pekanbaru', 'UD Irawan Kurniawan Tbk', 69.2, 138, 'UD Irawan Kurniawan Tbk - Pekanbaru', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3510, 'Wulan Wastuti', 'P', '2000-08-11', 'Tomohon', 'PD Utama', 63.3, 121, 'PD Utama - Tomohon', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3511, 'drg. Qori Yuliarti, S.Gz', 'P', '2018-05-28', 'Pontianak', 'PT Siregar Tbk', 66.5, 180, 'PT Siregar Tbk - Pontianak', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3512, 'Natalia Wijaya', 'L', '2016-03-07', 'Kendari', 'Perum Tampubolon', 66.8, 178, 'Perum Tampubolon - Kendari', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3513, 'Kenari Maryadi, M.TI.', 'L', '2017-10-12', 'Sawahlunto', 'Perum Sitompul Tbk', 35.7, 174, 'Perum Sitompul Tbk - Sawahlunto', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3514, 'Gina Wibowo', 'P', '2017-08-04', 'Pariaman', 'PT Winarno Marbun', 42.1, 169, 'PT Winarno Marbun - Pariaman', 'USIA DINI 2', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3515, 'R.M. Raihan Utama', 'P', '2017-07-21', 'Prabumulih', 'UD Gunarto Latupono Tbk', 59.2, 127, 'UD Gunarto Latupono Tbk - Prabumulih', 'USIA DINI 2', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3516, 'R.M. Omar Santoso, S.Ked', 'P', '2011-09-24', 'Mojokerto', 'PT Thamrin Sihombing', 72.9, 189, 'PT Thamrin Sihombing - Mojokerto', 'REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3517, 'Wira Prabowo', 'L', '2019-07-19', 'Palangkaraya', 'Perum Waskita', 49, 135, 'Perum Waskita - Palangkaraya', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3518, 'Gabriella Mansur, S.I.Kom', 'L', '2008-11-15', 'Ambon', 'Perum Marpaung Ramadan (Persero) Tbk', 87.4, 166, 'Perum Marpaung Ramadan (Persero) Tbk - Ambon', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3519, 'Hj. Michelle Waskita, S.Sos', 'P', '2017-08-19', 'Kota Administrasi Jakarta Timur', 'Perum Nashiruddin Tbk', 37.4, 184, 'Perum Nashiruddin Tbk - Kota Administrasi Jakarta Timur', 'USIA DINI 2', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3520, 'Saka Nasyidah', 'L', '2015-09-28', 'Palopo', 'CV Sihotang Tbk', 51.2, 146, 'CV Sihotang Tbk - Palopo', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3521, 'Kayla Purwanti', 'P', '2001-10-05', 'Semarang', 'UD Adriansyah', 79.1, 178, 'UD Adriansyah - Semarang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3522, 'Muhammad Mulyani', 'P', '2015-08-03', 'Purwokerto', 'UD Sirait Hassanah', 83.3, 161, 'UD Sirait Hassanah - Purwokerto', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3523, 'Kunthara Suartini, S.Kom', 'L', '2009-08-20', 'Tomohon', 'CV Wahyudin Lestari', 31.9, 152, 'CV Wahyudin Lestari - Tomohon', 'REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3524, 'Oman Prasetya', 'L', '2002-07-20', 'Semarang', 'PT Maulana Tbk', 83.1, 152, 'PT Maulana Tbk - Semarang', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3525, 'Dt. Kunthara Nashiruddin', 'L', '2008-11-04', 'Surakarta', 'UD Wulandari (Persero) Tbk', 73, 187, 'UD Wulandari (Persero) Tbk - Surakarta', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3526, 'Jais Hidayat', 'L', '2005-09-24', 'Sawahlunto', 'PD Pratiwi (Persero) Tbk', 86.8, 155, 'PD Pratiwi (Persero) Tbk - Sawahlunto', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3527, 'Lili Nurdiyanti', 'L', '2008-09-22', 'Pekanbaru', 'Perum Gunawan Purwanti (Persero) Tbk', 38.7, 125, 'Perum Gunawan Purwanti (Persero) Tbk - Pekanbaru', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3528, 'Putu Suryono', 'P', '2014-09-30', 'Sungai Penuh', 'CV Utama (Persero) Tbk', 79.6, 130, 'CV Utama (Persero) Tbk - Sungai Penuh', 'PRA REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3529, 'Silvia Simbolon', 'P', '2017-10-07', 'Tual', 'PD Winarno (Persero) Tbk', 80.2, 178, 'PD Winarno (Persero) Tbk - Tual', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3530, 'Ella Wacana', 'P', '2019-09-18', 'Bitung', 'UD Hastuti Prasetya Tbk', 45.9, 168, 'UD Hastuti Prasetya Tbk - Bitung', 'USIA DINI 1', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3531, 'Nova Nababan', 'P', '2020-03-23', 'Kendari', 'Perum Mandasari Ramadan (Persero) Tbk', 34.5, 156, 'Perum Mandasari Ramadan (Persero) Tbk - Kendari', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3532, 'Salimah Pudjiastuti', 'P', '2012-03-17', 'Dumai', 'Perum Suryatmi', 43.5, 157, 'Perum Suryatmi - Dumai', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3533, 'Faizah Kusumo', 'L', '2009-03-07', 'Malang', 'CV Hasanah Yulianti (Persero) Tbk', 43.4, 171, 'CV Hasanah Yulianti (Persero) Tbk - Malang', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3534, 'Kania Sudiati, M.Kom.', 'L', '2020-06-07', 'Padangpanjang', 'UD Astuti Winarsih', 83, 142, 'UD Astuti Winarsih - Padangpanjang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3535, 'drg. Ifa Nainggolan', 'L', '2001-12-18', 'Yogyakarta', 'CV Yuniar (Persero) Tbk', 75.3, 171, 'CV Yuniar (Persero) Tbk - Yogyakarta', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3536, 'Ade Habibi', 'P', '2011-05-13', 'Bima', 'PT Samosir (Persero) Tbk', 54.7, 168, 'PT Samosir (Persero) Tbk - Bima', 'REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3537, 'Raisa Wacana', 'P', '2010-11-14', 'Tanjungbalai', 'Perum Tamba', 58.1, 166, 'Perum Tamba - Tanjungbalai', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3538, 'Emong Lestari', 'L', '2004-07-07', 'Parepare', 'Perum Padmasari (Persero) Tbk', 41, 174, 'Perum Padmasari (Persero) Tbk - Parepare', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3539, 'Zamira Januar, M.M.', 'P', '2015-01-20', 'Bandar Lampung', 'UD Rajata Melani Tbk', 87.9, 120, 'UD Rajata Melani Tbk - Bandar Lampung', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3540, 'Michelle Samosir', 'P', '2008-11-14', 'Kediri', 'UD Nasyiah Waluyo (Persero) Tbk', 47.3, 187, 'UD Nasyiah Waluyo (Persero) Tbk - Kediri', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3541, 'Harja Kurniawan', 'P', '2019-12-31', 'Pekanbaru', 'UD Widodo Handayani (Persero) Tbk', 49.4, 127, 'UD Widodo Handayani (Persero) Tbk - Pekanbaru', 'USIA DINI 1', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3542, 'KH. Ganda Susanti', 'L', '2010-03-06', 'Bandung', 'UD Habibi', 44.5, 166, 'UD Habibi - Bandung', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3543, 'Tgk. Pangeran Simbolon, S.Pd', 'P', '2011-02-16', 'Bandar Lampung', 'UD Megantara (Persero) Tbk', 46.9, 158, 'UD Megantara (Persero) Tbk - Bandar Lampung', 'REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3544, 'Zaenab Uwais', 'L', '2016-03-08', 'Pematangsiantar', 'PT Puspita', 82.6, 125, 'PT Puspita - Pematangsiantar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3545, 'Usyi Pratiwi', 'P', '1995-10-09', 'Blitar', 'PT Sitorus (Persero) Tbk', 55.7, 136, 'PT Sitorus (Persero) Tbk - Blitar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3546, 'Ir. Eli Santoso', 'P', '2020-03-06', 'Banjarbaru', 'UD Najmudin (Persero) Tbk', 82.3, 168, 'UD Najmudin (Persero) Tbk - Banjarbaru', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3547, 'Novi Situmorang', 'P', '2019-07-08', 'Palembang', 'Perum Lailasari Pratama Tbk', 54.7, 144, 'Perum Lailasari Pratama Tbk - Palembang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3548, 'Garan Andriani', 'L', '2020-05-21', 'Bima', 'UD Hariyah Wibisono', 71.9, 184, 'UD Hariyah Wibisono - Bima', 'USIA DINI 1', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3549, 'Hj. Paris Mustofa', 'L', '2012-06-28', 'Banjarbaru', 'CV Kuswandari Hakim (Persero) Tbk', 45.5, 122, 'CV Kuswandari Hakim (Persero) Tbk - Banjarbaru', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3550, 'Hj. Shania Novitasari', 'L', '2012-07-01', 'Pariaman', 'PT Yuniar Saptono', 69.1, 180, 'PT Yuniar Saptono - Pariaman', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3551, 'Bakda Novitasari, S.Farm', 'P', '2010-07-17', 'Bitung', 'PD Pradipta (Persero) Tbk', 55.7, 152, 'PD Pradipta (Persero) Tbk - Bitung', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3552, 'Balidin Hardiansyah, S.Ked', 'P', '2003-07-15', 'Tasikmalaya', 'CV Sihotang', 76.7, 179, 'CV Sihotang - Tasikmalaya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3553, 'R. Mulyono Santoso', 'P', '2013-07-10', 'Medan', 'PD Palastri Yulianti', 66.9, 136, 'PD Palastri Yulianti - Medan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3554, 'Cut Hani Lailasari, S.I.Kom', 'P', '2017-08-27', 'Sukabumi', 'CV Hariyah', 66, 176, 'CV Hariyah - Sukabumi', 'USIA DINI 2', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3555, 'Ophelia Habibi', 'L', '2018-06-07', 'Prabumulih', 'PD Zulkarnain Padmasari Tbk', 36.4, 161, 'PD Zulkarnain Padmasari Tbk - Prabumulih', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3556, 'drg. Bagus Marbun, S.Sos', 'L', '2014-01-07', 'Lhokseumawe', 'UD Utami', 33.3, 169, 'UD Utami - Lhokseumawe', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3557, 'Dr. Ibrani Situmorang', 'P', '1996-07-13', 'Dumai', 'PT Widiastuti Pertiwi', 83.8, 134, 'PT Widiastuti Pertiwi - Dumai', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3558, 'Sari Farida', 'L', '2018-01-07', 'Tasikmalaya', 'Perum Saefullah Tbk', 33.4, 136, 'Perum Saefullah Tbk - Tasikmalaya', 'USIA DINI 2', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3559, 'dr. Melinda Sudiati, S.Farm', 'L', '1998-10-19', 'Blitar', 'UD Sihombing Tbk', 30.2, 157, 'UD Sihombing Tbk - Blitar', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3560, 'Gangsa Adriansyah', 'L', '2020-02-28', 'Binjai', 'CV Nuraini Tbk', 41.7, 125, 'CV Nuraini Tbk - Binjai', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3561, 'Gaduh Pradipta', 'P', '2018-03-09', 'Tual', 'PT Winarsih (Persero) Tbk', 70.9, 190, 'PT Winarsih (Persero) Tbk - Tual', 'USIA DINI 2', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3562, 'Prasetya Sudiati', 'P', '2010-03-14', 'Tegal', 'CV Samosir', 54.4, 176, 'CV Samosir - Tegal', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3563, 'Kasiran Firgantoro, S.Ked', 'L', '2012-06-07', 'Tangerang Selatan', 'UD Prasasta (Persero) Tbk', 50, 125, 'UD Prasasta (Persero) Tbk - Tangerang Selatan', 'REMAJA', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3564, 'Ir. Radit Tarihoran', 'L', '2019-12-23', 'Banjarmasin', 'Perum Wijaya Wibowo (Persero) Tbk', 51.8, 123, 'Perum Wijaya Wibowo (Persero) Tbk - Banjarmasin', 'USIA DINI 1', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3565, 'Cengkal Firgantoro', 'P', '2018-02-23', 'Metro', 'CV Puspita Usamah', 69.3, 144, 'CV Puspita Usamah - Metro', 'USIA DINI 2', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3566, 'Ir. Nova Rahimah', 'L', '2015-07-02', 'Bukittinggi', 'PD Prasetyo Halim Tbk', 80.3, 161, 'PD Prasetyo Halim Tbk - Bukittinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3567, 'Titin Nasyidah', 'L', '2014-02-10', 'Pasuruan', 'PT Anggriawan', 85.1, 125, 'PT Anggriawan - Pasuruan', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3568, 'Tgk. Lintang Widodo', 'P', '2014-07-18', 'Denpasar', 'CV Kusumo', 36.7, 173, 'CV Kusumo - Denpasar', 'PRA REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3569, 'Malika Sitompul', 'P', '1998-05-30', 'Langsa', 'UD Maryadi', 51.7, 173, 'UD Maryadi - Langsa', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3570, 'Hj. Nadia Hasanah', 'P', '2014-09-15', 'Pangkalpinang', 'CV Mustofa Tbk', 54.5, 155, 'CV Mustofa Tbk - Pangkalpinang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3571, 'Drs. Puti Sinaga', 'P', '2015-08-28', 'Tebingtinggi', 'UD Marbun (Persero) Tbk', 54, 169, 'UD Marbun (Persero) Tbk - Tebingtinggi', 'PRA REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3572, 'Genta Situmorang', 'L', '2011-01-03', 'Kota Administrasi Jakarta Pusat', 'PD Mustofa', 67.6, 173, 'PD Mustofa - Kota Administrasi Jakarta Pusat', 'REMAJA', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3573, 'Ajimin Lazuardi', 'L', '2016-03-08', 'Salatiga', 'PT Oktaviani Tbk', 39.9, 131, 'PT Oktaviani Tbk - Salatiga', 'PRA REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3574, 'Arta Kusmawati, S.Psi', 'L', '2011-04-21', 'Kota Administrasi Jakarta Selatan', 'PT Saputra', 86.5, 175, 'PT Saputra - Kota Administrasi Jakarta Selatan', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3575, 'dr. Darimin Maryati, S.Pt', 'P', '2020-01-16', 'Tegal', 'Perum Siregar Hakim', 43.7, 142, 'Perum Siregar Hakim - Tegal', 'USIA DINI 1', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3576, 'Parman Suartini', 'P', '2002-06-24', 'Surabaya', 'Perum Yolanda Tbk', 69.1, 151, 'Perum Yolanda Tbk - Surabaya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS G', '2025-07-24 20:44:35'),
(3577, 'Candrakanta Kurniawan', 'L', '2014-10-02', 'Kupang', 'UD Nugroho (Persero) Tbk', 37.8, 128, 'UD Nugroho (Persero) Tbk - Kupang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3578, 'Puput Marbun', 'L', '2019-08-17', 'Sorong', 'Perum Pratiwi Budiman', 75.5, 156, 'Perum Pratiwi Budiman - Sorong', 'USIA DINI 1', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3579, 'Hamzah Padmasari', 'L', '2003-03-04', 'Ternate', 'UD Sudiati', 56.1, 128, 'UD Sudiati - Ternate', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3580, 'Cici Iswahyudi, S.I.Kom', 'P', '2020-05-21', 'Tangerang Selatan', 'PD Saputra', 58.8, 168, 'PD Saputra - Tangerang Selatan', 'USIA DINI 1', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3581, 'Karja Narpati', 'L', '1995-09-19', 'Kota Administrasi Jakarta Timur', 'PD Hutasoit Laksmiwati', 55.8, 130, 'PD Hutasoit Laksmiwati - Kota Administrasi Jakarta Timur', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS C', '2025-07-24 20:44:35'),
(3582, 'Rama Rajasa', 'P', '2020-01-24', 'Padangpanjang', 'CV Novitasari (Persero) Tbk', 81.9, 168, 'CV Novitasari (Persero) Tbk - Padangpanjang', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3583, 'drg. Irnanto Rajata', 'L', '2000-11-24', 'Sungai Penuh', 'PT Astuti', 66.6, 140, 'PT Astuti - Sungai Penuh', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3584, 'Cici Farida, S.Kom', 'L', '2017-09-22', 'Kotamobagu', 'PD Habibi (Persero) Tbk', 73.4, 165, 'PD Habibi (Persero) Tbk - Kotamobagu', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3585, 'Jessica Siregar', 'P', '2010-10-29', 'Balikpapan', 'UD Purwanti', 30.5, 184, 'UD Purwanti - Balikpapan', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3586, 'Violet Susanti', 'L', '2003-10-19', 'Tangerang Selatan', 'CV Gunawan Mardhiyah', 35.8, 142, 'CV Gunawan Mardhiyah - Tangerang Selatan', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3587, 'dr. Darimin Nurdiyanti', 'P', '2014-06-29', 'Kota Administrasi Jakarta Pusat', 'PD Mandala Tbk', 38.6, 165, 'PD Mandala Tbk - Kota Administrasi Jakarta Pusat', 'PRA REMAJA', 'TANDING', 'TANDING KELAS I', '2025-07-24 20:44:35'),
(3588, 'Kasim Purwanti', 'L', '2006-08-12', 'Tasikmalaya', 'PT Thamrin Tbk', 63.6, 158, 'PT Thamrin Tbk - Tasikmalaya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3589, 'Sarah Aryani', 'L', '2010-02-24', 'Pasuruan', 'PT Suwarno Zulkarnain (Persero) Tbk', 42.5, 132, 'PT Suwarno Zulkarnain (Persero) Tbk - Pasuruan', 'REMAJA', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3590, 'Ani Rahmawati', 'P', '2018-07-01', 'Langsa', 'PD Hidayanto (Persero) Tbk', 65.4, 124, 'PD Hidayanto (Persero) Tbk - Langsa', 'USIA DINI 2', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3591, 'Aswani Mustofa, S.H.', 'P', '2010-08-30', 'Langsa', 'CV Wasita Farida', 87.1, 184, 'CV Wasita Farida - Langsa', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3592, 'Mulyanto Pranowo', 'P', '2011-12-27', 'Pasuruan', 'CV Sitompul (Persero) Tbk', 51.7, 147, 'CV Sitompul (Persero) Tbk - Pasuruan', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35'),
(3593, 'Reksa Gunawan', 'L', '2020-04-26', 'Sawahlunto', 'PD Kuswandari (Persero) Tbk', 77.9, 150, 'PD Kuswandari (Persero) Tbk - Sawahlunto', 'USIA DINI 1', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3594, 'Cut Uchita Setiawan, S.Farm', 'P', '2017-12-11', 'Bau-Bau', 'Perum Hutapea Hidayat (Persero) Tbk', 67.9, 120, 'Perum Hutapea Hidayat (Persero) Tbk - Bau-Bau', 'USIA DINI 2', 'TANDING', 'TANDING KELAS H', '2025-07-24 20:44:35'),
(3595, 'Rika Saragih, M.M.', 'P', '2015-08-09', 'Surakarta', 'CV Sihombing', 57.7, 170, 'CV Sihombing - Surakarta', 'PRA REMAJA', 'TANDING', 'TANDING KELAS J', '2025-07-24 20:44:35'),
(3596, 'Zulaikha Marbun, M.TI.', 'L', '2015-10-22', 'Malang', 'Perum Fujiati Situmorang', 41.5, 175, 'Perum Fujiati Situmorang - Malang', 'PRA REMAJA', 'TANDING', 'TANDING KELAS A', '2025-07-24 20:44:35'),
(3597, 'Sutan Lasmanto Pradana, S.T.', 'L', '2010-10-21', 'Purwokerto', 'Perum Gunawan Melani (Persero) Tbk', 47.7, 171, 'Perum Gunawan Melani (Persero) Tbk - Purwokerto', 'REMAJA', 'TANDING', 'TANDING KELAS F', '2025-07-24 20:44:35'),
(3598, 'Taufan Nuraini', 'P', '2004-01-10', 'Surabaya', 'Perum Siregar', 47.5, 161, 'Perum Siregar - Surabaya', 'DEWASA/UMUM', 'TANDING', 'TANDING KELAS D', '2025-07-24 20:44:35'),
(3599, 'Dt. Jarwa Hartati', 'P', '2008-12-29', 'Tanjungpinang', 'CV Mulyani', 70.3, 190, 'CV Mulyani - Tanjungpinang', 'REMAJA', 'TANDING', 'TANDING KELAS B', '2025-07-24 20:44:35'),
(3600, 'Hilda Hastuti', 'L', '2011-11-29', 'Blitar', 'PT Kuswandari', 50.7, 174, 'PT Kuswandari - Blitar', 'REMAJA', 'TANDING', 'TANDING KELAS E', '2025-07-24 20:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `daftar_peserta_draws`
--

CREATE TABLE `daftar_peserta_draws` (
  `id` int NOT NULL,
  `filter_id` int NOT NULL,
  `urutan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_peserta_draws`
--

INSERT INTO `daftar_peserta_draws` (`id`, `filter_id`, `urutan`, `created_at`) VALUES
(24, 1, '[2949,2767,3285,3124,3400,2915,3249,2844,2814,3506,2744]', '2025-07-30 08:47:49'),
(25, 2, '[3391,2784,3234,3074,3236,3231]', '2025-07-30 08:52:36'),
(26, 3, '[3367,3205,3385,3034,3431,3164,3264,2800,3075,3266,3167]', '2025-07-31 21:41:35'),
(27, 3, '[3264,3167,3266,2800,3034,3367,3205,3431,3075,3164,3385]', '2025-09-02 22:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `daftar_peserta_filtered`
--

CREATE TABLE `daftar_peserta_filtered` (
  `id` int NOT NULL,
  `filter_id` int NOT NULL,
  `peserta_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_peserta_filtered`
--

INSERT INTO `daftar_peserta_filtered` (`id`, `filter_id`, `peserta_id`, `created_at`) VALUES
(519, 1, 2744, '2025-07-30 08:31:01'),
(520, 1, 2767, '2025-07-30 08:31:01'),
(521, 1, 2814, '2025-07-30 08:31:01'),
(522, 1, 2844, '2025-07-30 08:31:01'),
(523, 1, 2915, '2025-07-30 08:31:01'),
(524, 1, 2949, '2025-07-30 08:31:01'),
(525, 1, 3124, '2025-07-30 08:31:01'),
(526, 1, 3249, '2025-07-30 08:31:01'),
(527, 1, 3285, '2025-07-30 08:31:01'),
(528, 1, 3400, '2025-07-30 08:31:01'),
(529, 1, 3506, '2025-07-30 08:31:01'),
(530, 2, 2784, '2025-07-30 08:52:10'),
(531, 2, 3074, '2025-07-30 08:52:10'),
(532, 2, 3231, '2025-07-30 08:52:10'),
(533, 2, 3234, '2025-07-30 08:52:10'),
(534, 2, 3236, '2025-07-30 08:52:10'),
(535, 2, 3391, '2025-07-30 08:52:10'),
(536, 3, 2800, '2025-07-31 21:41:03'),
(537, 3, 3034, '2025-07-31 21:41:03'),
(538, 3, 3075, '2025-07-31 21:41:03'),
(539, 3, 3164, '2025-07-31 21:41:03'),
(540, 3, 3167, '2025-07-31 21:41:03'),
(541, 3, 3205, '2025-07-31 21:41:03'),
(542, 3, 3264, '2025-07-31 21:41:03'),
(543, 3, 3266, '2025-07-31 21:41:03'),
(544, 3, 3367, '2025-07-31 21:41:03'),
(545, 3, 3385, '2025-07-31 21:41:03'),
(546, 3, 3431, '2025-07-31 21:41:03');

-- --------------------------------------------------------

--
-- Table structure for table `daftar_peserta_filter_batches`
--

CREATE TABLE `daftar_peserta_filter_batches` (
  `filter_id` int NOT NULL,
  `batch_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `kategori_umur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kompetisi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kategori_tanding` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_peserta_filter_batches`
--

INSERT INTO `daftar_peserta_filter_batches` (`filter_id`, `batch_name`, `created_at`, `kategori_umur`, `jenis_kelamin`, `jenis_kompetisi`, `kategori_tanding`) VALUES
(1, 'TANDING / TANDING KELAS A / REMAJA / PUTRA', '2025-07-30 08:31:01', 'REMAJA', 'L', 'TANDING', 'TANDING KELAS A'),
(2, 'TANDING / TANDING KELAS C / DEWASA/UMUM / PUTRI', '2025-07-30 08:52:10', 'DEWASA/UMUM', 'P', 'TANDING', 'TANDING KELAS C'),
(3, 'TANDING / TANDING KELAS C / PRA REMAJA / PUTRA', '2025-07-31 21:41:03', 'PRA REMAJA', 'L', 'TANDING', 'TANDING KELAS C');

-- --------------------------------------------------------

--
-- Table structure for table `kontingen`
--

CREATE TABLE `kontingen` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_kontingen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `provinsi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kota` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kontingen`
--

INSERT INTO `kontingen` (`id`, `user_id`, `nama_kontingen`, `provinsi`, `kota`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 3, 'Kontingen Jakarta Pusat', 'DKI Jakarta', 'Jakarta Pusat', 'Jl. Sudirman No. 1, Jakarta Pusat', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 3, 'Kontingen Bandung', 'Jawa Barat', 'Bandung', 'Jl. Asia Afrika No. 1, Bandung', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 4, 'IPSI UDINUS', 'Jawa Tengah', 'Kota Semarang', NULL, '2025-07-12 16:04:29', '2025-07-12 16:04:29'),
(4, 5, 'TS SEMARANG', 'Jawa Tengah', 'KOTA SEMARANG', NULL, '2025-07-12 16:24:56', '2025-07-12 16:24:56'),
(5, 5, 'UNIVERSITAS SEMARANG', 'Jawa Tengah', 'KOTA SEMARANG', NULL, '2025-08-28 16:28:42', '2025-08-28 16:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `kontingens`
--

CREATE TABLE `kontingens` (
  `id` int NOT NULL,
  `nama_kontingen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kontingen_payments`
--

CREATE TABLE `kontingen_payments` (
  `id` int NOT NULL,
  `kontingen_id` int DEFAULT NULL,
  `competition_id` int DEFAULT NULL,
  `jumlah_athlete` int DEFAULT NULL,
  `total_bayar` decimal(10,2) DEFAULT NULL,
  `bukti_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','verified','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int NOT NULL,
  `nama_bank` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_rekening` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pemilik_rekening` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_pemilik` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `nama_bank`, `nomor_rekening`, `pemilik_rekening`, `nama_pemilik`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bank BCA', '1234567890', 'Panitia Pencak Silat', 'Panitia Pencak Silat', 'active', '2024-01-01 00:00:00', '2025-07-12 17:00:06'),
(2, 'Bank Mandiri', '0987654321', 'Panitia Pencak Silat', 'Panitia Pencak Silat', 'active', '2024-01-01 00:00:00', '2025-07-12 17:00:06'),
(3, 'Bank BRI', '1234567890123456', 'Panitia Pencak Silat', 'Panitia Pencak Silat', 'active', '2025-07-12 16:54:20', '2025-07-12 17:00:06'),
(4, 'Bank BNI', '9876543210987654', 'Panitia Pencak Silat', 'Panitia Pencak Silat', 'active', '2025-07-12 16:54:20', '2025-07-12 17:00:06'),
(5, 'SEABANK', '9099909090909090', 'anom', 'anom', 'active', '2025-07-12 17:02:40', '2025-07-12 17:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int NOT NULL,
  `competition_id` int NOT NULL,
  `athlete_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','verified') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'unpaid',
  `payment_proof` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `age_category_id` int DEFAULT NULL,
  `competition_type_id` int DEFAULT NULL,
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `competition_id`, `athlete_id`, `category_id`, `status`, `payment_status`, `payment_proof`, `created_at`, `updated_at`, `age_category_id`, `competition_type_id`, `payment_note`) VALUES
(14, 6, 5, NULL, 'pending', 'paid', '1756397434_68b07f7aed1ce.jpeg', '2025-08-28 16:10:13', '2025-08-28 16:10:34', 6, 1, NULL),
(16, 6, 4, NULL, 'pending', 'paid', 'kontingen_payment_1756397806_68b080ee043d4.jpeg', '2025-08-28 16:16:34', '2025-08-28 16:16:46', 7, 3, NULL),
(18, 6, 5, NULL, 'pending', 'verified', '1756398369_68b083212cd2d.jpeg', '2025-08-28 16:22:51', '2025-09-08 15:23:48', 6, 4, NULL),
(19, 8, 4, 7, 'pending', 'paid', '1756826969_68b70d5999fe1.jpeg', '2025-09-02 15:27:40', '2025-09-02 15:29:29', 13, 25, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `whatsapp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `role` enum('admin','user','superadmin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `whatsapp`, `alamat`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@pencaksilat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jakarta', 'superadmin', 'active', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 'Admin 1', 'admin1@pencaksilat.com', '$2y$10$F70mmA2p2xwSBUn2oGrcMu0ZIOlzI02fEVGRY0c/3CMTWrvjbPSPC', '081234567891', 'Bandung', 'admin', 'active', '2024-01-01 00:00:00', '2025-07-12 16:16:50'),
(3, 'User Test', 'user123@pencaksilat.com', '$2y$10$E/Quh4q.C4r.iysByAIg2OsPrzNsCXm01UAjUYQXYTlP3nE2BkJma', '081234567892', 'Surabaya', 'user', 'active', '2024-01-01 00:00:00', '2025-07-12 16:20:30'),
(4, 'ARIQ KHAMILUDDIN ISMAWAN', 'ariq795@gmail.com', '$2y$10$iFsKPPSADSZ3nt0vrHPof.8BUqkh4uVcHqb2eobL4A9NeNxpB/2.2', '08985999461', 'TEGAL KANGKUNG 10 RT 05/02 KEDUNGMUNDU, KEC. TEMBALANG', 'superadmin', 'active', '2025-07-12 16:04:29', '2025-07-12 16:04:57'),
(5, 'naufal', 'naufal123@pencaksilat.com', '$2y$10$KjMos1y67h9IqpKFPs9rTuMNEOqX7pF6zvqUegWoEEuPuRjzis1Z6', '088123456789', 'KOTA SEMARANG', 'user', 'active', '2025-07-12 16:24:56', '2025-07-12 16:24:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `age_categories`
--
ALTER TABLE `age_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `athletes`
--
ALTER TABLE `athletes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kontingen_id` (`kontingen_id`);

--
-- Indexes for table `bracket_results`
--
ALTER TABLE `bracket_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_admins`
--
ALTER TABLE `competition_admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `competition_categories`
--
ALTER TABLE `competition_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `age_category_id` (`age_category_id`),
  ADD KEY `idx_competition_type_id` (`competition_type_id`);

--
-- Indexes for table `competition_contacts`
--
ALTER TABLE `competition_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `competition_documents`
--
ALTER TABLE `competition_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `competition_payment_methods`
--
ALTER TABLE `competition_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `competition_types`
--
ALTER TABLE `competition_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `daftar_peserta`
--
ALTER TABLE `daftar_peserta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daftar_peserta_draws`
--
ALTER TABLE `daftar_peserta_draws`
  ADD PRIMARY KEY (`id`),
  ADD KEY `filter_id` (`filter_id`);

--
-- Indexes for table `daftar_peserta_filtered`
--
ALTER TABLE `daftar_peserta_filtered`
  ADD PRIMARY KEY (`id`),
  ADD KEY `filter_id` (`filter_id`),
  ADD KEY `peserta_id` (`peserta_id`);

--
-- Indexes for table `daftar_peserta_filter_batches`
--
ALTER TABLE `daftar_peserta_filter_batches`
  ADD PRIMARY KEY (`filter_id`);

--
-- Indexes for table `kontingen`
--
ALTER TABLE `kontingen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kontingens`
--
ALTER TABLE `kontingens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kontingen_payments`
--
ALTER TABLE `kontingen_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kontingen_id` (`kontingen_id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `athlete_id` (`athlete_id`),
  ADD KEY `category_id` (`category_id`);

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
-- AUTO_INCREMENT for table `age_categories`
--
ALTER TABLE `age_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `athletes`
--
ALTER TABLE `athletes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `bracket_results`
--
ALTER TABLE `bracket_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=362;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `competition_admins`
--
ALTER TABLE `competition_admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `competition_categories`
--
ALTER TABLE `competition_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `competition_contacts`
--
ALTER TABLE `competition_contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `competition_documents`
--
ALTER TABLE `competition_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `competition_payment_methods`
--
ALTER TABLE `competition_payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `competition_types`
--
ALTER TABLE `competition_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `daftar_peserta`
--
ALTER TABLE `daftar_peserta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3601;

--
-- AUTO_INCREMENT for table `daftar_peserta_draws`
--
ALTER TABLE `daftar_peserta_draws`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `daftar_peserta_filtered`
--
ALTER TABLE `daftar_peserta_filtered`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=547;

--
-- AUTO_INCREMENT for table `daftar_peserta_filter_batches`
--
ALTER TABLE `daftar_peserta_filter_batches`
  MODIFY `filter_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kontingen`
--
ALTER TABLE `kontingen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kontingens`
--
ALTER TABLE `kontingens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kontingen_payments`
--
ALTER TABLE `kontingen_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `age_categories`
--
ALTER TABLE `age_categories`
  ADD CONSTRAINT `age_categories_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `athletes`
--
ALTER TABLE `athletes`
  ADD CONSTRAINT `athletes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `athletes_ibfk_2` FOREIGN KEY (`kontingen_id`) REFERENCES `kontingen` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `competition_admins`
--
ALTER TABLE `competition_admins`
  ADD CONSTRAINT `competition_admins_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_admins_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_categories`
--
ALTER TABLE `competition_categories`
  ADD CONSTRAINT `competition_categories_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_categories_ibfk_2` FOREIGN KEY (`age_category_id`) REFERENCES `age_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `competition_categories_ibfk_3` FOREIGN KEY (`competition_type_id`) REFERENCES `competition_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `competition_contacts`
--
ALTER TABLE `competition_contacts`
  ADD CONSTRAINT `competition_contacts_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_documents`
--
ALTER TABLE `competition_documents`
  ADD CONSTRAINT `competition_documents_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_payment_methods`
--
ALTER TABLE `competition_payment_methods`
  ADD CONSTRAINT `competition_payment_methods_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competition_payment_methods_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_types`
--
ALTER TABLE `competition_types`
  ADD CONSTRAINT `competition_types_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daftar_peserta_draws`
--
ALTER TABLE `daftar_peserta_draws`
  ADD CONSTRAINT `daftar_peserta_draws_ibfk_1` FOREIGN KEY (`filter_id`) REFERENCES `daftar_peserta_filter_batches` (`filter_id`) ON DELETE CASCADE;

--
-- Constraints for table `daftar_peserta_filtered`
--
ALTER TABLE `daftar_peserta_filtered`
  ADD CONSTRAINT `daftar_peserta_filtered_ibfk_1` FOREIGN KEY (`filter_id`) REFERENCES `daftar_peserta_filter_batches` (`filter_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daftar_peserta_filtered_ibfk_2` FOREIGN KEY (`peserta_id`) REFERENCES `daftar_peserta` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kontingen`
--
ALTER TABLE `kontingen`
  ADD CONSTRAINT `kontingen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kontingen_payments`
--
ALTER TABLE `kontingen_payments`
  ADD CONSTRAINT `kontingen_payments_ibfk_1` FOREIGN KEY (`kontingen_id`) REFERENCES `kontingen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kontingen_payments_ibfk_2` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`athlete_id`) REFERENCES `athletes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `competition_categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
