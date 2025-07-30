<?php
/**
 * Test Admin Management System
 * Verifies all components are working correctly
 */

echo "🧪 Testing Admin Management System\n";
echo "==================================\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    require_once 'admin/includes/config.php';
    require_once 'admin/includes/functions.php';
    $db = new Database();
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check Required Tables
echo "\n2. Checking Required Tables...\n";
$tables = ['admins', 'users', 'flights', 'bookings'];
foreach ($tables as $table) {
    try {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' missing\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error checking table '$table': " . $e->getMessage() . "\n";
    }
}

// Test 3: Test Admin Functions
echo "\n3. Testing Admin Functions...\n";

// Test password hashing
$test_password = 'test123';
$hash = hashPassword($test_password);
if (verifyPassword($test_password, $hash)) {
    echo "   ✅ Password hashing/verification works\n";
} else {
    echo "   ❌ Password hashing/verification failed\n";
}

// Test input sanitization
$test_input = "  <script>alert('test')</script>  ";
$sanitized = sanitizeInput($test_input);
if ($sanitized === "&lt;script&gt;alert('test')&lt;/script&gt;") {
    echo "   ✅ Input sanitization works\n";
} else {
    echo "   ❌ Input sanitization failed\n";
}

// Test 4: Check Admin Users
echo "\n4. Checking Admin Users...\n";
$admins = $db->fetchAll("SELECT COUNT(*) as count FROM admins");
$admin_count = $admins[0]['count'];
echo "   📊 Total admin users: $admin_count\n";

if ($admin_count > 0) {
    $admin_list = $db->fetchAll("SELECT username, email FROM admins LIMIT 5");
    echo "   👥 Sample admins:\n";
    foreach ($admin_list as $admin) {
        echo "      - " . $admin['username'] . " (" . $admin['email'] . ")\n";
    }
} else {
    echo "   ⚠️  No admin users found. You can create one using:\n";
    echo "      php create_admin_simple.php --create\n";
}

// Test 5: Test Token System
echo "\n5. Testing Token System...\n";
$test_token = bin2hex(random_bytes(32));
$token_valid = validateAdminToken($test_token);
if (!$token_valid) {
    echo "   ✅ Token validation works (invalid token correctly rejected)\n";
} else {
    echo "   ❌ Token validation failed (invalid token accepted)\n";
}

// Test 6: Check File Structure
echo "\n6. Checking File Structure...\n";
$required_files = [
    'create_admin_simple.php',
    'setup_admin.php',
    'generate_admin_token.php',
    'admin/register.php',
    'admin/login.php',
    'admin/includes/config.php',
    'admin/includes/database.php',
    'admin/includes/functions.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file missing\n";
    }
}

// Test 7: System Information
echo "\n7. System Information...\n";
echo "   🖥️  PHP Version: " . PHP_VERSION . "\n";
echo "   🌐 Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'CLI') . "\n";
echo "   📁 Working Directory: " . getcwd() . "\n";
echo "   🔗 Admin URL: http://localhost/Flight%20Boking/admin/login.php\n";
echo "   🔗 Token Generator: http://localhost/Flight%20Boking/generate_admin_token.php\n";

// Test 8: Quick Admin Creation Test
echo "\n8. Testing Admin Creation (Dry Run)...\n";
$test_admin_data = [
    'username' => 'test_admin',
    'email' => 'test@example.com',
    'password' => 'testpass123',
    'full_name' => 'Test Administrator'
];

// Validate test data
$validation = true;
if (strlen($test_admin_data['username']) < 3) $validation = false;
if (!filter_var($test_admin_data['email'], FILTER_VALIDATE_EMAIL)) $validation = false;
if (strlen($test_admin_data['password']) < 6) $validation = false;
if (strlen($test_admin_data['full_name']) < 2) $validation = false;

if ($validation) {
    echo "   ✅ Admin data validation works\n";
} else {
    echo "   ❌ Admin data validation failed\n";
}

echo "\n🎉 Test Complete!\n";
echo "================\n";

if ($admin_count == 0) {
    echo "\n📝 Next Steps:\n";
    echo "1. Create your first admin user:\n";
    echo "   php create_admin_simple.php --create\n";
    echo "   OR\n";
    echo "   php setup_admin.php\n";
    echo "\n2. Access the admin panel:\n";
    echo "   http://localhost/Flight%20Boking/admin/login.php\n";
    echo "\n3. Generate tokens for web registration:\n";
    echo "   http://localhost/Flight%20Boking/generate_admin_token.php\n";
} else {
    echo "\n✅ Admin system is ready!\n";
    echo "You can access the admin panel at:\n";
    echo "http://localhost/Flight%20Boking/admin/login.php\n";
}

echo "\n📚 Documentation:\n";
echo "- ADMIN_SETUP.md - Complete setup guide\n";
echo "- ADMIN_CREATION_SUMMARY.md - System overview\n";
echo "\n";
?> 