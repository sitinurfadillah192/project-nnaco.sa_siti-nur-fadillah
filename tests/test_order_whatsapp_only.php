<?php
/**
 * Test Suite: Verifikasi Notifikasi Order Khusus WhatsApp (Email Dihapus)
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/FonnteService.php';
require_once __DIR__ . '/../app/repositories/OrderNotificationRepository.php';
require_once __DIR__ . '/../app/services/OrderNotificationService.php';

echo "============================================================\n";
echo "TEST SUITE: NOTIFIKASI ORDER KHUSUS WHATSAPP (NO EMAIL)     \n";
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
// Test 1: Verifikasi File View adminorder/detail.php
// -----------------------------------------------------------------
$detailView = file_get_contents(__DIR__ . '/../app/views/adminorder/detail.php');

assertCondition("Judul card adalah 'Notifikasi WhatsApp'", 
    stripos($detailView, 'Notifikasi WhatsApp') !== false);

assertCondition("Teks 'Notifikasi Email' sudah tidak ada di view", 
    stripos($detailView, 'Notifikasi Email') === false);

assertCondition("Badge 'Email: Terkirim' sudah tidak ada di view", 
    stripos($detailView, 'Email: Terkirim') === false);

assertCondition("Tombol 'Preview Email' sudah tidak ada di view", 
    stripos($detailView, 'Preview Email') === false);

assertCondition("Tombol 'Coba Kirim Ulang Email' sudah tidak ada di view", 
    stripos($detailView, 'Coba Kirim Ulang Email') === false);

assertCondition("Tombol 'Kirim Notif WA' tetap ada di view", 
    stripos($detailView, 'Kirim Notif WA') !== false);

assertCondition("Badge status WhatsApp ('WA Terkirim', 'WA Gagal') ada di view", 
    stripos($detailView, 'WA Terkirim') !== false && stripos($detailView, 'WA Gagal') !== false);

// -----------------------------------------------------------------
// Test 2: Perubahan Status Pesanan Hanya Mengirim Notifikasi WhatsApp
// -----------------------------------------------------------------
$mockCalled = false;
$receivedTarget = null;
$receivedMessage = null;

FonnteService::setMockHandler(function($target, $msg) use (&$mockCalled, &$receivedTarget, &$receivedMessage) {
    $mockCalled = true;
    $receivedTarget = $target;
    $receivedMessage = $msg;
    return ['success' => true, 'message' => 'Notif WA mock berhasil dikirim.'];
});

// Setup dummy order with phone
$db = new Database();
$db->query("SELECT id FROM order_notification_logs ORDER BY id DESC LIMIT 1");
$latestNotif = $db->single();

if ($latestNotif) {
    $service = new OrderNotificationService();
    $res = $service->sendWhatsAppNotification($latestNotif['id'], '081953319613');
    assertCondition("OrderNotificationService::sendWhatsAppNotification() berhasil", 
        !empty($res['success']));

    if ($mockCalled) {
        assertCondition("Pesan WhatsApp terkirim ke target nomor", !empty($receivedTarget));
        assertCondition("Isi pesan WhatsApp memuat nama brand NNACOSA", stripos($receivedMessage, 'NNACOSA') !== false);
        assertCondition("Isi pesan WhatsApp memuat nomor pesanan", stripos($receivedMessage, 'Nomor Pesanan') !== false);
    }
}

// -----------------------------------------------------------------
// Test 3: Pastikan notifyStatusChange tidak mengirim email
// -----------------------------------------------------------------
$serviceRefl = new ReflectionClass(OrderNotificationService::class);
$notifyMethod = $serviceRefl->getMethod('notifyStatusChange');
$filename = $serviceRefl->getFileName();
$fileLines = file($filename);
$notifyMethodSource = implode('', array_slice($fileLines, $notifyMethod->getStartLine() - 1, $notifyMethod->getEndLine() - $notifyMethod->getStartLine() + 1));

assertCondition("notifyStatusChange() tidak lagi memanggil processNotification (email)", 
    stripos($notifyMethodSource, 'processNotification') === false);

assertCondition("notifyStatusChange() memanggil sendWhatsAppNotification", 
    stripos($notifyMethodSource, 'sendWhatsAppNotification') !== false);

FonnteService::resetMock();

echo "\n============================================================\n";
echo "Ringkasan Hasil Test: Total=" . ($passed + $failed) . ", Lulus=$passed, Gagal=$failed\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
