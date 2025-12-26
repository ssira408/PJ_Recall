-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2025 at 03:57 PM
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
-- Database: `pj_recall_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `item_type` enum('user','project','file','member') NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_data` longtext NOT NULL,
  `deleted_by` varchar(150) NOT NULL,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `restore_status` tinyint(1) NOT NULL DEFAULT 0,
  `restored_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `item_type`, `item_id`, `item_data`, `deleted_by`, `deleted_at`, `restore_status`, `restored_at`) VALUES
(1, 'project', 11, '{\"project_id\":11,\"title_th\":\"\\u0e2a\\u0e49\\u0e21\",\"title_en\":\"som\",\"education_level\":\"\\u0e1b\\u0e27\\u0e2a.\",\"department\":\"\\u0e40\\u0e17\\u0e04\\u0e42\\u0e19\\u0e42\\u0e25\\u0e22\\u0e35\\u0e2a\\u0e32\\u0e23\\u0e2a\\u0e19\\u0e40\\u0e17\\u0e28\",\"advisor_main\":\"\\u0e19\\u0e34\\u0e01\\u0e01\\u0e35\\u0e49\",\"advisor_co\":\"\\u0e04\\u0e23\\u0e38\\u0e40\\u0e1b\\u0e34\\u0e49\\u0e25\",\"creator_id\":1,\"objective\":\"\\u0e18\\u0e4d\\u0e13\\u0e4a\\u0e28\\u0e4b\\u0e29\",\"working_principle\":\"\\u0e18\\u0e4d\\u0e54\\u0e39\\u0e55\\u0e13\\u0e4a\\u0e28\\u0e13\\u0e4b\\u0e12\\u0e47\\u0e0c\\u0e42\",\"highlight\":\"\\u0e49\\u0e30\\\"\\u0e39\\u0e53\\u0e3f\\u0e18\\u0e4a\\u0e4d\",\"benefit\":\"\\u0e30\\u0e35\\u0e31\\u0e23\\u0e35\\u0e2a\\u0e48\",\"duration\":\"12\\/08\\/2005-12\\/09\\/2006\",\"github_link\":\"\",\"abstract\":\"\\u0e33\\u0e44\\u0e20\\u0e16\\u0e38\\u0e36\\u0e23\\u0e19\\u0e2a\",\"created_at\":\"2025-12-24 13:53:51\",\"college\":\"\\u0e27\\u0e34\\u0e17\\u0e22\\u0e32\\u0e25\\u0e31\\u0e22\\u0e40\\u0e17\\u0e04\\u0e19\\u0e34\\u0e04\\u0e2a\\u0e27\\u0e48\\u0e32\\u0e07\\u0e41\\u0e14\\u0e19\\u0e14\\u0e34\\u0e19\",\"status\":\"approved\"}', 'unknown', '2025-12-24 22:47:57', 1, '2025-12-24 22:57:25'),
(2, 'project', 11, '{\"project_id\":11,\"title_th\":\"\\u0e2a\\u0e49\\u0e21\",\"title_en\":\"som\",\"education_level\":\"\\u0e1b\\u0e27\\u0e2a.\",\"department\":\"\\u0e40\\u0e17\\u0e04\\u0e42\\u0e19\\u0e42\\u0e25\\u0e22\\u0e35\\u0e2a\\u0e32\\u0e23\\u0e2a\\u0e19\\u0e40\\u0e17\\u0e28\",\"advisor_main\":\"\\u0e19\\u0e34\\u0e01\\u0e01\\u0e35\\u0e49\",\"advisor_co\":\"\\u0e04\\u0e23\\u0e38\\u0e40\\u0e1b\\u0e34\\u0e49\\u0e25\",\"creator_id\":1,\"objective\":\"\\u0e18\\u0e4d\\u0e13\\u0e4a\\u0e28\\u0e4b\\u0e29\",\"working_principle\":\"\\u0e18\\u0e4d\\u0e54\\u0e39\\u0e55\\u0e13\\u0e4a\\u0e28\\u0e13\\u0e4b\\u0e12\\u0e47\\u0e0c\\u0e42\",\"highlight\":\"\\u0e49\\u0e30\\\"\\u0e39\\u0e53\\u0e3f\\u0e18\\u0e4a\\u0e4d\",\"benefit\":\"\\u0e30\\u0e35\\u0e31\\u0e23\\u0e35\\u0e2a\\u0e48\",\"duration\":\"12\\/08\\/2005-12\\/09\\/2006\",\"github_link\":\"\",\"abstract\":\"\\u0e33\\u0e44\\u0e20\\u0e16\\u0e38\\u0e36\\u0e23\\u0e19\\u0e2a\",\"created_at\":\"2025-12-24 13:53:51\",\"college\":\"\\u0e27\\u0e34\\u0e17\\u0e22\\u0e32\\u0e25\\u0e31\\u0e22\\u0e40\\u0e17\\u0e04\\u0e19\\u0e34\\u0e04\\u0e2a\\u0e27\\u0e48\\u0e32\\u0e07\\u0e41\\u0e14\\u0e19\\u0e14\\u0e34\\u0e19\",\"status\":\"approved\"}', 'unknown', '2025-12-24 22:57:35', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_code` varchar(6) DEFAULT NULL,
  `title_th` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `education_level` enum('ปวช.','ปวส.') NOT NULL,
  `department` enum('เทคโนโลยีสารสนเทศ','การบัญชี','คหกรรมศาสตร์','ช่างยนต์','ช่างไฟฟ้ากำลัง','ช่างโยธา','ช่างอิเล็กทรอนิกส์','ช่างเชื่อมโลหะ','ช่างกลโรงงาน','สามัญสัมพันธ์') NOT NULL,
  `advisor_main` varchar(255) NOT NULL,
  `advisor_co` varchar(255) DEFAULT NULL,
  `creator_id` int(11) NOT NULL,
  `objective` text DEFAULT NULL,
  `working_principle` text DEFAULT NULL,
  `highlight` text DEFAULT NULL,
  `benefit` text DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `github_link` varchar(255) DEFAULT NULL,
  `abstract` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `college` varchar(255) DEFAULT 'วิทยาลัยเทคนิคสว่างแดนดิน',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `project_code`, `title_th`, `title_en`, `education_level`, `department`, `advisor_main`, `advisor_co`, `creator_id`, `objective`, `working_principle`, `highlight`, `benefit`, `duration`, `github_link`, `abstract`, `created_at`, `college`, `status`, `approved_at`) VALUES
(9, '250001', 'ฺกล้วย', 'banana', 'ปวส.', 'เทคโนโลยีสารสนเทศ', 'นิกกี้', 'ครุเปิ้ล', 1, 'บรราาาาาาาาาาาาาาาาาาาาาาาาาาาา', 'บรราาาาาาาาาาาาาาาาาาาาาาาาาาาา', 'บรราาาาาาาาาาาาาาาาาาาาาาาาาาาา', NULL, '01/01/2005-01/01/2006', 'https://github.com/Sasira48/ssira.pk.git', 'บรราาาาาาาาาาาาาาาาาาาาาาาาาาาา', '2025-12-19 08:39:08', 'วิทยาลัยเทคนิคสว่างแดนดิน', 'approved', NULL),
(10, '250002', 'มิลทีนดี', 'Milk_teenD', 'ปวส.', 'เทคโนโลยีสารสนเทศ', 'somlong', '', 1, 'kdluodhd', 'khldlumfhnd', 'dkghldod', NULL, '615:695-884-8', 'https://github.com/Nampu48-V2/MT_project.git', 'tjgmgfmyo', '2025-12-19 08:48:48', 'วิทยาลัยเทคนิคสว่างแดนดิน', 'approved', NULL),
(12, '250003', 'ฺกล้วย', 'som', 'ปวส.', 'เทคโนโลยีสารสนเทศ', 'เป้ย', 'จัก', 1, 'ะีัี', 'พุถึุรนีรส้พ', 'ภุำถึุะรึีนัส่ไ', 'ภุำถึพุะรี้่าะ', '12/08/2005-12/09/2006', '', 'ำพัะีา้', '2025-12-24 07:03:50', 'วิทยาลัยเทคนิคสว่างแดนดิน', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

CREATE TABLE `project_files` (
  `file_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_path` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_files`
--

INSERT INTO `project_files` (`file_id`, `project_id`, `file_name`, `file_type`, `file_path`, `uploaded_at`) VALUES
(5, 9, '69450f2c9cbe4_123.docx', 'docx', '/projects/69450f2c9cbe4_123.docx', '2025-12-19 08:39:08'),
(6, 9, '69450f2c9cdee_124.pdf', 'pdf', '/projects/69450f2c9cdee_124.pdf', '2025-12-19 08:39:08'),
(7, 9, '69450f2c9cfb0_125.docx', 'docx', '/projects/69450f2c9cfb0_125.docx', '2025-12-19 08:39:08'),
(8, 9, '69450f2c9d13d_126.pdf', 'pdf', '/projects/69450f2c9d13d_126.pdf', '2025-12-19 08:39:08');

-- --------------------------------------------------------

--
-- Table structure for table `project_users`
--

CREATE TABLE `project_users` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `education_level` enum('ปวช.','ปวส.') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_users`
--

INSERT INTO `project_users` (`id`, `project_id`, `user_name`, `student_id`, `education_level`) VALUES
(4, 9, 'ศศิรา ปาสกิต', '67319010001', 'ปวส.'),
(5, 9, 'แก้ม', '67319010002', 'ปวส.'),
(6, 10, 'สมหยัง ฮี', '6731000000', 'ปวส.'),
(7, 11, 'ศศิรา ปาสกิต', '67319010001', 'ปวส.'),
(8, 12, 'ศศิรา ปาสกิต', '67319010004', 'ปวส.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `id_card` varchar(13) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `department` enum('เทคโนโลยีสารสนเทศ','การบัญชี','คหกรรมศาสตร์','ช่างยนต์','ช่างไฟฟ้ากำลัง','ช่างโยธา','ช่างอิเล็กทรอนิกส์','ช่างเชื่อมโลหะ','ช่างกลโรงงาน','สามัญสัมพันธ์') NOT NULL,
  `education_level` enum('ประกาศนียบัตรวิชาชีพ','ประกาศนียบัตรวิชาชีพชั้นสูง','อาจารย์') NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `college` varchar(255) DEFAULT 'วิทยาลัยเทคนิคสว่างแดนดิน',
  `role` enum('user','employee','admin','adminsupport') NOT NULL DEFAULT 'user',
  `remember_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `student_id`, `id_card`, `birth_date`, `department`, `education_level`, `email`, `password`, `college`, `role`, `remember_token`, `created_at`, `profile_img`) VALUES
(1, 'ศศิรา ปาสกิต', NULL, '1139900483682', '2005-08-04', 'เทคโนโลยีสารสนเทศ', '', 'ssirapaskit@gmail.com', '$2y$10$Al.7xFhB5qW5N7Ole282ru2WFJ6nBm0VrD5U9lCXVBZ0Za1xOGH7C', 'วิทยาลัยเทคนิคสว่างแดนดิน', 'admin', 'e8ff70a9b98fabdaf6c5d02cee00f85ddf7b99fddc38cb3c6459fc58d804c24e', '2025-12-19 06:34:54', 'user_1_1766637107.jpg'),
(2, 'น้ำพุ งามเถื่อน', NULL, '1139900483681', '2005-01-26', 'เทคโนโลยีสารสนเทศ', 'ประกาศนียบัตรวิชาชีพชั้นสูง', '67319010037@swdtcmail.com', '$2y$10$H31WpHtRO7speb8D9r/wMeB3yiYDhxSm.u/rn9RIZ8Ne52dP5KOke', 'วิทยาลัยเทคนิคสว่างแดนดิน', 'admin', NULL, '2025-12-25 02:40:59', 'user_2_1766632811.jpg'),
(3, 'กรวรรณ แก้วบุญมา', '67319010004', NULL, '2005-01-01', 'ช่างกลโรงงาน', 'ประกาศนียบัตรวิชาชีพ', '67319010004@swdtcmail.com', '$2y$10$ZgVre0v0seY7hWUCW9NEDOmCCokrXKPQeNx4/isvAANQIIi4zZ3yG', 'วิทยาลัยเทคนิคสว่างแดนดิน', 'user', NULL, '2025-12-25 04:07:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_type` (`item_type`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_restore_status` (`restore_status`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD UNIQUE KEY `project_code` (`project_code`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `project_files`
--
ALTER TABLE `project_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_users`
--
ALTER TABLE `project_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_users`
--
ALTER TABLE `project_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `project_files`
--
ALTER TABLE `project_files`
  ADD CONSTRAINT `project_files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
