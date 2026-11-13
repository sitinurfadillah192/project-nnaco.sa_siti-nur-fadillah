<?php
/**
 * SecretVault — Authenticated Encryption for Sensitive Credentials (AES-256-GCM)
 *
 * Mengenkripsi dan mendekripsi secret (API keys, tokens, SMTP passwords) menggunakan cipher
 * AES-256-GCM dengan authenticated data tag. Master key disimpan terpisah pada environment/config.
 */
class SecretVault {
    private const CIPHER = 'aes-256-gcm';
    private const IV_LENGTH = 12; // 96 bits recommended for GCM
    private const TAG_LENGTH = 16; // 128 bits auth tag

    /**
     * Dapatkan master encryption key biner (32 bytes = 256 bits)
     *
     * @return string
     * @throws RuntimeException Jika encryption key belum diset
     */
    private static function getKey(): string {
        $keyHex = defined('SYSTEM_ENCRYPTION_KEY') ? SYSTEM_ENCRYPTION_KEY : (getenv('SYSTEM_ENCRYPTION_KEY') ?: '');
        $keyHex = trim((string)$keyHex);

        if (empty($keyHex)) {
            throw new RuntimeException('Encryption key belum dikonfigurasi di server. Silakan atur SYSTEM_ENCRYPTION_KEY pada file .env.');
        }

        // Jika hex 64 karakter -> konversi ke 32 byte biner
        if (ctype_xdigit($keyHex) && strlen($keyHex) === 64) {
            return hex2bin($keyHex);
        }

        // Jika string biasa -> gunakan SHA-256 untuk derivasi key 32 bytes
        return hash('sha256', $keyHex, true);
    }

    /**
     * Cek apakah master encryption key tersedia
     *
     * @return bool
     */
    public static function hasMasterKey(): bool {
        $keyHex = defined('SYSTEM_ENCRYPTION_KEY') ? SYSTEM_ENCRYPTION_KEY : (getenv('SYSTEM_ENCRYPTION_KEY') ?: '');
        return !empty(trim((string)$keyHex));
    }

    /**
     * Enkripsi plaintext menggunakan AES-256-GCM
     *
     * @param string $plaintext
     * @param string $additionalData AAD opsional
     * @return string Base64 string terenkripsi (IV + Tag + Ciphertext)
     */
    public static function encrypt(string $plaintext, string $additionalData = ''): string {
        if ($plaintext === '') {
            return '';
        }

        $key = self::getKey();
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $additionalData,
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Enkripsi secret gagal dilakukan oleh OpenSSL.');
        }

        // Gabungkan IV (12) + Tag (16) + Ciphertext
        $combined = $iv . $tag . $ciphertext;
        return base64_encode($combined);
    }

    /**
     * Dekripsi ciphertext menggunakan AES-256-GCM
     *
     * @param string $payload Base64 string
     * @param string $additionalData AAD opsional
     * @return string|null Plaintext asli atau null jika gagal/tampered
     */
    public static function decrypt(?string $payload, string $additionalData = ''): ?string {
        if (empty($payload)) {
            return null;
        }

        $raw = base64_decode($payload, true);
        if ($raw === false || strlen($raw) < (self::IV_LENGTH + self::TAG_LENGTH)) {
            return null;
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $tag = substr($raw, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH + self::TAG_LENGTH);

        try {
            $key = self::getKey();
            $plaintext = openssl_decrypt(
                $ciphertext,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                $additionalData
            );

            if ($plaintext === false) {
                return null;
            }

            return $plaintext;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Samarkan secret untuk tampilan UI / API (e.g., nvapi-••••••••••••zzjh)
     *
     * @param string|null $secret
     * @return string
     */
    public static function maskSecret(?string $secret): string {
        if (empty($secret)) {
            return '';
        }

        $len = strlen($secret);
        if ($len <= 8) {
            return str_repeat('•', 8);
        }

        $prefix = substr($secret, 0, 4);
        $suffix = substr($secret, -4);
        return $prefix . '••••••••••••' . $suffix;
    }
}
