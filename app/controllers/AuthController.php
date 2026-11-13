<?php
class AuthController extends Controller {

    public function index() {
        $this->login();
    }

    public function login() {
        AuthMiddleware::isGuest();
        $data = ['title' => 'Login - nnaco_sa', 'error' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $data['error'] = 'Invalid CSRF Token.';
            } else {
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];

                $user = $this->model('User')->loginUser($email, $password);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    // Role-based routing
                    if ($user['role'] === 'customer') {
                        header('Location: ' . BASEURL . '/home');
                    } else {
                        header('Location: ' . BASEURL . '/dashboard'); 
                    }
                    exit;
                } else {
                    $data['error'] = 'Email atau Password salah.';
                }
            }
        }

        $this->view('templates/header', $data);
        $this->view('auth/login', $data);
        $this->view('templates/footer');
    }

    public function register() {
        AuthMiddleware::isGuest();
        $data = ['title' => 'Register - nnaco_sa', 'error' => '', 'success' => '', 'old' => []];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $data['error'] = 'Invalid CSRF Token.';
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
                $phone = trim($_POST['phone'] ?? '');
                $otp = trim($_POST['otp'] ?? '');
                $password = $_POST['password'] ?? '';
                $password_confirm = $_POST['password_confirm'] ?? '';

                $data['old'] = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ];

                if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                    $data['error'] = 'Semua field wajib diisi.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['error'] = 'Format alamat email tidak valid.';
                } elseif (strlen($password) < 6) {
                    $data['error'] = 'Password minimal harus 6 karakter.';
                } elseif ($password !== $password_confirm) {
                    $data['error'] = 'Konfirmasi password tidak cocok.';
                } elseif ($this->model('User')->getUserByEmail($email)) {
                    $data['error'] = 'Alamat email sudah terdaftar. Silakan gunakan email lain atau login.';
                } else {
                    $normalizedPhone = FonnteService::normalizePhone($phone);
                    if (!$normalizedPhone) {
                        $data['error'] = 'Format nomor WhatsApp tidak valid (contoh: 08123456789 atau 628123456789).';
                    } elseif ($this->model('User')->getUserByPhone($normalizedPhone)) {
                        $data['error'] = 'Nomor WhatsApp sudah terdaftar. Silakan gunakan nomor lain atau login.';
                    } elseif (empty($otp)) {
                        $data['error'] = 'Silakan masukkan kode OTP yang telah dikirim ke nomor WhatsApp Anda.';
                    } else {
                        // Verifikasi kode OTP WhatsApp via OtpService
                        $verifyResult = OtpService::verifyOtp($normalizedPhone, $otp);
                        if (!$verifyResult['success']) {
                            $data['error'] = $verifyResult['message'] ?? 'Kode OTP salah atau telah kedaluwarsa.';
                        } else {
                            $userData = [
                                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                                'email' => $email,
                                'phone' => $normalizedPhone,
                                'is_phone_verified' => 1,
                                'password' => $password
                            ];

                            if ($this->model('User')->registerUser($userData) > 0) {
                                Flasher::setFlash('Registrasi Berhasil!', 'Akun Anda berhasil dibuat dengan nomor WhatsApp terverifikasi. Silakan login.', 'success');
                                header('Location: ' . BASEURL . '/auth/login');
                                exit;
                            } else {
                                $data['error'] = 'Gagal melakukan registrasi ke database. Silakan coba lagi.';
                            }
                        }
                    }
                }
            }
        }

        $this->view('templates/header', $data);
        $this->view('auth/register', $data);
        $this->view('templates/footer');
    }

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
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    // ==========================================
    // WHATSAPP OTP AUTHENTICATION
    // ==========================================

    /**
     * Halaman antarmuka login & verifikasi OTP WhatsApp
     */
    public function whatsapp() {
        AuthMiddleware::isGuest();
        $data = ['title' => 'Masuk dengan WhatsApp - nnaco_sa'];
        $this->view('templates/header', $data);
        $this->view('auth/whatsapp', $data);
        $this->view('templates/footer');
    }

    /**
     * Endpoint kirim OTP via WhatsApp
     */
    public function send_otp() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
            exit;
        }

        $input = $this->getRequestPayload();
        $phone = $input['phone'] ?? '';

        if (empty($phone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp wajib diisi.']);
            exit;
        }

        $normalizedPhone = FonnteService::normalizePhone($phone);
        if (!$normalizedPhone) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 08123456789 atau 628123456789.']);
            exit;
        }

        if (!empty($input['check_registered'])) {
            $existing = $this->model('User')->getUserByPhone($normalizedPhone);
            if ($existing) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp ini sudah terdaftar. Silakan login atau gunakan nomor lain.']);
                exit;
            }
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $result = OtpService::requestOtp($normalizedPhone, $ip);

        if (!$result['success']) {
            http_response_code(400);
        }
        echo json_encode($result);
        exit;
    }

    /**
     * Endpoint verifikasi OTP WhatsApp & pembuatan sesi
     */
    public function verify_otp() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
            exit;
        }

        $input = $this->getRequestPayload();
        $phone = $input['phone'] ?? '';
        $otp = $input['otp'] ?? '';

        if (empty($phone) || empty($otp)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp dan kode OTP wajib diisi.']);
            exit;
        }

        $verifyResult = OtpService::verifyOtp($phone, $otp);

        if (!$verifyResult['success']) {
            http_response_code(400);
            echo json_encode($verifyResult);
            exit;
        }

        $normalizedPhone = $verifyResult['phone'];
        $userModel = $this->model('User');
        $user = $userModel->getUserByPhone($normalizedPhone);

        // Auto-register jika nomor belum terdaftar
        if (!$user) {
            $user = $userModel->registerUserWithPhone($normalizedPhone);
        }

        if (!$user) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal mengautentikasi atau mendaftarkan akun.']);
            exit;
        }

        // Buat sesi autentikasi yang aman
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'customer';

        $redirect = ($user['role'] === 'customer') ? BASEURL . '/home' : BASEURL . '/dashboard';

        echo json_encode([
            'success' => true,
            'message' => 'Verifikasi berhasil! Mengalihkan ke halaman utama...',
            'redirect' => $redirect,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'phone' => FonnteService::maskPhone($normalizedPhone),
                'role' => $user['role'] ?? 'customer'
            ]
        ]);
        exit;
    }

    private function getRequestPayload(): array {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                return $json;
            }
        }
        return $_POST;
    }

    public function forgot_password() {
        AuthMiddleware::isGuest();
        $data = ['title' => 'Lupa Password', 'error' => '', 'success' => '', 'reset_link' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $data['error'] = 'Invalid CSRF Token.';
            } else {
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $user = $this->model('User')->getUserByEmail($email);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour validity
                    
                    $this->model('User')->setResetToken($email, $token, $expires);
                    
                    // Display link for local testing (simulating email)
                    $data['success'] = 'Email reset password telah dikirim!';
                    $data['reset_link'] = BASEURL . '/auth/reset_password/' . $token;
                } else {
                    $data['error'] = 'Email tidak ditemukan di sistem.';
                }
            }
        }

        $this->view('templates/header', $data);
        $this->view('auth/forgot_password', $data);
        $this->view('templates/footer');
    }

    public function reset_password($token = '') {
        AuthMiddleware::isGuest();
        if (empty($token)) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $user = $this->model('User')->getUserByResetToken($token);

        if (!$user) {
            die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Token tidak valid atau sudah kedaluwarsa.</h2><a href='".BASEURL."/auth/forgot_password'>Coba Lagi</a></div>");
        }

        $data = ['title' => 'Reset Password', 'error' => '', 'success' => '', 'token' => $token];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $data['error'] = 'Invalid CSRF Token.';
            } else {
                $password = $_POST['password'];
                $password_confirm = $_POST['password_confirm'];

                if ($password !== $password_confirm) {
                    $data['error'] = 'Konfirmasi password tidak sama.';
                } else {
                    $this->model('User')->updatePassword($user['id'], $password);
                    $data['success'] = 'Password berhasil direset. Silakan login dengan password baru.';
                }
            }
        }

        $this->view('templates/header', $data);
        $this->view('auth/reset_password', $data);
        $this->view('templates/footer');
    }
}
