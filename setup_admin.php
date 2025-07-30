<?php
/**
 * Interactive Admin Setup for Flight Booking System
 * Menu-driven admin user management interface
 */

// Include database configuration
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

class AdminSetup {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new Database();
            echo "✅ Database connection established\n";
        } catch (Exception $e) {
            die("❌ Database connection failed: " . $e->getMessage() . "\n");
        }
    }
    
    /**
     * Show main menu
     */
    public function showMenu() {
        while (true) {
            echo "\n";
            echo "╔══════════════════════════════════════════════════════════════╗\n";
            echo "║                    🛠️  ADMIN SETUP MENU                      ║\n";
            echo "╠══════════════════════════════════════════════════════════════╣\n";
            echo "║  1. Create New Admin User                                    ║\n";
            echo "║  2. List All Admin Users                                     ║\n";
            echo "║  3. List All Users (Customers)                               ║\n";
            echo "║  4. Promote User to Admin                                    ║\n";
            echo "║  5. Generate Admin Registration Token                        ║\n";
            echo "║  6. Reset Admin Password                                     ║\n";
            echo "║  7. Delete Admin User                                        ║\n";
            echo "║  8. System Information                                       ║\n";
            echo "║  0. Exit                                                     ║\n";
            echo "╚══════════════════════════════════════════════════════════════╝\n";
            
            $choice = $this->getInput("Enter your choice (0-8): ");
            
            switch ($choice) {
                case '1':
                    $this->createAdmin();
                    break;
                case '2':
                    $this->listAdmins();
                    break;
                case '3':
                    $this->listUsers();
                    break;
                case '4':
                    $this->promoteUser();
                    break;
                case '5':
                    $this->generateToken();
                    break;
                case '6':
                    $this->resetPassword();
                    break;
                case '7':
                    $this->deleteAdmin();
                    break;
                case '8':
                    $this->systemInfo();
                    break;
                case '0':
                    echo "\n👋 Goodbye!\n";
                    exit();
                default:
                    echo "\n❌ Invalid choice. Please try again.\n";
            }
            
            $this->getInput("\nPress Enter to continue...");
        }
    }
    
    /**
     * Create new admin user
     */
    private function createAdmin() {
        echo "\n=== 🛠️ Create New Admin User ===\n\n";
        
        $username = $this->getInput("Enter username: ");
        $email = $this->getInput("Enter email: ");
        $full_name = $this->getInput("Enter full name: ");
        $password = $this->getInput("Enter password: ");
        
        // Validate inputs
        if (empty($username) || strlen($username) < 3) {
            echo "❌ Username must be at least 3 characters\n";
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "❌ Invalid email format\n";
            return;
        }
        
        if (strlen($password) < 6) {
            echo "❌ Password must be at least 6 characters\n";
            return;
        }
        
        // Check for duplicates
        $existing = $this->db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $email]);
        if ($existing) {
            echo "❌ Admin with this username or email already exists\n";
            return;
        }
        
        // Create admin
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $admin_data = [
            'username' => $username,
            'email' => $email,
            'password' => $password_hash,
            'full_name' => $full_name
        ];
        
        $result = $this->db->insert('admins', $admin_data);
        
        if ($result) {
            echo "\n✅ Admin user created successfully!\n";
            echo "Login credentials:\n";
            echo "- Username: $username\n";
            echo "- Email: $email\n";
            echo "- Password: $password\n";
            echo "\nAdmin login URL: http://localhost/Flight%20Boking/admin/login.php\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    }
    
    /**
     * List all admin users
     */
    private function listAdmins() {
        echo "\n=== 👥 Admin Users List ===\n\n";
        
        $admins = $this->db->fetchAll("SELECT id, username, email, full_name, created_at FROM admins ORDER BY created_at DESC");
        
        if (empty($admins)) {
            echo "No admin users found.\n";
            return;
        }
        
        printf("%-5s %-15s %-25s %-20s %-20s\n", "ID", "Username", "Email", "Full Name", "Created");
        echo str_repeat("-", 90) . "\n";
        
        foreach ($admins as $admin) {
            printf("%-5s %-15s %-25s %-20s %-20s\n", 
                $admin['id'], 
                $admin['username'], 
                $admin['email'], 
                $admin['full_name'],
                date('Y-m-d H:i', strtotime($admin['created_at']))
            );
        }
        
        echo "\nTotal admins: " . count($admins) . "\n";
    }
    
    /**
     * List all users
     */
    private function listUsers() {
        echo "\n=== 👥 All Users List ===\n\n";
        
        $users = $this->db->fetchAll("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
        
        if (empty($users)) {
            echo "No users found.\n";
            return;
        }
        
        printf("%-5s %-20s %-30s %-20s\n", "ID", "Name", "Email", "Created");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($users as $user) {
            printf("%-5s %-20s %-30s %-20s\n", 
                $user['id'], 
                $user['name'], 
                $user['email'],
                date('Y-m-d H:i', strtotime($user['created_at']))
            );
        }
        
        echo "\nTotal users: " . count($users) . "\n";
    }
    
    /**
     * Promote user to admin
     */
    private function promoteUser() {
        echo "\n=== 🔄 Promote User to Admin ===\n\n";
        
        // Show available users
        $users = $this->db->fetchAll("SELECT id, name, email FROM users ORDER BY name");
        
        if (empty($users)) {
            echo "No users found to promote.\n";
            return;
        }
        
        echo "Available users:\n";
        printf("%-5s %-20s %-30s\n", "ID", "Name", "Email");
        echo str_repeat("-", 60) . "\n";
        
        foreach ($users as $user) {
            printf("%-5s %-20s %-30s\n", $user['id'], $user['name'], $user['email']);
        }
        
        $user_id = $this->getInput("\nEnter user ID to promote: ");
        
        if (!is_numeric($user_id)) {
            echo "❌ Invalid user ID\n";
            return;
        }
        
        // Check if user exists
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
        if (!$user) {
            echo "❌ User with ID $user_id not found\n";
            return;
        }
        
        // Check if already admin
        $existing_admin = $this->db->fetchOne("SELECT * FROM admins WHERE email = ?", [$user['email']]);
        if ($existing_admin) {
            echo "❌ User is already an admin\n";
            return;
        }
        
        // Create admin from user
        $admin_data = [
            'username' => $user['name'],
            'email' => $user['email'],
            'password' => $user['password'],
            'full_name' => $user['name']
        ];
        
        $result = $this->db->insert('admins', $admin_data);
        
        if ($result) {
            echo "✅ User successfully promoted to admin!\n";
            echo "Login credentials:\n";
            echo "- Username: " . $user['name'] . "\n";
            echo "- Email: " . $user['email'] . "\n";
            echo "- Password: (same as user account)\n";
        } else {
            echo "❌ Failed to promote user to admin\n";
        }
    }
    
    /**
     * Generate admin registration token
     */
    private function generateToken() {
        echo "\n=== 🔐 Generate Admin Registration Token ===\n\n";
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        echo "Generated secure token: " . $token . "\n";
        echo "Token expires: " . $expiry . "\n";
        echo "\nUse this token for web-based admin registration:\n";
        echo "http://localhost/Flight%20Boking/admin/register.php?token=" . $token . "\n";
        echo "\n⚠️  Store this token securely and delete after use!\n";
    }
    
    /**
     * Reset admin password
     */
    private function resetPassword() {
        echo "\n=== 🔄 Reset Admin Password ===\n\n";
        
        // Show admin users
        $admins = $this->db->fetchAll("SELECT id, username, email FROM admins ORDER BY username");
        
        if (empty($admins)) {
            echo "No admin users found.\n";
            return;
        }
        
        echo "Admin users:\n";
        printf("%-5s %-15s %-25s\n", "ID", "Username", "Email");
        echo str_repeat("-", 50) . "\n";
        
        foreach ($admins as $admin) {
            printf("%-5s %-15s %-25s\n", $admin['id'], $admin['username'], $admin['email']);
        }
        
        $admin_id = $this->getInput("\nEnter admin ID to reset password: ");
        
        if (!is_numeric($admin_id)) {
            echo "❌ Invalid admin ID\n";
            return;
        }
        
        // Check if admin exists
        $admin = $this->db->fetchOne("SELECT * FROM admins WHERE id = ?", [$admin_id]);
        if (!$admin) {
            echo "❌ Admin with ID $admin_id not found\n";
            return;
        }
        
        $new_password = $this->getInput("Enter new password: ");
        
        if (strlen($new_password) < 6) {
            echo "❌ Password must be at least 6 characters\n";
            return;
        }
        
        // Update password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $result = $this->db->update('admins', ['password' => $password_hash], ['id' => $admin_id]);
        
        if ($result) {
            echo "✅ Password reset successfully!\n";
            echo "New login credentials:\n";
            echo "- Username: " . $admin['username'] . "\n";
            echo "- Email: " . $admin['email'] . "\n";
            echo "- Password: $new_password\n";
        } else {
            echo "❌ Failed to reset password\n";
        }
    }
    
    /**
     * Delete admin user
     */
    private function deleteAdmin() {
        echo "\n=== 🗑️ Delete Admin User ===\n\n";
        
        // Show admin users
        $admins = $this->db->fetchAll("SELECT id, username, email FROM admins ORDER BY username");
        
        if (empty($admins)) {
            echo "No admin users found.\n";
            return;
        }
        
        echo "Admin users:\n";
        printf("%-5s %-15s %-25s\n", "ID", "Username", "Email");
        echo str_repeat("-", 50) . "\n";
        
        foreach ($admins as $admin) {
            printf("%-5s %-15s %-25s\n", $admin['id'], $admin['username'], $admin['email']);
        }
        
        $admin_id = $this->getInput("\nEnter admin ID to delete: ");
        
        if (!is_numeric($admin_id)) {
            echo "❌ Invalid admin ID\n";
            return;
        }
        
        // Check if admin exists
        $admin = $this->db->fetchOne("SELECT * FROM admins WHERE id = ?", [$admin_id]);
        if (!$admin) {
            echo "❌ Admin with ID $admin_id not found\n";
            return;
        }
        
        $confirm = $this->getInput("Are you sure you want to delete admin '" . $admin['username'] . "'? (yes/no): ");
        
        if (strtolower($confirm) !== 'yes') {
            echo "❌ Deletion cancelled\n";
            return;
        }
        
        // Delete admin
        $result = $this->db->delete('admins', ['id' => $admin_id]);
        
        if ($result) {
            echo "✅ Admin user deleted successfully!\n";
        } else {
            echo "❌ Failed to delete admin user\n";
        }
    }
    
    /**
     * Show system information
     */
    private function systemInfo() {
        echo "\n=== ℹ️ System Information ===\n\n";
        
        // Database info
        $admin_count = $this->db->fetchOne("SELECT COUNT(*) as count FROM admins")['count'];
        $user_count = $this->db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
        $flight_count = $this->db->fetchOne("SELECT COUNT(*) as count FROM flights")['count'];
        $booking_count = $this->db->fetchOne("SELECT COUNT(*) as count FROM bookings")['count'];
        
        echo "Database Statistics:\n";
        echo "- Admin users: $admin_count\n";
        echo "- Customer users: $user_count\n";
        echo "- Flights: $flight_count\n";
        echo "- Bookings: $booking_count\n";
        
        echo "\nSystem Information:\n";
        echo "- PHP Version: " . PHP_VERSION . "\n";
        echo "- Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        echo "- Database: MySQL\n";
        echo "- Admin URL: http://localhost/Flight%20Boking/admin/login.php\n";
        echo "- Customer URL: http://localhost/Flight%20Boking/\n";
    }
    
    /**
     * Get user input
     */
    private function getInput($prompt) {
        echo $prompt;
        return trim(fgets(STDIN));
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line\n");
}

echo "🛠️  Flight Booking System - Admin Setup\n";
echo "=====================================\n";

$setup = new AdminSetup();
$setup->showMenu();
?> 