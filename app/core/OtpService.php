<?php
/**
 * Unified OTP Service (WhatsApp & Email)
 * 
 * Supports dynamic policies from SystemSetting (length, expiry, attempts, cooldown, rate limits),
 * multi-channel dispatch (Fonnte WhatsApp & SMTP Email via PHPMailer),
 * atomic single-use verification, rate-limiting, and connection testing.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/FonnteService.php';
require_once dirname(__DIR__) . '/models/SystemSetting.php';
require_once dirname(__DIR__) . '/models/AuthProviderConfig.php';

class OtpService {
    // Default fallback values
    const DEFAULT_LENGTH = 6;
    const DEFAULT_EXPIRY_MINUTES = 5;
    const DEFAULT_MAX_ATTEMPTS = 5;
    const DEFAULT_COOLDOWN_SECONDS = 60;
    const DEFAULT_MAX_PHONE_HOUR = 5;
    const DEFAULT_MAX_IP_HOUR = 15;

    /**
     * Load active OTP policies from database with sensible defaults
     */
    public static function getPolicies(): array {
        try {
            $settingModel = new SystemSetting();
            return $settingModel->getOtpPolicies();
        } catch (Throwable $e) {
            return [
                'otp_length' => self::DEFAULT_LENGTH,
                'otp_expiry_minutes' => self::DEFAULT_EXPIRY_MINUTES,
                'otp_max_attempts' => self::DEFAULT_MAX_ATTEMPTS,
                'otp_cooldown_seconds' => self::DEFAULT_COOLDOWN_SECONDS,
                'otp_max_requests_phone_hour' => self::DEFAULT_MAX_PHONE_HOUR,
                'otp_max_requests_ip_hour' => self::DEFAULT_MAX_IP_HOUR,
                'otp_allowed_channels' => 'whatsapp',
                'otp_auto_register' => true,
                'otp_login_required' => false,
                'otp_wa_template' => 'Halo! Kode verifikasi Anda di *{app_name}* adalah: *{otp}*. Kode berlaku selama {expiry} menit. Jangan bagikan kode ini kepada siapa pun.',
                'otp_email_template' => '<p>Halo <strong>{name}</strong>,</p><p>Kode verifikasi OTP Anda untuk {app_name} adalah: <strong style="font-size:20px;letter-spacing:3px;">{otp}</strong>.</p><p>Kode ini berlaku selama {expiry} menit. Jangan berikan kode ini kepada pihak mana pun.</p>'
            ];
        }
    }

    /**
     * Request OTP via WhatsApp or Email
     *
     * @param string $target Phone number or email address
     * @param string|null $channelOrIp 'whatsapp', 'email', or legacy IP address
     * @param string|null $ipAddress Client IP address
     * @param string|null $recipientName Recipient display name (optional)
     * @return array
     */
    public static function requestOtp(string $target, ?string $channelOrIp = 'whatsapp', ?string $ipAddress = null, ?string $recipientName = null): array {
        $policies = self::getPolicies();

        // Support legacy signature: requestOtp($phone, $ipAddress)
        if ($channelOrIp !== 'email' && $channelOrIp !== 'whatsapp') {
            $channel = 'whatsapp';
            $ip = $channelOrIp ?: ($ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
        } else {
            $channel = strtolower((string)$channelOrIp);
            $ip = $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        }

        if ($channel === 'email') {
            return self::requestEmailOtp($target, $ip, $policies, $recipientName);
        } else {
            return self::requestWhatsAppOtp($target, $ip, $policies);
        }
    }

    /**
     * Request OTP via WhatsApp
     */
    private static function requestWhatsAppOtp(string $phone, string $ip, array $policies): array {
        $normalizedPhone = FonnteService::normalizePhone($phone);
        if (!$normalizedPhone) {
            return [
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid. Gunakan format internasional (contoh: 08123456789 atau 628123456789).'
            ];
        }

        $db = new Database();
        $cooldownSec = (int)($policies['otp_cooldown_seconds'] ?? self::DEFAULT_COOLDOWN_SECONDS);
        $expiryMin = (int)($policies['otp_expiry_minutes'] ?? self::DEFAULT_EXPIRY_MINUTES);
        $maxPhoneHour = (int)($policies['otp_max_requests_phone_hour'] ?? self::DEFAULT_MAX_PHONE_HOUR);
        $maxIpHour = (int)($policies['otp_max_requests_ip_hour'] ?? self::DEFAULT_MAX_IP_HOUR);
        $otpLength = (int)($policies['otp_length'] ?? self::DEFAULT_LENGTH);

        // 1. Cooldown check
        $db->query("SELECT id, created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago 
                    FROM whatsapp_otps 
                    WHERE phone = :phone 
                    ORDER BY id DESC LIMIT 1");
        $db->bind('phone', $normalizedPhone);
        $lastOtp = $db->single();

        if ($lastOtp && isset($lastOtp['seconds_ago']) && $lastOtp['seconds_ago'] < $cooldownSec) {
            $remaining = $cooldownSec - (int)$lastOtp['seconds_ago'];
            return [
                'success' => false,
                'message' => "Mohon tunggu {$remaining} detik sebelum meminta kode OTP baru.",
                'cooldown' => $remaining
            ];
        }

        // 2. Max per phone per hour
        $db->query("SELECT COUNT(*) as count 
                    FROM whatsapp_otps 
                    WHERE phone = :phone AND created_at >= (NOW() - INTERVAL 1 HOUR)");
        $db->bind('phone', $normalizedPhone);
        $phoneCount = (int)($db->single()['count'] ?? 0);

        if ($phoneCount >= $maxPhoneHour) {
            return [
                'success' => false,
                'message' => 'Batas permintaan OTP per jam untuk nomor ini telah tercapai. Silakan coba lagi nanti.'
            ];
        }

        // 3. Max per IP per hour
        if ($ip) {
            $db->query("SELECT COUNT(*) as count 
                        FROM whatsapp_otps 
                        WHERE ip_address = :ip AND created_at >= (NOW() - INTERVAL 1 HOUR)");
            $db->bind('ip', $ip);
            $ipCount = (int)($db->single()['count'] ?? 0);

            if ($ipCount >= $maxIpHour) {
                return [
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan OTP dari alamat IP Anda. Silakan coba lagi nanti.'
                ];
            }
        }

        // 3.5 Validate whether phone is registered on WhatsApp via Fonnte validate endpoint
        $waCheck = FonnteService::checkWhatsAppRegistered($normalizedPhone);
        if (!$waCheck['registered']) {
            return [
                'success' => false,
                'message' => $waCheck['message']
            ];
        }

        // 4. Generate cryptographically secure numeric OTP
        $otpCode = self::generateNumericCode($otpLength);
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMin * 60));

        // Development log helper (useful for local debugging / testing)
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logLine = date('[Y-m-d H:i:s]') . " [OTP] Target: {$normalizedPhone} | Code: {$otpCode} | Expiry: {$expiryMin}m\n";
        @file_put_contents($logDir . '/otp.log', $logLine, FILE_APPEND);

        // 5. Save OTP Hash to database
        $db->query("INSERT INTO whatsapp_otps (phone, otp_hash, expires_at, attempts, is_used, ip_address, created_at) 
                    VALUES (:phone, :otp_hash, :expires_at, 0, 0, :ip, NOW())");
        $db->bind('phone', $normalizedPhone);
        $db->bind('otp_hash', $otpHash);
        $db->bind('expires_at', $expiresAt);
        $db->bind('ip', $ip);
        $db->execute();
        $otpId = $db->lastInsertId();

        // 6. Build message from template
        $template = $policies['otp_wa_template'] ?? 'Halo! Kode verifikasi WhatsApp Anda adalah: {otp}. Kode berlaku selama {expiry} menit. Jangan bagikan kode ini kepada siapa pun.';
        $appName = defined('APP_NAME') ? APP_NAME : 'NNACOSA';
        $message = str_replace(
            ['{app_name}', '{otp}', '{expiry}'],
            [$appName, $otpCode, $expiryMin],
            $template
        );

        // 7. Dispatch via Fonnte
        $sendResult = FonnteService::sendMessage($normalizedPhone, $message);

        if (!$sendResult['success']) {
            $db->query("DELETE FROM whatsapp_otps WHERE id = :id");
            $db->bind('id', $otpId);
            $db->execute();

            return [
                'success' => false,
                'message' => $sendResult['message'] ?? 'Gagal mengirim pesan WhatsApp. Silakan periksa kembali nomor Anda.'
            ];
        }

        return [
            'success' => true,
            'channel' => 'whatsapp',
            'message' => 'Kode OTP berhasil dikirim ke WhatsApp ' . FonnteService::maskPhone($normalizedPhone) . '.',
            'phone' => $normalizedPhone,
            'masked_phone' => FonnteService::maskPhone($normalizedPhone),
            'cooldown' => $cooldownSec,
            'expires_in' => $expiryMin * 60
        ];
    }

    /**
     * Request OTP via Email
     */
    private static function requestEmailOtp(string $email, string $ip, array $policies, ?string $recipientName = null): array {
        $email = trim(strtolower($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Format alamat email tidak valid.'
            ];
        }

        $db = new Database();
        $cooldownSec = (int)($policies['otp_cooldown_seconds'] ?? self::DEFAULT_COOLDOWN_SECONDS);
        $expiryMin = (int)($policies['otp_expiry_minutes'] ?? self::DEFAULT_EXPIRY_MINUTES);
        $maxEmailHour = (int)($policies['otp_max_requests_phone_hour'] ?? self::DEFAULT_MAX_PHONE_HOUR);
        $maxIpHour = (int)($policies['otp_max_requests_ip_hour'] ?? self::DEFAULT_MAX_IP_HOUR);
        $otpLength = (int)($policies['otp_length'] ?? self::DEFAULT_LENGTH);

        // 1. Cooldown check
        $db->query("SELECT id, created_at, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago 
                    FROM email_otps 
                    WHERE email = :email 
                    ORDER BY id DESC LIMIT 1");
        $db->bind('email', $email);
        $lastOtp = $db->single();

        if ($lastOtp && isset($lastOtp['seconds_ago']) && $lastOtp['seconds_ago'] < $cooldownSec) {
            $remaining = $cooldownSec - (int)$lastOtp['seconds_ago'];
            return [
                'success' => false,
                'message' => "Mohon tunggu {$remaining} detik sebelum meminta kode OTP email baru.",
                'cooldown' => $remaining
            ];
        }

        // 2. Max per email per hour
        $db->query("SELECT COUNT(*) as count 
                    FROM email_otps 
                    WHERE email = :email AND created_at >= (NOW() - INTERVAL 1 HOUR)");
        $db->bind('email', $email);
        $emailCount = (int)($db->single()['count'] ?? 0);

        if ($emailCount >= $maxEmailHour) {
            return [
                'success' => false,
                'message' => 'Batas permintaan OTP per jam untuk email ini telah tercapai.'
            ];
        }

        // 3. Max per IP per hour
        if ($ip) {
            $db->query("SELECT COUNT(*) as count 
                        FROM email_otps 
                        WHERE ip_address = :ip AND created_at >= (NOW() - INTERVAL 1 HOUR)");
            $db->bind('ip', $ip);
            $ipCount = (int)($db->single()['count'] ?? 0);

            if ($ipCount >= $maxIpHour) {
                return [
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan OTP dari alamat IP Anda.'
                ];
            }
        }

        // 4. Generate secure code
        $otpCode = self::generateNumericCode($otpLength);
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMin * 60));

        // 5. Save to database
        $db->query("INSERT INTO email_otps (email, otp_hash, expires_at, attempts, is_used, ip_address, created_at) 
                    VALUES (:email, :otp_hash, :expires_at, 0, 0, :ip, NOW())");
        $db->bind('email', $email);
        $db->bind('otp_hash', $otpHash);
        $db->bind('expires_at', $expiresAt);
        $db->bind('ip', $ip);
        $db->execute();
        $otpId = $db->lastInsertId();

        // 6. Build message
        $template = $policies['otp_email_template'] ?? '<p>Halo <strong>{name}</strong>,</p><p>Kode OTP: <strong>{otp}</strong></p>';
        $appName = defined('APP_NAME') ? APP_NAME : 'NNACOSA';
        $displayName = !empty($recipientName) ? htmlspecialchars($recipientName) : 'Pengguna';
        $body = str_replace(
            ['{app_name}', '{name}', '{otp}', '{expiry}'],
            [$appName, $displayName, $otpCode, $expiryMin],
            $template
        );

        $subject = "Kode Verifikasi OTP — {$appName}";

        // 7. Send email via SMTP
        $mailResult = self::sendEmailMessage($email, $displayName, $subject, $body);

        if (!$mailResult['success']) {
            $db->query("DELETE FROM email_otps WHERE id = :id");
            $db->bind('id', $otpId);
            $db->execute();

            return [
                'success' => false,
                'message' => 'Gagal mengirim email OTP: ' . ($mailResult['message'] ?? 'Periksa konfigurasi SMTP.')
            ];
        }

        $maskedEmail = self::maskEmail($email);
        return [
            'success' => true,
            'channel' => 'email',
            'message' => "Kode OTP berhasil dikirim ke email {$maskedEmail}.",
            'email' => $email,
            'masked_email' => $maskedEmail,
            'cooldown' => $cooldownSec,
            'expires_in' => $expiryMin * 60
        ];
    }

    /**
     * Verify submitted OTP against stored hash (WhatsApp or Email)
     */
    public static function verifyOtp(string $target, ?string $inputOtp = '', string $channel = 'whatsapp'): array {
        $channel = strtolower($channel) === 'email' ? 'email' : 'whatsapp';
        $policies = self::getPolicies();
        $expectedLength = (int)($policies['otp_length'] ?? self::DEFAULT_LENGTH);
        $maxAttempts = (int)($policies['otp_max_attempts'] ?? self::DEFAULT_MAX_ATTEMPTS);

        $inputOtp = trim((string)$inputOtp);
        if (!preg_match('/^[0-9]{' . $expectedLength . '}$/', $inputOtp)) {
            return [
                'success' => false,
                'message' => "Format kode OTP harus berupa {$expectedLength} digit angka."
            ];
        }

        $db = new Database();
        $table = ($channel === 'email') ? 'email_otps' : 'whatsapp_otps';
        $column = ($channel === 'email') ? 'email' : 'phone';

        $normalizedTarget = ($channel === 'whatsapp') ? FonnteService::normalizePhone($target) : trim(strtolower($target));
        if (!$normalizedTarget) {
            return [
                'success' => false,
                'message' => ($channel === 'email') ? 'Email tidak valid.' : 'Nomor WhatsApp tidak valid.'
            ];
        }

        // Find latest unused OTP
        $db->query("SELECT * FROM {$table} WHERE {$column} = :target AND is_used = 0 ORDER BY id DESC LIMIT 1");
        $db->bind('target', $normalizedTarget);
        $otpRow = $db->single();

        if (!$otpRow) {
            return [
                'success' => false,
                'message' => 'Tidak ditemukan kode OTP aktif. Silakan minta kode baru.'
            ];
        }

        // Check expiry
        if (strtotime($otpRow['expires_at']) < time()) {
            return [
                'success' => false,
                'message' => 'Kode OTP telah kedaluwarsa. Silakan minta kode OTP baru.',
                'expired' => true
            ];
        }

        // Check max attempts
        if ((int)$otpRow['attempts'] >= $maxAttempts) {
            return [
                'success' => false,
                'message' => "Batas maksimal {$maxAttempts} kali percobaan verifikasi telah tercapai. Silakan minta kode baru.",
                'max_attempts' => true
            ];
        }

        // Verify hash
        if (!password_verify($inputOtp, $otpRow['otp_hash'])) {
            $newAttempts = (int)$otpRow['attempts'] + 1;
            $db->query("UPDATE {$table} SET attempts = :attempts WHERE id = :id");
            $db->bind('attempts', $newAttempts);
            $db->bind('id', $otpRow['id']);
            $db->execute();

            $remaining = $maxAttempts - $newAttempts;
            if ($remaining > 0) {
                return [
                    'success' => false,
                    'message' => "Kode OTP salah. Sisa percobaan: {$remaining} kali.",
                    'remaining_attempts' => $remaining
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Batas maksimal {$maxAttempts} kali percobaan telah tercapai. Silakan minta kode baru.",
                    'max_attempts' => true
                ];
            }
        }

        // OTP is correct! Consume atomically
        $db->query("UPDATE {$table} SET is_used = 1 WHERE id = :id AND is_used = 0");
        $db->bind('id', $otpRow['id']);
        $db->execute();

        if ($db->rowCount() === 0) {
            return [
                'success' => false,
                'message' => 'Kode OTP ini sudah digunakan sebelumnya. Silakan minta kode baru.'
            ];
        }

        return [
            'success' => true,
            'channel' => $channel,
            'message' => 'Kode OTP valid dan berhasil diverifikasi.',
            'target' => $normalizedTarget
        ];
    }

    /**
     * Test SMTP connection without sending email
     */
    public static function testSmtpConnection(?array $customConfig = null): array {
        $startTime = microtime(true);
        $cfg = $customConfig ?: self::getActiveSmtpConfig();

        $host = trim($cfg['host'] ?? '127.0.0.1');
        $port = (int)($cfg['port'] ?? 1025);
        $timeout = (int)($cfg['timeout'] ?? 10);
        $username = trim($cfg['username'] ?? '');
        $password = $cfg['password'] ?? '';
        $encryption = strtolower($cfg['encryption'] ?? 'none');

        // Check SSRF on host
        $ssrf = Security::validateOutboundUrl("http://{$host}:{$port}", true);
        if (!$ssrf['valid']) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'status' => 'SSRF_BLOCKED',
                'message' => 'Validasi Host SMTP gagal: ' . $ssrf['error']
            ];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->Timeout = min($timeout, 10);
            $mail->SMTPAutoTLS = false;

            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
            }

            if (!empty($username) || !empty($password)) {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = $password;
            } else {
                $mail->SMTPAuth = false;
            }

            // Test socket connect and EHLO/AUTH
            $connected = $mail->smtpConnect();
            $mail->smtpClose();

            $endTime = microtime(true);
            $latencyMs = (int)round(($endTime - $startTime) * 1000);

            if ($connected) {
                return [
                    'success' => true,
                    'latency_ms' => $latencyMs,
                    'status' => 'CONNECTED',
                    'message' => "Koneksi dan autentikasi SMTP berhasil ({$latencyMs} ms) ke {$host}:{$port}."
                ];
            } else {
                return [
                    'success' => false,
                    'latency_ms' => $latencyMs,
                    'status' => 'AUTH_FAILED',
                    'message' => "Handshake SMTP ke {$host}:{$port} gagal atau autentikasi ditolak."
                ];
            }
        } catch (Throwable $e) {
            $endTime = microtime(true);
            $latencyMs = (int)round(($endTime - $startTime) * 1000);

            return [
                'success' => false,
                'latency_ms' => $latencyMs,
                'status' => 'CONNECTION_FAILED',
                'message' => 'Gagal terhubung ke SMTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Explicit test email dispatch
     */
    public static function sendTestEmail(string $recipientEmail, ?string $customSubject = null, ?string $customBody = null, ?array $customConfig = null): array {
        $email = trim(strtolower($recipientEmail));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Alamat email tujuan tidak valid.'
            ];
        }

        $appName = defined('APP_NAME') ? APP_NAME : 'NNACOSA';
        $subject = $customSubject ?: "[UJI KONEKSI] Test Email Pengaturan NNACOSA";
        $body = $customBody ?: "<p>Halo Administrator,</p><p>Ini adalah email uji coba dari menu <strong>Pengaturan Sistem &gt; Autentikasi OTP</strong> pada aplikasi <strong>{$appName}</strong>.</p><p>Jika Anda menerima pesan ini, konfigurasi server SMTP telah berjalan dengan benar!</p><p><small>Dikirim pada: " . date('Y-m-d H:i:s') . "</small></p>";

        return self::sendEmailMessage($email, 'Administrator', $subject, $body, $customConfig);
    }

    /**
     * Send email via PHPMailer
     */
    private static function sendEmailMessage(string $toEmail, string $toName, string $subject, string $htmlBody, ?array $customConfig = null): array {
        $cfg = $customConfig ?: self::getActiveSmtpConfig();

        $host = trim($cfg['host'] ?? '127.0.0.1');
        $port = (int)($cfg['port'] ?? 1025);
        $timeout = (int)($cfg['timeout'] ?? 15);
        $username = trim($cfg['username'] ?? '');
        $password = $cfg['password'] ?? '';
        $encryption = strtolower($cfg['encryption'] ?? 'none');
        $fromEmail = $cfg['from_email'] ?? 'noreply@nnacosa.com';
        $fromName = $cfg['from_name'] ?? 'NNACOSA';

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->Timeout = $timeout;
            $mail->SMTPAutoTLS = false;

            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
            }

            if (!empty($username) || !empty($password)) {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = $password;
            } else {
                $mail->SMTPAuth = false;
            }

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();

            return [
                'success' => true,
                'message' => "Email berhasil dikirim ke {$toEmail}."
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'PHPMailer Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve active SMTP configuration from database or environment
     */
    public static function getActiveSmtpConfig(): array {
        try {
            $authModel = new AuthProviderConfig();
            $conf = $authModel->getByType('email_smtp', true);
            if ($conf) {
                $settings = $conf['settings'] ?? [];
                return [
                    'host' => $settings['host'] ?? env('MAIL_HOST', '127.0.0.1'),
                    'port' => (int)($settings['port'] ?? env('MAIL_PORT', 1025)),
                    'username' => $settings['username'] ?? env('MAIL_USERNAME', ''),
                    'password' => $conf['plain_credentials'] ?? env('MAIL_PASSWORD', ''),
                    'encryption' => $settings['encryption'] ?? env('MAIL_ENCRYPTION', 'none'),
                    'from_email' => $settings['from_email'] ?? env('MAIL_FROM_ADDRESS', 'noreply@nnacosa.com'),
                    'from_name' => $settings['from_name'] ?? env('MAIL_FROM_NAME', 'NNACOSA'),
                    'timeout' => (int)($settings['timeout'] ?? 15),
                    'is_enabled' => (bool)$conf['is_enabled']
                ];
            }
        } catch (Throwable $e) {
            // Fallback to env
        }

        return [
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => (int)env('MAIL_PORT', 1025),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', 'none'),
            'from_email' => env('MAIL_FROM_ADDRESS', 'noreply@nnacosa.com'),
            'from_name' => env('MAIL_FROM_NAME', 'NNACOSA'),
            'timeout' => 15,
            'is_enabled' => false
        ];
    }

    /**
     * Proxy to FonnteService test connection
     */
    public static function testFonnteConnection(?string $explicitToken = null, ?string $explicitUrl = null): array {
        return FonnteService::testConnection($explicitToken, $explicitUrl);
    }

    /**
     * Explicit test WhatsApp dispatch
     */
    public static function sendTestWhatsApp(string $phone, ?string $customMessage = null): array {
        $normalized = FonnteService::normalizePhone($phone);
        if (!$normalized) {
            return [
                'success' => false,
                'message' => 'Format nomor WhatsApp tujuan tidak valid.'
            ];
        }

        $appName = defined('APP_NAME') ? APP_NAME : 'NNACOSA';
        $msg = $customMessage ?: "Halo! Ini adalah pesan uji coba dari Pengaturan Sistem {$appName}. Jika Anda menerima pesan ini, gateway WhatsApp Fonnte aktif dan berfungsi dengan baik.";

        return FonnteService::sendMessage($normalized, $msg);
    }

    /**
     * Helper to mask email address (e.g. j***e@domain.com)
     */
    public static function maskEmail(string $email): string {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', min($len - 2, 4)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    /**
     * Generate random numeric OTP
     */
    private static function generateNumericCode(int $length): string {
        $length = max(4, min($length, 8));
        $min = (int)pow(10, $length - 1);
        $max = (int)pow(10, $length) - 1;
        return (string)random_int($min, $max);
    }

    /**
     * Clean up expired OTP records older than 24 hours
     */
    public static function cleanupExpired(): int {
        $db = new Database();
        $db->query("DELETE FROM whatsapp_otps WHERE expires_at < (NOW() - INTERVAL 1 DAY)");
        $db->execute();
        $waCount = $db->rowCount();

        $db->query("DELETE FROM email_otps WHERE expires_at < (NOW() - INTERVAL 1 DAY)");
        $db->execute();
        $emailCount = $db->rowCount();

        return $waCount + $emailCount;
    }
}
