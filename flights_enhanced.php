<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get available flights
$flights = getAvailableFlights();

// Handle search filters
$from_location = isset($_GET['from']) ? sanitizeInput($_GET['from']) : '';
$to_location = isset($_GET['to']) ? sanitizeInput($_GET['to']) : '';
$date = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';

// Filter flights based on search criteria
if (!empty($from_location) || !empty($to_location) || !empty($date)) {
    $filtered_flights = [];
    foreach ($flights as $flight) {
        $match = true;
        
        if (!empty($from_location) && stripos($flight['from_location'], $from_location) === false) {
            $match = false;
        }
        if (!empty($to_location) && stripos($flight['to_location'], $to_location) === false) {
            $match = false;
        }
        if (!empty($date) && $flight['departure_date'] !== $date) {
            $match = false;
        }
        
        if ($match) {
            $filtered_flights[] = $flight;
        }
    }
    $flights = $filtered_flights;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Browse Flights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/customer.css" rel="stylesheet">
    <style>
        /* Flight-specific enhancements */
        .flight-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .flight-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 200" fill="rgba(255,255,255,0.1)"><path d="M0,200 Q250,100 500,200 T1000,200 L1000,0 L0,0 Z"/></svg>');
            background-size: cover;
        }
        
        .search-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        
        .flight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }
        
        .flight-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .flight-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .flight-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="rgba(255,255,255,0.1)"><circle cx="20" cy="20" r="2"/><circle cx="80" cy="40" r="1.5"/><circle cx="40" cy="80" r="1"/></svg>');
            background-size: 50px 50px;
        }
        
        .flight-number {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .flight-status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .flight-body {
            padding: 25px;
        }
        
        .route-display {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .route-point {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .route-point i {
            font-size: 1.8rem;
            margin-right: 15px;
        }
        
        .route-info h5 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .route-info small {
            color: #666;
            font-size: 0.9rem;
        }
        
        .route-arrow {
            margin: 0 25px;
            color: #667eea;
            font-size: 1.5rem;
            position: relative;
        }
        
        .route-arrow::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -10px;
            right: -10px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #667eea, transparent);
            transform: translateY(-50%);
        }
        
        .flight-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .detail-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .detail-item small {
            display: block;
            color: #666;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .detail-item strong {
            color: #333;
            font-size: 1.1rem;
        }
        
        .price-section {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .price-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .price-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .seats-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .seats-available {
            color: #28a745;
            font-weight: 600;
        }
        
        .seats-total {
            color: #666;
            font-size: 0.9rem;
        }
        
        .flight-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-book {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 10px;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-details {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-details:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .no-flights {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .no-flights i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .no-flights h4 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .no-flights p {
            color: #666;
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .flight-grid {
                grid-template-columns: 1fr;
            }
            
            .route-display {
                flex-direction: column;
                text-align: center;
            }
            
            .route-arrow {
                margin: 15px 0;
                transform: rotate(90deg);
            }
            
            .flight-details {
                grid-template-columns: 1fr;
            }
            
            .flight-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="customer-dashboard">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="flights.php">Flights</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="customer/bookings.php">My Bookings</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="customer/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="flight-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-plane-departure"></i> Find Your Perfect Flight
                    </h1>
                    <p class="lead mb-0">Discover amazing destinations with our wide selection of flights</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Form -->
    <div class="container">
        <div class="search-form">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="from" class="form-label">
                                <i class="fas fa-plane-departure text-primary"></i> From
                            </label>
                            <input type="text" class="form-control" id="from" name="from" 
                                   value="<?php echo htmlspecialchars($from_location); ?>" 
                                   placeholder="Departure City">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="to" class="form-label">
                                <i class="fas fa-plane-arrival text-success"></i> To
                            </label>
                            <input type="text" class="form-control" id="to" name="to" 
                                   value="<?php echo htmlspecialchars($to_location); ?>" 
                                   placeholder="Destination City">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="date" class="form-label">
                                <i class="fas fa-calendar text-info"></i> Date
                            </label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?php echo htmlspecialchars($date); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn-customer flex-fill">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="flights.php" class="btn-outline-customer">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Flights Section -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-plane"></i> Available Flights</h2>
                    <div class="text-muted">
                        Found <?php echo count($flights); ?> flight(s)
                    </div>
                </div>
                
                <?php if (empty($flights)): ?>
                    <div class="no-flights">
                        <i class="fas fa-plane-slash"></i>
                        <h4>No Flights Found</h4>
                        <p>Sorry, no flights match your search criteria. Try adjusting your filters or check back later.</p>
                        <a href="flights.php" class="btn-customer">
                            <i class="fas fa-refresh"></i> View All Flights
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flight-grid">
                        <?php foreach ($flights as $flight): ?>
                            <div class="flight-card">
                                <div class="flight-header">
                                    <div class="flight-number"><?php echo htmlspecialchars($flight['flight_number']); ?></div>
                                    <div class="flight-status"><?php echo ucfirst($flight['status']); ?></div>
                                </div>
                                
                                <div class="flight-body">
                                    <div class="route-display">
                                        <div class="route-point">
                                            <i class="fas fa-plane-departure text-primary"></i>
                                            <div class="route-info">
                                                <h5><?php echo htmlspecialchars($flight['from_location']); ?></h5>
                                                <small>Departure</small>
                                            </div>
                                        </div>
                                        <div class="route-arrow">
                                            <i class="fas fa-plane"></i>
                                        </div>
                                        <div class="route-point">
                                            <i class="fas fa-plane-arrival text-success"></i>
                                            <div class="route-info">
                                                <h5><?php echo htmlspecialchars($flight['to_location']); ?></h5>
                                                <small>Arrival</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-details">
                                        <div class="detail-item">
                                            <small>Date</small>
                                            <strong><?php echo formatDate($flight['departure_date']); ?></strong>
                                        </div>
                                        <div class="detail-item">
                                            <small>Departure</small>
                                            <strong><?php echo formatTime($flight['departure_time']); ?></strong>
                                        </div>
                                        <div class="detail-item">
                                            <small>Arrival</small>
                                            <strong><?php echo formatTime($flight['arrival_time']); ?></strong>
                                        </div>
                                        <div class="detail-item">
                                            <small>Duration</small>
                                            <strong><?php 
                                                $departure = new DateTime($flight['departure_time']);
                                                $arrival = new DateTime($flight['arrival_time']);
                                                $duration = $departure->diff($arrival);
                                                echo $duration->format('%Hh %Im');
                                            ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="price-section">
                                        <div class="price-amount"><?php echo formatPrice($flight['price']); ?></div>
                                        <div class="price-label">per passenger</div>
                                    </div>
                                    
                                    <div class="seats-info">
                                        <div>
                                            <span class="seats-available">
                                                <i class="fas fa-chair"></i> <?php echo $flight['available_seats']; ?> seats available
                                            </span>
                                        </div>
                                        <div class="seats-total">
                                            of <?php echo $flight['total_seats']; ?> total
                                        </div>
                                    </div>
                                    
                                    <div class="flight-actions">
                                        <?php if (isLoggedIn()): ?>
                                            <a href="customer/book_flight.php?flight_id=<?php echo $flight['id']; ?>" 
                                               class="btn-book">
                                                <i class="fas fa-ticket-alt"></i> Book Now
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn-book">
                                                <i class="fas fa-sign-in-alt"></i> Login to Book
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn-details" onclick="showFlightDetails(<?php echo $flight['id']; ?>)">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFlightDetails(flightId) {
            // You can implement a modal or redirect to a details page
            alert('Flight details for ID: ' + flightId + '\nThis feature can be expanded to show more detailed information.');
        }
    </script>
</body>
</html> 