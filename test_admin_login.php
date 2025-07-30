<?php
/**
 * Test Admin Login Debug Script
 * This script will help identify why admin login is not working
 */

// Include necessary files
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';
require_once 'includes/functions.php';

echo "=== Admin Login Debug Test ===\n\n";

// Test 1: Check if session is working
echo "1. Testing session functionality:\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "   ✅ Session is active\n";
} else {
    echo "   ❌ Session is not active\n";
}

// Test 2: Check database connection
echo "\n2. Testing database connection:\n";
try {
    $db = new Database();
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit();
}

// Test 3: Check if admins table exists and has data
echo "\n3. Testing admins table:\n";
try {
    $admins = $db->fetchAll("SELECT id, username, email, full_name FROM admins LIMIT 5");
    if (empty($admins)) {
        echo "   ❌ No admin users found in database\n";
    } else {
        echo "   ✅ Found " . count($admins) . " admin users:\n";
        foreach ($admins as $admin) {
            echo "      - ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error querying admins table: " . $e->getMessage() . "\n";
}

// Test 4: Test password verification with a known admin
echo "\n4. Testing password verification:\n";
if (!empty($admins)) {
    $test_admin = $admins[0];
    echo "   Testing with admin: {$test_admin['username']}\n";
    
    // Test with wrong password
    $wrong_password = "wrongpassword123";
    $result = verifyPassword($wrong_password, $test_admin['password']);
    echo "   Wrong password test: " . ($result ? "❌ Should be false" : "✅ Correctly false") . "\n";
    
    // Test with correct password (we'll need to know the password)
    echo "   Note: To test correct password, you need to know the actual password\n";
}

// Test 5: Test the exact login logic from login.php
echo "\n5. Testing login logic:\n";
if (!empty($admins)) {
    $test_admin = $admins[0];
    $username = $test_admin['username'];
    
    echo "   Testing with username: $username\n";
    
    // Simulate the login query
    $admin = $db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $username]);
    
    if ($admin) {
        echo "   ✅ Admin found in database\n";
        echo "   Admin details:\n";
        echo "   - ID: {$admin['id']}\n";
        echo "   - Username: {$admin['username']}\n";
        echo "   - Email: {$admin['email']}\n";
        echo "   - Full Name: {$admin['full_name']}\n";
        echo "   - Password Hash: " . substr($admin['password'], 0, 20) . "...\n";
    } else {
        echo "   ❌ Admin not found in database\n";
    }
}

// Test 6: Check if isAdminLoggedIn function works
echo "\n6. Testing isAdminLoggedIn function:\n";
$logged_in = isAdminLoggedIn();
echo "   Current login status: " . ($logged_in ? "✅ Logged in" : "❌ Not logged in") . "\n";

// Test 7: Test session variables
echo "\n7. Testing session variables:\n";
echo "   Session variables:\n";
foreach ($_SESSION as $key => $value) {
    echo "   - $key: " . (is_array($value) ? "Array" : $value) . "\n";
}

echo "\n=== Debug Test Complete ===\n";
echo "\nTo test actual login, try these credentials:\n";
if (!empty($admins)) {
    foreach ($admins as $admin) {
        echo "- Username: {$admin['username']} or Email: {$admin['email']}\n";
    }
}
echo "\nNote: You'll need to know the actual passwords to test login.\n";
?> 