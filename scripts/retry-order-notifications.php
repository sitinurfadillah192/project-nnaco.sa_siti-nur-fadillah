<?php
/**
 * CLI Script: Retry and Process Pending Order Status Notifications
 * NNACOSA — Automated Order Status Email Notifications
 *
 * Usage:
 *   php scripts/retry-order-notifications.php [--limit=10] [--delay=5] [--force-id=123]
 */

// Ensure this script can only run in CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: This script can only be executed via CLI.\n");
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/env.php';
require_once __DIR__ . '/../app/repositories/OrderNotificationRepository.php';
require_once __DIR__ . '/../app/services/OrderNotificationService.php';

// Parse command line arguments
$options = getopt('', ['limit::', 'delay::', 'force-id::', 'help::']);

if (isset($options['help'])) {
    echo "NNACOSA Order Status Notification Worker\n";
    echo "========================================\n";
    echo "Options:\n";
    echo "  --limit=N      Number of notifications to process (default: 10)\n";
    echo "  --delay=N      Minutes of backoff delay for retrying FAILED items (default: 5)\n";
    echo "  --force-id=N   Process a specific notification ID directly\n";
    echo "  --help         Show this help information\n";
    exit(0);
}

$limit = isset($options['limit']) ? (int)$options['limit'] : 10;
$delay = isset($options['delay']) ? (int)$options['delay'] : (int)env('MAIL_RETRY_DELAY_MINUTES', 5);
$forceId = isset($options['force-id']) ? (int)$options['force-id'] : null;

$db = new Database();
$repository = new OrderNotificationRepository($db);
$service = new OrderNotificationService($repository, $db);

$timestamp = date('Y-m-d H:i:s');
echo "[$timestamp] Starting NNACOSA Order Notification Worker...\n";

if ($forceId) {
    echo "Processing forced notification ID: $forceId\n";
    $res = $service->processNotification($forceId);
    echo "Result: " . json_encode($res, JSON_PRETTY_PRINT) . "\n";
    exit(0);
}

$batch = $service->processPendingAndRetries($limit, $delay);

echo "----------------------------------------\n";
echo "Batch Summary:\n";
echo " - Found Eligible: {$batch['total_found']}\n";
echo " - Processed     : {$batch['processed']}\n";
echo " - Sent          : {$batch['sent']}\n";
echo " - Skipped       : {$batch['skipped']}\n";
echo " - Failed        : {$batch['failed']}\n";
echo "----------------------------------------\n";

if (!empty($batch['details'])) {
    foreach ($batch['details'] as $item) {
        $id = $item['id'];
        $orderId = $item['order_id'];
        $res = $item['result'];
        $statusStr = !empty($res['success']) ? 'SUCCESS' : 'FAILED';
        $msg = $res['message'] ?? ($res['error'] ?? 'N/A');
        echo " - [Notif #$id | Order #$orderId]: $statusStr ($msg)\n";
    }
}

echo "[$timestamp] Notification Worker finished.\n";
exit(0);
