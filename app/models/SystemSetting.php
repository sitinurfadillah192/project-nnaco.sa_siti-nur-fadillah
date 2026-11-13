<?php
/**
 * Model SystemSetting — Key-Value Configuration Store
 */
class SystemSetting extends Model {
    protected $table = 'system_settings';

    /**
     * Dapatkan nilai konfigurasi berdasarkan key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $this->db->query("SELECT setting_value, value_type, is_secret FROM " . $this->table . " WHERE setting_key = :key");
        $this->db->bind('key', $key);
        $row = $this->db->single();

        if (!$row) {
            return $default;
        }

        return $this->castValue($row['setting_value'], $row['value_type'], (bool)$row['is_secret']);
    }

    /**
     * Simpan atau perbarui nilai konfigurasi
     *
     * @param string $key
     * @param mixed $value
     * @param string $type string|integer|boolean|json|encrypted
     * @param string $group
     * @param int $isSecret
     * @param int|null $updatedBy
     * @return bool
     */
    public function set(string $key, $value, string $type = 'string', string $group = 'general', int $isSecret = 0, ?int $updatedBy = null): bool {
        $storedValue = $value;

        if ($type === 'boolean') {
            $storedValue = $value ? '1' : '0';
        } elseif ($type === 'json' && is_array($value)) {
            $storedValue = json_encode($value);
        } elseif ($isSecret && !empty($value)) {
            $storedValue = SecretVault::encrypt((string)$value);
            $type = 'encrypted';
        }

        $query = "INSERT INTO " . $this->table . " (setting_group, setting_key, setting_value, value_type, is_secret, updated_by, created_at) 
                  VALUES (:group, :key, :val, :type, :secret, :updated_by, NOW()) 
                  ON DUPLICATE KEY UPDATE 
                  setting_value = :val2, value_type = :type2, is_secret = :secret2, updated_by = :updated_by2, updated_at = NOW()";

        $this->db->query($query);
        $this->db->bind('group', $group);
        $this->db->bind('key', $key);
        $this->db->bind('val', $storedValue);
        $this->db->bind('type', $type);
        $this->db->bind('secret', $isSecret);
        $this->db->bind('updated_by', $updatedBy);

        $this->db->bind('val2', $storedValue);
        $this->db->bind('type2', $type);
        $this->db->bind('secret2', $isSecret);
        $this->db->bind('updated_by2', $updatedBy);

        return $this->db->execute();
    }

    /**
     * Ambil seluruh konfigurasi dalam suatu group
     *
     * @param string $group
     * @return array
     */
    public function getAllByGroup(string $group): array {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE setting_group = :group");
        $this->db->bind('group', $group);
        $rows = $this->db->resultSet();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $this->castValue($row['setting_value'], $row['value_type'], (bool)$row['is_secret']);
        }
        return $settings;
    }

    /**
     * Ambil seluruh kebijakan OTP dari database
     *
     * @return array
     */
    public function getOtpPolicies(): array {
        $raw = $this->getAllByGroup('otp');
        return [
            'otp_length' => (int)($raw['otp_length'] ?? 6),
            'otp_expiry_minutes' => (int)($raw['otp_expiry_minutes'] ?? 5),
            'otp_max_attempts' => (int)($raw['otp_max_attempts'] ?? 5),
            'otp_cooldown_seconds' => (int)($raw['otp_cooldown_seconds'] ?? 60),
            'otp_max_requests_phone_hour' => (int)($raw['otp_max_requests_phone_hour'] ?? 5),
            'otp_max_requests_ip_hour' => (int)($raw['otp_max_requests_ip_hour'] ?? 15),
            'otp_allowed_channels' => (string)($raw['otp_allowed_channels'] ?? 'whatsapp'),
            'otp_auto_register' => (bool)($raw['otp_auto_register'] ?? true),
            'otp_login_required' => (bool)($raw['otp_login_required'] ?? false),
            'otp_wa_template' => (string)($raw['otp_wa_template'] ?? 'Halo! Kode verifikasi Anda di *{app_name}* adalah: *{otp}*. Kode berlaku selama {expiry} menit. Jangan bagikan kode ini kepada siapa pun.'),
            'otp_email_template' => (string)($raw['otp_email_template'] ?? '<p>Halo <strong>{name}</strong>,</p><p>Kode verifikasi OTP Anda untuk {app_name} adalah: <strong style="font-size:20px;letter-spacing:3px;">{otp}</strong>.</p><p>Kode ini berlaku selama {expiry} menit. Jangan berikan kode ini kepada pihak mana pun.</p>')
        ];
    }

    /**
     * Konversi tipe data string dari database ke tipe data PHP yang sesuai
     */
    private function castValue($value, string $type, bool $isSecret) {
        if ($value === null) {
            return null;
        }

        if ($isSecret || $type === 'encrypted') {
            return SecretVault::decrypt((string)$value);
        }

        switch ($type) {
            case 'integer':
            case 'int':
                return (int)$value;
            case 'boolean':
            case 'bool':
                return (bool)$value;
            case 'json':
                $decoded = json_decode((string)$value, true);
                return is_array($decoded) ? $decoded : $value;
            default:
                return (string)$value;
        }
    }
}
