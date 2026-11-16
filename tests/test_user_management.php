<?php
/**
 * Test Suite: Fitur Manajemen User (Admin & Owner)
 */

require_once __DIR__ . '/../app/init.php';
require_once __DIR__ . '/../app/models/User.php';

echo "============================================================\n";
echo "NNACOSA — User Management Test Suite\n";
echo "============================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $description\n";
        $passed++;
    } else {
        echo " [FAIL] $description\n";
        $failed++;
    }
}

$userModel = new User();

// Test 1: Model getAllRoles returns all roles
$roles = $userModel->getAllRoles();
assertTest("Model getAllRoles() returns 4 roles (customer, admin, operator, owner)", count($roles) === 4);

// Test 2: Model getUserStats returns required metrics
$stats = $userModel->getUserStats();
assertTest("Model getUserStats() returns total_users, total_customers, and total_staff", 
    isset($stats['total_users']) && isset($stats['total_customers']) && isset($stats['total_staff']));

// Test 3: Model getAllUsers with pagination
$users = $userModel->getAllUsers(5, 0);
assertTest("Model getAllUsers() returns array of users with role_name", 
    is_array($users) && count($users) <= 5 && isset($users[0]['role_name']));

// Test 4: Search filter by name
$searchUsers = $userModel->getAllUsers(10, 0, 'Admin');
assertTest("Model getAllUsers() filters by keyword search 'Admin'", 
    count($searchUsers) >= 1 && stripos($searchUsers[0]['name'], 'Admin') !== false);

// Test 5: Role filter
$adminUsers = $userModel->getAllUsers(10, 0, '', 2); // Role 2 = admin
assertTest("Model getAllUsers() filters by role_id = 2 (admin)", 
    count($adminUsers) >= 1 && (int)$adminUsers[0]['role_id'] === 2);

// Test 6: Create new user
$testEmail = 'test_mgmt_' . time() . '@nnacosa.test';
$testPhone = '628999' . substr(strval(time()), -6);
$newUserId = $userModel->createUser([
    'name' => 'Testing User Mgmt',
    'email' => $testEmail,
    'phone' => $testPhone,
    'role_id' => 1,
    'password' => 'secret123'
]);
assertTest("Model createUser() inserts a new user successfully", (bool)$newUserId);

// Test 7: Verify created user has hashed password and correct data
$createdUser = $userModel->getUserById($newUserId);
assertTest("Created user has valid password_verify() and role_name", 
    $createdUser && password_verify('secret123', $createdUser['password']) && $createdUser['role'] === 'customer');

// Test 8: Uniqueness checks for email and phone
assertTest("isEmailTaken() correctly identifies duplicate email", $userModel->isEmailTaken($testEmail) === true);
assertTest("isEmailTaken() excludes current user ID when editing", $userModel->isEmailTaken($testEmail, $newUserId) === false);
assertTest("isPhoneTaken() correctly identifies duplicate phone", $userModel->isPhoneTaken($testPhone) === true);
assertTest("isPhoneTaken() excludes current user ID when editing", $userModel->isPhoneTaken($testPhone, $newUserId) === false);

// Test 9: Update user data (change role to operator and update name)
$updateSuccess = $userModel->updateUser($newUserId, [
    'name' => 'Testing User Mgmt Updated',
    'email' => $testEmail,
    'phone' => $testPhone,
    'role_id' => 3 // Operator
]);
$updatedUser = $userModel->getUserById($newUserId);
assertTest("Model updateUser() updates name and promotes role to operator", 
    $updateSuccess && $updatedUser['name'] === 'Testing User Mgmt Updated' && (int)$updatedUser['role_id'] === 3);

// Test 10: Update password
$updatePwSuccess = $userModel->updateUser($newUserId, [
    'name' => 'Testing User Mgmt Updated',
    'email' => $testEmail,
    'phone' => $testPhone,
    'role_id' => 3,
    'password' => 'newpassword456'
]);
$updatedPwUser = $userModel->getUserById($newUserId);
assertTest("Model updateUser() updates password with bcrypt hash", 
    $updatePwSuccess && password_verify('newpassword456', $updatedPwUser['password']));

// Test 11: Safety check - countOrdersByUser
$orderCount = $userModel->countOrdersByUser(12); // User 12 (Fadillah) has orders
assertTest("countOrdersByUser() identifies user with existing orders", $orderCount > 0);

// Test 12: Safety check - countAdmins
$adminCount = $userModel->countAdmins();
assertTest("countAdmins() returns accurate count of active administrators (>= 1)", $adminCount >= 1);

// Test 13: Delete test user (no orders)
$deleteSuccess = $userModel->deleteUser($newUserId);
$deletedUser = $userModel->getUserById($newUserId);
assertTest("Model deleteUser() successfully removes user without order history", $deleteSuccess && empty($deletedUser));

echo "\n============================================================\n";
echo "Test Summary: Total=" . ($passed + $failed) . ", Passed=$passed, Failed=$failed\n";
echo "============================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
