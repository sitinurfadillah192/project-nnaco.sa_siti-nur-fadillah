<?php
class AdminorderController extends Controller {
    public function __construct() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'operator', 'owner']);
    }

    public function index() {
        $data['title'] = 'Manajemen Pesanan';
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        // Query all orders with pagination and filtering
        $db = new Database();
        
        $query = "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1 ";
        if (!empty($status)) {
            $query .= "AND o.status = :status ";
        }
        $query .= "ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
        
        $db->query($query);
        if (!empty($status)) {
            $db->bind('status', $status);
        }
        $db->bind('limit', $limit, PDO::PARAM_INT);
        $db->bind('offset', $offset, PDO::PARAM_INT);
        
        $data['orders'] = $db->resultSet();
        
        // Calculate Total Income
        $db->query("SELECT SUM(total_amount) as total_income FROM orders WHERE status IN ('completed', 'shipped')");
        $data['total_income'] = $db->single()['total_income'] ?? 0;
        
        // Count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM orders WHERE 1=1 ";
        if (!empty($status)) {
            $countQuery .= "AND status = :status ";
        }
        $db->query($countQuery);
        if (!empty($status)) {
            $db->bind('status', $status);
        }
        
        $total = $db->single()['total'];
        $data['total_pages'] = ceil($total / $limit);
        $data['current_page'] = $page;

        $this->view('templates/header', $data);
        $this->view('adminorder/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        $data['title'] = 'Detail Pesanan & Pembayaran';
        $result = $this->model('Order')->getOrderDetails($id);
        
        if (!$result || empty($result['order'])) {
            header('Location: ' . BASEURL . '/adminorder');
            exit;
        }
        
        $data['order'] = $result['order'];
        $data['items'] = $result['items'];
        $data['payment'] = $result['payment'];

        // Get customer email and phone
        $db = new Database();
        $db->query("SELECT email, name, phone FROM users WHERE id = :id");
        $db->bind('id', $data['order']['user_id']);
        $data['customer'] = $db->single();

        // Get notification history for this order
        $notifRepo = new OrderNotificationRepository($db);
        $data['notifications'] = $notifRepo->getLogsByOrderId($id);

        $this->view('templates/header', $data);
        $this->view('adminorder/detail', $data);
        $this->view('templates/footer');
    }

    public function update_status() {
        RoleMiddleware::check(['admin', 'operator']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Verification if token provided
            if (isset($_POST['csrf_token']) && !Security::verifyCsrfToken($_POST['csrf_token'])) {
                Flasher::setFlash('Gagal memperbarui status', 'Token keamanan (CSRF) tidak valid.', 'danger');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
                exit;
            }

            $order_id = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
            $status = strtolower(trim((string)($_POST['status'] ?? '')));

            $validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
            if (!$order_id || !in_array($status, $validStatuses, true)) {
                Flasher::setFlash('Status tidak valid', 'Pilihan status atau pesanan tidak sah.', 'warning');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
                exit;
            }

            $service = new OrderNotificationService();
            $result = $service->notifyStatusChange($order_id, $status);

            if (!$result['success']) {
                Flasher::setFlash('Gagal memperbarui status', $result['error'] ?? 'Terjadi kesalahan pada database.', 'danger');
            } elseif (!$result['changed']) {
                Flasher::setFlash('Info', 'Status pesanan tidak berubah.', 'info');
            } else {
                $wa = $result['whatsapp'] ?? [];
                if (!empty($wa['success'])) {
                    Flasher::setFlash('Berhasil', 'Status pesanan berhasil diperbarui & notifikasi terkirim ke WhatsApp customer (' . $wa['phone'] . ').', 'success');
                } elseif (!empty($wa['error'])) {
                    Flasher::setFlash('Status Diperbarui', 'Status pesanan berhasil diubah, namun notifikasi WhatsApp: ' . $wa['error'], 'warning');
                } else {
                    Flasher::setFlash('Berhasil', 'Status pesanan berhasil diperbarui.', 'success');
                }
            }

            $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder'));
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public function retry_notification() {
        RoleMiddleware::check(['admin', 'operator']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token']) && !Security::verifyCsrfToken($_POST['csrf_token'])) {
                Flasher::setFlash('Akses Ditolak', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
                exit;
            }

            $notification_id = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$notification_id) {
                Flasher::setFlash('Gagal', 'ID notifikasi tidak valid.', 'danger');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
                exit;
            }

            $service = new OrderNotificationService();
            $result = $service->processNotification($notification_id);

            if (!empty($result['success'])) {
                if (!empty($result['skipped'])) {
                    Flasher::setFlash('Info', $result['message'], 'info');
                } else {
                    Flasher::setFlash('Berhasil', 'Email notifikasi berhasil dikirim!', 'success');
                }
            } else {
                Flasher::setFlash('Gagal Mengirim', $result['error'] ?? ($result['message'] ?? 'Pengiriman email gagal.'), 'danger');
            }

            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
            exit;
        }
    }

    public function preview_email($notifId) {
        RoleMiddleware::check(['admin', 'operator', 'owner']);
        $notifId = (int)$notifId;
        $db = new Database();
        $repo = new OrderNotificationRepository($db);
        $log = $repo->getById($notifId);
        if (!$log) {
            die("Notifikasi tidak ditemukan.");
        }

        $service = new OrderNotificationService($repo, $db);
        $orderData = $service->getOrderDataFromDatabase($log['order_id']);
        if (!$orderData) {
            die("Data pesanan tidak ditemukan.");
        }

        $rendered = $service->renderTemplates([
            'recipientName' => $orderData['customer']['name'] ?? $log['recipient_name'],
            'recipientEmail' => $orderData['customer']['email'] ?? $log['recipient_email'],
            'order' => $orderData['order'],
            'items' => $orderData['items'],
            'shipment' => $orderData['shipment'],
            'oldStatus' => $log['old_status'],
            'newStatus' => $log['new_status'],
            'orderUrl' => BASEURL . '/order/detail/' . $log['order_id'],
            'brandName' => 'NNACOSA'
        ]);

        header('Content-Type: text/html; charset=UTF-8');
        echo $rendered['html'];
        exit;
    }

    public function send_whatsapp() {
        RoleMiddleware::check(['admin', 'operator']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token']) && !Security::verifyCsrfToken($_POST['csrf_token'])) {
                Flasher::setFlash('Akses Ditolak', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
                exit;
            }

            $notification_id = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
            $phone = trim((string)($_POST['phone'] ?? ''));

            if (!$notification_id) {
                Flasher::setFlash('Gagal', 'ID notifikasi tidak valid.', 'danger');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
                exit;
            }

            $service = new OrderNotificationService();
            $result = $service->sendWhatsAppNotification($notification_id, $phone ?: null);

            if (!empty($result['success'])) {
                Flasher::setFlash('Berhasil', 'Pesan notifikasi status pesanan berhasil dikirim ke WhatsApp ' . $result['phone'] . '!', 'success');
            } else {
                Flasher::setFlash('Gagal Kirim WA', $result['error'] ?? 'Gagal mengirim pesan WhatsApp.', 'danger');
            }

            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASEURL . '/adminorder')));
            exit;
        }
    }
}
