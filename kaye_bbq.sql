-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 03:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kaye_bbq`
--

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `BARANGAY_ID` int(11) NOT NULL,
  `BARANGAY_NAME` varchar(50) NOT NULL,
  `DELIVERY_FEE` decimal(10,2) NOT NULL DEFAULT 0.00,
  `BARANGAY_STATUS` varchar(10) NOT NULL DEFAULT 'Active',
  `ADDED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay`
--

INSERT INTO `barangay` (`BARANGAY_ID`, `BARANGAY_NAME`, `DELIVERY_FEE`, `BARANGAY_STATUS`, `ADDED_AT`) VALUES
(1, 'Balulang', 0.00, 'Active', '2026-10-08 14:00:05'),
(2, 'carmen', 20.00, 'Active', '2026-10-15 15:50:52'),
(3, 'Macasandig', 30.00, 'Active', '2026-10-21 11:48:00'),
(4, 'Lapasan', 50.00, 'Active', '2026-10-23 09:53:17'),
(5, 'Kauswagan', 50.00, 'Active', '2026-10-23 09:54:13'),
(9, 'Barangay 23', 30.00, 'Active', '2026-10-23 10:01:07');

-- --------------------------------------------------------

--
-- Table structure for table `company_gcash`
--

CREATE TABLE `company_gcash` (
  `GCASH_ID` int(11) NOT NULL,
  `GCASH_NAME` varchar(100) NOT NULL,
  `GCASH_NUMBER` varchar(11) NOT NULL,
  `GCASH_QR` varchar(255) NOT NULL,
  `ADDED_AT` datetime NOT NULL DEFAULT current_timestamp(),
  `ARCHIVED_AT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_gcash`
--

INSERT INTO `company_gcash` (`GCASH_ID`, `GCASH_NAME`, `GCASH_NUMBER`, `GCASH_QR`, `ADDED_AT`, `ARCHIVED_AT`) VALUES
(1, 'kaye', '09123456789', '1791443417_02504bdb89f83c572152.jpg', '2026-10-08 15:10:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_address`
--

CREATE TABLE `delivery_address` (
  `DELIVERY_ADDRESS_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) NOT NULL,
  `BARANGAY_ID` int(11) NOT NULL,
  `ADDRESS_DETAIL` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_address`
--

INSERT INTO `delivery_address` (`DELIVERY_ADDRESS_ID`, `ORDER_ID`, `BARANGAY_ID`, `ADDRESS_DETAIL`) VALUES
(1, 4, 1, 'balongis, mangahan'),
(2, 9, 1, 'balongis, manggahan'),
(3, 10, 1, 'manggahan green na balay'),
(4, 13, 1, 'balongis,balulang'),
(5, 14, 2, 'gaisano, carmen'),
(6, 24, 2, 'lapasan, rons, st nino center unahan'),
(7, 25, 2, 'lapasan, rons, health center unahan'),
(8, 26, 1, 'balongis, balulang, adarek rent a car'),
(9, 23, 2, 'near gaisano ksy');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_fee`
--

CREATE TABLE `delivery_fee` (
  `FEE_HISTORY_ID` int(11) NOT NULL,
  `BARANGAY_ID` int(11) NOT NULL,
  `OLD_FEE` decimal(10,2) NOT NULL,
  `NEW_FEE` decimal(10,2) NOT NULL,
  `UPDATED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_fee`
--

INSERT INTO `delivery_fee` (`FEE_HISTORY_ID`, `BARANGAY_ID`, `OLD_FEE`, `NEW_FEE`, `UPDATED_AT`) VALUES
(1, 2, 30.00, 20.00, '2026-10-23 10:19:05');

-- --------------------------------------------------------

--
-- Table structure for table `menu_category`
--

CREATE TABLE `menu_category` (
  `CATEGORY_ID` int(11) NOT NULL,
  `CATEGORY_NAME` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_category`
--

INSERT INTO `menu_category` (`CATEGORY_ID`, `CATEGORY_NAME`) VALUES
(1, 'Chicken BBQ'),
(2, 'Grilled Mains'),
(3, 'Processed Meat');

-- --------------------------------------------------------

--
-- Table structure for table `menu_item`
--

CREATE TABLE `menu_item` (
  `MENU_ID` int(11) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `IMAGE` varchar(100) NOT NULL,
  `DESCRIPTION` varchar(255) NOT NULL DEFAULT '',
  `PRICE` decimal(10,2) NOT NULL,
  `STOCK` int(11) NOT NULL DEFAULT 0,
  `STATUS` varchar(20) NOT NULL DEFAULT 'Available',
  `DATE_ADDED` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_item`
--

INSERT INTO `menu_item` (`MENU_ID`, `CATEGORY_ID`, `NAME`, `IMAGE`, `DESCRIPTION`, `PRICE`, `STOCK`, `STATUS`, `DATE_ADDED`) VALUES
(1, 1, 'isaw', '1791297395_96c3a5567845d7b4b08b.jpg', 'Spicy Isaw', 100.00, 12, 'Available', '2026-10-06 22:36:35'),
(2, 2, 'Tuna Panga', '1791300759_36777d9b8da83e7bd5a9.jpg', 'Grilled Tuna Jaw', 250.00, 0, 'Available', '2026-10-06 23:32:39'),
(3, 1, 'Spicy Isaw', '1792554231_2d5236cf5fc1b1f34df7.jpg', 'skewered entistine', 11.00, 0, 'Available', '2026-10-21 11:43:51'),
(4, 3, 'hotdog', '1792738799_46987956052ae894b82d.jpg', 'Grilled Sausage', 20.00, 20, 'Available', '2026-10-23 14:59:59');

-- --------------------------------------------------------

--
-- Table structure for table `menu_price`
--

CREATE TABLE `menu_price` (
  `PRICE_HISTORY_ID` int(11) NOT NULL,
  `MENU_ID` int(11) NOT NULL,
  `OLD_PRICE` decimal(10,2) NOT NULL,
  `UPDATED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_price`
--

INSERT INTO `menu_price` (`PRICE_HISTORY_ID`, `MENU_ID`, `OLD_PRICE`, `UPDATED_AT`) VALUES
(1, 1, 10.00, '2026-10-06 23:18:02'),
(2, 1, 11.00, '2026-10-21 18:57:30'),
(3, 1, 1.00, '2026-10-21 19:04:33'),
(4, 1, 10.00, '2026-10-21 19:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ORDER_ID` int(11) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `ORDER_TYPE` varchar(20) NOT NULL,
  `ORDER_DATE` datetime NOT NULL DEFAULT current_timestamp(),
  `TOTAL_AMOUNT` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ORDER_STATUS` varchar(20) NOT NULL DEFAULT 'Cart',
  `NOTE` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ORDER_ID`, `USER_ID`, `ORDER_TYPE`, `ORDER_DATE`, `TOTAL_AMOUNT`, `ORDER_STATUS`, `NOTE`) VALUES
(1, 2, 'pickup', '2026-10-06 23:18:33', 11.00, 'Delivered', 'None'),
(2, 2, 'pickup', '2026-10-08 13:45:44', 11.00, 'Delivered', 'None'),
(3, 2, 'pickup', '2026-10-08 13:59:41', 253.00, 'Preparing', 'None'),
(4, 2, 'delivery', '2026-10-08 14:08:38', 250.00, 'Preparing', 'None'),
(5, 2, 'pickup', '2026-10-08 14:09:48', 250.00, 'Delivered', 'None'),
(6, 2, 'pickup', '2026-10-08 15:01:35', 599.00, 'Preparing', 'None'),
(7, 2, 'pickup', '2026-10-08 16:08:53', 121.00, 'Preparing', 'None'),
(8, 2, 'pickup', '2026-10-08 16:10:25', 121.00, 'Preparing', 'None'),
(9, 2, 'delivery', '2026-10-08 16:15:43', 231.00, 'Preparing', 'None'),
(10, 2, 'delivery', '2026-10-08 18:59:45', 250.00, 'Delivered', 'None'),
(11, 2, 'pickup', '2026-10-15 15:28:40', 44.00, 'Preparing', 'None'),
(12, 2, 'pickup', '2026-10-15 15:29:13', 44.00, 'Preparing', 'None'),
(13, 2, 'delivery', '2026-10-15 15:49:47', 11.00, 'Preparing', 'None'),
(14, 2, 'delivery', '2026-10-15 15:51:18', 41.00, 'Preparing', 'None'),
(15, 2, 'pickup', '2026-10-16 14:57:09', 33.00, 'Delivered', 'None'),
(16, 2, 'pickup', '2026-10-20 18:43:52', 77.00, 'Delivered', 'None'),
(17, 2, 'pickup', '2026-10-20 22:49:32', 250.00, 'Delivering', 'None'),
(18, 2, 'pickup', '2026-10-20 22:51:41', 250.00, 'Delivered', 'None'),
(19, 2, 'pickup', '2026-10-20 23:05:47', 110.00, 'Cancelled', 'None'),
(20, 2, 'pickup', '2026-10-21 01:02:29', 110.00, 'Delivered', 'None'),
(21, 2, 'pickup', '2026-10-21 11:58:52', 110.00, 'Refund', 'None'),
(22, 2, 'pickup', '2026-10-21 14:02:23', 22.00, 'Refund', 'None'),
(23, 2, 'delivery', '2026-10-23 15:24:02', 142.00, 'Pending', 'None'),
(24, 3, 'delivery', '2026-10-22 19:25:22', 524.00, 'Delivered', 'all spicy'),
(25, 4, 'delivery', '2026-10-22 19:30:10', 474.00, 'Delivered', 'spicy'),
(26, 5, 'delivery', '2026-10-22 19:38:26', 110.00, 'Delivered', 'None');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `ORDER_ITEM_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) NOT NULL,
  `MENU_ID` int(11) NOT NULL,
  `QUANTITY` int(11) NOT NULL,
  `UNIT_PRICE` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`ORDER_ITEM_ID`, `ORDER_ID`, `MENU_ID`, `QUANTITY`, `UNIT_PRICE`) VALUES
(1, 1, 1, 1, 11.00),
(2, 2, 1, 1, 11.00),
(3, 3, 1, 23, 11.00),
(4, 4, 2, 1, 250.00),
(6, 5, 2, 1, 250.00),
(7, 6, 1, 9, 11.00),
(8, 6, 2, 2, 250.00),
(9, 7, 1, 11, 11.00),
(10, 8, 1, 11, 11.00),
(11, 9, 1, 21, 11.00),
(12, 10, 2, 1, 250.00),
(13, 11, 1, 4, 11.00),
(14, 12, 1, 4, 11.00),
(15, 13, 1, 1, 11.00),
(16, 14, 1, 1, 11.00),
(17, 15, 1, 3, 11.00),
(18, 16, 1, 7, 11.00),
(19, 17, 2, 1, 250.00),
(20, 18, 2, 1, 250.00),
(21, 19, 1, 10, 11.00),
(22, 20, 1, 10, 11.00),
(23, 21, 3, 10, 11.00),
(24, 22, 3, 2, 11.00),
(26, 24, 1, 2, 100.00),
(27, 24, 3, 4, 11.00),
(28, 24, 2, 1, 250.00),
(29, 25, 1, 4, 100.00),
(30, 25, 3, 4, 11.00),
(32, 26, 3, 10, 11.00),
(33, 23, 3, 2, 11.00),
(34, 23, 1, 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAYMENT_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) NOT NULL,
  `PAYMENT_METHOD` varchar(20) NOT NULL,
  `PAYMENT_DATE` datetime NOT NULL DEFAULT current_timestamp(),
  `REFERENCE_NUMBER` varchar(50) DEFAULT NULL,
  `PROOF_OF_PAYMENT` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PAYMENT_ID`, `ORDER_ID`, `PAYMENT_METHOD`, `PAYMENT_DATE`, `REFERENCE_NUMBER`, `PROOF_OF_PAYMENT`) VALUES
(1, 1, 'Cash', '2026-10-06 23:18:33', 'None', 'None'),
(2, 2, 'Cash', '2026-10-08 13:45:44', 'None', 'None'),
(3, 3, 'Cash', '2026-10-08 13:59:41', 'None', 'None'),
(4, 4, 'Cash', '2026-10-08 14:08:38', 'None', 'None'),
(5, 5, 'GCash', '2026-10-08 14:09:48', '3213123', '1791439788_4410511a6ddbb9326789.jpg'),
(6, 6, 'Cash', '2026-10-08 15:01:35', 'None', 'None'),
(15, 5, 'GCash', '2026-10-08 15:42:38', '31231qewqe', '1791445358_3b55d9d3719016391773.jpg'),
(16, 7, 'Cash', '2026-10-08 16:08:53', 'None', 'None'),
(17, 8, 'Cash', '2026-10-08 16:10:25', 'None', 'None'),
(18, 9, 'GCash', '2026-10-08 16:15:43', '123123', '1791447343_22ffe5ac84149e29d95e.jpg'),
(19, 9, 'GCash', '2026-10-08 16:32:23', '312312', '1791448343_61c99e1407581a4c6405.jpg'),
(20, 10, 'GCash', '2026-10-08 18:59:45', 'ddasdq23', '1791457185_84e238ae742546407c05.jpg'),
(21, 10, 'GCash', '2026-10-08 19:00:12', '3123123', '1791457212_0db7a4cc230da40d2c3a.jpg'),
(22, 11, 'Cash', '2026-10-15 15:28:40', 'None', 'None'),
(23, 12, 'GCash', '2026-10-15 15:29:13', 'dsadsa', '1792049353_561c826ab52d161d5364.jpg'),
(24, 13, 'Cash', '2026-10-15 15:49:47', 'None', 'None'),
(25, 14, 'Cash', '2026-10-15 15:51:18', 'None', 'None'),
(26, 15, 'Cash', '2026-10-16 14:57:09', 'None', 'None'),
(27, 16, 'Cash', '2026-10-20 18:43:52', 'None', 'None'),
(28, 17, 'Cash', '2026-10-20 22:49:32', 'None', 'None'),
(29, 18, 'Cash', '2026-10-20 22:51:41', 'None', 'None'),
(30, 19, 'GCash', '2026-10-20 23:05:47', 'rqwrweqr321321', '1792508747_aae4545dba90fd12cdfb.png'),
(31, 20, 'GCash', '2026-10-21 01:02:29', 'rqwrweqr321321', '1792515749_a798a401c73cc5a2ab7d.webp'),
(32, 20, 'GCash', '2026-10-21 01:02:58', '2312312', '1792515778_f719299ffc90cec2dda3.webp'),
(33, 21, 'GCash', '2026-10-21 11:58:52', '2132131', '1792555132_a3a0ff72f315cf172c42.jpg'),
(34, 21, 'Refund', '2026-10-21 13:50:15', 'dsa1321', '1792561815_9a9ef3409ed274dd4287.jpg'),
(35, 22, 'GCash', '2026-10-21 14:02:23', 'wqe', '1792562543_4df9441b32d7c82fe1b6.jpg'),
(36, 22, 'Refund', '2026-10-21 14:02:47', 'dasdsa', '1792562566_1fa9e5e43daf6d068dcd.jpg'),
(37, 24, 'Cash', '2026-10-22 19:25:22', 'None', 'None'),
(38, 25, 'Cash', '2026-10-22 19:30:10', 'None', 'None'),
(39, 26, 'Cash', '2026-10-22 19:38:26', 'None', 'None'),
(40, 23, 'GCash', '2026-10-23 15:24:02', '123123123', '1792740242_c206cd992232e0fef224.png');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `STOCK_HISTORY_ID` int(11) NOT NULL,
  `MENU_ID` int(11) NOT NULL,
  `ADDED_STOCK` int(11) NOT NULL,
  `UPDATED_AT` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`STOCK_HISTORY_ID`, `MENU_ID`, `ADDED_STOCK`, `UPDATED_AT`) VALUES
(1, 1, 10, '2026-10-15 17:14:42'),
(2, 1, 29, '2026-10-20 23:05:10'),
(3, 3, 20, '2026-10-21 11:43:51'),
(4, 4, 20, '2026-10-23 15:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USER_ID` int(11) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `FIRST_NAME` varchar(50) NOT NULL,
  `LAST_NAME` varchar(50) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `PHONE_NO` varchar(11) NOT NULL,
  `ROLE` varchar(20) NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `PASSWORD`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PHONE_NO`, `ROLE`) VALUES
(1, '$2y$12$/QkwHUAIFadLtOKR7.SpUu8b455Um9AXzc/mv1dE4bLE0JWbMznTW', 'Kaye', 'Baang', 'kaye@gmail.com', '09123456789', 'admin'),
(2, '$2y$12$2rqpESizf7tpkDj9LREkAeTloYEA8kp7ix.6b6pkGKuUp8U1Vk6lS', 'king', 'gwapo', 'gwapo@gmail.com', '09123456111', 'customer'),
(3, '$2y$12$cqdQ2CuXqEHt6a4vk6bNBO2FE1DdYRMlRp4su357hWcU4AGB9q4Me', 'kriszel', 'pedroso', 'kriszellynn@gmail.com', '09971034084', 'customer'),
(4, '$2y$12$ynkiCQui/QozRUrQHqNFKu0cMMzUxDjScjtF9MkvcMkxdV3gOqCxq', 'riel', 'oblenda', 'riel@gmail.com', '09554324730', 'customer'),
(5, '$2y$12$53RhiZdtlO5.fB8g9X0XvuZDekbtKOzbDmFXREgjw9F2Uj4miD/ZW', 'El', 'DuDE', 'bondox262@gmail.com', '09245668082', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`BARANGAY_ID`),
  ADD UNIQUE KEY `uq_barangay_name` (`BARANGAY_NAME`);

--
-- Indexes for table `company_gcash`
--
ALTER TABLE `company_gcash`
  ADD PRIMARY KEY (`GCASH_ID`);

--
-- Indexes for table `delivery_address`
--
ALTER TABLE `delivery_address`
  ADD PRIMARY KEY (`DELIVERY_ADDRESS_ID`),
  ADD UNIQUE KEY `uq_delivery_address_order` (`ORDER_ID`),
  ADD KEY `idx_delivery_address_barangay` (`BARANGAY_ID`);

--
-- Indexes for table `delivery_fee`
--
ALTER TABLE `delivery_fee`
  ADD PRIMARY KEY (`FEE_HISTORY_ID`),
  ADD KEY `idx_fee_history_barangay` (`BARANGAY_ID`);

--
-- Indexes for table `menu_category`
--
ALTER TABLE `menu_category`
  ADD PRIMARY KEY (`CATEGORY_ID`),
  ADD UNIQUE KEY `uq_category_name` (`CATEGORY_NAME`);

--
-- Indexes for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD PRIMARY KEY (`MENU_ID`),
  ADD KEY `idx_menu_category` (`CATEGORY_ID`);

--
-- Indexes for table `menu_price`
--
ALTER TABLE `menu_price`
  ADD PRIMARY KEY (`PRICE_HISTORY_ID`),
  ADD KEY `idx_price_history_menu` (`MENU_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ORDER_ID`),
  ADD KEY `idx_orders_user` (`USER_ID`),
  ADD KEY `idx_orders_status` (`ORDER_STATUS`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`ORDER_ITEM_ID`),
  ADD UNIQUE KEY `uq_order_item_order_menu` (`ORDER_ID`,`MENU_ID`),
  ADD KEY `idx_order_item_menu` (`MENU_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PAYMENT_ID`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`STOCK_HISTORY_ID`),
  ADD KEY `idx_stock_history_menu` (`MENU_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `uq_user_email` (`EMAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `BARANGAY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `company_gcash`
--
ALTER TABLE `company_gcash`
  MODIFY `GCASH_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_address`
--
ALTER TABLE `delivery_address`
  MODIFY `DELIVERY_ADDRESS_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `delivery_fee`
--
ALTER TABLE `delivery_fee`
  MODIFY `FEE_HISTORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_category`
--
ALTER TABLE `menu_category`
  MODIFY `CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_item`
--
ALTER TABLE `menu_item`
  MODIFY `MENU_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_price`
--
ALTER TABLE `menu_price`
  MODIFY `PRICE_HISTORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ORDER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `ORDER_ITEM_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `STOCK_HISTORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery_address`
--
ALTER TABLE `delivery_address`
  ADD CONSTRAINT `fk_delivery_address_barangay` FOREIGN KEY (`BARANGAY_ID`) REFERENCES `barangay` (`BARANGAY_ID`),
  ADD CONSTRAINT `fk_delivery_address_order` FOREIGN KEY (`ORDER_ID`) REFERENCES `orders` (`ORDER_ID`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_fee`
--
ALTER TABLE `delivery_fee`
  ADD CONSTRAINT `fk_fee_history_barangay` FOREIGN KEY (`BARANGAY_ID`) REFERENCES `barangay` (`BARANGAY_ID`);

--
-- Constraints for table `menu_item`
--
ALTER TABLE `menu_item`
  ADD CONSTRAINT `fk_menu_category` FOREIGN KEY (`CATEGORY_ID`) REFERENCES `menu_category` (`CATEGORY_ID`);

--
-- Constraints for table `menu_price`
--
ALTER TABLE `menu_price`
  ADD CONSTRAINT `fk_price_history_menu` FOREIGN KEY (`MENU_ID`) REFERENCES `menu_item` (`MENU_ID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `fk_order_item_menu` FOREIGN KEY (`MENU_ID`) REFERENCES `menu_item` (`MENU_ID`),
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`ORDER_ID`) REFERENCES `orders` (`ORDER_ID`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`ORDER_ID`) REFERENCES `orders` (`ORDER_ID`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_history_menu` FOREIGN KEY (`MENU_ID`) REFERENCES `menu_item` (`MENU_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
