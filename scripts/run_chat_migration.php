<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

echo "Menjalankan migrasi ai_chat_history...\n";

$db = new Database();

// Check existing columns
$db->query("DESCRIBE ai_chat_history");
$cols = [];
foreach ($db->resultSet() as $row) {
    $cols[] = $row['Field'];
}

$queries = [];
if (!in_array('conversation_id', $cols)) {
    $queries[] = "ALTER TABLE `ai_chat_history` ADD COLUMN `conversation_id` VARCHAR(64) NULL AFTER `id`";
    $queries[] = "ALTER TABLE `ai_chat_history` ADD INDEX `idx_conversation_id` (`conversation_id`)";
}
if (!in_array('session_id', $cols)) {
    $queries[] = "ALTER TABLE `ai_chat_history` ADD COLUMN `session_id` VARCHAR(64) NULL AFTER `conversation_id`";
}
if (!in_array('ip_address', $cols)) {
    $queries[] = "ALTER TABLE `ai_chat_history` ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `response`";
}
if (!in_array('model_id', $cols)) {
    $queries[] = "ALTER TABLE `ai_chat_history` ADD COLUMN `model_id` VARCHAR(100) NULL AFTER `ip_address`";
}
if (!in_array('status', $cols)) {
    $queries[] = "ALTER TABLE `ai_chat_history` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'completed' AFTER `model_id`";
}

foreach ($queries as $q) {
    echo "Running: $q\n";
    $db->query($q);
    $db->execute();
}

// Add index on created_at if not present
try {
    $db->query("ALTER TABLE `ai_chat_history` ADD INDEX `idx_created_at` (`created_at`)");
    $db->execute();
} catch (Exception $e) {
    // Index already exists
}

echo "Migrasi ai_chat_history selesai successfully.\n";

// Verify structure
$db->query("DESCRIBE ai_chat_history");
foreach ($db->resultSet() as $row) {
    echo " - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
