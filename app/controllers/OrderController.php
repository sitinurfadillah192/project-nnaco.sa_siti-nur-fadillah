<?php
class OrderController extends Controller {
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $data['title'] = 'Riwayat Pembelian';
        $data['orders'] = $this->model('Order')->getOrdersByUser($_SESSION['user_id']);

        $this->view('templates/header', $data);
        $this->view('order/history', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        $data['title'] = 'Detail Pesanan';
        $result = $this->model('Order')->getOrderDetails($id, $_SESSION['user_id']);
        
        if (!$result) {
            Flasher::setFlash('Pesanan tidak ditemukan.', 'Atau Anda tidak memiliki akses.', 'danger');
            header('Location: ' . BASEURL . '/order');
            exit;
        }
        
        $data['order'] = $result['order'];
        $data['items'] = $result['items'];
        $data['reviews'] = $this->model('Review')->getReviewsForOrder($id, $_SESSION['user_id']);

        $this->view('templates/header', $data);
        $this->view('order/detail', $data);
        $this->view('templates/footer');
    }

    public function success($id) {
        $data['title'] = 'Pesanan Berhasil';
        $data['order_id'] = $id;

        $this->view('templates/header', $data);
        $this->view('order/success', $data);
        $this->view('templates/footer');
    }

    public function payment($id) {
        $data['title'] = 'Konfirmasi Pembayaran Cash';
        $result = $this->model('Order')->getOrderDetails($id, $_SESSION['user_id']);
        
        if (!$result || $result['order']['status'] !== 'pending') {
            Flasher::setFlash('Pesanan tidak valid', 'Pembayaran tidak bisa dilakukan saat ini.', 'danger');
            header('Location: ' . BASEURL . '/order');
            exit;
        }
        
        $data['order'] = $result['order'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payment_method = 'Cash';
            $payment_proof = 'cash_transaction';
            
            $this->model('Payment')->savePayment($id, $payment_method, $payment_proof);
            Flasher::setFlash('Konfirmasi pembayaran cash berhasil.', 'Pesanan akan segera diproses.', 'success');
            header('Location: ' . BASEURL . '/order/detail/' . $id);
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('order/payment', $data);
        $this->view('templates/footer');
    }

    public function invoice($id) {
        $result = $this->model('Order')->getOrderDetails($id, $_SESSION['user_id']);
        
        if (!$result) {
            die("Pesanan tidak ditemukan."); // Tetap pakai die() di invoice khusus cetak PDF karena tidak ada navbar
        }
        
        $data['order'] = $result['order'];
        $data['items'] = $result['items'];

        $this->view('order/invoice', $data);
    }
}
