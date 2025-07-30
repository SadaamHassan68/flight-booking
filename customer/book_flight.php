<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require customer login
requireLogin();
if (isAdmin()) {
    header('Location: ../admin/');
    exit();
}

$error = '';
$success = '';

// Get flight details
$flight_id = isset($_GET['flight_id']) ? (int)$_GET['flight_id'] : 0;
if (!$flight_id) {
    header('Location: ../flights.php');
    exit();
}

$flight = getFlightById($flight_id);
if (!$flight || $flight['status'] !== 'active' || $flight['available_seats'] <= 0) {
    header('Location: ../flights.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = sanitizeInput($_POST['passenger_name']);
    $passenger_email = sanitizeInput($_POST['passenger_email']);
    $passenger_phone = sanitizeInput($_POST['passenger_phone']);
    
    // Validation
    if (empty($passenger_name) || empty($passenger_email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($passenger_email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!empty($passenger_phone) && !validatePhone($passenger_phone)) {
        $error = 'Please enter a valid phone number.';
    } else {
        $db = new Database();
        
        // Check if flight still has available seats
        $current_flight = $db->fetchOne("SELECT available_seats FROM flights WHERE id = :id AND status = 'active'", ['id' => $flight_id]);
        
        if (!$current_flight || $current_flight['available_seats'] <= 0) {
            $error = 'Sorry, this flight is no longer available.';
        } else {
            // Create booking with correct schema
            $passenger_details = json_encode([
                'name' => $passenger_name,
                'email' => $passenger_email,
                'phone' => $passenger_phone
            ]);
            
            $booking_data = [
                'user_id' => $_SESSION['user_id'],
                'flight_id' => $flight_id,
                'passengers' => 1, // Default to 1 passenger
                'total_amount' => $flight['price'],
                'status' => 'pending',
                'passenger_details' => $passenger_details
            ];
            
            try {
                $db->getConnection()->beginTransaction();
                
                // Insert booking
                $booking_id = $db->insert('bookings', $booking_data);
                
                if ($booking_id) {
                    // Update available seats
                    $db->update('flights', 
                        ['available_seats' => $current_flight['available_seats'] - 1], 
                        'id = :id', 
                        ['id' => $flight_id]
                    );
                    
                    $db->getConnection()->commit();
                    
                    // Send email notification (optional)
                    $email_subject = "Flight Booking Confirmation - " . $flight['flight_number'];
                    $email_message = "
                        <h2>Flight Booking Confirmation</h2>
                        <p>Dear {$passenger_name},</p>
                        <p>Your flight booking has been successfully created.</p>
                        <h3>Booking Details:</h3>
                        <ul>
                            <li><strong>Booking ID:</strong> #{$booking_id}</li>
                            <li><strong>Flight Number:</strong> {$flight['flight_number']}</li>
                            <li><strong>From:</strong> {$flight['from_location']}</li>
                            <li><strong>To:</strong> {$flight['to_location']}</li>
                            <li><strong>Date:</strong> " . formatDate($flight['departure_date']) . "</li>
                            <li><strong>Time:</strong> " . formatTime($flight['departure_time']) . "</li>
                            <li><strong>Amount:</strong> " . formatPrice($flight['price']) . "</li>
                        </ul>
                        <p>Your booking is currently pending approval. You will receive another email once it's confirmed.</p>
                        <p>Thank you for choosing our service!</p>
                    ";
                    
                    // Uncomment the line below to enable email notifications
                    // sendEmail($passenger_email, $email_subject, $email_message);
                    
                    $success = 'Flight booked successfully! Your booking ID is #' . $booking_id;
                } else {
                    $db->getConnection()->rollBack();
                    $error = 'Booking failed. Please try again.';
                }
            } catch (Exception $e) {
                $db->getConnection()->rollBack();
                $error = 'Booking failed. Please try again.';
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
    <title><?php echo SITE_NAME; ?> - Book Flight</title>
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
                        <a class="nav-link active" href="bookings.php">My Bookings</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
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
                <h1><i class="fas fa-ticket-alt"></i> Book Flight</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Complete your flight booking below.</p>
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
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="booking-form">
                    <h2><i class="fas fa-ticket-alt"></i> Book Flight</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="bookings.php" class="btn btn-primary">
                                        <i class="fas fa-list"></i> View My Bookings
                                    </a>
                                    <a href="../flights.php" class="btn btn-outline-primary">
                                        <i class="fas fa-plane"></i> Book Another Flight
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Flight Summary -->
                            <div class="flight-summary">
                                <h3><i class="fas fa-plane"></i> Flight Summary</h3>
                                <div class="flight-details">
                                    <div class="flight-detail">
                                        <small>Flight Number</small>
                                        <strong><?php echo htmlspecialchars($flight['flight_number']); ?></strong>
                                    </div>
                                            <p><strong>From:</strong> <?php echo htmlspecialchars($flight['from_location']); ?></p>
                                            <p><strong>To:</strong> <?php echo htmlspecialchars($flight['to_location']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date:</strong> <?php echo formatDate($flight['departure_date']); ?></p>
                                            <p><strong>Departure:</strong> <?php echo formatTime($flight['departure_time']); ?></p>
                                            <p><strong>Price:</strong> <?php echo formatPrice($flight['price']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Booking Form -->
                            <form method="POST" action="">
                                <h5 class="mb-3">Passenger Information</h5>
                                
                                <div class="mb-3">
                                    <label for="passenger_name" class="form-label">
                                        <i class="fas fa-user"></i> Passenger Name *
                                    </label>
                                    <input type="text" class="form-control" id="passenger_name" name="passenger_name" 
                                           value="<?php echo isset($_POST['passenger_name']) ? htmlspecialchars($_POST['passenger_name']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="passenger_email" class="form-label">
                                        <i class="fas fa-envelope"></i> Passenger Email *
                                    </label>
                                    <input type="email" class="form-control" id="passenger_email" name="passenger_email" 
                                           value="<?php echo isset($_POST['passenger_email']) ? htmlspecialchars($_POST['passenger_email']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="passenger_phone" class="form-label">
                                        <i class="fas fa-phone"></i> Passenger Phone
                                    </label>
                                    <input type="tel" class="form-control" id="passenger_phone" name="passenger_phone" 
                                           value="<?php echo isset($_POST['passenger_phone']) ? htmlspecialchars($_POST['passenger_phone']) : ''; ?>">
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-credit-card"></i> Confirm Booking
                                    </button>
                                    <a href="../flights.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Flights
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
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