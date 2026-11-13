<?php
/**
 * Model AuthProviderConfig — WhatsApp Fonnte & Email SMTP Provider Configurations
 */
class AuthProviderConfig extends Model {
    protected $table = 'auth_provider_configs';

    /**
     * Dapatkan konfigurasi provider berdasarkan tipe ('whatsapp_fonnte', 'email_smtp')
     *
     * @param string $type
     * @param bool $withPlainCredentials
     * @return array|null
     */
    public function getByType(string $type, bool $withPlainCredentials = false): ?array {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE provider_type = :type LIMIT 1");
        $this->db->bind('type', $type);
        $row = $this->db->single();

        if (!$row) {
            return null;
        }

        $row['settings'] = !empty($row['settings_json']) ? json_decode($row['settings_json'], true) : [];
        $row['has_credentials'] = !empty($row['encrypted_credentials']);
        $plainCreds = $row['has_credentials'] ? SecretVault::decrypt($row['encrypted_credentials']) : '';
        $row['masked_credentials'] = SecretVault::maskSecret($plainCreds);

        if ($withPlainCredentials) {
            $row['plain_credentials'] = $plainCreds;
        }
        unset($row['encrypted_credentials']);

        return $row;
    }

    /**
     * Simpan atau perbarui konfigurasi provider
     * Jika $newCredentials null atau string kosong, kredensial lama dipertahankan!
     *
     * @param string $type
     * @param string|null $newCredentials
     * @param array $settings
     * @param bool $isEnabled
     * @return bool
     */
    public function saveConfig(string $type, ?string $newCredentials, array $settings, bool $isEnabled): bool {
        $existing = $this->getByType($type, true);

        $encryptedCreds = null;
        if (!empty($newCredentials)) {
            $encryptedCreds = SecretVault::encrypt($newCredentials);
        } elseif ($existing && !empty($existing['plain_credentials'])) {
            $encryptedCreds = SecretVault::encrypt($existing['plain_credentials']);
        }

        $settingsJson = json_encode($settings);
        $enabledInt = $isEnabled ? 1 : 0;

        $query = "INSERT INTO " . $this->table . " 
                  (provider_type, encrypted_credentials, settings_json, is_enabled, created_at) 
                  VALUES (:type, :creds, :settings, :enabled, NOW()) 
                  ON DUPLICATE KEY UPDATE 
                  settings_json = :settings2, is_enabled = :enabled2, updated_at = NOW()";

        if (!empty($newCredentials)) {
            $query .= ", encrypted_credentials = :creds2";
        }

        $this->db->query($query);
        $this->db->bind('type', $type);
        $this->db->bind('creds', $encryptedCreds);
        $this->db->bind('settings', $settingsJson);
        $this->db->bind('enabled', $enabledInt);

        $this->db->bind('settings2', $settingsJson);
        $this->db->bind('enabled2', $enabledInt);

        if (!empty($newCredentials)) {
            $this->db->bind('creds2', $encryptedCreds);
        }

        return $this->db->execute();
    }

    /**
     * Update hasil pengujian koneksi provider
     *
     * @param string $type
     * @param string $status SUCCESS|FAILED|ERROR
     * @param int|null $latencyMs
     * @param string|null $message
     * @return bool
     */
    public function updateTestStatus(string $type, string $status, ?int $latencyMs = null, ?string $message = null): bool {
        $this->db->query("UPDATE " . $this->table . " 
                          SET last_tested_at = NOW(), last_test_status = :status, last_test_latency_ms = :latency, last_test_message = :msg 
                          WHERE provider_type = :type");
        $this->db->bind('status', $status);
        $this->db->bind('latency', $latencyMs);
        $this->db->bind('msg', $message ? substr($message, 0, 250) : null);
        $this->db->bind('type', $type);
        return $this->db->execute();
    }

    /**
     * Cek apakah WhatsApp Fonnte aktif
     *
     * @return bool
     */
    public function isWhatsAppEnabled(): bool {
        $conf = $this->getByType('whatsapp_fonnte');
        return !empty($conf['is_enabled']);
    }

    /**
     * Cek apakah Email SMTP aktif
     *
     * @return bool
     */
    public function isEmailEnabled(): bool {
        $conf = $this->getByType('email_smtp');
        return !empty($conf['is_enabled']);
    }
}
