<?php
/**
 * NNACOSA - Email / SMTP Configuration
 */

require_once dirname(__DIR__) . '/helpers/env.php';

$mailUsername = env('MAIL_USERNAME', '');
$fromAddress = env('MAIL_FROM_ADDRESS');
if (empty($fromAddress) || $fromAddress === 'noreply@nnacosa.com') {
    $fromAddress = !empty($mailUsername) ? $mailUsername : 'noreply@nnacosa.com';
}

return [
    'enabled' => env('MAIL_ENABLED', false),
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => (int)env('MAIL_PORT', 587),
    'username' => $mailUsername,
    'password' => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'), // 'tls' or 'ssl'
    'from_address' => $fromAddress,
    'from_name' => env('MAIL_FROM_NAME', 'NNACOSA'),
    'timeout' => (int)env('MAIL_TIMEOUT', 15),
    'max_retries' => (int)env('MAIL_MAX_RETRIES', 5),
    'retry_delay_minutes' => (int)env('MAIL_RETRY_DELAY_MINUTES', 5),
    'app_url' => env('APP_URL', 'http://localhost/nnco_sa/public'),
];
