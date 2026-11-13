-- ============================================================
-- NNACOSA — System Settings & Provider Configuration Migration
-- Tables: system_settings, ai_provider_configs, auth_provider_configs
-- ============================================================

CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `value_type` VARCHAR(20) NOT NULL DEFAULT 'string',
    `is_secret` TINYINT(1) NOT NULL DEFAULT 0,
    `updated_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_group` (`setting_group`),
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_provider_configs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `config_name` VARCHAR(100) NOT NULL,
    `provider` VARCHAR(50) NOT NULL,
    `base_url` VARCHAR(255) NOT NULL,
    `model_id` VARCHAR(100) NOT NULL,
    `encrypted_api_key` TEXT NULL,
    `parameters_json` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `last_tested_at` DATETIME NULL,
    `last_test_status` VARCHAR(50) NULL,
    `last_test_latency_ms` INT NULL,
    `last_test_message` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_provider` (`provider`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_provider_configs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_type` VARCHAR(50) NOT NULL UNIQUE,
    `encrypted_credentials` TEXT NULL,
    `settings_json` TEXT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `last_tested_at` DATETIME NULL,
    `last_test_status` VARCHAR(50) NULL,
    `last_test_latency_ms` INT NULL,
    `last_test_message` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_provider_type` (`provider_type`),
    INDEX `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_otps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `otp_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `attempts` INT NOT NULL DEFAULT 0,
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`is_used`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

