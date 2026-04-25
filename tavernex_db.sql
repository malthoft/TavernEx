DROP DATABASE IF EXISTS `tavernex_db`;
CREATE DATABASE IF NOT EXISTS `tavernex_db`;
USE `tavernex_db`;

CREATE TABLE `users` (
  `id` int(11) KEY AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_acc_number` varchar(50) DEFAULT NULL,
  `bank_acc_name` varchar(100) DEFAULT NULL,
  `is_verified` boolean DEFAULT false,
  `is_seller` boolean DEFAULT false,
  `store_status` enum('open','closed') DEFAULT 'open',
  `store_open_time` time DEFAULT '00:00:00',
  `store_close_time` time DEFAULT '23:59:59',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `categories` (
  `id` int(11) KEY AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `device_type` enum('mobile','pc','both') DEFAULT 'both'
);

CREATE TABLE `products` (
  `id` int(11) KEY AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) DEFAULT 1,
  `sold_count` int(11) DEFAULT 0,
  `game` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `login_type` varchar(100) NOT NULL,
  `product_type` enum('Akun','Item','Gamepass','Joki','Mata Uang') DEFAULT 'Akun',
  `image_url` varchar(255) DEFAULT NULL,
  `color_theme` varchar(100) DEFAULT 'bg-gradient-to-br from-blue-900 to-indigo-800',
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `wishlists` (
  `id` int(11) KEY AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `cart` (
  `id` int(11) KEY AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `transactions` (
  `id` varchar(20) PRIMARY KEY,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `qty` int(11) DEFAULT 1,
  `total_price` int(11) NOT NULL,
  `admin_fee` int(11) DEFAULT 0,
  `system_fee` int(11) DEFAULT 0,
  `seller_fee` int(11) DEFAULT 0,
  `net_seller_amount` int(11) DEFAULT 0,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','processing','completed','cancelling','cancelled') DEFAULT 'pending',
  `delivery_proof` varchar(255) DEFAULT NULL,
  `sensitive_data` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `withdrawals` (
  `id` int(11) KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `bank_info` text NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `reviews` (
  `id` int(11) KEY AUTO_INCREMENT,
  `transaction_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `comment` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `notifications` (
  `id` int(11) KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT '#',
  `is_read` boolean DEFAULT false,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `chat_messages` (
  `id` int(11) KEY AUTO_INCREMENT,
  `transaction_id` varchar(20) NOT NULL,
  `sender_role` varchar(50) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `verification_requests` (
  `id` int(11) KEY AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `ktp_image` varchar(255) NOT NULL,
  `whatsapp_number` varchar(50) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `reports` (
  `id` int(11) KEY AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Insert Data Dummy
INSERT INTO `users` (`id`, `username`, `password`, `role`, `is_verified`) VALUES 
(1, 'GuestBuyer_99', '123', 'buyer', 0),
(2, 'ProGamer_ID', '123', 'seller', 1),
(3, 'Admin_Tavern', '123', 'admin', 1);

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES 
(1, 'Genshin Impact', 'genshin-impact'),
(2, 'Valorant', 'valorant'),
(3, 'Mobile Legends', 'mobile-legends'),
(4, 'PUBG Mobile', 'pubg-mobile'),
(5, 'Free Fire', 'free-fire'),
(6, 'Roblox', 'roblox');

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `title`, `price`, `stock`, `game`, `description`, `login_type`, `color_theme`) VALUES 
(1, 2, 1, 'Akun Genshin Impact AR 60 | 30 Bintang 5', 1500000, 1, 'Genshin Impact', 'Akun rawatan pribadi dari hari pertama rilis. Pity event 60/90 guarantee.', 'Email Hoyoverse', 'bg-gradient-to-br from-blue-900 to-indigo-800'),
(2, 2, 2, 'Valorant Rank Immortal 2 | 150 Premium Skins', 2800000, 1, 'Valorant', 'Full akses sampai email pertama. Akun pribadi no hackback.', 'Riot ID', 'bg-gradient-to-br from-red-900 to-rose-800');