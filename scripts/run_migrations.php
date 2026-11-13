<?php
/**
 * Migration runner for NNACOSA database migrations
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = new Database();
    $sqlFile = __DIR__ . '/../database/migrations/create_order_notification_logs.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    $db->query($sql);
    $db->execute();

    echo "Migration completed successfully: order_notification_logs created.\n";

    // Verify
    $db->query("DESCRIBE order_notification_logs");
    $columns = $db->resultSet();
    echo "Columns in order_notification_logs:\n";
    foreach ($columns as $col) {
        echo " - {$col['Field']} ({$col['Type']}) [{$col['Key']}]\n";
    }

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
