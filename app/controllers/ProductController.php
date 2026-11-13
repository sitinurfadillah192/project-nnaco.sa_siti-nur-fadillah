<?php
class ProductController extends Controller {

    public function __construct() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'operator']);
    }

    public function index() {
        $data['title'] = 'Manajemen Produk';
        
        $limit = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;

        $data['products'] = $this->model('Product')->getProductsWithCategory($limit, $offset, $search, $category_id);
        $data['categories'] = $this->model('Category')->getAll();
        
        $total = $this->model('Product')->countProducts($search, $category_id);
        $data['total_pages'] = ceil($total / $limit);
        $data['current_page'] = $page;

        $this->view('templates/header', $data);
        $this->view('product/index', $data);
        $this->view('templates/footer');
    }

    public function create() {
        $data['title'] = 'Tambah Produk';
        $data['categories'] = $this->model('Category')->getAll();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imageUrl = '';
            
            // Handle Image Upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../public/uploads/products/';
                $filename = time() . '_' . basename($_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $imageUrl = $filename;
                }
            }

            $postData = [
                'category_id' => $_POST['category_id'],
                'name' => htmlspecialchars($_POST['name']),
                'slug' => strtolower(str_replace(' ', '-', trim($_POST['name']))),
                'description' => htmlspecialchars($_POST['description']),
                'price' => $_POST['price'],
                'stock' => $_POST['stock'],
                'image_url' => $imageUrl
            ];

            if ($this->model('Product')->create($postData) > 0) {
                header('Location: ' . BASEURL . '/product');
                exit;
            }
        }

        $this->view('templates/header', $data);
        $this->view('product/create', $data);
        $this->view('templates/footer');
    }

    public function edit($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/product');
            exit;
        }

        $data['title'] = 'Edit Produk';
        $data['product'] = $this->model('Product')->getById($id);
        $data['categories'] = $this->model('Category')->getAll();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $imageUrl = '';
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../public/uploads/products/';
                $filename = time() . '_' . basename($_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $imageUrl = $filename;
                }
            }

            $postData = [
                'category_id' => $_POST['category_id'],
                'name' => htmlspecialchars($_POST['name']),
                'slug' => strtolower(str_replace(' ', '-', trim($_POST['name']))),
                'description' => htmlspecialchars($_POST['description']),
                'price' => $_POST['price'],
                'stock' => $_POST['stock'],
                'image_url' => $imageUrl
            ];

            $this->model('Product')->updateProduct($id, $postData);
            header('Location: ' . BASEURL . '/product');
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('product/edit', $data);
        $this->view('templates/footer');
    }

    public function delete($id = null) {
        if ($id) {
            $this->model('Product')->delete($id);
        }
        header('Location: ' . BASEURL . '/product');
        exit;
    }
}
