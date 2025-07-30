<?php
/**
 * Admin Creation System for Flight Booking System
 * Command-line admin user management tool
 */

// Include database configuration
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

class AdminManager {
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
     * Create admin user interactively
     */
    public function createAdminInteractive() {
        echo "\n=== 🛠️ Interactive Admin Creation ===\n\n";
        
        // Get admin details
        $username = $this->getInput("Enter username: ");
        $email = $this->getInput("Enter email: ");
        $full_name = $this->getInput("Enter full name: ");
        $password = $this->getInput("Enter password: ", true);
        
        // Validate inputs
        $validation = $this->validateAdminData($username, $email, $password, $full_name);
        if (!$validation['valid']) {
            echo "❌ Validation failed: " . $validation['message'] . "\n";
            return false;
        }
        
        // Check for duplicates
        if ($this->adminExists($username, $email)) {
            echo "❌ Admin with this username or email already exists\n";
            return false;
        }
        
        // Create admin
        return $this->createAdmin($username, $email, $password, $full_name);
    }
    
    /**
     * Create admin user with command line parameters
     */
    public function createAdminWithParams($username, $email, $password, $full_name) {
        echo "\n=== 🛠️ Command Line Admin Creation ===\n\n";
        
        // Validate inputs
        $validation = $this->validateAdminData($username, $email, $password, $full_name);
        if (!$validation['valid']) {
            echo "❌ Validation failed: " . $validation['message'] . "\n";
            return false;
        }
        
        // Check for duplicates
        if ($this->adminExists($username, $email)) {
            echo "❌ Admin with this username or email already exists\n";
            return false;
        }
        
        // Create admin
        return $this->createAdmin($username, $email, $password, $full_name);
    }
    
    /**
     * List all admin users
     */
    public function listAdmins() {
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
     * List all users (customers and admins)
     */
    public function listAllUsers() {
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
     * Promote existing user to admin
     */
    public function promoteUserToAdmin($user_id) {
        echo "\n=== 🔄 Promote User to Admin ===\n\n";
        
        // Check if user exists
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
        if (!$user) {
            echo "❌ User with ID $user_id not found\n";
            return false;
        }
        
        echo "User found:\n";
        echo "- Name: " . $user['name'] . "\n";
        echo "- Email: " . $user['email'] . "\n";
        
        // Check if already admin
        $existing_admin = $this->db->fetchOne("SELECT * FROM admins WHERE email = ?", [$user['email']]);
        if ($existing_admin) {
            echo "❌ User is already an admin\n";
            return false;
        }
        
        // Create admin from user
        $admin_data = [
            'username' => $user['name'],
            'email' => $user['email'],
            'password' => $user['password'], // Use existing password
            'full_name' => $user['name']
        ];
        
        $result = $this->db->insert('admins', $admin_data);
        
        if ($result) {
            echo "✅ User successfully promoted to admin!\n";
            echo "Login credentials:\n";
            echo "- Username: " . $user['name'] . "\n";
            echo "- Email: " . $user['email'] . "\n";
            echo "- Password: (same as user account)\n";
            return true;
        } else {
            echo "❌ Failed to promote user to admin\n";
            return false;
        }
    }
    
    /**
     * Generate secure token for web-based admin registration
     */
    public function generateToken() {
        echo "\n=== 🔐 Generate Admin Registration Token ===\n\n";
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Store token in database (you might want to create a tokens table)
        echo "Generated secure token: " . $token . "\n";
        echo "Token expires: " . $expiry . "\n";
        echo "\nUse this token for web-based admin registration:\n";
        echo "http://localhost/Flight%20Boking/admin/register.php?token=" . $token . "\n";
        echo "\n⚠️  Store this token securely and delete after use!\n";
        
        return $token;
    }
    
    /**
     * Validate admin data
     */
    private function validateAdminData($username, $email, $password, $full_name) {
        // Username validation
        if (empty($username) || strlen($username) < 3) {
            return ['valid' => false, 'message' => 'Username must be at least 3 characters'];
        }
        
        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }
        
        // Password validation
        if (strlen($password) < 6) {
            return ['valid' => false, 'message' => 'Password must be at least 6 characters'];
        }
        
        // Full name validation
        if (empty($full_name) || strlen($full_name) < 2) {
            return ['valid' => false, 'message' => 'Full name must be at least 2 characters'];
        }
        
        return ['valid' => true, 'message' => 'Validation passed'];
    }
    
    /**
     * Check if admin already exists
     */
    private function adminExists($username, $email) {
        $admin = $this->db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $email]);
        return $admin !== false;
    }
    
    /**
     * Create admin user
     */
    private function createAdmin($username, $email, $password, $full_name) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $admin_data = [
            'username' => $username,
            'email' => $email,
            'password' => $password_hash,
            'full_name' => $full_name
        ];
        
        $result = $this->db->insert('admins', $admin_data);
        
        if ($result) {
            echo "✅ Admin user created successfully!\n";
            echo "Login credentials:\n";
            echo "- Username: $username\n";
            echo "- Email: $email\n";
            echo "- Password: $password\n";
            echo "\nAdmin login URL: http://localhost/Flight%20Boking/admin/login.php\n";
            return true;
        } else {
            echo "❌ Failed to create admin user\n";
            return false;
        }
    }
    
    /**
     * Get user input
     */
    private function getInput($prompt, $hidden = false) {
        echo $prompt;
        if ($hidden && PHP_OS_FAMILY === 'Windows') {
            // Windows doesn't support stty, so we'll just read normally
            $input = trim(fgets(STDIN));
            echo "\n";
        } elseif ($hidden) {
            system('stty -echo');
            $input = trim(fgets(STDIN));
            system('stty echo');
            echo "\n";
        } else {
            $input = trim(fgets(STDIN));
        }
        return $input;
    }
    
    /**
     * Show help
     */
    public function showHelp() {
        echo "\n=== 🛠️ Admin Management Tool ===\n\n";
        echo "Usage: php create_admin_simple.php [OPTIONS]\n\n";
        echo "Options:\n";
        echo "  --create                    Interactive admin creation\n";
        echo "  --create-admin              Create admin with parameters\n";
        echo "  --username <username>       Admin username\n";
        echo "  --email <email>             Admin email\n";
        echo "  --password <password>       Admin password\n";
        echo "  --full-name <full_name>     Admin full name\n";
        echo "  --list                      List all admin users\n";
        echo "  --all-users                 List all users (customers and admins)\n";
        echo "  --promote <user_id>         Promote existing user to admin\n";
        echo "  --token                     Generate admin registration token\n";
        echo "  --help                      Show this help message\n\n";
        echo "Examples:\n";
        echo "  php create_admin_simple.php --create\n";
        echo "  php create_admin_simple.php --create-admin --username admin --email admin@example.com --password SecurePass123 --full-name \"System Administrator\"\n";
        echo "  php create_admin_simple.php --list\n";
        echo "  php create_admin_simple.php --promote 1\n";
        echo "  php create_admin_simple.php --token\n\n";
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line\n");
}

$manager = new AdminManager();

// Parse command line arguments
$options = getopt('', [
    'create',
    'create-admin',
    'username:',
    'email:',
    'password:',
    'full-name:',
    'list',
    'all-users',
    'promote:',
    'token',
    'help'
]);

if (isset($options['help']) || empty($options)) {
    $manager->showHelp();
    exit();
}

if (isset($options['create'])) {
    $manager->createAdminInteractive();
} elseif (isset($options['create-admin'])) {
    if (!isset($options['username']) || !isset($options['email']) || !isset($options['password']) || !isset($options['full-name'])) {
        echo "❌ Missing required parameters for admin creation\n";
        echo "Use --help for usage information\n";
        exit(1);
    }
    $manager->createAdminWithParams(
        $options['username'],
        $options['email'],
        $options['password'],
        $options['full-name']
    );
} elseif (isset($options['list'])) {
    $manager->listAdmins();
} elseif (isset($options['all-users'])) {
    $manager->listAllUsers();
} elseif (isset($options['promote'])) {
    $manager->promoteUserToAdmin($options['promote']);
} elseif (isset($options['token'])) {
    $manager->generateToken();
} else {
    echo "❌ Invalid option. Use --help for usage information\n";
    exit(1);
}

echo "\n";
?> 