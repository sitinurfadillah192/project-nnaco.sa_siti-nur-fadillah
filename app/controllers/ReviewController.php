<?php

class ReviewController extends Controller {
    /**
     * Submit rating dan ulasan pelanggan dari halaman Detail Pesanan atau Detail Produk
     */
    public function submit() {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        // Verifikasi CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrfToken($csrfToken)) {
            Flasher::setFlash('Sesi kedaluwarsa.', 'Permintaan tidak valid, silakan coba kembali.', 'danger');
            $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            header('Location: ' . BASEURL . ($orderId ? '/order/detail/' . $orderId : '/order'));
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        $redirect = isset($_POST['redirect_to']) ? trim($_POST['redirect_to']) : '';

        if ($orderId <= 0 || $productId <= 0) {
            Flasher::setFlash('Gagal mengirim ulasan.', 'Data produk atau pesanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/order');
            exit;
        }

        if ($rating < 1 || $rating > 5) {
            Flasher::setFlash('Rating tidak valid.', 'Silakan pilih antara 1 hingga 5 bintang.', 'warning');
            header('Location: ' . BASEURL . '/order/detail/' . $orderId);
            exit;
        }

        $res = $this->model('Review')->saveReview($userId, $productId, $orderId, $rating, $comment);

        if ($res['success']) {
            Flasher::setFlash('Ulasan Berhasil Disimpan! ✨', $res['message'], 'success');
        } else {
            Flasher::setFlash('Perhatian', $res['message'], 'warning');
        }

        if (!empty($redirect) && strpos($redirect, 'http') === false) {
            header('Location: ' . BASEURL . '/' . ltrim($redirect, '/'));
        } else {
            header('Location: ' . BASEURL . '/order/detail/' . $orderId);
        }
        exit;
    }

    /**
     * Dashboard kelola ulasan untuk Admin / Operator / Owner
     */
    public function admin() {
        RoleMiddleware::check(['admin', 'owner', 'operator']);

        $limit = 15;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim(filter_var($_GET['search'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';
        $rating = (isset($_GET['rating']) && $_GET['rating'] !== '') ? (int)$_GET['rating'] : null;

        $reviewModel = $this->model('Review');
        $reviews = $reviewModel->getAllReviewsAdmin($limit, $offset, $search, $rating);
        $totalReviews = $reviewModel->countReviewsAdmin($search, $rating);
        $totalPages = ceil($totalReviews / $limit);

        $data = [
            'title' => 'Manajemen Ulasan & Rating Pelanggan',
            'reviews' => $reviews,
            'total_reviews' => $totalReviews,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'search' => $search,
            'rating_filter' => $rating
        ];

        $this->view('templates/header', $data);
        $this->view('review/admin', $data);
        $this->view('templates/footer');
    }

    /**
     * Hapus ulasan oleh Admin atau pemilik ulasan
     */
    public function delete($id) {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrfToken($csrfToken)) {
            Flasher::setFlash('Sesi kedaluwarsa.', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $id = (int)$id;
        $userId = (int)$_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'customer';
        $isAdmin = in_array($userRole, ['admin', 'owner', 'operator']);

        $success = $this->model('Review')->deleteReview($id, $userId, $isAdmin);

        if ($success) {
            Flasher::setFlash('Berhasil', 'Ulasan telah berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Ulasan tidak dapat dihapus.', 'danger');
        }

        if ($isAdmin) {
            header('Location: ' . BASEURL . '/review/admin');
        } else {
            header('Location: ' . BASEURL . '/order');
        }
        exit;
    }
}
