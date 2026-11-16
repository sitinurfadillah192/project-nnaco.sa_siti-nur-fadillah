<?php
/**
 * Live HTTP Test for User Management Controller & Dashboard Access
 */

require_once __DIR__ . '/../app/helpers/env.php';
$baseUrl = rtrim(env('APP_URL', 'http://localhost/nnco_sa/public'), '/');

$cookieCustomer = sys_get_temp_dir() . '/cookie_customer_' . time() . '.txt';
$cookieAdmin = sys_get_temp_dir() . '/cookie_admin_' . time() . '.txt';

function loginWithCsrf($email, $password, $cookieFile) {
    global $baseUrl;
    // 1. Get CSRF Token
    $ch = curl_init($baseUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $html = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="csrf_token"\s+value="([^"]+)"/', (string)$html, $matches);
    $csrf = $matches[1] ?? '';

    // 2. Perform Login POST
    $ch = curl_init($baseUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password,
        'csrf_token' => $csrf
    ]));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    return ['body' => $res, 'status' => $info['http_code'], 'url' => $info['url']];
}

function httpGet($url, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['body' => $res, 'status' => $info['http_code'], 'url' => $info['url']];
}

// Probe HTTP connectivity
$probe = httpGet($baseUrl . '/auth/login', $cookieCustomer);
if ($probe['status'] === 0 || empty($probe['body'])) {
    echo "Info: HTTP Web server tidak terdeteksi pada {$baseUrl}.\n";
    echo "[SKIPPED] Live HTTP integration test dilewati secara anggun.\n";
    @unlink($cookieCustomer);
    @unlink($cookieAdmin);
    exit(0);
}

echo "Testing Customer Login & RBAC Access Control:\n";
// 1. Customer login
loginWithCsrf('customer@gmail.com', '123456', $cookieCustomer);

$userAccessCust = httpGet($baseUrl . '/user', $cookieCustomer);
$isCustomerBlocked = (strpos($userAccessCust['url'], '/public/user') === false || strpos($userAccessCust['body'], 'Manajemen User') === false);
echo "Customer blocked from User Management: " . ($isCustomerBlocked ? "[PASS]" : "[FAIL]") . "\n\n";

echo "Testing Admin Login & Access:\n";
// 2. Admin login
$adminLoginRes = loginWithCsrf('admin@nnacosa.com', '123456', $cookieAdmin);

// Check dashboard contains Manajemen User
$dashAdmin = httpGet($baseUrl . '/dashboard', $cookieAdmin);
$hasDashCard = strpos($dashAdmin['body'], 'Manajemen User') !== false;
echo "Admin Dashboard contains 'Manajemen User' card: " . ($hasDashCard ? "[PASS]" : "[FAIL]") . "\n";

// Check /user page contains table and stats
$userPageAdmin = httpGet($baseUrl . '/user', $cookieAdmin);
$hasUserTable = strpos($userPageAdmin['body'], 'Daftar Pengguna') !== false && strpos($userPageAdmin['body'], 'Tambah Pengguna') !== false;
echo "Admin /user page contains 'Daftar Pengguna' and 'Tambah Pengguna': " . ($hasUserTable ? "[PASS]" : "[FAIL]") . "\n";

// Check create page
$createPageAdmin = httpGet($baseUrl . '/user/create', $cookieAdmin);
$hasCreateForm = strpos($createPageAdmin['body'], 'Tambah Pengguna Baru') !== false && strpos($createPageAdmin['body'], 'name="name"') !== false;
echo "Admin /user/create page contains form: " . ($hasCreateForm ? "[PASS]" : "[FAIL]") . "\n";

// Clean temp cookies
@unlink($cookieCustomer);
@unlink($cookieAdmin);
