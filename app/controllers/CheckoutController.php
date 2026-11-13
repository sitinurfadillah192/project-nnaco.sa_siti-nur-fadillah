<?php
class CheckoutController extends Controller {
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $data['title'] = 'Checkout';
        $data['cart'] = $this->model('Cart')->getUserCart($_SESSION['user_id']);
        $data['user'] = $this->model('User')->getUserById($_SESSION['user_id']);
        
        if (empty($data['cart'])) {
            Flasher::setFlash('Keranjang masih kosong.', 'Silakan pilih produk terlebih dahulu.', 'warning');
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        $total = 0;
        foreach($data['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $data['total'] = $total;

        $this->view('templates/header', $data);
        $this->view('checkout/index', $data);
        $this->view('templates/footer');
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $address = htmlspecialchars(trim($_POST['shipping_address'] ?? ''));
            $phoneInput = trim($_POST['phone'] ?? '');

            // Validasi Nomor WhatsApp
            $phone = FonnteService::normalizePhone($phoneInput);
            if (empty($phone)) {
                Flasher::setFlash('Nomor WhatsApp Tidak Valid', 'Mohon masukkan nomor WhatsApp yang aktif (contoh: 081234567890).', 'danger');
                header('Location: ' . BASEURL . '/checkout');
                exit;
            }

            if (empty($address)) {
                Flasher::setFlash('Alamat Wajib Diisi', 'Mohon lengkapi alamat pengiriman Anda.', 'danger');
                header('Location: ' . BASEURL . '/checkout');
                exit;
            }
            
            $cart = $this->model('Cart')->getUserCart($_SESSION['user_id']);
            if (empty($cart)) {
                Flasher::setFlash('Keranjang kosong.', 'Tidak bisa memproses pesanan.', 'danger');
                header('Location: ' . BASEURL . '/cart');
                exit;
            }

            $total = 0;
            foreach($cart as $item) {
                if ($item['quantity'] > $item['stock']) {
                    Flasher::setFlash('Stok produk '.$item['name'].' tidak mencukupi.', 'Tersisa: '.$item['stock'], 'danger');
                    header('Location: ' . BASEURL . '/cart');
                    exit;
                }
                $total += $item['price'] * $item['quantity'];
            }

            // Simpan / tautkan nomor WhatsApp ke akun pengguna
            $this->model('User')->linkPhoneToUser($_SESSION['user_id'], $phone);

            // Buat pesanan dengan nomor WhatsApp
            $order_id = $this->model('Order')->createOrder($_SESSION['user_id'], $address, $cart, $total, $phone);

            if ($order_id) {
                // Kirim notifikasi WhatsApp instan ke customer bahwa pesanan berhasil dibuat
                $orderCode = '#ORD-' . str_pad($order_id, 5, '0', STR_PAD_LEFT);
                $customerName = $_SESSION['user_name'] ?? 'Pelanggan';
                $totalFormatted = number_format($total, 0, ',', '.');
                $orderUrl = BASEURL . '/order/detail/' . $order_id;

                $waMsg = "*NNACOSA — PESANAN BERHASIL DIBUAT* 💎\n\n"
                       . "Halo *{$customerName}*,\n\n"
                       . "Terima kasih! Pesanan Anda telah berhasil dibuat di *NNACOSA*.\n\n"
                       . "📦 *Nomor Pesanan:* {$orderCode}\n"
                       . "💰 *Total Tagihan:* Rp {$totalFormatted}\n"
                       . "📍 *Alamat Pengiriman:*\n{$address}\n\n"
                       . "Update status pesanan berikutnya (sedang diproses, dikirim, no. resi kurir) akan dikirimkan otomatis ke WhatsApp ini.\n\n"
                       . "Lihat rincian pesanan:\n{$orderUrl}\n\n"
                       . "Terima kasih telah berbelanja di *NNACOSA*! ✨";
                
                FonnteService::sendMessage($phone, $waMsg);

                Flasher::setFlash('Pesanan Berhasil Dibuat!', 'Konfirmasi telah dikirim ke WhatsApp Anda. Lanjutkan ke pembayaran.', 'success');
                header('Location: ' . BASEURL . '/order/success/' . $order_id);
                exit;
            } else {
                Flasher::setFlash('Gagal memproses pesanan.', 'Silakan coba lagi.', 'danger');
                header('Location: ' . BASEURL . '/cart');
                exit;
            }
        }
    }
}
