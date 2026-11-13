<?php
class WishlistController extends Controller {
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $data['title'] = 'Wishlist Saya';
        $data['wishlist'] = $this->model('Wishlist')->getUserWishlist($_SESSION['user_id']);

        $this->view('templates/header', $data);
        $this->view('wishlist/index', $data);
        $this->view('templates/footer');
    }

    public function toggle() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
            $status = $this->model('Wishlist')->toggle($_SESSION['user_id'], $_POST['product_id']);
            echo json_encode(['status' => 'success', 'action' => $status]);
        }
    }
}
