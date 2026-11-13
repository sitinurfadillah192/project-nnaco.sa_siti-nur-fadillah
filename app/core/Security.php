<?php
class Security {
    // Generate CSRF Token
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Verify CSRF Token
    public static function verifyCsrfToken($token) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }

    // XSS Prevention (Escape HTML output)
    public static function escape($string) {
        if ($string === null) return '';
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * SSRF Prevention: Validasi URL sebelum memanggil API eksternal
     * Mencegah akses ke alamat private RFC 1918, loopback, link-local (169.254.x.x cloud metadata)
     *
     * @param string $url
     * @param bool $allowLocalhost Hanya untuk testing/sandbox lokal jika diaktifkan eksplisit
     * @return array ['valid' => bool, 'error' => string|null, 'host' => string|null]
     */
    public static function validateOutboundUrl(string $url, bool $allowLocalhost = false): array {
        $trimmed = trim($url);
        if (empty($trimmed)) {
            return ['valid' => false, 'error' => 'URL tidak boleh kosong.', 'host' => null];
        }

        $parsed = parse_url($trimmed);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return ['valid' => false, 'error' => 'Format URL tidak valid (harus menyertakan http:// atau https://).', 'host' => null];
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['valid' => false, 'error' => 'Skema URL tidak diizinkan. Hanya http:// dan https:// yang didukung.', 'host' => null];
        }

        $host = strtolower($parsed['host']);

        // Cek localhost/loopback
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1' || $host === '0.0.0.0') {
            if (!$allowLocalhost) {
                return ['valid' => false, 'error' => 'Akses ke localhost/loopback ditolak demi keamanan SSRF.', 'host' => $host];
            }
            return ['valid' => true, 'error' => null, 'host' => $host];
        }

        // Cek cloud metadata & internal suffixes
        $forbiddenSuffixes = ['.local', '.internal', '.lan', '.corp', '.home', '.test', '.invalid'];
        foreach ($forbiddenSuffixes as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return ['valid' => false, 'error' => "Akses ke domain internal ('{$suffix}') ditolak demi keamanan SSRF.", 'host' => $host];
            }
        }

        if (str_contains($host, '169.254.169.254') || $host === 'metadata.google.internal') {
            return ['valid' => false, 'error' => 'Akses ke cloud metadata service ditolak demi keamanan SSRF.', 'host' => $host];
        }

        // Cek apakah host langsung berupa IP address
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                if (!$allowLocalhost) {
                    return ['valid' => false, 'error' => "Akses ke alamat IP privat/reserved ({$host}) ditolak demi keamanan SSRF.", 'host' => $host];
                }
            }
            return ['valid' => true, 'error' => null, 'host' => $host];
        }

        // Resolusi hostname ke IP untuk cek rentang IP terlarang
        $ip = gethostbyname($host);
        if ($ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                if (!$allowLocalhost) {
                    return ['valid' => false, 'error' => "Hostname '{$host}' merujuk ke alamat IP privat ({$ip}) yang ditolak demi keamanan SSRF.", 'host' => $host];
                }
            }
        } else {
            // DNS tidak teresolusi di lingkungan saat ini. Validasi format nama domain standar.
            if (!str_contains($host, '.')) {
                return ['valid' => false, 'error' => "Hostname '{$host}' bukan domain publik yang valid.", 'host' => $host];
            }
        }

        return ['valid' => true, 'error' => null, 'host' => $host];
    }
}
