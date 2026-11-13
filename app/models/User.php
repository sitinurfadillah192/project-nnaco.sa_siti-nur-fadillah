<?php
class User extends Model {
    protected $table = 'users';

    public function registerUser($data) {
        $query = "INSERT INTO " . $this->table . " (name, email, phone, is_phone_verified, password, role_id, created_at) 
                  VALUES (:name, :email, :phone, :is_phone_verified, :password, :role_id, NOW())";
        
        $this->db->query($query);
        $this->db->bind('name', $data['name']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('phone', !empty($data['phone']) ? $data['phone'] : null);
        $this->db->bind('is_phone_verified', !empty($data['is_phone_verified']) ? 1 : 0);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind('role_id', 1); // 1 = customer

        try {
            $this->db->execute();
            return $this->db->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function loginUser($email, $password) {
        $query = "SELECT u.*, r.name as role FROM " . $this->table . " u JOIN roles r ON u.role_id = r.id WHERE u.email = :email";
        $this->db->query($query);
        $this->db->bind('email', $email);
        $user = $this->db->single();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getUserByEmail($email) {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE email = :email");
        $this->db->bind('email', $email);
        return $this->db->single();
    }

    // NEW: Simpan token reset password
    public function setResetToken($email, $token, $expires) {
        $this->db->query("UPDATE " . $this->table . " SET reset_token = :token, reset_expires = :expires WHERE email = :email");
        $this->db->bind('token', $token);
        $this->db->bind('expires', $expires);
        $this->db->bind('email', $email);
        $this->db->execute();
    }

    // NEW: Validasi token reset
    public function getUserByResetToken($token) {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE reset_token = :token AND reset_expires > NOW()");
        $this->db->bind('token', $token);
        return $this->db->single();
    }

    // NEW: Update password baru dan hapus token
    public function updatePassword($userId, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->query("UPDATE " . $this->table . " SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        $this->db->bind('password', $hashed);
        $this->db->bind('id', $userId);
        $this->db->execute();
    }

    // WhatsApp OTP Auth: Cari pengguna berdasarkan nomor WhatsApp
    public function getUserByPhone($phone) {
        $query = "SELECT u.*, r.name as role 
                  FROM " . $this->table . " u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.phone = :phone";
        $this->db->query($query);
        $this->db->bind('phone', $phone);
        return $this->db->single();
    }

    // WhatsApp OTP Auth: Cari pengguna berdasarkan ID lengkap dengan role
    public function getUserById($id) {
        $query = "SELECT u.*, r.name as role 
                  FROM " . $this->table . " u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    // WhatsApp OTP Auth: Registrasi pengguna baru hanya menggunakan nomor WhatsApp
    public function registerUserWithPhone($phone, $name = null) {
        if (empty($name)) {
            $name = 'Pelanggan ' . substr($phone, -4);
        }

        $query = "INSERT INTO " . $this->table . " (name, phone, role_id, is_phone_verified, created_at) 
                  VALUES (:name, :phone, 1, 1, NOW())";
        
        $this->db->query($query);
        $this->db->bind('name', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
        $this->db->bind('phone', $phone);

        try {
            $this->db->execute();
            $newId = $this->db->lastInsertId();
            return $this->getUserById($newId);
        } catch (PDOException $e) {
            // Jika ada duplicate phone karena race condition
            return $this->getUserByPhone($phone);
        }
    }

    // WhatsApp OTP Auth: Tautkan atau perbarui nomor WhatsApp pada akun
    public function linkPhoneToUser($userId, $phone) {
        $query = "UPDATE " . $this->table . " SET phone = :phone, is_phone_verified = 1 WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('phone', $phone);
        $this->db->bind('id', $userId);
        return $this->db->execute();
    }

    // ==========================================
    // MANAJEMEN USER (ADMIN & OWNER)
    // ==========================================

    public function getAllUsers($limit = 10, $offset = 0, $search = '', $roleId = null) {
        $query = "SELECT u.id, u.name, u.email, u.phone, u.is_phone_verified, u.role_id, u.created_at, r.name as role_name 
                  FROM " . $this->table . " u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE 1=1 ";
        
        if (!empty($search)) {
            $query .= "AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search) ";
        }
        if (!empty($roleId)) {
            $query .= "AND u.role_id = :role_id ";
        }

        $query .= "ORDER BY u.id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        if (!empty($search)) {
            $this->db->bind('search', '%' . $search . '%');
        }
        if (!empty($roleId)) {
            $this->db->bind('role_id', $roleId);
        }
        $this->db->bind('limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind('offset', (int)$offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function countUsers($search = '', $roleId = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " u WHERE 1=1 ";
        if (!empty($search)) {
            $query .= "AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search) ";
        }
        if (!empty($roleId)) {
            $query .= "AND u.role_id = :role_id ";
        }

        $this->db->query($query);
        if (!empty($search)) {
            $this->db->bind('search', '%' . $search . '%');
        }
        if (!empty($roleId)) {
            $this->db->bind('role_id', $roleId);
        }

        $res = $this->db->single();
        return $res ? (int)$res['total'] : 0;
    }

    public function getUserStats() {
        $this->db->query("SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as total_customers,
            SUM(CASE WHEN role_id IN (2, 3, 4) THEN 1 ELSE 0 END) as total_staff,
            SUM(CASE WHEN phone IS NOT NULL AND phone != '' THEN 1 ELSE 0 END) as total_with_phone,
            SUM(CASE WHEN is_phone_verified = 1 THEN 1 ELSE 0 END) as total_verified_wa
            FROM " . $this->table);
        return $this->db->single();
    }

    public function getAllRoles() {
        $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        return $this->db->resultSet();
    }

    public function createUser($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (name, email, phone, is_phone_verified, password, role_id, created_at) 
                  VALUES (:name, :email, :phone, :is_phone_verified, :password, :role_id, NOW())";
        
        $this->db->query($query);
        $this->db->bind('name', $data['name']);
        $this->db->bind('email', !empty($data['email']) ? $data['email'] : null);
        $this->db->bind('phone', !empty($data['phone']) ? $data['phone'] : null);
        $this->db->bind('is_phone_verified', !empty($data['phone']) ? 1 : 0);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind('role_id', (int)$data['role_id']);

        try {
            $this->db->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateUser($id, $data) {
        $fields = [
            'name = :name',
            'email = :email',
            'phone = :phone',
            'role_id = :role_id'
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('name', $data['name']);
        $this->db->bind('email', !empty($data['email']) ? $data['email'] : null);
        $this->db->bind('phone', !empty($data['phone']) ? $data['phone'] : null);
        $this->db->bind('role_id', (int)$data['role_id']);
        $this->db->bind('id', (int)$id);

        if (!empty($data['password'])) {
            $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        }

        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteUser($id) {
        $this->db->query("DELETE FROM " . $this->table . " WHERE id = :id");
        $this->db->bind('id', (int)$id);
        return $this->db->execute();
    }

    public function countOrdersByUser($userId) {
        $this->db->query("SELECT COUNT(*) as total FROM orders WHERE user_id = :user_id");
        $this->db->bind('user_id', (int)$userId);
        $res = $this->db->single();
        return $res ? (int)$res['total'] : 0;
    }

    public function countAdmins() {
        $this->db->query("SELECT COUNT(*) as total FROM " . $this->table . " WHERE role_id = 2");
        $res = $this->db->single();
        return $res ? (int)$res['total'] : 0;
    }

    public function isEmailTaken($email, $excludeId = null) {
        if (empty($email)) return false;
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        $this->db->query($query);
        $this->db->bind('email', $email);
        if ($excludeId) {
            $this->db->bind('exclude_id', (int)$excludeId);
        }
        return (bool)$this->db->single();
    }

    public function isPhoneTaken($phone, $excludeId = null) {
        if (empty($phone)) return false;
        $query = "SELECT id FROM " . $this->table . " WHERE phone = :phone";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        $this->db->query($query);
        $this->db->bind('phone', $phone);
        if ($excludeId) {
            $this->db->bind('exclude_id', (int)$excludeId);
        }
        return (bool)$this->db->single();
    }
}

