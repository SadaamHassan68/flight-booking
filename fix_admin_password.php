<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

echo "<h2>🔧 Fix Admin Password</h2>";

try {
    $db = new Database();
    echo "<p>✅ Database connection: <strong>SUCCESS</strong></p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection: <strong>FAILED</strong> - " . $e->getMessage() . "</p>";
    exit();
}

// Admin details
$username = 'admin1';
$email = 'admin1@gmail.com';
$password = '683871sa'; // Your plain text password
$full_name = 'admin';

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$admin = $db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $email]);

if ($admin) {
    echo "<p>✅ Admin user found:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
    echo "<li><strong>Username:</strong> " . $admin['username'] . "</li>";
    echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
    echo "<li><strong>Current Password:</strong> " . substr($admin['password'], 0, 20) . "...</li>";
    echo "</ul>";
    
    // Update the password
    $result = $db->update('admins', 
        ['password' => $password_hash], 
        ['id' => $admin['id']]
    );
    
    if ($result) {
        echo "<p>✅ <strong>Password updated successfully!</strong></p>";
    } else {
        echo "<p>❌ <strong>Failed to update password</strong></p>";
    }
} else {
    echo "<p>❌ Admin user not found. Creating new admin...</p>";
    
    // Create new admin
    $admin_data = [
        'username' => $username,
        'email' => $email,
        'password' => $password_hash,
        'full_name' => $full_name
    ];
    
    $result = $db->insert('admins', $admin_data);
    
    if ($result) {
        echo "<p>✅ <strong>New admin user created successfully!</strong></p>";
    } else {
        echo "<p>❌ <strong>Failed to create admin user</strong></p>";
    }
}

echo "<h3>🎯 Login Credentials:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>";
echo "<p><strong>Login URL:</strong> <a href='admin/login.php' target='_blank'>http://localhost/Flight%20Boking/admin/login.php</a></p>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Email:</strong> $email</p>";
echo "<p><strong>Password:</strong> $password</p>";
echo "</div>";

echo "<h3>🔐 Test the Login:</h3>";
echo "<p>1. Go to the admin login page</p>";
echo "<p>2. Enter username: <strong>$username</strong></p>";
echo "<p>3. Enter password: <strong>$password</strong></p>";
echo "<p>4. Click Login</p>";

echo "<h3>📋 SQL Statement Used:</h3>";
echo "<pre>";
echo "UPDATE admins SET password = '$password_hash' WHERE username = '$username';";
echo "</pre>";

echo "<hr>";
echo "<p><strong>⚠️ Security Note:</strong> Delete this file after use!</p>";
echo "<p><strong>💡 Tip:</strong> The password is now properly hashed and secure.</p>";
?> 