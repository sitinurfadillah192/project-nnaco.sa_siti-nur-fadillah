<?php
class CartController extends Controller {
    public function __construct() {
        AuthMiddleware::check(); // Must login
    }

    public function index() {
        $data['title'] = 'Keranjang Belanja';
        $data['cart'] = $this->model('Cart')->getUserCart($_SESSION['user_id']);
        
        $total = 0;
        foreach($data['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $data['total'] = $total;

        $this->view('templates/header', $data);
        $this->view('cart/index', $data);
        $this->view('templates/footer');
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $product_id = $_POST['product_id'];
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            $this->model('Cart')->addToCart($_SESSION['user_id'], $product_id, $quantity);
            header('Location: ' . BASEURL . '/cart');
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cart_id = $_POST['cart_id'];
            $quantity = (int)$_POST['quantity'];
            if ($quantity > 0) {
                $this->model('Cart')->updateQuantity($cart_id, $_SESSION['user_id'], $quantity);
            }
            header('Location: ' . BASEURL . '/cart');
            exit;
        }
    }

    public function remove($cart_id) {
        $this->model('Cart')->remove($cart_id, $_SESSION['user_id']);
        header('Location: ' . BASEURL . '/cart');
        exit;
    }
}
