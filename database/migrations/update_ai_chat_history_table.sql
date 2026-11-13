-- ============================================================
-- NNACOSA — Update ai_chat_history for Conversation Tracking
-- ============================================================

ALTER TABLE `ai_chat_history` 
    ADD COLUMN IF NOT EXISTS `conversation_id` VARCHAR(64) NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `session_id` VARCHAR(64) NULL AFTER `conversation_id`,
    ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL AFTER `response`,
    ADD COLUMN IF NOT EXISTS `model_id` VARCHAR(100) NULL AFTER `ip_address`,
    ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'completed' AFTER `model_id`;

-- Add indexes if not exists
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ai_chat_history' AND index_name = 'idx_conversation_id');
SET @sql := IF(@exist = 0, 'ALTER TABLE ai_chat_history ADD INDEX idx_conversation_id (conversation_id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ai_chat_history' AND index_name = 'idx_created_at');
SET @sql := IF(@exist = 0, 'ALTER TABLE ai_chat_history ADD INDEX idx_created_at (created_at)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
