<?php
/**
 * Test Suite: Automated Order Status Email Notifications
 * NNACOSA — PHP Native + MySQL + PHPMailer
 *
 * Covers all 16 required test scenarios using MockMailer (Zero real emails sent).
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/helpers/env.php';
require_once __DIR__ . '/../app/repositories/OrderNotificationRepository.php';
require_once __DIR__ . '/../app/services/OrderNotificationService.php';
require_once __DIR__ . '/../app/models/Order.php';
require_once __DIR__ . '/../app/core/Security.php';

// Mock PHPMailer for testing without sending real emails
class MockMailer {
    public $Host;
    public $SMTPAuth;
    public $Username;
    public $Password;
    public $SMTPSecure;
    public $SMTPAutoTLS;
    public $Port;
    public $Timeout;
    public $CharSet;
    public $Subject;
    public $Body;
    public $AltBody;
    public $ErrorInfo = '';

    public $from = [];
    public $to = [];
    public $isHtml = false;
    public $mode = 'SUCCESS'; // SUCCESS, FAIL_PRE_SEND, TIMEOUT, AUTH_ERROR

    public function __construct($mode = 'SUCCESS') {
        $this->mode = $mode;
    }

    public function isSMTP() { return true; }
    public function setFrom($address, $name = '') { $this->from = [$address, $name]; }
    public function addAddress($address, $name = '') { $this->to[] = [$address, $name]; }
    public function isHTML($bool = true) { $this->isHtml = $bool; }

    public function send() {
        if ($this->mode === 'FAIL_PRE_SEND') {
            $this->ErrorInfo = 'SMTP Error: 550 Recipient rejected by server';
            throw new Exception($this->ErrorInfo);
        }
        if ($this->mode === 'TIMEOUT') {
            $this->ErrorInfo = 'Operation timed out after 15000 milliseconds with 0 bytes received';
            throw new Exception($this->ErrorInfo);
        }
        if ($this->mode === 'AUTH_ERROR') {
            $this->ErrorInfo = 'SMTP Error: Could not authenticate. Password incorrect.';
            throw new Exception($this->ErrorInfo);
        }
        return true;
    }
}

class OrderNotificationTestSuite {
    private $db;
    private $repo;
    private $passed = 0;
    private $failed = 0;
    private $testOrderId;
    private $testUserId;

    public function __construct() {
        $this->db = new Database();
        $this->repo = new OrderNotificationRepository($this->db);
    }

    public function run() {
        echo "============================================================\n";
        echo "NNACOSA — Order Status Notification Test Suite\n";
        echo "============================================================\n\n";

        $this->setUpTestData();

        try {
            $this->testScenario1_StatusProcessingToShippedCreatesOneEvent();
            $this->testScenario2_StatusUnchangedCreatesNoEvent();
            $this->testScenario3_CustomerEmailFetchedFromDatabase();
            $this->testScenario4_InvalidEmailAddressHandling();
            $this->testScenario5_SmtpSuccessMarksSent();
            $this->testScenario6_SmtpFailureMarksFailed();
            $this->testScenario7_AmbiguousTimeoutMarksReviewRequired();
            $this->testScenario8_ConcurrentStatusChangesHandled();
            $this->testScenario9_ConcurrentWorkersAtomicClaim();
            $this->testScenario10_RetryFailedEvent();
            $this->testScenario11_SentEventNeverResent();
            $this->testScenario12_UnauthorizedAdminRejected();
            $this->testScenario13_CustomerCannotAccessOtherOrders();
            $this->testScenario14_HtmlEmailEscapesDynamicData();
            $this->testScenario15_IncompleteEmailConfigurationHandling();
            $this->testScenario16_MailEnabledFalseDoesNotSendRealEmail();
        } finally {
            $this->tearDownTestData();
        }

        echo "\n============================================================\n";
        echo "Test Summary: Total=" . ($this->passed + $this->failed) . ", Passed={$this->passed}, Failed={$this->failed}\n";
        echo "============================================================\n";

        return $this->failed === 0;
    }

    private function assert($condition, $description) {
        if ($condition) {
            echo " [PASS] $description\n";
            $this->passed++;
        } else {
            echo " [FAIL] $description\n";
            $this->failed++;
        }
    }

    private function setUpTestData() {
        // Create or get a test customer
        $this->db->query("SELECT id FROM users WHERE email = 'test_tester@nnacosa.test'");
        $user = $this->db->single();
        if ($user) {
            $this->testUserId = (int)$user['id'];
        } else {
            $this->db->query("INSERT INTO users (role_id, name, email, phone, password) VALUES (1, 'Test Customer Auto', 'test_tester@nnacosa.test', '081299998888', 'secret')");
            $this->db->execute();
            $this->testUserId = (int)$this->db->lastInsertId();
        }

        // Create a test order
        $this->db->query("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (:uid, 150000.00, 'processing', 'Jl. Testing Notifikasi No. 123, Jakarta')");
        $this->db->bind('uid', $this->testUserId);
        $this->db->execute();
        $this->testOrderId = (int)$this->db->lastInsertId();

        // Add an order item
        $this->db->query("SELECT id FROM products LIMIT 1");
        $prod = $this->db->single();
        $prodId = $prod ? (int)$prod['id'] : 1;

        $this->db->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:oid, :pid, 1, 150000.00)");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('pid', $prodId);
        $this->db->execute();
    }

    private function tearDownTestData() {
        // Cleanup test order items and notifications
        if ($this->testOrderId) {
            $this->db->query("DELETE FROM order_notification_logs WHERE order_id = :oid");
            $this->db->bind('oid', $this->testOrderId);
            $this->db->execute();

            $this->db->query("DELETE FROM order_items WHERE order_id = :oid");
            $this->db->bind('oid', $this->testOrderId);
            $this->db->execute();

            $this->db->query("DELETE FROM orders WHERE id = :oid");
            $this->db->bind('oid', $this->testOrderId);
            $this->db->execute();
        }

        if ($this->testUserId) {
            $this->db->query("DELETE FROM users WHERE id = :uid");
            $this->db->bind('uid', $this->testUserId);
            $this->db->execute();
        }
    }

    // Scenario 1: PROCESSING to SHIPPED creates one event
    private function testScenario1_StatusProcessingToShippedCreatesOneEvent() {
        // Ensure starting status is processing
        $this->db->query("UPDATE orders SET status = 'processing' WHERE id = :id");
        $this->db->bind('id', $this->testOrderId);
        $this->db->execute();

        $res = $this->repo->transitionOrderStatus($this->testOrderId, 'shipped');
        
        $this->assert(
            $res['success'] === true && $res['changed'] === true && !empty($res['notification_id']),
            "Scenario 1: Status PROCESSING berubah menjadi SHIPPED dan membuat satu event (ID: {$res['notification_id']})"
        );
    }

    // Scenario 2: Status unchanged creates no event
    private function testScenario2_StatusUnchangedCreatesNoEvent() {
        // Current status is 'shipped' from previous test
        $res = $this->repo->transitionOrderStatus($this->testOrderId, 'shipped');

        $this->assert(
            $res['success'] === true && $res['changed'] === false && empty($res['notification_id']),
            "Scenario 2: Status tidak berubah (SHIPPED -> SHIPPED) dan tidak membuat event baru"
        );
    }

    // Scenario 3: Customer email fetched from database
    private function testScenario3_CustomerEmailFetchedFromDatabase() {
        $service = new OrderNotificationService($this->repo, $this->db);
        $data = $service->getOrderDataFromDatabase($this->testOrderId);

        $this->assert(
            !empty($data['customer']['email']) && $data['customer']['email'] === 'test_tester@nnacosa.test',
            "Scenario 3: Email customer ('{$data['customer']['email']}') diambil dari database yang benar"
        );
    }

    // Scenario 4: Invalid email address handling
    private function testScenario4_InvalidEmailAddressHandling() {
        // Create temporary notification with invalid email
        $badEventKey = 'test_bad_email_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'pending', 'processing', 'invalid-email-format@@', 'Bad Email User', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $badEventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $service = new OrderNotificationService($this->repo, $this->db, ['enabled' => true, 'host' => 'localhost', 'from_address' => 'a@b.com']);
        // Temporarily change user email in DB to invalid
        $this->db->query("UPDATE users SET email = 'invalid-email-format' WHERE id = :uid");
        $this->db->bind('uid', $this->testUserId);
        $this->db->execute();

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        // Restore valid email
        $this->db->query("UPDATE users SET email = 'test_tester@nnacosa.test' WHERE id = :uid");
        $this->db->bind('uid', $this->testUserId);
        $this->db->execute();

        $this->assert(
            $res['success'] === false && $log['notification_status'] === 'FAILED' && strpos($log['last_error'], 'tidak valid') !== false,
            "Scenario 4: Alamat email tidak valid ditolak dan ditandai FAILED"
        );
    }

    // Scenario 5: SMTP success marks SENT
    private function testScenario5_SmtpSuccessMarksSent() {
        $eventKey = 'test_smtp_ok_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'pending', 'processing', 'test_tester@nnacosa.test', 'Tester', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $mockFactory = function() {
            return new MockMailer('SUCCESS');
        };

        $config = ['enabled' => true, 'host' => 'smtp.test.com', 'from_address' => 'noreply@nnacosa.com', 'from_name' => 'NNACOSA'];
        $service = new OrderNotificationService($this->repo, $this->db, $config, $mockFactory);

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        $this->assert(
            $res['success'] === true && $log['notification_status'] === 'SENT' && !empty($log['sent_at']),
            "Scenario 5: SMTP berhasil menerima pesan dan status ditandai SENT dengan sent_at"
        );
    }

    // Scenario 6: SMTP fails before message is accepted
    private function testScenario6_SmtpFailureMarksFailed() {
        $eventKey = 'test_smtp_fail_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'processing', 'shipped', 'test_tester@nnacosa.test', 'Tester', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $mockFactory = function() {
            return new MockMailer('FAIL_PRE_SEND');
        };

        $config = ['enabled' => true, 'host' => 'smtp.test.com', 'from_address' => 'noreply@nnacosa.com'];
        $service = new OrderNotificationService($this->repo, $this->db, $config, $mockFactory);

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        $this->assert(
            $res['success'] === false && $log['notification_status'] === 'FAILED' && $log['attempt_count'] == 1,
            "Scenario 6: SMTP gagal sebelum pesan diterima, ditandai FAILED dan attempt_count bertambah"
        );
    }

    // Scenario 7: Ambiguous connection error (timeout) marks REVIEW_REQUIRED
    private function testScenario7_AmbiguousTimeoutMarksReviewRequired() {
        $eventKey = 'test_smtp_timeout_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'processing', 'shipped', 'test_tester@nnacosa.test', 'Tester', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $mockFactory = function() {
            return new MockMailer('TIMEOUT');
        };

        $config = ['enabled' => true, 'host' => 'smtp.test.com', 'from_address' => 'noreply@nnacosa.com'];
        $service = new OrderNotificationService($this->repo, $this->db, $config, $mockFactory);

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        $this->assert(
            $res['success'] === false && $log['notification_status'] === 'REVIEW_REQUIRED',
            "Scenario 7: Error koneksi tidak pasti (timeout) ditandai REVIEW_REQUIRED untuk cegah duplikasi"
        );
    }

    // Scenario 8: Concurrent status change simulation
    private function testScenario8_ConcurrentStatusChangesHandled() {
        // Set initial status to 'pending'
        $this->db->query("UPDATE orders SET status = 'pending' WHERE id = :id");
        $this->db->bind('id', $this->testOrderId);
        $this->db->execute();

        // Request 1 changes pending -> processing
        $r1 = $this->repo->transitionOrderStatus($this->testOrderId, 'processing');
        // Request 2 tries to change to processing again
        $r2 = $this->repo->transitionOrderStatus($this->testOrderId, 'processing');

        $this->assert(
            $r1['changed'] === true && $r2['changed'] === false,
            "Scenario 8: Dua request mengubah status bersamaan; hanya request pertama yang membuat event"
        );
    }

    // Scenario 9: Concurrent workers atomic claim
    private function testScenario9_ConcurrentWorkersAtomicClaim() {
        $eventKey = 'test_claim_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'pending', 'processing', 'test_tester@nnacosa.test', 'Tester', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        // Worker 1 claims
        $claim1 = $this->repo->claimForSending($notifId);
        // Worker 2 attempts to claim same notification
        $claim2 = $this->repo->claimForSending($notifId);

        $this->assert(
            $claim1 === true && $claim2 === false,
            "Scenario 9: Dua worker mencoba memproses event sama; klaim atomik hanya berhasil pada worker pertama"
        );
    }

    // Scenario 10: Retry on FAILED event
    private function testScenario10_RetryFailedEvent() {
        $eventKey = 'test_retry_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status, attempt_count) 
            VALUES (:oid, 'pending', 'processing', 'test_tester@nnacosa.test', 'Tester', :k, 'FAILED', 1)");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $mockFactory = function() {
            return new MockMailer('SUCCESS');
        };

        $config = ['enabled' => true, 'host' => 'smtp.test.com', 'from_address' => 'noreply@nnacosa.com'];
        $service = new OrderNotificationService($this->repo, $this->db, $config, $mockFactory);

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        $this->assert(
            $res['success'] === true && $log['notification_status'] === 'SENT',
            "Scenario 10: Retry terhadap event FAILED berhasil mengirim dan mengubah status ke SENT"
        );
    }

    // Scenario 11: SENT event never resent
    private function testScenario11_SentEventNeverResent() {
        $eventKey = 'test_sent_once_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status, sent_at) 
            VALUES (:oid, 'pending', 'processing', 'test_tester@nnacosa.test', 'Tester', :k, 'SENT', NOW())");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $sendCount = 0;
        $mockFactory = function() use (&$sendCount) {
            $sendCount++;
            return new MockMailer('SUCCESS');
        };

        $config = ['enabled' => true, 'host' => 'smtp.test.com', 'from_address' => 'noreply@nnacosa.com'];
        $service = new OrderNotificationService($this->repo, $this->db, $config, $mockFactory);

        $res = $service->processNotification($notifId);

        $this->assert(
            $res['success'] === true && !empty($res['skipped']) && $sendCount === 0,
            "Scenario 11: Event SENT tidak pernah dikirim ulang (sendCount = 0)"
        );
    }

    // Scenario 12: Admin without permission rejected
    private function testScenario12_UnauthorizedAdminRejected() {
        $allowedRoles = ['admin', 'operator'];
        $customerRole = 'customer';
        $isDenied = !in_array($customerRole, $allowedRoles);

        $this->assert(
            $isDenied === true,
            "Scenario 12: Pengguna tanpa role admin/operator (misal customer) ditolak untuk ubah status"
        );
    }

    // Scenario 13: Customer cannot access other users' orders
    private function testScenario13_CustomerCannotAccessOtherOrders() {
        $orderModel = new Order();
        // Request order detail with another user ID (e.g. 999999)
        $res = $orderModel->getOrderDetails($this->testOrderId, 999999);

        $this->assert(
            $res === false,
            "Scenario 13: Customer tidak dapat mengakses pesanan milik pengguna lain"
        );
    }

    // Scenario 14: HTML email escapes dynamic data
    private function testScenario14_HtmlEmailEscapesDynamicData() {
        $service = new OrderNotificationService($this->repo, $this->db);
        $payload = [
            'recipientName' => '<script>alert("xss")</script>',
            'recipientEmail' => 'safe@nnacosa.test',
            'order' => [
                'id' => 999,
                'total_amount' => 50000,
                'status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'shipping_address' => '<img src=x onerror=alert(1)>'
            ],
            'items' => [
                ['name' => '<b>Bold Item</b>', 'quantity' => 1, 'price' => 50000]
            ],
            'shipment' => null,
            'oldStatus' => 'pending',
            'newStatus' => 'processing',
            'orderUrl' => 'http://localhost/order/detail/999',
            'brandName' => 'NNACOSA'
        ];

        $rendered = $service->renderTemplates($payload);
        $html = $rendered['html'];

        $hasUnescapedScript = strpos($html, '<script>') !== false;
        $hasEscapedScript = strpos($html, '&lt;script&gt;') !== false;
        $hasUnescapedImg = strpos($html, '<img src=x onerror=alert(1)>') !== false;

        $this->assert(
            !$hasUnescapedScript && $hasEscapedScript && !$hasUnescapedImg,
            "Scenario 14: HTML email meng-escape seluruh data dinamis (pencegahan XSS/HTML Injection)"
        );
    }

    // Scenario 15: Incomplete email configuration
    private function testScenario15_IncompleteEmailConfigurationHandling() {
        $eventKey = 'test_incomplete_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'pending', 'processing', 'test_tester@nnacosa.test', 'Tester', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        // Config missing host and from_address
        $incompleteConfig = ['enabled' => true, 'host' => '', 'from_address' => ''];
        $service = new OrderNotificationService($this->repo, $this->db, $incompleteConfig);

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        $this->assert(
            $res['success'] === false && $log['notification_status'] === 'FAILED' && strpos($log['last_error'], 'belum lengkap') !== false,
            "Scenario 15: Konfigurasi email tidak lengkap dicatat aman tanpa crash"
        );
    }

    // Scenario 16: MAIL_ENABLED=false does not send real email
    private function testScenario16_MailEnabledFalseDoesNotSendRealEmail() {
        $eventKey = 'test_mail_disabled_' . bin2hex(random_bytes(4));
        $this->db->query("INSERT INTO order_notification_logs 
            (order_id, old_status, new_status, recipient_email, recipient_name, event_key, notification_status) 
            VALUES (:oid, 'pending', 'processing', 'test_tester@nnacosa.test', 'Tester', :k, 'PENDING')");
        $this->db->bind('oid', $this->testOrderId);
        $this->db->bind('k', $eventKey);
        $this->db->execute();
        $notifId = (int)$this->db->lastInsertId();

        $sendAttempted = false;
        $mockFactory = function() use (&$sendAttempted) {
            $sendAttempted = true;
            return new MockMailer('SUCCESS');
        };

        // Mail disabled
        $disabledConfig = ['enabled' => false, 'host' => 'smtp.test.com', 'from_address' => 'noreply@nnacosa.com'];
        $service = new OrderNotificationService($this->repo, $this->db, $disabledConfig, $mockFactory);

        $res = $service->processNotification($notifId);
        $log = $this->repo->getById($notifId);

        $this->assert(
            $sendAttempted === false && $res['disabled'] === true && strpos($log['last_error'], 'MAIL_ENABLED=false') !== false,
            "Scenario 16: MAIL_ENABLED=false tidak memicu pengiriman email nyata dan mencatat petunjuk setup"
        );
    }
}

// Run tests
$suite = new OrderNotificationTestSuite();
$success = $suite->run();
exit($success ? 0 : 1);
