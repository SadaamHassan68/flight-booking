<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/functions.php';

// Require admin login
requireAdminLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_number = sanitizeInput($_POST['flight_number']);
    $from_location = sanitizeInput($_POST['from_location']);
    $to_location = sanitizeInput($_POST['to_location']);
    $departure_date = sanitizeInput($_POST['departure_date']);
    $departure_time = sanitizeInput($_POST['departure_time']);
    $arrival_time = sanitizeInput($_POST['arrival_time']);
    $total_seats = (int)$_POST['total_seats'];
    $price = (float)$_POST['price'];
    $status = sanitizeInput($_POST['status']);
    
    // Validation
    if (empty($flight_number) || empty($from_location) || empty($to_location) || 
        empty($departure_date) || empty($departure_time) || empty($arrival_time) || 
        $total_seats <= 0 || $price <= 0) {
        $error = 'Please fill in all fields with valid values.';
    } else {
        try {
            $db = new Database();
            
            // Check if flight number already exists
            $existing = $db->fetchOne("SELECT id FROM flights WHERE flight_number = ?", [$flight_number]);
            if ($existing) {
                $error = 'Flight number already exists.';
            } else {
                $flight_data = [
                    'flight_number' => $flight_number,
                    'from_location' => $from_location,
                    'to_location' => $to_location,
                    'departure_date' => $departure_date,
                    'departure_time' => $departure_time,
                    'arrival_time' => $arrival_time,
                    'total_seats' => $total_seats,
                    'available_seats' => $total_seats,
                    'price' => $price,
                    'status' => $status
                ];
                
                $result = $db->insert('flights', $flight_data);
                
                if ($result) {
                    $success = 'Flight added successfully!';
                    // Clear form data
                    $_POST = array();
                } else {
                    $error = 'Failed to add flight. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Add Flight</title>
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
                            <a class="nav-link" href="flights.php">
                                <i class="fas fa-plane me-2"></i>
                                Manage Flights
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="add_flight.php">
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
                        <i class="fas fa-plus-circle text-primary"></i> Add New Flight
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="flights.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Flights
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

                <!-- Add Flight Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-plane text-primary"></i> Flight Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="flight_number" class="form-label">
                                        <i class="fas fa-plane text-primary"></i> Flight Number *
                                    </label>
                                    <input type="text" class="form-control" id="flight_number" name="flight_number" 
                                           value="<?php echo isset($_POST['flight_number']) ? htmlspecialchars($_POST['flight_number']) : ''; ?>" 
                                           placeholder="e.g., FL001" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on text-primary"></i> Status *
                                    </label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="from_location" class="form-label">
                                        <i class="fas fa-map-marker-alt text-success"></i> From Location *
                                    </label>
                                    <input type="text" class="form-control" id="from_location" name="from_location" 
                                           value="<?php echo isset($_POST['from_location']) ? htmlspecialchars($_POST['from_location']) : ''; ?>" 
                                           placeholder="e.g., New York" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="to_location" class="form-label">
                                        <i class="fas fa-map-marker text-danger"></i> To Location *
                                    </label>
                                    <input type="text" class="form-control" id="to_location" name="to_location" 
                                           value="<?php echo isset($_POST['to_location']) ? htmlspecialchars($_POST['to_location']) : ''; ?>" 
                                           placeholder="e.g., Los Angeles" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="departure_date" class="form-label">
                                        <i class="fas fa-calendar text-primary"></i> Departure Date *
                                    </label>
                                    <input type="date" class="form-control" id="departure_date" name="departure_date" 
                                           value="<?php echo isset($_POST['departure_date']) ? htmlspecialchars($_POST['departure_date']) : ''; ?>" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="departure_time" class="form-label">
                                        <i class="fas fa-clock text-warning"></i> Departure Time *
                                    </label>
                                    <input type="time" class="form-control" id="departure_time" name="departure_time" 
                                           value="<?php echo isset($_POST['departure_time']) ? htmlspecialchars($_POST['departure_time']) : ''; ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="arrival_time" class="form-label">
                                        <i class="fas fa-clock text-success"></i> Arrival Time *
                                    </label>
                                    <input type="time" class="form-control" id="arrival_time" name="arrival_time" 
                                           value="<?php echo isset($_POST['arrival_time']) ? htmlspecialchars($_POST['arrival_time']) : ''; ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_seats" class="form-label">
                                        <i class="fas fa-chair text-info"></i> Total Seats *
                                    </label>
                                    <input type="number" class="form-control" id="total_seats" name="total_seats" 
                                           value="<?php echo isset($_POST['total_seats']) ? htmlspecialchars($_POST['total_seats']) : ''; ?>" 
                                           min="1" max="500" placeholder="e.g., 150" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">
                                        <i class="fas fa-dollar-sign text-success"></i> Price (USD) *
                                    </label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" 
                                           min="1" step="0.01" placeholder="e.g., 299.99" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-undo me-2"></i> Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Add Flight
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.getElementById('departure_date').min = new Date().toISOString().split('T')[0];
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const departureDate = document.getElementById('departure_date').value;
            const departureTime = document.getElementById('departure_time').value;
            const arrivalTime = document.getElementById('arrival_time').value;
            
            if (departureDate < new Date().toISOString().split('T')[0]) {
                e.preventDefault();
                alert('Departure date cannot be in the past.');
                return false;
            }
            
            if (departureTime >= arrivalTime) {
                e.preventDefault();
                alert('Arrival time must be after departure time.');
                return false;
            }
        });
    </script>
</body>
</html> 