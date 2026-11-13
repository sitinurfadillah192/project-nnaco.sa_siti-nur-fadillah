<?php
/**
 * Order Notification Service
 * NNACOSA — Automated Order Status Email Notifications
 *
 * Coordinates data gathering, template rendering, SMTP dispatching via PHPMailer,
 * and idempotent logging.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once dirname(__DIR__) . '/repositories/OrderNotificationRepository.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/FonnteService.php';

// Load composer autoloader if present
$autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class OrderNotificationService {
    private $repository;
    private $db;
    private $config;
    private $mailerFactory;

    /**
     * @param OrderNotificationRepository|null $repository
     * @param Database|null $db
     * @param array|null $config Mail configuration array
     * @param callable|null $mailerFactory Optional factory for dependency injection / mocking
     */
    public function __construct($repository = null, $db = null, $config = null, $mailerFactory = null) {
        $this->db = $db ?: new Database();
        $this->repository = $repository ?: new OrderNotificationRepository($this->db);
        
        $configPath = dirname(__DIR__) . '/config/mail.php';
        $defaultConfig = file_exists($configPath) ? require $configPath : [];
        $this->config = $config !== null ? $config : $defaultConfig;
        
        $this->mailerFactory = $mailerFactory;
    }

    /**
     * Triggers status transition and immediately processes notification
     *
     * @param int $orderId
     * @param string $newStatus
     * @param string|null $customEventKey
     * @return array
     */
    public function notifyStatusChange($orderId, $newStatus, $customEventKey = null) {
        // 1. Atomically record status transition in DB
        $transition = $this->repository->transitionOrderStatus($orderId, $newStatus, $customEventKey);

        if (!$transition['success']) {
            return [
                'success' => false,
                'changed' => false,
                'error' => $transition['error'] ?? 'Failed to update order status'
            ];
        }

        // If status didn't change, no notification is created
        if (!$transition['changed']) {
            return [
                'success' => true,
                'changed' => false,
                'message' => 'Status unchanged, no notification created'
            ];
        }

        // Dispatch WhatsApp notification automatically if customer phone is available
        $waResult = $this->sendWhatsAppNotification($transition['notification_id']);

        return [
            'success' => true,
            'changed' => true,
            'notification_id' => $transition['notification_id'],
            'event_key' => $transition['event_key'],
            'whatsapp' => $waResult
        ];
    }

    /**
     * Process a single notification by ID (atomic claim -> render -> send -> update log)
     *
     * @param int $notificationId
     * @return array
     */
    public function processNotification($notificationId) {
        $log = $this->repository->getById($notificationId);
        if (!$log) {
            return ['success' => false, 'error' => 'Notification not found'];
        }

        // Already sent? Never resend!
        if ($log['notification_status'] === 'SENT') {
            return [
                'success' => true,
                'skipped' => true,
                'message' => 'Notification has already been sent.'
            ];
        }

        // Claim notification atomically to prevent concurrent processing
        if (!$this->repository->claimForSending($notificationId)) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'Could not claim notification (already sending or reached max attempts).'
            ];
        }

        // Fetch refreshed log record
        $log = $this->repository->getById($notificationId);
        $orderId = (int)$log['order_id'];

        // 1. Fetch verified order data from DB (never trust frontend)
        $orderData = $this->getOrderDataFromDatabase($orderId);
        if (!$orderData || empty($orderData['order'])) {
            $this->repository->markFailed($notificationId, 'Order or customer not found in database.');
            return ['success' => false, 'error' => 'Order data missing'];
        }

        $order = $orderData['order'];
        $customer = $orderData['customer'];
        $items = $orderData['items'];
        $shipment = $orderData['shipment'];

        $recipientEmail = trim((string)($customer['email'] ?? $log['recipient_email']));
        $recipientName = trim((string)($customer['name'] ?? $log['recipient_name']));

        // 2. Validate email address
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->repository->markFailed($notificationId, 'Alamat email customer tidak valid: ' . $recipientEmail);
            return [
                'success' => false,
                'error' => 'Invalid email address: ' . $recipientEmail
            ];
        }

        // 3. Check if mail sending is enabled in configuration
        if (empty($this->config['enabled'])) {
            $msg = 'Notifikasi belum dikirim: MAIL_ENABLED=false. Silakan atur MAIL_ENABLED=true dan kredensial SMTP pada .env untuk mengaktifkan pengiriman email.';
            $this->repository->markFailed($notificationId, $msg);
            return [
                'success' => false,
                'disabled' => true,
                'message' => $msg
            ];
        }

        // Validate basic SMTP configuration
        if (empty($this->config['host']) || empty($this->config['from_address'])) {
            $msg = 'Konfigurasi SMTP belum lengkap (MAIL_HOST atau MAIL_FROM_ADDRESS kosong).';
            $this->repository->markFailed($notificationId, $msg);
            return [
                'success' => false,
                'error' => $msg
            ];
        }

        // 4. Render email templates
        $rendered = $this->renderTemplates([
            'recipientName' => $recipientName,
            'recipientEmail' => $recipientEmail,
            'order' => $order,
            'items' => $items,
            'shipment' => $shipment,
            'oldStatus' => $log['old_status'],
            'newStatus' => $log['new_status'],
            'orderUrl' => ($this->config['app_url'] ?? BASEURL) . '/order/detail/' . $order['id'],
            'brandName' => $this->config['from_name'] ?? 'NNACOSA'
        ]);

        $subject = sprintf(
            'Pesanan #ORD-%s %s — %s',
            str_pad($order['id'], 5, '0', STR_PAD_LEFT),
            $this->getStatusSubjectLabel($log['new_status']),
            $this->config['from_name'] ?? 'NNACOSA'
        );

        // 5. Send via PHPMailer
        try {
            $mailer = $this->createMailer();

            $mailer->isSMTP();
            $mailer->Host = $this->config['host'];
            $mailer->SMTPAuth = !empty($this->config['username']) || !empty($this->config['password']);
            $mailer->Username = $this->config['username'] ?? '';
            $mailer->Password = $this->config['password'] ?? '';
            
            $encryption = strtolower((string)($this->config['encryption'] ?? 'tls'));
            if ($encryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mailer->SMTPSecure = '';
                $mailer->SMTPAutoTLS = false;
            }

            $mailer->Port = (int)($this->config['port'] ?? 587);
            $mailer->Timeout = (int)($this->config['timeout'] ?? 15);
            $mailer->CharSet = 'UTF-8';

            $mailer->setFrom(
                $this->config['from_address'],
                $this->config['from_name'] ?? 'NNACOSA'
            );
            $mailer->addAddress($recipientEmail, $recipientName);

            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $rendered['html'];
            $mailer->AltBody = $rendered['text'];

            $sent = $mailer->send();

            if ($sent) {
                $this->repository->markSent($notificationId);
                return [
                    'success' => true,
                    'message' => 'Email notification sent successfully.'
                ];
            } else {
                throw new Exception($mailer->ErrorInfo ?: 'Unknown SMTP dispatch failure');
            }

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $isAmbiguous = $this->isAmbiguousConnectionError($errorMessage);

            $this->repository->markFailed($notificationId, $errorMessage, $isAmbiguous);

            return [
                'success' => false,
                'ambiguous' => $isAmbiguous,
                'error' => $this->repository->sanitizeErrorMessage($errorMessage)
            ];
        }
    }

    /**
     * Create PHPMailer instance or use mock factory
     *
     * @return PHPMailer|object
     */
    protected function createMailer() {
        if ($this->mailerFactory && is_callable($this->mailerFactory)) {
            return call_user_func($this->mailerFactory);
        }

        return new PHPMailer(true);
    }

    /**
     * Detects if an error occurred during/after socket transmission where SMTP status is uncertain
     *
     * @param string $error
     * @return bool
     */
    public function isAmbiguousConnectionError($error) {
        $patterns = [
            '/timed?\s*out/i',
            '/stream_socket_client/i',
            '/connection\s+(lost|reset|dropped|aborted)/i',
            '/operation\s+timed\s*out/i',
            '/errno=10060/i', // WSAETIMEDOUT on Windows
            '/errno=110/i'    // ETIMEDOUT on Linux
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $error)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map order status to human readable subject phrase
     */
    private function getStatusSubjectLabel($status) {
        switch (strtolower(trim((string)$status))) {
            case 'processing':
                return 'Sedang Diproses';
            case 'shipped':
                return 'Telah Dikirim';
            case 'completed':
                return 'Selesai';
            case 'cancelled':
                return 'Dibatalkan';
            case 'pending':
                return 'Menunggu Pembayaran';
            default:
                return 'Status Diperbarui';
        }
    }

    /**
     * Retrieve complete verified order data from database
     */
    public function getOrderDataFromDatabase($orderId) {
        // 1. Order
        $this->db->query("SELECT * FROM orders WHERE id = :id");
        $this->db->bind('id', $orderId, PDO::PARAM_INT);
        $order = $this->db->single();
        if (!$order) {
            return null;
        }

        // 2. Customer
        $this->db->query("SELECT id, name, email, phone FROM users WHERE id = :user_id");
        $this->db->bind('user_id', $order['user_id'], PDO::PARAM_INT);
        $customer = $this->db->single();

        // 3. Order Items
        $this->db->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
        $this->db->bind('order_id', $orderId, PDO::PARAM_INT);
        $items = $this->db->resultSet();

        // 4. Shipment (if available)
        $this->db->query("SELECT * FROM shipments WHERE order_id = :order_id ORDER BY id DESC LIMIT 1");
        $this->db->bind('order_id', $orderId, PDO::PARAM_INT);
        $shipment = $this->db->single() ?: null;

        return [
            'order' => $order,
            'customer' => $customer,
            'items' => $items,
            'shipment' => $shipment
        ];
    }

    /**
     * Render both HTML and Plain Text templates
     *
     * @param array $data
     * @return array ['html' => string, 'text' => string]
     */
    public function renderTemplates(array $data) {
        extract($data);

        // HTML
        $htmlTemplate = dirname(__DIR__) . '/templates/emails/order-status.php';
        ob_start();
        if (file_exists($htmlTemplate)) {
            include $htmlTemplate;
        } else {
            echo "<h2>Status Pesanan #ORD-{$order['id']}</h2><p>Status: {$newStatus}</p>";
        }
        $htmlContent = ob_get_clean();

        // Plain Text
        $txtTemplate = dirname(__DIR__) . '/templates/emails/order-status.txt.php';
        ob_start();
        if (file_exists($txtTemplate)) {
            include $txtTemplate;
        } else {
            echo "Status Pesanan #ORD-{$order['id']}: {$newStatus}";
        }
        $textContent = ob_get_clean();

        return [
            'html' => $htmlContent,
            'text' => $textContent
        ];
    }

    /**
     * Send WhatsApp notification for an order status change via Fonnte
     *
     * @param int $notificationId
     * @param string|null $overridePhone
     * @return array
     */
    public function sendWhatsAppNotification($notificationId, $overridePhone = null) {
        $log = $this->repository->getById($notificationId);
        if (!$log) {
            return ['success' => false, 'error' => 'Notification record not found'];
        }

        $orderData = $this->getOrderDataFromDatabase($log['order_id']);
        if (!$orderData || empty($orderData['order'])) {
            return ['success' => false, 'error' => 'Order data missing'];
        }

        $order = $orderData['order'];
        $customer = $orderData['customer'];
        $shipment = $orderData['shipment'];

        $phone = $overridePhone ?: (!empty($order['phone']) ? $order['phone'] : ($orderData['customer']['phone'] ?? $log['whatsapp_phone']));
        $normalizedPhone = FonnteService::normalizePhone((string)$phone);

        if (!$normalizedPhone) {
            $msg = 'Nomor WhatsApp customer tidak valid atau belum diisi.';
            $this->repository->markWhatsAppFailed($notificationId, (string)$phone, $msg);
            return ['success' => false, 'error' => $msg];
        }

        $customerName = !empty($customer['name']) ? $customer['name'] : 'Pelanggan';
        $orderCode = '#ORD-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);
        $statusLabel = $this->getStatusSubjectLabel($log['new_status']);
        $statusUpper = strtoupper($log['new_status']);
        $totalFormatted = number_format($order['total_amount'], 0, ',', '.');
        $orderUrl = ($this->config['app_url'] ?? BASEURL) . '/order/detail/' . $order['id'];

        $statusGreetings = [
            'processing' => "Pesanan Anda di *NNACOSA* sedang kami proses dan siapkan dengan teliti.",
            'shipped' => "Kabar baik! Pesanan Anda telah dikirim dan dalam perjalanan menuju alamat tujuan.",
            'completed' => "Pesanan Anda telah selesai dan berhasil diterima. Terima kasih!",
            'cancelled' => "Pesanan Anda telah dibatalkan.",
            'pending' => "Pesanan Anda telah dibuat dan sedang menunggu konfirmasi pembayaran."
        ];
        $greeting = $statusGreetings[strtolower($log['new_status'])] ?? "Status pesanan Anda telah diperbarui.";

        $msg = "*NNACOSA — NOTIFIKASI PESANAN* 💎\n\n"
             . "Halo *{$customerName}*,\n\n"
             . "{$greeting}\n\n"
             . "📦 *Nomor Pesanan:* {$orderCode}\n"
             . "📊 *Status Terbaru:* {$statusUpper} ({$statusLabel})\n"
             . "💰 *Total Pembayaran:* Rp {$totalFormatted}\n";

        if (!empty($shipment['courier'])) {
            $msg .= "🚚 *Kurir:* {$shipment['courier']}\n";
        }
        if (!empty($shipment['tracking_number'])) {
            $msg .= "🔖 *No. Resi:* `{$shipment['tracking_number']}`\n";
        }

        $msg .= "\nBuka riwayat pembelian untuk rincian pesanan:\n"
              . "{$orderUrl}\n\n"
              . "Terima kasih telah berbelanja di *NNACOSA*! ✨";

        $res = FonnteService::sendMessage($normalizedPhone, $msg);

        if (!empty($res['success'])) {
            $this->repository->markWhatsAppSent($notificationId, $normalizedPhone);
            return [
                'success' => true,
                'phone' => $normalizedPhone,
                'message' => 'Pesan WhatsApp berhasil dikirim ke ' . $normalizedPhone
            ];
        } else {
            $errMsg = $res['message'] ?? 'Gagal mengirim pesan WhatsApp';
            $this->repository->markWhatsAppFailed($notificationId, $normalizedPhone, $errMsg);
            return [
                'success' => false,
                'phone' => $normalizedPhone,
                'error' => $errMsg
            ];
        }
    }

    /**
     * Process retry batch for CLI script
     *
     * @param int $limit
     * @param int $retryDelayMinutes
     * @return array
     */
    public function processPendingAndRetries($limit = 10, $retryDelayMinutes = 5) {
        $notifications = $this->repository->getPendingOrRetryable($limit, $retryDelayMinutes);
        $results = [
            'total_found' => count($notifications),
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => []
        ];

        foreach ($notifications as $notif) {
            $id = (int)$notif['id'];
            $res = $this->processNotification($id);
            $results['processed']++;

            if (!empty($res['success'])) {
                if (!empty($res['skipped'])) {
                    $results['skipped']++;
                } else {
                    $results['sent']++;
                }
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'id' => $id,
                'order_id' => $notif['order_id'],
                'event_key' => $notif['event_key'],
                'result' => $res
            ];
        }

        return $results;
    }
}
