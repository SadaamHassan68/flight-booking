<?php
/**
 * Admin Registration Page
 * Secure admin registration using tokens
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/functions.php';

// Check if admin is already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate token
    $token_valid = validateAdminToken($token);
    if (!$token_valid) {
        $error = 'Invalid or expired registration token.';
    } elseif (empty($username) || strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check for existing admin
        $existing_admin = $db->fetchOne("SELECT * FROM admins WHERE username = ? OR email = ?", [$username, $email]);
        if ($existing_admin) {
            $error = 'Admin with this username or email already exists.';
        } else {
            // Create admin
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $admin_data = [
                'username' => $username,
                'email' => $email,
                'password' => $password_hash,
                'full_name' => $full_name
            ];
            
            $result = $db->insert('admins', $admin_data);
            
            if ($result) {
                // Mark token as used
                markAdminTokenUsed($token);
                $success = 'Admin account created successfully! You can now login.';
            } else {
                $error = 'Failed to create admin account. Please try again.';
            }
        }
    }
}

// Get token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Validate token for display
$token_valid = validateAdminToken($token);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Flight Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Admin Registration
                        </h3>
                        <p class="mb-0 mt-2">Flight Booking System</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Go to Login
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if (!$token_valid): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Invalid Token:</strong> This registration link is invalid or has expired. 
                                    Please contact your system administrator for a new token.
                                </div>
                                <div class="text-center">
                                    <a href="../generate_admin_token.php" class="btn btn-outline-primary">
                                        <i class="fas fa-key me-2"></i>
                                        Generate New Token
                                    </a>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="token" class="form-label">
                                            <i class="fas fa-key me-2"></i>Registration Token
                                        </label>
                                        <input type="text" class="form-control" id="token" name="token" 
                                               value="<?php echo htmlspecialchars($token); ?>" readonly>
                                        <div class="form-text">This token is required for admin registration.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user me-2"></i>Username
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                               required>
                                        <div class="form-text">Minimum 3 characters.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Email Address
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">
                                            <i class="fas fa-id-card me-2"></i>Full Name
                                        </label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Password
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="form-text">Minimum 6 characters.</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Confirm Password
                                        </label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-2"></i>
                                            Create Admin Account
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-center">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Security Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Registration requires a valid token</li>
                            <li><i class="fas fa-check text-success me-2"></i>Tokens expire after 24 hours</li>
                            <li><i class="fas fa-check text-success me-2"></i>Passwords are securely hashed</li>
                            <li><i class="fas fa-check text-success me-2"></i>All inputs are validated and sanitized</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 