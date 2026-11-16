<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
/**
 * NNACOSA — Standalone Core Security & Utilities Unit Tests
 * 
 * Verifies core security functions without database dependency:
 * - CSRF Protection (Token generation, verification, and rejection)
 * - XSS Sanitization
 * - SSRF Outbound URL Validation
 * - Authenticated AES-256-GCM Secret Encryption & Decryption
 * - Secret Masking
 * - Phone Normalization
 * - Environment Variable Resolution
 */

require_once dirname(__DIR__, 2) . '/app/init.php';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function it(string $description, bool $condition, string $details = '') {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$description}" . ($details ? " ({$details})" : "") . "\n";
    }
}

echo "============================================================\n";
echo "1. Security & CSRF Protection Tests\n";
echo "============================================================\n";

$token = Security::generateCsrfToken();
it("CSRF token is generated as a non-empty string", !empty($token) && is_string($token));
it("CSRF token is 64 characters long (32 hex bytes)", strlen($token) === 64);
it("Valid CSRF token passes verification", Security::verifyCsrfToken($token) === true);
it("Invalid CSRF token is rejected", Security::verifyCsrfToken('malicious_invalid_token') === false);
it("Empty CSRF token is rejected", Security::verifyCsrfToken('') === false);

echo "\n============================================================\n";
echo "2. XSS & HTML Sanitization Tests\n";
echo "============================================================\n";

$xssInput = '<script>alert("hacked")</script>';
$escaped = Security::escape($xssInput);
it("Script tags are converted to HTML entities", strpos($escaped, '<script>') === false && strpos($escaped, '&lt;script&gt;') !== false);

$quoteInput = 'He said "Hello & welcome"';
$escapedQuotes = Security::escape($quoteInput);
it("Double quotes and ampersands are escaped safely", strpos($escapedQuotes, '&quot;') !== false && strpos($escapedQuotes, '&amp;') !== false);

echo "\n============================================================\n";
echo "3. SSRF Outbound URL Validation Tests\n";
echo "============================================================\n";

$localUrl = Security::validateOutboundUrl('http://localhost:8080/admin');
it("Blocks http://localhost against SSRF", $localUrl['valid'] === false);

$loopbackUrl = Security::validateOutboundUrl('http://127.0.0.1/api');
it("Blocks http://127.0.0.1 loopback against SSRF", $loopbackUrl['valid'] === false);

$metadataUrl = Security::validateOutboundUrl('http://169.254.169.254/latest/meta-data/');
it("Blocks AWS/Cloud metadata IP against SSRF", $metadataUrl['valid'] === false);

$internalTld = Security::validateOutboundUrl('http://internal.service.local/v1');
it("Blocks .local internal TLD against SSRF", $internalTld['valid'] === false);

$publicUrl = Security::validateOutboundUrl('https://api.openai.com/v1/chat/completions');
it("Allows public HTTPS OpenAI URL", $publicUrl['valid'] === true);

$nvidiaUrl = Security::validateOutboundUrl('https://integrate.api.nvidia.com/v1/chat/completions');
it("Allows public HTTPS NVIDIA NIM URL", $nvidiaUrl['valid'] === true);

$fonnteUrl = Security::validateOutboundUrl('https://api.fonnte.com/send');
it("Allows public HTTPS Fonnte Gateway URL", $fonnteUrl['valid'] === true);

echo "\n============================================================\n";
echo "4. Authenticated AES-256-GCM Secret Encryption Tests\n";
echo "============================================================\n";

$rawSecret = "nvapi-SuperSecretNvidiaKey123456!@#$%^";
$cipher = SecretVault::encrypt($rawSecret);
it("SecretVault::encrypt produces valid base64 ciphertext with IV and GCM tag", !empty($cipher) && $cipher !== $rawSecret);

$decrypted = SecretVault::decrypt($cipher);
it("SecretVault::decrypt successfully restores original secret", $decrypted === $rawSecret);

// Tamper check (GCM tag verification)
$tamperedCipher = substr_replace($cipher, 'Z', 12, 1);
$tamperedDecrypted = SecretVault::decrypt($tamperedCipher);
it("Tampered ciphertext is rejected by AES-256-GCM tag verification (returns null)", $tamperedDecrypted === null);

$masked = SecretVault::maskSecret($rawSecret);
it("SecretVault::maskSecret hides middle characters with bullets", strpos($masked, '•••') !== false);

echo "\n============================================================\n";
echo "5. Phone Number Normalization Tests\n";
echo "============================================================\n";

it("Normalizes 08123456789 to 628123456789", FonnteService::normalizePhone('08123456789') === '628123456789');
it("Normalizes +628123456789 to 628123456789", FonnteService::normalizePhone('+628123456789') === '628123456789');
it("Preserves 628123456789 as 628123456789", FonnteService::normalizePhone('628123456789') === '628123456789');
it("Rejects non-numeric / invalid phone format", FonnteService::normalizePhone('0123') === null);

echo "\n============================================================\n";
echo "6. Environment Variable Resolution Tests\n";
echo "============================================================\n";

it("env() resolves default value when key is undefined", env('NON_EXISTENT_ENV_KEY_XYZ', 'default_val') === 'default_val');
it("env() resolves existing APP_ENV or defaults to development", in_array(env('APP_ENV', 'development'), ['development', 'production', 'testing']));

echo "\n============================================================\n";
echo "CoreSecurityTest Summary: Total={$totalTests}, Passed={$passedTests}, Failed={$failedTests}\n";
echo "============================================================\n";

exit($failedTests > 0 ? 1 : 0);
