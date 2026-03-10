-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 08:16 PM
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
-- Database: `crust_pizza`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `created_at`, `action`, `details`) VALUES
(1, 1, '2025-06-08 01:00:45', 'user_login', 'User customer1 logged in'),
(2, 2, '2025-06-08 01:01:33', 'user_login', 'User admin logged in'),
(3, 2, '2025-06-08 01:21:24', 'user_login', 'User admin logged in'),
(4, 2, '2025-06-08 01:21:24', 'login', 'User admin logged in'),
(5, 1, '2025-06-08 01:26:35', 'user_login', 'User customer1 logged in'),
(6, 1, '2025-06-08 01:26:35', 'login', 'User customer1 logged in'),
(7, 2, '2025-06-08 01:38:01', 'user_login', 'User admin logged in'),
(8, 2, '2025-06-08 01:38:01', 'login', 'User admin logged in'),
(9, 1, '2025-06-08 01:49:46', 'user_login', 'User customer1 logged in'),
(10, 1, '2025-06-08 01:49:46', 'login', 'User customer1 logged in'),
(11, 1, '2025-06-08 02:07:19', 'user_login', 'User customer1 logged in'),
(12, 1, '2025-06-08 02:07:19', 'login', 'User customer1 logged in'),
(13, 1, '2025-06-08 02:15:32', 'user_login', 'User customer1 logged in'),
(14, 2, '2025-06-08 02:18:04', 'user_login', 'User admin logged in'),
(15, 1, '2025-06-08 02:18:21', 'user_login', 'User customer1 logged in'),
(16, 1, '2025-06-08 03:25:34', 'user_login', 'User customer1 logged in'),
(17, 1, '2025-06-08 03:49:35', 'user_login', 'User customer1 logged in'),
(18, 6, '2025-06-08 03:55:19', 'user_created', 'User bibek created'),
(19, 6, '2025-06-08 03:55:26', 'user_login', 'User bibek logged in'),
(20, 6, '2025-06-08 04:22:45', 'user_login', 'User bibek logged in'),
(21, 6, '2025-06-08 04:39:02', 'user_login', 'User bibek logged in'),
(22, 1, '2025-06-08 04:43:55', 'user_login', 'User customer1 logged in'),
(23, 2, '2025-06-08 05:15:24', 'user_login', 'User admin logged in'),
(24, 1, '2025-06-08 05:27:51', 'user_login', 'User customer1 logged in'),
(25, 5, '2025-06-08 05:28:44', 'user_login', 'User counter1 logged in'),
(26, 1, '2025-06-08 05:43:17', 'user_login', 'User customer1 logged in'),
(27, 2, '2025-06-08 05:49:26', 'user_login', 'User admin logged in'),
(28, 2, '2025-06-08 05:55:59', 'pizza_created', 'Pizza Danielle Harmon created'),
(29, 2, '2025-06-08 05:55:59', 'add_pizza', 'Added pizza: Danielle Harmon'),
(30, 2, '2025-06-08 05:56:17', 'pizza_created', 'Pizza new pizza created'),
(31, 2, '2025-06-08 05:56:17', 'add_pizza', 'Added pizza: new pizza'),
(32, 2, '2025-06-08 06:00:16', 'add_ingredient', 'Added ingredient: hello'),
(33, 2, '2025-06-08 06:01:29', 'add_menu_item', 'Added menu item: Charity Cain'),
(34, 2, '2025-06-08 06:01:43', 'add_menu_item', 'Added menu item: Aaku'),
(35, 6, '2025-06-08 06:02:39', 'user_login', 'User bibek logged in'),
(36, 2, '2025-06-08 06:19:44', 'user_login', 'User admin logged in'),
(37, 2, '2025-06-08 08:22:17', 'pizza_created', 'Pizza ABhi created'),
(38, 2, '2025-06-08 08:22:17', 'add_pizza', 'Added pizza: ABhi'),
(39, 6, '2025-06-08 09:57:51', 'user_login', 'User bibek logged in'),
(40, 2, '2025-06-08 10:10:54', 'user_login', 'User admin logged in'),
(41, 3, '2025-06-08 11:06:58', 'user_login', 'User kitchen1 logged in'),
(42, 4, '2025-06-08 11:13:54', 'user_login', 'User delivery1 logged in'),
(43, 4, '2025-06-08 11:19:00', 'user_login', 'User delivery1 logged in'),
(44, 6, '2025-06-08 11:24:30', 'user_login', 'User bibek logged in'),
(45, 1, '2025-06-08 11:41:50', 'user_login', 'User customer1 logged in'),
(46, 6, '2025-06-08 12:00:21', 'user_login', 'User bibek logged in'),
(47, 1, '2025-06-08 12:00:57', 'user_login', 'User customer1 logged in'),
(48, 6, '2025-06-08 12:18:16', 'user_login', 'User bibek logged in'),
(49, 1, '2025-06-08 12:24:59', 'user_login', 'User customer1 logged in'),
(50, 6, '2025-06-08 12:27:48', 'user_login', 'User bibek logged in'),
(51, 6, '2025-06-08 12:37:57', 'fetch_user_details_failed', 'Failed to fetch user details for user_id: 6'),
(52, 6, '2025-06-08 12:39:15', 'order_created', 'Order ORD202506081439152f47 created'),
(53, 6, '2025-06-08 12:40:29', 'user_login', 'User bibek logged in'),
(54, 6, '2025-06-08 12:56:45', 'order_created', 'Order ORD202506081456453111 created'),
(55, 6, '2025-06-08 12:56:45', 'user_updated', 'User profile updated'),
(56, 6, '2025-06-08 13:06:54', 'order_created', 'Order ORD20250608150654114a created'),
(57, 6, '2025-06-08 13:06:54', 'user_updated', 'User profile updated'),
(58, 6, '2025-06-08 13:07:52', 'order_created', 'Order ORD20250608150752cab0 created'),
(59, 6, '2025-06-08 13:07:52', 'user_updated', 'User profile updated'),
(60, 6, '2025-06-08 13:11:33', 'order_created', 'Order ORD20250608151133b166 created'),
(61, 6, '2025-06-08 13:11:33', 'user_updated', 'User profile updated'),
(62, 1, '2025-06-08 13:13:17', 'user_login', 'User customer1 logged in'),
(63, 1, '2025-06-08 13:13:59', 'order_created', 'Order ORD20250608151359271c created'),
(64, 1, '2025-06-08 13:13:59', 'user_updated', 'User profile updated'),
(65, 2, '2025-06-08 13:16:18', 'user_login', 'User admin logged in'),
(66, 2, '2025-06-08 13:17:52', 'pizza_updated', 'Pizza ID 1 updated'),
(67, 2, '2025-06-08 13:17:52', 'update_pizza', 'Updated pizza: Peri Peri Chicken'),
(68, 2, '2025-06-08 13:18:03', 'pizza_updated', 'Pizza ID 6 updated'),
(69, 2, '2025-06-08 13:18:03', 'update_pizza', 'Updated pizza: Supreme'),
(70, 2, '2025-06-08 13:18:16', 'pizza_updated', 'Pizza ID 2 updated'),
(71, 2, '2025-06-08 13:18:16', 'update_pizza', 'Updated pizza: Margherita'),
(72, 2, '2025-06-08 13:18:29', 'pizza_updated', 'Pizza ID 12 updated'),
(73, 2, '2025-06-08 13:18:29', 'update_pizza', 'Updated pizza: new pizza'),
(74, 2, '2025-06-08 13:19:04', 'pizza_deleted', 'Pizza ID 13 deleted'),
(75, 2, '2025-06-08 13:19:04', 'delete_pizza', 'Deleted pizza ID: 13'),
(76, 2, '2025-06-08 13:19:19', 'pizza_updated', 'Pizza ID 7 updated'),
(77, 2, '2025-06-08 13:19:19', 'update_pizza', 'Updated pizza: BBQ Chicken'),
(78, 2, '2025-06-08 13:19:48', 'pizza_updated', 'Pizza ID 5 updated'),
(79, 2, '2025-06-08 13:19:48', 'update_pizza', 'Updated pizza: Hawaiian'),
(80, 2, '2025-06-08 13:20:09', 'pizza_updated', 'Pizza ID 4 updated'),
(81, 2, '2025-06-08 13:20:09', 'update_pizza', 'Updated pizza: Meat Lovers'),
(82, 2, '2025-06-08 13:20:22', 'pizza_updated', 'Pizza ID 3 updated'),
(83, 2, '2025-06-08 13:20:22', 'update_pizza', 'Updated pizza: Vegan Supreme'),
(84, 2, '2025-06-08 13:20:45', 'pizza_updated', 'Pizza ID 8 updated'),
(85, 2, '2025-06-08 13:20:45', 'update_pizza', 'Updated pizza: Prosciutto &amp; Rocket'),
(86, 3, '2025-06-08 13:25:28', 'user_login', 'User kitchen1 logged in'),
(87, 3, '2025-06-08 13:25:42', 'order_status_updated', 'Order status updated to prepared'),
(88, 3, '2025-06-08 13:25:44', 'update_order_status', 'Kitchen staff marked order ID: 3 as Prepared'),
(89, 5, '2025-06-08 13:25:59', 'user_login', 'User counter1 logged in'),
(90, 4, '2025-06-08 13:26:56', 'user_login', 'User delivery1 logged in'),
(91, 4, '2025-06-08 13:27:05', 'order_status_updated', 'Order status updated to out_for_delivery'),
(92, 4, '2025-06-08 13:27:07', 'update_order_status', 'Delivery staff updated order ID: 3 to out_for_delivery'),
(93, 5, '2025-06-08 13:27:18', 'user_login', 'User counter1 logged in'),
(94, 5, '2025-06-08 13:32:43', 'user_login', 'User counter1 logged in'),
(95, 5, '2025-06-08 13:32:47', 'order_status_updated', 'Order status updated to received_by_customer'),
(96, 5, '2025-06-08 13:32:49', 'update_order_status', 'Counter staff updated order ID: 6 to received_by_customer'),
(97, 2, '2025-06-08 14:19:30', 'user_login', 'User admin logged in'),
(98, 2, '2025-06-08 14:19:51', 'pizza_updated', 'Pizza ID 2 updated'),
(99, 2, '2025-06-08 14:19:51', 'update_pizza', 'Updated pizza: Margherita'),
(100, 2, '2025-06-08 14:20:20', 'pizza_created', 'Pizza new pizza created'),
(101, 2, '2025-06-08 14:20:20', 'add_pizza', 'Added pizza: new pizza'),
(102, 2, '2025-06-08 14:24:47', 'pizza_created', 'Pizza Slice created'),
(103, 2, '2025-06-08 14:24:47', 'add_pizza', 'Added pizza: Slice'),
(104, 2, '2025-06-08 14:29:01', 'pizza_created', 'Pizza September Patton created'),
(105, 2, '2025-06-08 14:29:01', 'add_pizza', 'Added pizza: September Patton'),
(106, 2, '2025-06-08 14:32:38', 'pizza_updated', 'Pizza ID 15 updated'),
(107, 2, '2025-06-08 14:32:38', 'update_pizza', 'Updated pizza: Slice'),
(108, 2, '2025-06-08 14:46:56', 'image_upload_success', 'Uploaded image for pizza: Margherita to /assets/public/uploads/6845a260e9d85_1749394016.jpg'),
(109, 2, '2025-06-08 14:46:56', 'pizza_updated', 'Pizza ID 2 updated'),
(110, 2, '2025-06-08 14:46:56', 'update_pizza', 'Updated pizza: Margherita'),
(111, 2, '2025-06-08 14:47:18', 'image_upload_success', 'Uploaded image for pizza: Meat Lovers to /assets/public/uploads/6845a27602c89_1749394038.jpg'),
(112, 2, '2025-06-08 14:47:18', 'pizza_updated', 'Pizza ID 4 updated'),
(113, 2, '2025-06-08 14:47:18', 'update_pizza', 'Updated pizza: Meat Lovers'),
(114, 2, '2025-06-08 14:47:22', 'pizza_deleted', 'Pizza ID 14 deleted'),
(115, 2, '2025-06-08 14:47:22', 'delete_pizza', 'Deleted pizza ID: 14'),
(116, 2, '2025-06-08 14:47:31', 'image_upload_success', 'Uploaded image for pizza: Peri Peri Chicken to /assets/public/uploads/6845a283b6b23_1749394051.jpg'),
(117, 2, '2025-06-08 14:47:31', 'pizza_updated', 'Pizza ID 1 updated'),
(118, 2, '2025-06-08 14:47:31', 'update_pizza', 'Updated pizza: Peri Peri Chicken'),
(119, 2, '2025-06-08 14:47:54', 'image_upload_success', 'Uploaded image for pizza: Prosciutto Rocket to /assets/public/uploads/6845a29a67610_1749394074.jpg'),
(120, 2, '2025-06-08 14:47:54', 'pizza_updated', 'Pizza ID 8 updated'),
(121, 2, '2025-06-08 14:47:54', 'update_pizza', 'Updated pizza: Prosciutto Rocket'),
(122, 2, '2025-06-08 14:48:00', 'pizza_deleted', 'Pizza ID 15 deleted'),
(123, 2, '2025-06-08 14:48:00', 'image_delete_success', 'Deleted image for pizza ID: 15'),
(124, 2, '2025-06-08 14:48:00', 'delete_pizza', 'Deleted pizza ID: 15'),
(125, 6, '2025-06-08 15:10:02', 'user_login', 'User bibek logged in'),
(126, 2, '2025-06-08 15:22:05', 'user_login', 'User admin logged in'),
(127, 2, '2025-06-08 15:22:30', 'image_upload_success', 'Uploaded image for pizza: Vegan Supreme to /assets/public/uploads/6845aab6dcb8a_1749396150.jpg'),
(128, 2, '2025-06-08 15:22:30', 'pizza_updated', 'Pizza ID 3 updated'),
(129, 2, '2025-06-08 15:22:30', 'update_pizza', 'Updated pizza: Vegan Supreme'),
(130, 2, '2025-06-08 15:22:54', 'image_upload_success', 'Uploaded image for pizza: Supreme to /assets/public/uploads/6845aaceac5cf_1749396174.jpg'),
(131, 2, '2025-06-08 15:22:54', 'pizza_updated', 'Pizza ID 6 updated'),
(132, 2, '2025-06-08 15:22:54', 'update_pizza', 'Updated pizza: Supreme'),
(133, 2, '2025-06-08 15:23:16', 'image_upload_success', 'Uploaded image for pizza: Hawaiian to /assets/public/uploads/6845aae4ad453_1749396196.jpg'),
(134, 2, '2025-06-08 15:23:16', 'pizza_updated', 'Pizza ID 5 updated'),
(135, 2, '2025-06-08 15:23:16', 'update_pizza', 'Updated pizza: Hawaiian'),
(136, 2, '2025-06-08 15:23:37', 'image_upload_success', 'Uploaded image for pizza: BBQ Chicken to /assets/public/uploads/6845aaf99167e_1749396217.jpg'),
(137, 2, '2025-06-08 15:23:37', 'pizza_updated', 'Pizza ID 7 updated'),
(138, 2, '2025-06-08 15:23:37', 'update_pizza', 'Updated pizza: BBQ Chicken'),
(139, 6, '2025-06-08 16:18:44', 'user_login', 'User bibek logged in'),
(140, 2, '2025-06-08 16:32:54', 'user_login', 'User admin logged in'),
(141, 2, '2025-06-08 16:33:22', 'image_upload_success', 'Uploaded image for pizza: Margherita to /assets/public/uploads/6845bb52ece6c_1749400402.jpg'),
(142, 2, '2025-06-08 16:33:22', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845a260e9d85_1749394016.jpg'),
(143, 2, '2025-06-08 16:33:22', 'pizza_updated', 'Pizza ID 2 updated'),
(144, 2, '2025-06-08 16:33:23', 'update_pizza', 'Updated pizza: Margherita'),
(145, 2, '2025-06-08 16:33:33', 'image_upload_success', 'Uploaded image for pizza: Meat Lovers to /assets/public/uploads/6845bb5d499ef_1749400413.jpg'),
(146, 2, '2025-06-08 16:33:33', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845a27602c89_1749394038.jpg'),
(147, 2, '2025-06-08 16:33:33', 'pizza_updated', 'Pizza ID 4 updated'),
(148, 2, '2025-06-08 16:33:33', 'update_pizza', 'Updated pizza: Meat Lovers'),
(149, 2, '2025-06-08 16:33:44', 'image_upload_success', 'Uploaded image for pizza: Peri Peri Chicken to /assets/public/uploads/6845bb6816015_1749400424.jpg'),
(150, 2, '2025-06-08 16:33:44', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845a283b6b23_1749394051.jpg'),
(151, 2, '2025-06-08 16:33:44', 'pizza_updated', 'Pizza ID 1 updated'),
(152, 2, '2025-06-08 16:33:44', 'update_pizza', 'Updated pizza: Peri Peri Chicken'),
(153, 2, '2025-06-08 16:33:56', 'image_upload_success', 'Uploaded image for pizza: Prosciutto Rocket to /assets/public/uploads/6845bb74da42c_1749400436.jpg'),
(154, 2, '2025-06-08 16:33:56', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845a29a67610_1749394074.jpg'),
(155, 2, '2025-06-08 16:33:56', 'pizza_updated', 'Pizza ID 8 updated'),
(156, 2, '2025-06-08 16:33:56', 'update_pizza', 'Updated pizza: Prosciutto Rocket'),
(157, 2, '2025-06-08 16:34:11', 'image_upload_success', 'Uploaded image for pizza: Supreme to /assets/public/uploads/6845bb835dcab_1749400451.jpeg'),
(158, 2, '2025-06-08 16:34:11', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845aaceac5cf_1749396174.jpg'),
(159, 2, '2025-06-08 16:34:11', 'pizza_updated', 'Pizza ID 6 updated'),
(160, 2, '2025-06-08 16:34:11', 'update_pizza', 'Updated pizza: Supreme'),
(161, 2, '2025-06-08 16:34:22', 'image_upload_success', 'Uploaded image for pizza: Vegan Supreme to /assets/public/uploads/6845bb8e27337_1749400462.jpg'),
(162, 2, '2025-06-08 16:34:22', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845aab6dcb8a_1749396150.jpg'),
(163, 2, '2025-06-08 16:34:22', 'pizza_updated', 'Pizza ID 3 updated'),
(164, 2, '2025-06-08 16:34:22', 'update_pizza', 'Updated pizza: Vegan Supreme'),
(165, 2, '2025-06-08 16:34:27', 'pizza_updated', 'Pizza ID 7 updated'),
(166, 2, '2025-06-08 16:34:27', 'update_pizza', 'Updated pizza: BBQ Chicken'),
(167, 2, '2025-06-08 16:34:37', 'image_upload_success', 'Uploaded image for pizza: BBQ Chicken to /assets/public/uploads/6845bb9d099f6_1749400477.jpg'),
(168, 2, '2025-06-08 16:34:37', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845aaf99167e_1749396217.jpg'),
(169, 2, '2025-06-08 16:34:37', 'pizza_updated', 'Pizza ID 7 updated'),
(170, 2, '2025-06-08 16:34:37', 'update_pizza', 'Updated pizza: BBQ Chicken'),
(171, 2, '2025-06-08 16:34:51', 'image_upload_success', 'Uploaded image for pizza: Hawaiian to /assets/public/uploads/6845bbab3b2eb_1749400491.jpg'),
(172, 2, '2025-06-08 16:34:51', 'image_delete_success', 'Deleted old image: assets/public/uploads/6845aae4ad453_1749396196.jpg'),
(173, 2, '2025-06-08 16:34:51', 'pizza_updated', 'Pizza ID 5 updated'),
(174, 2, '2025-06-08 16:34:51', 'update_pizza', 'Updated pizza: Hawaiian'),
(175, 3, '2025-06-08 16:36:06', 'user_login', 'User kitchen1 logged in'),
(176, 4, '2025-06-08 16:36:17', 'user_login', 'User delivery1 logged in'),
(177, 5, '2025-06-08 16:36:28', 'user_login', 'User counter1 logged in'),
(178, 2, '2025-06-08 16:41:22', 'user_login', 'User admin logged in'),
(179, 6, '2025-06-08 16:44:16', 'user_login', 'User bibek logged in'),
(180, 1, '2025-06-08 17:08:38', 'user_login', 'User customer1 logged in'),
(181, 6, '2025-06-08 17:11:58', 'user_login', 'User bibek logged in'),
(182, 6, '2025-06-08 17:22:50', 'order_created', 'Order ORD20250608192250d777 created'),
(183, 6, '2025-06-08 17:22:50', 'user_updated', 'User profile updated'),
(184, 5, '2025-06-08 17:23:16', 'user_login', 'User counter1 logged in'),
(185, 6, '2025-06-08 17:23:27', 'user_login', 'User bibek logged in'),
(186, 6, '2025-06-08 17:33:05', 'order_created', 'Order ORD2025060819330562ea created'),
(187, 6, '2025-06-08 17:33:05', 'user_updated', 'User profile updated'),
(188, 6, '2025-06-08 17:37:59', 'order_created', 'Order ORD20250608193759d73d created'),
(189, 6, '2025-06-08 17:37:59', 'user_updated', 'User profile updated'),
(190, 6, '2025-06-08 18:06:23', 'order_created', 'Order ORD20250608200623b960 created'),
(191, 6, '2025-06-08 18:06:23', 'user_updated', 'User profile updated'),
(192, 6, '2025-06-08 18:07:22', 'order_created', 'Order ORD202506082007224eb8 created'),
(193, 6, '2025-06-08 18:07:22', 'user_updated', 'User profile updated'),
(194, 5, '2025-06-08 18:07:38', 'user_login', 'User counter1 logged in'),
(195, 3, '2025-06-08 18:08:04', 'user_login', 'User kitchen1 logged in'),
(196, 3, '2025-06-08 18:10:34', 'order_status_updated', 'Order status updated to prepared'),
(197, 3, '2025-06-08 18:10:36', 'update_order_status', 'Kitchen staff marked order ID: 12 as Prepared'),
(198, 5, '2025-06-08 18:10:50', 'user_login', 'User counter1 logged in'),
(199, 4, '2025-06-08 18:11:00', 'user_login', 'User delivery1 logged in'),
(200, 4, '2025-06-08 18:11:07', 'order_status_updated', 'Order status updated to out_for_delivery'),
(201, 4, '2025-06-08 18:11:09', 'update_order_status', 'Delivery staff updated order ID: 12 to out_for_delivery'),
(202, 5, '2025-06-08 18:11:20', 'user_login', 'User counter1 logged in'),
(203, 5, '2025-06-08 18:13:57', 'order_status_updated', 'Order status updated to received_by_customer'),
(204, 5, '2025-06-08 18:13:59', 'update_order_status', 'Counter staff updated order ID: 7 to received_by_customer'),
(205, 6, '2025-06-08 18:15:42', 'user_login', 'User bibek logged in');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` varchar(50) NOT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `size` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `custom_ingredients` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `item_type`, `pizza_id`, `menu_item_id`, `item_name`, `size`, `quantity`, `unit_price`, `total_price`, `custom_ingredients`, `special_instructions`, `created_at`, `updated_at`) VALUES
(24, 2, 'pizza', 2, NULL, 'Margherita', 'medium', 1, 19.08, 19.08, NULL, NULL, '2025-06-08 15:09:48', '2025-06-08 15:09:48'),
(26, 2, 'pizza', 8, NULL, 'Prosciutto Rocket', 'medium', 1, 27.48, 27.48, NULL, NULL, '2025-06-08 15:24:50', '2025-06-08 15:24:50'),
(27, 2, 'pizza', 1, NULL, 'Peri Peri Chicken', 'medium', 1, 22.68, 22.68, NULL, NULL, '2025-06-08 15:39:53', '2025-06-08 15:39:53'),
(32, 2, 'pizza', 4, NULL, 'Meat Lovers', 'medium', 1, 26.28, 26.28, NULL, NULL, '2025-06-08 16:41:30', '2025-06-08 16:41:30'),
(41, 1, 'pizza', 2, NULL, 'Margherita', 'medium', 1, 19.08, 19.08, NULL, NULL, '2025-06-08 17:09:31', '2025-06-08 17:09:31');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `item_type` enum('pizza','menu_item') NOT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `size` enum('small','medium','large') DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `user_id`, `session_id`, `item_type`, `pizza_id`, `menu_item_id`, `size`, `quantity`, `created_at`) VALUES
(1, 1, 'session123', 'pizza', 1, NULL, 'large', 1, '2025-06-07 16:52:42'),
(2, 1, 'session123', 'menu_item', NULL, 3, NULL, 2, '2025-06-07 16:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item_ingredients`
--

CREATE TABLE `cart_item_ingredients` (
  `cart_item_ingredient_id` int(11) NOT NULL,
  `cart_item_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `quantity` decimal(5,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item_ingredients`
--

INSERT INTO `cart_item_ingredients` (`cart_item_ingredient_id`, `cart_item_id`, `ingredient_id`, `quantity`) VALUES
(1, 1, 13, 1.00),
(2, 1, 35, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `image_url`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Signature', 'Our award-winning signature pizzas', NULL, 1, 1, '2025-06-07 16:52:41'),
(2, 'Classic', 'Traditional pizza favorites', NULL, 2, 1, '2025-06-07 16:52:41'),
(3, 'Vegan', 'Plant-based pizza options', NULL, 3, 1, '2025-06-07 16:52:41'),
(4, 'Meat Lovers', 'For the carnivores', NULL, 4, 1, '2025-06-07 16:52:41'),
(5, 'Gourmet', 'Premium ingredient combinations', NULL, 5, 1, '2025-06-07 16:52:41'),
(6, 'Sides', 'Appetizers and side dishes', NULL, 6, 1, '2025-06-07 16:52:41'),
(7, 'Drinks', 'Beverages and refreshments', NULL, 7, 1, '2025-06-07 16:52:41'),
(8, 'Desserts', 'Sweet treats to finish your meal', NULL, 8, 1, '2025-06-07 16:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed_amount') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `valid_from` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid_until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `name`, `description`, `discount_type`, `discount_value`, `minimum_order_amount`, `max_discount_amount`, `usage_limit`, `used_count`, `valid_from`, `valid_until`, `is_active`, `created_at`) VALUES
(1, 'WELCOME10', 'Welcome Discount', '10% off your first order', 'percentage', 10.00, 25.00, NULL, 1, 0, '2025-06-07 16:52:41', '2024-12-31 18:14:59', 1, '2025-06-07 16:52:41'),
(2, 'PIZZA20', 'Pizza Special', '$20 off orders over $50', 'fixed_amount', 20.00, 50.00, NULL, 100, 0, '2025-06-07 16:52:41', '2024-12-31 18:14:59', 1, '2025-06-07 16:52:41'),
(3, 'STUDENT15', 'Student Discount', '15% off with valid student ID', 'percentage', 15.00, 20.00, NULL, 1000, 0, '2025-06-07 16:52:41', '2024-12-31 18:14:59', 1, '2025-06-07 16:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('crust','sauce','cheese','meat','vegetable','other') NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 10,
  `is_available` tinyint(1) DEFAULT 1,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `allergens` text DEFAULT NULL,
  `nutritional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nutritional_info`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `name`, `category`, `price`, `cost`, `stock_quantity`, `min_stock_level`, `is_available`, `is_vegan`, `is_gluten_free`, `allergens`, `nutritional_info`, `created_at`, `updated_at`, `stock`) VALUES
(1, 'Thin Crust', 'crust', 0.00, 1.50, 100, 10, 1, 1, 0, 'Gluten', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(2, 'Classic Crust', 'crust', 0.00, 1.80, 100, 10, 1, 1, 0, 'Gluten', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(3, 'Thick Crust', 'crust', 2.00, 2.20, 100, 10, 1, 1, 0, 'Gluten', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(4, 'Gluten Free Crust', 'crust', 3.00, 3.50, 50, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(5, 'Cauliflower Crust', 'crust', 4.00, 4.50, 30, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(6, 'Tomato Base', 'sauce', 0.00, 0.50, 200, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(7, 'BBQ Sauce', 'sauce', 1.00, 0.60, 150, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(8, 'Pesto', 'sauce', 1.50, 1.00, 100, 10, 1, 0, 1, 'Nuts', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(9, 'Garlic Base', 'sauce', 1.00, 0.70, 120, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(10, 'Buffalo Sauce', 'sauce', 1.50, 0.80, 80, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(11, 'White Sauce', 'sauce', 1.50, 0.90, 90, 10, 1, 0, 1, 'Dairy', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(12, 'Mozzarella', 'cheese', 0.00, 2.00, 200, 10, 1, 0, 1, 'Dairy', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(13, 'Extra Mozzarella', 'cheese', 2.50, 3.00, 150, 10, 1, 0, 1, 'Dairy', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(14, 'Vegan Cheese', 'cheese', 3.00, 3.50, 80, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(15, 'Parmesan', 'cheese', 2.00, 2.50, 100, 10, 1, 0, 1, 'Dairy', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(16, 'Feta', 'cheese', 2.50, 3.00, 70, 10, 1, 0, 1, 'Dairy', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(17, 'Ricotta', 'cheese', 2.00, 2.30, 60, 10, 1, 0, 1, 'Dairy', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(18, 'Pepperoni', 'meat', 2.50, 3.00, 150, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(19, 'Chicken', 'meat', 3.50, 4.00, 120, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(20, 'Ham', 'meat', 2.50, 3.00, 100, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(21, 'Bacon', 'meat', 3.00, 3.50, 80, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(22, 'Italian Sausage', 'meat', 3.00, 3.50, 90, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(23, 'Prosciutto', 'meat', 4.00, 5.00, 50, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(24, 'Salami', 'meat', 2.50, 3.00, 70, 10, 1, 0, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(25, 'Anchovies', 'meat', 2.00, 2.50, 40, 10, 1, 0, 1, 'Fish', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(26, 'Mushrooms', 'vegetable', 1.50, 1.00, 100, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(27, 'Capsicum', 'vegetable', 1.50, 1.00, 120, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(28, 'Red Onion', 'vegetable', 1.00, 0.70, 150, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(29, 'Olives', 'vegetable', 2.00, 1.50, 80, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(30, 'Cherry Tomatoes', 'vegetable', 2.00, 1.30, 90, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(31, 'Pineapple', 'vegetable', 2.00, 1.20, 70, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(32, 'Baby Spinach', 'vegetable', 1.50, 1.00, 60, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(33, 'Roasted Eggplant', 'vegetable', 2.50, 1.80, 50, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(34, 'Sun-dried Tomatoes', 'vegetable', 2.50, 2.00, 40, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(35, 'Caramelized Onions', 'vegetable', 2.00, 1.50, 60, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(36, 'Jalapeños', 'vegetable', 1.50, 1.00, 80, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(37, 'Artichokes', 'vegetable', 3.00, 2.50, 30, 10, 1, 1, 1, '', NULL, '2025-06-07 16:52:41', '2025-06-07 16:52:41', 0),
(38, 'hello', 'crust', 693.00, 0.00, 0, 10, 1, 0, 0, NULL, NULL, '2025-06-08 06:00:16', '2025-06-08 06:00:16', 0);

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `point_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_redeemed` int(11) DEFAULT 0,
  `transaction_type` enum('earned','redeemed','expired','bonus') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_points`
--

INSERT INTO `loyalty_points` (`point_id`, `user_id`, `order_id`, `points_earned`, `points_redeemed`, `transaction_type`, `description`, `created_at`) VALUES
(1, 1, 1, 44, 0, 'earned', 'Points earned from order ORD001', '2025-06-07 16:52:42'),
(2, 1, 2, 27, 0, 'earned', 'Points earned from order ORD002', '2025-06-07 16:52:42'),
(3, 1, NULL, 0, 20, 'redeemed', 'Redeemed for discount on next order', '2025-06-07 16:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `menu_item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `prep_time_minutes` int(11) DEFAULT 5,
  `calories` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free` tinyint(1) DEFAULT 0,
  `allergens` text DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `popularity_score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`menu_item_id`, `name`, `description`, `category_id`, `price`, `cost`, `image_url`, `prep_time_minutes`, `calories`, `is_available`, `is_featured`, `is_vegan`, `is_gluten_free`, `allergens`, `stock_quantity`, `popularity_score`, `created_at`, `updated_at`) VALUES
(1, 'Garlic Bread', 'Fresh baked bread with garlic butter', 6, 8.90, 3.50, NULL, 8, 320, 1, 1, 0, 0, 'Gluten, Dairy', 50, 85, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(2, 'Vegan Garlic Bread', 'Fresh baked bread with vegan garlic spread', 6, 9.90, 4.00, NULL, 8, 300, 1, 0, 1, 0, 'Gluten', 30, 60, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(3, 'Chicken Wings (6pc)', 'Crispy wings with your choice of sauce', 6, 12.90, 6.50, NULL, 12, 480, 1, 1, 0, 1, '', 40, 90, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(4, 'Chicken Wings (12pc)', 'Crispy wings with your choice of sauce', 6, 22.90, 11.50, NULL, 15, 960, 1, 0, 0, 1, '', 30, 75, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(5, 'Potato Wedges', 'Crispy seasoned potato wedges with sour cream', 6, 9.90, 4.20, NULL, 10, 380, 1, 0, 0, 1, 'Dairy', 40, 70, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(6, 'Caesar Salad', 'Fresh romaine lettuce with caesar dressing and croutons', 6, 11.90, 5.50, NULL, 5, 280, 1, 0, 0, 0, 'Gluten, Dairy, Eggs', 25, 65, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(7, 'Mozzarella Sticks (6pc)', 'Crispy mozzarella sticks with marinara sauce', 6, 10.90, 5.20, NULL, 8, 420, 1, 0, 0, 0, 'Gluten, Dairy', 35, 80, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(8, 'Coca Cola 375ml', 'Classic soft drink', 7, 3.50, 1.20, NULL, 1, 140, 1, 0, 1, 1, '', 100, 95, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(9, 'Coca Cola 1.25L', 'Classic soft drink family size', 7, 5.50, 2.00, NULL, 1, 560, 1, 0, 1, 1, '', 50, 70, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(10, 'Sprite 375ml', 'Lemon-lime soft drink', 7, 3.50, 1.20, NULL, 1, 135, 1, 0, 1, 1, '', 80, 75, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(11, 'Orange Juice 375ml', 'Fresh orange juice', 7, 4.50, 2.00, NULL, 1, 160, 1, 0, 1, 1, '', 60, 60, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(12, 'Water 600ml', 'Still water', 7, 2.50, 0.80, NULL, 1, 0, 1, 0, 1, 1, '', 100, 50, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(13, 'Sparkling Water 375ml', 'Sparkling mineral water', 7, 3.00, 1.00, NULL, 1, 0, 1, 0, 1, 1, '', 70, 40, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(14, 'Chocolate Brownie', 'Rich chocolate brownie with vanilla ice cream', 8, 8.90, 3.50, NULL, 5, 420, 1, 1, 0, 0, 'Gluten, Dairy, Eggs, Nuts', 20, 85, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(15, 'Tiramisu', 'Classic Italian dessert', 8, 9.90, 4.20, NULL, 3, 380, 1, 0, 0, 0, 'Gluten, Dairy, Eggs', 15, 70, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(16, 'Gelato (2 scoops)', 'Choice of vanilla, chocolate, or strawberry', 8, 6.90, 2.80, NULL, 2, 250, 1, 0, 0, 1, 'Dairy', 30, 75, '2025-06-07 16:52:41', '2025-06-07 16:52:41'),
(17, 'Charity Cain', 'Numquam praesentium', 6, 699.00, 15.00, '', 49, 56, 1, 1, 1, 1, 'Sunt et voluptatem', 833, 96, '2025-06-08 06:01:29', '2025-06-08 06:01:29'),
(18, 'Aaku', 'Veniam soluta irure', 4, 249.00, 21.00, '', 31, 40, 1, 1, 1, 1, 'Aliquip qui quidem o', 446, 7, '2025-06-08 06:01:43', '2025-06-08 06:01:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `type` enum('order_update','promotion','system','reminder') NOT NULL,
  `title` varchar(265) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `staff_id`, `type`, `title`, `message`, `is_read`, `action_url`, `created_at`) VALUES
(1, 1, NULL, 'order_update', 'Order Confirmation', 'Your order ORD001 has been confirmed!', 1, '/orders/1', '2025-06-07 16:52:42'),
(2, 1, NULL, 'promotion', 'Special Offer', 'Get 10% off your next order with code WELCOME10', 0, '/promotions', '2025-06-07 16:52:42'),
(3, NULL, 2, 'system', 'Inventory Alert', 'Low stock on Gluten Free Crust', 0, '/inventory', '2025-06-07 16:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `order_type` enum('delivery','pickup') NOT NULL,
  `status` enum('pending','confirmed','preparing','prepared','out_for_delivery','ready_for_pickup','delivered','delivery_failure','received_by_customer','cancelled') DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','online','paypal','apple_pay') NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded','partial') DEFAULT 'pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `estimated_prep_time` int(11) DEFAULT 20,
  `estimated_delivery_time` timestamp NULL DEFAULT NULL,
  `actual_delivery_time` timestamp NULL DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `user_id`, `store_id`, `order_type`, `status`, `priority`, `subtotal`, `tax`, `delivery_fee`, `discount_amount`, `total`, `payment_method`, `payment_status`, `payment_reference`, `customer_name`, `customer_phone`, `customer_email`, `delivery_address`, `delivery_instructions`, `estimated_prep_time`, `estimated_delivery_time`, `actual_delivery_time`, `assigned_staff_id`, `special_requests`, `rating`, `review`, `created_at`, `updated_at`) VALUES
(1, 'ORD001', 1, 1, 'delivery', 'prepared', 'normal', 38.80, 3.88, 5.50, 3.88, 44.30, 'card', 'paid', NULL, 'John Customer', '0412345678', 'customer@example.com', '123 Test Street, Sydney NSW 2000', 'Leave at front door', 20, '2025-06-06 08:15:00', NULL, 4, 'No onions please', 5, 'Great service!', '2025-06-06 06:15:00', '2025-06-08 13:32:00'),
(2, 'ORD002', 1, 1, 'pickup', 'pending', 'normal', 24.90, 2.49, 0.00, 0.00, 27.39, 'cash', 'paid', NULL, 'John Customer', '0412345678', 'customer@example.com', NULL, NULL, 15, NULL, NULL, NULL, NULL, 4, 'Pizza was delicious', '2025-06-05 12:15:00', '2025-06-08 13:31:53'),
(3, 'ORD202506081439152f4', 6, 1, 'delivery', 'out_for_delivery', 'normal', 100.60, 10.06, 0.00, 0.00, 110.66, 'cash', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', ', ,  ', '', 30, NULL, NULL, 4, '', NULL, NULL, '2025-06-08 12:39:15', '2025-06-08 13:27:05'),
(4, 'ORD20250608145645311', 6, 1, 'delivery', 'preparing', 'normal', 27.90, 2.79, 5.99, 0.00, 36.68, '', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 12:56:45', '2025-06-08 16:58:19'),
(5, 'ORD20250608150654114', 6, 1, 'delivery', 'confirmed', 'normal', 52.80, 5.28, 0.00, 0.00, 58.08, '', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 13:06:54', '2025-06-08 16:58:28'),
(6, 'ORD20250608150752cab', 6, 1, 'pickup', 'received_by_customer', 'normal', 27.90, 2.79, 5.99, 0.00, 36.68, '', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, '2025-06-08 13:32:47', 5, '', NULL, NULL, '2025-06-08 13:07:52', '2025-06-08 18:14:44'),
(7, 'ORD20250608151133b16', 6, 1, 'delivery', 'received_by_customer', 'normal', 47.80, 4.78, 0.00, 0.00, 52.58, '', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, '2025-06-08 18:13:57', 5, '', NULL, NULL, '2025-06-08 13:11:33', '2025-06-08 18:13:57'),
(8, 'ORD20250608151359271', 1, 1, 'pickup', 'prepared', 'normal', 261.90, 26.19, 0.00, 0.00, 288.09, '', 'pending', NULL, 'John Customer', '0412345678', 'customer@example.com', '123 Test Street, Sydney, NSW 2000', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 13:13:59', '2025-06-08 18:15:12'),
(9, 'ORD20250608192250d77', 6, 1, 'pickup', 'prepared', 'normal', 143.28, 14.33, 0.00, 0.00, 157.61, '', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', '', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 17:22:50', '2025-06-08 18:14:29'),
(10, 'ORD2025060819330562e', 6, 1, '', 'pending', 'normal', 22.68, 2.27, 0.00, 0.00, 24.95, 'online', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', '', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 17:33:05', '2025-06-08 17:33:05'),
(11, 'ORD20250608193759d73', 6, 1, 'delivery', 'pending', 'normal', 19.08, 1.91, 5.99, 0.00, 26.98, '', 'pending', NULL, 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 17:37:59', '2025-06-08 17:37:59'),
(12, 'ORD20250608200623b96', 6, 1, 'delivery', 'out_for_delivery', 'normal', 75.24, 7.52, 0.00, 0.00, 82.76, 'online', 'pending', '', 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, NULL, 4, '', NULL, NULL, '2025-06-08 18:06:23', '2025-06-08 18:11:07'),
(13, 'ORD202506082007224eb', 6, 1, 'delivery', 'ready_for_pickup', 'normal', 26.28, 2.63, 5.99, 0.00, 34.90, '', 'pending', '', 'Bibek Tamang', '0412345678', 'bibeks337@gmail.com', 'Kathmandu, Kathmadu, VIC 3045', '', 30, NULL, NULL, NULL, '', NULL, NULL, '2025-06-08 18:07:22', '2025-06-08 18:13:13');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item_type` enum('pizza','menu_item') NOT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `size` enum('small','medium','large') DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_type`, `pizza_id`, `menu_item_id`, `size`, `quantity`, `unit_price`, `total_price`, `special_instructions`) VALUES
(1, 1, 'pizza', 1, NULL, 'medium', 1, 24.90, 24.90, 'Extra spicy'),
(2, 1, 'menu_item', NULL, 1, NULL, 1, 8.90, 8.90, NULL),
(3, 2, 'pizza', 2, NULL, 'medium', 1, 21.90, 21.90, 'Extra basil'),
(4, 3, 'pizza', 2, NULL, 'medium', 1, 21.90, 21.90, ''),
(5, 3, 'pizza', 6, NULL, 'medium', 1, 25.90, 25.90, ''),
(6, 3, 'pizza', 1, NULL, 'medium', 1, 24.90, 24.90, ''),
(7, 3, 'pizza', 4, NULL, 'medium', 1, 27.90, 27.90, ''),
(8, 4, 'pizza', 4, NULL, 'medium', 1, 27.90, 27.90, ''),
(9, 5, 'pizza', 1, NULL, 'medium', 1, 24.90, 24.90, ''),
(10, 5, 'pizza', 4, NULL, 'medium', 1, 27.90, 27.90, ''),
(11, 6, 'pizza', 4, NULL, 'medium', 1, 27.90, 27.90, ''),
(12, 7, 'pizza', 2, NULL, 'medium', 1, 21.90, 21.90, ''),
(13, 7, 'pizza', 6, NULL, 'medium', 1, 25.90, 25.90, ''),
(14, 8, 'pizza', 8, NULL, 'medium', 1, 28.90, 28.90, ''),
(15, 8, 'pizza', NULL, NULL, 'medium', 1, 10.00, 10.00, ''),
(16, 8, 'pizza', 1, NULL, 'medium', 1, 24.90, 24.90, ''),
(17, 8, 'pizza', 2, NULL, 'medium', 1, 23.90, 23.90, ''),
(18, 8, 'pizza', NULL, NULL, 'medium', 1, 25.90, 25.90, ''),
(19, 8, 'pizza', NULL, NULL, 'medium', 1, 16.90, 16.90, ''),
(20, 8, 'pizza', NULL, NULL, 'medium', 6, 21.90, 131.40, ''),
(21, 9, 'pizza', 8, NULL, 'medium', 1, 27.48, 27.48, ''),
(22, 9, 'pizza', 6, NULL, 'medium', 1, 23.88, 23.88, ''),
(23, 9, 'pizza', 4, NULL, 'medium', 1, 26.28, 26.28, ''),
(24, 9, 'pizza', 3, NULL, 'medium', 1, 23.88, 23.88, ''),
(25, 9, 'pizza', 2, NULL, 'medium', 1, 19.08, 19.08, ''),
(26, 9, 'pizza', 1, NULL, 'medium', 1, 22.68, 22.68, ''),
(27, 10, 'pizza', 1, NULL, 'medium', 1, 22.68, 22.68, ''),
(28, 11, 'pizza', 2, NULL, 'medium', 1, 19.08, 19.08, ''),
(29, 12, 'pizza', 5, NULL, 'medium', 1, 21.48, 21.48, ''),
(30, 12, 'pizza', 8, NULL, 'medium', 1, 27.48, 27.48, ''),
(31, 12, 'pizza', 4, NULL, 'medium', 1, 26.28, 26.28, ''),
(32, 13, 'pizza', 4, NULL, 'medium', 1, 26.28, 26.28, '');

-- --------------------------------------------------------

--
-- Table structure for table `order_item_ingredients`
--

CREATE TABLE `order_item_ingredients` (
  `order_item_ingredient_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `quantity` decimal(5,2) DEFAULT 1.00,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item_ingredients`
--

INSERT INTO `order_item_ingredients` (`order_item_ingredient_id`, `order_item_id`, `ingredient_id`, `quantity`, `price`) VALUES
(1, 1, 13, 1.00, 2.50),
(2, 2, 13, 1.00, 2.50);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `history_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','prepared','out_for_delivery','ready_for_pickup','delivered','delivery_failure','received_by_customer','cancelled') NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`history_id`, `order_id`, `status`, `changed_by`, `notes`, `created_at`) VALUES
(1, 1, 'pending', NULL, 'Order received', '2025-06-06 06:15:00'),
(2, 1, 'confirmed', 2, 'Order confirmed by kitchen', '2025-06-06 06:20:00'),
(3, 1, 'preparing', 2, 'Pizza in preparation', '2025-06-06 06:25:00'),
(4, 1, 'out_for_delivery', 4, 'Assigned to delivery', '2025-06-06 06:45:00'),
(5, 1, 'delivered', 4, 'Delivered to customer', '2025-06-06 07:15:00'),
(6, 1, '', NULL, 'Customer confirmed receipt', '2025-06-06 07:20:00'),
(7, 2, 'pending', NULL, 'Order received', '2025-06-05 12:15:00'),
(8, 2, 'confirmed', 2, 'Order confirmed by kitchen', '2025-06-05 12:20:00'),
(9, 2, 'preparing', 2, 'Pizza in preparation', '2025-06-05 12:25:00'),
(10, 2, 'ready_for_pickup', 5, 'Ready for customer pickup', '2025-06-05 12:40:00'),
(11, 2, '', NULL, 'Customer picked up', '2025-06-05 12:55:00'),
(12, 3, 'pending', NULL, 'Order created', '2025-06-08 12:39:15'),
(13, 4, 'pending', NULL, 'Order created', '2025-06-08 12:56:45'),
(14, 5, 'pending', NULL, 'Order created', '2025-06-08 13:06:54'),
(15, 6, 'pending', NULL, 'Order created', '2025-06-08 13:07:52'),
(16, 7, 'pending', NULL, 'Order created', '2025-06-08 13:11:33'),
(17, 8, 'pending', NULL, 'Order created', '2025-06-08 13:13:59'),
(18, 3, 'prepared', 3, '', '2025-06-08 13:25:42'),
(19, 3, 'out_for_delivery', 4, '', '2025-06-08 13:27:05'),
(20, 6, 'received_by_customer', 5, '', '2025-06-08 13:32:47'),
(21, 9, 'pending', NULL, 'Order created', '2025-06-08 17:22:50'),
(22, 10, 'pending', NULL, 'Order created', '2025-06-08 17:33:05'),
(23, 11, 'pending', NULL, 'Order created', '2025-06-08 17:37:59'),
(24, 12, 'pending', NULL, 'Order created', '2025-06-08 18:06:23'),
(25, 13, 'pending', NULL, 'Order created', '2025-06-08 18:07:22'),
(26, 12, 'prepared', 3, '', '2025-06-08 18:10:34'),
(27, 12, 'out_for_delivery', 4, '', '2025-06-08 18:11:07'),
(28, 7, 'received_by_customer', 5, '', '2025-06-08 18:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `pizzas`
--

CREATE TABLE `pizzas` (
  `pizza_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `base_price_small` decimal(10,2) NOT NULL,
  `base_price_medium` decimal(10,2) NOT NULL,
  `base_price_large` decimal(10,2) NOT NULL,
  `cost_small` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_medium` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_large` decimal(10,2) NOT NULL DEFAULT 0.00,
  `prep_time_minutes` int(11) DEFAULT 15,
  `calories_small` int(11) DEFAULT NULL,
  `calories_medium` int(11) DEFAULT NULL,
  `calories_large` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_vegan` tinyint(1) DEFAULT 0,
  `is_gluten_free_available` tinyint(1) DEFAULT 0,
  `allergens` text DEFAULT NULL,
  `popularity_score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pizzas`
--

INSERT INTO `pizzas` (`pizza_id`, `name`, `description`, `category_id`, `image_url`, `base_price_small`, `base_price_medium`, `base_price_large`, `cost_small`, `cost_medium`, `cost_large`, `prep_time_minutes`, `calories_small`, `calories_medium`, `calories_large`, `is_available`, `is_featured`, `is_vegan`, `is_gluten_free_available`, `allergens`, `popularity_score`, `created_at`, `updated_at`) VALUES
(1, 'Peri Peri Chicken', 'Award-winning pizza with peri peri chicken, capsicum, red onion, and mozzarella', 1, '/assets/public/uploads/6845bb6816015_1749400424.jpg', 18.90, 22.68, 28.35, 11.34, 13.61, 17.01, 18, 0, 0, 0, 1, 1, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:33:44'),
(2, 'Margherita', 'Classic pizza with fresh basil, mozzarella, and tomato sauce', 2, '/assets/public/uploads/6845bb52ece6c_1749400402.jpg', 15.90, 19.08, 23.85, 9.54, 11.45, 14.31, 15, 0, 0, 0, 1, 1, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:33:22'),
(3, 'Vegan Supreme', 'Plant-based pepperoni, mushrooms, capsicum, olives, and vegan cheese', 3, '/assets/public/uploads/6845bb8e27337_1749400462.jpg', 19.90, 23.88, 29.85, 11.94, 14.33, 17.91, 20, 0, 0, 0, 1, 1, 0, 1, 'Gluten', 0, '2025-06-07 16:52:41', '2025-06-08 16:34:22'),
(4, 'Meat Lovers', 'Pepperoni, ham, bacon, Italian sausage, and mozzarella', 4, '/assets/public/uploads/6845bb5d499ef_1749400413.jpg', 21.90, 26.28, 32.85, 13.14, 15.77, 19.71, 22, 0, 0, 0, 1, 1, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:33:33'),
(5, 'Hawaiian', 'Ham, pineapple, and mozzarella on tomato base', 2, '/assets/public/uploads/6845bbab3b2eb_1749400491.jpg', 17.90, 21.48, 26.85, 10.74, 12.89, 16.11, 16, 0, 0, 0, 1, 0, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:34:51'),
(6, 'Supreme', 'Pepperoni, mushrooms, capsicum, olives, and mozzarella', 2, '/assets/public/uploads/6845bb835dcab_1749400451.jpeg', 19.90, 23.88, 29.85, 11.94, 14.33, 17.91, 20, 0, 0, 0, 1, 1, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:34:11'),
(7, 'BBQ Chicken', 'BBQ sauce, chicken, red onion, capsicum, and mozzarella', 2, '/assets/public/uploads/6845bb9d099f6_1749400477.jpg', 18.90, 22.68, 28.35, 11.34, 13.61, 17.01, 18, 0, 0, 0, 1, 0, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:34:37'),
(8, 'Prosciutto Rocket', 'Prosciutto, rocket, cherry tomatoes, parmesan, and mozzarella', 5, '/assets/public/uploads/6845bb74da42c_1749400436.jpg', 22.90, 27.48, 34.35, 13.74, 16.49, 20.61, 20, 0, 0, 0, 1, 1, 0, 1, 'Gluten, Dairy', 0, '2025-06-07 16:52:41', '2025-06-08 16:33:56');

-- --------------------------------------------------------

--
-- Table structure for table `pizza_ingredients`
--

CREATE TABLE `pizza_ingredients` (
  `pizza_ingredient_id` int(11) NOT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 1,
  `quantity` decimal(5,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pizza_ingredients`
--

INSERT INTO `pizza_ingredients` (`pizza_ingredient_id`, `pizza_id`, `ingredient_id`, `is_default`, `quantity`) VALUES
(303, 2, 12, 1, 1.00),
(304, 2, 1, 1, 1.00),
(305, 2, 6, 1, 1.00),
(306, 4, 21, 1, 1.00),
(307, 4, 20, 1, 1.00),
(308, 4, 22, 1, 1.00),
(309, 4, 12, 1, 1.00),
(310, 4, 18, 1, 1.00),
(311, 4, 1, 1, 1.00),
(312, 4, 6, 1, 1.00),
(313, 1, 27, 1, 1.00),
(314, 1, 19, 1, 1.00),
(315, 1, 12, 1, 1.00),
(316, 1, 28, 1, 1.00),
(317, 1, 1, 1, 1.00),
(318, 1, 6, 1, 1.00),
(319, 8, 32, 1, 1.00),
(320, 8, 30, 1, 1.00),
(321, 8, 12, 1, 1.00),
(322, 8, 15, 1, 1.00),
(323, 8, 23, 1, 1.00),
(324, 8, 1, 1, 1.00),
(325, 8, 6, 1, 1.00),
(326, 6, 27, 1, 1.00),
(327, 6, 12, 1, 1.00),
(328, 6, 26, 1, 1.00),
(329, 6, 29, 1, 1.00),
(330, 6, 18, 1, 1.00),
(331, 6, 1, 1, 1.00),
(332, 6, 6, 1, 1.00),
(333, 3, 27, 1, 1.00),
(334, 3, 4, 1, 1.00),
(335, 3, 26, 1, 1.00),
(336, 3, 29, 1, 1.00),
(337, 3, 6, 1, 1.00),
(338, 3, 14, 1, 1.00),
(345, 7, 7, 1, 1.00),
(346, 7, 27, 1, 1.00),
(347, 7, 19, 1, 1.00),
(348, 7, 12, 1, 1.00),
(349, 7, 28, 1, 1.00),
(350, 7, 1, 1, 1.00),
(351, 5, 20, 1, 1.00),
(352, 5, 12, 1, 1.00),
(353, 5, 31, 1, 1.00),
(354, 5, 1, 1, 1.00),
(355, 5, 6, 1, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `store_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `opening_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`opening_hours`)),
  `is_active` tinyint(1) DEFAULT 1,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`store_id`, `name`, `address`, `phone`, `email`, `opening_hours`, `is_active`, `manager_id`, `created_at`) VALUES
(1, 'Crust Pizza Annandale', '123 Parramatta Rd, Annandale NSW 2038', '(02) 9560 1234', 'annandale@crustpizza.com.au', '{\"monday\": \"11:00-22:00\", \"tuesday\": \"11:00-22:00\", \"wednesday\": \"11:00-22:00\", \"thursday\": \"11:00-22:00\", \"friday\": \"11:00-23:00\", \"saturday\": \"11:00-23:00\", \"sunday\": \"11:00-22:00\"}', 1, NULL, '2025-06-07 16:52:41'),
(2, 'Crust Pizza Richmond', '456 Swan St, Richmond VIC 3121', '(03) 9428 5678', 'richmond@crustpizza.com.au', '{\"monday\": \"11:00-22:00\", \"tuesday\": \"11:00-22:00\", \"wednesday\": \"11:00-22:00\", \"thursday\": \"11:00-22:00\", \"friday\": \"11:00-23:00\", \"saturday\": \"11:00-23:00\", \"sunday\": \"11:00-22:00\"}', 1, NULL, '2025-06-07 16:52:41'),
(3, 'Crust Pizza Bondi', '789 Campbell Parade, Bondi Beach NSW 2026', '(02) 9365 9999', 'bondi@crustpizza.com.au', '{\"monday\": \"11:00-22:00\", \"tuesday\": \"11:00-22:00\", \"wednesday\": \"11:00-22:00\", \"thursday\": \"11:00-22:00\", \"friday\": \"11:00-23:00\", \"saturday\": \"11:00-23:00\", \"sunday\": \"11:00-22:00\"}', 1, NULL, '2025-06-07 16:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_public`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'Crust Pizza', 'string', 'Website name', 1, NULL, '2025-06-07 16:52:41'),
(2, 'delivery_fee', '5.50', 'number', 'Standard delivery fee', 1, NULL, '2025-06-07 16:52:41'),
(3, 'free_delivery_threshold', '35.00', 'number', 'Minimum order for free delivery', 1, NULL, '2025-06-07 16:52:41'),
(4, 'tax_rate', '0.10', 'number', 'GST tax rate', 0, NULL, '2025-06-07 16:52:41'),
(5, 'max_delivery_distance', '15', 'number', 'Maximum delivery distance in km', 0, NULL, '2025-06-07 16:52:41'),
(6, 'order_prep_time', '20', 'number', 'Average order preparation time in minutes', 1, NULL, '2025-06-07 16:52:41'),
(7, 'loyalty_points_rate', '1', 'number', 'Points earned per dollar spent', 1, NULL, '2025-06-07 16:52:41'),
(8, 'points_redemption_value', '0.01', 'number', 'Dollar value per loyalty point', 1, NULL, '2025-06-07 16:52:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('customer','kitchen','delivery','counter','admin') NOT NULL DEFAULT 'customer',
  `store_id` int(11) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `address`, `date_of_birth`, `role`, `store_id`, `hire_date`, `salary`, `is_active`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 'customer1', 'customer@example.com', '$2a$12$2k9y8eMZ6NoTHBOURTPHTuTNGEHHKxkf6eDSzQY3i8Rs8oM00CrfS', 'John Customer', '0412345678', '123 Test Street', '1990-05-15', 'customer', NULL, NULL, NULL, 1, 1, '2025-06-07 16:52:41', '2025-06-08 13:13:59'),
(2, 'admin', 'admin@crustpizza.com.au', '$2a$12$djlpBXmwxJ3QjIiASJTs1OSy3h6rfcQ2ny.yRDF9sUBV9qtaLWzty', 'System Administrator', '0412345679', '456 Admin Rd, Sydney NSW 2000', NULL, 'admin', 1, '2023-01-01', 75000.00, 1, 1, '2025-06-07 16:52:41', '2025-06-07 18:38:14'),
(3, 'kitchen1', 'kitchen1@crustpizza.com.au', '$2a$12$IRH9LaGRwD/Pl9RfhXjiLO3NSwbz7SZjVCiZIPQ8oXfu9nZOYmXdO', 'Mario Rossi', '0412345680', '789 Kitchen St, Sydney NSW 2000', NULL, 'kitchen', 1, '2023-02-15', 55000.00, 1, 1, '2025-06-07 16:52:41', '2025-06-08 11:17:25'),
(4, 'delivery1', 'delivery1@crustpizza.com.au', '$2a$12$2k9y8eMZ6NoTHBOURTPHTuTNGEHHKxkf6eDSzQY3i8Rs8oM00CrfS', 'James Wilson', '0412345681', '101 Delivery Ave, Sydney NSW 2000', NULL, 'delivery', 1, '2023-03-01', 45000.00, 1, 1, '2025-06-07 16:52:41', '2025-06-08 11:13:35'),
(5, 'counter1', 'counter1@crustpizza.com.au', '$2a$12$2k9y8eMZ6NoTHBOURTPHTuTNGEHHKxkf6eDSzQY3i8Rs8oM00CrfS', 'Sarah Johnson', '0412345682', '202 Counter Rd, Sydney NSW 2000', NULL, 'counter', 1, '2023-02-20', 48000.00, 1, 1, '2025-06-07 16:52:41', '2025-06-08 05:28:22'),
(6, 'bibek', 'bibeks337@gmail.com', '$2a$12$2k9y8eMZ6NoTHBOURTPHTuTNGEHHKxkf6eDSzQY3i8Rs8oM00CrfS', 'Bibek Tamang', '0412345678', 'Kathmandu', NULL, 'customer', NULL, NULL, NULL, 1, 0, '2025-06-08 03:55:19', '2025-06-08 18:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address_type` enum('home','work','other') DEFAULT 'home',
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `suburb` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `country` varchar(50) DEFAULT 'Australia',
  `is_default` tinyint(1) DEFAULT 0,
  `delivery_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `user_id`, `address_type`, `address_line_1`, `address_line_2`, `suburb`, `state`, `postcode`, `country`, `is_default`, `delivery_instructions`, `created_at`) VALUES
(1, 1, 'home', '123 Test Street', 'Apartment 4B', 'Sydney', 'NSW', '2000', 'Australia', 1, 'Leave at front door', '2025-06-07 16:52:42'),
(2, 1, 'work', '456 Business Rd', 'Suite 101', 'Sydney', 'NSW', '2000', 'Australia', 0, 'Deliver to reception', '2025-06-07 16:52:42'),
(3, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 1, '', '2025-06-08 12:56:45'),
(4, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 0, '', '2025-06-08 13:06:54'),
(5, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 0, '', '2025-06-08 13:07:52'),
(6, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 0, '', '2025-06-08 13:11:33'),
(7, 1, '', '123 Test Street', '', 'Sydney', 'NSW', '2000', 'Australia', 0, '', '2025-06-08 13:13:59'),
(8, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 0, '', '2025-06-08 17:37:59'),
(9, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 0, '', '2025-06-08 18:06:23'),
(10, 6, '', 'Kathmandu', '', 'Kathmadu', 'VIC', '3045', 'Australia', 0, '', '2025-06-08 18:07:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_type` enum('pizza','menu_item') NOT NULL,
  `pizza_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `size` enum('small','medium','large') DEFAULT NULL,
  `custom_ingredients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_ingredients`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorites`
--

INSERT INTO `user_favorites` (`favorite_id`, `user_id`, `item_type`, `pizza_id`, `menu_item_id`, `size`, `custom_ingredients`, `created_at`) VALUES
(1, 1, 'pizza', 1, NULL, 'medium', '{\"ingredients\": [{\"id\": 13, \"quantity\": 1.00}]}', '2025-06-07 16:52:42'),
(2, 1, 'menu_item', NULL, 1, NULL, NULL, '2025-06-07 16:52:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pizza_id` (`pizza_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `pizza_id` (`pizza_id`),
  ADD KEY `menu_item_id` (`menu_item_id`),
  ADD KEY `idx_cart_items_user_id` (`user_id`),
  ADD KEY `idx_cart_items_session_id` (`session_id`);

--
-- Indexes for table `cart_item_ingredients`
--
ALTER TABLE `cart_item_ingredients`
  ADD PRIMARY KEY (`cart_item_ingredient_id`),
  ADD KEY `cart_item_id` (`cart_item_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `idx_ingredients_category` (`category`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`point_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_loyalty_points_user_id` (`user_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`menu_item_id`),
  ADD KEY `idx_menu_items_category_id` (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `idx_notifications_user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD UNIQUE KEY `order_number_2` (`order_number`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_store_id` (`store_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_order_number` (`order_number`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `pizza_id` (`pizza_id`),
  ADD KEY `menu_item_id` (`menu_item_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `order_item_ingredients`
--
ALTER TABLE `order_item_ingredients`
  ADD PRIMARY KEY (`order_item_ingredient_id`),
  ADD KEY `order_item_id` (`order_item_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `pizzas`
--
ALTER TABLE `pizzas`
  ADD PRIMARY KEY (`pizza_id`),
  ADD KEY `idx_pizzas_category_id` (`category_id`),
  ADD KEY `idx_pizzas_is_available` (`is_available`);

--
-- Indexes for table `pizza_ingredients`
--
ALTER TABLE `pizza_ingredients`
  ADD PRIMARY KEY (`pizza_ingredient_id`),
  ADD KEY `pizza_id` (`pizza_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`store_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_store_id` (`store_id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_user_addresses_user_id` (`user_id`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pizza_id` (`pizza_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_item_ingredients`
--
ALTER TABLE `cart_item_ingredients`
  MODIFY `cart_item_ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `point_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `menu_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_item_ingredients`
--
ALTER TABLE `order_item_ingredients`
  MODIFY `order_item_ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pizzas`
--
ALTER TABLE `pizzas`
  MODIFY `pizza_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pizza_ingredients`
--
ALTER TABLE `pizza_ingredients`
  MODIFY `pizza_ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=356;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`),
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`);

--
-- Constraints for table `cart_item_ingredients`
--
ALTER TABLE `cart_item_ingredients`
  ADD CONSTRAINT `cart_item_ingredients_ibfk_1` FOREIGN KEY (`cart_item_id`) REFERENCES `cart_items` (`cart_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_item_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`);

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`);

--
-- Constraints for table `order_item_ingredients`
--
ALTER TABLE `order_item_ingredients`
  ADD CONSTRAINT `order_item_ingredients_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `pizzas`
--
ALTER TABLE `pizzas`
  ADD CONSTRAINT `pizzas_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `pizza_ingredients`
--
ALTER TABLE `pizza_ingredients`
  ADD CONSTRAINT `pizza_ingredients_ibfk_1` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pizza_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`);

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`),
  ADD CONSTRAINT `user_favorites_ibfk_3` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
