<?php
/**
 * Run migration for System Settings & Provider Configuration
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/SecretVault.php';

echo "Menjalankan migrasi System Settings...\n";

$db = new Database();

// 1. Run DDL SQL
$sql = file_get_contents(__DIR__ . '/../database/migrations/create_system_settings_tables.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        $db->query($stmt);
        $db->execute();
    }
}
echo "Tabel system_settings, ai_provider_configs, dan auth_provider_configs berhasil diverifikasi/dibuat.\n";

// 2. Seed initial AI Provider Configs
$db->query("SELECT COUNT(*) as count FROM ai_provider_configs");
$aiCount = (int)$db->single()['count'];

if ($aiCount === 0) {
    echo "Membuat seed konfigurasi AI default...\n";
    $initialApiKey = defined('AI_API_KEY') ? AI_API_KEY : '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';
    $encryptedKey = SecretVault::encrypt($initialApiKey);

    // NVIDIA NIM (OpenAI Compatible)
    $db->query("INSERT INTO ai_provider_configs 
        (config_name, provider, base_url, model_id, encrypted_api_key, parameters_json, is_active, last_test_status) 
        VALUES 
        (:name, :provider, :url, :model, :key, :params, 1, 'UNTESTED')");
    $db->bind('name', 'NVIDIA NIM (Llama 3.2 11B Vision)');
    $db->bind('provider', 'openai_compatible');
    $db->bind('url', 'https://integrate.api.nvidia.com/v1/chat/completions');
    $db->bind('model', 'meta/llama-3.2-11b-vision-instruct');
    $db->bind('key', $encryptedKey);
    $db->bind('params', json_encode(['timeout' => 45, 'max_tokens' => 500, 'temperature' => 0.5]));
    $db->execute();

    // OpenAI Official Template (Inactive)
    $db->query("INSERT INTO ai_provider_configs 
        (config_name, provider, base_url, model_id, encrypted_api_key, parameters_json, is_active, last_test_status) 
        VALUES 
        (:name, :provider, :url, :model, :key, :params, 0, 'UNTESTED')");
    $db->bind('name', 'OpenAI Official (GPT-4o Mini)');
    $db->bind('provider', 'openai');
    $db->bind('url', 'https://api.openai.com/v1/chat/completions');
    $db->bind('model', 'gpt-4o-mini');
    $db->bind('key', null);
    $db->bind('params', json_encode(['timeout' => 30, 'max_tokens' => 500, 'temperature' => 0.7]));
    $db->execute();
    echo "Seed AI configs berhasil ditambahkan.\n";
}

// 3. Seed initial Auth Provider Configs (Fonnte & SMTP)
$db->query("SELECT COUNT(*) as count FROM auth_provider_configs");
$authCount = (int)$db->single()['count'];

if ($authCount === 0) {
    echo "Membuat seed konfigurasi Auth OTP default...\n";
    $fonnteToken = defined('FONNTE_TOKEN') ? FONNTE_TOKEN : 'UFkgx7mvJJgcCcVW8uUk';
    $encryptedFonnteToken = SecretVault::encrypt($fonnteToken);

    // WhatsApp Fonnte
    $db->query("INSERT INTO auth_provider_configs 
        (provider_type, encrypted_credentials, settings_json, is_enabled, last_test_status) 
        VALUES 
        ('whatsapp_fonnte', :cred, :settings, 1, 'UNTESTED')");
    $db->bind('cred', $encryptedFonnteToken);
    $db->bind('settings', json_encode([
        'api_url' => 'https://api.fonnte.com/send',
        'country_code' => '62'
    ]));
    $db->execute();

    // Email SMTP
    $smtpPassword = env('MAIL_PASSWORD', '');
    $encryptedSmtpPass = !empty($smtpPassword) ? SecretVault::encrypt($smtpPassword) : null;
    $db->query("INSERT INTO auth_provider_configs 
        (provider_type, encrypted_credentials, settings_json, is_enabled, last_test_status) 
        VALUES 
        ('email_smtp', :cred, :settings, 0, 'UNTESTED')");
    $db->bind('cred', $encryptedSmtpPass);
    $db->bind('settings', json_encode([
        'host' => env('MAIL_HOST', '127.0.0.1'),
        'port' => (int)env('MAIL_PORT', 1025),
        'username' => env('MAIL_USERNAME', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'none'),
        'from_email' => env('MAIL_FROM_ADDRESS', 'noreply@nnacosa.com'),
        'from_name' => env('MAIL_FROM_NAME', 'NNACOSA'),
        'timeout' => 15
    ]));
    $db->execute();
    echo "Seed Auth OTP configs berhasil ditambahkan.\n";
}

// 4. Seed initial System Settings (OTP Policy)
$defaultSettings = [
    ['otp', 'otp_length', '6', 'integer', 0],
    ['otp', 'otp_expiry_minutes', '5', 'integer', 0],
    ['otp', 'otp_max_attempts', '5', 'integer', 0],
    ['otp', 'otp_cooldown_seconds', '60', 'integer', 0],
    ['otp', 'otp_max_requests_phone_hour', '5', 'integer', 0],
    ['otp', 'otp_max_requests_ip_hour', '15', 'integer', 0],
    ['otp', 'otp_allowed_channels', 'whatsapp', 'string', 0],
    ['otp', 'otp_auto_register', '1', 'boolean', 0],
    ['otp', 'otp_login_required', '0', 'boolean', 0],
    ['otp', 'otp_wa_template', 'Halo! Kode verifikasi Anda di *{app_name}* adalah: *{otp}*. Kode berlaku selama {expiry} menit. Jangan bagikan kode ini kepada siapa pun.', 'string', 0],
    ['otp', 'otp_email_template', '<p>Halo <strong>{name}</strong>,</p><p>Kode verifikasi OTP Anda untuk {app_name} adalah: <strong style="font-size:20px;letter-spacing:3px;">{otp}</strong>.</p><p>Kode ini berlaku selama {expiry} menit. Jangan berikan kode ini kepada pihak mana pun.</p>', 'string', 0]
];

foreach ($defaultSettings as $s) {
    $db->query("INSERT IGNORE INTO system_settings (setting_group, setting_key, setting_value, value_type, is_secret) 
                VALUES (:group, :key, :val, :type, :secret)");
    $db->bind('group', $s[0]);
    $db->bind('key', $s[1]);
    $db->bind('val', $s[2]);
    $db->bind('type', $s[3]);
    $db->bind('secret', $s[4]);
    $db->execute();
}
echo "Seed system_settings OTP policies berhasil diverifikasi/ditambahkan.\n";

echo "Migrasi System Settings Selesai dengan Sukses!\n";
