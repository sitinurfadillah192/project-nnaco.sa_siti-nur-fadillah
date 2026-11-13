-- ==========================================================
-- Database Setup untuk Sistem Manajemen Toko Aksesoris AI
-- Nama Database: nnaco_sa
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `nnaco_sa`;
USE `nnaco_sa`;

-- ==========================================
-- 1. TABEL: users
-- ==========================================
CREATE TABLE `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('customer', 'admin', 'operator', 'owner') NOT NULL DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 2. TABEL: categories
-- ==========================================
CREATE TABLE `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 3. TABEL: products
-- ==========================================
CREATE TABLE `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT(11) NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_slug` (`slug`),
    KEY `idx_category` (`category_id`),
    CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 4. TABEL: carts
-- ==========================================
CREATE TABLE `carts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `quantity` INT(11) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_product` (`product_id`),
    CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 5. TABEL: orders
-- ==========================================
CREATE TABLE `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `shipping_address` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 6. TABEL: order_details
-- ==========================================
CREATE TABLE `order_details` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `quantity` INT(11) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`),
    CONSTRAINT `fk_od_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_od_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 7. TABEL: payments
-- ==========================================
CREATE TABLE `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `payment_proof` VARCHAR(255),
    `status` ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'pending',
    `verified_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `verified_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_verifier` (`verified_by`),
    CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 8. TABEL: shipments
-- ==========================================
CREATE TABLE `shipments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `courier` VARCHAR(50) NOT NULL,
    `tracking_number` VARCHAR(100),
    `status` ENUM('preparing', 'shipped', 'delivered') NOT NULL DEFAULT 'preparing',
    `shipped_at` TIMESTAMP NULL DEFAULT NULL,
    `delivered_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    CONSTRAINT `fk_shipment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 9. TABEL: ai_recommendations
-- ==========================================
CREATE TABLE `ai_recommendations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `recommended_product_id` INT(11) NOT NULL,
    `score` DECIMAL(5,4) NOT NULL,
    `reason` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_product` (`recommended_product_id`),
    CONSTRAINT `fk_ai_rec_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ai_rec_product` FOREIGN KEY (`recommended_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 10. TABEL: ai_analytics
-- ==========================================
CREATE TABLE `ai_analytics` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `report_type` ENUM('sales_prediction', 'trend_analysis', 'customer_behavior') NOT NULL,
    `report_data` JSON NOT NULL,
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SAMPLE DATA (Seed)
-- ==========================================

-- Password: 'password123'
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Budi Customer', 'customer@nnaco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Ani Admin', 'admin@nnaco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Joko Operator', 'operator@nnaco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator'),
('Siti Owner', 'owner@nnaco.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner');

INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Kalung', 'kalung', 'Koleksi kalung elegan dan modern.'),
('Gelang', 'gelang', 'Berbagai jenis gelang untuk semua acara.'),
('Cincin', 'cincin', 'Cincin emas, perak, dan berlian tiruan.');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `price`, `stock`, `image_url`) VALUES
(1, 'Kalung Mutiara Putih', 'kalung-mutiara-putih', 'Kalung elegan dengan mutiara asli laut.', 250000.00, 15, 'kalung_mutiara.jpg'),
(2, 'Gelang Tali Kulit', 'gelang-tali-kulit', 'Gelang pria dari bahan kulit sintetis premium.', 75000.00, 50, 'gelang_kulit.jpg'),
(3, 'Cincin Titanium Hitam', 'cincin-titanium-hitam', 'Cincin tahan karat dan elegan.', 120000.00, 30, 'cincin_titanium.jpg');

INSERT INTO `carts` (`user_id`, `product_id`, `quantity`) VALUES
(1, 1, 1),
(1, 2, 2);

INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `shipping_address`) VALUES
(1, 400000.00, 'pending', 'Jl. Merdeka No 10, Jakarta');

INSERT INTO `order_details` (`order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 250000.00, 250000.00),
(1, 2, 2, 75000.00, 150000.00);

INSERT INTO `payments` (`order_id`, `payment_method`, `payment_proof`, `status`) VALUES
(1, 'Bank Transfer BCA', 'proof_1.jpg', 'pending');

INSERT INTO `shipments` (`order_id`, `courier`, `tracking_number`, `status`) VALUES
(1, 'JNE Reguler', 'JNEXX123456789', 'preparing');

INSERT INTO `ai_recommendations` (`user_id`, `recommended_product_id`, `score`, `reason`) VALUES
(1, 3, 0.8500, 'User yang membeli gelang kulit biasanya juga membeli cincin titanium.');

INSERT INTO `ai_analytics` (`report_type`, `report_data`) VALUES
('sales_prediction', '{"next_month_prediction": 15000000, "top_category": "Kalung"}');
