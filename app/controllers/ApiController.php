<?php
class ApiController extends Controller {

    public function __construct() {
        header('Content-Type: application/json; charset=UTF-8');
    }

    /**
     * Dispatcher for /api/auth/... routes
     * Supports:
     * - POST /api/auth/whatsapp/send-otp
     * - POST /api/auth/whatsapp/verify-otp
     * - POST /api/auth/logout
     */
    public function auth($service = '', $action = '') {
        $service = strtolower($service);
        $action = strtolower($action);

        if ($service === 'whatsapp' && in_array($action, ['send-otp', 'send_otp'])) {
            $this->sendOtp();
            return;
        }

        if ($service === 'whatsapp' && in_array($action, ['verify-otp', 'verify_otp'])) {
            $this->verifyOtp();
            return;
        }

        if ($service === 'logout' || $action === 'logout') {
            $this->logout();
            return;
        }

        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint API tidak ditemukan.'
        ]);
    }

    /**
     * POST /api/auth/whatsapp/send-otp
     */
    public function sendOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method Not Allowed. Gunakan POST.'
            ]);
            return;
        }

        $input = $this->getRequestData();
        $phone = $input['phone'] ?? '';

        if (empty($phone)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nomor WhatsApp (phone) wajib diisi.'
            ]);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $result = OtpService::requestOtp($phone, $ip);

        if (!$result['success']) {
            http_response_code(400);
        } else {
            http_response_code(200);
        }

        echo json_encode($result);
    }

    /**
     * POST /api/auth/whatsapp/verify-otp
     */
    public function verifyOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method Not Allowed. Gunakan POST.'
            ]);
            return;
        }

        $input = $this->getRequestData();
        $phone = $input['phone'] ?? '';
        $otp = $input['otp'] ?? '';

        if (empty($phone) || empty($otp)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nomor WhatsApp (phone) dan kode OTP wajib diisi.'
            ]);
            return;
        }

        $verifyResult = OtpService::verifyOtp($phone, $otp);

        if (!$verifyResult['success']) {
            http_response_code(400);
            echo json_encode($verifyResult);
            return;
        }

        $normalizedPhone = $verifyResult['phone'];
        $userModel = $this->model('User');
        $user = $userModel->getUserByPhone($normalizedPhone);

        // Auto-register new customer account if not yet registered
        if (!$user) {
            $user = $userModel->registerUserWithPhone($normalizedPhone);
        }

        if (!$user) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal membuat sesi atau mendaftarkan akun pengguna.'
            ]);
            return;
        }

        // Establish secure session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'customer';

        $redirectUrl = ($user['role'] === 'customer') ? BASEURL . '/home' : BASEURL . '/dashboard';

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Verifikasi berhasil. Anda telah masuk.',
            'redirect' => $redirectUrl,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'phone' => FonnteService::maskPhone($normalizedPhone),
                'role' => $user['role'] ?? 'customer'
            ]
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Sesi autentikasi telah diakhiri (logout berhasil).'
        ]);
    }

    /**
     * Helper to read JSON or Form POST data
     */
    private function getRequestData(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                return $json;
            }
        }
        return $_POST;
    }
}
