-- Migration: WhatsApp OTP Authentication
-- Adds phone number support to users and creates whatsapp_otps table

-- 1. Modify users table to support phone login
ALTER TABLE `users` 
    ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) NULL UNIQUE AFTER `email`,
    ADD COLUMN IF NOT EXISTS `is_phone_verified` TINYINT(1) DEFAULT 0 AFTER `phone`,
    MODIFY COLUMN `email` VARCHAR(100) NULL,
    MODIFY COLUMN `password` VARCHAR(255) NULL;

-- 2. Create whatsapp_otps table
CREATE TABLE IF NOT EXISTS `whatsapp_otps` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `phone` VARCHAR(20) NOT NULL,
    `otp_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `attempts` INT(11) NOT NULL DEFAULT 0,
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_phone_created` (`phone`, `created_at`),
    KEY `idx_phone_active` (`phone`, `is_used`, `expires_at`),
    KEY `idx_ip_created` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
