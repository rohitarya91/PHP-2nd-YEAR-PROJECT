-- Harvest Fresh quick-commerce starter database
-- Generated on 2026-04-06

CREATE DATABASE IF NOT EXISTS `shopdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `shopdb`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `phone_number` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `mrp` int(11) NOT NULL DEFAULT 0,
  `unit_label` varchar(50) NOT NULL DEFAULT '1 pack',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `delivery_minutes` int(11) NOT NULL DEFAULT 20,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `badge_text` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `products_category_idx` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subtotal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `handling_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Confirmed',
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
  `delivery_eta_label` varchar(50) DEFAULT NULL,
  `shipping_name` varchar(100) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `orders_user_idx` (`user_id`),
  CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(120) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `unit_label` varchar(50) DEFAULT NULL,
  `product_price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_idx` (`order_id`),
  CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `name`, `email`, `role`, `phone_number`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `password`, `created_at`) VALUES
(1, 'Harvest Admin', 'admin@gmail.com', 'admin', '9876543210', 'Harvest Fresh Control Room', 'Near City Market', 'Ahmedabad', 'Gujarat', '380001', '$2y$10$84VV85miENJBqJRNa64d0.Y5BeN80U8J7ynzAhaVPVHZlvCkTknjq', '2026-04-06 00:00:00'),
(2, 'Rohit Customer', 'customer@example.com', 'user', '9876501234', '28, Shree Residency', 'Satellite Road', 'Ahmedabad', 'Gujarat', '380015', '$2y$10$2IMSocVCgnV4UpioheVhduzJW3h0gTKXPvGoelCq4S3lduv6z.R.i', '2026-04-06 00:05:00');

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Vegetables', 'Daily fresh sabzi sourced for quick local delivery.'),
(2, 'Fruits', 'Seasonal fruits with same-day freshness and careful handling.'),
(3, 'Dairy', 'Milk, butter, curd, and chilled essentials delivered cold.'),
(4, 'Bakery', 'Fresh breads and baked favourites for breakfast and snacking.'),
(5, 'Daily Needs', 'Staples and home essentials for fast repeat orders.');

INSERT INTO `products` (`id`, `sku`, `name`, `price`, `mrp`, `unit_label`, `description`, `image`, `stock_quantity`, `delivery_minutes`, `is_featured`, `badge_text`, `category_id`, `created_at`) VALUES
(1, 'HF-VEG-001', 'Farm Potato', 39, 46, '1 kg', 'Clean, table-grade potatoes for everyday curries, fries, and tiffin prep.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Potato.jpg', 82, 16, 1, 'Best Seller', 1, '2026-04-06 01:00:00'),
(2, 'HF-VEG-002', 'Red Tomato', 34, 40, '500 g', 'Juicy red tomatoes ideal for gravy, salads, and daily cooking.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Tomato_(1).jpg', 56, 14, 1, 'Farm Pick', 1, '2026-04-06 01:01:00'),
(3, 'HF-VEG-003', 'Red Onion', 43, 50, '1 kg', 'Fresh onions with balanced sharpness, sorted for home kitchen use.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Onions.JPG', 48, 18, 0, 'Kitchen Staple', 1, '2026-04-06 01:02:00'),
(4, 'HF-FRU-001', 'Robusta Banana', 59, 68, '6 pcs', 'Ripe, ready-to-eat bananas packed for breakfast bowls and quick snacks.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Bananas.JPG', 65, 15, 1, 'Ready to Eat', 2, '2026-04-06 01:03:00'),
(5, 'HF-FRU-002', 'Apple Pack', 149, 169, '4 pcs', 'Crunchy apples selected for quality, lunchboxes, and healthy snacking.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Apple.JPG', 31, 20, 1, 'Premium', 2, '2026-04-06 01:04:00'),
(6, 'HF-FRU-003', 'Alphonso Mango', 189, 220, '1 kg', 'Seasonal alphonso mangoes with rich sweetness and gifting-quality selection.', 'https://commons.wikimedia.org/wiki/Special:FilePath/SH-Mango.jpg', 24, 24, 1, 'Seasonal', 2, '2026-04-06 01:05:00'),
(7, 'HF-DAI-001', 'Amul Gold Milk', 36, 38, '500 ml', 'Trusted full-cream milk kept in the cold chain for tea, coffee, and daily use.', 'https://amul.com/files/products/amul-gold.png', 92, 15, 1, 'Cold Chain', 3, '2026-04-06 01:06:00'),
(8, 'HF-DAI-002', 'Amul Butter', 60, 65, '100 g', 'Classic table butter for toast, sandwiches, and everyday cooking.', 'https://amul.com/files/products/amul_tablebutter.jpeg', 44, 18, 0, 'Top Rated', 3, '2026-04-06 01:07:00'),
(9, 'HF-DAI-003', 'Fresh Curd', 48, 55, '400 g', 'Homestyle curd with a thick set texture, chilled for same-day delivery.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Perfect_Curd_yoghurt_picture.JPG', 38, 18, 0, 'Daily Fresh', 3, '2026-04-06 01:08:00'),
(10, 'HF-BAK-001', 'Whole Wheat Bread', 45, 52, '400 g', 'Fresh loaf baked for breakfast and sandwich prep with soft slice texture.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Loaf_Of_Bread.jpg', 29, 20, 1, 'Fresh Bake', 4, '2026-04-06 01:09:00'),
(11, 'HF-BAK-002', 'Butter Croissant', 79, 95, '2 pcs', 'Flaky croissants packed fresh for breakfast combos and office snack runs.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Chocolate_and_croissant.jpg', 18, 22, 0, 'Morning Pick', 4, '2026-04-06 01:10:00'),
(12, 'HF-DLY-001', 'Basmati Rice', 96, 110, '1 kg', 'Long-grain rice for daily meals, biryani prep, and pantry restock orders.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Raw_rice.jpg', 74, 18, 1, 'Staple Buy', 5, '2026-04-06 01:11:00'),
(13, 'HF-DLY-002', 'Strong Teeth Toothpaste', 99, 120, '150 g', 'Family-size toothpaste for daily oral care and repeat convenience orders.', 'https://commons.wikimedia.org/wiki/Special:FilePath/Toothpaste.jpg', 43, 20, 0, 'Family Pack', 5, '2026-04-06 01:12:00'),
(14, 'HF-DLY-003', 'Bath Soap', 38, 45, '125 g', 'Everyday bathing soap for repeat household orders and top-up purchases.', 'https://commons.wikimedia.org/wiki/Special:FilePath/A_bar_of_soap.jpg', 57, 20, 0, 'Everyday Care', 5, '2026-04-06 01:13:00');

ALTER TABLE `users` AUTO_INCREMENT = 3;
ALTER TABLE `categories` AUTO_INCREMENT = 6;
ALTER TABLE `products` AUTO_INCREMENT = 15;
ALTER TABLE `orders` AUTO_INCREMENT = 1;
ALTER TABLE `order_items` AUTO_INCREMENT = 1;

COMMIT;
