-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2025 at 11:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fluenceshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_url`, `created_at`) VALUES
(1, 'Cameras', 'High-quality cameras for content creators', 'https://images.pexels.com/photos/243757/pexels-photo-243757.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2025-06-30 08:00:00'),
(2, 'Lighting', 'Professional lighting equipment for perfect shots', 'https://images.pexels.com/photos/3851254/pexels-photo-3851254.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2025-06-30 08:00:00'),
(3, 'Audio Equipment', 'Microphones and audio accessories for clear sound', 'https://images.pexels.com/photos/3779726/pexels-photo-3779726.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2025-06-30 08:00:00'),
(4, 'Tripods & Stabilizers', 'Steady your shots with quality tripods and stabilizers', 'https://images.pexels.com/photos/134469/pexels-photo-134469.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2025-06-30 08:00:00'),
(5, 'Accessories', 'Must-have accessories for your equipment', 'https://images.pexels.com/photos/3829271/pexels-photo-3829271.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '2025-06-30 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `combo_deals`
--

CREATE TABLE `combo_deals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `regular_price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `product_ids` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combo_deals`
--

INSERT INTO `combo_deals` (`id`, `name`, `description`, `regular_price`, `discount_price`, `image_url`, `product_ids`, `created_at`) VALUES
(1, 'Streamer Starter Kit', 'Everything you need to start your streaming career. Includes webcam, microphone, and ring light.', 349.97, 279.97, 'https://images.pexels.com/photos/5082567/pexels-photo-5082567.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '3,6,8', '2025-06-30 08:00:00'),
(2, 'Professional Video Creator Bundle', 'Take your video creation to the next level with this professional bundle. Includes DSLR camera, tripod, and microphone.', 1399.97, 1099.97, 'https://images.pexels.com/photos/7232124/pexels-photo-7232124.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', '1,4,6', '2025-06-30 08:00:00'),
(3, 'Portrait Photography Kit', 'Perfect for portrait photography. Includes DSLR camera, ring light, and reflector.', 899.97, 749.97, 'https://images.pexels.com/photos/821749/pexels-photo-821749.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', '1,8,11', '2025-06-30 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `discount_codes`
--

CREATE TABLE `discount_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discount_codes`
--

INSERT INTO `discount_codes` (`id`, `code`, `type`, `value`, `active`, `expires_at`, `created_at`) VALUES
(1, 'WELCOME20', 'percentage', 20.00, 1, '2025-12-31 23:59:59', '2025-06-30 08:00:00'),
(2, 'SUMMER10', 'percentage', 10.00, 1, '2025-09-30 23:59:59', '2025-06-30 08:00:00'),
(3, 'INFLUENCER25', 'percentage', 25.00, 1, '2025-12-31 23:59:59', '2025-06-30 08:00:00'),
(4, 'FREESHIP', 'fixed', 10.00, 1, '2025-12-31 23:59:59', '2025-06-30 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `discount_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_status`, `subtotal`, `shipping`, `tax`, `discount`, `total`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `state`, `zip_code`, `country`, `payment_method`, `discount_code`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 'shipped', 119.98, 10.00, 9.60, 0.00, 139.58, 'lahif', 'khan', 'lahifkhan52@gmail.com', '01738612728', 'Nagra', 'Netrakona', 'dfdd', '221002405', 'CA', 'credit_card', NULL, NULL, '2025-05-08 12:09:56', '2025-05-08 13:14:34');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `quantity`, `total`, `created_at`) VALUES
(1, 1, 9, 59.99, 2, 119.98, '2025-05-08 12:09:56');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `additional_images` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `avg_rating` decimal(3,1) NOT NULL DEFAULT 0.0,
  `rating_count` int(11) NOT NULL DEFAULT 0,
  `specifications` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `additional_images`, `featured`, `avg_rating`, `rating_count`, `specifications`, `created_at`, `updated_at`) VALUES
(1, 1, 'Professional DSLR Camera', 'High-quality DSLR camera for professional content creators. Capture stunning photos and videos with this versatile camera.\r\n\r\nFeatures:\r\n- 24.2 megapixel CMOS sensor\r\n- 4K video recording\r\n- 3.0-inch vari-angle touchscreen\r\n- Built-in Wi-Fi and Bluetooth\r\n- 45-point all cross-type AF system', 799.99, 25, 'https://images.pexels.com/photos/5022806/pexels-photo-5022806.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', '[\"https://images.pexels.com/photos/243757/pexels-photo-243757.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2\", \"https://images.pexels.com/photos/51383/photo-camera-subject-photographer-51383.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2\"]', 1, 4.8, 24, '{\"Sensor Type\":\"CMOS\", \"Megapixels\":\"24.2\", \"Video Resolution\":\"4K\", \"Screen Size\":\"3.0 inch\", \"Connectivity\":\"Wi-Fi, Bluetooth\", \"Battery Life\":\"1200 shots\", \"Weight\":\"650g\"}', '2025-06-30 08:00:00', '2025-05-08 13:11:31'),
(2, 1, 'Compact Mirrorless Camera', 'Lightweight mirrorless camera perfect for vlogging and on-the-go content creation.\r\n\r\nFeatures:\r\n- 20.1 megapixel sensor\r\n- 4K video recording\r\n- 180-degree flip screen\r\n- Eye-detection autofocus\r\n- Compact and lightweight design', 649.99, 18, 'https://images.pexels.com/photos/243757/pexels-photo-243757.jpeg?auto=compress&cs=tinysrgb&w=600', '[\"https://images.pexels.com/photos/274973/pexels-photo-274973.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2\", \"https://images.pexels.com/photos/1787220/pexels-photo-1787220.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2\"]', 1, 4.6, 18, '{\"Sensor Type\":\"CMOS\", \"Megapixels\":\"20.1\", \"Video Resolution\":\"4K\", \"Screen Size\":\"3.0 inch flip\", \"Connectivity\":\"Wi-Fi, Bluetooth\", \"Battery Life\":\"350 shots\", \"Weight\":\"390g\"}', '2025-06-30 08:00:00', '2025-05-08 12:37:16'),
(3, 1, 'HD Webcam', 'Crystal clear HD webcam for streaming and video conferencing.\r\n\r\nFeatures:\r\n- 1080p video resolution\r\n- Built-in microphone with noise reduction\r\n- Automatic light correction\r\n- Universal clip fits laptops and monitors\r\n- Plug and play USB connection', 79.99, 50, 'https://images.pexels.com/photos/27574914/pexels-photo-27574914/free-photo-of-security-start-home-web-cam-neon-pastel-light.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', NULL, 0, 4.3, 42, '{\"Resolution\":\"1080p\", \"Frame Rate\":\"30fps\", \"Microphone\":\"Yes, dual\", \"Connection\":\"USB-A\", \"Compatibility\":\"Windows, macOS\", \"Field of View\":\"78 degrees\"}', '2025-06-30 08:00:00', '2025-05-08 12:34:06'),
(4, 4, 'Professional Tripod', 'Sturdy tripod for cameras and smartphones. Provides stable support for professional shots.\r\n\r\nFeatures:\r\n- Adjustable height from 21\" to 65\"\r\n- 360° panoramic rotation\r\n- Quick-release plate\r\n- Lightweight aluminum construction\r\n- Carrying bag included', 99.99, 35, 'https://images.pexels.com/photos/212372/pexels-photo-212372.jpeg?auto=compress&cs=tinysrgb&w=600', NULL, 0, 4.5, 31, '{\"Material\":\"Aluminum\", \"Maximum Height\":\"65 inches\", \"Minimum Height\":\"21 inches\", \"Maximum Load\":\"15 lbs\", \"Weight\":\"3.4 lbs\", \"Folded Length\":\"24 inches\"}', '2025-06-30 08:00:00', '2025-05-08 12:23:30'),
(5, 4, 'Smartphone Gimbal Stabilizer', 'Keep your smartphone videos smooth and professional with this 3-axis gimbal stabilizer.\r\n\r\nFeatures:\r\n- 3-axis stabilization\r\n- 12-hour battery life\r\n- Active tracking mode\r\n- Foldable design\r\n- Bluetooth connectivity', 119.99, 22, 'https://media.istockphoto.com/id/1190671525/photo/man-doing-live-video-with-phone-with-stabilizer-in-ny.jpg?b=1&s=612x612&w=0&k=20&c=Oq1FKJKcU6vbBTwWiayPz4lyW9mU4h7pv2VwFcD3ER4=', NULL, 1, 4.7, 29, '{\"Axes\":\"3-axis\", \"Battery Life\":\"12 hours\", \"Maximum Payload\":\"280g\", \"Connectivity\":\"Bluetooth 5.0\", \"Charging\":\"USB-C\", \"Weight\":\"340g\"}', '2025-06-30 08:00:00', '2025-05-08 12:30:18'),
(6, 3, 'Professional Condenser Microphone', 'Studio-quality condenser microphone for podcasting, voice-overs, and music recording.\r\n\r\nFeatures:\r\n- Cardioid pickup pattern\r\n- 24-bit/96kHz resolution\r\n- Zero-latency monitoring\r\n- Gain control and mute button\r\n- Plug and play USB connection', 129.99, 20, 'https://images.pexels.com/photos/3825764/pexels-photo-3825764.jpeg?auto=compress&cs=tinysrgb&w=600', NULL, 1, 4.8, 36, '{\"Type\":\"Condenser\", \"Pattern\":\"Cardioid\", \"Frequency Response\":\"20Hz - 20kHz\", \"Sample Rate\":\"24-bit/96kHz\", \"Connection\":\"USB-C\", \"Headphone Output\":\"Yes\"}', '2025-06-30 08:00:00', '2025-05-08 12:27:18'),
(7, 3, 'Wireless Lavalier Microphone', 'Discreet clip-on microphone perfect for interviews, vlogs, and presentations.\r\n\r\nFeatures:\r\n- Wireless transmission up to 100ft\r\n- 6-hour battery life\r\n- Noise cancellation technology\r\n- Easy plug-and-play setup\r\n- Compatible with cameras and smartphones', 69.99, 28, 'https://images.pexels.com/photos/20567845/pexels-photo-20567845/free-photo-of-wireless-microphone-charging-port.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', NULL, 0, 4.4, 27, '{\"Type\":\"Lavalier\", \"Pattern\":\"Omnidirectional\", \"Range\":\"100 feet\", \"Battery Life\":\"6 hours\", \"Frequency\":\"2.4GHz\", \"Compatibility\":\"Camera, Smartphone, Tablet\"}', '2025-06-30 08:00:00', '2025-05-08 12:50:49'),
(8, 2, '18\" LED Ring Light', 'Perfect lighting for beauty, makeup, and portrait content creation.\r\n\r\nFeatures:\r\n- Adjustable color temperature (3200K-5600K)\r\n- Dimming from 1%-100%\r\n- Smartphone holder\r\n- Remote control included\r\n- Sturdy tripod stand', 89.99, 30, 'https://images.pexels.com/photos/4620853/pexels-photo-4620853.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', NULL, 1, 4.6, 33, '{\"Diameter\":\"18 inches\", \"Color Temperature\":\"3200K-5600K\", \"Power\":\"60W\", \"Dimming\":\"1%-100%\", \"Stand Height\":\"78 inches\", \"Power Supply\":\"AC Adapter\"}', '2025-06-30 08:00:00', '2025-05-08 12:42:20'),
(9, 2, 'Portable LED Video Light Panel', 'Compact and powerful LED panel for on-location video shooting.\r\n\r\nFeatures:\r\n- 280 high-quality LED beads\r\n- Bi-color adjustment (3200K-5600K)\r\n- Built-in rechargeable battery\r\n- Aluminum alloy construction\r\n- Multiple mounting options', 59.99, 23, 'https://m.media-amazon.com/images/S/al-na-9d5791cf-3faf/c0149d1d-8876-4f70-ac29-ae3bc87ec5f9._CR0,0,1200,628_SX920_CB1169409_QL70_.jpg', NULL, 0, 4.5, 22, '{\"LEDs\":\"280\", \"Color Temperature\":\"3200K-5600K\", \"Battery\":\"4000mAh\", \"Runtime\":\"2-4 hours\", \"Charging\":\"USB-C\", \"Dimensions\":\"5.9 x 3.9 x 0.4 inches\"}', '2025-06-30 08:00:00', '2025-05-08 12:39:49'),
(10, 5, 'Camera Backpack', 'Durable and spacious backpack for safely transporting all your camera equipment.\r\n\r\nFeatures:\r\n- Customizable dividers\r\n- Laptop compartment (fits up to 15\")\r\n- Weather-resistant material\r\n- Side tripod mount\r\n- Comfortable padded straps', 69.99, 15, 'https://images.pexels.com/photos/1549974/pexels-photo-1549974.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', NULL, 0, 4.7, 19, '{\"Material\":\"Water-resistant nylon\", \"Capacity\":\"20L\", \"Laptop Compartment\":\"15 inches\", \"Dimensions\":\"18 x 12 x 8 inches\", \"Weight\":\"2.6 lbs\", \"Color\":\"Black\"}', '2025-06-30 08:00:00', '2025-05-08 13:06:45'),
(11, 5, '5-in-1 Reflector Kit', 'Versatile light reflector for photography and video production.\r\n\r\nFeatures:\r\n- 5 surfaces: gold, silver, white, black, and translucent\r\n- Collapsible design for easy transport\r\n- 43\" diameter when open\r\n- Carrying case included\r\n- Durable construction', 29.99, 40, 'https://images.pexels.com/photos/1932666/pexels-photo-1932666.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', NULL, 0, 4.4, 21, '{\"Diameter\":\"43 inches\", \"Surfaces\":\"Gold, Silver, White, Black, Translucent\", \"Collapsed Size\":\"15 inches\", \"Material\":\"Nylon, Steel Frame\", \"Weight\":\"1.5 lbs\"}', '2025-06-30 08:00:00', '2025-05-08 13:05:16'),
(12, 5, 'Wireless Remote Shutter', 'Control your camera remotely for selfies, group shots, and long exposures.\r\n\r\nFeatures:\r\n- Range up to 100 feet\r\n- Compatible with most DSLR and mirrorless cameras\r\n- Instant or 2-second delay modes\r\n- Long battery life\r\n- Compact and lightweight design', 19.99, 50, 'https://images.pexels.com/photos/9910849/pexels-photo-9910849.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1', NULL, 0, 4.3, 15, '{\"Range\":\"100 feet\", \"Battery\":\"CR2032\", \"Battery Life\":\"3 years standby\", \"Compatibility\":\"Most Canon, Nikon, Sony\", \"Dimensions\":\"1.6 x 1.2 x 0.5 inches\", \"Weight\":\"0.6 oz\"}', '2025-06-30 08:00:00', '2025-05-08 13:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 1, 5, 'Excellent camera! The image quality is outstanding and the 4K video is crystal clear. I\'ve been using it for my YouTube channel and the results are professional. Highly recommended!', '2025-06-15 08:00:00'),
(2, 1, 2, 4, 'Great camera for the price. The only thing I wish it had was better battery life, but overall I\'m very satisfied with my purchase.', '2025-06-18 08:00:00'),
(3, 6, 1, 5, 'This microphone is a game-changer for my podcast. The sound quality is excellent, and it was super easy to set up. I love the zero-latency monitoring feature!', '2025-06-20 08:00:00'),
(4, 8, 3, 4, 'The ring light makes a huge difference in my makeup tutorials. Nice even lighting and adjustable brightness. The only reason I didn\'t give 5 stars is because the stand is a bit wobbly.', '2025-06-22 08:00:00'),
(5, 5, 2, 5, 'This gimbal has completely transformed my phone videos. So smooth and professional looking! The battery life is impressive too.', '2025-06-25 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `last_login`, `created_at`) VALUES
(1, 'johndoe', 'john@example.com', '$2y$10$92IOVMjt9YCsxAVw.unvl.DYG7JtDQwm6l.pKDYAFEGzL7soD0Bwa', '2025-06-29 08:00:00', '2025-05-15 08:00:00'),
(2, 'janedoe', 'jane@example.com', '$2y$10$92IOVMjt9YCsxAVw.unvl.DYG7JtDQwm6l.pKDYAFEGzL7soD0Bwa', '2025-06-28 08:00:00', '2025-05-20 08:00:00'),
(3, 'sarasmith', 'sara@example.com', '$2y$10$92IOVMjt9YCsxAVw.unvl.DYG7JtDQwm6l.pKDYAFEGzL7soD0Bwa', '2025-06-27 08:00:00', '2025-06-01 08:00:00'),
(4, 'lahifkhan52', 'lahifkhan52@gmail.com', '$2y$10$SM7WbRtO/N6UY9mwDdIBLe2yn1k0DEU9iRHVu2aUv8oenINELwOR.', NULL, '2025-05-08 12:08:28');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `address`, `city`, `state`, `zip_code`, `country`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'John', 'Doe', '123-456-7890', '123 Main St', 'New York', 'NY', '10001', 'US', 'https://randomuser.me/api/portraits/men/1.jpg', '2025-05-15 08:00:00', '2025-05-15 08:00:00'),
(2, 2, 'Jane', 'Doe', '123-456-7891', '456 Oak Ave', 'Los Angeles', 'CA', '90001', 'US', 'https://randomuser.me/api/portraits/women/1.jpg', '2025-05-20 08:00:00', '2025-05-20 08:00:00'),
(3, 3, 'Sara', 'Smith', '123-456-7892', '789 Pine St', 'Chicago', 'IL', '60007', 'US', 'https://randomuser.me/api/portraits/women/2.jpg', '2025-06-01 08:00:00', '2025-06-01 08:00:00'),
(4, 4, NULL, NULL, '01738612728', 'Nagra', 'Netrakona', 'dfdd', '221002405', 'CA', NULL, '2025-05-08 12:08:28', '2025-05-08 12:09:56');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 1, 2, '2025-06-01 08:00:00'),
(2, 1, 8, '2025-06-01 08:00:00'),
(3, 2, 5, '2025-06-02 08:00:00'),
(4, 2, 6, '2025-06-02 08:00:00'),
(5, 3, 1, '2025-06-03 08:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `combo_deals`
--
ALTER TABLE `combo_deals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `combo_deals`
--
ALTER TABLE `combo_deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
