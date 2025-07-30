<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require customer login
requireLogin();
if (isAdmin()) {
    header('Location: ../admin/');
    exit();
}



$bookings = getUserBookings($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - My Bookings</title>
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
                <h1><i class="fas fa-ticket-alt"></i> My Bookings</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Here are all your flight bookings.</p>
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
                    <a href="bookings.php" class="nav-link active">
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

        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-ticket-alt"></i> My Bookings</h2>
                    <a href="../flights.php" class="btn-customer">
                        <i class="fas fa-plus"></i> Book New Flight
                    </a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h4>No Bookings Found</h4>
                        <p>You haven't made any flight bookings yet. Start your journey by browsing available flights!</p>
                        <a href="../flights.php" class="btn-customer">
                            <i class="fas fa-plane"></i> Browse Flights
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="booking-card">
                                    <div class="booking-header">
                                        <div class="booking-number">Booking #<?php echo $booking['id']; ?></div>
                                        <div class="booking-status status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </div>
                                    </div>
                                    <div class="booking-body">
                                        <div class="flight-info">
                                            <div class="flight-route">
                                                <div class="route-point">
                                                    <i class="fas fa-plane-departure"></i>
                                                    <div class="route-info">
                                                        <h6><?php echo htmlspecialchars($booking['from_location']); ?></h6>
                                                        <small>Departure</small>
                                                    </div>
                                                </div>
                                                <div class="route-arrow">
                                                    <i class="fas fa-arrow-right"></i>
                                                </div>
                                                <div class="route-point">
                                                    <i class="fas fa-plane-arrival"></i>
                                                    <div class="route-info">
                                                        <h6><?php echo htmlspecialchars($booking['to_location']); ?></h6>
                                                        <small>Arrival</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-details">
                                            <div class="detail-item">
                                                <small>Flight Number</small>
                                                <strong><?php echo htmlspecialchars($booking['flight_number']); ?></strong>
                                            </div>
                                            <div class="detail-item">
                                                <small>Departure Date</small>
                                                <strong><?php echo formatDate($booking['departure_date']); ?></strong>
                                            </div>
                                            <div class="detail-item">
                                                <small>Departure Time</small>
                                                <strong><?php echo formatTime($booking['departure_time']); ?></strong>
                                            </div>
                                            <div class="detail-item">
                                                <small>Booking Date</small>
                                                <strong><?php echo formatDate($booking['created_at']); ?></strong>
                                            </div>
                                            <?php 
                                            $passenger_details = getPassengerDetails($booking['passenger_details']);
                                            ?>
                                            <div class="detail-item">
                                                <small>Passenger</small>
                                                <strong><?php echo htmlspecialchars($passenger_details['name']); ?></strong>
                                            </div>
                                            <div class="detail-item">
                                                <small>Amount</small>
                                                <strong><?php echo formatPrice($booking['total_amount']); ?></strong>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Flight Date</small>
                                                <div><?php echo formatDate($booking['departure_date']); ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Email</small>
                                                <div><?php echo htmlspecialchars($passenger_details['email']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($passenger_details['phone'])): ?>
                                            <div class="mb-3">
                                                <small class="text-muted">Phone</small>
                                                <div><?php echo htmlspecialchars($passenger_details['phone']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex gap-2">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="printTicket(<?php echo $booking['id']; ?>)">
                                                    <i class="fas fa-print"></i> Print Ticket
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="viewDetails(<?php echo $booking['id']; ?>)">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingModalBody">
                    <!-- Content will be loaded here -->
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
    <script>
        function viewDetails(bookingId) {
            // This would typically load booking details via AJAX
            // For now, we'll just show a simple message
            document.getElementById('bookingModalBody').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-info-circle fa-3x text-primary mb-3"></i>
                    <h5>Booking #${bookingId}</h5>
                    <p class="text-muted">Detailed booking information would be displayed here.</p>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }
        
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                // This would typically send a request to cancel the booking
                alert('Booking cancellation request sent. Please contact customer support for confirmation.');
            }
        }
        
        function printTicket(bookingId) {
            // This would typically open a print-friendly ticket page
            window.open(`ticket.php?id=${bookingId}`, '_blank');
        }
    </script>
</body>
</html> 