<?php
/**
 * Simple Admin Login Test
 * Test login with the admin we just created
 */

require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';
require_once 'includes/functions.php';

echo "=== Testing Admin Login ===\n\n";

// Test credentials from the admin we just created
$test_username = "newadmin";
$test_email = "newadmin@example.com";
$test_password = "NewAdminPass123";

echo "Testing with credentials:\n";
echo "Username: $test_username\n";
echo "Email: $test_email\n";
echo "Password: $test_password\n\n";

try {
    $db = new Database();
    
    // Test 1: Find admin by username
    echo "1. Testing admin lookup by username:\n";
    $admin_by_username = $db->fetchOne("SELECT * FROM admins WHERE username = ?", [$test_username]);
    if ($admin_by_username) {
        echo "   ✅ Admin found by username\n";
        echo "   - ID: {$admin_by_username['id']}\n";
        echo "   - Username: {$admin_by_username['username']}\n";
        echo "   - Email: {$admin_by_username['email']}\n";
        echo "   - Password Hash: " . substr($admin_by_username['password'], 0, 20) . "...\n";
    } else {
        echo "   ❌ Admin not found by username\n";
    }
    
    // Test 2: Find admin by email
    echo "\n2. Testing admin lookup by email:\n";
    $admin_by_email = $db->fetchOne("SELECT * FROM admins WHERE email = ?", [$test_email]);
    if ($admin_by_email) {
        echo "   ✅ Admin found by email\n";
        echo "   - ID: {$admin_by_email['id']}\n";
        echo "   - Username: {$admin_by_email['username']}\n";
        echo "   - Email: {$admin_by_email['email']}\n";
        echo "   - Password Hash: " . substr($admin_by_email['password'], 0, 20) . "...\n";
    } else {
        echo "   ❌ Admin not found by email\n";
    }
    
    // Test 3: Test password verification
    echo "\n3. Testing password verification:\n";
    if ($admin_by_username) {
        $password_verify_result = verifyPassword($test_password, $admin_by_username['password']);
        echo "   Password verification result: " . ($password_verify_result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
        
        if ($password_verify_result) {
            echo "   ✅ Login should work!\n";
        } else {
            echo "   ❌ Login will fail due to password mismatch\n";
        }
    } else {
        echo "   ❌ Cannot test password - admin not found\n";
    }
    
    // Test 4: Simulate the exact login process from login.php
    echo "\n4. Simulating login process:\n";
    $username = $test_username;
    $password = $test_password;
    
    // This is the exact logic from login.php
    $admin = $db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $username]);
    
    if ($admin && verifyPassword($password, $admin['password'])) {
        echo "   ✅ Login simulation SUCCESSFUL!\n";
        echo "   Would set session variables:\n";
        echo "   - admin_id: {$admin['id']}\n";
        echo "   - admin_username: {$admin['username']}\n";
        echo "   - admin_email: {$admin['email']}\n";
        echo "   - admin_full_name: {$admin['full_name']}\n";
    } else {
        echo "   ❌ Login simulation FAILED!\n";
        if (!$admin) {
            echo "   Reason: Admin not found in database\n";
        } else {
            echo "   Reason: Password verification failed\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 