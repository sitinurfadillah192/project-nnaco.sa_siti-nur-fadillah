<?php
class UserController extends Controller {

    public function __construct() {
        AuthMiddleware::check();
        RoleMiddleware::check(['admin', 'owner']);
    }

    public function index() {
        $data['title'] = 'Manajemen User - nnaco_sa';

        $limit = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $roleId = isset($_GET['role_id']) && $_GET['role_id'] !== '' ? (int)$_GET['role_id'] : null;

        $userModel = $this->model('User');
        $data['users'] = $userModel->getAllUsers($limit, $offset, $search, $roleId);
        $totalUsers = $userModel->countUsers($search, $roleId);

        $data['total_users'] = $totalUsers;
        $data['total_pages'] = ceil($totalUsers / $limit);
        $data['current_page'] = $page;
        $data['search'] = $search;
        $data['role_id'] = $roleId;
        $data['stats'] = $userModel->getUserStats();
        $data['roles'] = $userModel->getAllRoles();

        $this->view('templates/header', $data);
        $this->view('user/index', $data);
        $this->view('templates/footer');
    }

    public function create() {
        $data['title'] = 'Tambah Pengguna Baru';
        $userModel = $this->model('User');
        $data['roles'] = $userModel->getAllRoles();
        $data['errors'] = [];
        $data['old'] = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('Keamanan!', 'Token CSRF tidak valid. Silakan coba lagi.', 'danger');
                header('Location: ' . BASEURL . '/user/create');
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role_id = (int)($_POST['role_id'] ?? 1);
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Format phone if provided
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($phone, '08')) {
                    $phone = '628' . substr($phone, 2);
                }
            }

            $data['old'] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role_id' => $role_id
            ];

            // Validasi
            if (empty($name)) {
                $data['errors'][] = 'Nama lengkap wajib diisi.';
            }

            if (empty($email) && empty($phone)) {
                $data['errors'][] = 'Wajib mengisi minimal salah satu antara Email atau Nomor WhatsApp.';
            }

            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['errors'][] = 'Format alamat email tidak valid.';
                } elseif ($userModel->isEmailTaken($email)) {
                    $data['errors'][] = 'Alamat email sudah digunakan oleh pengguna lain.';
                }
            }

            if (!empty($phone)) {
                if (strlen($phone) < 10 || strlen($phone) > 16) {
                    $data['errors'][] = 'Nomor WhatsApp harus terdiri dari 10 - 15 digit angka.';
                } elseif ($userModel->isPhoneTaken($phone)) {
                    $data['errors'][] = 'Nomor WhatsApp sudah terdaftar pada pengguna lain.';
                }
            }

            if (strlen($password) < 6) {
                $data['errors'][] = 'Password minimal harus 6 karakter.';
            } elseif ($password !== $confirm_password) {
                $data['errors'][] = 'Konfirmasi password tidak cocok.';
            }

            if (empty($data['errors'])) {
                $newId = $userModel->createUser([
                    'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                    'email' => $email,
                    'phone' => $phone,
                    'role_id' => $role_id,
                    'password' => $password
                ]);

                if ($newId) {
                    Flasher::setFlash('Berhasil!', "Pengguna '{$name}' berhasil ditambahkan.", 'success');
                    header('Location: ' . BASEURL . '/user');
                    exit;
                } else {
                    $data['errors'][] = 'Terjadi kesalahan sistem saat menyimpan pengguna ke database.';
                }
            }
        }

        $this->view('templates/header', $data);
        $this->view('user/create', $data);
        $this->view('templates/footer');
    }

    public function edit($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->getUserById($id);

        if (!$user) {
            Flasher::setFlash('Tidak Ditemukan!', 'Data pengguna tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        $data['title'] = 'Edit Pengguna - ' . htmlspecialchars($user['name']);
        $data['user'] = $user;
        $data['roles'] = $userModel->getAllRoles();
        $data['errors'] = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('Keamanan!', 'Token CSRF tidak valid. Silakan coba lagi.', 'danger');
                header('Location: ' . BASEURL . '/user/edit/' . $id);
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role_id = (int)($_POST['role_id'] ?? $user['role_id']);
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Format phone
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($phone, '08')) {
                    $phone = '628' . substr($phone, 2);
                }
            }

            // Validasi
            if (empty($name)) {
                $data['errors'][] = 'Nama lengkap wajib diisi.';
            }

            if (empty($email) && empty($phone)) {
                $data['errors'][] = 'Wajib mengisi minimal salah satu antara Email atau Nomor WhatsApp.';
            }

            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['errors'][] = 'Format alamat email tidak valid.';
                } elseif ($userModel->isEmailTaken($email, $id)) {
                    $data['errors'][] = 'Alamat email sudah digunakan oleh pengguna lain.';
                }
            }

            if (!empty($phone)) {
                if (strlen($phone) < 10 || strlen($phone) > 16) {
                    $data['errors'][] = 'Nomor WhatsApp harus terdiri dari 10 - 15 digit angka.';
                } elseif ($userModel->isPhoneTaken($phone, $id)) {
                    $data['errors'][] = 'Nomor WhatsApp sudah digunakan oleh pengguna lain.';
                }
            }

            // Mencegah akun admin sendiri di-downgrade jika dia admin terakhir
            if ($id == $_SESSION['user_id'] && $user['role_id'] == 2 && $role_id != 2) {
                if ($userModel->countAdmins() <= 1) {
                    $data['errors'][] = 'Anda tidak dapat mengubah role Anda sendiri karena Anda adalah admin satu-satunya.';
                }
            }

            // Validasi password jika diisi
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $data['errors'][] = 'Password baru minimal harus 6 karakter.';
                } elseif ($password !== $confirm_password) {
                    $data['errors'][] = 'Konfirmasi password baru tidak cocok.';
                }
            }

            if (empty($data['errors'])) {
                $updateData = [
                    'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                    'email' => $email,
                    'phone' => $phone,
                    'role_id' => $role_id
                ];

                if (!empty($password)) {
                    $updateData['password'] = $password;
                }

                $updated = $userModel->updateUser($id, $updateData);

                if ($updated) {
                    // Update sesi aktif jika mengubah nama diri sendiri
                    if ($id == $_SESSION['user_id']) {
                        $_SESSION['user_name'] = $name;
                    }

                    Flasher::setFlash('Berhasil!', "Data pengguna '{$name}' berhasil diperbarui.", 'success');
                    header('Location: ' . BASEURL . '/user');
                    exit;
                } else {
                    $data['errors'][] = 'Terjadi kesalahan sistem saat memperbarui data pengguna.';
                }
            }

            // Refresh displayed user data with submitted values
            $data['user']['name'] = $name;
            $data['user']['email'] = $email;
            $data['user']['phone'] = $phone;
            $data['user']['role_id'] = $role_id;
        }

        $this->view('templates/header', $data);
        $this->view('user/edit', $data);
        $this->view('templates/footer');
    }

    public function delete($id = null) {
        if (!$id) {
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->getUserById($id);

        if (!$user) {
            Flasher::setFlash('Tidak Ditemukan!', 'Data pengguna tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        // Cegah menghapus akun sendiri
        if ($id == $_SESSION['user_id']) {
            Flasher::setFlash('Gagal!', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.', 'danger');
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        // Cegah menghapus admin terakhir
        if ($user['role_id'] == 2 && $userModel->countAdmins() <= 1) {
            Flasher::setFlash('Gagal!', 'Tidak dapat menghapus admin utama karena merupakan satu-satunya admin aktif di sistem.', 'danger');
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        // Cek riwayat pesanan (integritas data finansial & transaksi)
        $orderCount = $userModel->countOrdersByUser($id);
        if ($orderCount > 0) {
            Flasher::setFlash('Peringatan!', "Pengguna '{$user['name']}' memiliki {$orderCount} riwayat pesanan/transaksi. Akun tidak dapat dihapus permanen demi audit dan laporan transaksi.", 'warning');
            header('Location: ' . BASEURL . '/user');
            exit;
        }

        try {
            $userModel->deleteUser($id);
            Flasher::setFlash('Berhasil!', "Pengguna '{$user['name']}' berhasil dihapus dari sistem.", 'success');
        } catch (PDOException $e) {
            Flasher::setFlash('Gagal!', 'Pengguna memiliki relasi data lain dan tidak dapat dihapus.', 'danger');
        }

        header('Location: ' . BASEURL . '/user');
        exit;
    }
}
