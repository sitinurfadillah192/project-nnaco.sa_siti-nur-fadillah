<?php
class AiController extends Controller {
    
    private $ai;

    public function __construct() {
        try {
            $this->ai = new AiService();
        } catch (Exception $e) {
            // Tangkap exception tanpa menghentikan aplikasi secara total.
            // Biarkan method handle API key yang hilang dengan anggun.
        }
    }

    // ==========================================
    // 1. AI CUSTOMER SERVICE CHATBOT (API Endpoint)
    // ==========================================
    public function chat_api() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request method']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $userMessage = $input['message'] ?? '';

        if (empty($userMessage)) {
            echo json_encode(['error' => 'Message is empty']);
            exit;
        }

        // Conversation & Session Tracking
        $conversationId = !empty($input['conversation_id']) ? trim($input['conversation_id']) : null;
        if (empty($conversationId)) {
            if (!empty($_SESSION['chat_conversation_id'])) {
                $conversationId = $_SESSION['chat_conversation_id'];
            } else {
                $conversationId = 'conv_' . bin2hex(random_bytes(10));
            }
        }
        $_SESSION['chat_conversation_id'] = $conversationId;

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $sessionId = session_id() ?: null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        $db = new Database();

        // 1. Data Produk & Kategori dari Database
        $db->query("SELECT p.name, p.price, p.stock, p.description, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY p.id ASC");
        $products = $db->resultSet();
        $productList = "";
        $totalStock = 0;
        foreach ($products as $p) {
            $cat = !empty($p['category_name']) ? $p['category_name'] : 'Aksesoris';
            $desc = !empty($p['description']) ? $p['description'] : '-';
            $totalStock += (int)$p['stock'];
            $productList .= "- {$p['name']} (Kategori: {$cat}, Harga: Rp " . number_format($p['price'], 0, ',', '.') . ", Stok: {$p['stock']} unit, Deskripsi: {$desc})\n";
        }

        // 2. Data Kategori dari Database
        $db->query("SELECT name, description FROM categories ORDER BY id ASC");
        $categories = $db->resultSet();
        $categoryList = "";
        foreach ($categories as $c) {
            $categoryList .= "- {$c['name']}: " . ($c['description'] ?? '') . "\n";
        }

        // 3. Kupon / Promo Diskon Aktif dari Database
        $db->query("SELECT code, discount_type, discount_value, min_purchase, valid_until 
                    FROM coupons 
                    WHERE is_active = 1 AND valid_until >= CURDATE()");
        $coupons = $db->resultSet();
        $couponList = "";
        foreach ($coupons as $cp) {
            $discount = ($cp['discount_type'] === 'percentage') ? $cp['discount_value'] . '%' : 'Rp ' . number_format($cp['discount_value'], 0, ',', '.');
            $couponList .= "- Kode Kupon: {$cp['code']} (Diskon {$discount}, Min. Belanja: Rp " . number_format($cp['min_purchase'], 0, ',', '.') . ", Berlaku s/d {$cp['valid_until']})\n";
        }

        // 4. Data Pengguna / User Terdaftar di Sistem
        $db->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $db->single()['total'] ?? 0;

        $db->query("SELECT COALESCE(r.name, 'customer') as role_name, COUNT(u.id) as count 
                    FROM users u 
                    LEFT JOIN roles r ON u.role_id = r.id 
                    GROUP BY r.name");
        $usersByRole = $db->resultSet();
        $userRoleBreakdown = [];
        foreach ($usersByRole as $ubr) {
            $userRoleBreakdown[] = "{$ubr['count']} " . ucfirst($ubr['role_name']);
        }
        $userSummary = "Total Pengguna Terdaftar: {$totalUsers} pengguna (" . implode(", ", $userRoleBreakdown) . ")";

        // 5. DATA PESANAN & TRANSAKSI SISTEM ADMIN (DATA MASUK & KELUAR TOKO)
        // a. Total Pemasukan Valid (completed & shipped)
        $db->query("SELECT SUM(total_amount) as total_income FROM orders WHERE status IN ('completed', 'shipped')");
        $totalIncome = $db->single()['total_income'] ?? 0;

        // b. Total Semua Pesanan & Nilai Transaksi
        $db->query("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_amount FROM orders");
        $orderStats = $db->single();
        $totalOrdersCount = (int)($orderStats['total_orders'] ?? 0);
        $totalOrdersAmount = (float)($orderStats['total_amount'] ?? 0);

        // c. Breakdown Status Pesanan
        $db->query("SELECT status, COUNT(*) as count, SUM(total_amount) as amount FROM orders GROUP BY status");
        $statusRows = $db->resultSet();
        $statusCounts = [
            'pending' => 0,
            'paid' => 0,
            'processing' => 0,
            'shipped' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        foreach ($statusRows as $sr) {
            $statusCounts[$sr['status']] = (int)$sr['count'];
        }
        // Pesanan Masuk (yang baru masuk atau sedang ditangani: pending/paid/processing)
        $incomingOrdersCount = $statusCounts['pending'] + $statusCounts['paid'] + $statusCounts['processing'];

        // d. Daftar Seluruh Pesanan Toko (Riwayat Transaksi Admin)
        $db->query("SELECT o.id, o.user_id, u.name as customer_name, u.email as customer_email,
                           o.total_amount, o.status, o.shipping_address, o.created_at,
                           pay.payment_method, pay.status as payment_status
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    LEFT JOIN payments pay ON o.id = pay.order_id
                    ORDER BY o.created_at DESC LIMIT 15");
        $allOrders = $db->resultSet();

        $adminOrderList = "";
        if (!empty($allOrders)) {
            foreach ($allOrders as $ao) {
                $orderCode = "#ORD-" . str_pad($ao['id'], 5, '0', STR_PAD_LEFT);
                $db->query("SELECT oi.quantity, oi.price, p.name as product_name 
                            FROM order_items oi 
                            LEFT JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = :order_id");
                $db->bind('order_id', $ao['id']);
                $items = $db->resultSet();
                $itemStrs = [];
                foreach ($items as $it) {
                    $itemStrs[] = "{$it['product_name']} ({$it['quantity']}x)";
                }
                $itemSummary = !empty($itemStrs) ? implode(", ", $itemStrs) : "Item data";
                $payMethod = !empty($ao['payment_method']) ? $ao['payment_method'] : 'Belum memilih';

                $adminOrderList .= "  * {$orderCode} | Tgl: {$ao['created_at']} | Pelanggan: {$ao['customer_name']} | Total: Rp " . number_format($ao['total_amount'], 0, ',', '.') . " | Status: " . strtoupper($ao['status']) . " | Produk: {$itemSummary} | Pembayaran: {$payMethod}\n";
            }
        } else {
            $adminOrderList = "  * Belum ada data pesanan di sistem.\n";
        }

        // e. Data Barang Keluar (Produk Terjual / Sedang Dipesan)
        $db->query("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as total_revenue
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.status != 'cancelled'
                    GROUP BY p.id
                    ORDER BY total_sold DESC");
        $soldProducts = $db->resultSet();
        $soldList = "";
        $totalItemsSold = 0;
        if (!empty($soldProducts)) {
            foreach ($soldProducts as $sp) {
                $totalItemsSold += (int)$sp['total_sold'];
                $soldList .= "  * {$sp['name']}: {$sp['total_sold']} unit terjual (Senilai Rp " . number_format($sp['total_revenue'], 0, ',', '.') . ")\n";
            }
        } else {
            $soldList = "  * Belum ada barang yang terjual.\n";
        }

        // 6. Identitas Pengguna & Riwayat Transaksi (dari Session & Database)
        $isLoggedIn = isset($_SESSION['user_id']);
        $userInfo = "";
        $userName = "";
        $userEmail = "";
        $userRole = "customer";

        if ($isLoggedIn) {
            $userId = $_SESSION['user_id'];
            $db->query("SELECT u.name, u.email, COALESCE(r.name, 'customer') as role 
                        FROM users u 
                        LEFT JOIN roles r ON u.role_id = r.id 
                        WHERE u.id = :id");
            $db->bind('id', $userId);
            $userDb = $db->single();
            $userName = $userDb['name'] ?? ($_SESSION['user_name'] ?? 'Pengguna');
            $userEmail = $userDb['email'] ?? '-';
            $userRole = strtolower($userDb['role'] ?? ($_SESSION['user_role'] ?? 'customer'));

            switch ($userRole) {
                case 'admin':
                    $roleTitle = 'ADMIN (Administrator Sistem / Toko)';
                    $userGreetingRule = "Jawablah dengan tegas dan hormat bahwa pengguna adalah ADMIN (Administrator Sistem / Toko NNACO SA) bernama '{$userName}' (email: {$userEmail}) yang saat ini sedang login di akunnya.";
                    break;
                case 'operator':
                    $roleTitle = 'OPERATOR (Operator Toko)';
                    $userGreetingRule = "Jawablah bahwa pengguna adalah OPERATOR toko NNACO SA bernama '{$userName}' (email: {$userEmail}) yang saat ini sedang login di akunnya.";
                    break;
                case 'owner':
                    $roleTitle = 'OWNER (Pemilik Toko)';
                    $userGreetingRule = "Jawablah dengan penuh rasa hormat bahwa pengguna adalah OWNER (Pemilik Toko NNACO SA) bernama '{$userName}' (email: {$userEmail}) yang saat ini sedang login di akunnya.";
                    break;
                case 'customer':
                default:
                    $roleTitle = 'CUSTOMER (Pelanggan / Pembeli Toko)';
                    $userGreetingRule = "Jawablah bahwa pengguna adalah CUSTOMER (pelanggan toko NNACO SA) bernama '{$userName}' (email: {$userEmail}) yang saat ini sedang login di akunnya.";
                    break;
            }

            $isStaff = in_array($userRole, ['admin', 'operator', 'owner']);

            // Pesanan customer pribadi
            $db->query("SELECT o.id, o.total_amount, o.status, o.shipping_address, o.created_at, 
                               pay.payment_method, pay.status as payment_status 
                        FROM orders o 
                        LEFT JOIN payments pay ON o.id = pay.order_id 
                        WHERE o.user_id = :user_id 
                        ORDER BY o.created_at DESC LIMIT 5");
            $db->bind('user_id', $userId);
            $userOrders = $db->resultSet();
            $orderList = "";
            if (!empty($userOrders)) {
                foreach ($userOrders as $ord) {
                    $payStatus = !empty($ord['payment_status']) ? " ({$ord['payment_status']})" : "";
                    $payInfo = !empty($ord['payment_method']) ? "Pembayaran: {$ord['payment_method']}{$payStatus}" : "Pembayaran belum dipilih";
                    $orderList .= "  * Order #{$ord['id']} | Tanggal: {$ord['created_at']} | Total: Rp " . number_format($ord['total_amount'], 0, ',', '.') . " | Status Pesanan: {$ord['status']} | {$payInfo} | Alamat: {$ord['shipping_address']}\n";
                }
            } else {
                $orderList = "  * Belum ada riwayat pesanan pribadi.\n";
            }

            // Keranjang belanja customer pribadi
            $db->query("SELECT ci.quantity, p.name, p.price 
                        FROM cart_items ci 
                        JOIN carts c ON ci.cart_id = c.id 
                        JOIN products p ON ci.product_id = p.id 
                        WHERE c.user_id = :user_id");
            $db->bind('user_id', $userId);
            $cartItems = $db->resultSet();
            $cartList = "";
            if (!empty($cartItems)) {
                foreach ($cartItems as $ci) {
                    $cartList .= "  * {$ci['name']} ({$ci['quantity']} pcs @ Rp " . number_format($ci['price'], 0, ',', '.') . ")\n";
                }
            } else {
                $cartList = "  * Keranjang belanja kosong saat ini.\n";
            }

            $roleSpecificNote = $isStaff 
                ? "Pengguna adalah PENGELOLA SISTEM (" . strtoupper($userRole) . "). Jika pengguna menanyakan jumlah pesanan, pesanan masuk, total pemasukan, data penjualan, atau transaksi keluar/masuk, JAWABLAH MENGGUNAKAN DATA MANAJEMEN PESANAN TOKO secara keseluruhan."
                : "Pengguna adalah CUSTOMER. Jika pengguna menanyakan 'pesanan saya' atau 'status order saya', jawablah riwayat pesanan pribadinya.";

            $userInfo = "INFORMASI PENGGUNA SAAT INI (SEDANG LOGIN):
- Peran Akun (Role): {$roleTitle}
- Nama: {$userName}
- Email: {$userEmail}
- Panduan Peran: {$roleSpecificNote}
- Riwayat Pesanan Pribadi Pengguna:
{$orderList}
- Isi Keranjang Belanja Pengguna:
{$cartList}";
        } else {
            $roleTitle = "PENGUNJUNG / TAMU (Belum Login)";
            $userGreetingRule = "Jawablah bahwa pengguna adalah seorang CUSTOMER (pelanggan / pembeli toko kami di NNACO SA). Sampaikan saat ini sedang terhubung sebagai pengunjung (tamu belum login).";
            $userInfo = "INFORMASI PENGGUNA SAAT INI (BELUM LOGIN / TAMU):
- Peran Akun: Pengunjung / Tamu (Calon Customer)
- Pengguna belum login. Jika pengguna menanyakan pesanan pribadi atau keranjang belanja, sampaikan bahwa ia dapat login terlebih dahulu ke akunnya.";
        }

        // 7. System Prompt Lengkap
        $systemPrompt = "Kamu adalah 'NNACO Assistant', AI Assistant & Customer Service resmi dari toko aksesoris 'NNACO SA'.
Lawan bicara kamu saat ini adalah: {$roleTitle} " . ($isLoggedIn ? "bernama '{$userName}' (email: {$userEmail}, status: Sedang Login)." : "(status: Pengunjung / Tamu belum login).") . "

{$userInfo}

DATA DATABASE NNACO SA:
1. Jam Operasional Toko:
   Senin - Sabtu, pukul 09:00 - 21:00 WIB (Pemesanan online website aktif 24 jam).

2. Data Pengguna / User Terdaftar di Sistem:
   - {$userSummary}

3. Data Manajemen Pesanan & Transaksi Toko (Data Masuk & Keluar di Sistem Admin):
   - Total Keseluruhan Pesanan di Sistem: {$totalOrdersCount} pesanan (Total nilai transaksi: Rp " . number_format($totalOrdersAmount, 0, ',', '.') . ")
   - Jumlah Pesanan Masuk (Aktif / Butuh Diproses): {$incomingOrdersCount} pesanan (Processing: {$statusCounts['processing']}, Pending: {$statusCounts['pending']}, Paid: {$statusCounts['paid']})
   - Rincian Status Pesanan Toko:
     * Processing (Sedang Diproses): {$statusCounts['processing']} pesanan
     * Shipped (Sedang Dikirim): {$statusCounts['shipped']} pesanan
     * Completed (Selesai): {$statusCounts['completed']} pesanan
     * Pending (Menunggu Pembayaran): {$statusCounts['pending']} pesanan
     * Cancelled (Dibatalkan): {$statusCounts['cancelled']} pesanan
   - Total Pemasukan Toko (Pesanan Completed & Shipped): Rp " . number_format($totalIncome, 0, ',', '.') . "
   - Daftar Seluruh Pesanan Pelanggan di Sistem:
{$adminOrderList}

4. Data Barang Keluar (Penjualan / Produk Terjual):
   - Total Barang Keluar/Terjual: {$totalItemsSold} unit
{$soldList}

5. Data Barang Masuk / Stok Inventori di Toko:
   - Total Stok Tersedia: {$totalStock} unit produk
{$productList}

6. Kategori Produk di Database:
{$categoryList}

7. Kupon Diskon & Promo Aktif:
" . (!empty($couponList) ? $couponList : "- Belum ada kupon promo aktif saat ini.\n") . "

PETUNJUK RESPONS WAJIB:
1. Menjawab Pertanyaan Pesanan / Pesanan Masuk / Keuangan Toko:
   - Jika pengguna bertanya tentang 'berapa jumlah pesanan', 'ada berapa jumlah pesanan masuk', 'pesanan masuk', 'status pesanan', atau seputar pesanan/order:
     * Jika lawan bicara adalah staf/pengelola (Admin/Operator/Owner):
       Kamu WAJIB menjawab langsung menggunakan Data Manajemen Pesanan Toko:
       - Sebutkan total pesanan di sistem ({$totalOrdersCount} pesanan).
       - Sebutkan berapa pesanan masuk/aktif ({$incomingOrdersCount} pesanan, yaitu status Processing/Pending, misalnya Order #ORD-00003 dari Customer Cantik senilai Rp 100.000).
       - Sebutkan status pesanan lainnya ({$statusCounts['shipped']} dikirim, {$statusCounts['completed']} selesai).
       - JANGAN PERNAH mengatakan 'tidak ada riwayat pesanan pribadi untuk Admin' saat ditanya pesanan atau pesanan masuk toko!
     * Jika lawan bicara adalah Customer yang bertanya 'pesanan saya':
       Jawab sesuai Riwayat Pesanan Pribadi Customer tersebut.
   - Jika ditanya total pemasukan / omset / pendapatan toko:
     Sampaikan bahwa Total Pemasukan toko saat ini adalah Rp " . number_format($totalIncome, 0, ',', '.') . " (dengan total nilai seluruh pesanan Rp " . number_format($totalOrdersAmount, 0, ',', '.') . ").
   - Jika ditanya data barang masuk atau keluar:
     Jelaskan data barang keluar/terjual ({$totalItemsSold} unit terjual) dan sisa stok barang masuk/tersedia ({$totalStock} unit produk).

2. Menjawab 'Siapa Saya':
   HANYA JIKA pengguna bertanya 'siapa saya', 'siapa aku', 'saya siapa', 'kamu tahu siapa saya', atau pertanyaan tentang identitas dirinya:
   Kamu WAJIB menjawab sesuai peran akun pengguna saat ini secara tepat (JANGAN sebut admin/operator/owner sebagai customer, sebutkan peran aslinya):
   {$userGreetingRule}

3. Menjawab 'Siapa Kamu':
   HANYA JIKA pengguna bertanya 'siapa kamu', 'kamu siapa', atau 'siapa namamu':
   Jawablah bahwa kamu adalah NNACO Assistant, AI Customer Service & Asisten Toko resmi dari toko aksesoris NNACO SA yang siap membantu seputar produk aksesoris, stok, promo, dan pesanan.

4. Menjawab Pertanyaan Database (Produk, Kategori, Harga, Stok, Promo, Pesanan, Keranjang, Jumlah Pengguna / User):
   - Jika ditanya berapa pengguna atau user yang ada pada sistem (atau berapa customer/user terdaftar): sampaikan secara jelas total pengguna yang terdaftar di database sistem yaitu {$totalUsers} pengguna (" . implode(", ", $userRoleBreakdown) . ").
   - Jawablah langsung pertanyaan inti pengguna dengan jelas dan akurat berdasarkan data database di atas. JANGAN sertakan kalimat perkenalan akun jika pengguna hanya bertanya data/produk/user/stok/promo/pesanan.
   (Catatan: Toko NNACO SA khusus menjual aksesoris handmade seperti kalung, cincin, gelang, anting).

5. Larangan Coding / Materi Luar Toko:
   Jika pengguna meminta materi di luar toko (seperti minta script coding Python/PHP, matematika umum, atau politik), tolaklah dengan sopan bahwa kamu adalah asisten toko aksesoris NNACO SA dan JANGAN tuliskan kodenya.

Gunakan bahasa Indonesia yang profesional, ramah, bersahabat, dan jelas.";

        $chatHistory = $this->model('ChatHistory');

        if (!$this->ai) {
            $replyContent = 'Maaf, sistem AI belum dikonfigurasi oleh admin (API Key kosong).';
            try {
                $chatHistory->saveChatTurn(
                    $conversationId,
                    $userId,
                    $userMessage,
                    $replyContent,
                    $sessionId,
                    $ipAddress,
                    null,
                    'failed'
                );
            } catch (Throwable $e) {}

            echo json_encode([
                'reply' => $replyContent,
                'conversation_id' => $conversationId
            ]);
            exit;
        }

        $response = $this->ai->chatCompletion($systemPrompt, $userMessage);

        $modelId = method_exists($this->ai, 'getModelId') ? $this->ai->getModelId() : null;
        if (isset($response['success']) && !empty(trim($response['content'] ?? ''))) {
            $replyContent = trim($response['content']);
            $status = 'completed';
        } else {
            $status = 'failed';
            $errDetail = isset($response['error']) ? $response['error'] : 'Unknown error';

            // Smart graceful fallback responses based on user query keywords
            $lower = strtolower($userMessage);
            if (strpos($lower, 'gelang') !== false) {
                $replyContent = "Halo! Senang sekali Anda tertarik dengan koleksi kami. 😊\n\nUntuk hadiah ulang tahun, **Gelang Mutiara Soft** (Rp 120.000) adalah pilihan paling favorit karena desainnya yang anggun, lembut, dan cocok dipadukan dengan berbagai gaya busana.\n\nAnda dapat melihat detail foto dan melakukan pemesanan langsung melalui menu **Katalog** toko kami! ✨";
            } elseif (strpos($lower, 'cincin') !== false) {
                $replyContent = "Halo! Untuk cincin, kami memiliki **Cincin Bunga Daisy** (Rp 100.000) yang manis dan **Cincin Resin Aesthetic** (Rp 75.000) dengan hiasan kelopak bunga asli. Silakan kunjungi menu **Katalog** untuk melihat koleksi lengkapnya! 💍";
            } elseif (strpos($lower, 'kalung') !== false) {
                $replyContent = "Halo! Kami merekomendasikan **Kalung Titanium Rose Gold** (Rp 150.000) yang tahan karat, tidak luntur, dan memberikan kilau mewah yang elegan untuk hadiah spesial. Cek menu **Katalog** untuk detailnya ya! ✨";
            } elseif (strpos($lower, 'halo') !== false || strpos($lower, 'hi') !== false || strpos($lower, 'hai') !== false) {
                $replyContent = "Halo! 👋 Selamat datang di NNACO SA. Saya NNACO Assistant, siap membantu Anda menemukan aksesoris dan perhiasan handmade terbaik untuk momen istimewa Anda. Ada yang bisa saya bantu?";
            } else {
                $replyContent = "Halo! Mohon maaf, server AI kami sedang mengalami antrean jaringan yang cukup padat. Silakan telusuri koleksi perhiasan kami langsung di menu **Katalog** atau tanyakan seputar produk gelang, cincin, dan kalung kepada saya! ✨";
            }

            if ($isStaff) {
                $replyContent .= "\n\n*(Catatan Admin: Terjadi kendala respon upstream AI: {$errDetail})*";
            }
        }

        try {
            $chatHistory->saveChatTurn(
                $conversationId,
                $userId,
                $userMessage,
                $replyContent,
                $sessionId,
                $ipAddress,
                $modelId,
                $status
            );
        } catch (Throwable $e) {
            // Keep endpoint responsive even if DB logging hits unexpected issue
        }

        echo json_encode([
            'reply' => $replyContent,
            'conversation_id' => $conversationId
        ]);
        exit;
    }

    // ==========================================
    // ADMIN DASHBOARD AI FEATURES
    // ==========================================
    public function index() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'owner']);

        $data['title'] = 'AI Analytics Dashboard';
        $this->view('templates/header', $data);
        $this->view('ai/dashboard', $data);
        $this->view('templates/footer');
    }

    // 2. AI SALES PREDICTION
    public function predict_sales() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'owner']);
        header('Content-Type: application/json');
        
        $historicalData = "Hari H-7: 1500000, Hari H-6: 1200000, Hari H-5: 1800000, Hari H-4: 2100000, Hari H-3: 1600000, Hari H-2: 1900000, Hari H-1: 2200000";
        
        $systemPrompt = "Anda adalah Data Scientist spesialis peramalan deret waktu (time-series). 
Diberikan sekumpulan data penjualan aksesoris (IDR) dalam 7 hari terakhir. 
Tugas Anda adalah memprediksi angka penjualan untuk 7 hari KEDEPAN berdasarkan tren tersebut.
PENTING: Anda WAJIB merespon murni dalam format JSON object valid tanpa markdown dengan schema: 
{ \"predictions\": [angka_hari_1, angka_hari_2, angka_hari_3, angka_hari_4, angka_hari_5, angka_hari_6, angka_hari_7], \"analysis_text\": \"penjelasan ringkas analisa\" }";

        if (!$this->ai) {
            echo json_encode(['error' => 'Layanan AI belum diinisialisasi.']);
            exit;
        }

        $response = $this->ai->jsonCompletion($systemPrompt, $historicalData);
        
        if (isset($response['success'])) {
            $raw = trim($response['content']);
            // Strip code fence if any
            if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $raw, $matches)) {
                $raw = trim($matches[1]);
            }
            $decoded = json_decode($raw, true);
            if ($decoded && isset($decoded['predictions'])) {
                echo json_encode($decoded);
            } else {
                echo $raw;
            }
        } else {
            echo json_encode(['error' => $response['error'] ?? 'Gagal menghubungi AI Engine.']);
        }
    }

    // 3. AI TREND ANALYSIS
    public function trend_analysis() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'owner']);
        header('Content-Type: application/json');

        $prompt = "Anda adalah analis pakar e-commerce fashion & aksesoris di Indonesia. 
Prediksikan 3 tren produk aksesoris (seperti Kalung Layered, Cincin Resin Aesthetic, Gelang Manik) yang diperkirakan akan sangat populer dalam satu bulan ke depan.
PENTING: Anda WAJIB merespon murni dalam format JSON object valid tanpa markdown dengan schema: 
{ \"trends\": [ { \"item\": \"nama barang\", \"reason\": \"alasan kepopuleran singkat\" } ], \"summary\": \"kesimpulan keseluruhan\" }";

        if (!$this->ai) {
            echo json_encode(['error' => 'Layanan AI belum diinisialisasi.']);
            exit;
        }

        $response = $this->ai->jsonCompletion($prompt, "Buatkan trend analisis pasar aksesoris bulan ini dalam format JSON.");
        
        if (isset($response['success'])) {
            $raw = trim($response['content']);
            // Strip code fence if any
            if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*```$/i', $raw, $matches)) {
                $raw = trim($matches[1]);
            }
            $decoded = json_decode($raw, true);
            if ($decoded && isset($decoded['trends'])) {
                echo json_encode($decoded);
            } else {
                echo $raw;
            }
        } else {
            echo json_encode(['error' => $response['error'] ?? 'Gagal menghubungi AI Engine.']);
        }
    }
}
