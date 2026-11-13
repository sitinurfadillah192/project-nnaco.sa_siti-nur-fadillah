<?php
/**
 * Repository for Order Status Notifications and Database Idempotency
 * NNACOSA — Automated Order Status Email Notifications
 */

require_once __DIR__ . '/../core/Database.php';

class OrderNotificationRepository {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: new Database();
    }

    /**
     * Atomically transitions order status and records a notification event if status changed.
     * Uses row-level lock (FOR UPDATE) to prevent race conditions.
     *
     * @param int $orderId
     * @param string $newStatus
     * @param string|null $customEventKey Optional idempotency key
     * @return array Result metadata
     */
    public function transitionOrderStatus($orderId, $newStatus, $customEventKey = null) {
        $newStatus = strtolower(trim((string)$newStatus));

        try {
            $this->db->beginTransaction();

            // 1. Lock and fetch current order row
            $this->db->query("SELECT id, user_id, phone, status FROM orders WHERE id = :id FOR UPDATE");
            $this->db->bind('id', $orderId, PDO::PARAM_INT);
            $order = $this->db->single();

            if (!$order) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'changed' => false,
                    'error' => 'Order not found'
                ];
            }

            $oldStatus = strtolower(trim((string)$order['status']));

            // 2. Check if status actually changed
            if ($oldStatus === $newStatus) {
                $this->db->rollBack();
                return [
                    'success' => true,
                    'changed' => false,
                    'reason' => 'Status unchanged',
                    'order_id' => $orderId,
                    'status' => $newStatus
                ];
            }

            // 3. Fetch customer email, name, and phone
            $this->db->query("SELECT name, email, phone FROM users WHERE id = :user_id");
            $this->db->bind('user_id', $order['user_id'], PDO::PARAM_INT);
            $user = $this->db->single();

            $recipientEmail = $user['email'] ?? '';
            $recipientName = $user['name'] ?? 'Pelanggan';
            $recipientPhone = !empty($order['phone']) ? $order['phone'] : ($user['phone'] ?? '');

            // 4. Generate stable unique event key if not provided
            // Format: ord_{orderId}_{oldStatus}_to_{newStatus}_{microtime}_{randomHex}
            $eventKey = $customEventKey ?: sprintf(
                'ord_%d_%s_to_%s_%d_%s',
                $orderId,
                $oldStatus,
                $newStatus,
                time(),
                bin2hex(random_bytes(6))
            );

            // 5. Update order status
            $this->db->query("UPDATE orders SET status = :status WHERE id = :id");
            $this->db->bind('status', $newStatus);
            $this->db->bind('id', $orderId, PDO::PARAM_INT);
            $this->db->execute();

            // 6. Insert notification log record atomically with UNIQUE event_key
            $this->db->query("INSERT INTO order_notification_logs 
                (order_id, old_status, new_status, recipient_email, recipient_name, whatsapp_phone, event_key, notification_status, attempt_count, max_attempts) 
                VALUES 
                (:order_id, :old_status, :new_status, :recipient_email, :recipient_name, :whatsapp_phone, :event_key, 'PENDING', 0, 5)");
            $this->db->bind('order_id', $orderId, PDO::PARAM_INT);
            $this->db->bind('old_status', $oldStatus);
            $this->db->bind('new_status', $newStatus);
            $this->db->bind('recipient_email', $recipientEmail);
            $this->db->bind('recipient_name', $recipientName);
            $this->db->bind('whatsapp_phone', $recipientPhone);
            $this->db->bind('event_key', $eventKey);
            $this->db->execute();

            $notificationId = (int)$this->db->lastInsertId();

            // 7. Commit database transaction
            $this->db->commit();

            return [
                'success' => true,
                'changed' => true,
                'notification_id' => $notificationId,
                'event_key' => $eventKey,
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'changed' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Atomically claims a notification for processing.
     * Prevents race condition between concurrent workers.
     *
     * @param int $notificationId
     * @return bool True if successfully claimed, false otherwise
     */
    public function claimForSending($notificationId) {
        $this->db->query("UPDATE order_notification_logs 
            SET notification_status = 'SENDING', updated_at = NOW() 
            WHERE id = :id AND notification_status IN ('PENDING', 'FAILED') AND attempt_count < max_attempts");
        $this->db->bind('id', $notificationId, PDO::PARAM_INT);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Mark notification as successfully sent
     *
     * @param int $notificationId
     * @return bool
     */
    public function markSent($notificationId) {
        $this->db->query("UPDATE order_notification_logs 
            SET notification_status = 'SENT', sent_at = NOW(), last_error = NULL 
            WHERE id = :id");
        $this->db->bind('id', $notificationId, PDO::PARAM_INT);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Mark notification as failed or requiring review
     *
     * @param int $notificationId
     * @param string $errorMessage
     * @param bool $isAmbiguous If timeout occurred after dispatch, mark as REVIEW_REQUIRED
     * @return bool
     */
    public function markFailed($notificationId, $errorMessage, $isAmbiguous = false) {
        $sanitizedError = $this->sanitizeErrorMessage($errorMessage);
        $targetStatus = $isAmbiguous ? 'REVIEW_REQUIRED' : 'FAILED';

        $this->db->query("UPDATE order_notification_logs 
            SET notification_status = :status, 
                attempt_count = attempt_count + 1, 
                last_error = :last_error, 
                updated_at = NOW() 
            WHERE id = :id");
        $this->db->bind('status', $targetStatus);
        $this->db->bind('last_error', $sanitizedError);
        $this->db->bind('id', $notificationId, PDO::PARAM_INT);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Mark WhatsApp notification as successfully sent
     *
     * @param int $notificationId
     * @param string $phone
     * @return bool
     */
    public function markWhatsAppSent($notificationId, $phone) {
        $this->db->query("UPDATE order_notification_logs 
            SET whatsapp_status = 'SENT', whatsapp_phone = :phone, whatsapp_sent_at = NOW(), whatsapp_error = NULL 
            WHERE id = :id");
        $this->db->bind('phone', $phone);
        $this->db->bind('id', $notificationId, PDO::PARAM_INT);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Mark WhatsApp notification as failed
     *
     * @param int $notificationId
     * @param string $phone
     * @param string $errorMessage
     * @return bool
     */
    public function markWhatsAppFailed($notificationId, $phone, $errorMessage) {
        $sanitized = $this->sanitizeErrorMessage($errorMessage);
        $this->db->query("UPDATE order_notification_logs 
            SET whatsapp_status = 'FAILED', whatsapp_phone = :phone, whatsapp_error = :err 
            WHERE id = :id");
        $this->db->bind('phone', $phone);
        $this->db->bind('err', $sanitized);
        $this->db->bind('id', $notificationId, PDO::PARAM_INT);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Fetch single notification log by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM order_notification_logs WHERE id = :id");
        $this->db->bind('id', $id, PDO::PARAM_INT);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Fetch single notification log by unique event_key
     *
     * @param string $eventKey
     * @return array|null
     */
    public function getByEventKey($eventKey) {
        $this->db->query("SELECT * FROM order_notification_logs WHERE event_key = :event_key");
        $this->db->bind('event_key', $eventKey);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Fetch pending or retryable failed notifications for worker / CLI retry script
     *
     * @param int $limit
     * @param int $retryDelayMinutes
     * @return array
     */
    public function getPendingOrRetryable($limit = 10, $retryDelayMinutes = 5) {
        $this->db->query("SELECT * FROM order_notification_logs 
            WHERE notification_status = 'PENDING' 
               OR (notification_status = 'FAILED' 
                   AND attempt_count < max_attempts 
                   AND updated_at <= DATE_SUB(NOW(), INTERVAL :delay MINUTE))
            ORDER BY created_at ASC 
            LIMIT :limit");
        $this->db->bind('delay', $retryDelayMinutes, PDO::PARAM_INT);
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Fetch notification logs for a specific order (for Admin UI)
     *
     * @param int $orderId
     * @return array
     */
    public function getLogsByOrderId($orderId) {
        $this->db->query("SELECT * FROM order_notification_logs WHERE order_id = :order_id ORDER BY id DESC");
        $this->db->bind('order_id', $orderId, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Sanitize error message to prevent leaking credentials or stack traces
     *
     * @param string $message
     * @return string
     */
    public function sanitizeErrorMessage($message) {
        if (empty($message)) {
            return 'Unknown error occurred.';
        }

        // Mask email passwords, authorization tokens, or secret keys if any
        $cleaned = preg_replace('/password[:=]\s*[^\s;,]+/i', 'password=***', $message);
        $cleaned = preg_replace('/(bearer|token|key)[:=]\s*[^\s;,]+/i', '$1=***', $cleaned);

        // Limit length
        if (strlen($cleaned) > 500) {
            $cleaned = substr($cleaned, 0, 497) . '...';
        }

        return $cleaned;
    }
}
