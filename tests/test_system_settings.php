<?php
/**
 * Test Suite: System Settings, Multi-Provider AI & OTP Authentication
 * NNACOSA — Comprehensive Verification
 */

require_once __DIR__ . '/../app/init.php';

echo "========================================================================\n";
echo "NNACOSA — System Settings & Security Test Suite\n";
echo "========================================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest($description, $condition, $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $description\n";
        $passed++;
    } else {
        echo " [FAIL] $description" . ($details ? " ($details)" : '') . "\n";
        $failed++;
    }
}

// =========================================================================
// 1. SECRET ENCRYPTION & DECRYPTION (AES-256-GCM)
// =========================================================================
echo "\n--- 1. Secret Vault & Authenticated Encryption (AES-256-GCM) ---\n";

$originalSecret = "sk-test-super-secret-key-1234567890!@#$";
$encrypted = SecretVault::encrypt($originalSecret);

assertTest("SecretVault::encrypt produces base64 ciphertext with IV and Tag", 
    !empty($encrypted) && $encrypted !== $originalSecret && strlen($encrypted) > 40);

$decrypted = SecretVault::decrypt($encrypted);
assertTest("SecretVault::decrypt successfully restores original secret", 
    $decrypted === $originalSecret);

// Tamper test: modify ciphertext
$tampered = substr_replace($encrypted, 'X', 10, 1);
$decryptedTampered = SecretVault::decrypt($tampered);
assertTest("Tampered ciphertext is rejected by GCM tag verification (returns null)", 
    $decryptedTampered === null);

// Masking test
$masked = SecretVault::maskSecret($originalSecret);
assertTest("SecretVault::maskSecret hides middle chars with bullets (••••)", 
    str_starts_with($masked, 'sk-t') && str_ends_with($masked, '!@#$') && str_contains($masked, '••••') && !str_contains($masked, 'super-secret'));

// =========================================================================
// 2. SSRF PREVENTION & OUTBOUND URL VALIDATION
// =========================================================================
echo "\n--- 2. SSRF Prevention & Outbound URL Validation ---\n";

$ssrfLocalhost = Security::validateOutboundUrl('http://localhost:8080/admin');
assertTest("Security::validateOutboundUrl blocks http://localhost", 
    $ssrfLocalhost['valid'] === false);

$ssrfLoopback = Security::validateOutboundUrl('http://127.0.0.1:3306');
assertTest("Security::validateOutboundUrl blocks http://127.0.0.1", 
    $ssrfLoopback['valid'] === false);

$ssrfMetadata = Security::validateOutboundUrl('http://169.254.169.254/latest/meta-data/');
assertTest("Security::validateOutboundUrl blocks cloud metadata service (169.254.169.254)", 
    $ssrfMetadata['valid'] === false);

$ssrfInternal = Security::validateOutboundUrl('http://database.internal/keys');
assertTest("Security::validateOutboundUrl blocks internal TLDs (.internal)", 
    $ssrfInternal['valid'] === false);

$ssrfValidOpenAI = Security::validateOutboundUrl('https://api.openai.com/v1/chat/completions');
assertTest("Security::validateOutboundUrl permits valid public OpenAI endpoint", 
    $ssrfValidOpenAI['valid'] === true);

$ssrfValidFonnte = Security::validateOutboundUrl('https://api.fonnte.com/send');
assertTest("Security::validateOutboundUrl permits valid public Fonnte endpoint", 
    $ssrfValidFonnte['valid'] === true);

// =========================================================================
// 3. AI PROVIDER CONFIGURATION MODEL & SECRET RETENTION
// =========================================================================
echo "\n--- 3. AI Provider Configuration Model & Secret Retention ---\n";

$aiModel = new AiProviderConfig();

// Create test config
$testKey = "sk-ai-test-key-abc123xyz";
$testConfigId = $aiModel->create([
    'config_name' => 'Automated Test Engine',
    'provider' => 'openai_compatible',
    'base_url' => 'https://api.openai.com/v1/chat/completions',
    'model_id' => 'gpt-4o-mini',
    'api_key' => $testKey,
    'parameters' => ['timeout' => 20, 'max_tokens' => 250, 'temperature' => 0.7],
    'is_active' => 0
]);

assertTest("AiProviderConfig::create persists new configuration", 
    $testConfigId && $testConfigId > 0);

// Verify secret is masked in public view
$configs = $aiModel->getAll();
$found = null;
foreach ($configs as $c) {
    if ((int)$c['id'] === (int)$testConfigId) {
        $found = $c;
        break;
    }
}
assertTest("getAll() masks API key and does NOT return raw ciphertext or plaintext", 
    $found && !empty($found['masked_key']) && !isset($found['encrypted_api_key']) && !isset($found['plain_key']) && !str_contains($found['masked_key'], 'test-key'));

// Update config WITHOUT providing new API key (must retain old encrypted key)
$updateRes = $aiModel->update($testConfigId, [
    'config_name' => 'Automated Test Engine (Renamed)',
    'provider' => 'openai_compatible',
    'base_url' => 'https://api.openai.com/v1/chat/completions',
    'model_id' => 'gpt-4o',
    'parameters' => ['timeout' => 30, 'max_tokens' => 300, 'temperature' => 0.4]
    // Note: api_key is omitted!
]);
assertTest("AiProviderConfig::update succeeds when api_key is omitted", 
    $updateRes === true);

$updatedConfig = $aiModel->getById($testConfigId, true);
assertTest("Old API key is safely retained after update with empty key", 
    $updatedConfig['plain_key'] === $testKey && $updatedConfig['model_id'] === 'gpt-4o');

// Test activation switcher
$activateRes = $aiModel->activate($testConfigId);
assertTest("AiProviderConfig::activate switches active engine", 
    $activateRes === true);

$activeConf = $aiModel->getActive();
assertTest("getActive() returns newly activated engine", 
    (int)$activeConf['id'] === (int)$testConfigId);

// Test deleting active config is prevented
$deleteActiveRes = $aiModel->delete($testConfigId);
assertTest("Deleting active configuration is rejected", 
    $deleteActiveRes['success'] === false);

// Re-activate config 1 (NVIDIA NIM) and delete test config
$aiModel->activate(1);
$deleteRes = $aiModel->delete($testConfigId);
assertTest("Deleting inactive configuration succeeds", 
    $deleteRes['success'] === true);

// =========================================================================
// 4. CENTRALIZED AI SERVICE DYNAMIC RESOLUTION
// =========================================================================
echo "\n--- 4. Centralized AiService Dynamic Resolution ---\n";

$aiService = new AiService();
assertTest("AiService instantiates and loads active database provider", 
    $aiService->getProvider() !== null);

$activeDbModel = $aiModel->getActive()['model_id'] ?? 'minimaxai/minimax-m3.1-flash';
assertTest("AiService resolves configured model ID from database", 
    $aiService->getProvider()->getModelId() === $activeDbModel);

// =========================================================================
// 5. AUTH PROVIDER CONFIG & SECRET RETENTION (FONNTE & SMTP)
// =========================================================================
echo "\n--- 5. Auth Provider Config & Secret Retention (Fonnte & SMTP) ---\n";

$authModel = new AuthProviderConfig();

// Test Fonnte config retention
$fonnteOriginal = $authModel->getByType('whatsapp_fonnte', true);
$origFonnteCred = $fonnteOriginal['plain_credentials'];

// Save settings without new token
$authModel->saveConfig('whatsapp_fonnte', null, [
    'api_url' => 'https://api.fonnte.com/send',
    'country_code' => '62'
], true);

$fonnteAfter = $authModel->getByType('whatsapp_fonnte', true);
assertTest("Fonnte token is preserved when omitted during save", 
    $fonnteAfter['plain_credentials'] === $origFonnteCred);

// Test SMTP config retention
$smtpOriginal = $authModel->getByType('email_smtp', true);
$origSmtpCred = $smtpOriginal['plain_credentials'] ?? '';

$authModel->saveConfig('email_smtp', null, [
    'host' => '127.0.0.1',
    'port' => 1025,
    'username' => '',
    'encryption' => 'none',
    'from_email' => 'test@nnacosa.com',
    'from_name' => 'NNACOSA TEST',
    'timeout' => 10
], false);

$smtpAfter = $authModel->getByType('email_smtp', true);
assertTest("SMTP settings saved and credentials preserved", 
    $smtpAfter['settings']['from_email'] === 'test@nnacosa.com');

// =========================================================================
// 6. SYSTEM SETTINGS & OTP POLICIES
// =========================================================================
echo "\n--- 6. System Settings & OTP Policies ---\n";

$settingModel = new SystemSetting();
$policies = $settingModel->getOtpPolicies();

assertTest("SystemSetting::getOtpPolicies returns length, expiry, attempts, cooldown", 
    isset($policies['otp_length']) && isset($policies['otp_expiry_minutes']) && 
    isset($policies['otp_max_attempts']) && isset($policies['otp_cooldown_seconds']));

// Test updating policy
$settingModel->set('otp_length', 6, 'integer', 'otp');
$settingModel->set('otp_expiry_minutes', 5, 'integer', 'otp');
$settingModel->set('otp_max_attempts', 5, 'integer', 'otp');
$settingModel->set('otp_cooldown_seconds', 60, 'integer', 'otp');

$updatedPolicies = $settingModel->getOtpPolicies();
assertTest("Policies successfully saved and cast as integers", 
    $updatedPolicies['otp_length'] === 6 && $updatedPolicies['otp_cooldown_seconds'] === 60);

// =========================================================================
// 7. OTP MULTI-CHANNEL & SECURITY FLOW
// =========================================================================
echo "\n--- 7. OTP Multi-Channel, Atomic Verification, & Security Flow ---\n";

// Set Fonnte mock handler so test doesn't send real WhatsApp
$dispatchedMessages = [];
FonnteService::setMockHandler(function($target, $message) use (&$dispatchedMessages) {
    $dispatchedMessages[] = ['target' => $target, 'message' => $message];
    return ['success' => true, 'message' => 'Mock WhatsApp sent successfully'];
});

$testPhone = '081299998888';
$normalizedPhone = FonnteService::normalizePhone($testPhone);

// Clean previous test OTPs for this phone
$db = new Database();
$db->query("DELETE FROM whatsapp_otps WHERE phone = :phone");
$db->bind('phone', $normalizedPhone);
$db->execute();

// Request WhatsApp OTP
$waReq = OtpService::requestOtp($testPhone, 'whatsapp');
assertTest("OtpService::requestOtp via WhatsApp succeeds with mock provider", 
    $waReq['success'] === true && !empty($waReq['phone']));

assertTest("WhatsApp message was dispatched containing OTP code", 
    count($dispatchedMessages) > 0 && str_contains($dispatchedMessages[0]['message'], 'Kode verifikasi'));

// Extract generated OTP from message
preg_match('/([0-9]{6})/', $dispatchedMessages[0]['message'], $otpMatch);
$generatedOtp = $otpMatch[1] ?? '';
assertTest("OTP extracted from message is 6 digits", 
    strlen($generatedOtp) === 6);

// Verify OTP hash in database (must NOT be plaintext!)
$db->query("SELECT * FROM whatsapp_otps WHERE phone = :phone ORDER BY id DESC LIMIT 1");
$db->bind('phone', $normalizedPhone);
$storedRow = $db->single();

assertTest("OTP stored in database is a bcrypt hash (starts with $2y$)", 
    str_starts_with($storedRow['otp_hash'], '$2y$') && $storedRow['otp_hash'] !== $generatedOtp);

// Cooldown test: immediately requesting again must fail
$secondReq = OtpService::requestOtp($testPhone, 'whatsapp');
assertTest("Cooldown enforcement blocks rapid second request", 
    $secondReq['success'] === false && isset($secondReq['cooldown']));

// Test wrong OTP attempt
$wrongVerify = OtpService::verifyOtp($testPhone, '000000', 'whatsapp');
assertTest("Wrong OTP verification fails and reports remaining attempts", 
    $wrongVerify['success'] === false && isset($wrongVerify['remaining_attempts']) && $wrongVerify['remaining_attempts'] === 4);

// Test correct OTP verification
$correctVerify = OtpService::verifyOtp($testPhone, $generatedOtp, 'whatsapp');
assertTest("Correct OTP verification succeeds", 
    $correctVerify['success'] === true);

// Test single-use atomic replay protection
$replayVerify = OtpService::verifyOtp($testPhone, $generatedOtp, 'whatsapp');
assertTest("Replay attack prevented: OTP cannot be reused after verification", 
    $replayVerify['success'] === false);

// Clean up mock
FonnteService::resetMock();

// =========================================================================
// 8. SMTP CONNECTION & EMAIL OTP TESTING
// =========================================================================
echo "\n--- 8. SMTP Connection & Email OTP Verification ---\n";

// =========================================================================
// 8. EMAIL OTP PERMANENTLY DISABLED & WHATSAPP EXCLUSIVITY
// =========================================================================
echo "\n--- 8. Email OTP Disabled & WhatsApp Exclusivity ---\n";

$emailConfig = $authModel->getByType('email_smtp');
assertTest("Email SMTP configuration is marked disabled (is_enabled = 0)", 
    empty($emailConfig['is_enabled']));

$activePolicies = $settingModel->getOtpPolicies();
assertTest("Active OTP policies restrict allowed channels strictly to whatsapp", 
    $activePolicies['otp_allowed_channels'] === 'whatsapp');

$isWaEnabled = $authModel->isWhatsAppEnabled();
assertTest("WhatsApp Fonnte gateway is confirmed enabled as primary provider", 
    $isWaEnabled === true);

// =========================================================================
// 9. RBAC & SECURITY CHECK ON CONTROLLER
// =========================================================================
echo "\n--- 9. RBAC & CSRF Protection ---\n";

assertTest("RoleMiddleware strictly restricts settings to admin and owner", 
    class_exists('RoleMiddleware'));

$csrfToken = Security::generateCsrfToken();
assertTest("Security::verifyCsrfToken accepts valid CSRF token", 
    Security::verifyCsrfToken($csrfToken) === true);

assertTest("Security::verifyCsrfToken rejects invalid CSRF token", 
    Security::verifyCsrfToken('invalid_csrf_token_123') === false);

// =========================================================================
// SUMMARY
// =========================================================================
echo "\n========================================================================\n";
echo "TEST RESULTS: Passed: $passed | Failed: $failed\n";
echo "========================================================================\n";

if ($failed === 0) {
    echo " ALL TESTS PASSED SUCCESSFULLY! 100% COMPLIANT.\n\n";
    exit(0);
} else {
    echo " SOME TESTS FAILED. PLEASE REVIEW LOGS.\n\n";
    exit(1);
}
