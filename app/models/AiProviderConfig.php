<?php
/**
 * Model AiProviderConfig — Multi-Provider AI Configuration Model
 */
class AiProviderConfig extends Model {
    protected $table = 'ai_provider_configs';

    /**
     * Dapatkan semua konfigurasi AI dengan API key tersamarkan
     *
     * @return array
     */
    public function getAll() {
        $this->db->query("SELECT * FROM " . $this->table . " ORDER BY is_active DESC, id ASC");
        $rows = $this->db->resultSet();

        foreach ($rows as &$row) {
            $row['parameters'] = !empty($row['parameters_json']) ? json_decode($row['parameters_json'], true) : [];
            $row['has_key'] = !empty($row['encrypted_api_key']);
            $plainKey = $row['has_key'] ? SecretVault::decrypt($row['encrypted_api_key']) : '';
            $row['masked_key'] = SecretVault::maskSecret($plainKey);
            unset($row['encrypted_api_key']); // Jangan kirim ciphertext ke pemanggil publik
        }

        return $rows;
    }

    /**
     * Dapatkan konfigurasi AI berdasarkan ID
     *
     * @param mixed $id
     * @param bool $withPlainKey Jika true, sertakan API key hasil dekripsi
     * @return array|null
     */
    public function getById($id, bool $withPlainKey = false) {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE id = :id");
        $this->db->bind('id', $id);
        $row = $this->db->single();

        if (!$row) {
            return null;
        }

        $row['parameters'] = !empty($row['parameters_json']) ? json_decode($row['parameters_json'], true) : [];
        $row['has_key'] = !empty($row['encrypted_api_key']);
        $plainKey = $row['has_key'] ? SecretVault::decrypt($row['encrypted_api_key']) : '';
        $row['masked_key'] = SecretVault::maskSecret($plainKey);

        if ($withPlainKey) {
            $row['plain_key'] = $plainKey;
        }
        unset($row['encrypted_api_key']);

        return $row;
    }

    /**
     * Dapatkan konfigurasi AI aktif saat ini lengkap dengan decrypted key
     *
     * @return array|null
     */
    public function getActive(): ?array {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE is_active = 1 LIMIT 1");
        $row = $this->db->single();

        if (!$row) {
            return null;
        }

        $row['parameters'] = !empty($row['parameters_json']) ? json_decode($row['parameters_json'], true) : [];
        $row['plain_key'] = !empty($row['encrypted_api_key']) ? SecretVault::decrypt($row['encrypted_api_key']) : '';
        return $row;
    }

    /**
     * Buat konfigurasi AI baru
     *
     * @param array $data
     * @return int|false ID konfigurasi baru atau false
     */
    public function create(array $data) {
        $encryptedKey = !empty($data['api_key']) ? SecretVault::encrypt($data['api_key']) : null;
        $parameters = is_array($data['parameters'] ?? null) ? json_encode($data['parameters']) : ($data['parameters_json'] ?? null);

        $query = "INSERT INTO " . $this->table . " 
                  (config_name, provider, base_url, model_id, encrypted_api_key, parameters_json, is_active, last_test_status, created_at) 
                  VALUES (:name, :provider, :url, :model, :key, :params, :active, 'UNTESTED', NOW())";

        $this->db->query($query);
        $this->db->bind('name', $data['config_name']);
        $this->db->bind('provider', $data['provider']);
        $this->db->bind('url', $data['base_url']);
        $this->db->bind('model', $data['model_id']);
        $this->db->bind('key', $encryptedKey);
        $this->db->bind('params', $parameters);
        $this->db->bind('active', !empty($data['is_active']) ? 1 : 0);

        if ($this->db->execute()) {
            $newId = (int)$this->db->lastInsertId();
            if (!empty($data['is_active'])) {
                $this->activate($newId);
            }
            return $newId;
        }
        return false;
    }

    /**
     * Update konfigurasi AI yang sudah ada
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [
            'config_name = :name',
            'provider = :provider',
            'base_url = :url',
            'model_id = :model',
            'parameters_json = :params'
        ];

        // Jika API key baru diisi, enkripsi dan simpan. Jika kosong, pertahankan key lama!
        if (isset($data['api_key']) && $data['api_key'] !== '') {
            $fields[] = 'encrypted_api_key = :key';
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('name', $data['config_name']);
        $this->db->bind('provider', $data['provider']);
        $this->db->bind('url', $data['base_url']);
        $this->db->bind('model', $data['model_id']);
        $this->db->bind('params', is_array($data['parameters'] ?? null) ? json_encode($data['parameters']) : ($data['parameters_json'] ?? null));
        $this->db->bind('id', $id);

        if (isset($data['api_key']) && $data['api_key'] !== '') {
            $this->db->bind('key', SecretVault::encrypt($data['api_key']));
        }

        return $this->db->execute();
    }

    /**
     * Aktifkan satu konfigurasi AI dan nonaktifkan yang lain
     *
     * @param int $id
     * @return bool
     */
    public function activate(int $id): bool {
        $this->db->query("UPDATE " . $this->table . " SET is_active = 0");
        $this->db->execute();

        $this->db->query("UPDATE " . $this->table . " SET is_active = 1 WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    /**
     * Hapus konfigurasi AI jika tidak sedang aktif
     *
     * @param mixed $id
     * @return array|int
     */
    public function delete($id) {
        $config = $this->getById($id);
        if (!$config) {
            return ['success' => false, 'error' => 'Konfigurasi tidak ditemukan.'];
        }

        if (!empty($config['is_active'])) {
            return ['success' => false, 'error' => 'Konfigurasi yang sedang aktif tidak dapat dihapus. Silakan aktifkan konfigurasi lain terlebih dahulu.'];
        }

        $this->db->query("DELETE FROM " . $this->table . " WHERE id = :id");
        $this->db->bind('id', $id);
        $res = $this->db->execute();

        return ['success' => $res, 'error' => $res ? null : 'Gagal menghapus data dari database.'];
    }

    /**
     * Perbarui status dan latency pengujian koneksi
     *
     * @param int $id
     * @param string $status SUCCESS|FAILED|ERROR
     * @param int|null $latencyMs
     * @param string|null $message
     * @return bool
     */
    public function updateTestStatus(int $id, string $status, ?int $latencyMs = null, ?string $message = null): bool {
        $this->db->query("UPDATE " . $this->table . " 
                          SET last_tested_at = NOW(), last_test_status = :status, last_test_latency_ms = :latency, last_test_message = :msg 
                          WHERE id = :id");
        $this->db->bind('status', $status);
        $this->db->bind('latency', $latencyMs);
        $this->db->bind('msg', $message ? substr($message, 0, 250) : null);
        $this->db->bind('id', $id);
        return $this->db->execute();
    }
}
