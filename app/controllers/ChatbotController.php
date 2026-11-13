<?php
/**
 * ChatbotController — Admin Chatbot Conversation History Management
 * 
 * Strict RBAC protected for admin and owner roles.
 * Provides server-side search, filtering, pagination, and chronological chat inspection.
 */
class ChatbotController extends Controller {

    public function __construct() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'owner']);
    }

    /**
     * Display conversation list with server-side filters & pagination
     */
    public function index() {
        $chatModel = $this->model('ChatHistory');

        // Capture search & filter inputs
        $keyword   = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $endDate   = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
        $userType  = isset($_GET['user_type']) ? trim($_GET['user_type']) : 'all';
        $status    = isset($_GET['status']) ? trim($_GET['status']) : 'all';
        $limit     = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }

        $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword'    => $keyword,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'user_type'  => $userType,
            'status'     => $status
        ];

        $conversations = $chatModel->getConversations($limit, $offset, $filters);
        $totalRecords  = $chatModel->countConversations($filters);
        $totalPages    = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
        $stats         = $chatModel->getStats();

        $data = [
            'title'         => 'Riwayat Percakapan Chatbot',
            'conversations' => $conversations,
            'totalRecords'  => $totalRecords,
            'totalPages'    => $totalPages,
            'currentPage'   => $page,
            'limit'         => $limit,
            'filters'       => $filters,
            'stats'         => $stats
        ];

        $this->view('templates/header', $data);
        $this->view('chatbot/index', $data);
        $this->view('templates/footer');
    }

    /**
     * Display full conversation thread chronologically
     *
     * @param string|null $conversationId
     */
    public function detail($conversationId = null) {
        if (!$conversationId && isset($_GET['id'])) {
            $conversationId = trim($_GET['id']);
        }

        if (empty($conversationId)) {
            Flasher::setFlash('ID Percakapan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/chatbot');
            exit;
        }

        $chatModel    = $this->model('ChatHistory');
        $conversation = $chatModel->getConversationMetadata($conversationId);

        if (!$conversation) {
            Flasher::setFlash('Riwayat percakapan tidak ditemukan atau sudah tidak tersedia.', 'warning');
            header('Location: ' . BASEURL . '/chatbot');
            exit;
        }

        $messages = $chatModel->getMessagesByConversationId($conversationId);

        // Record audit access log
        $adminId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $chatModel->logAccess($adminId, $conversationId, 'view_conversation', 'success', $ipAddress);

        $data = [
            'title'          => 'Detail Percakapan Chatbot',
            'conversation'   => $conversation,
            'messages'       => $messages,
            'conversationId' => $conversationId
        ];

        $this->view('templates/header', $data);
        $this->view('chatbot/detail', $data);
        $this->view('templates/footer');
    }
}
