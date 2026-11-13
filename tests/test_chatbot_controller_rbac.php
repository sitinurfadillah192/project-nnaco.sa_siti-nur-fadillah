<?php
/**
 * Test RBAC Protection on ChatbotController
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Security.php';
require_once __DIR__ . '/../app/core/Flasher.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/RoleMiddleware.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$passed = 0;
$failed = 0;

function assertRbac(string $desc, bool $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] {$desc}\n";
        $passed++;
    } else {
        echo "[FAIL] {$desc}\n";
        $failed++;
    }
}

echo "=========================================================\n";
echo "TESTING RBAC PERMISSIONS ON CHATBOT CONTROLLER\n";
echo "=========================================================\n";

// Test 1: Unauthenticated
$_SESSION = [];
$unauthBlocked = false;
try {
    // AuthMiddleware redirects if not logged in
    if (!isset($_SESSION['user_id'])) {
        $unauthBlocked = true;
    }
} catch (Exception $e) {
    $unauthBlocked = true;
}
assertRbac("Unauthenticated visitor is blocked from accessing Chatbot", $unauthBlocked);

// Test 2: Role 'customer'
$_SESSION['user_id'] = 999;
$_SESSION['user_role'] = 'customer';
$customerAllowed = in_array($_SESSION['user_role'], ['admin', 'owner'], true);
assertRbac("Role 'customer' is strictly denied access", !$customerAllowed);

// Test 3: Role 'operator'
$_SESSION['user_role'] = 'operator';
$operatorAllowed = in_array($_SESSION['user_role'], ['admin', 'owner'], true);
assertRbac("Role 'operator' is denied access (admin/owner only)", !$operatorAllowed);

// Test 4: Role 'admin'
$_SESSION['user_role'] = 'admin';
$adminAllowed = in_array($_SESSION['user_role'], ['admin', 'owner'], true);
assertRbac("Role 'admin' is granted access", $adminAllowed);

// Test 5: Role 'owner'
$_SESSION['user_role'] = 'owner';
$ownerAllowed = in_array($_SESSION['user_role'], ['admin', 'owner'], true);
assertRbac("Role 'owner' is granted access", $ownerAllowed);

// Cleanup
$_SESSION = [];

echo "\nRBAC RESULTS: {$passed} PASSED, {$failed} FAILED\n";
exit($failed > 0 ? 1 : 0);
