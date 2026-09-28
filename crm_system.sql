-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2026 at 10:18 PM
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
-- Database: `crm_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments_history`
--

CREATE TABLE `assignments_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments_history`
--

INSERT INTO `assignments_history` (`id`, `user_id`, `employee_id`, `assigned_by`, `assigned_at`) VALUES
(59, 57, 4, 3, '2026-06-04 12:23:15'),
(60, 43, 4, 3, '2026-06-04 12:23:15'),
(61, 23, 4, 3, '2026-06-04 12:23:15'),
(62, 25, 4, 3, '2026-06-04 12:23:24'),
(63, 12, 4, 3, '2026-06-04 12:23:24'),
(64, 58, 9, 3, '2026-06-05 06:01:16'),
(65, 59, 9, 3, '2026-06-05 06:01:16'),
(66, 53, 9, 3, '2026-06-05 06:01:16'),
(67, 54, 9, 3, '2026-06-05 06:01:16'),
(68, 55, 9, 3, '2026-06-05 06:01:16'),
(69, 56, 9, 3, '2026-06-05 06:01:16'),
(70, 38, 8, 3, '2026-06-05 08:21:04'),
(71, 39, 8, 3, '2026-06-05 08:21:04'),
(72, 40, 8, 3, '2026-06-05 08:21:04'),
(73, 33, 8, 3, '2026-06-05 08:21:04'),
(74, 34, 8, 3, '2026-06-05 08:21:04'),
(75, 35, 8, 3, '2026-06-05 08:21:04'),
(76, 36, 8, 3, '2026-06-05 08:21:04'),
(77, 28, 8, 3, '2026-06-05 08:21:04'),
(78, 29, 8, 3, '2026-06-05 08:21:04'),
(79, 30, 8, 3, '2026-06-05 08:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `call_records`
--

CREATE TABLE `call_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `interest_level` enum('interested','not_interested','follow_up') DEFAULT 'follow_up',
  `follow_up_date` date DEFAULT NULL,
  `recording_file` varchar(255) DEFAULT NULL,
  `called_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `call_records`
--

INSERT INTO `call_records` (`id`, `user_id`, `employee_id`, `notes`, `interest_level`, `follow_up_date`, `recording_file`, `called_at`) VALUES
(38, 43, 4, 'no', 'not_interested', NULL, NULL, '2026-06-04 12:53:29'),
(40, 57, 4, 'no', 'not_interested', '0000-00-00', NULL, '2026-06-04 13:22:47'),
(41, 23, 4, 'd', 'interested', '0000-00-00', NULL, '2026-06-05 06:17:30'),
(42, 25, 4, 'done', 'interested', '0000-00-00', 'call_1780640392_9856.mp3', '2026-06-05 06:19:52'),
(43, 12, 4, 'call next week', 'follow_up', '2026-06-12', NULL, '2026-06-05 08:12:47'),
(44, 58, 9, 'No conversation', 'not_interested', '0000-00-00', NULL, '2026-06-05 08:15:41'),
(45, 59, 9, 'I\'m interested, but can you call me later?', 'follow_up', '2026-06-15', NULL, '2026-06-05 08:17:19'),
(46, 53, 9, 'Perched Product', 'interested', '0000-00-00', NULL, '2026-06-05 08:17:44'),
(47, 54, 9, 'Cut the all', 'not_interested', '0000-00-00', NULL, '2026-06-05 08:18:03'),
(48, 55, 9, 'Call me end of the month', 'follow_up', '2026-06-29', NULL, '2026-06-05 08:18:38');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') DEFAULT 'employee',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `created_at`) VALUES
(3, 'Super Admin', 'admin@crm.com', '1234567890', 'admin123', 'admin', 'active', '2026-06-04 05:53:13'),
(4, 'John Employee', 'employee@crm.com', '9876543210', 'emp123', 'employee', 'active', '2026-06-04 05:53:13'),
(8, 'New Emp', 'emp@gmail.com', '1111111111', '1234', 'employee', 'active', '2026-06-04 08:57:00'),
(9, 'Testing Employe', 'test@gmail.com', '6663625129', '66636', 'employee', 'active', '2026-06-05 05:59:34'),
(10, 'e', 'employeeeeeee@crm.com', '1212121212', '1212', 'employee', 'active', '2026-06-05 13:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expiry`, `created_at`) VALUES
(39, 4, '2790d67a03e45cce15cd12664db3ac44075caa9a3dabfa3b7b8850d71aed755c', '2026-07-09 14:43:24', '2026-06-09 12:43:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assignment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `company`, `address`, `assigned_to`, `status`, `created_at`, `updated_at`, `assignment_date`) VALUES
(11, 'Aarav Sharma', 'aarav.sharma@example.com', '+91 98765 43210', 'Tech Solutions India', '101, MG Road, Bengaluru, Karnataka', NULL, 'active', '2026-06-01', '2026-06-04 11:04:44', NULL),
(12, 'Vivaan Singh', 'vivaan.singh@example.com', '+91 98765 43211', 'Digital Dreams', '202, Park Street, Kolkata, West Bengal', 4, 'active', '2026-06-01', '2026-06-04 12:23:24', '2026-06-04'),
(13, 'Aditya Verma', 'aditya.verma@example.com', '+91 98765 43212', 'Innovative Minds', '303, Civil Lines, Delhi', NULL, 'active', '2026-06-01', '2026-06-04 11:04:47', NULL),
(14, 'Vihaan Gupta', 'vihaan.gupta@example.com', '+91 98765 43213', 'Global Enterprises', '404, Marine Drive, Mumbai, Maharashtra', NULL, 'active', '2026-06-02', '2026-06-04 11:04:37', NULL),
(15, 'Arjun Nair', 'arjun.nair@example.com', '+91 98765 43214', 'Kerala Tech', '505, Brigade Road, Kochi, Kerala', NULL, 'active', '2026-06-02', '2026-06-04 12:21:16', NULL),
(16, 'Sai Reddy', 'sai.reddy@example.com', '+91 98765 43215', 'Hyderabad Solutions', '606, Banjara Hills, Hyderabad, Telangana', NULL, 'active', '2026-06-02', '2026-06-04 11:04:40', NULL),
(17, 'Ayaan Khan', 'ayaan.khan@example.com', '+91 98765 43216', 'Royal Industries', '707, Hazratganj, Lucknow, UP', NULL, 'active', '2026-06-02', '2026-06-04 11:04:41', NULL),
(18, 'Dhruv Joshi', 'dhruv.joshi@example.com', '+91 98765 43217', 'Mountain Soft', '808, Mall Road, Shimla, Himachal', NULL, 'active', '2026-06-03', '2026-06-04 11:04:30', NULL),
(19, 'Kabir Mehra', 'kabir.mehra@example.com', '+91 98765 43218', 'Punjab Inc', '909, Sector 17, Chandigarh', NULL, 'active', '2026-06-03', '2026-06-04 11:04:32', NULL),
(20, 'Reyansh Kaur', 'reyansh.kaur@example.com', '+91 98765 43219', 'Golden Harvest', '110, Lake Road, Amritsar, Punjab', NULL, 'active', '2026-06-03', '2026-06-04 11:04:33', NULL),
(21, 'Shaurya Malhotra', 'shaurya.malhotra@example.com', '+91 98765 43220', 'Delhi Connect', '111, Connaught Place, New Delhi', NULL, 'active', '2026-06-03', '2026-06-04 11:04:35', NULL),
(22, 'Advik Thakur', 'advik.thakur@example.com', '+91 98765 43221', 'Himalayan Tech', '112, Ridge Road, Manali, Himachal', NULL, 'active', '2026-06-03', '2026-06-04 11:04:36', NULL),
(23, 'Pranav Patil', 'pranav.patil@example.com', '+91 98765 43222', 'Pune IT Hub', '113, FC Road, Pune, Maharashtra', 4, 'active', '2026-06-04', '2026-06-04 12:23:15', '2026-06-04'),
(24, 'Ishaan Desai', 'ishaan.desai@example.com', '+91 98765 43223', 'Gujarat Solutions', '114, CG Road, Ahmedabad, Gujarat', NULL, 'active', '2026-06-04', '2026-06-04 09:04:59', NULL),
(25, 'Rudra Naik', 'rudra.naik@example.com', '+91 98765 43224', 'Goa Soft', '115, Calangute Beach Road, Goa', 4, 'active', '2026-06-04', '2026-06-04 12:23:24', '2026-06-04'),
(26, 'Ananya Iyer', 'ananya.iyer@example.com', '+91 98765 43225', 'Tamil Tech', '116, Cathedral Road, Chennai, Tamil Nadu', NULL, 'active', '2026-06-04', '2026-06-04 09:05:05', NULL),
(27, 'Diya Menon', 'diya.menon@example.com', '+91 98765 43226', 'Kerala Connect', '117, MG Road, Trivandrum, Kerala', NULL, 'active', '2026-06-04', '2026-06-04 09:05:03', NULL),
(28, 'Ananya Sharma', 'ananya.sharma@example.com', '+91 98765 43227', 'Jaipur Jewels', '118, MI Road, Jaipur, Rajasthan', 8, 'active', '2026-06-05', '2026-06-05 08:21:04', '2026-06-05'),
(29, 'Myra Singh', 'myra.singh@example.com', '+91 98765 43228', 'Bhopal Tech', '119, DB Mall Road, Bhopal, MP', 8, 'active', '2026-06-05', '2026-06-05 08:21:04', '2026-06-05'),
(30, 'Sarah Khan', 'sarah.khan@example.com', '+91 98765 43229', 'Lucknow Nawabs', '120, Gomti Nagar, Lucknow, UP', 8, 'active', '2026-06-05', '2026-06-05 08:21:04', '2026-06-05'),
(31, 'Kiara Reddy', 'kiara.reddy@example.com', '+91 98765 43230', 'Andhra Solutions', '121, RK Beach Road, Visakhapatnam, AP', NULL, 'active', '2026-06-05', '2026-06-04 11:04:25', NULL),
(32, 'Riya Gupta', 'riya.gupta@example.com', '+91 98765 43231', 'Indore IT Park', '122, Vijay Nagar, Indore, MP', NULL, 'active', '2026-06-05', '2026-06-04 12:21:14', NULL),
(33, 'Ishita Verma', 'ishita.verma@example.com', '+91 98765 43232', 'Nagpur Central', '123, Dharampeth, Nagpur, Maharashtra', 8, 'active', '2026-06-06', '2026-06-05 08:21:04', '2026-06-05'),
(34, 'Anushka Nair', 'anushka.nair@example.com', '+91 98765 43233', 'Kochi Startups', '124, Panampilly Nagar, Kochi, Kerala', 8, 'active', '2026-06-06', '2026-06-05 08:21:04', '2026-06-05'),
(35, 'Sanya Patil', 'sanya.patil@example.com', '+91 98765 43234', 'Kolhapur Tech', '125, Shahupuri, Kolhapur, Maharashtra', 8, 'active', '2026-06-06', '2026-06-05 08:21:04', '2026-06-05'),
(36, 'Tanvi Joshi', 'tanvi.joshi@example.com', '+91 98765 43235', 'Udaipur Royal', '126, Fateh Sagar Road, Udaipur, Rajasthan', 8, 'active', '2026-06-06', '2026-06-05 08:21:04', '2026-06-05'),
(37, 'Neha Thakur', 'neha.thakur@example.com', '+91 98765 43236', 'Shimla Hills', '127, The Mall, Shimla, Himachal', NULL, 'active', '2026-06-07', '2026-06-04 12:21:41', NULL),
(38, 'Pooja Malhotra', 'pooja.malhotra@example.com', '+91 98765 43237', 'Amritsar Golden', '128, Lawrence Road, Amritsar, Punjab', 8, 'active', '2026-06-07', '2026-06-05 08:21:04', '2026-06-05'),
(39, 'Meera Kaur', 'meera.kaur@example.com', '+91 98765 43238', 'Chandigarh Tech', '129, Sector 35, Chandigarh', 8, 'active', '2026-06-07', '2026-06-05 08:21:04', '2026-06-05'),
(40, 'Priya Desai', 'priya.desai@example.com', '+91 98765 43239', 'Vadodara Soft', '130, Alkapuri, Vadodara, Gujarat', 8, 'active', '2026-06-07', '2026-06-05 08:21:04', '2026-06-05'),
(41, 'Kavya Iyer', 'kavya.iyer@example.com', '+91 98765 43240', 'Coimbatore IT', '131, Race Course, Coimbatore, TN', NULL, 'active', '2026-06-08', '2026-06-04 12:21:35', NULL),
(42, 'Aditi Menon', 'aditi.menon@example.com', '+91 98765 43241', 'Mysore Palace', '132, JLB Road, Mysore, Karnataka', NULL, 'active', '2026-06-08', '2026-06-04 12:21:37', NULL),
(43, 'Trisha Naik', 'trisha.naik@example.com', '+91 98765 43242', 'Panaji Tech', '133, Miramar Beach, Panaji, Goa', 4, 'active', '2026-06-08', '2026-06-04 12:23:15', '2026-06-04'),
(44, 'Shreya Reddy', 'shreya.reddy@example.com', '+91 98765 43243', 'Warangal Tech', '134, Hanamkonda, Warangal, Telangana', NULL, 'active', '2026-06-08', '2026-06-04 12:21:40', NULL),
(45, 'Rajesh Kumar', 'rajesh.kumar@example.com', '+91 98765 43244', 'Patna Solutions', '135, Frazer Road, Patna, Bihar', NULL, 'active', '2026-06-09', '2026-06-04 12:21:29', NULL),
(46, 'Amit Singh', 'amit.singh@example.com', '+91 98765 43245', 'Noida IT Hub', '136, Sector 18, Noida, UP', NULL, 'active', '2026-06-09', '2026-06-04 12:21:30', NULL),
(47, 'Vikram Sharma', 'vikram.sharma@example.com', '+91 98765 43246', 'Gurgaon Tech', '137, Cyber City, Gurgaon, Haryana', NULL, 'active', '2026-06-09', '2026-06-04 12:21:32', NULL),
(48, 'Rohit Verma', 'rohit.verma@example.com', '+91 98765 43247', 'Faridabad Soft', '138, Sector 15, Faridabad, Haryana', NULL, 'active', '2026-06-09', '2026-06-04 12:21:33', NULL),
(49, 'Manish Gupta', 'manish.gupta@example.com', '+91 98765 43248', 'Agra Heritage', '139, Taj Road, Agra, UP', NULL, 'active', '2026-06-10', '2026-06-04 11:04:23', NULL),
(50, 'Sachin Nair', 'sachin.nair@example.com', '+91 98765 43249', 'Thiruvananthapuram', '140, Kowdiar, Trivandrum, Kerala', NULL, 'active', '2026-06-10', '2026-06-04 12:21:24', NULL),
(51, 'Rahul Joshi', 'rahul.joshi@example.com', '+91 98765 43250', 'Dehradun Soft', '141, Rajpur Road, Dehradun, Uttarakhand', NULL, 'active', '2026-06-10', '2026-06-04 12:21:26', NULL),
(52, 'Kunal Thakur', 'kunal.thakur@example.com', '+91 98765 43251', 'Jammu Tech', '142, Residency Road, Jammu', NULL, 'active', '2026-06-10', '2026-06-04 12:21:27', NULL),
(53, 'Deepak Kaur', 'deepak.kaur@example.com', '+91 98765 43252', 'Ludhiana Hub', '143, Model Town, Ludhiana, Punjab', 9, 'active', '2026-06-11', '2026-06-05 06:01:16', '2026-06-05'),
(54, 'Sunil Reddy', 'sunil.reddy@example.com', '+91 98765 43253', 'Nellore Solutions', '144, Grand Trunk Road, Nellore, AP', 9, 'active', '2026-06-11', '2026-06-05 06:01:16', '2026-06-05'),
(55, 'Ajay Deshmukh', 'ajay.deshmukh@example.com', '+91 98765 43254', 'Aurangabad IT', '145, Jalna Road, Aurangabad, MH', 9, 'active', '2026-06-11', '2026-06-05 06:01:16', '2026-06-05'),
(56, 'Vijay Patil', 'vijay.patil@example.com', '+91 98765 43255', 'Solapur Soft', '146, Hotgi Road, Solapur, Maharashtra', 9, 'active', '2026-06-11', '2026-06-05 06:01:16', '2026-06-05'),
(57, 'Sanjay Iyer', 'sanjay.iyer@example.com', '+91 98765 43256', 'Salem Tech', '147, Omalur Main Road, Salem, TN', 4, 'active', '2026-06-12', '2026-06-04 12:23:15', '2026-06-04'),
(58, 'Prakash Menon', 'prakash.menon@example.com', '+91 98765 43257', 'Tirupur Textiles', '148, Avinashi Road, Tirupur, TN', 9, 'active', '2026-06-12', '2026-06-05 08:25:27', '2026-06-05'),
(59, 'Mahesh Naik', 'mahesh.naik@example.com', '+91 98765 43258', 'Hubli Tech', '149, P B Road, Hubli, Karnataka', 9, 'active', '2026-06-12', '2026-06-05 06:01:16', '2026-06-05'),
(62, 'kuldeep', 'kuldeep@gmail.com', '9302077029', 'Sensible Computer', 'Green valy bhilai', NULL, 'active', '2026-06-04', '2026-06-04 11:04:28', NULL),
(63, 'Rakesh', '', '', '', '', NULL, 'active', '2026-06-05', '2026-06-05 13:39:01', NULL),
(64, 'Rakesh', '', '', '', '', NULL, 'active', '2026-06-05', '2026-06-05 13:39:33', NULL),
(65, 'ramesh', '', '121-212-1212', '', '', NULL, 'active', '2026-06-05', '2026-06-05 13:43:46', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments_history`
--
ALTER TABLE `assignments_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `call_records`
--
ALTER TABLE `call_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments_history`
--
ALTER TABLE `assignments_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `call_records`
--
ALTER TABLE `call_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments_history`
--
ALTER TABLE `assignments_history`
  ADD CONSTRAINT `assignments_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `assignments_history_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `assignments_history_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `employees` (`id`);

--
-- Constraints for table `call_records`
--
ALTER TABLE `call_records`
  ADD CONSTRAINT `call_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `call_records_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
