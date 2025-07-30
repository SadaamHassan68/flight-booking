<?php
/**
 * Admin Registration Token Generator
 * Generates secure tokens for web-based admin registration
 */

// Include database configuration
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

class TokenGenerator {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Generate a secure token
     */
    public function generateToken() {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Store token in database (create tokens table if needed)
        $this->createTokensTable();
        
        $token_data = [
            'token' => $token,
            'expires_at' => $expiry,
            'used' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->insert('admin_tokens', $token_data);
        
        if ($result) {
            return [
                'token' => $token,
                'expires_at' => $expiry,
                'success' => true
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to store token'
            ];
        }
    }
    
    /**
     * Create tokens table if it doesn't exist
     */
    private function createTokensTable() {
        $sql = "CREATE TABLE IF NOT EXISTS admin_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        )";
        
        $this->db->query($sql);
    }
    
    /**
     * Validate token
     */
    public function validateToken($token) {
        $token_data = $this->db->fetchOne(
            "SELECT * FROM admin_tokens WHERE token = ? AND expires_at > NOW() AND used = 0",
            [$token]
        );
        
        return $token_data !== false;
    }
    
    /**
     * Mark token as used
     */
    public function markTokenUsed($token) {
        return $this->db->update('admin_tokens', ['used' => 1], ['token' => $token]);
    }
    
    /**
     * Clean expired tokens
     */
    public function cleanExpiredTokens() {
        return $this->db->query("DELETE FROM admin_tokens WHERE expires_at < NOW()");
    }
}

// Handle web requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generator = new TokenGenerator();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'generate':
                $result = $generator->generateToken();
                if ($result['success']) {
                    $response = [
                        'success' => true,
                        'token' => $result['token'],
                        'expires_at' => $result['expires_at'],
                        'registration_url' => 'http://localhost/Flight%20Boking/admin/register.php?token=' . $result['token']
                    ];
                } else {
                    $response = ['success' => false, 'error' => $result['error']];
                }
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
                
            case 'clean':
                $generator->cleanExpiredTokens();
                $response = ['success' => true, 'message' => 'Expired tokens cleaned'];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
        }
    }
}

// Show the token generator interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Token Generator - Flight Booking System</title>
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
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #495057);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }
        .token-display {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            word-break: break-all;
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
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Admin Token Generator
                        </h3>
                        <p class="mb-0 mt-2">Flight Booking System</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Security Notice:</strong> This tool generates secure tokens for admin registration. 
                            Store tokens securely and delete them after use.
                        </div>
                        
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-primary" onclick="generateToken()">
                                <i class="fas fa-plus me-2"></i>
                                Generate New Token
                            </button>
                            
                            <button type="button" class="btn btn-secondary" onclick="cleanTokens()">
                                <i class="fas fa-broom me-2"></i>
                                Clean Expired Tokens
                            </button>
                        </div>
                        
                        <div id="tokenResult" class="mt-4" style="display: none;">
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>Token Generated Successfully!</h5>
                                <div class="token-display">
                                    <strong>Token:</strong><br>
                                    <span id="generatedToken" class="text-primary"></span>
                                </div>
                                <div class="token-display">
                                    <strong>Registration URL:</strong><br>
                                    <a id="registrationUrl" href="#" target="_blank" class="text-decoration-none"></a>
                                </div>
                                <div class="mt-3">
                                    <strong>Expires:</strong> <span id="expiresAt"></span><br>
                                    <strong>Instructions:</strong>
                                    <ol class="mt-2">
                                        <li>Share this token securely with the new admin</li>
                                        <li>They can use the registration URL to create their account</li>
                                        <li>Token expires in 24 hours</li>
                                        <li>Delete this token after use</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <div id="errorResult" class="mt-4" style="display: none;">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="errorMessage"></span>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="admin/login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Go to Admin Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Security Best Practices
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Generate tokens only when needed</li>
                            <li><i class="fas fa-check text-success me-2"></i>Share tokens through secure channels</li>
                            <li><i class="fas fa-check text-success me-2"></i>Delete tokens immediately after use</li>
                            <li><i class="fas fa-check text-success me-2"></i>Regularly clean expired tokens</li>
                            <li><i class="fas fa-check text-success me-2"></i>Monitor admin account creation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateToken() {
            fetch('generate_admin_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=generate'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('generatedToken').textContent = data.token;
                    document.getElementById('registrationUrl').href = data.registration_url;
                    document.getElementById('registrationUrl').textContent = data.registration_url;
                    document.getElementById('expiresAt').textContent = data.expires_at;
                    document.getElementById('tokenResult').style.display = 'block';
                    document.getElementById('errorResult').style.display = 'none';
                } else {
                    document.getElementById('errorMessage').textContent = data.error;
                    document.getElementById('errorResult').style.display = 'block';
                    document.getElementById('tokenResult').style.display = 'none';
                }
            })
            .catch(error => {
                document.getElementById('errorMessage').textContent = 'An error occurred while generating the token.';
                document.getElementById('errorResult').style.display = 'block';
                document.getElementById('tokenResult').style.display = 'none';
            });
        }
        
        function cleanTokens() {
            fetch('generate_admin_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clean'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Expired tokens cleaned successfully!');
                } else {
                    alert('Error cleaning tokens: ' + data.error);
                }
            })
            .catch(error => {
                alert('An error occurred while cleaning tokens.');
            });
        }
    </script>
</body>
</html> 