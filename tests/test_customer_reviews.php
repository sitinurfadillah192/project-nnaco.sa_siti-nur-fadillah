<?php
/**
 * Test Suite: Fitur Customer Rating & Ulasan (Review Model & Business Logic)
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Review.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/User.php';

echo "============================================================\n";
echo "NNACOSA — Customer Rating & Reviews Test Suite\n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $description\n";
        $passed++;
    } else {
        echo " [FAIL] $description\n";
        $failed++;
    }
}

$db = new Database();
$reviewModel = new Review();
$productModel = new Product();
$userModel = new User();

// Setup: Temukan atau buat User, Kategori, Produk, dan Order untuk pengujian
$suffix = time();
$testEmail = "reviewer_{$suffix}@nnacosa.test";
$testPhone = "62811" . substr(strval($suffix), -7);

// 1. Buat Test User
$testUserId = $userModel->createUser([
    'name' => 'Rina Permata Test',
    'email' => $testEmail,
    'phone' => $testPhone,
    'role_id' => 1,
    'password' => 'Secret123!'
]);

// 2. Ambil atau buat Test Kategori & Produk
$db->query("SELECT id FROM categories LIMIT 1");
$cat = $db->single();
$catId = $cat ? $cat['id'] : 1;

$db->query("
    INSERT INTO products (category_id, name, description, price, stock, created_at)
    VALUES (:cat, 'Cincin Safir Biru Test Review', 'Cincin safir elegan untuk tes ulasan', 450000, 15, NOW())
");
$db->bind(':cat', $catId);
$db->execute();
$testProductId = $db->lastInsertId();

// 3. Buat Test Order milik Test User
$db->query("
    INSERT INTO orders (user_id, total_amount, shipping_address, phone, status, created_at)
    VALUES (:uid, 450000, 'Jl. Berlian No. 12, Jakarta', :phone, 'completed', NOW())
");
$db->bind(':uid', $testUserId);
$db->bind(':phone', $testPhone);
$db->execute();
$testOrderId = $db->lastInsertId();

// 4. Masukkan Produk ke dalam order_items
$db->query("
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES (:oid, :pid, 1, 450000)
");
$db->bind(':oid', $testOrderId);
$db->bind(':pid', $testProductId);
$db->execute();

try {
    // --- TEST 1: Verifikasi Produk & Detail ---
    $detail = $productModel->getProductDetail($testProductId);
    assertTest("Product::getProductDetail() mengambil data produk dan kategori", 
        $detail && $detail['name'] === 'Cincin Safir Biru Test Review');

    // --- TEST 2: Penolakan Ulasan untuk Pesanan yang Tidak Sah / Produk Tidak Dibeli ---
    $db->query("
        INSERT INTO products (category_id, name, description, price, stock, created_at)
        VALUES (3, 'Gelang Emas Palsu Test', 'Tidak pernah dibeli', 200000, 5, NOW())
    ");
    $db->execute();
    $unboughtProductId = $db->lastInsertId();

    $invalidReviewRes = $reviewModel->saveReview($testUserId, $unboughtProductId, $testOrderId, 5, 'Bagus tapi tidak pernah beli');
    assertTest("Review::saveReview() menolak ulasan untuk produk yang tidak ada di dalam pesanan", 
        $invalidReviewRes['success'] === false);

    // --- TEST 3: Pembuatan Ulasan Baru Pertama Kali (Rating 5) ---
    $createRes = $reviewModel->saveReview($testUserId, $testProductId, $testOrderId, 5, 'Kilau cincinnya sangat menakjubkan dan ukurannya pas!');
    assertTest("Review::saveReview() berhasil menyimpan ulasan baru", 
        $createRes['success'] === true && $createRes['action'] === 'created' && !empty($createRes['review_id']));
    $reviewId = $createRes['review_id'];

    // --- TEST 4: Idempotensi / Pembaruan Ulasan (Edit Review) ---
    $updateRes = $reviewModel->saveReview($testUserId, $testProductId, $testOrderId, 4, 'Diperbarui: Pengiriman cepat, bahan sangat premium.');
    assertTest("Review::saveReview() memperbarui ulasan yang sudah ada tanpa menduplikasi baris", 
        $updateRes['success'] === true && $updateRes['action'] === 'updated' && (int)$updateRes['review_id'] === (int)$reviewId);

    // --- TEST 5: getReviewByOrderProduct ---
    $singleRev = $reviewModel->getReviewByOrderProduct($testUserId, $testProductId, $testOrderId);
    assertTest("Review::getReviewByOrderProduct() mengambil ulasan sesuai user, produk, order", 
        $singleRev && (int)$singleRev['rating'] === 4 && strpos($singleRev['comment'], 'Diperbarui:') !== false);

    // --- TEST 6: getReviewsForOrder ---
    $orderReviews = $reviewModel->getReviewsForOrder($testOrderId, $testUserId);
    assertTest("Review::getReviewsForOrder() mengembalikan array ulasan terindeks dengan product_id", 
        isset($orderReviews[$testProductId]) && (int)$orderReviews[$testProductId]['rating'] === 4);

    // --- TEST 7: Summary Rating Produk (getProductRatingSummary) ---
    $summary = $reviewModel->getProductRatingSummary($testProductId);
    assertTest("Review::getProductRatingSummary() menghitung rata-rata dan distribusi bintang dengan benar", 
        $summary['total_reviews'] === 1 && (float)$summary['avg_rating'] === 4.0 && $summary['counts'][4] === 1);

    // --- TEST 8: getAverageRatingsForProducts (Batch query untuk katalog) ---
    $batchRatings = $reviewModel->getAverageRatingsForProducts([$testProductId, $unboughtProductId]);
    assertTest("Review::getAverageRatingsForProducts() mengembalikan batch rating akurat untuk katalog", 
        isset($batchRatings[$testProductId]) && $batchRatings[$testProductId]['avg'] == 4.0 && $batchRatings[$testProductId]['total'] === 1);

    // --- TEST 9: getFeaturedReviews (Rating >= 4 untuk Beranda) ---
    $featured = $reviewModel->getFeaturedReviews(10);
    $foundInFeatured = false;
    foreach ($featured as $f) {
        if ((int)$f['id'] === (int)$reviewId) {
            $foundInFeatured = true;
            break;
        }
    }
    assertTest("Review::getFeaturedReviews() menampilkan ulasan bintang 4-5 untuk testimoni beranda", $foundInFeatured);

    // --- TEST 10: Admin getAllReviewsAdmin dan Filter ---
    $adminList = $reviewModel->getAllReviewsAdmin(10, 0, 'Safir Biru', 4);
    assertTest("Review::getAllReviewsAdmin() mendukung filter search dan filter bintang", 
        count($adminList) >= 1 && (int)$adminList[0]['id'] === (int)$reviewId);

    $adminCount = $reviewModel->countReviewsAdmin('Safir Biru', 4);
    assertTest("Review::countReviewsAdmin() menghitung total data ulasan sesuai kriteria", $adminCount >= 1);

    // --- TEST 11: Keamanan Penghapusan Ulasan (Delete Authorization) ---
    $fakeOtherUserId = 99999999;
    $unauthorizedDelete = $reviewModel->deleteReview($reviewId, $fakeOtherUserId, false);
    $checkStillExists = $reviewModel->getReviewByOrderProduct($testUserId, $testProductId, $testOrderId);
    assertTest("Review::deleteReview() menolak penghapusan oleh user yang bukan pemilik ulasan", 
        !empty($checkStillExists));

    // --- TEST 12: Penghapusan Berhasil oleh Admin atau Pemilik ---
    $authorizedDelete = $reviewModel->deleteReview($reviewId, $testUserId, false);
    $checkDeleted = $reviewModel->getReviewByOrderProduct($testUserId, $testProductId, $testOrderId);
    assertTest("Review::deleteReview() berhasil menghapus ulasan oleh pemilik", 
        $authorizedDelete && empty($checkDeleted));

} finally {
    // --- GUARANTEED CLEANUP OF ALL TEST DATA ---
    if (!empty($reviewId)) {
        $db->query("DELETE FROM reviews WHERE id = :rid");
        $db->bind(':rid', $reviewId);
        $db->execute();
    }
    if (!empty($testOrderId)) {
        $db->query("DELETE FROM order_items WHERE order_id = :oid");
        $db->bind(':oid', $testOrderId);
        $db->execute();

        $db->query("DELETE FROM orders WHERE id = :oid");
        $db->bind(':oid', $testOrderId);
        $db->execute();
    }
    $pids = array_filter([$testProductId ?? null, $unboughtProductId ?? null]);
    if (!empty($pids)) {
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $db->query("DELETE FROM products WHERE id IN ($placeholders)");
        foreach (array_values($pids) as $idx => $pid) {
            $db->bind($idx + 1, $pid);
        }
        $db->execute();
    }
    if (!empty($testUserId)) {
        $db->query("DELETE FROM users WHERE id = :uid");
        $db->bind(':uid', $testUserId);
        $db->execute();
    }
}

echo "\n============================================================\n";
echo "HASIL PENGUJIAN: $passed PASSED, $failed FAILED\n";
echo "============================================================\n";
exit($failed > 0 ? 1 : 0);
