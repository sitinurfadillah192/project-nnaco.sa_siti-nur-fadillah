<?php
/**
 * CLI Tool: Test SMTP Connection & Send Diagnostic Email
 * NNACOSA — Automated Order Status Email Notifications
 *
 * Usage:
 *   php scripts/test-smtp-connection.php [recipient@email.com]
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Akses ditolak: Skrip hanya dapat dijalankan melalui CLI.\n");
}

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$config = require __DIR__ . '/../app/config/mail.php';

echo "============================================================\n";
echo "NNACOSA — SMTP Connection Diagnostic Tool\n";
echo "============================================================\n\n";

echo "Memeriksa konfigurasi .env:\n";
echo " - MAIL_ENABLED     : " . ($config['enabled'] ? 'TRUE' : 'FALSE') . "\n";
echo " - MAIL_HOST        : " . ($config['host'] ?: '(kosong)') . "\n";
echo " - MAIL_PORT        : " . $config['port'] . "\n";
echo " - MAIL_USERNAME    : " . ($config['username'] ?: '(kosong)') . "\n";
echo " - MAIL_PASSWORD    : " . (!empty($config['password']) ? '****** (terpasang)' : '(kosong)') . "\n";
echo " - MAIL_ENCRYPTION  : " . ($config['encryption'] ?: 'none') . "\n";
echo " - MAIL_FROM_ADDRESS: " . ($config['from_address'] ?: '(kosong)') . "\n";
echo " - MAIL_FROM_NAME   : " . ($config['from_name'] ?: '(kosong)') . "\n\n";

if (!$config['enabled']) {
    echo "[PERINGATAN] MAIL_ENABLED diset ke 'false' di .env.\n";
    echo "Untuk mengaktifkan pengiriman email sungguhan, ubah MAIL_ENABLED=true di file .env.\n\n";
}

$isLocalSandbox = in_array($config['host'], ['127.0.0.1', 'localhost', 'sandbox'], true);

if (empty($config['host'])) {
    echo "[ERROR] Konfigurasi SMTP belum lengkap. Mohon isi MAIL_HOST di file .env.\n";
    exit(1);
}

if (!$isLocalSandbox && (empty($config['username']) || empty($config['password']))) {
    echo "[ERROR] Konfigurasi SMTP belum lengkap. Mohon isi MAIL_USERNAME dan MAIL_PASSWORD di file .env.\n";
    exit(1);
}

$recipient = $argv[1] ?? (!empty($config['username']) ? $config['username'] : 'test@nnacosa.local');

if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    echo "[ERROR] Alamat email tujuan tidak valid: $recipient\n";
    echo "Penggunaan: php scripts/test-smtp-connection.php [email_tujuan@domain.com]\n";
    exit(1);
}

echo "Mencoba koneksi ke server SMTP {$config['host']}:{$config['port']}...\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo " [SMTP] $str\n";
    };

    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = !empty($config['username']) || !empty($config['password']);
    $mail->Username = $config['username'] ?? '';
    $mail->Password = $config['password'] ?? '';

    $encryption = strtolower((string)($config['encryption'] ?? 'none'));
    if ($encryption === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($encryption === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    }

    $mail->Port = $config['port'];
    $mail->Timeout = $config['timeout'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($config['from_address'], $config['from_name']);
    $mail->addAddress($recipient);

    $mail->isHTML(true);
    $mail->Subject = 'NNACOSA — Tes Koneksi SMTP Berhasil';
    $mail->Body = '<h3>Koneksi SMTP NNACOSA Berhasil!</h3><p>Email pengujian ini mengonfirmasi bahwa konfigurasi SMTP PHPMailer pada website NNACOSA telah terhubung dengan benar dan siap mengirimkan notifikasi status pesanan kepada pelanggan.</p><p>Waktu pengujian: ' . date('Y-m-d H:i:s') . ' WIB</p>';
    $mail->AltBody = "Koneksi SMTP NNACOSA Berhasil!\nEmail pengujian ini mengonfirmasi konfigurasi SMTP PHPMailer siap digunakan.\nWaktu: " . date('Y-m-d H:i:s') . " WIB\n";

    $mail->send();

    echo "\n============================================================\n";
    echo "[BERHASIL] Email tes diagnostik berhasil terkirim ke $recipient!\n";
    echo "============================================================\n";
    exit(0);

} catch (Exception $e) {
    echo "\n============================================================\n";
    echo "[GAGAL] Pengiriman email gagal: " . $mail->ErrorInfo . "\n";
    echo "Exception: " . $e->getMessage() . "\n";
    echo "============================================================\n";
    exit(1);
}
