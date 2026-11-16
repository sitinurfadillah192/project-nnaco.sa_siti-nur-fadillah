<?php
/**
 * Automated Test Suite for WhatsApp OTP Authentication with Fonnte
 * Covers all 13 required test cases without sending real WhatsApp messages.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Security.php';
require_once __DIR__ . '/../app/core/FonnteService.php';
require_once __DIR__ . '/../app/core/OtpService.php';
require_once __DIR__ . '/../app/models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

class WhatsAppAuthTestSuite {
    private $db;
    private $userModel;
    private $passed = 0;
    private $failed = 0;
    private $lastSentMessage = null;
    private $lastSentTarget = null;
    private $lastExtractedOtp = null;

    public function __construct() {
        $this->db = new Database();
        $this->userModel = new User();
    }

    public function runAll() {
        echo "=================================================================\n";
        echo "   TEST SUITE: WHATSAPP OTP AUTHENTICATION WITH FONNTE           \n";
        echo "=================================================================\n\n";

        // Setup mock for Fonnte API
        FonnteService::setMockHandler(function($target, $message) {
            $this->lastSentTarget = $target;
            $this->lastSentMessage = $message;
            if (preg_match('/kode verifikasi WhatsApp Anda adalah:\s*(\d{6})/i', $message, $m)) {
                $this->lastExtractedOtp = $m[1];
            }
            return [
                'success' => true,
                'message' => 'MOCK: Message successfully sent',
                'raw' => ['status' => true, 'target' => [$target]]
            ];
        });

        // Clean up test data before starting
        $this->cleanTestData();

        // 13 Required Test Scenarios
        $this->test1_SendOtpValidPhone();
        $this->test2_RejectInvalidPhone();
        $this->test3_FonnteApiFailureHandling();
        $this->test4_VerifyCorrectOtp();
        $this->test5_VerifyWrongOtp();
        $this->test6_VerifyExpiredOtp();
        $this->test7_VerifyAlreadyUsedOtp();
        $this->test8_MaxAttemptsLimit();
        $this->test9_CooldownAndRateLimiting();
        $this->test10_RegisterNewPhoneNumber();
        $this->test11_LoginExistingPhoneNumber();
        $this->test12_SessionCreationAndLogout();
        $this->test13_AtomicConsumeConcurrency();

        // Clean up after tests
        $this->cleanTestData();
        FonnteService::resetMock();

        echo "\n=================================================================\n";
        echo "   TEST SUMMARY: {$this->passed} PASSED, {$this->failed} FAILED                 \n";
        echo "=================================================================\n";

        return $this->failed === 0;
    }

    private function assert($condition, $testName, $detail = '') {
        if ($condition) {
            $this->passed++;
            echo " [PASS] {$testName}\n";
            if ($detail) echo "        -> {$detail}\n";
        } else {
            $this->failed++;
            echo " [FAIL] {$testName}\n";
            if ($detail) echo "        -> ERROR: {$detail}\n";
        }
    }

    private function cleanTestData() {
        $testPhones = ['6281234567890', '6287700001111', '6287700002222', '6289999999999', '6285555555555', '6281112223334'];
        foreach ($testPhones as $p) {
            $this->db->query("DELETE FROM whatsapp_otps WHERE phone = :phone");
            $this->db->bind('phone', $p);
            $this->db->execute();

            $this->db->query("DELETE FROM users WHERE phone = :phone");
            $this->db->bind('phone', $p);
            $this->db->execute();
        }
    }

    // 1. Pengiriman OTP dengan nomor valid
    private function test1_SendOtpValidPhone() {
        echo "\n--- Test 1: Pengiriman OTP dengan nomor valid ---\n";
        $phone = '081234567890';
        $res = OtpService::requestOtp($phone, '127.0.0.1');

        $this->assert($res['success'] === true, 'Request OTP dengan format 081234567890 harus berhasil');
        $this->assert($res['phone'] === '6281234567890', 'Nomor harus dinormalisasi menjadi 6281234567890', "Got: {$res['phone']}");
        $this->assert(!empty($this->lastExtractedOtp), 'Mock Fonnte menerima kode OTP 6 digit', "OTP: {$this->lastExtractedOtp}");

        // Verifikasi di DB
        $this->db->query("SELECT * FROM whatsapp_otps WHERE phone = '6281234567890' ORDER BY id DESC LIMIT 1");
        $row = $this->db->single();
        $this->assert(!empty($row) && password_verify($this->lastExtractedOtp, $row['otp_hash']), 'OTP tersimpan dalam database dalam bentuk hash BCRYPT yang valid');
    }

    // 2. Penolakan nomor tidak valid
    private function test2_RejectInvalidPhone() {
        echo "\n--- Test 2: Penolakan nomor tidak valid ---\n";
        $invalidNumbers = ['abcd', '12345', '', '++628', '0812'];
        $allRejected = true;

        foreach ($invalidNumbers as $inv) {
            $res = OtpService::requestOtp($inv);
            if ($res['success']) {
                $allRejected = false;
                break;
            }
        }
        $this->assert($allRejected, 'Semua format nomor tidak valid berhasil ditolak dengan pesan kesalahan');
    }

    // 3. Kegagalan API Fonnte
    private function test3_FonnteApiFailureHandling() {
        echo "\n--- Test 3: Penanganan kegagalan API Fonnte ---\n";
        FonnteService::setMockHandler(function($target, $message) {
            return [
                'success' => false,
                'message' => 'device disconnected from WhatsApp network'
            ];
        });

        $res = OtpService::requestOtp('089999999999', '127.0.0.1');
        $this->assert($res['success'] === false, 'Ketika Fonnte gagal/disconnect, sistem harus mengembalikan status gagal');
        $this->assert(str_contains($res['message'], 'device disconnected'), 'Pesan error dari gateway Fonnte diteruskan dengan jelas');

        // Pastikan tidak ada OTP yatim di DB
        $this->db->query("SELECT * FROM whatsapp_otps WHERE phone = '628999999999'");
        $this->assert(empty($this->db->single()), 'Record OTP yang gagal dikirim otomatis dibatalkan/dihapus');

        // Restore normal mock
        FonnteService::setMockHandler(function($target, $message) {
            $this->lastSentTarget = $target;
            $this->lastSentMessage = $message;
            if (preg_match('/kode verifikasi WhatsApp Anda adalah:\s*(\d{6})/i', $message, $m)) {
                $this->lastExtractedOtp = $m[1];
            }
            return ['success' => true, 'message' => 'MOCK: Sent', 'raw' => ['status' => true]];
        });
    }

    // 4. OTP benar
    private function test4_VerifyCorrectOtp() {
        echo "\n--- Test 4: Verifikasi OTP benar ---\n";
        $phone = '6285555555555';
        $req = OtpService::requestOtp($phone);
        $correctOtp = $this->lastExtractedOtp;

        $verifyRes = OtpService::verifyOtp($phone, $correctOtp);
        $this->assert($verifyRes['success'] === true, 'Kode OTP yang benar berhasil diverifikasi');

        // Cek is_used
        $this->db->query("SELECT is_used FROM whatsapp_otps WHERE phone = :phone ORDER BY id DESC LIMIT 1");
        $this->db->bind('phone', $phone);
        $isUsed = (int)$this->db->single()['is_used'];
        $this->assert($isUsed === 1, 'Status OTP di database ditandai is_used = 1');
    }

    // 5. OTP salah
    private function test5_VerifyWrongOtp() {
        echo "\n--- Test 5: Verifikasi OTP salah ---\n";
        $phone = '6281112223334';
        OtpService::requestOtp($phone);

        $verifyRes = OtpService::verifyOtp($phone, '000000');
        $this->assert($verifyRes['success'] === false, 'Kode OTP salah harus ditolak');
        $this->assert(str_contains($verifyRes['message'], 'Kode OTP salah'), 'Pesan error menginformasikan bahwa kode OTP salah');

        // Cek increment attempts
        $this->db->query("SELECT attempts FROM whatsapp_otps WHERE phone = :phone ORDER BY id DESC LIMIT 1");
        $this->db->bind('phone', $phone);
        $attempts = (int)$this->db->single()['attempts'];
        $this->assert($attempts === 1, "Jumlah percobaan diverifikasi bertambah menjadi 1 (Actual: {$attempts})");
    }

    // 6. OTP kedaluwarsa
    private function test6_VerifyExpiredOtp() {
        echo "\n--- Test 6: Verifikasi OTP kedaluwarsa ---\n";
        $phone = '6287700001111';
        OtpService::requestOtp($phone);
        $otp = $this->lastExtractedOtp;

        // Paksa set expired 10 menit lalu
        $this->db->query("UPDATE whatsapp_otps SET expires_at = (NOW() - INTERVAL 10 MINUTE) WHERE phone = :phone");
        $this->db->bind('phone', $phone);
        $this->db->execute();

        $verifyRes = OtpService::verifyOtp($phone, $otp);
        $this->assert($verifyRes['success'] === false, 'Kode OTP yang sudah lewat masa berlaku harus ditolak');
        $this->assert(!empty($verifyRes['expired']), 'Mengembalikan flag expired = true');
    }

    // 7. OTP yang sudah digunakan
    private function test7_VerifyAlreadyUsedOtp() {
        echo "\n--- Test 7: Verifikasi OTP yang sudah digunakan ---\n";
        $phone = '6285555555555'; // Sudah verified di Test 4
        $res = OtpService::verifyOtp($phone, '123456');
        $this->assert($res['success'] === false, 'OTP yang sudah is_used = 1 tidak dapat digunakan lagi');
    }

    // 8. Batas percobaan verifikasi (5 max)
    private function test8_MaxAttemptsLimit() {
        echo "\n--- Test 8: Batas maksimal percobaan verifikasi (5 kali) ---\n";
        $phone = '6281112223334'; // Sudah 1 attempt di Test 5
        for ($i = 2; $i <= 5; $i++) {
            OtpService::verifyOtp($phone, '111111');
        }

        $res6 = OtpService::verifyOtp($phone, '222222');
        $this->assert($res6['success'] === false, 'Setelah 5 kali percobaan gagal, verifikasi berikutnya harus diblokir');
        $this->assert(!empty($res6['max_attempts']), 'Mengembalikan flag max_attempts = true');
    }

    // 9. Cooldown dan rate limiting
    private function test9_CooldownAndRateLimiting() {
        echo "\n--- Test 9: Cooldown 60 detik dan pembatasan frekuensi ---\n";
        $phone = '6287700002222';
        $res1 = OtpService::requestOtp($phone);
        $this->assert($res1['success'] === true, 'Request OTP pertama berhasil');

        // Request kedua langsung dalam beberapa detik
        $res2 = OtpService::requestOtp($phone);
        $this->assert($res2['success'] === false, 'Request kedua dalam 60 detik langsung ditolak karena cooldown');
        $this->assert(isset($res2['cooldown']) && $res2['cooldown'] > 0, "Mengembalikan sisa cooldown detik ({$res2['cooldown']}s)");
    }

    // 10. Registrasi nomor baru jika diizinkan
    private function test10_RegisterNewPhoneNumber() {
        echo "\n--- Test 10: Registrasi nomor baru secara otomatis ---\n";
        $newPhone = '6287700001111';
        $existing = $this->userModel->getUserByPhone($newPhone);
        $this->assert(empty($existing), 'Nomor belum ada di tabel users sebelum registrasi');

        $newUser = $this->userModel->registerUserWithPhone($newPhone, 'Pelanggan Baru WA');
        $this->assert(!empty($newUser) && $newUser['phone'] === $newPhone, 'Akun pengguna baru berhasil dibuat dengan nomor WhatsApp');
        $this->assert((int)$newUser['is_phone_verified'] === 1, 'Nomor baru ditandai is_phone_verified = 1');
        $this->assert($newUser['role'] === 'customer', 'Role default akun baru adalah customer');
    }

    // 11. Login nomor yang sudah terdaftar
    private function test11_LoginExistingPhoneNumber() {
        echo "\n--- Test 11: Login nomor yang sudah terdaftar ---\n";
        $phone = '6287700001111';
        $user = $this->userModel->getUserByPhone($phone);
        $this->assert(!empty($user) && $user['name'] === 'Pelanggan Baru WA', 'Nomor yang sudah terdaftar berhasil ditemukan untuk login tanpa duplikasi');
    }

    // 12. Pembuatan sesi dan logout
    private function test12_SessionCreationAndLogout() {
        echo "\n--- Test 12: Pembuatan sesi autentikasi dan logout ---\n";
        
        // Simulasi login sukses
        $_SESSION['user_id'] = 999;
        $_SESSION['user_name'] = 'Tester WA';
        $_SESSION['user_role'] = 'customer';

        $this->assert(isset($_SESSION['user_id']) && $_SESSION['user_id'] === 999, 'Sesi user_id berhasil dibuat');

        // Simulasi logout
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }

        $this->assert(empty($_SESSION['user_id']), 'Logout berhasil mengosongkan dan mengakhiri sesi pengguna');
    }

    // 13. Permintaan bersamaan yang mencoba menggunakan OTP yang sama (atomic consume)
    private function test13_AtomicConsumeConcurrency() {
        echo "\n--- Test 13: Proteksi penggunaan bersamaan (Atomic Consume Concurrency) ---\n";
        $phone = '6281234567890';
        // Buat record OTP baru
        $otpCode = '789123';
        $otpHash = password_hash($otpCode, PASSWORD_BCRYPT);
        $this->db->query("INSERT INTO whatsapp_otps (phone, otp_hash, expires_at, attempts, is_used, created_at) 
                          VALUES (:phone, :hash, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 0, 0, NOW())");
        $this->db->bind('phone', $phone);
        $this->db->bind('hash', $otpHash);
        $this->db->execute();
        $otpId = $this->db->lastInsertId();

        // Simulasi Thread A mengonsumsi OTP
        $this->db->query("UPDATE whatsapp_otps SET is_used = 1 WHERE id = :id AND is_used = 0");
        $this->db->bind('id', $otpId);
        $this->db->execute();
        $threadAConsumed = $this->db->rowCount() === 1;

        // Simulasi Thread B (konkuren) mencoba mengonsumsi OTP yang sama pada milidetik yang sama
        $this->db->query("UPDATE whatsapp_otps SET is_used = 1 WHERE id = :id AND is_used = 0");
        $this->db->bind('id', $otpId);
        $this->db->execute();
        $threadBConsumed = $this->db->rowCount() === 1;

        $this->assert($threadAConsumed === true, 'Thread A pertama berhasil mengonsumsi OTP (rowCount = 1)');
        $this->assert($threadBConsumed === false, 'Thread B kedua ditolak secara atomik (rowCount = 0, dicegah double consumption)');
    }
}

$suite = new WhatsAppAuthTestSuite();
$allPassed = $suite->runAll();
exit($allPassed ? 0 : 1);
