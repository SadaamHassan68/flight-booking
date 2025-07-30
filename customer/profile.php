<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require customer login
requireLogin();
if (isAdmin()) {
    header('Location: ../admin/');
    exit();
}

$db = new Database();
$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!empty($phone) && !validatePhone($phone)) {
        $error = 'Please enter a valid phone number.';
    } else {
        // Check if email already exists (excluding current user)
        $existing_email = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
        if ($existing_email) {
            $error = 'Email already exists. Please use a different email address.';
        } else {
            $update_data = [
                'email' => $email,
                'phone' => $phone,
                'address' => $address
            ];
            
            // Handle password change
            if (!empty($current_password)) {
                if (!verifyPassword($current_password, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } elseif (empty($new_password)) {
                    $error = 'New password is required.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } else {
                    $update_data['password'] = hashPassword($new_password);
                }
            }
            
            if (empty($error)) {
                try {
                    $db->update('users', $update_data, 'id = :id', ['id' => $_SESSION['user_id']]);
                    $success = 'Profile updated successfully!';
                    $user = getUserById($_SESSION['user_id']); // Refresh user data
                } catch (Exception $e) {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/customer.css" rel="stylesheet">
</head>
<body class="customer-dashboard">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-plane"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../flights.php">Flights</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">My Bookings</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Customer Header -->
        <div class="customer-header">
            <div class="customer-welcome">
                <h1><i class="fas fa-user-edit"></i> My Profile</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Update your profile information here.</p>
            </div>
        </div>

        <!-- Customer Navigation -->
        <div class="customer-nav">
            <div class="row">
                <div class="col-md-3">
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-home me-2"></i> Home
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="../flights.php" class="nav-link">
                        <i class="fas fa-plane me-2"></i> Browse Flights
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="bookings.php" class="nav-link">
                        <i class="fas fa-ticket-alt me-2"></i> My Bookings
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-form">
                    <h2><i class="fas fa-user-edit"></i> My Profile</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user"></i> Name
                                        </label>
                                        <input type="text" class="form-control" id="name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                        <div class="form-text">Name cannot be changed</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope"></i> Email *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone"></i> Phone Number
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">
                                            <i class="fas fa-map-marker-alt"></i> Address
                                        </label>
                                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-3"><i class="fas fa-lock"></i> Change Password</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                        <div class="form-text">Minimum 6 characters</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Leave password fields empty if you don't want to change your password.
                                </small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn-customer">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="../index.php" class="btn-outline-customer">
                                    <i class="fas fa-arrow-left"></i> Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="profile-form mt-4">
                    <h2><i class="fas fa-info-circle"></i> Account Information</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <small>Member Since</small>
                                <strong><?php echo formatDate($user['created_at']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <small>Last Updated</small>
                                <strong><?php echo formatDate($user['updated_at']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <small>Account Type</small>
                                <strong><span class="status-badge status-confirmed">Customer</span></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <small>Account Status</small>
                                <strong><span class="status-badge status-confirmed">Active</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="mb-0">Your trusted partner for flight bookings.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 