<?php
if (!function_exists('env')) {
    require_once dirname(__DIR__) . '/helpers/env.php';
}

if (!defined('BASEURL')) {
    define('BASEURL', env('APP_URL', 'http://localhost/nnco_sa/public'));
}
if (!defined('DB_HOST')) {
    define('DB_HOST', env('DB_HOST', '127.0.0.1'));
}
if (!defined('DB_USER')) {
    define('DB_USER', env('DB_USER', 'root'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', env('DB_PASS', ''));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', env('DB_NAME', 'nnaco_sa'));
}

// Timezone Configuration
date_default_timezone_set('Asia/Jakarta');

// AI Engine Configuration (NVIDIA NIM / LLM)
define('AI_API_KEY', env('AI_API_KEY', ''));
define('OPENAI_API_KEY', env('OPENAI_API_KEY', ''));
// Fonnte WhatsApp API Configuration
define('FONNTE_TOKEN', env('FONNTE_TOKEN', ''));
define('FONNTE_API_URL', env('FONNTE_API_URL', 'https://api.fonnte.com/send'));

// Master Encryption Key for Secrets (AES-256-GCM)
define('SYSTEM_ENCRYPTION_KEY', env('SYSTEM_ENCRYPTION_KEY', '85dd911c3598b089229232ee5c3cd34e72dd9bf530e0872b9462fab8f188bd37'));
