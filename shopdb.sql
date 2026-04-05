-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2026 at 07:46 AM
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
-- Database: `shopdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
  (1, 'Vegetables'),
  (2, 'Fruits'),
  (3, 'Dairy'),
  (4, 'Bakery'),
  (5, 'Daily Needs');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Placed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(1, 3, 125.00, 'Placed', '2026-03-24 14:36:44'),
(2, 10, 345.00, 'Placed', '2026-03-24 15:18:31');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `product_price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `created_at`, `category_id`) VALUES
(1, 'Apple', 120, 'Fresh and juicy red apples rich in fiber and vitamins', 'https://picsum.photos/200?fruit=1', '2026-04-04 04:04:20', NULL),
(2, 'Banana', 60, 'Sweet bananas full of potassium and energy', 'https://picsum.photos/200?fruit=2', '2026-04-04 04:04:20', NULL),
(3, 'Mango', 150, 'Delicious ripe mangoes with natural sweetness', 'https://picsum.photos/200?fruit=3', '2026-04-04 04:04:20', NULL),
(4, 'Orange', 80, 'Citrus oranges loaded with vitamin C', 'https://picsum.photos/200?fruit=4', '2026-04-04 04:04:20', NULL),
(5, 'Pineapple', 90, 'Tropical pineapple with tangy and sweet taste', 'https://picsum.photos/200?fruit=5', '2026-04-04 04:04:20', NULL),
(6, 'Strawberry', 200, 'Fresh strawberries rich in antioxidants', 'https://picsum.photos/200?fruit=6', '2026-04-04 04:04:20', NULL),
(7, 'Watermelon', 70, 'Refreshing watermelon perfect for summer hydration', 'https://picsum.photos/200?fruit=7', '2026-04-04 04:04:20', NULL),
(8, 'Papaya', 65, 'Healthy papaya good for digestion', 'https://picsum.photos/200?fruit=8', '2026-04-04 04:04:20', NULL),
(9, 'Grapes', 110, 'Sweet seedless grapes rich in vitamins', 'https://picsum.photos/200?fruit=9', '2026-04-04 04:04:20', NULL),
(10, 'Guava', 50, 'Fresh guava with high vitamin C content', 'https://picsum.photos/200?fruit=10', '2026-04-04 04:04:20', NULL),
(11, 'Kiwi', 180, 'Exotic kiwi fruit packed with nutrients', 'https://picsum.photos/200?fruit=11', '2026-04-04 04:04:20', NULL),
(12, 'Pomegranate', 140, 'Juicy pomegranate rich in antioxidants', 'https://picsum.photos/200?fruit=12', '2026-04-04 04:04:20', NULL),
(13, 'Cherry', 220, 'Sweet cherries with a rich flavor', 'https://picsum.photos/200?fruit=13', '2026-04-04 04:04:20', NULL),
(14, 'Peach', 130, 'Soft and juicy peaches with sweet taste', 'https://picsum.photos/200?fruit=14', '2026-04-04 04:04:20', NULL),
(15, 'Plum', 100, 'Fresh plums with tangy flavor', 'https://picsum.photos/200?fruit=15', '2026-04-04 04:04:20', NULL),
(16, 'Litchi', 160, 'Sweet litchi with refreshing taste', 'https://picsum.photos/200?fruit=16', '2026-04-04 04:04:20', NULL),
(17, 'Dragon Fruit', 250, 'Exotic dragon fruit with vibrant color', 'https://picsum.photos/200?fruit=17', '2026-04-04 04:04:20', NULL),
(18, 'Avocado', 300, 'Healthy avocado rich in good fats', 'https://picsum.photos/200?fruit=18', '2026-04-04 04:04:20', NULL),
(19, 'Blueberry', 280, 'Nutritious blueberries rich in antioxidants', 'https://picsum.photos/200?fruit=19', '2026-04-04 04:04:20', NULL),
(20, 'Blackberry', 260, 'Juicy blackberries with sweet-tart flavor', 'https://picsum.photos/200?fruit=20', '2026-04-04 04:04:20', NULL),
(21, 'Raspberry', 240, 'Fresh raspberries full of nutrients', 'https://picsum.photos/200?fruit=21', '2026-04-04 04:04:20', NULL),
(22, 'Coconut', 90, 'Fresh coconut with natural water and pulp', 'https://picsum.photos/200?fruit=22', '2026-04-04 04:04:20', NULL),
(23, 'Fig', 170, 'Sweet figs with soft texture', 'https://picsum.photos/200?fruit=23', '2026-04-04 04:04:20', NULL),
(24, 'Apricot', 150, 'Delicious apricots rich in vitamins', 'https://picsum.photos/200?fruit=24', '2026-04-04 04:04:20', NULL),
(25, 'Custard Apple', 120, 'Creamy custard apple with sweet pulp', 'https://picsum.photos/200?fruit=25', '2026-04-04 04:04:20', NULL),
(26, 'Mulberry', 200, 'Fresh mulberries rich in antioxidants', 'https://picsum.photos/200?fruit=26', '2026-04-04 04:04:20', NULL),
(27, 'Star Fruit', 140, 'Unique star-shaped fruit with tangy taste', 'https://picsum.photos/200?fruit=27', '2026-04-04 04:04:20', NULL),
(28, 'Passion Fruit', 220, 'Tropical passion fruit with strong aroma', 'https://picsum.photos/200?fruit=28', '2026-04-04 04:04:20', NULL),
(29, 'Pear', 110, 'Sweet pears with soft texture', 'https://picsum.photos/200?fruit=29', '2026-04-04 04:04:20', NULL),
(30, 'Sapota', 80, 'Sweet sapota rich in natural sugars', 'https://picsum.photos/200?fruit=30', '2026-04-04 04:04:20', NULL),
(31, 'Jamun', 70, 'Healthy jamun good for digestion', 'https://picsum.photos/200?fruit=31', '2026-04-04 04:04:20', NULL),
(32, 'Gooseberry', 60, 'Amla rich in vitamin C and immunity booster', 'https://picsum.photos/200?fruit=32', '2026-04-04 04:04:20', NULL),
(33, 'Tamarind', 50, 'Tangy tamarind used in cooking', 'https://picsum.photos/200?fruit=33', '2026-04-04 04:04:20', NULL),
(34, 'Jackfruit', 90, 'Large tropical fruit with sweet taste', 'https://picsum.photos/200?fruit=34', '2026-04-04 04:04:20', NULL),
(35, 'Durian', 300, 'Exotic durian with strong aroma', 'https://picsum.photos/200?fruit=35', '2026-04-04 04:04:20', NULL),
(36, 'Longan', 210, 'Sweet longan similar to litchi', 'https://picsum.photos/200?fruit=36', '2026-04-04 04:04:20', NULL),
(37, 'Rambutan', 230, 'Hairy tropical fruit with sweet pulp', 'https://picsum.photos/200?fruit=37', '2026-04-04 04:04:20', NULL),
(38, 'Mangosteen', 260, 'Premium mangosteen with rich flavor', 'https://picsum.photos/200?fruit=38', '2026-04-04 04:04:20', NULL),
(39, 'Cranberry', 240, 'Healthy cranberries for juice and snacks', 'https://picsum.photos/200?fruit=39', '2026-04-04 04:04:20', NULL),
(40, 'Date', 180, 'Sweet dates rich in energy', 'https://picsum.photos/200?fruit=40', '2026-04-04 04:04:20', NULL),
(41, 'Olive', 200, 'Olives used for oil and healthy fats', 'https://picsum.photos/200?fruit=41', '2026-04-04 04:04:20', NULL),
(42, 'Lemon', 40, 'Sour lemon rich in vitamin C', 'https://picsum.photos/200?fruit=42', '2026-04-04 04:04:20', NULL),
(43, 'Sweet Lime', 60, 'Mosambi juice fruit with mild sweetness', 'https://picsum.photos/200?fruit=43', '2026-04-04 04:04:20', NULL),
(44, 'Kokum', 90, 'Tangy kokum used in drinks and curry', 'https://picsum.photos/200?fruit=44', '2026-04-04 04:04:20', NULL),
(45, 'Wood Apple', 100, 'Traditional fruit with medicinal benefits', 'https://picsum.photos/200?fruit=45', '2026-04-04 04:04:20', NULL),
(46, 'Bael', 110, 'Bael fruit good for digestion', 'https://picsum.photos/200?fruit=46', '2026-04-04 04:04:20', NULL),
(47, 'Palm Fruit', 70, 'Refreshing palm fruit for summer', 'https://picsum.photos/200?fruit=47', '2026-04-04 04:04:20', NULL),
(48, 'Sugarcane', 50, 'Sweet sugarcane for juice', 'https://picsum.photos/200?fruit=48', '2026-04-04 04:04:20', NULL),
(49, 'Red Banana', 90, 'Nutritious red banana variety', 'https://picsum.photos/200?fruit=49', '2026-04-04 04:04:20', NULL),
(50, 'Green Apple', 130, 'Crisp green apples with tangy taste', 'https://picsum.photos/200?fruit=50', '2026-04-04 04:04:20', NULL),
(51, 'Mulberry', 200, 'Fresh mulberries rich in antioxidants', 'https://picsum.photos/200?fruit=26', '2026-04-04 04:09:08', NULL),
(52, 'Star Fruit', 140, 'Unique star-shaped fruit with tangy taste', 'https://picsum.photos/200?fruit=27', '2026-04-04 04:09:08', NULL),
(53, 'Passion Fruit', 220, 'Tropical passion fruit with strong aroma', 'https://picsum.photos/200?fruit=28', '2026-04-04 04:09:08', NULL),
(54, 'Pear', 110, 'Sweet pears with soft texture', 'https://picsum.photos/200?fruit=29', '2026-04-04 04:09:08', NULL),
(55, 'Sapota', 80, 'Sweet sapota rich in natural sugars', 'https://picsum.photos/200?fruit=30', '2026-04-04 04:09:08', NULL),
(56, 'Jamun', 70, 'Healthy jamun good for digestion', 'https://picsum.photos/200?fruit=31', '2026-04-04 04:09:08', NULL),
(57, 'Gooseberry', 60, 'Amla rich in vitamin C and immunity booster', 'https://picsum.photos/200?fruit=32', '2026-04-04 04:09:08', NULL),
(58, 'Tamarind', 50, 'Tangy tamarind used in cooking', 'https://picsum.photos/200?fruit=33', '2026-04-04 04:09:08', NULL),
(59, 'Jackfruit', 90, 'Large tropical fruit with sweet taste', 'https://picsum.photos/200?fruit=34', '2026-04-04 04:09:08', NULL),
(60, 'Durian', 300, 'Exotic durian with strong aroma', 'https://picsum.photos/200?fruit=35', '2026-04-04 04:09:08', NULL),
(61, 'Longan', 210, 'Sweet longan similar to litchi', 'https://picsum.photos/200?fruit=36', '2026-04-04 04:09:08', NULL),
(62, 'Rambutan', 230, 'Hairy tropical fruit with sweet pulp', 'https://picsum.photos/200?fruit=37', '2026-04-04 04:09:08', NULL),
(63, 'Mangosteen', 260, 'Premium mangosteen with rich flavor', 'https://picsum.photos/200?fruit=38', '2026-04-04 04:09:08', NULL),
(64, 'Cranberry', 240, 'Healthy cranberries for juice and snacks', 'https://picsum.photos/200?fruit=39', '2026-04-04 04:09:08', NULL),
(65, 'Date', 180, 'Sweet dates rich in energy', 'https://picsum.photos/200?fruit=40', '2026-04-04 04:09:08', NULL),
(66, 'Olive', 200, 'Olives used for oil and healthy fats', 'https://picsum.photos/200?fruit=41', '2026-04-04 04:09:08', NULL),
(67, 'Lemon', 40, 'Sour lemon rich in vitamin C', 'https://picsum.photos/200?fruit=42', '2026-04-04 04:09:08', NULL),
(68, 'Sweet Lime', 60, 'Mosambi juice fruit with mild sweetness', 'https://picsum.photos/200?fruit=43', '2026-04-04 04:09:08', NULL),
(69, 'Kokum', 90, 'Tangy kokum used in drinks and curry', 'https://picsum.photos/200?fruit=44', '2026-04-04 04:09:08', NULL),
(70, 'Wood Apple', 100, 'Traditional fruit with medicinal benefits', 'https://picsum.photos/200?fruit=45', '2026-04-04 04:09:08', NULL),
(71, 'Bael', 110, 'Bael fruit good for digestion', 'https://picsum.photos/200?fruit=46', '2026-04-04 04:09:08', NULL),
(72, 'Palm Fruit', 70, 'Refreshing palm fruit for summer', 'https://picsum.photos/200?fruit=47', '2026-04-04 04:09:08', NULL),
(73, 'Sugarcane', 50, 'Sweet sugarcane for juice', 'https://picsum.photos/200?fruit=48', '2026-04-04 04:09:08', NULL),
(74, 'Red Banana', 90, 'Nutritious red banana variety', 'https://picsum.photos/200?fruit=49', '2026-04-04 04:09:08', NULL),
(75, 'Green Apple', 130, 'Crisp green apples with tangy taste', 'https://picsum.photos/200?fruit=50', '2026-04-04 04:09:08', NULL),
(76, 'Apple', 120, 'Fresh apple', 'apple.jpg', '2026-04-04 05:27:52', 2);

UPDATE `products` SET `category_id` = 2 WHERE `category_id` IS NULL;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(3, 'Raushan kumar', 'raushan@gmail.com', '$2y$10$/39gslokhzmsFM.PYciobehva7x5XHHZbc2R94Xm8VpHZP3D2ds6i'),
(4, 'pinkal rathod', '24035@gamil.com', '$2y$10$I47pCNdpSHBBHbBZ7yO/0ePC4YfOrKDzA4AjNRKzjZui6F.QHKFOG'),
(8, 'Admin', 'admin@gmail.com', '$2y$10$oVJsyfA36.lQGqblnu.LQOJcA.h4wVaMctVTHgNkrx8jSRJI2QnFO'),
(10, 'Rohit Kumar', 'rohit@gmail.com', '$2y$10$.F9ep67WHlRkyD2mV/Q7je.8Io9awPa2TzYPhR2Al/hZEYL1q0EfG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
