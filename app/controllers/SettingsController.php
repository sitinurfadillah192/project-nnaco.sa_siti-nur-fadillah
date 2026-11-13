<?php
/**
 * SettingsController — System Settings Administration
 * 
 * Manages AI Providers (OpenAI, OpenAI-Compatible) and OTP Authentication (WhatsApp Fonnte, Email SMTP)
 * with strict RBAC (admin, owner), CSRF protection, AES-256-GCM secret management,
 * and live connection testing.
 */

class SettingsController extends Controller {

    public function __construct() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'owner']);
    }

    /**
     * Default redirect to AI Configuration
     */
    public function index() {
        header('Location: ' . BASEURL . '/settings/ai');
        exit;
    }

    // =========================================================================
    // 1. AI CONFIGURATION
    // =========================================================================

    /**
     * View AI Provider Configurations
     */
    public function ai() {
        $data['title'] = 'Konfigurasi Model & Provider AI — Pengaturan Sistem';
        $data['active_tab'] = 'ai';

        $aiModel = $this->model('AiProviderConfig');
        $data['configs'] = $aiModel->getAll();
        $data['active_config'] = $aiModel->getActive();
        $data['csrf_token'] = Security::generateCsrfToken();

        $this->view('templates/header', $data);
        $this->view('settings/ai', $data);
        $this->view('templates/footer');
    }

    /**
     * Save AI Provider Configuration (Create or Update)
     */
    public function ai_save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('Keamanan!', 'Token CSRF tidak valid. Silakan coba lagi.', 'danger');
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        $id = !empty($_POST['config_id']) ? (int)$_POST['config_id'] : null;
        $configName = trim($_POST['config_name'] ?? '');
        $provider = trim($_POST['provider'] ?? 'openai_compatible');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $modelId = trim($_POST['model_id'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $timeout = max(5, min((int)($_POST['timeout'] ?? 45), 120));
        $maxTokens = max(50, min((int)($_POST['max_tokens'] ?? 500), 4096));
        $temperature = max(0.0, min((float)($_POST['temperature'] ?? 0.5), 2.0));
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        // Validation
        if (empty($configName) || empty($baseUrl) || empty($modelId)) {
            Flasher::setFlash('Validasi Gagal!', 'Nama konfigurasi, Base URL, dan Model ID wajib diisi.', 'danger');
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        // SSRF check on base URL
        $ssrf = Security::validateOutboundUrl($baseUrl);
        if (!$ssrf['valid']) {
            Flasher::setFlash('URL Ditolak!', 'Validasi URL gagal: ' . $ssrf['error'], 'danger');
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        $aiModel = $this->model('AiProviderConfig');
        $params = [
            'timeout' => $timeout,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ];

        $payload = [
            'config_name' => $configName,
            'provider' => $provider,
            'base_url' => $baseUrl,
            'model_id' => $modelId,
            'parameters' => $params,
            'is_active' => $isActive
        ];

        if (!empty($apiKey)) {
            $payload['api_key'] = $apiKey;
        }

        if ($id) {
            // Update
            $res = $aiModel->update($id, $payload);
            if ($res) {
                if ($isActive) {
                    $aiModel->activate($id);
                }
                Flasher::setFlash('Berhasil!', "Konfigurasi AI '{$configName}' berhasil diperbarui.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat memperbarui konfigurasi AI.', 'danger');
            }
        } else {
            // Create
            if (empty($apiKey)) {
                Flasher::setFlash('Peringatan!', 'API Key diperlukan untuk membuat konfigurasi AI baru.', 'warning');
                header('Location: ' . BASEURL . '/settings/ai');
                exit;
            }
            $payload['api_key'] = $apiKey;
            $newId = $aiModel->create($payload);
            if ($newId) {
                Flasher::setFlash('Berhasil!', "Konfigurasi AI baru '{$configName}' berhasil ditambahkan.", 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat menyimpan konfigurasi AI baru.', 'danger');
            }
        }

        header('Location: ' . BASEURL . '/settings/ai');
        exit;
    }

    /**
     * Activate an AI Configuration
     */
    public function ai_activate($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($id)) {
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('Keamanan!', 'Token CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        $aiModel = $this->model('AiProviderConfig');
        $config = $aiModel->getById((int)$id);

        if (!$config) {
            Flasher::setFlash('Gagal!', 'Konfigurasi AI tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        $aiModel->activate((int)$id);
        Flasher::setFlash('Berhasil!', "Konfigurasi AI '{$config['config_name']}' sekarang aktif sebagai mesin utama.", 'success');
        header('Location: ' . BASEURL . '/settings/ai');
        exit;
    }

    /**
     * Delete an Inactive AI Configuration
     */
    public function ai_delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($id)) {
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('Keamanan!', 'Token CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/settings/ai');
            exit;
        }

        $aiModel = $this->model('AiProviderConfig');
        $res = $aiModel->delete((int)$id);

        if (is_array($res) && !empty($res['error'])) {
            Flasher::setFlash('Tidak Dapat Menghapus!', $res['error'], 'warning');
        } else {
            Flasher::setFlash('Berhasil!', 'Konfigurasi AI berhasil dihapus.', 'success');
        }

        header('Location: ' . BASEURL . '/settings/ai');
        exit;
    }

    /**
     * Test AI Provider Connection (AJAX Endpoint)
     */
    public function ai_test() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid atau kedaluwarsa.']);
            exit;
        }

        $configId = !empty($_POST['config_id']) ? (int)$_POST['config_id'] : null;
        $aiModel = $this->model('AiProviderConfig');

        $baseUrl = trim($_POST['base_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $modelId = trim($_POST['model_id'] ?? '');
        $provider = trim($_POST['provider'] ?? 'openai_compatible');
        $timeout = min((int)($_POST['timeout'] ?? 15), 30);

        // If config_id is provided and apiKey field is blank, fetch stored decrypted key
        if ($configId && empty($apiKey)) {
            $existing = $aiModel->getById($configId, true);
            if ($existing) {
                $apiKey = $existing['plain_key'] ?? '';
                if (empty($baseUrl)) $baseUrl = $existing['base_url'];
                if (empty($modelId)) $modelId = $existing['model_id'];
                if (empty($provider)) $provider = $existing['provider'];
            }
        }

        if (empty($baseUrl) || empty($modelId)) {
            echo json_encode(['success' => false, 'message' => 'Base URL dan Model ID harus diisi untuk pengujian.']);
            exit;
        }

        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'API Key kosong atau belum tersimpan. Masukkan API Key terlebih dahulu.']);
            exit;
        }

        // SSRF Check
        $ssrf = Security::validateOutboundUrl($baseUrl);
        if (!$ssrf['valid']) {
            echo json_encode(['success' => false, 'message' => 'Validasi URL gagal: ' . $ssrf['error']]);
            exit;
        }

        // Initialize provider adapter
        $cfg = [
            'provider' => $provider,
            'base_url' => $baseUrl,
            'api_key' => $apiKey,
            'model_id' => $modelId,
            'parameters' => ['timeout' => $timeout]
        ];

        $adapter = ($provider === 'openai') ? new OpenAIProvider($cfg) : new OpenAICompatibleProvider($cfg);
        $result = $adapter->testConnection();

        // Save test record to DB if configId exists
        if ($configId) {
            $statusStr = $result['success'] ? 'SUCCESS' : ($result['status'] ?? 'FAILED');
            $aiModel->updateTestStatus($configId, $statusStr, $result['latency_ms'] ?? null, $result['message'] ?? null);
        }

        echo json_encode($result);
        exit;
    }

    // =========================================================================
    // 2. OTP CONFIGURATION (WHATSAPP FONNTE KHUSUS)
    // =========================================================================

    /**
     * View OTP Configuration Page
     */
    public function otp() {
        $data['title'] = 'Autentikasi OTP WhatsApp (Fonnte) — Pengaturan Sistem';
        $data['active_tab'] = 'otp';

        $authModel = $this->model('AuthProviderConfig');
        $settingModel = $this->model('SystemSetting');

        $data['fonnte_config'] = $authModel->getByType('whatsapp_fonnte');
        $data['otp_policies'] = $settingModel->getOtpPolicies();
        $data['csrf_token'] = Security::generateCsrfToken();

        $this->view('templates/header', $data);
        $this->view('settings/otp', $data);
        $this->view('templates/footer');
    }

    /**
     * Save OTP Configuration & Policies (WhatsApp Only)
     */
    public function otp_save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/settings/otp');
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('Keamanan!', 'Token CSRF tidak valid. Silakan coba lagi.', 'danger');
            header('Location: ' . BASEURL . '/settings/otp');
            exit;
        }

        $authModel = $this->model('AuthProviderConfig');
        $settingModel = $this->model('SystemSetting');
        $adminId = $_SESSION['user_id'] ?? null;

        // 1. WhatsApp Fonnte Settings
        $fonnteEnabled = !empty($_POST['fonnte_enabled']);
        $fonnteToken = trim($_POST['fonnte_token'] ?? '');
        $fonnteUrl = trim($_POST['fonnte_api_url'] ?? 'https://api.fonnte.com/send');
        $fonnteCountry = trim($_POST['fonnte_country_code'] ?? '62');

        // SSRF check on Fonnte URL
        if (!empty($fonnteUrl)) {
            $ssrf = Security::validateOutboundUrl($fonnteUrl);
            if (!$ssrf['valid']) {
                Flasher::setFlash('URL Ditolak!', 'Validasi URL Fonnte gagal: ' . $ssrf['error'], 'danger');
                header('Location: ' . BASEURL . '/settings/otp');
                exit;
            }
        }

        $authModel->saveConfig(
            'whatsapp_fonnte',
            !empty($fonnteToken) ? $fonnteToken : null,
            [
                'api_url' => $fonnteUrl ?: 'https://api.fonnte.com/send',
                'country_code' => $fonnteCountry ?: '62'
            ],
            $fonnteEnabled
        );

        // Ensure email_smtp is permanently disabled in database
        $db = new Database();
        $db->query("UPDATE auth_provider_configs SET is_enabled = 0 WHERE provider_type = 'email_smtp'");
        $db->execute();

        // 2. OTP Policies (Backend Min/Max validation)
        $otpLength = max(4, min((int)($_POST['otp_length'] ?? 6), 8));
        $expiryMinutes = max(1, min((int)($_POST['otp_expiry_minutes'] ?? 5), 60));
        $maxAttempts = max(1, min((int)($_POST['otp_max_attempts'] ?? 5), 10));
        $cooldownSeconds = max(10, min((int)($_POST['otp_cooldown_seconds'] ?? 60), 300));
        $maxPhoneHour = max(1, min((int)($_POST['otp_max_requests_phone_hour'] ?? 5), 50));
        $maxIpHour = max(5, min((int)($_POST['otp_max_requests_ip_hour'] ?? 15), 100));
        $allowedChannels = 'whatsapp'; // WhatsApp only
        $autoRegister = !empty($_POST['otp_auto_register']) ? 1 : 0;
        $loginRequired = !empty($_POST['otp_login_required']) ? 1 : 0;

        $waTemplate = trim($_POST['otp_wa_template'] ?? '');
        if (empty($waTemplate) || !str_contains($waTemplate, '{otp}')) {
            $waTemplate = 'Halo! Kode verifikasi WhatsApp Anda adalah: {otp}. Kode berlaku selama {expiry} menit. Jangan bagikan kode ini kepada siapa pun.';
        }

        $settingModel->set('otp_length', $otpLength, 'integer', 'otp', 0, $adminId);
        $settingModel->set('otp_expiry_minutes', $expiryMinutes, 'integer', 'otp', 0, $adminId);
        $settingModel->set('otp_max_attempts', $maxAttempts, 'integer', 'otp', 0, $adminId);
        $settingModel->set('otp_cooldown_seconds', $cooldownSeconds, 'integer', 'otp', 0, $adminId);
        $settingModel->set('otp_max_requests_phone_hour', $maxPhoneHour, 'integer', 'otp', 0, $adminId);
        $settingModel->set('otp_max_requests_ip_hour', $maxIpHour, 'integer', 'otp', 0, $adminId);
        $settingModel->set('otp_allowed_channels', $allowedChannels, 'string', 'otp', 0, $adminId);
        $settingModel->set('otp_auto_register', $autoRegister, 'boolean', 'otp', 0, $adminId);
        $settingModel->set('otp_login_required', $loginRequired, 'boolean', 'otp', 0, $adminId);
        $settingModel->set('otp_wa_template', $waTemplate, 'string', 'otp', 0, $adminId);

        Flasher::setFlash('Berhasil Disimpan!', 'Pengaturan WhatsApp Fonnte dan kebijakan keamanan OTP berhasil diperbarui.', 'success');
        header('Location: ' . BASEURL . '/settings/otp');
        exit;
    }

    /**
     * Test OTP Gateway Connection (AJAX Endpoint)
     */
    public function otp_test() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
            exit;
        }

        $channel = trim($_POST['channel'] ?? 'whatsapp');
        $authModel = $this->model('AuthProviderConfig');

        if ($channel === 'whatsapp') {
            $token = trim($_POST['token'] ?? '');
            $url = trim($_POST['api_url'] ?? '');

            // If token field is empty, check stored credentials
            if (empty($token)) {
                $existing = $authModel->getByType('whatsapp_fonnte', true);
                $token = $existing['plain_credentials'] ?? '';
                if (empty($url)) {
                    $url = $existing['settings']['api_url'] ?? 'https://api.fonnte.com/send';
                }
            }

            $res = OtpService::testFonnteConnection($token, $url);
            $statusStr = $res['success'] ? 'SUCCESS' : ($res['status'] ?? 'FAILED');
            $authModel->updateTestStatus('whatsapp_fonnte', $statusStr, $res['latency_ms'] ?? null, $res['message'] ?? null);

            echo json_encode($res);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Channel tidak didukung atau telah dinonaktifkan.']);
        exit;
    }

    /**
     * Send Real Test OTP Message (AJAX Endpoint)
     * Requires explicit recipient destination from admin.
     */
    public function otp_send_test() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
            exit;
        }

        $channel = trim($_POST['channel'] ?? 'whatsapp');
        $target = trim($_POST['target'] ?? '');

        if (empty($target)) {
            echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tujuan wajib dimasukkan.']);
            exit;
        }

        if ($channel === 'whatsapp') {
            $res = OtpService::sendTestWhatsApp($target);
            echo json_encode($res);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Layanan pengiriman email telah dinonaktifkan.']);
        exit;
    }
}
