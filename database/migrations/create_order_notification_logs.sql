-- ==========================================================
-- Migration: Create order_notification_logs Table
-- NNACOSA — Automated Order Status Email Notifications
-- ==========================================================

CREATE TABLE IF NOT EXISTS `order_notification_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `old_status` VARCHAR(50) NOT NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `recipient_email` VARCHAR(150) NOT NULL,
    `recipient_name` VARCHAR(100) NULL,
    `event_key` VARCHAR(100) NOT NULL,
    `notification_status` ENUM('PENDING', 'SENDING', 'SENT', 'FAILED', 'REVIEW_REQUIRED') NOT NULL DEFAULT 'PENDING',
    `whatsapp_status` ENUM('NOT_SENT', 'SENT', 'FAILED') NOT NULL DEFAULT 'NOT_SENT',
    `whatsapp_phone` VARCHAR(30) NULL,
    `whatsapp_sent_at` DATETIME NULL,
    `whatsapp_error` TEXT NULL,
    `attempt_count` INT(11) NOT NULL DEFAULT 0,
    `max_attempts` INT(11) NOT NULL DEFAULT 5,
    `last_error` TEXT NULL,
    `sent_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_key` (`event_key`),
    KEY `idx_notif_status_created` (`notification_status`, `created_at`),
    KEY `idx_order_id` (`order_id`),
    CONSTRAINT `fk_notif_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
