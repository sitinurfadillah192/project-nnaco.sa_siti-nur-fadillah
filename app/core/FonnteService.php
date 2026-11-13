<?php
/**
 * Fonnte WhatsApp Gateway Service
 * 
 * Supports dynamic configuration via AuthProviderConfig model (AES-256-GCM),
 * SSRF validation, connection testing via device status endpoint,
 * and automated test mocking.
 */

require_once __DIR__ . '/Security.php';
require_once dirname(__DIR__) . '/models/AuthProviderConfig.php';

class FonnteService {
    private static $mockHandler = null;

    /**
     * Set a mock handler for automated testing
     * @param callable|null $handler Function($target, $message): array
     */
    public static function setMockHandler(?callable $handler): void {
        self::$mockHandler = $handler;
    }

    /**
     * Reset mock handler to default (live cURL)
     */
    public static function resetMock(): void {
        self::$mockHandler = null;
    }

    /**
     * Resolve active Fonnte token and API URL from DB or constants
     */
    public static function getActiveConfig(): array {
        try {
            $authModel = new AuthProviderConfig();
            $conf = $authModel->getByType('whatsapp_fonnte', true);
            if ($conf && !empty($conf['plain_credentials'])) {
                $settings = $conf['settings'] ?? [];
                return [
                    'token' => $conf['plain_credentials'],
                    'api_url' => $settings['api_url'] ?? 'https://api.fonnte.com/send',
                    'country_code' => $settings['country_code'] ?? '62',
                    'is_enabled' => (bool)$conf['is_enabled']
                ];
            }
        } catch (Throwable $e) {
            // Fallback to constants
        }

        $token = defined('FONNTE_TOKEN') ? FONNTE_TOKEN : getenv('FONNTE_TOKEN');
        $apiUrl = defined('FONNTE_API_URL') ? FONNTE_API_URL : (getenv('FONNTE_API_URL') ?: 'https://api.fonnte.com/send');

        return [
            'token' => $token ?: '',
            'api_url' => $apiUrl,
            'country_code' => '62',
            'is_enabled' => true
        ];
    }

    /**
     * Normalize a phone number to international format without plus sign (e.g. 6281234567890)
     */
    public static function normalizePhone(string $phone): ?string {
        $cleaned = trim($phone);
        $cleaned = preg_replace('/[\s\-\.\(\)]+/', '', $cleaned);

        if (empty($cleaned)) {
            return null;
        }

        if (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '08')) {
            $cleaned = '628' . substr($cleaned, 2);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '628' . substr($cleaned, 1);
        }

        if (!preg_match('/^[1-9][0-9]{9,15}$/', $cleaned)) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Mask phone number for secure UI display (e.g. 6281234567890 -> +62 812-****-7890)
     */
    public static function maskPhone(string $phone): string {
        $normalized = self::normalizePhone($phone) ?: $phone;
        $len = strlen($normalized);
        if ($len < 8) {
            return $normalized;
        }

        $prefix = substr($normalized, 0, 5);
        $suffix = substr($normalized, -4);
        return '+' . $prefix . '-****-' . $suffix;
    }

    /**
     * Check if a phone number is registered on WhatsApp via Fonnte /validate endpoint
     *
     * @param string $phone
     * @return array ['registered' => bool, 'message' => string]
     */
    public static function checkWhatsAppRegistered(string $phone): array {
        if (self::$mockHandler !== null) {
            return ['registered' => true, 'message' => 'Mock validation pass'];
        }

        $normalized = self::normalizePhone($phone);
        if (!$normalized) {
            return ['registered' => false, 'message' => 'Format nomor WhatsApp tidak valid.'];
        }

        $cfg = self::getActiveConfig();
        $token = $cfg['token'];

        if (empty($token) || $token === 'YOUR_FONNTE_TOKEN_HERE') {
            return ['registered' => true, 'message' => 'Fonnte token unconfigured'];
        }

        $ch = curl_init('https://api.fonnte.com/validate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['target' => $normalized, 'countryCode' => $cfg['country_code'] ?? '62'],
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $token]
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (isset($data['status']) && $data['status'] === true) {
            if (!empty($data['not_registered']) && is_array($data['not_registered']) && in_array($normalized, $data['not_registered'], true)) {
                return [
                    'registered' => false,
                    'message' => 'Nomor WhatsApp ' . self::maskPhone($normalized) . ' tidak terdaftar di WhatsApp. Pastikan nomor sudah benar dan akun WhatsApp aktif.'
                ];
            }
            if (!empty($data['registered']) && is_array($data['registered']) && in_array($normalized, $data['registered'], true)) {
                return [
                    'registered' => true,
                    'message' => 'Nomor terdaftar di WhatsApp.'
                ];
            }
        }

        return ['registered' => true, 'message' => 'Lanjut proses.'];
    }

    /**
     * Test connection to Fonnte API without sending any message.
     * Uses the Fonnte /device status endpoint.
     */
    public static function testConnection(?string $explicitToken = null, ?string $explicitUrl = null): array {
        $startTime = microtime(true);
        $cfg = self::getActiveConfig();
        $token = $explicitToken !== null && $explicitToken !== '' ? $explicitToken : $cfg['token'];
        $apiUrl = $explicitUrl !== null && $explicitUrl !== '' ? $explicitUrl : $cfg['api_url'];

        if (empty($token) || $token === 'YOUR_FONNTE_TOKEN_HERE') {
            return [
                'success' => false,
                'latency_ms' => 0,
                'status' => 'AUTH_FAILED',
                'message' => 'Token API Fonnte belum dikonfigurasi atau kosong.'
            ];
        }

        // Derive device check URL from send endpoint
        $deviceUrl = preg_replace('#/send/?$#i', '/device', $apiUrl);
        if ($deviceUrl === $apiUrl) {
            $deviceUrl = 'https://api.fonnte.com/device';
        }

        // Validate outbound URL against SSRF
        $ssrf = Security::validateOutboundUrl($deviceUrl);
        if (!$ssrf['valid']) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'status' => 'SSRF_BLOCKED',
                'message' => 'Validasi URL Fonnte gagal: ' . $ssrf['error']
            ];
        }

        $ch = curl_init($deviceUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $token
            ]
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        $endTime = microtime(true);
        $latencyMs = (int)round(($endTime - $startTime) * 1000);

        if ($curlErr) {
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'NETWORK_ERROR',
                'message' => 'Gagal menghubungi server Fonnte: ' . $curlErr
            ];
        }

        $decoded = json_decode($rawResponse, true);

        if ($httpCode === 401 || (isset($decoded['status']) && $decoded['status'] === false && stripos($decoded['reason'] ?? '', 'token') !== false)) {
            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'AUTH_FAILED',
                'message' => 'Autentikasi token Fonnte ditolak: ' . ($decoded['reason'] ?? 'Token tidak valid.')
            ];
        }

        if (isset($decoded['status']) && $decoded['status'] === true) {
            $deviceStatus = $decoded['device_status'] ?? ($decoded['status'] ?? 'unknown');
            $deviceNum = !empty($decoded['device']) ? self::maskPhone((string)$decoded['device']) : null;
            $statusText = $deviceNum ? "Terhubung ke perangkat {$deviceNum} (Status: {$deviceStatus})" : "Token valid dan terhubung ke Fonnte";

            return [
                'success' => true,
                'latency_ms' => $latencyMs,
                'status' => 'CONNECTED',
                'message' => "Koneksi berhasil ({$latencyMs} ms). {$statusText}.",
                'device_status' => $deviceStatus
            ];
        }

        $msg = $decoded['reason'] ?? ($decoded['message'] ?? 'Respon Fonnte: HTTP ' . $httpCode);
        return [
            'success' => false,
            'latency_ms' => $latencyMs,
            'status' => 'API_ERROR',
            'message' => 'Fonnte mengembalikan status: ' . $msg
        ];
    }

    /**
     * Send WhatsApp message via Fonnte API
     */
    public static function sendMessage(string $target, string $message, ?string $customToken = null, ?string $customUrl = null): array {
        if (self::$mockHandler !== null) {
            return call_user_func(self::$mockHandler, $target, $message);
        }

        $cfg = self::getActiveConfig();
        $token = $customToken !== null && $customToken !== '' ? $customToken : $cfg['token'];
        $apiUrl = $customUrl !== null && $customUrl !== '' ? $customUrl : $cfg['api_url'];

        if (empty($token) || $token === 'YOUR_FONNTE_TOKEN_HERE') {
            return [
                'success' => false,
                'message' => 'Konfigurasi Token Fonnte belum diset di sistem.',
                'raw' => null
            ];
        }

        // Validate outbound URL against SSRF
        $ssrf = Security::validateOutboundUrl($apiUrl);
        if (!$ssrf['valid']) {
            return [
                'success' => false,
                'message' => 'Keamanan: URL Fonnte ditolak (' . $ssrf['error'] . ')',
                'raw' => null
            ];
        }

        $ch = curl_init($apiUrl);
        $payload = [
            'target' => $target,
            'message' => $message,
            'countryCode' => $cfg['country_code'] ?? '62'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $token
            ]
        ]);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) {
            return [
                'success' => false,
                'message' => 'Koneksi ke gateway WhatsApp (Fonnte) gagal: ' . $curlErr,
                'raw' => null
            ];
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['status']) && $decoded['status'] === true) {
            return [
                'success' => true,
                'message' => 'Pesan WhatsApp berhasil dikirim.',
                'raw' => $decoded
            ];
        }

        $reason = $decoded['reason'] ?? ($decoded['message'] ?? 'Respon API Fonnte tidak valid (HTTP ' . $httpCode . ')');
        return [
            'success' => false,
            'message' => 'Fonnte gagal mengirim pesan: ' . $reason,
            'raw' => $decoded ?? $response
        ];
    }
}
