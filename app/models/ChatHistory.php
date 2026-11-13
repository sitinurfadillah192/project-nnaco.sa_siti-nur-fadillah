<?php
/**
 * Model ChatHistory — Management of AI Chatbot Conversations
 * 
 * Supports conversation grouping, server-side search & filtering,
 * customer vs anonymous identification, and chronological message retrieval.
 */

class ChatHistory extends Model {
    protected $table = 'ai_chat_history';

    /**
     * Save a completed or ongoing chat turn
     *
     * @param string $conversationId
     * @param int|null $userId
     * @param string $message
     * @param string $response
     * @param string|null $sessionId
     * @param string|null $ipAddress
     * @param string|null $modelId
     * @param string $status
     * @return int|false
     */
    public function saveChatTurn(
        string $conversationId,
        ?int $userId,
        string $message,
        string $response,
        ?string $sessionId = null,
        ?string $ipAddress = null,
        ?string $modelId = null,
        string $status = 'completed'
    ) {
        // Prevent duplicate spam/retry insertion within last 2 seconds
        $this->db->query("SELECT id FROM " . $this->table . " 
                          WHERE conversation_id = :conv_id 
                            AND message = :msg 
                            AND created_at >= (NOW() - INTERVAL 2 SECOND) 
                          LIMIT 1");
        $this->db->bind('conv_id', $conversationId);
        $this->db->bind('msg', $message);
        if ($this->db->single()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  (conversation_id, session_id, user_id, message, response, ip_address, model_id, status, created_at) 
                  VALUES (:conv_id, :sess_id, :user_id, :msg, :resp, :ip, :model, :status, NOW())";

        $this->db->query($query);
        $this->db->bind('conv_id', $conversationId);
        $this->db->bind('sess_id', $sessionId);
        $this->db->bind('user_id', $userId);
        $this->db->bind('msg', $message);
        $this->db->bind('resp', $response);
        $this->db->bind('ip', $ipAddress);
        $this->db->bind('model', $modelId);
        $this->db->bind('status', $status);

        if ($this->db->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Get paginated list of conversations with server-side filters
     *
     * @param int $limit
     * @param int $offset
     * @param array $filters ['keyword', 'start_date', 'end_date', 'user_type', 'status']
     * @return array
     */
    public function getConversations(int $limit = 10, int $offset = 0, array $filters = []): array {
        $whereClauses = ["1=1"];
        $params = [];

        // 1. Keyword search (customer name, email, conversation_id, or message text)
        if (!empty($filters['keyword'])) {
            $kw = '%' . trim($filters['keyword']) . '%';
            $whereClauses[] = "(
                ch.conversation_id LIKE :kw1 
                OR u.name LIKE :kw2 
                OR u.email LIKE :kw3 
                OR ch.message LIKE :kw4 
                OR ch.response LIKE :kw5
            )";
            $params['kw1'] = $kw;
            $params['kw2'] = $kw;
            $params['kw3'] = $kw;
            $params['kw4'] = $kw;
            $params['kw5'] = $kw;
        }

        // 2. Date filters
        if (!empty($filters['start_date'])) {
            $whereClauses[] = "ch.created_at >= :start_date";
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "ch.created_at <= :end_date";
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        // 3. User Type Filter (all, registered, anonymous)
        if (!empty($filters['user_type'])) {
            if ($filters['user_type'] === 'registered') {
                $whereClauses[] = "ch.user_id IS NOT NULL";
            } elseif ($filters['user_type'] === 'anonymous') {
                $whereClauses[] = "ch.user_id IS NULL";
            }
        }

        // 4. Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereClauses[] = "ch.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Group by conversation_id (fallback to id if conversation_id is null)
        $query = "SELECT 
                    COALESCE(ch.conversation_id, CONCAT('conv_legacy_', ch.id)) AS conversation_id,
                    ch.user_id,
                    u.name AS customer_name,
                    u.email AS customer_email,
                    u.phone AS customer_phone,
                    COUNT(ch.id) AS total_messages,
                    MIN(ch.created_at) AS started_at,
                    MAX(ch.created_at) AS last_message_at,
                    SUBSTRING(
                        (SELECT ch2.message FROM ai_chat_history ch2 
                         WHERE (ch2.conversation_id = ch.conversation_id OR (ch.conversation_id IS NULL AND ch2.id = ch.id))
                         ORDER BY ch2.id DESC LIMIT 1), 1, 100
                    ) AS last_user_message,
                    SUBSTRING(
                        (SELECT ch3.response FROM ai_chat_history ch3 
                         WHERE (ch3.conversation_id = ch.conversation_id OR (ch.conversation_id IS NULL AND ch3.id = ch.id))
                         ORDER BY ch3.id DESC LIMIT 1), 1, 120
                    ) AS last_bot_response,
                    ch.ip_address,
                    ch.model_id,
                    ch.status
                  FROM " . $this->table . " ch 
                  LEFT JOIN users u ON ch.user_id = u.id 
                  WHERE " . $whereSql . " 
                  GROUP BY COALESCE(ch.conversation_id, CONCAT('conv_legacy_', ch.id)) 
                  ORDER BY last_message_at DESC 
                  LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $this->db->query($query);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }

        return $this->db->resultSet();
    }

    /**
     * Count total unique conversations matching the filters for pagination
     *
     * @param array $filters
     * @return int
     */
    public function countConversations(array $filters = []): int {
        $whereClauses = ["1=1"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $kw = '%' . trim($filters['keyword']) . '%';
            $whereClauses[] = "(
                ch.conversation_id LIKE :kw1 
                OR u.name LIKE :kw2 
                OR u.email LIKE :kw3 
                OR ch.message LIKE :kw4 
                OR ch.response LIKE :kw5
            )";
            $params['kw1'] = $kw;
            $params['kw2'] = $kw;
            $params['kw3'] = $kw;
            $params['kw4'] = $kw;
            $params['kw5'] = $kw;
        }

        if (!empty($filters['start_date'])) {
            $whereClauses[] = "ch.created_at >= :start_date";
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $whereClauses[] = "ch.created_at <= :end_date";
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        if (!empty($filters['user_type'])) {
            if ($filters['user_type'] === 'registered') {
                $whereClauses[] = "ch.user_id IS NOT NULL";
            } elseif ($filters['user_type'] === 'anonymous') {
                $whereClauses[] = "ch.user_id IS NULL";
            }
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereClauses[] = "ch.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereSql = implode(' AND ', $whereClauses);

        $query = "SELECT COUNT(DISTINCT COALESCE(ch.conversation_id, CONCAT('conv_legacy_', ch.id))) AS total 
                  FROM " . $this->table . " ch 
                  LEFT JOIN users u ON ch.user_id = u.id 
                  WHERE " . $whereSql;

        $this->db->query($query);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }

        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Get metadata and user info for a specific conversation
     *
     * @param string $conversationId
     * @return array|null
     */
    public function getConversationMetadata(string $conversationId): ?array {
        $isLegacy = str_starts_with($conversationId, 'conv_legacy_');
        if ($isLegacy) {
            $legacyId = (int)str_replace('conv_legacy_', '', $conversationId);
            $query = "SELECT 
                        ch.id,
                        CONCAT('conv_legacy_', ch.id) AS conversation_id,
                        ch.user_id,
                        u.name AS customer_name,
                        u.email AS customer_email,
                        u.phone AS customer_phone,
                        1 AS total_messages,
                        ch.created_at AS started_at,
                        ch.created_at AS last_message_at,
                        ch.ip_address,
                        ch.model_id,
                        ch.status
                      FROM " . $this->table . " ch 
                      LEFT JOIN users u ON ch.user_id = u.id 
                      WHERE ch.id = :legacy_id LIMIT 1";
            $this->db->query($query);
            $this->db->bind('legacy_id', $legacyId);
        } else {
            $query = "SELECT 
                        ch.conversation_id,
                        ch.user_id,
                        u.name AS customer_name,
                        u.email AS customer_email,
                        u.phone AS customer_phone,
                        COUNT(ch.id) AS total_messages,
                        MIN(ch.created_at) AS started_at,
                        MAX(ch.created_at) AS last_message_at,
                        ch.ip_address,
                        ch.model_id,
                        ch.status
                      FROM " . $this->table . " ch 
                      LEFT JOIN users u ON ch.user_id = u.id 
                      WHERE ch.conversation_id = :conv_id 
                      GROUP BY ch.conversation_id LIMIT 1";
            $this->db->query($query);
            $this->db->bind('conv_id', $conversationId);
        }

        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Get all message turns in a conversation in chronological order
     *
     * @param string $conversationId
     * @return array
     */
    public function getMessagesByConversationId(string $conversationId): array {
        $isLegacy = str_starts_with($conversationId, 'conv_legacy_');
        if ($isLegacy) {
            $legacyId = (int)str_replace('conv_legacy_', '', $conversationId);
            $this->db->query("SELECT * FROM " . $this->table . " WHERE id = :legacy_id ORDER BY id ASC");
            $this->db->bind('legacy_id', $legacyId);
        } else {
            $this->db->query("SELECT * FROM " . $this->table . " WHERE conversation_id = :conv_id ORDER BY id ASC");
            $this->db->bind('conv_id', $conversationId);
        }

        return $this->db->resultSet();
    }

    /**
     * Get overall statistics for Chatbot conversation dashboard
     *
     * @return array
     */
    public function getStats(): array {
        $stats = [
            'total_conversations' => 0,
            'total_messages' => 0,
            'registered_conversations' => 0,
            'anonymous_conversations' => 0,
            'conversations_today' => 0
        ];

        // Total messages count
        $this->db->query("SELECT COUNT(*) AS total FROM " . $this->table);
        $stats['total_messages'] = (int)($this->db->single()['total'] ?? 0);

        // Total distinct conversations
        $this->db->query("SELECT COUNT(DISTINCT COALESCE(conversation_id, CONCAT('conv_legacy_', id))) AS total FROM " . $this->table);
        $stats['total_conversations'] = (int)($this->db->single()['total'] ?? 0);

        // Registered user conversations
        $this->db->query("SELECT COUNT(DISTINCT COALESCE(conversation_id, CONCAT('conv_legacy_', id))) AS total 
                          FROM " . $this->table . " WHERE user_id IS NOT NULL");
        $stats['registered_conversations'] = (int)($this->db->single()['total'] ?? 0);

        // Anonymous visitor conversations
        $stats['anonymous_conversations'] = max(0, $stats['total_conversations'] - $stats['registered_conversations']);

        // Today's conversations
        $this->db->query("SELECT COUNT(DISTINCT COALESCE(conversation_id, CONCAT('conv_legacy_', id))) AS total 
                          FROM " . $this->table . " WHERE created_at >= CURDATE()");
        $stats['conversations_today'] = (int)($this->db->single()['total'] ?? 0);

        return $stats;
    }

    /**
     * Log admin access to conversation detail
     *
     * @param int|null $adminId
     * @param string $conversationId
     * @param string $action
     * @param string $result
     * @param string|null $ipAddress
     * @return bool
     */
    public function logAccess(?int $adminId, string $conversationId, string $action = 'view_conversation', string $result = 'success', ?string $ipAddress = null): bool {
        try {
            $this->db->query("INSERT INTO admin_audit_logs 
                              (admin_id, action, target_type, target_id, details, ip_address, created_at) 
                              VALUES (:admin_id, :action, 'chat_conversation', :target_id, :details, :ip, NOW())");
            $this->db->bind('admin_id', $adminId);
            $this->db->bind('action', $action);
            $this->db->bind('target_id', $conversationId);
            $this->db->bind('details', $result);
            $this->db->bind('ip', $ipAddress);
            return $this->db->execute();
        } catch (Throwable $e) {
            return false;
        }
    }
}
