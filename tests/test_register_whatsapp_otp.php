<?php
/**
 * Test Suite: Registrasi Pengguna dengan WhatsApp OTP & Penghapusan Login WA di Login View
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/FonnteService.php';
require_once __DIR__ . '/../app/core/OtpService.php';
require_once __DIR__ . '/../app/models/User.php';

echo "============================================================\n";
echo "TEST SUITE: REGISTER DENGAN WHATSAPP OTP & LOGIN CLEANUP    \n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;

function assertCondition($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $name\n";
        $passed++;
    } else {
        echo " [FAIL] $name\n";
        $failed++;
    }
}

// -----------------------------------------------------------------
// Test 1: Verifikasi Tampilan Login (Login to nnaco_sa)
// -----------------------------------------------------------------
$loginFile = file_get_contents(__DIR__ . '/../app/views/auth/login.php');
assertCondition("Login view tidak memiliki tombol 'Masuk dengan WhatsApp'", 
    stripos($loginFile, 'Masuk dengan WhatsApp') === false);
assertCondition("Login view tidak memiliki teks pembatas 'atau dengan email'", 
    stripos($loginFile, 'atau dengan email') === false);
assertCondition("Login view tetap mempertahankan form Email, Password, dan Login", 
    stripos($loginFile, 'name="email"') !== false && stripos($loginFile, 'name="password"') !== false);

// -----------------------------------------------------------------
// Test 2: Verifikasi Tampilan Register (Create Account)
// -----------------------------------------------------------------
$registerFile = file_get_contents(__DIR__ . '/../app/views/auth/register.php');
assertCondition("Register view memiliki input Nomor WhatsApp (name=\"phone\")", 
    stripos($registerFile, 'name="phone"') !== false);
assertCondition("Register view memiliki tombol 'Kirim OTP'", 
    stripos($registerFile, 'Kirim OTP') !== false);
assertCondition("Register view memiliki input Kode OTP (name=\"otp\")", 
    stripos($registerFile, 'name="otp"') !== false);

// -----------------------------------------------------------------
// Test 3: Normalisasi Nomor & Pengiriman OTP Mock Fonnte
// -----------------------------------------------------------------
$capturedTarget = null;
$capturedOtp = null;
FonnteService::setMockHandler(function($target, $msg) use (&$capturedTarget, &$capturedOtp) {
    $capturedTarget = $target;
    preg_match('/(\d{6})/', $msg, $m);
    $capturedOtp = $m[1] ?? null;
    return ['success' => true, 'message' => 'Terkirim ke WhatsApp Mock (Fonnte).', 'raw' => []];
});

$testPhone = '087811' . rand(100000, 999999);
$normalizedPhone = FonnteService::normalizePhone($testPhone);
$ip = '127.0.0.1';

// Request OTP
$sendResult = OtpService::requestOtp($normalizedPhone, $ip);
assertCondition("OtpService::requestOtp() berhasil untuk nomor baru", $sendResult['success'] === true);
assertCondition("Nomor WhatsApp dinormalisasi menjadi awalan 628", $capturedTarget === $normalizedPhone && str_starts_with($capturedTarget, '628'));
assertCondition("Mock Fonnte menerima kode OTP 6 digit", strlen($capturedOtp) === 6);

// -----------------------------------------------------------------
// Test 4: Verifikasi OTP Salah & Kedaluwarsa
// -----------------------------------------------------------------
$wrongVerify = OtpService::verifyOtp($normalizedPhone, '000000');
assertCondition("Verifikasi dengan kode OTP salah berhasil ditolak", $wrongVerify['success'] === false);

// -----------------------------------------------------------------
// Test 5: Verifikasi OTP Benar & Pendaftaran Akun
// -----------------------------------------------------------------
$correctVerify = OtpService::verifyOtp($normalizedPhone, $capturedOtp);
assertCondition("Verifikasi dengan kode OTP yang benar berhasil diterima", $correctVerify['success'] === true);

// Pendaftaran ke database
$userModel = new User();
$testEmail = 'cust_wa_' . time() . '@test.com';
$testName = 'Pelanggan OTP WA';
$testPassword = 'password123';

$insertSuccess = $userModel->registerUser([
    'name' => $testName,
    'email' => $testEmail,
    'phone' => $normalizedPhone,
    'is_phone_verified' => 1,
    'password' => $testPassword
]);
assertCondition("User::registerUser() berhasil menyimpan data pengguna baru", $insertSuccess > 0);

// Periksa data di database
$savedUser = $userModel->getUserByEmail($testEmail);
assertCondition("Pengguna tersimpan dengan nomor WhatsApp yang benar", $savedUser && $savedUser['phone'] === $normalizedPhone);
assertCondition("Status nomor terverifikasi (is_phone_verified = 1)", $savedUser && (int)$savedUser['is_phone_verified'] === 1);
assertCondition("Password tersimpan dalam format hash Bcrypt", $savedUser && password_verify($testPassword, $savedUser['password']));
assertCondition("Role pengguna default adalah customer (role_id = 1)", $savedUser && (int)$savedUser['role_id'] === 1);

// -----------------------------------------------------------------
// Test 6: Pencegahan Pendaftaran Ulang Nomor WhatsApp yang Sama
// -----------------------------------------------------------------
assertCondition("User::getUserByPhone() menemukan pengguna yang baru didaftarkan", 
    (bool)$userModel->getUserByPhone($normalizedPhone));

// Cleanup test user & otps
if ($savedUser) {
    $db = new Database();
    $db->query("DELETE FROM users WHERE id = :id");
    $db->bind('id', $savedUser['id']);
    $db->execute();

    $db->query("DELETE FROM whatsapp_otps WHERE phone = :phone");
    $db->bind('phone', $normalizedPhone);
    $db->execute();
}

FonnteService::resetMock();

echo "\n============================================================\n";
echo "Ringkasan Hasil Test: Total=" . ($passed + $failed) . ", Lulus=$passed, Gagal=$failed\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
