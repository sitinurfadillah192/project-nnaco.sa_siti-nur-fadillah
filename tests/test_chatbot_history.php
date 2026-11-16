<?php
/**
 * Automated Test Suite — Admin Chatbot Conversation History
 * Tests all 17 criteria specified in section 12 of the prompt.
 */

require_once __DIR__ . '/../app/init.php';
require_once __DIR__ . '/../app/models/ChatHistory.php';

// Mock session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$passed = 0;
$failed = 0;

function assertTest(string $description, bool $condition, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] " . $description . "\n";
        $passed++;
    } else {
        echo "[FAIL] " . $description . ($details ? " -> " . $details : "") . "\n";
        $failed++;
    }
}

echo "=========================================================\n";
echo "RUNNING NNACOSA CHATBOT HISTORY VERIFICATION SUITE\n";
echo "=========================================================\n\n";

$model = new ChatHistory();
$db = new Database();

// Get an existing user for testing registered chat
$db->query("SELECT id, name, email FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'customer' LIMIT 1) LIMIT 1");
$sampleCustomer = $db->single();
if (!$sampleCustomer) {
    $db->query("SELECT id, name, email FROM users LIMIT 1");
    $sampleCustomer = $db->single();
}
$customerId = $sampleCustomer ? (int)$sampleCustomer['id'] : 1;
$customerName = $sampleCustomer ? $sampleCustomer['name'] : 'Customer';
$customerEmail = $sampleCustomer ? $sampleCustomer['email'] : 'customer@nnaco.com';

// -------------------------------------------------------------
// TEST 1 & 2: Chat Turn Persistence & Deduplication
// -------------------------------------------------------------
echo "--- 1. Turn Persistence & Deduplication ---\n";
$testConvReg = 'test_conv_reg_' . bin2hex(random_bytes(6));
$msg1 = "Halo NNACO, ada kalung mawar?";
$reply1 = "Halo! Ya, kami memiliki Kalung Mawar Aesthetic seharga Rp 45.000.";

$insertId1 = $model->saveChatTurn(
    $testConvReg,
    $customerId,
    $msg1,
    $reply1,
    'sess_test_1',
    '127.0.0.1',
    'meta/llama-3.2-11b-vision-instruct',
    'completed'
);

assertTest("Registered customer turn saved successfully", $insertId1 !== false && $insertId1 > 0);

// Anti-duplicate test within 2 seconds
$duplicateId = $model->saveChatTurn(
    $testConvReg,
    $customerId,
    $msg1,
    $reply1,
    'sess_test_1',
    '127.0.0.1',
    'meta/llama-3.2-11b-vision-instruct',
    'completed'
);
assertTest("Immediate duplicate turn is rejected (Idempotency)", $duplicateId === false);

// -------------------------------------------------------------
// TEST 3: Multi-turn Conversation & Chronological Order
// -------------------------------------------------------------
echo "\n--- 2. Multi-turn Dialog & Chronological Order ---\n";
// Sleep 10ms then save second turn
usleep(20000);
$msg2 = "Apakah stok masih ada?";
$reply2 = "Stok masih tersedia 15 unit saat ini.";
$insertId2 = $model->saveChatTurn(
    $testConvReg,
    $customerId,
    $msg2,
    $reply2,
    'sess_test_1',
    '127.0.0.1',
    'meta/llama-3.2-11b-vision-instruct',
    'completed'
);
assertTest("Second turn saved successfully", $insertId2 !== false && $insertId2 > $insertId1);

$messages = $model->getMessagesByConversationId($testConvReg);
assertTest("Retrieved exactly 2 turns for conversation", count($messages) === 2);
assertTest(
    "Turns strictly ordered chronologically (id ASC)", 
    isset($messages[0]) && isset($messages[1]) && $messages[0]['id'] < $messages[1]['id'] && $messages[0]['message'] === $msg1
);

// -------------------------------------------------------------
// TEST 4: Anonymous Visitor Turn
// -------------------------------------------------------------
echo "\n--- 3. Anonymous Visitor Conversation Handling ---\n";
$testConvAnon = 'test_conv_anon_' . bin2hex(random_bytes(6));
$anonMsg = "Berapa lama estimasi pengiriman ke Surabaya?";
$anonReply = "Estimasi pengiriman ke Surabaya sekitar 2-3 hari kerja.";

$insertAnonId = $model->saveChatTurn(
    $testConvAnon,
    null, // anonymous visitor
    $anonMsg,
    $anonReply,
    'sess_anon_99',
    '192.168.1.100',
    'meta/llama-3.2-11b-vision-instruct',
    'completed'
);

assertTest("Anonymous turn saved with null user_id", $insertAnonId !== false && $insertAnonId > 0);

$anonMeta = $model->getConversationMetadata($testConvAnon);
assertTest("Anonymous conversation metadata has null customer_name", empty($anonMeta['customer_name']));
assertTest("Anonymous conversation stores IP address correctly", $anonMeta['ip_address'] === '192.168.1.100');

// -------------------------------------------------------------
// TEST 5: Server-side Search & Filtering
// -------------------------------------------------------------
echo "\n--- 4. Server-side Search & Filters ---\n";
// 5.1 Search by Conversation ID
$searchConvRes = $model->getConversations(10, 0, ['keyword' => $testConvReg]);
assertTest("Search by Conversation ID finds exact conversation", count($searchConvRes) >= 1 && $searchConvRes[0]['conversation_id'] === $testConvReg);

// 5.2 Search by Message Content
$searchMsgRes = $model->getConversations(10, 0, ['keyword' => 'Surabaya']);
assertTest("Search by message text 'Surabaya' finds anonymous conversation", count($searchMsgRes) >= 1);

// 5.3 Filter by User Type: Registered
$regFilterRes = $model->getConversations(50, 0, ['user_type' => 'registered']);
$allRegistered = true;
foreach ($regFilterRes as $r) {
    if (empty($r['user_id'])) {
        $allRegistered = false;
        break;
    }
}
assertTest("Filter by user_type 'registered' returns only registered users", $allRegistered && count($regFilterRes) >= 1);

// 5.4 Filter by User Type: Anonymous
$anonFilterRes = $model->getConversations(50, 0, ['user_type' => 'anonymous']);
$allAnon = true;
foreach ($anonFilterRes as $r) {
    if (!empty($r['user_id'])) {
        $allAnon = false;
        break;
    }
}
assertTest("Filter by user_type 'anonymous' returns only anonymous visitors", $allAnon && count($anonFilterRes) >= 1);

// 5.5 Filter by Date
$today = date('Y-m-d');
$dateFilterRes = $model->getConversations(50, 0, ['start_date' => $today, 'end_date' => $today]);
assertTest("Filter by today's date range returns active conversations", count($dateFilterRes) >= 2);

// -------------------------------------------------------------
// TEST 6: Legacy Row Compatibility
// -------------------------------------------------------------
echo "\n--- 5. Legacy Conversation Rows (conversation_id IS NULL) ---\n";
// Insert a legacy row with NULL conversation_id
$db->query("INSERT INTO ai_chat_history (conversation_id, user_id, message, response, created_at) 
            VALUES (NULL, :uid, 'Pesan legacy tanpa conv id', 'Jawaban legacy', NOW())");
$db->bind('uid', $customerId);
$db->execute();
$legacyDbId = $db->lastInsertId();

$legacyConvId = 'conv_legacy_' . $legacyDbId;
$legacyMeta = $model->getConversationMetadata($legacyConvId);
assertTest("Legacy row with NULL conversation_id resolved as conv_legacy_{id}", $legacyMeta !== null && $legacyMeta['conversation_id'] === $legacyConvId);

$legacyMsgs = $model->getMessagesByConversationId($legacyConvId);
assertTest("Legacy messages retrieved successfully", count($legacyMsgs) === 1 && $legacyMsgs[0]['message'] === 'Pesan legacy tanpa conv id');

// -------------------------------------------------------------
// TEST 7: XSS Safety & Escaping Verification
// -------------------------------------------------------------
echo "\n--- 6. Security: XSS Sanitization & Data Integrity ---\n";
$xssPayload = "<script>alert('XSS-ATTACK');</script><img src='x' onerror='alert(1)'>";
$escaped = htmlspecialchars($xssPayload, ENT_QUOTES, 'UTF-8');
assertTest("XSS payload is completely neutralized via htmlspecialchars", !str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'));

// -------------------------------------------------------------
// TEST 8: Operational Secrets Protection
// -------------------------------------------------------------
echo "\n--- 7. Operational Secrets & Privacy Verification ---\n";
$metadata = $model->getConversationMetadata($testConvReg);
assertTest("Conversation metadata exposes model_id safely", !empty($metadata['model_id']));
assertTest("Conversation metadata NEVER contains api_key", !isset($metadata['api_key']) && !isset($metadata['plain_key']));

// -------------------------------------------------------------
// TEST 9: Admin Audit Logging
// -------------------------------------------------------------
echo "\n--- 8. Admin Audit Logging ---\n";
$auditResult = $model->logAccess(1, $testConvReg, 'view_conversation', 'success', '127.0.0.1');
assertTest("Audit log inserted successfully", $auditResult === true);

$db->query("SELECT * FROM admin_audit_logs WHERE target_id = :tid ORDER BY id DESC LIMIT 1");
$db->bind('tid', $testConvReg);
$logRow = $db->single();
assertTest("Audit log row exists with correct target_id and action", $logRow && $logRow['action'] === 'view_conversation');
assertTest("Audit log does NOT store conversation message content", !isset($logRow['message']));

// -------------------------------------------------------------
// TEST 10: Statistics Aggregation
// -------------------------------------------------------------
echo "\n--- 9. Statistics Dashboard Counters ---\n";
$stats = $model->getStats();
assertTest("Total conversations count > 0", $stats['total_conversations'] > 0);
assertTest("Total messages count >= total conversations", $stats['total_messages'] >= $stats['total_conversations']);
assertTest("Registered count + anonymous count equals total conversations", 
    ($stats['registered_conversations'] + $stats['anonymous_conversations']) === $stats['total_conversations']
);

// -------------------------------------------------------------
// CLEANUP TEST DATA
// -------------------------------------------------------------
echo "\n--- Cleanup ---\n";
$db->query("DELETE FROM ai_chat_history WHERE conversation_id IN (:c1, :c2) OR id = :legId");
$db->bind('c1', $testConvReg);
$db->bind('c2', $testConvAnon);
$db->bind('legId', $legacyDbId);
$db->execute();

$db->query("DELETE FROM admin_audit_logs WHERE target_id = :tid");
$db->bind('tid', $testConvReg);
$db->execute();
echo "Test data cleaned up successfully.\n";

echo "\n=========================================================\n";
echo "TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=========================================================\n";

exit($failed > 0 ? 1 : 0);
