<?php
class CatalogController extends Controller {
    public function index() {
        $data['title'] = 'Katalog Produk - nnaco_sa';
        
        $limit = 9; // Grid 3x3
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $search = isset($_GET['keyword']) ? filter_var($_GET['keyword'], FILTER_SANITIZE_STRING) : '';
        $category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;

        $data['products'] = $this->model('Product')->getProductsWithCategory($limit, $offset, $search, $category_id);
        $data['categories'] = $this->model('Category')->getAll();
        
        $total = $this->model('Product')->countProducts($search, $category_id);
        $data['total_pages'] = ceil($total / $limit);
        $data['current_page'] = $page;
        $data['search'] = $search;
        $data['category_id'] = $category_id;

        // Cek wishlist user
        $data['wishlist'] = [];
        if (isset($_SESSION['user_id'])) {
            $data['wishlist'] = $this->model('Wishlist')->getWishlistProductIds($_SESSION['user_id']);
        }

        // Ambil rating rata-rata untuk setiap produk di halaman ini
        $productIds = !empty($data['products']) ? array_column($data['products'], 'id') : [];
        $data['ratings'] = !empty($productIds) ? $this->model('Review')->getAverageRatingsForProducts($productIds) : [];

        $this->view('templates/header', $data);
        $this->view('catalog/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        $id = (int)$id;
        $product = $this->model('Product')->getProductDetail($id);

        if (!$product) {
            Flasher::setFlash('Produk tidak ditemukan.', 'Perhiasan yang Anda cari tidak tersedia.', 'warning');
            header('Location: ' . BASEURL . '/catalog');
            exit;
        }

        $data['title'] = Security::escape($product['name']) . ' - NNACO SA';
        $data['product'] = $product;
        
        // Review & Rating Data
        $reviewModel = $this->model('Review');
        $data['rating_summary'] = $reviewModel->getProductRatingSummary($id);
        $data['reviews'] = $reviewModel->getReviewsByProduct($id, 30);

        // Cek wishlist
        $data['in_wishlist'] = false;
        if (isset($_SESSION['user_id'])) {
            $wishlistIds = $this->model('Wishlist')->getWishlistProductIds($_SESSION['user_id']);
            $data['in_wishlist'] = in_array($id, $wishlistIds);
        }

        // Cek apakah user berhak memberikan ulasan (pernah order produk ini)
        $data['can_review'] = false;
        $data['user_order_id'] = null;
        $data['my_review'] = null;
        if (isset($_SESSION['user_id'])) {
            $db = new Database();
            $db->query("
                SELECT o.id FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = :uid AND oi.product_id = :pid
                ORDER BY o.id DESC LIMIT 1
            ");
            $db->bind(':uid', $_SESSION['user_id']);
            $db->bind(':pid', $id);
            $userOrder = $db->single();
            if ($userOrder) {
                $data['can_review'] = true;
                $data['user_order_id'] = $userOrder['id'];
                $data['my_review'] = $reviewModel->getReviewByOrderProduct($_SESSION['user_id'], $id, $userOrder['id']);
            }
        }

        // Produk Serupa / Terkait
        $data['related_products'] = $this->model('Product')->getProductsWithCategory(4, 0, '', $product['category_id']);

        $this->view('templates/header', $data);
        $this->view('catalog/detail', $data);
        $this->view('templates/footer');
    }
}
