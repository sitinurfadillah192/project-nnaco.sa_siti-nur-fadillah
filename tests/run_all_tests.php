<?php
/**
 * NNACOSA — Master Test Suite Runner
 * 
 * Used for Local Verification and Automated CI/CD Pipelines (GitHub Actions)
 */

echo "========================================================================\n";
echo "   NNACOSA E-COMMERCE PLATFORM — AUTOMATED CI/CD TEST SUITE RUNNER       \n";
echo "========================================================================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "========================================================================\n\n";

$testFiles = [
    'test_customer_reviews.php'       => 'Fitur Ulasan & Rating Pelanggan (Verified Reviews)',
    'test_system_settings.php'        => 'Keamanan Sistem, AES-256-GCM Vault & Multi-Provider AI',
    'test_register_whatsapp_otp.php'  => 'Registrasi Pengguna & Verifikasi WhatsApp OTP',
    'test_whatsapp_auth.php'          => 'Lifecycle Autentikasi WhatsApp & Atomic OTP Guard',
    'test_user_management.php'        => 'Manajemen Pengguna & Penegakan RBAC Multi-Role',
    'test_chatbot_controller_rbac.php'=> 'Otorisasi & Keamanan Chatbot Controller',
    'test_chatbot_history.php'        => 'Penyimpanan Riwayat Percakapan & Audit Logging',
    'test_order_notifications.php'    => 'Dispatcher Notifikasi Status Pesanan',
    'test_order_whatsapp_only.php'    => 'Eksklusivitas Saluran Notifikasi WhatsApp',
    'test_user_controller_http.php'   => 'Verifikasi HTTP Endpoint Controller Pengguna'
];

$results = [];
$totalSuites = count($testFiles);
$passedSuites = 0;
$failedSuites = 0;

$phpBinary = PHP_BINARY ?: 'php';

foreach ($testFiles as $file => $description) {
    $filePath = __DIR__ . '/' . $file;
    if (!file_exists($filePath)) {
        echo "[ERROR] Berkas test {$file} tidak ditemukan!\n";
        $results[$file] = ['status' => 'MISSING', 'description' => $description, 'code' => 1];
        $failedSuites++;
        continue;
    }

    echo ">>> Menjalankan: {$file} ({$description})...\n";
    $output = [];
    $returnVar = 0;
    
    // Jalankan setiap suite pengujian di proses terisolasi
    exec("\"{$phpBinary}\" \"{$filePath}\"", $output, $returnVar);

    // Tampilkan cuplikan hasil jika ada error
    if ($returnVar === 0) {
        echo "    Status: [PASSED] (Exit Code: 0)\n\n";
        $results[$file] = ['status' => 'PASSED', 'description' => $description, 'code' => 0];
        $passedSuites++;
    } else {
        echo "    Status: [FAILED] (Exit Code: {$returnVar})\n";
        echo "    Output Cuplikan:\n";
        echo "    " . implode("\n    ", array_slice($output, -10)) . "\n\n";
        $results[$file] = ['status' => 'FAILED', 'description' => $description, 'code' => $returnVar];
        $failedSuites++;
    }
}

echo "========================================================================\n";
echo "   RINGKASAN HASIL EKSEKUSI SUITE PENGUJIAN CI/CD                       \n";
echo "========================================================================\n";
printf("%-35s | %-10s | %s\n", "Berkas Test", "Status", "Keterangan Modul");
echo str_repeat("-", 80) . "\n";

foreach ($results as $file => $res) {
    printf("%-35s | %-10s | %s\n", $file, $res['status'], $res['description']);
}

echo str_repeat("=", 80) . "\n";
echo "Total Suite: {$totalSuites} | Lulus: {$passedSuites} | Gagal: {$failedSuites}\n";

if ($failedSuites === 0) {
    echo "\n>>> STATUS AKHIR: SEMUA PENGUJIAN BERHASIL (SUCCESS - PIPELINE HIJAU) <<<\n";
    echo "========================================================================\n";
    exit(0);
} else {
    echo "\n>>> STATUS AKHIR: TERDAPAT KEGAGALAN PENGUJIAN (PIPELINE MERAH) <<<\n";
    echo "========================================================================\n";
    exit(1);
}
