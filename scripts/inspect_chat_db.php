<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();

echo "=== DESCRIBE ai_chat_history ===\n";
try {
    $db->query("DESCRIBE ai_chat_history");
    foreach ($db->resultSet() as $col) {
        echo $col['Field'] . ' (' . $col['Type'] . ') ' . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . ' Key:' . $col['Key'] . ' Def:' . $col['Default'] . "\n";
    }
} catch (Exception $e) {
    echo "Error describing ai_chat_history: " . $e->getMessage() . "\n";
}

echo "\n=== SAMPLE DATA FROM ai_chat_history (LIMIT 5) ===\n";
try {
    $db->query("SELECT * FROM ai_chat_history ORDER BY id DESC LIMIT 5");
    $rows = $db->resultSet();
    echo "Total rows sampled: " . count($rows) . "\n";
    print_r($rows);
} catch (Exception $e) {
    echo "Error querying ai_chat_history: " . $e->getMessage() . "\n";
}

echo "\n=== ALL TABLES IN DB ===\n";
$db->query("SHOW TABLES");
foreach ($db->resultSet() as $r) {
    echo array_values($r)[0] . "\n";
}
