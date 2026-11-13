<?php
class CategoryController extends Controller {

    public function __construct() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'operator']);
    }

    public function index() {
        $data['title'] = 'Manajemen Kategori';
        $data['categories'] = $this->model('Category')->getAll();

        $this->view('templates/header', $data);
        $this->view('category/index', $data);
        $this->view('templates/footer');
    }

    public function create() {
        $data['title'] = 'Tambah Kategori';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $postData = [
                'name' => htmlspecialchars($_POST['name']),
                'slug' => strtolower(str_replace(' ', '-', trim($_POST['name']))),
                'description' => htmlspecialchars($_POST['description'])
            ];

            if ($this->model('Category')->create($postData) > 0) {
                header('Location: ' . BASEURL . '/category');
                exit;
            }
        }

        $this->view('templates/header', $data);
        $this->view('category/create', $data);
        $this->view('templates/footer');
    }

    public function edit($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/category');
            exit;
        }

        $data['title'] = 'Edit Kategori';
        $data['category'] = $this->model('Category')->getById($id);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $postData = [
                'name' => htmlspecialchars($_POST['name']),
                'slug' => strtolower(str_replace(' ', '-', trim($_POST['name']))),
                'description' => htmlspecialchars($_POST['description'])
            ];

            $this->model('Category')->updateCategory($id, $postData);
            header('Location: ' . BASEURL . '/category');
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('category/edit', $data);
        $this->view('templates/footer');
    }

    public function delete($id = null) {
        if ($id) {
            $this->model('Category')->delete($id);
        }
        header('Location: ' . BASEURL . '/category');
        exit;
    }
}
