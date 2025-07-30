<?php
/**
 * Web-based Admin Login Test
 * Simulate the login form submission
 */

// Start session
session_start();

// Include necessary files
require_once 'admin/includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'admin/includes/functions.php';

echo "<h2>Admin Login Test</h2>";

// Test credentials
$test_username = "newadmin";
$test_password = "NewAdminPass123";

echo "<p>Testing with username: $test_username</p>";

try {
    $db = new Database();
    
    // Simulate the login process
    $username = sanitizeInput($test_username);
    $password = $test_password;
    
    if (empty($username) || empty($password)) {
        echo "<p style='color: red;'>Error: Empty username or password</p>";
    } else {
        $admin = $db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $username]);
        
        if ($admin) {
            echo "<p>✅ Admin found in database</p>";
            echo "<p>Admin ID: {$admin['id']}</p>";
            echo "<p>Username: {$admin['username']}</p>";
            echo "<p>Email: {$admin['email']}</p>";
            
            if (verifyPassword($password, $admin['password'])) {
                echo "<p style='color: green;'>✅ Password verification successful!</p>";
                
                // Set session variables (like in login.php)
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_full_name'] = $admin['full_name'];
                
                echo "<p style='color: green;'>✅ Session variables set successfully!</p>";
                echo "<p>Session variables:</p>";
                echo "<ul>";
                foreach ($_SESSION as $key => $value) {
                    echo "<li>$key: $value</li>";
                }
                echo "</ul>";
                
                // Test isAdminLoggedIn function
                if (isAdminLoggedIn()) {
                    echo "<p style='color: green;'>✅ isAdminLoggedIn() returns true</p>";
                } else {
                    echo "<p style='color: red;'>❌ isAdminLoggedIn() returns false</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Password verification failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Admin not found in database</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='admin/login.php'>Go to Admin Login Page</a></p>";
echo "<p><a href='admin/index.php'>Go to Admin Dashboard</a></p>";
?> 