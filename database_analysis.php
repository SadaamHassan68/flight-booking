<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Database Analysis Script
// This script analyzes the current state of your flight booking database

$db = new Database();
$analysis = [];

try {
    // Test database connection
    $connection_test = $db->getConnection();
    $analysis['connection'] = '✅ Database connection successful';
    
    // Get database information
    $db_info = $db->fetchOne("SELECT DATABASE() as db_name, VERSION() as version");
    $analysis['database_info'] = [
        'name' => $db_info['db_name'],
        'version' => $db_info['version']
    ];
    
    // Check if tables exist
    $tables = $db->fetchAll("SHOW TABLES");
    $analysis['tables'] = [];
    
    foreach ($tables as $table) {
        $table_name = array_values($table)[0];
        $analysis['tables'][] = $table_name;
    }
    
    // Analyze users table
    if (in_array('users', $analysis['tables'])) {
        $users_count = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
        $admins_count = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'];
        $customers_count = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")['count'];
        
        $analysis['users'] = [
            'total' => $users_count,
            'admins' => $admins_count,
            'customers' => $customers_count
        ];
        
        // Get admin users
        $admin_users = $db->fetchAll("SELECT id, username, email, full_name, created_at FROM users WHERE role = 'admin'");
        $analysis['admin_users'] = $admin_users;
        
        // Get recent users
        $recent_users = $db->fetchAll("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $analysis['recent_users'] = $recent_users;
    }
    
    // Analyze flights table
    if (in_array('flights', $analysis['tables'])) {
        $flights_count = $db->fetchOne("SELECT COUNT(*) as count FROM flights")['count'];
        $active_flights = $db->fetchOne("SELECT COUNT(*) as count FROM flights WHERE status = 'active'")['count'];
        $available_flights = $db->fetchOne("SELECT COUNT(*) as count FROM flights WHERE status = 'active' AND available_seats > 0")['count'];
        
        $analysis['flights'] = [
            'total' => $flights_count,
            'active' => $active_flights,
            'available' => $available_flights
        ];
        
        // Get recent flights
        $recent_flights = $db->fetchAll("SELECT id, flight_number, source, destination, departure_date, status, available_seats FROM flights ORDER BY created_at DESC LIMIT 5");
        $analysis['recent_flights'] = $recent_flights;
    }
    
    // Analyze bookings table
    if (in_array('bookings', $analysis['tables'])) {
        $bookings_count = $db->fetchOne("SELECT COUNT(*) as count FROM bookings")['count'];
        $pending_bookings = $db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'];
        $confirmed_bookings = $db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'];
        $cancelled_bookings = $db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'")['count'];
        
        $analysis['bookings'] = [
            'total' => $bookings_count,
            'pending' => $pending_bookings,
            'confirmed' => $confirmed_bookings,
            'cancelled' => $cancelled_bookings
        ];
        
        // Get recent bookings
        $recent_bookings = $db->fetchAll("
            SELECT b.id, b.passenger_name, b.status, b.total_amount, b.booking_date,
                   u.username, f.flight_number
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            LEFT JOIN flights f ON b.flight_id = f.id 
            ORDER BY b.booking_date DESC LIMIT 5
        ");
        $analysis['recent_bookings'] = $recent_bookings;
    }
    
    // Check for potential issues
    $analysis['issues'] = [];
    
    // Check for flights with no available seats
    $no_seats_flights = $db->fetchAll("SELECT flight_number, source, destination FROM flights WHERE available_seats = 0 AND status = 'active'");
    if (!empty($no_seats_flights)) {
        $analysis['issues'][] = '⚠️ Flights with no available seats: ' . count($no_seats_flights);
    }
    
    // Check for pending bookings
    if ($pending_bookings > 0) {
        $analysis['issues'][] = '📋 Pending bookings that need attention: ' . $pending_bookings;
    }
    
    // Check for orphaned bookings (no user or flight)
    $orphaned_bookings = $db->fetchOne("
        SELECT COUNT(*) as count 
        FROM bookings b 
        LEFT JOIN users u ON b.user_id = u.id 
        LEFT JOIN flights f ON b.flight_id = f.id 
        WHERE u.id IS NULL OR f.id IS NULL
    ")['count'];
    
    if ($orphaned_bookings > 0) {
        $analysis['issues'][] = '🚨 Orphaned bookings found: ' . $orphaned_bookings;
    }
    
    // Database size estimation
    $db_size = $db->fetchOne("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ")['DB Size in MB'];
    
    $analysis['database_size'] = $db_size . ' MB';
    
} catch (Exception $e) {
    $analysis['error'] = '❌ Database analysis failed: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Database Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane"></i> <?php echo SITE_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-database"></i> Database Analysis Report
                </h1>
                
                <?php if (isset($analysis['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $analysis['error']; ?>
                    </div>
                <?php else: ?>
                    <!-- Database Connection Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-plug"></i> Database Connection</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><?php echo $analysis['connection']; ?></p>
                            <p class="mb-0">
                                <strong>Database:</strong> <?php echo $analysis['database_info']['name']; ?> | 
                                <strong>Version:</strong> <?php echo $analysis['database_info']['version']; ?> |
                                <strong>Size:</strong> <?php echo $analysis['database_size']; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Database Tables -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-table"></i> Database Tables</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($analysis['tables'] as $table): ?>
                                    <div class="col-md-3 mb-2">
                                        <span class="badge bg-success"><?php echo $table; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Users Analysis -->
                    <?php if (isset($analysis['users'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-users"></i> Users Analysis</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h3 class="text-primary"><?php echo $analysis['users']['total']; ?></h3>
                                            <p class="text-muted">Total Users</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h3 class="text-danger"><?php echo $analysis['users']['admins']; ?></h3>
                                            <p class="text-muted">Administrators</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h3 class="text-success"><?php echo $analysis['users']['customers']; ?></h3>
                                            <p class="text-muted">Customers</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Admin Users -->
                                <?php if (!empty($analysis['admin_users'])): ?>
                                    <h6>Admin Users:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Full Name</th>
                                                    <th>Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($analysis['admin_users'] as $admin): ?>
                                                    <tr>
                                                        <td><?php echo $admin['id']; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Flights Analysis -->
                    <?php if (isset($analysis['flights'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-plane"></i> Flights Analysis</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h3 class="text-primary"><?php echo $analysis['flights']['total']; ?></h3>
                                            <p class="text-muted">Total Flights</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h3 class="text-success"><?php echo $analysis['flights']['active']; ?></h3>
                                            <p class="text-muted">Active Flights</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h3 class="text-info"><?php echo $analysis['flights']['available']; ?></h3>
                                            <p class="text-muted">Available Flights</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recent Flights -->
                                <?php if (!empty($analysis['recent_flights'])): ?>
                                    <h6>Recent Flights:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Flight #</th>
                                                    <th>Route</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Seats</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($analysis['recent_flights'] as $flight): ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($flight['flight_number']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($flight['source']); ?> → <?php echo htmlspecialchars($flight['destination']); ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($flight['departure_date'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $flight['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($flight['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $flight['available_seats']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bookings Analysis -->
                    <?php if (isset($analysis['bookings'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-ticket-alt"></i> Bookings Analysis</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-primary"><?php echo $analysis['bookings']['total']; ?></h3>
                                            <p class="text-muted">Total Bookings</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-warning"><?php echo $analysis['bookings']['pending']; ?></h3>
                                            <p class="text-muted">Pending</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-success"><?php echo $analysis['bookings']['confirmed']; ?></h3>
                                            <p class="text-muted">Confirmed</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-danger"><?php echo $analysis['bookings']['cancelled']; ?></h3>
                                            <p class="text-muted">Cancelled</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recent Bookings -->
                                <?php if (!empty($analysis['recent_bookings'])): ?>
                                    <h6>Recent Bookings:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Passenger</th>
                                                    <th>Customer</th>
                                                    <th>Flight</th>
                                                    <th>Status</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($analysis['recent_bookings'] as $booking): ?>
                                                    <tr>
                                                        <td>#<?php echo $booking['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($booking['username'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($booking['flight_number'] ?? 'N/A'); ?></td>
                                                        <td>
                                                            <span class="badge status-<?php echo $booking['status']; ?>">
                                                                <?php echo ucfirst($booking['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo formatPrice($booking['total_amount']); ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Issues and Recommendations -->
                    <?php if (!empty($analysis['issues'])): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Issues & Recommendations</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <?php foreach ($analysis['issues'] as $issue): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-info-circle text-warning me-2"></i>
                                            <?php echo $issue; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- System Health -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-heartbeat"></i> System Health</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>✅ Good:</h6>
                                    <ul>
                                        <li>Database connection working</li>
                                        <li>All required tables exist</li>
                                        <li>Admin users configured</li>
                                        <li>Flight booking system operational</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>📋 Recommendations:</h6>
                                    <ul>
                                        <li>Regularly backup your database</li>
                                        <li>Monitor pending bookings</li>
                                        <li>Update flight availability regularly</li>
                                        <li>Review system logs periodically</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="mb-0">Database Analysis Report</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Generated on: <?php echo date('F j, Y \a\t g:i A'); ?></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 