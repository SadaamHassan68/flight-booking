<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once 'includes/functions.php';

// Require admin login
requireAdminLogin();

// Get statistics
$booking_stats = getBookingStats();
$flight_stats = getFlightStats();
$recent_bookings = getRecentBookings(5);
$pending_bookings = getPendingBookings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-plane fa-2x text-white mb-2"></i>
                        <h5 class="text-white"><?php echo SITE_NAME; ?></h5>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="flights.php">
                                <i class="fas fa-plane me-2"></i>
                                Manage Flights
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_flight.php">
                                <i class="fas fa-plus-circle me-2"></i>
                                Add Flight
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bookings.php">
                                <i class="fas fa-ticket-alt me-2"></i>
                                Manage Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customers.php">
                                <i class="fas fa-users me-2"></i>
                                View Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user-cog me-2"></i>
                                Profile Settings
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt text-primary"></i> Admin Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-external-link-alt"></i> View Site
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Welcome Message -->
                <div class="alert alert-info">
                    <i class="fas fa-user-shield"></i> 
                    Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_full_name']); ?></strong>!
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="mb-0"><?php echo $booking_stats['total'] ?? 0; ?></h4>
                                        <p class="mb-0">Total Bookings</p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-ticket-alt stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="mb-0"><?php echo $flight_stats['total'] ?? 0; ?></h4>
                                        <p class="mb-0">Total Flights</p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-plane stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="mb-0"><?php echo $booking_stats['pending'] ?? 0; ?></h4>
                                        <p class="mb-0">Pending Bookings</p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="mb-0"><?php echo formatPrice($booking_stats['total_revenue'] ?? 0); ?></h4>
                                        <p class="mb-0">Total Revenue</p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign stat-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt text-warning"></i> Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="add_flight.php" class="btn btn-primary w-100">
                                            <i class="fas fa-plus-circle me-2"></i> Add New Flight
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="bookings.php?status=pending" class="btn btn-warning w-100">
                                            <i class="fas fa-clock me-2"></i> Pending Bookings
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="flights.php" class="btn btn-success w-100">
                                            <i class="fas fa-plane me-2"></i> Manage Flights
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="customers.php" class="btn btn-info w-100">
                                            <i class="fas fa-users me-2"></i> View Customers
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history text-primary"></i> Recent Bookings
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_bookings): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Booking ID</th>
                                                    <th>Customer</th>
                                                    <th>Flight</th>
                                                    <th>Status</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_bookings as $booking): ?>
                                                    <tr>
                                                        <td>#<?php echo $booking['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                                        <td><?php echo $booking['flight_number']; ?></td>
                                                        <td><?php echo getStatusBadge($booking['status']); ?></td>
                                                        <td><?php echo formatPrice($booking['total_amount']); ?></td>
                                                        <td><?php echo formatDate($booking['created_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <a href="bookings.php" class="btn btn-outline-primary">
                                            View All Bookings
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No recent bookings found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-triangle text-warning"></i> Pending Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($pending_bookings): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($pending_bookings, 0, 5) as $booking): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['user_name']); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo $booking['flight_number']; ?> - 
                                                            <?php echo $booking['passengers']; ?> passenger(s)
                                                        </small>
                                                    </div>
                                                    <a href="bookings.php?action=view&id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        Review
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($pending_bookings) > 5): ?>
                                        <div class="text-center mt-3">
                                            <a href="bookings.php?status=pending" class="btn btn-sm btn-warning">
                                                View All Pending (<?php echo count($pending_bookings); ?>)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No pending bookings.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 