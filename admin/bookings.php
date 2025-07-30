<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/functions.php';

// Require admin login
requireAdminLogin();

$db = new Database();
$error = '';
$success = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    
    try {
        // Get booking details first
        $booking = $db->fetchOne("SELECT * FROM bookings WHERE id = ?", [$booking_id]);
        
        if ($booking && isset($booking['flight_id']) && is_numeric($booking['flight_id'])) {
            // Update available seats on the flight
            $flight_id = (int)$booking['flight_id'];
            $db->query("UPDATE flights SET available_seats = available_seats + 1 WHERE id = ?", [$flight_id]);
            
            // Delete the booking
            $result = $db->delete('bookings', 'id = :id', ['id' => $booking_id]);
            if ($result) {
                $success = 'Booking deleted successfully!';
                // Send email notification
                sendBookingNotification($booking_id, 'deleted');
            } else {
                $error = 'Failed to delete booking.';
            }
        } else {
            $error = 'Booking not found.';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $result = $db->update('bookings', ['status' => 'confirmed'], 'id = :id', ['id' => $booking_id]);
        if ($result) {
            $success = 'Booking approved successfully!';
            // Send email notification
            sendBookingNotification($booking_id, 'confirmed');
        } else {
            $error = 'Failed to approve booking.';
        }
    } elseif ($action === 'cancel') {
        $result = $db->update('bookings', ['status' => 'cancelled'], 'id = :id', ['id' => $booking_id]);
        if ($result) {
            $success = 'Booking cancelled successfully!';
            // Send email notification
            sendBookingNotification($booking_id, 'cancelled');
        } else {
            $error = 'Failed to cancel booking.';
        }
    } elseif ($action === 'complete') {
        $result = $db->update('bookings', ['status' => 'completed'], 'id = :id', ['id' => $booking_id]);
        if ($result) {
            $success = 'Booking marked as completed!';
            // Send email notification
            sendBookingNotification($booking_id, 'completed');
        } else {
            $error = 'Failed to complete booking.';
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$sql = "
    SELECT b.*, u.name as user_name, u.email as user_email, 
           f.flight_number, f.from_location, f.to_location, f.departure_date, f.departure_time, f.price
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN flights f ON b.flight_id = f.id 
    WHERE 1=1
";
$params = [];

if (!empty($status_filter)) {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR f.flight_number LIKE ? OR f.from_location LIKE ? OR f.to_location LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

$sql .= " ORDER BY b.created_at DESC";

$bookings = $db->fetchAll($sql, $params);

// Get booking statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM bookings
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Admin - Bookings</title>
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
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-confirmed { background-color: #d1edff; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .booking-card { 
            transition: transform 0.2s; 
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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
                            <a class="nav-link" href="index.php">
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
                            <a class="nav-link active" href="bookings.php">
                                <i class="fas fa-ticket-alt me-2"></i>
                                Manage Bookings
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white-50">
                        <span>Quick Actions</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="bookings.php?status=pending">
                                <i class="fas fa-clock me-2"></i>
                                Pending Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="flights.php?status=active">
                                <i class="fas fa-check-circle me-2"></i>
                                Active Flights
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-eye me-2"></i>
                                View Public Site
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white-50">
                        <span>Account</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user-cog me-2"></i>
                                Profile Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-ticket-alt"></i> Manage Bookings
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?status=" class="btn btn-sm btn-outline-secondary">All</a>
                            <a href="?status=pending" class="btn btn-sm btn-outline-warning">Pending</a>
                            <a href="?status=confirmed" class="btn btn-sm btn-outline-info">Confirmed</a>
                            <a href="?status=completed" class="btn btn-sm btn-outline-success">Completed</a>
                        </div>
                    </div>
                </div>

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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">Total Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $stats['total']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-ticket-alt fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">Pending</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $stats['pending']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">Confirmed</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $stats['confirmed']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">Completed</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $stats['completed']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-plane fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search bookings..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="bookings.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings List -->
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No bookings found</h4>
                        <p class="text-muted">Try adjusting your search criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($bookings as $booking): ?>
                            <?php $passenger_details = getPassengerDetails($booking['passenger_details']); ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card booking-card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span class="badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                        <small class="text-muted">#<?php echo $booking['id']; ?></small>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong><?php echo htmlspecialchars($booking['flight_number']); ?></strong>
                                        </div>
                                        
                                        <div class="flight-route mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="route-point">
                                                    <i class="fas fa-plane-departure text-primary"></i>
                                                    <strong><?php echo htmlspecialchars($booking['from_location']); ?></strong>
                                                </div>
                                                <div class="route-line">
                                                    <i class="fas fa-plane text-muted"></i>
                                                </div>
                                                <div class="route-point">
                                                    <i class="fas fa-plane-arrival text-success"></i>
                                                    <strong><?php echo htmlspecialchars($booking['to_location']); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Customer</small>
                                                <div><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Passenger</small>
                                                <div><?php echo htmlspecialchars($passenger_details['name']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Date</small>
                                                <div><?php echo formatDate($booking['departure_date']); ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Time</small>
                                                <div><?php echo formatTime($booking['departure_time']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            onclick="return confirm('Approve this booking?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            onclick="return confirm('Cancel this booking?')">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php elseif ($booking['status'] === 'confirmed'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="action" value="complete">
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            onclick="return confirm('Mark as completed?')">
                                                        <i class="fas fa-plane"></i> Complete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="viewBookingDetails(<?php echo $booking['id']; ?>)">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                            
                                            <a href="?action=delete&id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewBookingDetails(bookingId) {
            // This would load booking details via AJAX
            // For now, just show a simple message
            document.getElementById('bookingModalBody').innerHTML = 
                '<p>Booking details for ID: ' + bookingId + '</p>' +
                '<p>This would show detailed passenger information, contact details, etc.</p>';
            
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }
    </script>
</body>
</html> 