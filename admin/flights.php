<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/functions.php';

// Require admin login
requireAdminLogin();

$success = '';
$error = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $flight_id = (int)$_GET['id'];
    
    try {
        $db = new Database();
        
        // Check if flight has bookings
        $bookings = $db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE flight_id = ?", [$flight_id]);
        
        if ($bookings && $bookings['count'] > 0) {
            $error = 'Cannot delete flight with existing bookings.';
        } else {
            $result = $db->delete('flights', ['id' => $flight_id]);
            if ($result) {
                $success = 'Flight deleted successfully!';
            } else {
                $error = 'Failed to delete flight.';
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Get all flights
$flights = getAllFlights();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Manage Flights</title>
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
                            <a class="nav-link active" href="flights.php">
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
                        <i class="fas fa-plane text-primary"></i> Manage Flights
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_flight.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> Add New Flight
                        </a>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Flights Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list text-primary"></i> All Flights
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($flights): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Flight Number</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Seats</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($flights as $flight): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($flight['flight_number']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($flight['from_location']); ?></td>
                                                <td><?php echo htmlspecialchars($flight['to_location']); ?></td>
                                                <td><?php echo formatDate($flight['departure_date']); ?></td>
                                                <td>
                                                    <?php echo formatTime($flight['departure_time']); ?> - 
                                                    <?php echo formatTime($flight['arrival_time']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $flight['available_seats']; ?>/<?php echo $flight['total_seats']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatPrice($flight['price']); ?></td>
                                                <td><?php echo getStatusBadge($flight['status']); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_flight.php?id=<?php echo $flight['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="view_flight.php?id=<?php echo $flight['id']; ?>" 
                                                           class="btn btn-sm btn-outline-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDelete(<?php echo $flight['id']; ?>, '<?php echo htmlspecialchars($flight['flight_number']); ?>')" 
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No flights found</h5>
                                <p class="text-muted">Start by adding your first flight.</p>
                                <a href="add_flight.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i> Add First Flight
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(flightId, flightNumber) {
            if (confirm('Are you sure you want to delete flight ' + flightNumber + '?\n\nThis action cannot be undone.')) {
                window.location.href = 'flights.php?action=delete&id=' + flightId;
            }
        }
    </script>
</body>
</html> 